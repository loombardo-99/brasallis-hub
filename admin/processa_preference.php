<?php
// admin/processa_preference.php
session_start();
require_once '../includes/funcoes.php';
require_once '../includes/MercadoPagoService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['empresa_id'])) {
    echo json_encode(['error' => 'Sessão expirada']);
    exit;
}

$plan = $_POST['plan'];
$prices = [
    'growth' => 99.00,
    'enterprise' => 299.00
];

if (!isset($prices[$plan])) {
    echo json_encode(['error' => 'Plano inválido']);
    exit;
}

$amount = $prices[$plan];
$empresa_id = $_SESSION['empresa_id'];
$user_email = $_SESSION['user_email'] ?? 'email@teste.com';

// URL base do sistema (ajuste conforme necessário)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_url = "$protocol://$host/gerenciador_de_estoque/admin";

try {
    $mp = new MercadoPagoService();
    // Definição das URLs de retorno (Moved up)
    $successUrl = "$base_url/sucesso.php";
    $failureUrl = "$base_url/checkout.php?error=payment_failed";
    $pendingUrl = "$base_url/sucesso.php?status=pending";

    // Webhook (Em localhost não funciona, precisa de túnel como ngrok. Em prod use a URL real)
    $notificationUrl = "$protocol://$host/gerenciador_de_estoque/api/webhook_mercadopago.php";
    // Se for localhost, anulamos para evitar erros de validação (ou use uma URL fixa de dev)
    if ($host === 'localhost' || $host === '127.0.0.1') {
        $notificationUrl = null; 
    }

    $paymentData = [
        'amount' => $amount,
        'description' => "Assinatura Gestor Inteligente - " . ucfirst($plan),
        'email' => $user_email,
        'first_name' => $_SESSION['username'],
        'last_name' => 'User', 
        'external_ref' => "EMP_{$empresa_id}_PLAN_{$plan}_" . time(),
        'back_urls' => [
            'success' => $successUrl,
            'failure' => $failureUrl,
            'pending' => $pendingUrl
        ],
        'notification_url' => $notificationUrl
    ];

    $response = $mp->createPreference($paymentData);

    if (isset($response['id'])) {
        // Opcional: Salvar intenção inicial no banco se quiser rastrear abandonos
        // Mas para Checkout Pro, o Webhook é a fonte da verdade.
        
        echo json_encode([
            'preference_id' => $response['id'],
            'init_point' => $response['init_point'], // Link para redirecionamento (Prod)
            'sandbox_init_point' => $response['sandbox_init_point'] // Link para testes
        ]);
    } else {
        echo json_encode(['error' => 'Erro MP: ' . json_encode($response)]);
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
