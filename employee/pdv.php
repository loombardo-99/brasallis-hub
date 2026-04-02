<?php
// A lógica de processamento de formulário DEVE vir antes de qualquer output HTML.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/funcoes.php';

// Lógica de processamento da venda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
    $conn = connect_db();
    $empresa_id = $_SESSION['empresa_id'];
    $user_id = $_SESSION['user_id'];
    $cart_data = json_decode($_POST['cart_data'], true);

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

            // 2. Inserir na tabela 'vendas'
            $payment_method = $_POST['payment_method'] ?? 'dinheiro'; // Pega o método de pagamento do POST
            $venda_stmt = $conn->prepare("INSERT INTO vendas (empresa_id, user_id, total_amount, payment_method) VALUES (?, ?, ?, ?)");
            $venda_stmt->execute([$empresa_id, $user_id, $total_amount, $payment_method]);
            $venda_id = $conn->lastInsertId();

            // 3. Inserir itens, atualizar estoque e registrar histórico
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

            // 4. Integração Fiscal Automática
            require_once __DIR__ . '/../src/Services/FiscalIntegrator.php';
            $fiscalService = new \App\Services\FiscalIntegrator($conn);
            $fiscalService->createFromSale($venda_id, $empresa_id, $user_id);

            // 5. Integração com Financeiro (Contas a Receber)
            $statusReceber = ($payment_method === 'dinheiro' || $payment_method === 'pix' || $payment_method === 'debito') ? 'recebido' : 'pendente';
            $dataRecebimento = ($statusReceber === 'recebido') ? 'CURDATE()' : 'NULL';
            
            $sqlFin = "INSERT INTO contas_receber (empresa_id, descricao, valor, data_vencimento, data_recebimento, status, venda_id) 
                       VALUES (?, ?, ?, CURDATE(), $dataRecebimento, ?, ?)";
            $stmtFin = $conn->prepare($sqlFin);
            $stmtFin->execute([$empresa_id, "Venda PDV #$venda_id", $total_amount, $statusReceber, $venda_id]);

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

    .btn-trust {
        background-color: var(--trust-navy);
        color: white;
        border-radius: 12px;
        font-weight: 600;
        padding: 12px 24px;
        transition: all 0.3s ease;
    }

    .btn-trust:hover {
        background-color: var(--trust-blue);
        color: white;
        transform: translateY(-2px);
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

    .payment-method-btn {
        border-radius: 12px;
        padding: 15px;
        border: 2px solid #eef2f7;
        transition: all 0.2s;
        flex: 1;
        text-align: center;
        background: white;
    }

    .payment-method-btn.active {
        border-color: var(--trust-navy);
        background-color: rgba(10, 38, 71, 0.05);
        font-weight: bold;
    }
</style>

<div class="pdv-container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-navy mb-1"><i class="fas fa-cash-register me-2"></i>PDV Profissional</h1>
            <p class="text-muted small">Caixa Aberto: <?= date('d/m/Y H:i') ?></p>
        </div>
        <div class="text-end">
            <span class="badge bg-success-light text-success px-3 py-2 rounded-pill">Sistema Online</span>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])) : ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-info-circle me-2"></i><?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Coluna da Esquerda: Busca -->
        <div class="col-lg-7">
            <div class="card card-premium mb-4">
                <div class="card-body p-4">
                    <div class="position-relative">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0 search-pill"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="product-search" class="form-control border-start-0 search-pill" placeholder="Buscar por Nome, SKU ou Código de Barras...">
                            <button class="btn btn-outline-navy ms-2 search-pill px-4" type="button" data-bs-toggle="modal" data-bs-target="#scannerModal"><i class="fas fa-expand"></i></button>
                        </div>
                        <div id="search-results" class="list-group shadow position-absolute w-100 z-3" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <div class="card card-premium">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-navy mb-0">Itens no Carrinho</h5>
                </div>
                <div class="card-body p-0">
                    <div id="cart-items" class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">Produto</th>
                                    <th style="width: 140px;">Quantidade</th>
                                    <th>Unitário</th>
                                    <th>Total</th>
                                    <th class="pe-4 text-center">Ações</th>
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
                    <span class="text-muted">Subtotal:</span>
                    <span id="summary-subtotal" class="fw-bold">R$ 0,00</span>
                </div>
                <div class="d-flex justify-content-between mb-4 border-bottom pb-3">
                    <span class="text-muted">Desconto:</span>
                    <span class="text-success fw-bold">R$ 0,00</span>
                </div>
                
                <div class="text-center my-4 py-4 bg-light rounded-4">
                    <p class="text-muted small text-uppercase fw-bold mb-1">Total a Pagar</p>
                    <div id="cart-total" class="total-amount">R$ 0,00</div>
                </div>

                <div class="d-grid gap-3">
                    <button id="finalize-sale-btn" class="btn btn-trust btn-lg shadow-sm" disabled data-bs-toggle="modal" data-bs-target="#finalizeSaleModal">
                        FECHAR PEDIDO (F2)
                    </button>
                    <button class="btn btn-outline-danger border-0 small" onclick="window.location.reload()"><i class="fas fa-trash me-2"></i>Limpar Carrinho</button>
                </div>

                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                        <div class="bg-white p-2 rounded-circle shadow-sm"><i class="fas fa-user-tie text-navy"></i></div>
                        <div>
                            <p class="mb-0 small text-muted">Vendedor</p>
                            <p class="mb-0 fw-bold small"><?= $_SESSION['user_name'] ?? 'Caixa Principal' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulário oculto para submeter a venda -->
