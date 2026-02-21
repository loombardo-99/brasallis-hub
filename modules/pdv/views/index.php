<?php
// modules/pdv/views/index.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';
require_once __DIR__ . '/../../../includes/cabecalho.php';

// Check permissions
if (!isset($_SESSION['user_id'])) { header('Location: ../../../login.php'); exit; }
if (!check_permission('pdv', 'leitura')) { header('Location: ../../../admin/painel_admin.php?error=acesso_negado'); exit; }

?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1"><i class="fas fa-cash-register me-2"></i>Frente de Caixa (PDV)</h2>
            <p class="text-muted small mb-0">Realize vendas e controle o estoque em tempo real.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                <i class="fas fa-circle me-1 small"></i> Caixa Aberto
            </span>
        </div>
    </div>

    <div class="row g-4 h-100">
        <!-- LEFT COLUMN: Product Catalog -->
        <div class="col-lg-8 d-flex flex-column h-100">
            <!-- Search Bar -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-2">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="product-search" class="form-control border-0 bg-transparent" placeholder="Buscar produto por nome ou código de barras (SKU)..." autocomplete="off" autofocus>
                    </div>
                </div>
            </div>

            <!-- Results Grid -->
            <div id="product-grid" class="row g-3 overflow-auto" style="max-height: 60vh; align-content: flex-start;">
                <div class="col-12 text-center text-muted py-5 mt-5">
                    <i class="fas fa-barcode fa-3x mb-3 opacity-25"></i>
                    <p>Digite ou escaneie um produto para começar.</p>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Cart & Checkout -->
        <div class="col-lg-4 d-flex flex-column h-100">
            <div class="card border-0 shadow-lg h-100 d-flex flex-column">
                <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Carrinho</h5>
                    <span class="badge bg-white text-navy rounded-pill" id="cart-count">0 itens</span>
                </div>

                <!-- Cart Items List -->
                <div class="card-body p-0 flex-grow-1 overflow-auto bg-light" id="cart-items" style="max-height: 50vh;">
                    <div class="text-center text-muted py-5 mt-5">
                        <p class="small">Carrinho vazio.</p>
                    </div>
                </div>

                <!-- Summary & Actions -->
                <div class="card-footer bg-white p-4 border-top">
                    <!-- Subtotals -->
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Subtotal:</span>
                        <span id="cart-subtotal">R$ 0,00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 align-items-center">
                        <span class="h4 fw-bold text-navy mb-0">Total:</span>
                        <span class="h3 fw-bold text-success mb-0" id="cart-total">R$ 0,00</span>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Forma de Pagamento</label>
                        <select class="form-select" id="payment-method">
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">PIX</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                            <option value="cartao_debito">Cartão de Débito</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-lg fw-bold py-3" onclick="finalizeSale()" id="btn-checkout" disabled>
                            <i class="fas fa-check-circle me-2"></i>FINALIZAR VENDA (F9)
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="clearCart()">
                            Cancelar Venda (Esc)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-navy { background-color: #0A2647; }
    .text-navy { color: #0A2647; }
    .product-card { transition: all 0.2s; cursor: pointer; border: 1px solid transparent; }
    .product-card:hover { transform: translateY(-2px); border-color: #0A2647; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .cart-item { transition: background 0.2s; }
    .cart-item:hover { background-color: #f8f9fa; }
</style>

<script>
let cart = [];

// Focus search on load
document.addEventListener('DOMContentLoaded', () => document.getElementById('product-search').focus());

// Search Logic
document.getElementById('product-search').addEventListener('input', function(e) {
    const query = e.target.value;
    if (query.length < 2) {
        document.getElementById('product-grid').innerHTML = '<div class="col-12 text-center text-muted py-5 mt-5"><i class="fas fa-barcode fa-3x mb-3 opacity-25"></i><p>Digite ou escaneie um produto para começar.</p></div>';
        return;
    }

    fetch(`../api/search_products.php?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(products => {
            const grid = document.getElementById('product-grid');
            grid.innerHTML = '';
            
            if (products.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center text-muted py-5">Nenhum produto encontrado.</div>';
                return;
            }

            // Auto-add if exact match SKU
            if (products.length === 1 && products[0].sku === query) {
                addToCart(products[0]);
                e.target.value = ''; // Clear input for next scan
                return;
            }

            products.forEach(p => {
                const col = document.createElement('div');
                col.className = 'col-md-4 col-sm-6';
                col.innerHTML = `
                    <div class="card h-100 product-card shadow-sm border-0" onclick='addToCart(${JSON.stringify(p)})'>
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-navy mb-1 text-truncate">${p.name}</h6>
                            <small class="text-muted d-block mb-2">SKU: ${p.sku}</small>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="badge bg-light text-dark border">Est: ${p.quantity}</span>
                                <span class="fw-bold text-success">R$ ${parseFloat(p.price).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                `;
                grid.appendChild(col);
            });
        });
});

function addToCart(product) {
    const existing = cart.find(item => item.id === product.id);
    if (existing) {
        if (existing.qty + 1 > product.quantity) {
            alert('Estoque insuficiente!');
            return;
        }
        existing.qty++;
    } else {
        if (product.quantity < 1) {
            alert('Produto sem estoque!');
            return;
        }
        cart.push({...product, qty: 1});
    }
    updateCartUI();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartUI();
}

function updateCartUI() {
    const container = document.getElementById('cart-items');
    container.innerHTML = '';
    
    let total = 0;
    
    if (cart.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-5 mt-5"><p class="small">Carrinho vazio.</p></div>';
        document.getElementById('btn-checkout').disabled = true;
    } else {
        document.getElementById('btn-checkout').disabled = false;
        cart.forEach((item, index) => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            
            const div = document.createElement('div');
            div.className = 'cart-item p-3 border-bottom d-flex justify-content-between align-items-center';
            div.innerHTML = `
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold text-navy text-truncate" style="max-width: 180px;">${item.name}</span>
                        <span class="fw-bold">R$ ${itemTotal.toFixed(2)}</span>
                    </div>
                    <div class="d-flex align-items-center mt-1">
                        <small class="text-muted me-2">${item.qty} x R$ ${parseFloat(item.price).toFixed(2)}</small>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary py-0" onclick="changeQty(${index}, -1)">-</button>
                            <button class="btn btn-outline-secondary py-0" onclick="changeQty(${index}, 1)">+</button>
                        </div>
                        <button class="btn btn-link text-danger p-0 ms-auto" onclick="removeFromCart(${index})"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
            `;
            container.appendChild(div);
        });
    }

    document.getElementById('cart-count').innerText = `${cart.length} itens`;
    document.getElementById('cart-subtotal').innerText = `R$ ${total.toFixed(2)}`;
    document.getElementById('cart-total').innerText = `R$ ${total.toFixed(2)}`;
}

function changeQty(index, delta) {
    const item = cart[index];
    if (item.qty + delta > 0) {
        // Stock Check logic could be added here if we persist product data better
        item.qty += delta;
    } else {
        removeFromCart(index);
    }
    updateCartUI();
}

function clearCart() {
    if(confirm('Tem certeza que deseja cancelar a venda?')) {
        cart = [];
        updateCartUI();
    }
}

function finalizeSale() {
    if (cart.length === 0) return;
    
    const method = document.getElementById('payment-method').value;
    const btn = document.getElementById('btn-checkout');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';

    fetch('../api/process_sale.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            items: cart,
            payment_method: method
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // alert('Venda realizada com sucesso!'); // Replaced with nicer flow
            
            if(confirm('Venda realizada! Deseja imprimir a nota?')) {
                window.open('../views/invoice_view.php?id=' + data.venda_id, '_blank', 'width=400,height=600');
            }
            
            cart = [];
            updateCartUI();
            document.getElementById('product-search').focus();
        } else {
            alert('Erro ao finalizar: ' + data.error);
        }
    })
    .catch(err => alert('Erro de conexão.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Shortcuts
document.addEventListener('keydown', (e) => {
    if (e.key === 'F9') { e.preventDefault(); finalizeSale(); }
    if (e.key === 'Escape') { e.preventDefault(); clearCart(); }
});
</script>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
