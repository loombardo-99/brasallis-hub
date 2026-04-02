<?php
/**
 * View: admin/dashboard
 */
$title = "Painel de Controle";
require BASE_PATH . '/resources/views/layouts/header.php';

$revenueToday = $kpis['revenue_today']['current'] ?? 0;
$lowStockCount = $kpis['low_stock_items']['current'] ?? 0;
?>

<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold text-navy mb-1">Bem-vindo, <?= htmlspecialchars($username) ?></h2>
        <p class="text-secondary mb-0"><?= htmlspecialchars($empresa_nome) ?> — Resumo operacional de hoje.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-premium btn-dark shadow-sm">
            <i class="fas fa-plus me-2"></i>Nova Venda
        </button>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card-premium p-4 h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #0A2647 0%, #1e3a5f 100%); color: white;">
            <div class="d-flex justify-content-between mb-3">
                <div class="bg-white bg-opacity-10 p-2 rounded-3">
                    <i class="fas fa-dollar-sign fa-lg"></i>
                </div>
                <div class="small opacity-75">Hoje</div>
            </div>
            <div class="h3 fw-bold mb-1">R$ <?= number_format($revenueToday, 2, ',', '.') ?></div>
            <div class="small opacity-75">Vendas realizadas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-premium p-4 h-100">
            <div class="d-flex justify-content-between mb-3">
                <div class="bg-danger bg-opacity-10 p-2 rounded-3 text-danger">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                </div>
                <div class="small text-muted">Atenção</div>
            </div>
            <div class="h3 fw-bold mb-1"><?= $lowStockCount ?></div>
            <div class="small text-muted">Itens com baixo estoque</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-premium p-4 h-100">
            <div class="d-flex justify-content-between mb-3">
                <div class="bg-success bg-opacity-10 p-2 rounded-3 text-success">
                    <i class="fas fa-shopping-basket fa-lg"></i>
                </div>
                <div class="small text-muted">Pedidos</div>
            </div>
            <div class="h3 fw-bold mb-1">12</div>
            <div class="small text-muted">Processados hoje</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-premium p-4 h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #2C7865 0%, #399D85 100%); color: white;">
            <div class="d-flex justify-content-between mb-3">
                <div class="bg-white bg-opacity-10 p-2 rounded-3">
                    <i class="fas fa-users fa-lg"></i>
                </div>
                <div class="small opacity-75">Clientes</div>
            </div>
            <div class="h3 fw-bold mb-1">154</div>
            <div class="small opacity-75">Base total ativa</div>
        </div>
    </div>
</div>

<!-- Main Row -->
<div class="row g-4 mb-4">
    <!-- Chart -->
    <div class="col-lg-8">
        <div class="card-premium h-100">
            <div class="card-header-premium border-0 pb-0">
                <div>
                    <h5 class="fw-bold text-navy mb-0">Desempenho Semanal</h5>
                    <p class="small text-muted">Comparativo de receita e custos</p>
                </div>
                <select class="form-select form-select-sm bg-light border-0 fw-bold" style="width: auto;">
                    <option>Últimos 7 dias</option>
                    <option>Este mês</option>
                </select>
            </div>
            <div class="card-body" style="height: 350px;">
                <canvas id="mainChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-4">
        <div class="card-premium h-100">
            <div class="card-header-premium border-0 pb-0">
                <h5 class="fw-bold text-navy mb-0">Últimas Compras</h5>
            </div>
            <div class="card-body pt-3">
                <?php if (empty($ultimas_compras)): ?>
                    <div class="text-center py-5 text-muted small">Nenhuma compra recente.</div>
                <?php else: ?>
                    <?php foreach ($ultimas_compras as $compra): ?>
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom border-light last-child-no-border">
                            <div class="bg-light p-2 rounded-3 me-3">
                                <i class="fas fa-truck text-muted small"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold small text-navy text-truncate" style="max-width: 150px;">
                                    <?= htmlspecialchars($compra['fornecedor_nome'] ?? 'Fornecedor Desconhecido') ?>
                                </div>
                                <div class="small text-muted fs-xs"><?= date('d/m/Y', strtotime($compra['data_compra'])) ?></div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold small text-navy">R$ <?= number_format($compra['total'], 2, ',', '.') ?></div>
                                <span class="badge <?= $compra['status'] == 'confirmado' ? 'bg-success' : 'bg-warning' ?> bg-opacity-10 <?= $compra['status'] == 'confirmado' ? 'text-success' : 'text-warning' ?>" style="font-size: 0.6rem;">
                                    <?= ucfirst($compra['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="/admin/compras" class="btn btn-light w-100 btn-sm rounded-3 mt-2 fw-bold text-muted border">Ver Tudo</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('mainChart').getContext('2d');
    
    // Gradients
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(10, 38, 71, 0.15)');
    gradient.addColorStop(1, 'rgba(10, 38, 71, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= $chart_labels ?>,
            datasets: [{
                label: 'Receita',
                data: <?= $chart_sales ?>,
                borderColor: '#0A2647',
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2
            }, {
                label: 'Custos',
                data: <?= $chart_cost ?>,
                borderColor: '#E2E8F0',
                borderDash: [5, 5],
                fill: false,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#F1F5F9' },
                    ticks: { color: '#94A3B8', font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94A3B8', font: { size: 11 } }
                }
            }
        }
    });
});
</script>

<style>
    .fs-xs { font-size: 0.75rem; }
    .last-child-no-border:last-child { border-bottom: 0 !important; margin-bottom: 0 !important; padding-bottom: 0 !important; }
</style>

<?php require BASE_PATH . '/resources/views/layouts/footer.php'; ?>
