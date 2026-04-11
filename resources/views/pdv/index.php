<?php
/**
 * View: pdv/index
 */
$title = "PDV — Frente de Caixa";
require BASE_PATH . '/resources/views/layouts/header.php';
?>

<div class="row g-4 h-100 pb-4 pdv-container">
    <!-- LEFT: Catalog & Search -->
    <div class="col-lg-8">
        <div class="card card-premium border-0 h-100 p-4 shadow-sm" style="border-radius: 16px;">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-navy p-3 rounded-circle text-white me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                    <i class="fas fa-cash-register fa-lg"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-navy mb-0" style="letter-spacing: -0.5px;">Frente de Caixa</h4>
                    <p class="text-muted small mb-0">Escaneie ou busque itens para adicionar ao carrinho.</p>
                </div>
            </div>

            <div class="input-group input-group-lg mb-4 shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #e0e0e0;">
                <span class="input-group-text bg-white border-0 ps-4"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="pdv-search" class="form-control border-0 py-3" placeholder="Buscar produto por Nome ou SKU (F2)..." autocomplete="off" autofocus style="box-shadow: none;">
            </div>

            <div id="pdv-results" class="row g-3 overflow-auto custom-scrollbar" style="max-height: 55vh; align-content: flex-start;">
                <!-- Results grid filled via JS -->
                <div class="col-12 d-flex flex-column align-items-center justify-content-center py-5 text-muted opacity-50" style="height: 100%;">
                    <i class="fas fa-barcode fa-4x mb-3"></i>
                    <p class="mb-0">Aguardando leitura de código ou busca...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Cart & Checkout -->
    <div class="col-lg-4">
        <div class="card card-premium border-0 h-100 p-0 overflow-hidden shadow-lg d-flex flex-column" style="border-radius: 16px; background: #fff;">
            <div class="bg-navy p-4 text-white" style="border-radius: 16px 16px 0 0;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h5 class="fw-bold mb-0" style="letter-spacing: -0.5px;">Carrinho</h5>
                    <span class="badge bg-white text-navy px-3 py-2 rounded-pill shadow-sm" id="cart-qty" style="font-size: 0.85rem;">0 itens</span>
                </div>
                <div class="small opacity-75 d-flex align-items-center mt-2">
                    <i class="fas fa-user-circle me-1"></i> Operador: <?= htmlspecialchars($_SESSION['username'] ?? 'Operador') ?>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto bg-light p-3 custom-scrollbar" id="cart-container" style="max-height: 45vh;">
                <!-- Cart items -->
                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted small" id="cart-empty">
                    <i class="fas fa-shopping-basket fa-3x mb-3 opacity-25"></i>
                    <p>Seu carrinho está vazio.</p>
                </div>
            </div>

            <div class="p-4 bg-white border-top mt-auto" style="border-radius: 0 0 16px 16px;">
                <div class="d-flex justify-content-between mb-2 align-items-center">
                    <span class="text-muted fw-medium">Subtotal</span>
                    <span class="fw-bold text-navy" id="cart-subtotal">R$ 0,00</span>
                </div>
                <div class="d-flex justify-content-between mb-4 align-items-center">
                    <span class="h4 fw-bold text-navy mb-0">Total</span>
                    <span class="h3 fw-bold text-success mb-0" id="cart-total">R$ 0,00</span>
                </div>

                <button class="btn btn-premium btn-success w-100 py-3 fw-bold shadow-sm rounded-pill d-flex align-items-center justify-content-center transition-all bg-gradient-success" id="btn-open-payment" disabled onclick="openPaymentModal()" style="font-size: 1.1rem; letter-spacing: 0.5px;">
                    <i class="fas fa-wallet me-2"></i> IR PARA PAGAMENTO (F9)
                </button>
                <div class="text-center mt-3">
                    <button class="btn btn-link text-danger btn-sm text-decoration-none fw-medium" onclick="clearCart()">Cancelar Venda (ESC)</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PAYMENT MODAL (Material Design Inspired) -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
      <div class="modal-header bg-navy text-white border-0 p-4">
        <h5 class="modal-title fw-bold" id="paymentModalLabel"><i class="fas fa-cash-register me-2"></i> Finalizar Pagamento</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="row g-0">
            <!-- Resumo e Inserção de Pagamentos -->
            <div class="col-md-7 p-4 bg-light">
                <div class="bg-white p-3 rounded-4 shadow-sm border mb-4 text-center">
                    <span class="text-muted d-block small fw-bold text-uppercase mb-1">Total da Venda</span>
                    <span class="h2 fw-bold text-navy mb-0" id="modal-total-sale">R$ 0,00</span>
                </div>

                <h6 class="fw-bold text-navy mb-3">Adicionar Pagamento</h6>
                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label small fw-bold text-muted">Método</label>
                        <select class="form-select form-select-lg shadow-sm border-0" id="payment-method-select" style="border-radius: 10px;">
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">PIX</option>
                            <option value="cartao_debito">Cartão de Débito</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-bold text-muted">Valor (R$)</label>
                        <div class="input-group shadow-sm" style="border-radius: 10px;">
                            <input type="number" class="form-control form-control-lg border-0" id="payment-value-input" step="0.01" min="0.01" style="border-radius: 10px 0 0 10px;">
                            <button class="btn btn-navy text-white border-0 fw-bold px-3" type="button" onclick="addPayment()" style="border-radius: 0 10px 10px 0;">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lista de pagamentos adicionados -->
                <div class="mt-4">
                    <h6 class="fw-bold text-navy mb-2 d-flex justify-content-between">
                        <span>Valores Pagos</span>
                        <span class="badge bg-navy rounded-pill" id="payment-count">0</span>
                    </h6>
                    <ul class="list-group list-group-flush border rounded-4 overflow-hidden shadow-sm" id="payments-list">
                        <li class="list-group-item text-center text-muted small py-3" id="no-payments-msg">Nenhum pagamento registrado.</li>
                    </ul>
                </div>
            </div>

            <!-- Totais e Ação Final -->
            <div class="col-md-5 p-4 bg-white border-start d-flex flex-column justify-content-center">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted fw-medium">Total Pago</span>
                        <span class="fw-bold text-success fs-5" id="modal-total-paid">R$ 0,00</span>
                    </div>
                    <hr class="my-3 opacity-25">
                    
                    <!-- Dinâmico: Falta ou Troco -->
                    <div id="remaining-container" class="p-3 rounded-4 bg-light border border-warning">
                        <span class="text-warning-emphasis fw-bold d-block mb-1"><i class="fas fa-exclamation-circle me-1"></i> Falta Pagar</span>
                        <span class="h3 fw-bold text-warning-emphasis mb-0" id="modal-remaining">R$ 0,00</span>
                    </div>

                    <div id="change-container" class="p-3 rounded-4 bg-success text-white shadow-sm d-none mt-2 transition-all">
                        <span class="fw-bold d-block mb-1"><i class="fas fa-hand-holding-usd me-1"></i> Troco</span>
                        <span class="h3 fw-bold text-white mb-0" id="modal-change">R$ 0,00</span>
                    </div>
                </div>

                <button class="btn btn-success btn-lg w-100 rounded-pill shadow fw-bold mt-auto py-3 disabled transition-all" id="btn-confirm-sale" onclick="confirmSale()">
                    <i class="fas fa-check-circle me-2"></i> CONFIRMAR (Enter)
                </button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let cart = [];