<form id="sale-form" action="pdv.php" method="POST" style="display: none;">
    <input type="hidden" name="cart_data" id="cart-data-input">
    <input type="hidden" name="payment_method" id="payment-method-input">
</form>

<!-- Modal de Finalização de Venda -->
<div class="modal fade" id="finalizeSaleModal" tabindex="-1" aria-labelledby="finalizeSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finalizeSaleModalLabel">Finalizar Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <h6 class="text-muted">VALOR TOTAL</h6>
                    <h2 id="modal-total-amount" class="fw-bold">R$ 0,00</h2>
                </div>
                <div class="mb-3">
                    <label class="form-label">Forma de Pagamento</label>
                    <div id="payment-method-options" class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary payment-method-btn active" data-method="dinheiro">Dinheiro</button>
                        <button type="button" class="btn btn-outline-primary payment-method-btn" data-method="credito">Crédito</button>
                        <button type="button" class="btn btn-outline-primary payment-method-btn" data-method="debito">Débito</button>
                        <button type="button" class="btn btn-outline-primary payment-method-btn" data-method="pix">Pix</button>
                    </div>
                </div>
                <div id="cash-payment-fields">
                    <div class="mb-3">
                        <label for="amount-received" class="form-label">Valor Recebido</label>
                        <input type="number" step="0.01" class="form-control" id="amount-received" placeholder="0,00">
                    </div>
                    <div class="text-center">
                        <h6 class="text-muted">TROCO</h6>
                        <h3 id="change-amount" class="fw-bold">R$ 0,00</h3>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirm-sale-btn" class="btn btn-primary">Confirmar Venda</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal do Scanner -->
<div class="modal fade" id="scannerModal" tabindex="-1" aria-labelledby="scannerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="scannerModalLabel">Escanear Código de Barras</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><div id="reader"></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Fechar</button></div>
        </div>
    </div>
</div>

