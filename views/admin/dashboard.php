<?php include_once __DIR__ . '/../../includes/cabecalho.php'; ?>

<link href="../assets/css/dashboard.css" rel="stylesheet">

<?php
// Header Logic (View specific)
$today_revenue = $kpis['revenue_today']['current'];
$low_stock_count = $kpis['low_stock_items']['current'];

if (!isset($_SESSION['empresa_nome'])) {
    // Fallback if not set in session (though controller should handle this largely)
    $_SESSION['empresa_nome'] = 'Minha Empresa';
}
$empresa_nome = $_SESSION['empresa_nome'];
?>

<div class="container-fluid">
    <!-- Global Announcements -->
    <?php if (isset($avisos) && is_array($avisos)): ?>
        <?php foreach($avisos as $aviso): ?>
            <div class="alert alert-<?php echo $aviso['tipo']; ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-bullhorn me-2 fs-5"></i>
                    <div>
                        <strong><?php echo htmlspecialchars($aviso['titulo']); ?>:</strong> 
                        <?php echo htmlspecialchars($aviso['mensagem']); ?>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="header-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 header-content">
        <!-- Identity -->
        <div>
            <div class="d-flex align-items-center gap-3 mb-1 justify-content-center justify-content-md-start">
                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                    <i class="fas fa-cube fa-lg"></i>
                </div>
                <div class="text-start">
                    <h3 class="mb-0 company-brand"><?php echo htmlspecialchars($empresa_nome); ?></h3>
                    <p class="welcome-text mb-0">Bem-vindo, <?php echo $_SESSION['username']; ?></p>
                </div>
            </div>
        </div>

        <!-- Metrics -->
        <div class="d-flex align-items-center gap-3 flex-wrap metrics-container">
            <div class="metric-pill">
                <div class="metric-icon bg-success-subtle text-success"><i class="fas fa-chart-bar"></i></div>
                <div>
                    <div class="kpi-label mb-0" style="font-size: 0.7rem;">Hoje</div>
                    <div class="fw-bold text-main">R$ <?php echo number_format($today_revenue, 2, ',', '.'); ?></div>
                </div>
            </div>
            <div class="metric-pill">
                <div class="metric-icon bg-danger-subtle text-danger"><i class="fas fa-bell"></i></div>
                <div>
                    <div class="kpi-label mb-0" style="font-size: 0.7rem;">Alertas</div>
                    <div class="fw-bold text-main"><?php echo $low_stock_count; ?></div>
                </div>
            </div>
            <div class="vr mx-2 d-none d-md-block" style="height: 30px; opacity: 0.1;"></div>
            <?php 
                $userPlan = $_SESSION['user_plan'] ?? 'free';
                if ($userPlan === 'free' || $userPlan === 'trial'): 
            ?>
                <a href="planos.php" class="btn btn-gradient-primary rounded-pill px-4 py-2 fw-bold shadow-sm text-white border-0" style="background: linear-gradient(135deg, #0071e3 0%, #42a5f5 100%);">
                    <i class="fas fa-crown me-2 text-warning"></i>Upgrade
                </a>
            <?php else: ?>
                <button class="btn btn-dark rounded-pill px-4 py-2 fw-bold shadow-sm"><i class="fas fa-plus me-2"></i>Venda</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Dynamic Dashboard Layout -->
<?php
// 1. Carregar Layout Salvo
$user_id_layout = $_SESSION['user_id'];
$stmtLayout = $conn->prepare("SELECT layout_json FROM dashboard_layouts WHERE user_id = ?");
$stmtLayout->execute([$user_id_layout]);
$savedLayout = $stmtLayout->fetch(PDO::FETCH_ASSOC);

$defaultOrder = [
    'row1' => ['financeiro_revenue', 'financeiro_profit'], 
    // Developer card removed as requested.
    // 'financeiro_kpis.php' actually outputs TWO divs. We should split it or wrap it.
    // For this refactor, we will treat 'financeiro_kpis' as a block of widgets if possible, OR better:
    // We should assume specific IDs for the atomic cards if we want true dnd.
    // But 'financeiro_kpis.php' echoes multiple cols.
    // Hack: We will wrap Row 1 in a sortable container and just let the user reorder the chunks.
    'row2' => ['sales_chart', 'setores_card', 'estoque_saude']
];

$layout = $savedLayout ? json_decode($savedLayout['layout_json'], true) : $defaultOrder;