let totalSaleAmount = 0;
let payments = []; // Array of {method, value}
let paymentModalInstance;

document.addEventListener('DOMContentLoaded', () => {
    paymentModalInstance = new bootstrap.Modal(document.getElementById('paymentModal'), {
        keyboard: false
    });
    
    // Key bindings
    document.addEventListener('keydown', (e) => {
        if (e.key === 'F2') {
            e.preventDefault();
            document.getElementById('pdv-search').focus();
        }
        if (e.key === 'F9') {
            e.preventDefault();
            if(!document.getElementById('btn-open-payment').disabled) {
                openPaymentModal();
            }
        }
        if (e.key === 'Escape') {
            const modalEl = document.getElementById('paymentModal');
            if(modalEl.classList.contains('show')) {
                paymentModalInstance.hide();
            } else {
                clearCart();
            }
        }
        if (e.key === 'Enter') {
            const modalEl = document.getElementById('paymentModal');
            if(modalEl.classList.contains('show')) {
                // If focus is on input, add payment, else try to confirm
                if (document.activeElement.id === 'payment-value-input') {
                    addPayment();
                } else if (!document.getElementById('btn-confirm-sale').classList.contains('disabled')) {
                    confirmSale();
                }
            }
        }
    });

    // Handle payment modal open logic
    document.getElementById('paymentModal').addEventListener('shown.bs.modal', function () {
        const remaining = Math.max(0, totalSaleAmount - getTotalPaid());
        document.getElementById('payment-value-input').value = remaining.toFixed(2);
        document.getElementById('payment-value-input').focus();
        document.getElementById('payment-value-input').select();
    });
});