<?php include_once '../includes/rodape.php'; ?>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- SELETORES DE ELEMENTOS ---
    const searchInput = document.getElementById('product-search');
    const searchResults = document.getElementById('search-results');
    const cartTableBody = document.getElementById('cart-table-body');
    const cartEmptyRow = document.getElementById('cart-empty-row');
    const cartTotalEl = document.getElementById('cart-total');
    const mainFinalizeBtn = document.getElementById('finalize-sale-btn');
    
    // Formulário oculto
    const saleForm = document.getElementById('sale-form');
    const cartDataInput = document.getElementById('cart-data-input');
    const paymentMethodInput = document.getElementById('payment-method-input');

    // Modal de Finalização
    const finalizeSaleModal = new bootstrap.Modal(document.getElementById('finalizeSaleModal'));
    const modalTotalAmount = document.getElementById('modal-total-amount');
    const paymentMethodOptions = document.getElementById('payment-method-options');
    const cashPaymentFields = document.getElementById('cash-payment-fields');
    const amountReceivedInput = document.getElementById('amount-received');
    const changeAmountEl = document.getElementById('change-amount');
    const confirmSaleBtn = document.getElementById('confirm-sale-btn');

    // Modal do Scanner
    const scannerModal = new bootstrap.Modal(document.getElementById('scannerModal'));

    // --- ESTADO DA APLICAÇÃO ---
    let cart = {};
    let currentTotal = 0;
    let selectedPaymentMethod = 'dinheiro';

    // --- FUNÇÕES DO CARRINHO ---
    function addToCart(product) {
        if (cart[product.id]) {
            if (cart[product.id].quantity < product.quantity) {
                 cart[product.id].quantity++;
            }
        } else {
            if (product.quantity > 0) {
                cart[product.id] = { id: product.id, name: product.name, price: product.price, stock: product.quantity, quantity: 1 };
            }
        }
        renderCart();
    }

    function updateQuantity(productId, newQuantity) {
        const item = cart[productId];
        if (!item) return;
        if (newQuantity <= 0) {
            delete cart[productId];
        } else if (newQuantity <= item.stock) {
            item.quantity = newQuantity;
        } else {
            item.quantity = item.stock;
        }
        renderCart();
    }

    function renderCart() {
        cartTableBody.innerHTML = '';
        let total = 0;
        const hasItems = Object.keys(cart).length > 0;

        if (!hasItems) {
            cartTableBody.appendChild(cartEmptyRow);
            document.getElementById('summary-subtotal').textContent = 'R$ 0,00';
        } else {
            for (const productId in cart) {
                const item = cart[productId];
                const subtotal = item.price * item.quantity;
                total += subtotal;
                const row = document.createElement('tr');
                row.className = 'cart-item-row';
                row.innerHTML = `
                    <td class="ps-4">
                        <div class="fw-bold">${item.name}</div>
                        <div class="text-muted extra-small">ID: ${item.id} | Estoque: ${item.stock}</div>
                    </td>
                    <td>
                        <div class="input-group input-group-sm" style="width: 100px;">
                            <input type="number" class="form-control text-center quantity-input border-0 bg-light" value="${item.quantity}" min="1" max="${item.stock}" data-id="${item.id}">
                        </div>
                    </td>
                    <td>R$ ${parseFloat(item.price).toFixed(2)}</td>
                    <td class="fw-bold">R$ ${subtotal.toFixed(2)}</td>
                    <td class="pe-4 text-center">
                        <button class="btn btn-sm btn-link text-danger remove-btn" data-id="${item.id}"><i class="fas fa-trash-alt"></i></button>
                    </td>
                `;
                cartTableBody.appendChild(row);
            }
            document.getElementById('summary-subtotal').textContent = `R$ ${total.toFixed(2)}`;
        }
        currentTotal = total;
        cartTotalEl.textContent = `R$ ${total.toFixed(2)}`;
        mainFinalizeBtn.disabled = !hasItems;
    }

    // --- LÓGICA DO MODAL DE FINALIZAÇÃO ---
    mainFinalizeBtn.addEventListener('click', () => {
        modalTotalAmount.textContent = `R$ ${currentTotal.toFixed(2)}`;
        amountReceivedInput.value = '';
        changeAmountEl.textContent = 'R$ 0,00';
        // O modal é aberto via atributos data-bs-* no botão
    });

    paymentMethodOptions.addEventListener('click', (e) => {
        const target = e.target.closest('.payment-method-btn');
        if (!target) return;

        // Atualiza o estado visual dos botões
        paymentMethodOptions.querySelectorAll('.payment-method-btn').forEach(btn => btn.classList.remove('active'));
        target.classList.add('active');

        selectedPaymentMethod = target.getAttribute('data-method');
        cashPaymentFields.style.display = selectedPaymentMethod === 'dinheiro' ? 'block' : 'none';
        calculateChange();
    });

    function calculateChange() {
        const amountReceived = parseFloat(amountReceivedInput.value) || 0;
        if (selectedPaymentMethod === 'dinheiro' && amountReceived > 0) {
            const change = amountReceived - currentTotal;
            changeAmountEl.textContent = `R$ ${Math.max(0, change).toFixed(2)}`;
        } else {
            changeAmountEl.textContent = 'R$ 0,00';
        }
    }
    amountReceivedInput.addEventListener('input', calculateChange);

    confirmSaleBtn.addEventListener('click', () => {
        const cartForPost = Object.values(cart).map(item => ({ id: item.id, quantity: item.quantity }));
        if(cartForPost.length === 0) {
            alert('O carrinho está vazio.');
            return;
        }
        cartDataInput.value = JSON.stringify(cartForPost);
        paymentMethodInput.value = selectedPaymentMethod;
        saleForm.submit();
    });

    // --- LÓGICA DE BUSCA E EVENTOS DO CARRINHO ---
    cartTableBody.addEventListener('click', (e) => {
        if (e.target.closest('button.remove-btn')) {
            updateQuantity(e.target.closest('button.remove-btn').getAttribute('data-id'), 0);
        }
    });
    cartTableBody.addEventListener('change', (e) => {
        if (e.target.closest('input.quantity-input')) {
            const input = e.target.closest('input.quantity-input');
            updateQuantity(input.getAttribute('data-id'), parseInt(input.value, 10));
        }
    });

    let debounceTimer;
    searchInput.addEventListener('keyup', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const query = searchInput.value;
            if (query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }
            fetch(`../api/search_products.php?term=${encodeURIComponent(query)}&in_stock=1`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        searchResults.style.display = 'block';
                        data.forEach(product => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'list-group-item list-group-item-action';
                            item.innerHTML = `<strong>${product.name}</strong> <small class="text-muted">(Estoque: ${product.quantity})</small>`;
                            item.addEventListener('click', (e) => {
                                e.preventDefault();
                                addToCart(product);
                                searchInput.value = '';
                                searchResults.style.display = 'none';
                            });
                            searchResults.appendChild(item);
                        });
                    } else {
                        searchResults.style.display = 'none';
                    }
                });
        }, 300);
    });

    // --- LÓGICA DO SCANNER (sem alterações) ---
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