// Helper to include widget
function render_widget($id) {
    if ($id === 'financeiro_kpis') include __DIR__ . '/../widgets/financeiro_kpis.php';
    elseif ($id === 'developer_card') include __DIR__ . '/../widgets/developer_card.php';
    elseif ($id === 'estoque_saude') include __DIR__ . '/../widgets/estoque_saude.php';
    elseif ($id === 'setores_card') include __DIR__ . '/../widgets/setores_card.php';
    elseif ($id === 'sales_chart') {
        // Embed Chart Directly or via include
        echo '<div class="col-12 col-xl-8" data-id="sales_chart"><div class="card card-dashboard p-4 h-100"><div class="chart-header"><div><h5 class="chart-title">Análise de Desempenho</h5><p class="text-muted small mb-0">Comparativo de Vendas, Lucro e Previsão</p></div><select id="chartFilter" class="form-select form-select-sm w-auto border-0 bg-light fw-bold text-secondary" style="cursor: pointer;"><option value="day">30 Dias</option><option value="month" selected>12 Meses</option></select></div><div style="height: 350px; width: 100%;"><canvas id="salesChart"></canvas></div></div></div>';
    }
}
?>

<!-- Row 1: Metrics -->
<div class="row g-4 mb-4 sortable-row" id="row1">
    <?php 
    $row1 = $layout['row1'] ?? $defaultOrder['row1'];
    foreach($row1 as $widgetId) render_widget($widgetId); 
    ?>
</div>

<!-- Row 2: Analytics -->
<div class="row g-4 mb-4 sortable-row" id="row2">
    <?php 
    $row2 = $layout['row2'] ?? $defaultOrder['row2'];
    foreach($row2 as $widgetId) render_widget($widgetId);
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    ['row1', 'row2'].forEach(rowId => {
        const el = document.getElementById(rowId);
        new Sortable(el, {
            animation: 150,
            handle: '.card', // Drag handle
            ghostClass: 'bg-light',
            onEnd: function (evt) {
                saveLayout();
            }
        });
    });

    function saveLayout() {
        const layout = {
            row1: getWidgetIds('row1'),
            row2: getWidgetIds('row2')
        };

        fetch('/gerenciador_de_estoque/api/save_layout.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(layout)
        }).then(res => console.log('Layout salvo'));
    }

    function getWidgetIds(containerId) {
        const container = document.getElementById(containerId);
        const ids = [];
        if (container) {
            // Simple implementation: try to find data-widget-id or just use index
            // For now, returning empty to prevent errors until widgets have proper IDs
        }
        return ids; 
    }
}); // End DOMContentLoaded
</script>

<!-- Recent Activity -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card card-dashboard">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Últimas Compras</h5>
            </div>
            <div class="card-body px-0">
                <div class="table-responsive table-responsive-card">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 border-0 text-muted small text-uppercase fw-bold">ID</th>
                                <th class="border-0 text-muted small text-uppercase fw-bold">Fornecedor</th>
                                <th class="border-0 text-muted small text-uppercase fw-bold d-none d-md-table-cell">Data</th>
                                <th class="border-0 text-muted small text-uppercase fw-bold">Valor</th>
                                <th class="border-0 text-muted small text-uppercase fw-bold d-none d-sm-table-cell">Status</th>
                                <th class="pe-4 border-0 text-muted small text-uppercase fw-bold text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimas_compras as $compra): ?>
                            <tr>
                                <td class="ps-4 fw-bold" data-label="ID">#<?php echo $compra['id']; ?></td>
                                <td data-label="Fornecedor">
                                    <div class="d-flex flex-column">
                                        <span><?php echo $compra['fornecedor_nome']; ?></span>
                                        <span class="d-md-none small text-muted"><?php echo date('d/m/Y', strtotime($compra['data_compra'])); ?></span>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell" data-label="Data"><?php echo date('d/m/Y', strtotime($compra['data_compra'])); ?></td>
                                <td class="fw-bold" data-label="Valor">R$ <?php echo number_format($compra['total'], 2, ',', '.'); ?></td>
                                <td class="d-none d-lg-table-cell" data-label="Status">
                                    <?php if($compra['status'] == 'pendente'): ?>
                                        <span class="badge bg-warning-light text-warning rounded-pill px-3">Pendente</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-light text-success rounded-pill px-3">Confirmado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end no-label" data-label="Ações">
                                    <a href="confirmar_compra.php?id=<?php echo $compra['id']; ?>" class="btn btn-sm btn-light text-muted"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/rodape.php'; ?>

