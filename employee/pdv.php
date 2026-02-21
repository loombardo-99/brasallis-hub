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
                $history_stmt->execute([$empresa_id, $product_id, $user_id, $quantity, $venda_id]);
            }

            // 4. Integração Fiscal Automática
            require_once __DIR__ . '/../src/Services/FiscalIntegrator.php';
            $fiscalService = new \App\Services\FiscalIntegrator($conn);
            $fiscalService->createFromSale($venda_id, $empresa_id, $user_id);

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
    #search-results { max-height: 300px; overflow-y: auto; position: absolute; background: white; width: 100%; z-index: 1000; }
    .cart-item-row:hover { background-color: #f8f9fa; }
    #reader { width: 100%; border: 2px solid #f8f9fa; border-radius: 8px; }
    .payment-method-btn { flex: 1; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">PDV - Ponto de Venda</h1>
</div>

<?php if (isset($_SESSION['message'])) : ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
<?php endif; ?>

<div class="row">
    <!-- Coluna da Esquerda: Busca e Resultados -->
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header"><h5 class="mb-0">Buscar Produto</h5></div>
            <div class="card-body">
                <div class="position-relative">
                    <div class="input-group">
                        <input type="text" id="product-search" class="form-control" placeholder="Digite o nome ou SKU...">
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#scannerModal"><i class="fas fa-barcode"></i></button>
                    </div>
                    <div id="search-results" class="list-group shadow"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coluna da Direita: Carrinho -->
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Carrinho de Venda</h5></div>
            <div class="card-body">
                <div id="cart-items" class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>Produto</th><th style="width: 120px;">Qtd.</th><th>Preço Unit.</th><th>Subtotal</th><th></th></tr>
                        </thead>
                        <tbody id="cart-table-body">
                            <tr id="cart-empty-row"><td colspan="5" class="text-center text-muted">O carrinho está vazio.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white fs-5">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>Total:</strong>
                    <strong id="cart-total">R$ 0,00</strong>
                </div>
            </div>
        </div>
        <div class="d-grid mt-3">
            <button id="finalize-sale-btn" class="btn btn-success btn-lg" disabled data-bs-toggle="modal" data-bs-target="#finalizeSaleModal">Finalizar Venda</button>
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
        } else {
            for (const productId in cart) {
                const item = cart[productId];
                const subtotal = item.price * item.quantity;
                total += subtotal;
                const row = document.createElement('tr');
                row.className = 'cart-item-row';
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td><input type="number" class="form-control form-control-sm quantity-input" value="${item.quantity}" min="1" max="${item.stock}" data-id="${item.id}"></td>
                    <td>R$ ${parseFloat(item.price).toFixed(2)}</td>
                    <td>R$ ${subtotal.toFixed(2)}</td>
                    <td><button class="btn btn-sm btn-outline-danger remove-btn" data-id="${item.id}"><i class="fas fa-times"></i></button></td>
                `;
                cartTableBody.appendChild(row);
            }
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