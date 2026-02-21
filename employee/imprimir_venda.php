<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Não incluir o cabeçalho padrão para ter uma página limpa para impressão
require_once '../includes/funcoes.php';

// Validações de segurança
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die('Acesso negado. Faça o login.');
}

$venda_id = $_GET['id'] ?? null;
if (!$venda_id) {
    http_response_code(400);
    die('ID da venda não fornecido.');
}

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// --- BUSCAR DADOS DA VENDA E DA EMPRESA ---

// Dados da Venda
$stmt_venda = $conn->prepare("SELECT v.*, u.username as vendedor_nome FROM vendas v JOIN usuarios u ON v.user_id = u.id WHERE v.id = ? AND v.empresa_id = ?");
$stmt_venda->execute([$venda_id, $empresa_id]);
$venda = $stmt_venda->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    http_response_code(404);
    die('Venda não encontrada ou não pertence à sua empresa.');
}

// Itens da Venda
$stmt_itens = $conn->prepare("SELECT vi.*, p.name as produto_nome, p.sku FROM venda_itens vi JOIN produtos p ON vi.product_id = p.id WHERE vi.venda_id = ?");
$stmt_itens->execute([$venda_id]);
$itens = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

// Dados da Empresa
$stmt_empresa = $conn->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt_empresa->execute([$empresa_id]);
$empresa = $stmt_empresa->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Venda #<?php echo $venda['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .receipt-container {
            max-width: 800px;
            margin: 2rem auto;
            background: #fff;
            padding: 2rem;
            border: 1px solid #dee2e6;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .actions {
            text-align: center;
            margin-top: 2rem;
        }
        @media print {
            body {
                background-color: #fff;
            }
            .receipt-container {
                margin: 0;
                border: none;
                box-shadow: none;
                max-width: 100%;
            }
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h2><?php echo htmlspecialchars($empresa['name']); ?></h2>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars($empresa['address']); ?><br>
                CNPJ: <?php echo htmlspecialchars($empresa['cnpj']); ?> | Tel: <?php echo htmlspecialchars($empresa['phone']); ?>
            </p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Recibo de Venda</h4>
            <p class="mb-0"><strong>#<?php echo $venda['id']; ?></strong></p>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <strong>Data da Venda:</strong> <?php echo date('d/m/Y H:i', strtotime($venda['created_at'])); ?>
            </div>
            <div class="col-6 text-end">
                <strong>Vendedor:</strong> <?php echo htmlspecialchars($venda['vendedor_nome']); ?>
            </div>
        </div>

        <table class="table">
            <thead class="table-light">
                <tr>
                    <th>SKU</th>
                    <th>Produto</th>
                    <th class="text-center">Qtd.</th>
                    <th class="text-end">Preço Unit.</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                <tr>
                    <td class="text-muted"><?php echo htmlspecialchars($item['sku']); ?></td>
                    <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                    <td class="text-end">R$ <?php echo number_format($item['unit_price'], 2, ',', '.'); ?></td>
                    <td class="text-end">R$ <?php echo number_format($item['unit_price'] * $item['quantity'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold fs-5">
                    <td colspan="4" class="text-end">Total:</td>
                    <td class="text-end">R$ <?php echo number_format($venda['total_amount'], 2, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="text-center text-muted mt-4">
            <p>Obrigado pela sua compra!</p>
        </div>

        <div class="actions">
            <button class="btn btn-primary" onclick="window.print();">
                <i class="fas fa-print me-2"></i>Imprimir
            </button>
            <a href="pdv.php" class="btn btn-secondary">Voltar ao PDV</a>
        </div>
    </div>
</body>
</html>