<script>
    // Ensure DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('salesChart');
        if (!canvas) {
            console.error('Canvas element not found!');
            return;
        }
        
        // Helper to get CSS Variable value
        const getCssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

        const ctx = canvas.getContext('2d');
        let salesChart;

        // Theme-aware colors
        const textColor = getCssVar('--text-main') || '#333';
        const gridColor = getCssVar('--divider-color') || '#e0e0e0';
        const mutedColor = getCssVar('--text-muted') || '#888';


        // Gradients
        const gradientSales = ctx.createLinearGradient(0, 0, 0, 400);
        gradientSales.addColorStop(0, 'rgba(0, 113, 227, 0.2)');
        gradientSales.addColorStop(1, 'rgba(0, 113, 227, 0)');

        const gradientCost = ctx.createLinearGradient(0, 0, 0, 400);
        gradientCost.addColorStop(0, 'rgba(255, 59, 48, 0.2)');
        gradientCost.addColorStop(1, 'rgba(255, 59, 48, 0)');

        function initChart(labels, salesData, costData, forecastData = []) {
            if (salesChart) salesChart.destroy();

            // Prepare Forecast Data
            let finalLabels = [...labels];
            let finalSales = [...salesData];
            let finalCost = [...costData];
            let finalForecast = new Array(salesData.length).fill(null);

            if (forecastData && forecastData.length > 0) {
                if (salesData.length > 0) {
                    finalForecast[salesData.length - 1] = salesData[salesData.length - 1];
                }
                forecastData.forEach(item => {
                    finalLabels.push(item.label);
                    finalSales.push(null);
                    finalCost.push(null);
                    finalForecast.push(item.predicted_sales);
                });
            }

            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: finalLabels,
                    datasets: [
                        {
                            label: 'Receita (R$)',
                            data: finalSales,
                            borderColor: '#0071e3',
                            backgroundColor: gradientSales,
                            borderWidth: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#0071e3',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4,
                            order: 2
                        },
                        {
                            label: 'Custos (R$)',
                            data: finalCost,
                            borderColor: '#ff3b30',
                            backgroundColor: gradientCost,
                            borderWidth: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#ff3b30',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4,
                            order: 3
                        },
                        {
                            label: 'Previsão',
                            data: finalForecast,
                            borderColor: '#ff9f0a',
                            borderDash: [5, 5],
                            borderWidth: 2,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#ff9f0a',
                            pointRadius: 3,
                            fill: false,
                            tension: 0.4,
                            order: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { 
                            display: true, 
                            position: 'top',
                            labels: { 
                                usePointStyle: true, 
                                boxWidth: 8, 
                                padding: 20,
                                color: textColor // User Theme Color
                            }
                        },
                        tooltip: {
                            backgroundColor: getCssVar('--bg-card'), // Theme Background
                            titleColor: textColor,
                            bodyColor: textColor,
                            borderColor: gridColor,
                            borderWidth: 1,
                            padding: 12,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    if (context.raw === null) return null;
                                    return context.dataset.label + ': R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5], color: gridColor },
                            ticks: { 
                                callback: function(value) { return 'R$ ' + (value/1000).toFixed(1) + 'k'; },
                                color: mutedColor
                            },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: mutedColor },
                            border: { display: false }
                        }
                    }
                }
            });
        }

        // Initial Load - Safe Injection
        const initialLabels = <?php echo $chart_labels ?: '[]'; ?>;
        const initialSales = <?php echo $chart_sales ?: '[]'; ?>;
        const initialCost = <?php echo $chart_cost ?: '[]'; ?>;
        const initialForecast = <?php echo $chart_forecast ?: '[]'; ?>;
        
        console.log('Dashboard Data Loaded:', { labels: initialLabels, sales: initialSales });

        initChart(initialLabels, initialSales, initialCost, initialForecast);

        // Ouvinte de Filtro
        if (chartFilter) {
            chartFilter.addEventListener('change', function() {
                const period = this.value;
                fetch(`../api/get_dashboard_data.php?period=${period}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) { console.error(data.error); return; }
                        initChart(data.labels, data.sales, data.cost, data.forecast ? data.forecast.forecast : []);
                        
                        // Update KPI texts if needed
                        if (data.totals) {
                            // ... existing logic ...
                        }
                    })
                    .catch(err => console.error('Erro:', err));
            });
        }

        // (Theme Switcher Logic Removed - System uses constant identity)
    });
</script>