document.getElementById('pdv-search').addEventListener('input', function(e) {
    const q = e.target.value;
    if (q.length < 2) return;

    fetch(`/pdv/search?q=${encodeURIComponent(q)}`)
        .then(res => res.json())
        .then(products => {
            renderResults(products);
        })
        .catch(err => console.error(err));
});

function renderResults(products) {
    const grid = document.getElementById('pdv-results');
    grid.innerHTML = '';
    
    if (!products || products.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center py-5 text-muted"><p>Nenhum produto encontrado com estoque.</p></div>';
        return;
    }

    products.forEach(p => {
        const div = document.createElement('div');
        div.className = 'col-md-4 col-sm-6 fade-in';
        div.innerHTML = `
            <div class="card h-100 card-premium border shadow-sm cursor-pointer hover-lift" onclick='addToCart(${JSON.stringify(p)})' style="border-radius: 12px; transition: all 0.2s;">
                <div class="card-body p-3 d-flex flex-column h-100">
                    <div class="fw-bold text-navy text-truncate mb-1" title="${p.name}">${p.name}</div>
                    <div class="small text-muted mb-auto">SKU: ${p.sku}</div>
                    <div class="d-flex justify-content-between align-items-end mt-3">
                        <span class="badge bg-light text-navy border">Est: ${p.quantity}</span>
                        <span class="fw-bold text-success fs-6">R$ ${parseFloat(p.price).toFixed(2)}</span>
                    </div>
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
    
    const searchInput = document.getElementById('pdv-search');
    searchInput.value = '';
    searchInput.focus();
    document.getElementById('pdv-results').innerHTML = `
        <div class="col-12 d-flex flex-column align-items-center justify-content-center py-5 text-muted opacity-50" style="height: 100%;">
            <i class="fas fa-barcode fa-4x mb-3"></i>
            <p class="mb-0">Aguardando leitura de código ou busca...</p>
        </div>
    `;
}

function renderCart() {
    const container = document.getElementById('cart-container');
    container.innerHTML = '';
    
    let total = 0;
    
    if (cart.length === 0) {
        container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted small" id="cart-empty">
                <i class="fas fa-shopping-basket fa-3x mb-3 opacity-25"></i>
                <p>Seu carrinho está vazio.</p>
            </div>
        `;
        document.getElementById('btn-open-payment').disabled = true;
    } else {
        document.getElementById('btn-open-payment').disabled = false;
        cart.forEach((item, idx) => {
            const sub = item.price * item.qty;
            total += sub;
            const div = document.createElement('div');
            div.className = 'bg-white p-3 rounded-4 shadow-sm border mb-2 d-flex justify-content-between align-items-center slide-in';
            div.innerHTML = `
                <div class="d-flex align-items-center">
                    <span class="badge bg-light text-navy me-3 fs-6 rounded-3 px-2 py-1">${item.qty}x</span>
                    <div>
                        <div class="fw-bold text-navy small mb-0">${item.name}</div>
                        <div class="small text-muted">R$ ${parseFloat(item.price).toFixed(2)} un</div>
                    </div>
                </div>
                <div class="text-end d-flex align-items-center">
                    <div class="fw-bold text-success me-3">R$ ${sub.toFixed(2)}</div>
                    <button class="btn btn-light text-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center hover-danger" style="width: 28px; height: 28px;" onclick="removeFromCart(${idx})">
                        <i class="fas fa-trash-alt small"></i>
                    </button>
                </div>
            `;
            container.appendChild(div);
        });
    }

    totalSaleAmount = total;
    document.getElementById('cart-qty').innerText = `${cart.length} itens`;
    document.getElementById('cart-subtotal').innerText = `R$ ${total.toFixed(2)}`;
    document.getElementById('cart-total').innerText = `R$ ${total.toFixed(2)}`;
}

function removeFromCart(idx) {
    cart.splice(idx, 1);
    renderCart();
}

function clearCart() {
    if(cart.length > 0) {
        if(confirm("Deseja realmente cancelar esta venda?")) {
            cart = [];
            renderCart();
        }
    }
}

// === MULTIPLE PAYMENTS LOGIC ===

function openPaymentModal() {
    if (cart.length === 0) return;
    
    // Reset payments state
    payments = [];
    document.getElementById('modal-total-sale').innerText = `R$ ${totalSaleAmount.toFixed(2)}`;
    updatePaymentUI();
    
    paymentModalInstance.show();
}

function addPayment() {
    const valueInput = document.getElementById('payment-value-input');
    let val = parseFloat(valueInput.value);
    
    if (isNaN(val) || val <= 0) {
        alert("Digite um valor válido para o pagamento.");
        valueInput.focus();
        return;
    }
    
    const methodEl = document.getElementById('payment-method-select');
    const methodName = methodEl.options[methodEl.selectedIndex].text;
    const methodVal = methodEl.value;
    
    payments.push({ method: methodVal, name: methodName, value: val });
    
    updatePaymentUI();
    
    // Auto calculate remaining for next input
    const remaining = Math.max(0, totalSaleAmount - getTotalPaid());
    if (remaining > 0) {
        valueInput.value = remaining.toFixed(2);
        valueInput.focus();
        valueInput.select();
    } else {
        valueInput.value = '';
    }
}

function removePayment(index) {
    payments.splice(index, 1);
    updatePaymentUI();
}

function getTotalPaid() {
    return payments.reduce((sum, p) => sum + p.value, 0);
}

function updatePaymentUI() {
    const listEl = document.getElementById('payments-list');
    const totalPaid = getTotalPaid();
    
    // Render list
    listEl.innerHTML = '';
    if (payments.length === 0) {
        let li = document.createElement('li');
        li.className = 'list-group-item text-center text-muted small py-3';
        li.innerText = 'Nenhum pagamento registrado.';
        li.id = 'no-payments-msg';
        listEl.appendChild(li);
    } else {
        payments.forEach((p, idx) => {
            let li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3 fade-in';
            li.innerHTML = `
                <div>
                    <span class="badge bg-light text-dark border me-2">${p.name}</span>
                    <span class="fw-bold text-success">R$ ${p.value.toFixed(2)}</span>
                </div>
                <button class="btn btn-link text-danger p-0" onclick="removePayment(${idx})"><i class="fas fa-times-circle"></i></button>
            `;
            listEl.appendChild(li);
        });
    }
    
    document.getElementById('payment-count').innerText = payments.length;
    document.getElementById('modal-total-paid').innerText = `R$ ${totalPaid.toFixed(2)}`;
    
    // Logic for remaining and change
    const remaining = totalSaleAmount - totalPaid;
    const remainingEl = document.getElementById('remaining-container');
    const changeEl = document.getElementById('change-container');
    const confirmBtn = document.getElementById('btn-confirm-sale');
    
    if (remaining > 0) {
        // Falta pagar
        remainingEl.classList.remove('d-none');
        changeEl.classList.add('d-none');
        document.getElementById('modal-remaining').innerText = `R$ ${remaining.toFixed(2)}`;
        confirmBtn.classList.add('disabled');
    } else if (remaining < 0) {
        // Tem Troco
        remainingEl.classList.add('d-none');
        changeEl.classList.remove('d-none');
        document.getElementById('modal-change').innerText = `R$ ${Math.abs(remaining).toFixed(2)}`;
        confirmBtn.classList.remove('disabled');
    } else {
        // Exatamente zero
        remainingEl.classList.remove('d-none');
        changeEl.classList.add('d-none');
        remainingEl.classList.remove('border-warning');
        remainingEl.classList.add('border-success', 'bg-success', 'bg-opacity-10');
        document.getElementById('remaining-container').querySelector('span').innerHTML = '<i class="fas fa-check-circle me-1"></i> Total Pago';
        document.getElementById('remaining-container').querySelector('span').classList.remove('text-warning-emphasis');
        document.getElementById('remaining-container').querySelector('span').classList.add('text-success');
        
        document.getElementById('modal-remaining').innerText = 'R$ 0,00';
        document.getElementById('modal-remaining').classList.remove('text-warning-emphasis');
        document.getElementById('modal-remaining').classList.add('text-success');
        
        confirmBtn.classList.remove('disabled');
    }
    
    // Reset remaining UI if it went back > 0
    if (remaining > 0) {
        remainingEl.classList.add('border-warning');
        remainingEl.classList.remove('border-success', 'bg-success', 'bg-opacity-10');
        document.getElementById('remaining-container').querySelector('span').innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Falta Pagar';
        document.getElementById('remaining-container').querySelector('span').classList.add('text-warning-emphasis');
        document.getElementById('remaining-container').querySelector('span').classList.remove('text-success');
        document.getElementById('modal-remaining').classList.add('text-warning-emphasis');
        document.getElementById('modal-remaining').classList.remove('text-success');
    }
}

function confirmSale() {
    if (cart.length === 0) return;
    if (getTotalPaid() < totalSaleAmount - 0.01) {
        alert('O valor pago é inferior ao total da venda.');
        return;
    }
    
    const btn = document.getElementById('btn-confirm-sale');
    const original = btn.innerHTML;
    btn.classList.add('disabled');
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Processando...';

    // Format payments for backend
    const paymentsPayload = payments.map(p => ({
        method: p.method,
        value: p.value
    }));

    fetch('/pdv/sale', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            items: cart,
            payments: paymentsPayload
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            paymentModalInstance.hide();
            // Success feedback
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = 9999;
            toast.innerHTML = `
                <div class="toast show bg-success text-white border-0 shadow" role="alert" style="border-radius:12px;">
                    <div class="toast-body d-flex align-items-center fs-6">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div>
                            <strong>Venda Finalizada!</strong><br>
                            <small>ID: ${data.venda_id}</small>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
            
            cart = [];
            renderCart();
        } else {
            alert('Erro ao finalizar venda: ' + data.error);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro fatal ao processar venda no servidor.');
    })
    .finally(() => {
        btn.classList.remove('disabled');
        btn.innerHTML = original;
    });
}
</script>

<style>
/* Base Styles */
.bg-navy { background-color: #0A2647; }
.text-navy { color: #0A2647; }
.btn-navy { background-color: #0A2647; border-color: #0A2647; }
.btn-navy:hover { background-color: #061a35; border-color: #061a35; }
.text-warning-emphasis { color: #997404 !important; }

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* Effects & Hover states */
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05) !important; border-color: #0A2647 !important;}
.hover-danger:hover { background-color: #fee2e2 !important; color: #ef4444 !important; }
.bg-gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); border:none; }
.bg-gradient-success:hover { background: linear-gradient(135deg, #059669 0%, #047857 100%); }

/* Animations */
.fade-in { animation: fadeIn 0.3s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.slide-in { animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes slideIn { 
    from { opacity: 0; transform: translateX(20px); } 
    to { opacity: 1; transform: translateX(0); } 
}

.transition-all { transition: all 0.3s ease; }
</style>

<?php require BASE_PATH . '/resources/views/layouts/footer.php'; ?>
