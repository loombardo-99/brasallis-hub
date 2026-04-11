<?php
// A lógica de processamento de formulário DEVE vir antes de qualquer output HTML.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/funcoes.php';

// Bloqueia acesso de usuário deslogado direto na interface do PDV
if (!isset($_SESSION['user_id']) || !isset($_SESSION['empresa_id'])) {
    header("Location: ../login.php");
    exit();
}

// Lógica de processamento da venda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
    $conn = connect_db();
    $empresa_id = $_SESSION['empresa_id'];
    $user_id = $_SESSION['user_id'];
    $cart_data = json_decode($_POST['cart_data'], true);
    
    // Novo payload de múltiplos pagamentos
    $payments_data = json_decode($_POST['payments_data'] ?? '[]', true);

    if (json_last_error() === JSON_ERROR_NONE && !empty($cart_data)) {
        try {
            $conn->beginTransaction();

            // 1. Calcular o valor total e buscar preços do DB para segurança
            $total_amount = 0;
            $product_ids = array_column($cart_data, 'id');
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            
            $price_stmt = $conn->prepare("SELECT id, price, quantity FROM produtos WHERE id IN ($placeholders) AND empresa_id = ?");
            $price_stmt->execute(array_merge($product_ids, [$empresa_id]));
            $results = $price_stmt->fetchAll(PDO::FETCH_ASSOC);
            $products_from_db = [];
            foreach ($results as $row) {
                $products_from_db[$row['id']] = $row;
            }

            foreach ($cart_data as $item) {
                $db_product = $products_from_db[$item['id']];
                if (!$db_product || $item['quantity'] > $db_product['quantity']) {
                    throw new Exception("Estoque insuficiente para o produto ID: {" . $item['id'] . "}. A venda foi cancelada.");
                }
                $total_amount += $db_product['price'] * $item['quantity'];
            }

            // Fallback caso venha sem os múltiplos
            if (empty($payments_data)) {
                $payment_method = $_POST['payment_method'] ?? 'dinheiro';
                $payments_data = [['method' => $payment_method, 'value' => $total_amount]];
            }

            // Validar o total pago
            $total_paid = 0;
            foreach ($payments_data as $pgto) {
                $total_paid += (float)$pgto['value'];
            }
            if (round($total_paid, 2) < round($total_amount, 2)) {
                throw new Exception("O valor pago é menor que o total da venda. Venda cancelada.");
            }

            $main_method = count($payments_data) === 1 ? $payments_data[0]['method'] : 'múltiplos';

            // 2. Inserir na tabela 'vendas'
            $venda_stmt = $conn->prepare("INSERT INTO vendas (empresa_id, user_id, total_amount, payment_method) VALUES (?, ?, ?, ?)");
            $venda_stmt->execute([$empresa_id, $user_id, $total_amount, $main_method]);
            $venda_id = $conn->lastInsertId();

            // 3. Inserir pagamentos múltiplos na nova tabela `venda_pagamentos`
            $pgto_stmt = $conn->prepare("INSERT INTO venda_pagamentos (venda_id, metodo_pagamento, valor) VALUES (?, ?, ?)");
            foreach ($payments_data as $pgto) {
                // Caso tenha dado um valor superior (troco em dinheiro), limita o inserido ao total na soma, ou registra cru.
                // Registraremos o valor real informado
                $pgto_stmt->execute([$venda_id, $pgto['method'], $pgto['value']]);
            }

            // 4. Inserir itens, atualizar estoque e registrar histórico
            $item_stmt = $conn->prepare("INSERT INTO venda_itens (venda_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $update_stmt = $conn->prepare("UPDATE produtos SET quantity = quantity - ? WHERE id = ? AND empresa_id = ?");
            $history_stmt = $conn->prepare("INSERT INTO historico_estoque (empresa_id, product_id, user_id, action, quantity, venda_id) VALUES (?, ?, ?, 'saida', ?, ?)");

            foreach ($cart_data as $item) {
                $product_id = $item['id'];
                $quantity = $item['quantity'];
                $unit_price = $products_from_db[$product_id]['price'];

                $item_stmt->execute([$venda_id, $product_id, $quantity, $unit_price]);
                $update_stmt->execute([$quantity, $product_id, $empresa_id]);
                $history_stmt->execute([$empresa_id, $product_id, $user_id, $quantity, $venda_id]);
            }

            // 5. Integração Fiscal Automática
            require_once __DIR__ . '/../src/Services/FiscalIntegrator.php';
            $fiscalService = new \App\Services\FiscalIntegrator($conn);
            $fiscalService->createFromSale($venda_id, $empresa_id, $user_id);

            // 6. Integração com Financeiro (Contas a Receber)
            // Agora varremos a tabela de múltiplos pagamentos
            $sqlFin = "INSERT INTO contas_receber (empresa_id, descricao, valor, data_vencimento, data_recebimento, status, venda_id) 
                       VALUES (?, ?, ?, CURDATE(), $1, ?, ?)";
            
            foreach ($payments_data as $pgto) {
                $m = $pgto['method'];
                $v = $pgto['value'];
                
                $statusReceber = ($m === 'dinheiro' || $m === 'pix' || $m === 'cartao_debito' || $m === 'debito') ? 'recebido' : 'pendente';
                $dataRecebimento = ($statusReceber === 'recebido') ? 'CURDATE()' : 'NULL';
                
                // Formatação correta para o insert com data dinâmica (não dá pra bindar CURDATE(), então formatamos string)
                $sqlFinD = "INSERT INTO contas_receber (empresa_id, descricao, valor, data_vencimento, data_recebimento, status, venda_id) 
                            VALUES (?, ?, ?, CURDATE(), $dataRecebimento, ?, ?)";
                $stmtFin = $conn->prepare($sqlFinD);
                // "Troco" pode ser ignorado no contas a receber ou apenas marcamos o valor correto se ultrapassou o total_amount
                // (Para simplificar, deixamos que o excesso/troco seja lidado naturalmente ou registramos normal)
                $stmtFin->execute([$empresa_id, "Venda PDV #$venda_id ($m)", $v, $statusReceber, $venda_id]);
            }

            $conn->commit();
            $_SESSION['last_venda_id'] = $venda_id; // Guarda o ID da última venda na sessão

        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['message'] = 'Erro ao processar a venda: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = 'Nenhum item no carrinho ou dados inválidos.';
        $_SESSION['message_type'] = 'warning';
    }
    header("Location: pdv.php");
    exit();
}

// --- A partir daqui, começa a renderização da página ---
include_once '../includes/cabecalho.php';

// Verifica se uma venda acabou de ser concluída para mostrar a mensagem com o link de impressão
if (isset($_SESSION['last_venda_id'])) {
    $last_venda_id = $_SESSION['last_venda_id'];
    $_SESSION['message'] = "Venda #{$last_venda_id} registrada com sucesso! <a href='imprimir_venda.php?id={$last_venda_id}' target='_blank' class='alert-link'>Imprimir Recibo</a>";
    $_SESSION['message_type'] = 'success';
    unset($_SESSION['last_venda_id']);
}

?>

<style>
    :root {
        --trust-navy: #0A2647;
        --trust-blue: #144272;
        --trust-accent: #205295;
        --trust-light: #F8F9FA;
    }

    body { background-color: #f4f7fa; }
    
    .card-premium {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .pdv-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .search-pill {
        border-radius: 50px !important;
        padding-left: 20px;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }

    #search-results {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        margin-top: 8px;
        max-height: 400px;
        overflow-y: auto;
    }

    .cart-summary {
        background: white;
        border-radius: 20px;
        padding: 25px;
        border: 2px solid #eef2f7;
    }

    .total-amount {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--trust-navy);
    }
    
    .text-warning-emphasis { color: #997404 !important; }
    
    /* Animations & Scrollbars */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<div class="pdv-container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-navy mb-1"><i class="fas fa-cash-register me-2"></i>PDV Profissional</h1>
            <p class="text-muted small">Caixa Aberto: <?= date('d/m/Y H:i') ?></p>
        </div>
        <div class="text-end">
            <span class="badge bg-success-light text-success px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> Sistema Online</span>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])) : ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> border-0 shadow-sm alert-dismissible fade show mb-4 rounded-4" role="alert">
            <i class="fas fa-info-circle me-2"></i><?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Coluna da Esquerda: Busca e Itens -->
        <div class="col-lg-7">
            <div class="card card-premium mb-4">
                <div class="card-body p-4">
                    <div class="position-relative">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0 search-pill"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="product-search" class="form-control border-start-0 search-pill" placeholder="Buscar por Nome, SKU ou Código (F2)..." autocomplete="off">
                            <button class="btn border search-pill ms-2 bg-light text-navy fw-bold px-4 hover-lift" type="button" data-bs-toggle="modal" data-bs-target="#scannerModal" title="Escanear Código (Scanner Demo)">
                                <i class="fas fa-barcode"></i>
                            </button>
                        </div>
                        <div id="search-results" class="list-group shadow position-absolute w-100 z-3 custom-scrollbar" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <div class="card card-premium overflow-hidden">
                <div class="bg-navy p-3 text-white">
                    <h6 class="fw-bold mb-0 mx-2">Carrinho de Compras</h6>
                </div>
                <div class="card-body p-0">
                    <div id="cart-items" class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">Produto</th>
                                    <th style="width: 140px;">Qtd</th>
                                    <th>Unitário</th>
                                    <th>Total</th>
                                    <th class="pe-4 text-center">Remover</th>
                                </tr>
                            </thead>
                            <tbody id="cart-table-body">
                                <tr id="cart-empty-row"><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-shopping-basket fa-3x mb-3 d-block opacity-25"></i>Seu carrinho está vazio para começar...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna da Direita: Finalização -->
        <div class="col-lg-5">
            <div class="cart-summary sticky-top" style="top: 20px;">
                <h5 class="fw-bold text-navy mb-4">Resumo do Pedido</h5>
                
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fw-medium">Subtotal:</span>
                    <span id="summary-subtotal" class="fw-bold">R$ 0,00</span>
                </div>
                <div class="d-flex justify-content-between mb-4 border-bottom pb-3">
                    <span class="text-muted fw-medium">Desconto:</span>
                    <span class="text-success fw-bold">R$ 0,00</span>
                </div>
                
                <div class="text-center my-4 py-4 bg-light rounded-4 shadow-inner">
                    <p class="text-muted small text-uppercase fw-bold mb-1">Total da Venda</p>
                    <div id="cart-total" class="total-amount">R$ 0,00</div>
                </div>

                <div class="d-grid gap-3">
                    <button id="finalize-sale-btn" class="btn btn-success btn-lg shadow-sm py-3 fw-bold rounded-pill" disabled onclick="openPaymentModal()" style="font-size:1.1rem">
                        <i class="fas fa-wallet me-2"></i> RECEBER PAGAMENTO (F9)
                    </button>
                    <button class="btn btn-light text-danger fw-bold rounded-pill" onclick="window.location.reload()"><i class="fas fa-trash-alt me-2"></i> Limpar Venda</button>
                </div>

                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex align-items-center gap-3 p-3 text-muted">
                        <i class="fas fa-user-circle fa-2x opacity-50"></i>
                        <div>
                            <p class="mb-0 fw-bold small"><?= $_SESSION['user_name'] ?? 'Operador' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================= -->
<!-- MODAL GOOGLE MATERIAL   -->
<!-- ======================= -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
      <div class="modal-header bg-navy text-white border-0 p-4">
        <h4 class="modal-title fw-bold" id="paymentModalLabel"><i class="fas fa-wallet me-2"></i> Caixa - Pagamento</h4>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="row g-0">
            <!-- Esquerda: Inserção Direta e NumPad Simulado (UI Rápida) -->
            <div class="col-md-7 p-5 bg-light d-flex flex-column justify-content-center">
                <div class="text-center mb-4">
                    <span class="text-muted d-block fw-bold text-uppercase mb-2 letter-spacing-1">Restante a Pagar</span>
                    <span class="display-3 fw-bold text-navy" id="modal-remaining-display">R$ 0,00</span>
                </div>

                <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
                    <label class="form-label fw-bold text-muted mb-3 d-block text-center">Informe o Valor e Clique na Forma de Pagamento</label>
                    <div class="input-group input-group-lg mb-4 justify-content-center">
                        <span class="input-group-text bg-light border-0 fw-bold fs-3 text-muted">R$</span>
                        <input type="number" class="form-control border-0 bg-light fs-2 fw-bold text-center" id="payment-value-input" step="0.01" min="0.01" style="max-width: 200px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <button class="btn btn-outline-success payment-fast-btn w-100 p-3 h-100 rounded-4 fw-bold shadow-sm" onclick="addPayment('dinheiro', 'Dinheiro')">
                                <i class="fas fa-money-bill-wave fa-2x d-block mb-2"></i> DINHEIRO
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-primary payment-fast-btn w-100 p-3 h-100 rounded-4 fw-bold shadow-sm" onclick="addPayment('pix', 'PIX')">
                                <i class="fab fa-pix fa-2x d-block mb-2"></i> PIX
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-info payment-fast-btn w-100 p-3 h-100 rounded-4 fw-bold shadow-sm" style="color: #0d6efd;" onclick="addPayment('cartao_debito', 'Débito')">
                                <i class="fas fa-credit-card fa-2x d-block mb-2"></i> DÉBITO
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-warning payment-fast-btn w-100 p-3 h-100 rounded-4 fw-bold shadow-sm text-dark border-warning" onclick="addPayment('cartao_credito', 'Crédito')">
                                <i class="fas fa-credit-card fa-2x d-block mb-2"></i> CRÉDITO
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Direita: Visão Geral e Confirmação -->
            <div class="col-md-5 p-5 bg-white border-start d-flex flex-column">
                <div class="bg-navy p-3 rounded-4 text-white text-center mb-4 shadow-sm">
                    <span class="small opacity-75 d-block text-uppercase fw-bold">Total da Venda</span>
                    <span class="h3 fw-bold mb-0" id="modal-total-sale">R$ 0,00</span>
                </div>

                <div class="flex-grow-1 overflow-auto custom-scrollbar pe-2 mb-4" style="max-height: 250px;">
                    <h6 class="fw-bold text-muted mb-3 d-flex justify-content-between">
                        <span>Lançamentos</span>
                        <span class="badge bg-light text-navy border" id="payment-count">0</span>
                    </h6>
                    <ul class="list-group list-group-flush border rounded-4 overflow-hidden shadow-sm" id="payments-list">
                        <li class="list-group-item text-center text-muted py-4" id="no-payments-msg">
                            <i class="fas fa-receipt fa-2x opacity-25 mb-2 d-block"></i>
                            Aguardando pagamentos...
                        </li>
                    </ul>
                </div>

                <div class="mt-auto">
                    <div class="d-flex justify-content-between mb-3 px-2">
                        <span class="text-muted fw-bold">Total Recebido</span>
                        <span class="fw-bold text-success fs-5" id="modal-total-paid">R$ 0,00</span>
                    </div>
                    
                    <div id="change-container" class="p-3 rounded-4 bg-success text-white shadow-sm d-none mt-2 transition-all text-center mb-4">
                        <span class="fw-bold d-block mb-1"><i class="fas fa-hand-holding-usd me-1"></i> Troco a Devolver</span>
                        <span class="display-5 fw-bold text-white mb-0" id="modal-change">R$ 0,00</span>
                    </div>

                    <button class="btn btn-success btn-lg w-100 rounded-pill shadow-lg fw-bold py-4 disabled transition-all" id="btn-confirm-sale" onclick="confirmSale()" style="font-size: 1.25rem;">
                        <i class="fas fa-check-circle me-2"></i> CONFIRMAR (Enter)
                    </button>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ======================= -->
<!-- Formulário Oculto       -->
<!-- ======================= -->
<form id="sale-form" action="pdv.php" method="POST" style="display: none;">
    <input type="hidden" name="cart_data" id="cart-data-input">
    <input type="hidden" name="payments_data" id="payments-data-input"> <!-- JSON de multiplos pagamentos -->
</form>

<!-- Modal do Scanner Antigo -->
<div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white"><h5 class="modal-title">Escanear Código de Barras</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div id="reader"></div></div>
        </div>
    </div>
</div>

<?php include_once '../includes/rodape.php'; ?>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- ESTADO DA APLICAÇÃO ---
    let cart = {};
    let totalSaleAmount = 0;
    let payments = []; // Multiple payments
    
    // UI Elements
    const searchInput = document.getElementById('product-search');
    const searchResults = document.getElementById('search-results');
    const cartTableBody = document.getElementById('cart-table-body');
    const mainFinalizeBtn = document.getElementById('finalize-sale-btn');
    const _cartTotalDisplays = [document.getElementById('summary-subtotal'), document.getElementById('cart-total')];
    
    const cartEmptyHtml = `<tr id="cart-empty-row"><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-shopping-basket fa-3x mb-3 d-block opacity-25"></i>Seu carrinho está vazio para começar...</td></tr>`;

    // Modal Instance
    const paymentModalInstance = new bootstrap.Modal(document.getElementById('paymentModal'), {keyboard: false});

    // --- MÉTODOS DO CARRINHO ---
    window.addToCart = function(product) {
        if (cart[product.id]) {
            if (cart[product.id].quantity < product.quantity) cart[product.id].quantity++;
        } else {
            if (product.quantity > 0) cart[product.id] = { ...product, quantity: 1, stock: product.quantity };
        }
        renderCart();
    }

    window.updateQuantity = function(productId, qty) {
        if (!cart[productId]) return;
        if (qty <= 0) delete cart[productId];
        else if (qty <= cart[productId].stock) cart[productId].quantity = qty;
        else cart[productId].quantity = cart[productId].stock;
        renderCart();
    }

    function renderCart() {
        cartTableBody.innerHTML = '';
        let total = 0;
        const hasItems = Object.keys(cart).length > 0;

        if (!hasItems) {
            cartTableBody.innerHTML = cartEmptyHtml;
            _cartTotalDisplays.forEach(el => el.textContent = 'R$ 0,00');
            mainFinalizeBtn.disabled = true;
        } else {
            for (const pid in cart) {
                const i = cart[pid];
                const sub = i.price * i.quantity;
                total += sub;
                const row = document.createElement('tr');
                row.className = 'fade-in';
                row.innerHTML = `
                    <td class="ps-4">
                        <div class="fw-bold text-navy">${i.name}</div>
                        <div class="text-muted small">Estoque: <span class="badge bg-light text-dark">${i.stock}</span></div>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-center bg-light border-0" value="${i.quantity}" min="1" max="${i.stock}" onchange="updateQuantity(${i.id}, this.value)">
                    </td>
                    <td>R$ ${parseFloat(i.price).toFixed(2)}</td>
                    <td class="fw-bold">R$ ${sub.toFixed(2)}</td>
                    <td class="pe-4 text-center">
                        <button class="btn btn-sm text-danger hover-danger rounded-circle p-2" onclick="updateQuantity(${i.id}, 0)"><i class="fas fa-trash-alt"></i></button>
                    </td>
                `;
                cartTableBody.appendChild(row);
            }
            _cartTotalDisplays.forEach(el => el.textContent = `R$ ${total.toFixed(2)}`);
            mainFinalizeBtn.disabled = false;
        }
        totalSaleAmount = total;
    }

    // --- MÉTODOS DE PAGAMENTO (Modal) ---
    window.openPaymentModal = function() {
        if (Object.keys(cart).length === 0) return;
        payments = [];
        document.getElementById('modal-total-sale').innerText = `R$ ${totalSaleAmount.toFixed(2)}`;
        updatePaymentUI();
        paymentModalInstance.show();
    }

    window.addPayment = function(methodVal, methodName) {
        const valInput = document.getElementById('payment-value-input');
        let val = parseFloat(valInput.value);
        if (isNaN(val) || val <= 0) {
            valInput.classList.add('is-invalid');
            setTimeout(() => valInput.classList.remove('is-invalid'), 1000);
            return;
        }
        
        payments.push({ method: methodVal, name: methodName, value: val });
        updatePaymentUI();
        
        const rem = Math.max(0, totalSaleAmount - getTotalPaid());
        valInput.value = rem > 0 ? rem.toFixed(2) : '';
        if(rem > 0) valInput.focus();
    }

    window.removePayment = function(idx) {
        payments.splice(idx, 1);
        updatePaymentUI();
    }

    function getTotalPaid() { return payments.reduce((sum, p) => sum + p.value, 0); }

    function updatePaymentUI() {
        const list = document.getElementById('payments-list');
        const totalPaid = getTotalPaid();
        list.innerHTML = '';
        
        if (payments.length === 0) {
            list.innerHTML = '<li class="list-group-item text-center text-muted py-3">Nenhum pagamento inserido.</li>';
        } else {
            payments.forEach((p, idx) => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3 fade-in';
                li.innerHTML = `<div><span class="badge bg-light text-navy border me-2">${p.name}</span><span class="fw-bold">R$ ${p.value.toFixed(2)}</span></div><button class="btn btn-link py-0 text-danger" onclick="removePayment(${idx})"><i class="fas fa-times-circle"></i></button>`;
                list.appendChild(li);
            });
        }
        
        document.getElementById('payment-count').innerText = payments.length;
        document.getElementById('modal-total-paid').innerText = `R$ ${totalPaid.toFixed(2)}`;
        
        const rem = totalSaleAmount - totalPaid;
        const confBtn = document.getElementById('btn-confirm-sale');
        
        const chaBox = document.getElementById('change-container');
        const remDisplay = document.getElementById('modal-remaining-display');

        if (rem > 0) {
            // Falta
            remDisplay.innerText = `R$ ${rem.toFixed(2)}`;
            remDisplay.className = 'display-3 fw-bold text-warning';
            
            chaBox.classList.add('d-none');
            confBtn.classList.add('disabled');
        } else if (rem < 0) {
            // Troco
            remDisplay.innerText = `R$ 0,00`;
            remDisplay.className = 'display-3 fw-bold text-success opacity-50';
            
            chaBox.classList.remove('d-none');
            document.getElementById('modal-change').innerText = `R$ ${Math.abs(rem).toFixed(2)}`;
            confBtn.classList.remove('disabled');
        } else {
            // Pago exato
            remDisplay.innerText = `R$ 0,00`;
            remDisplay.className = 'display-3 fw-bold text-success';
            
            chaBox.classList.add('d-none');
            confBtn.classList.remove('disabled');
        }
    }

    window.confirmSale = function() {
        if (Object.keys(cart).length === 0) return;
        if (getTotalPaid() < totalSaleAmount - 0.01) { alert("Valor insuficiente!"); return; }
        
        // Final submit
        const postCart = Object.values(cart).map(i => ({ id: i.id, quantity: i.quantity }));
        const postPayments = payments.map(p => ({method: p.method, value: p.value}));
        
        document.getElementById('cart-data-input').value = JSON.stringify(postCart);
        document.getElementById('payments-data-input').value = JSON.stringify(postPayments);
        
        const btn = document.getElementById('btn-confirm-sale');
        btn.classList.add('disabled');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>PROCESSANDO...';
        
        document.getElementById('sale-form').submit();
    }

    // --- EVENTOS GERAIS (Busca e Atalhos) ---
    document.getElementById('paymentModal').addEventListener('shown.bs.modal', () => {
        const i = document.getElementById('payment-value-input');
        const rem = Math.max(0, totalSaleAmount - getTotalPaid());
        i.value = rem.toFixed(2);
        i.focus(); i.select();
    });

    document.addEventListener('keydown', (e) => {
        if(e.key === 'F2') { e.preventDefault(); searchInput.focus(); }
        if(e.key === 'F9') { e.preventDefault(); if(!mainFinalizeBtn.disabled) openPaymentModal(); }
        if(e.key === 'Enter') {
            const m = document.getElementById('paymentModal');
            if(m.classList.contains('show')) {
                if(document.activeElement.id === 'payment-value-input') {
                    // Default enter to Dinheiro just as a quick shortcut if they press enter in input
                    addPayment('dinheiro', 'Dinheiro');
                }
                else if(!document.getElementById('btn-confirm-sale').classList.contains('disabled')) {
                    confirmSale();
                }
            }
        }
    });

    let deb;
    searchInput.addEventListener('keyup', () => {
        clearTimeout(deb);
        deb = setTimeout(() => {
            const val = searchInput.value;
            if(val.length < 2) { searchResults.style.display = 'none'; return; }
            fetch(`../api/search_products.php?term=${encodeURIComponent(val)}&in_stock=1`)
            .then(r => r.json())
            .then(data => {
                searchResults.innerHTML = '';
                if(data && data.length) {
                    searchResults.style.display = 'block';
                    data.forEach(p => {
                        const a = document.createElement('a');
                        a.className = 'list-group-item list-group-item-action d-flex justify-content-between p-3 border-bottom';
                        a.innerHTML = `<span class="fw-bold">${p.name} <small class="text-muted d-block fw-normal mt-1">Estoque: ${p.quantity} | SKU: ${p.sku||'-'}</small></span><span class="text-success fw-bold align-self-center">R$ ${parseFloat(p.price).toFixed(2)}</span>`;
                        a.href='#';
                        a.onclick = (e) => { e.preventDefault(); addToCart(p); searchInput.value=''; searchResults.style.display='none'; }
                        searchResults.appendChild(a);
                    });
                } else {
                    searchResults.style.display = 'none';
                }
            }).catch(err => {
                console.error("Erro na busca:", err);
            });
        }, 300);
    });

    // --- LÓGICA DO SCANNER ---
    const scannerModal = new bootstrap.Modal(document.getElementById('scannerModal'));
    const html5QrCode = new Html5Qrcode("reader");
    const scannerModalEl = document.getElementById('scannerModal');
    const onScanSuccess = (decodedText, decodedResult) => {
        searchInput.value = decodedText;
        scannerModal.hide();
        searchInput.dispatchEvent(new Event('keyup'));
    };
    scannerModalEl.addEventListener('shown.bs.modal', () => {
        html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } }, onScanSuccess, (error) => {}).catch(err => console.error("Unable to start scanning.", err));
    });
    scannerModalEl.addEventListener('hide.bs.modal', () => {
        if (html5QrCode.isScanning) {
            html5QrCode.stop().catch(err => console.error("Unable to stop scanning.", err));
        }
    });

});
</script>