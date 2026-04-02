<?php
// modules/pdv/api/process_sale.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/db_config.php';

session_start();
if (!isset($_SESSION['empresa_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$items = $input['items'] ?? [];
$method = $input['payment_method'] ?? 'dinheiro';
$empresa_id = $_SESSION['empresa_id'];
$user_id = $_SESSION['user_id'];

if (empty($items)) {
    echo json_encode(['error' => 'Carrinho vazio']);
    exit;
}

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->beginTransaction();

    // 1. Calculate Total and Verify Stock
    $total = 0;
    foreach ($items as $item) {
        // Double check stock
        $stmt = $conn->prepare("SELECT quantity, price FROM produtos WHERE id = ? AND empresa_id = ? FOR UPDATE");
        $stmt->execute([$item['id'], $empresa_id]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prod) throw new Exception("Produto ID {$item['id']} não encontrado.");
        if ($prod['quantity'] < $item['qty']) throw new Exception("Estoque insuficiente para {$item['name']}.");

        $total += $prod['price'] * $item['qty'];
    }

    // 2. Create Sale Record
    $sqlSale = "INSERT INTO vendas (empresa_id, user_id, total_amount, payment_method, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sqlSale);
    $stmt->execute([$empresa_id, $user_id, $total, $method]);
    $vendaId = $conn->lastInsertId();

    // 3. Process Items and Deduct Stock
    $sqlItem = "INSERT INTO venda_itens (venda_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
    $stmtItem = $conn->prepare($sqlItem);

    $sqlUpdate = "UPDATE produtos SET quantity = quantity - ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);

    // 3b. Log Stock Movement
    // Schema: id, empresa_id, user_id, product_id, action, quantity, new_quantity, venda_id, created_at
    // We are replacing 'details' (which doesn't exist) with 'venda_id'
    $sqlLog = "INSERT INTO historico_estoque (empresa_id, product_id, user_id, action, quantity, venda_id, created_at) VALUES (?, ?, ?, 'saida_venda', ?, ?, NOW())";
    $stmtLog = $conn->prepare($sqlLog);

    foreach ($items as $item) {
        $price = $item['price']; 
        
        $stmtItem->execute([$vendaId, $item['id'], $item['qty'], $price]);
        $stmtUpdate->execute([$item['qty'], $item['id']]);
        
        // Log movement with venda_id
        $stmtLog->execute([$empresa_id, $item['id'], $user_id, $item['qty'], $vendaId]);
    }

    // 4. Integrar com Financeiro (Contas a Receber)
    // Se a venda foi finalizada, geramos o recebível correspondente
    $statusReceber = ($method === 'dinheiro' || $method === 'pix' || $method === 'cartao') ? 'recebido' : 'pendente';
    $dataRecebimento = ($statusReceber === 'recebido') ? 'NOW()' : 'NULL';
    
    $sqlFin = "INSERT INTO contas_receber (empresa_id, descricao, valor, data_vencimento, data_recebimento, status, venda_id) 
               VALUES (?, ?, ?, CURDATE(), $dataRecebimento, ?, ?)";
    $stmtFin = $conn->prepare($sqlFin);
    $stmtFin->execute([$empresa_id, "Venda PDV #$vendaId", $total, $statusReceber, $vendaId]);

    $conn->commit();
    echo json_encode(['success' => true, 'venda_id' => $vendaId]);

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
