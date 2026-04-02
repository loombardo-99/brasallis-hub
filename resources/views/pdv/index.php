<?php
/**
 * View: pdv/index
 */
$title = "PDV — Frente de Caixa";
require BASE_PATH . '/resources/views/layouts/header.php';
?>

<div class="row g-4 h-100 pb-4">
    <!-- LEFT: Catalog & Search -->
    <div class="col-lg-8">
        <div class="card-premium border-0 h-100 p-4">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-navy p-2 rounded-3 text-white me-3">
                    <i class="fas fa-cash-register fa-lg"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-navy mb-0">Venda Direta</h4>
                    <p class="text-muted small mb-0">Escaneie ou busque itens para o carrinho.</p>
                </div>
            </div>

            <div class="input-group input-group-lg mb-4 shadow-sm rounded-3 overflow-hidden border">
                <span class="input-group-text bg-white border-0 ps-4"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="pdv-search" class="form-control border-0 py-3" placeholder="Buscar por Nome ou SKU (F2)..." autocomplete="off" autofocus>
            </div>

            <div id="pdv-results" class="row g-3 overflow-auto" style="max-height: 55vh;">
                <!-- Results grid filled via JS -->
                <div class="col-12 text-center py-5 text-muted opacity-50">
                    <i class="fas fa-barcode fa-4x mb-3"></i>
                    <p>Aguardando leitura de código ou busca...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Cart & Checkout -->
    <div class="col-lg-4">
        <div class="card-premium border-0 h-100 p-0 overflow-hidden shadow-lg d-flex flex-column" style="background: #fff;">
            <div class="bg-navy p-4 text-white">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h5 class="fw-bold mb-0">Carrinho de Compras</h5>
                    <span class="badge bg-white text-navy px-3" id="cart-qty">0 itens</span>
                </div>
                <div class="small opacity-75">Caixa aberto por: <?= $_SESSION['username'] ?? 'Operador' ?></div>
            </div>

            <div class="flex-grow-1 overflow-auto bg-light p-3" id="cart-container" style="max-height: 45vh;">
                <!-- Cart items -->
                <div class="text-center py-5 text-muted small" id="cart-empty">
                    <p>Seu carrinho está vazio.</p>
                </div>
            </div>

            <div class="p-4 bg-white border-top mt-auto">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-bold text-navy" id="cart-subtotal">R$ 0,00</span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="h4 fw-bold text-navy mb-0">Total</span>
                    <span class="h3 fw-bold text-success mb-0" id="cart-total">R$ 0,00</span>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Forma de Pagamento</label>
                    <select class="form-select form-select-premium" id="payment-method">
                        <option value="dinheiro">Dinheiro (Cash)</option>
                        <option value="pix">PIX / Transferência</option>
                        <option value="cartao_debito">Cartão de Débito</option>
                        <option value="cartao_credito">Cartão de Crédito</option>
                    </select>
                </div>

                <button class="btn btn-premium btn-success w-100 py-3 fw-bold shadow-sm" id="btn-finish" disabled onclick="finishSale()">
                    <i class="fas fa-check-circle me-2"></i>FINALIZAR VENDA (F9)
                </button>
                <div class="text-center mt-3">
                    <button class="btn btn-link text-danger btn-sm text-decoration-none" onclick="clearCart()">Cancelar (ESC)</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

document.getElementById('pdv-search').addEventListener('input', function(e) {
    const q = e.target.value;
    if (q.length < 2) return;

    fetch(`/pdv/search?q=${encodeURIComponent(q)}`)
        .then(res => res.json())
        .then(products => {
            renderResults(products);
        });
});

function renderResults(products) {
    const grid = document.getElementById('pdv-results');
    grid.innerHTML = '';
    
    if (products.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center py-5 text-muted">Nenhum produto encontrado.</div>';
        return;
    }

    products.forEach(p => {
        const div = document.createElement('div');
        div.className = 'col-md-4 col-sm-6';
        div.innerHTML = `
            <div class="card h-100 card-premium p-3 border cursor-pointer hover-navy" onclick='addToCart(${JSON.stringify(p)})'>
                <div class="fw-bold text-navy text-truncate mb-1">${p.name}</div>
                <div class="small text-muted mb-2">SKU: ${p.sku}</div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-light text-dark">Est: ${p.quantity}</span>
                    <span class="fw-bold text-success">R$ ${parseFloat(p.price).toFixed(2)}</span>
                </div>
            </div>
        `;
        grid.appendChild(div);
    });
}

function addToCart(p) {
    const exists = cart.find(i => i.id === p.id);
    if (exists) {
        exists.qty++;
    } else {
        cart.push({...p, qty: 1});
    }
    renderCart();
    document.getElementById('pdv-search').value = '';
    document.getElementById('pdv-search').focus();
}

function renderCart() {
    const container = document.getElementById('cart-container');
    const empty = document.getElementById('cart-empty');
    container.innerHTML = '';
    
    let total = 0;
    
    if (cart.length === 0) {
        container.appendChild(empty);
        document.getElementById('btn-finish').disabled = true;
    } else {
        document.getElementById('btn-finish').disabled = false;
        cart.forEach((item, idx) => {
            const sub = item.price * item.qty;
            total += sub;
            const div = document.createElement('div');
            div.className = 'bg-white p-3 rounded-3 shadow-sm mb-2 d-flex justify-content-between align-items-center';
            div.innerHTML = `
                <div>
                    <div class="fw-bold text-navy small mb-0">${item.name}</div>
                    <div class="small text-muted">${item.qty}x R$ ${parseFloat(item.price).toFixed(2)}</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-navy small">R$ ${sub.toFixed(2)}</div>
                    <button class="btn btn-link text-danger p-0 small" onclick="removeFromCart(${idx})"><i class="fas fa-times"></i></button>
                </div>
            `;
            container.appendChild(div);
        });
    }

    document.getElementById('cart-qty').innerText = `${cart.length} itens`;
    document.getElementById('cart-subtotal').innerText = `R$ ${total.toFixed(2)}`;
    document.getElementById('cart-total').innerText = `R$ ${total.toFixed(2)}`;
}

function removeFromCart(idx) {
    cart.splice(idx, 1);
    renderCart();
}

function finishSale() {
    const btn = document.getElementById('btn-finish');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';

    fetch('/pdv/sale', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            items: cart,
            payment_method: document.getElementById('payment-method').value
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Venda finalizada com sucesso!');
            cart = [];
            renderCart();
        } else {
            alert('Erro: ' + data.error);
        }
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = original;
    });
}
</script>

<style>
.hover-navy:hover { border-color: #0A2647 !important; background: #f8f9fa; }
.bg-navy { background-color: #0A2647; }
.text-navy { color: #0A2647; }
</style>

<?php require BASE_PATH . '/resources/views/layouts/footer.php'; ?>
