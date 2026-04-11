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

// Pagamentos Acoplados à Venda (Nova abstração)
$stmt_pgtos = $conn->prepare("SELECT * FROM venda_pagamentos WHERE venda_id = ?");
$stmt_pgtos->execute([$venda_id]);
$pagamentos = $stmt_pgtos->fetchAll(PDO::FETCH_ASSOC);

// Fallback se para transações muito antigas não houver na venda_pagamentos
if (empty($pagamentos)) {
    $pagamentos[] = ['metodo_pagamento' => $venda['payment_method'], 'valor' => $venda['total_amount']];
}

$total_recebido = 0;
foreach($pagamentos as $pg) {
    $total_recebido += $pg['valor'];
}
$troco = max(0, $total_recebido - $venda['total_amount']);

// Dados da Empresa
$stmt_empresa = $conn->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt_empresa->execute([$empresa_id]);
$empresa = $stmt_empresa->fetch(PDO::FETCH_ASSOC);

// Função auxiliar para formato cupom (alinhamento esq/dir num tamanho maximo)
function fill_spaces($left, $right, $totalWidth = 40) {
    $len = mb_strlen($left, 'UTF-8') + mb_strlen($right, 'UTF-8');
    $spaces = $totalWidth - $len;
    if ($spaces < 1) $spaces = 1;
    return $left . str_repeat("&nbsp;", $spaces) . $right;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupom Fiscal Opcional - Venda #<?php echo $venda['id']; ?></title>
    <!-- Para o layout de cupom, evitamos frameworks grandes e focamos no "hardcode" CSS térmico -->
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            /* Fundo escuro para a tela, mas ticket branco */
            background-color: #222;
            color: #000;
            font-family: 'Consolas', 'Courier New', Courier, monospace;
            font-size: 13px;
        }

        .ticket {
            width: 300px;
            max-width: 300px;
            margin: 20px auto;
            background: #fff;
            padding: 10px 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            /* Importante para forçar texto em preto e branco purista */
            color: #000;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .divider-solid {
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        .header h2 {
            font-size: 16px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .header p {
            font-size: 12px;
            margin-bottom: 2px;
        }

        .content-table {
            width: 100%;
            margin-bottom: 5px;
        }

        /* Tabela não-borda para os itens */
        .item-line {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        
        .item-desc {
            font-size: 12px;
            margin-bottom: 2px;
            word-break: break-all;
        }

        .actions {
            margin-top: 20px;
            text-align: center;
            background: #333;
            padding: 10px;
        }

        .btn {
            background-color: #f1f1f1;
            border: 1px solid #ccc;
            padding: 10px 20px;
            cursor: pointer;
            font-family: Arial, sans-serif;
            font-weight: bold;
            color: #333;
            text-decoration: none;
            display: inline-block;
            margin: 0 5px;
            border-radius: 4px;
        }
        
        .btn-primary { background-color: #0d6efd; color: white; border-color: #0d6efd; }

        @media print {
            /* Regras críticas para impressoras térmicas */
            @page {
                margin: 0;
            }
            body {
                background-color: #fff;
                margin: 0;
            }
            .ticket {
                width: 100%; /* Adapta para 58mm ou 80mm baseado na impressora padrão do usuário */
                max-width: 100%;
                margin: 0;
                padding: 5px;
                box-shadow: none;
                border: none;
            }
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header center">
            <h2><?php echo htmlspecialchars($empresa['name'] ?: 'NOME DA EMPRESA'); ?></h2>
            <p><?php echo htmlspecialchars($empresa['address'] ?? 'ENDERECO NAO INFORMADO'); ?></p>
            <p>CNPJ: <?php echo htmlspecialchars($empresa['cnpj'] ?? '00.000.000/0000-00'); ?></p>
            <p>Tel: <?php echo htmlspecialchars($empresa['phone'] ?? '(00) 0000-0000'); ?></p>
            <div class="divider"></div>
            <p>CUPOM PROMOCIONAL NÃO FISCAL</p>
            <div class="divider"></div>
        </div>

        <div style="font-size: 12px;">
            <p>Data: <?php echo date('d/m/Y H:i:s', strtotime($venda['created_at'])); ?></p>
            <p>Pedido Nº: <b><?php echo str_pad($venda['id'], 6, '0', STR_PAD_LEFT); ?></b></p>
            <p>Vendedor: <?php echo mb_strtoupper(htmlspecialchars($venda['vendedor_nome']), 'UTF-8'); ?></p>
        </div>

        <div class="divider-solid"></div>

        <div style="font-size: 12px; margin-bottom: 5px;">
            <div class="item-line bold">
                <span>ITEM  QTD x UN</span>
                <span>TOTAL</span>
            </div>
        </div>
        
        <div class="divider"></div>

        <?php 
        $i = 1;
        foreach ($itens as $item): 
            $sub = $item['unit_price'] * $item['quantity'];
        ?>
        <div style="margin-bottom: 5px;">
            <div class="item-desc">
                <?php echo str_pad($i, 3, '0', STR_PAD_LEFT); ?> - <?php echo mb_strtoupper(htmlspecialchars($item['produto_nome'])); ?>
            </div>
            <div class="item-line">
                <span>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $item['quantity']; ?> x <?php echo number_format($item['unit_price'], 2, ',', '.'); ?></span>
                <span><?php echo number_format($sub, 2, ',', '.'); ?></span>
            </div>
        </div>
        <?php $i++; endforeach; ?>

        <div class="divider-solid"></div>

        <!-- TOTAL GERAL -->
        <div class="item-line bold" style="font-size: 14px; margin: 4px 0;">
            <span>TOTAL R$</span>
            <span><?php echo number_format($venda['total_amount'], 2, ',', '.'); ?></span>
        </div>

        <div class="divider"></div>

        <!-- FORMAS DE PAGAMENTO DETALHADAS -->
        <p class="bold" style="font-size: 12px; margin-bottom:2px;">FORMA DE PAGAMENTO</p>
        <?php foreach ($pagamentos as $pg): 
            $metodo_label = match($pg['metodo_pagamento']) {
                'dinheiro' => 'DINHEIRO',
                'pix' => 'PIX',
                'cartao_debito', 'debito' => 'CARTÃO DÉBITO',
                'cartao_credito', 'credito' => 'CARTÃO CRÉDITO',
                default => mb_strtoupper($pg['metodo_pagamento'])
            };
        ?>
        <div class="item-line" style="font-size: 12px;">
            <span><?php echo $metodo_label; ?></span>
            <span><?php echo number_format($pg['valor'], 2, ',', '.'); ?></span>
        </div>
        <?php endforeach; ?>

        <div class="divider"></div>

        <div class="item-line" style="font-size: 12px;">
            <span>TOTAL RECEBIDO R$</span>
            <span><?php echo number_format($total_recebido, 2, ',', '.'); ?></span>
        </div>
        
        <?php if ($troco > 0): ?>
        <div class="item-line bold" style="font-size: 12px; margin-top:2px;">
            <span>TROCO R$</span>
            <span><?php echo number_format($troco, 2, ',', '.'); ?></span>
        </div>
        <?php endif; ?>

        <div class="divider-solid" style="margin-top: 15px;"></div>
        
        <div class="center" style="margin-top: 10px; font-size: 12px;">
            <p>Obrigado pela preferência!</p>
            <p>Volte Sempre</p>
            <br>
            <p style="font-size: 10px;">Sistema Brasallis 360 - hub.brasallis.com.br</p>
        </div>
    </div>

    <!-- AREA Nao Imprimível (TELA) -->
    <div class="actions">
        <button class="btn btn-primary" onclick="window.print();">🖨️ IMPRIMIR RECIBO</button>
        <a href="pdv.php" class="btn">FECHAR (VOLTAR AO CAIXA)</a>
    </div>

    <!-- Script para agonia de atalhos e impressão rápida -->
    <script>
        document.addEventListener('keydown', (e) => {
            if (e.key === 'p' || e.key === 'P') { // Option for quick print without macro
                window.print();
            }
            if (e.key === 'Escape' || e.key === 'Esc') {
                window.location.href = 'pdv.php';
            }
        });
        
        // Muitos lojistas pedem para abrir a aba imprimir automaticamente quando carregado
        // Descomente a linha abaixo caso o cliente queira auto-print
        window.onload = function() {
            // window.print();
        };
    </script>
</body>
</html>
