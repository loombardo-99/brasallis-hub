<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/funcoes.php';

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// --- CÁLCULO DE MÉTRICAS ---

// 1. Total de Economia Potencial (Recuperada/Evitada)
$savings_stmt = $conn->prepare("
    SELECT SUM(at.savings_potential) 
    FROM analise_tributaria at
    JOIN compras c ON at.compra_id = c.id
    WHERE c.empresa_id = ?
");
$savings_stmt->execute([$empresa_id]);
$total_savings = $savings_stmt->fetchColumn() ?: 0.00;

// 2. Total de Itens Analisados
$count_stmt = $conn->prepare("
    SELECT COUNT(at.id)
    FROM analise_tributaria at
    JOIN compras c ON at.compra_id = c.id
    WHERE c.empresa_id = ?
");
$count_stmt->execute([$empresa_id]);
$total_analyzed = $count_stmt->fetchColumn();

// 3. Alertas Críticos (CFOP errado, etc)
$alerts_stmt = $conn->prepare("
    SELECT COUNT(at.id)
    FROM analise_tributaria at
    JOIN compras c ON at.compra_id = c.id
    WHERE c.empresa_id = ? AND at.alert_level IN ('warning', 'critical')
");
$alerts_stmt->execute([$empresa_id]);
$total_alerts = $alerts_stmt->fetchColumn();

// 4. Buscar últimas análises para a tabela
$recent_stmt = $conn->prepare("
    SELECT at.*, p.name as product_name, c.purchase_date
    FROM analise_tributaria at
    JOIN compras c ON at.compra_id = c.id
    LEFT JOIN produtos p ON at.product_id = p.id
    WHERE c.empresa_id = ?
    ORDER BY at.created_at DESC
    LIMIT 10
");
$recent_stmt->execute([$empresa_id]);
$recent_analysis = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Dados para o Gráfico (Economia por Mês)
$chart_stmt = $conn->prepare("
    SELECT DATE_FORMAT(c.purchase_date, '%Y-%m') as mes, SUM(at.savings_potential) as economia
    FROM analise_tributaria at
    JOIN compras c ON at.compra_id = c.id
    WHERE c.empresa_id = ?
    GROUP BY mes
    ORDER BY mes ASC
    LIMIT 6
");
$chart_stmt->execute([$empresa_id]);
$chart_data = $chart_stmt->fetchAll(PDO::FETCH_ASSOC);

$chart_labels = [];
$chart_values = [];
foreach ($chart_data as $row) {
    $chart_labels[] = date('M/Y', strtotime($row['mes'] . '-01'));
    $chart_values[] = $row['economia'];
}

include_once '../includes/cabecalho.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-balance-scale text-primary me-2"></i>Inteligência Tributária</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="compras.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Ir para Compras
        </a>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="row g-4 mb-4">
    <!-- Economia Potencial -->
    <div class="col-md-4">
        <div class="card-dashboard p-3 h-100 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted text-uppercase mb-2">Economia Potencial (PIS/COFINS)</h6>
                    <h2 class="mb-0 text-success fw-bold">R$ <?= number_format($total_savings, 2, ',', '.') ?></h2>
                    <small class="text-muted">Créditos ou isenções identificadas</small>
                </div>
                <div class="icon-shape bg-success-light text-success rounded-circle p-3">
                    <i class="fas fa-piggy-bank fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Itens Analisados -->
    <div class="col-md-4">
        <div class="card-dashboard p-3 h-100 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted text-uppercase mb-2">Itens Auditados</h6>
                    <h2 class="mb-0 text-primary fw-bold"><?= $total_analyzed ?></h2>
                    <small class="text-muted">Produtos processados pela IA</small>
                </div>
                <div class="icon-shape bg-primary-light text-primary rounded-circle p-3">
                    <i class="fas fa-search-dollar fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas Fiscais -->
    <div class="col-md-4">
        <div class="card-dashboard p-3 h-100 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted text-uppercase mb-2">Alertas de Conformidade</h6>
                    <h2 class="mb-0 text-warning fw-bold"><?= $total_alerts ?></h2>
                    <small class="text-muted">Divergências de CFOP ou NCM</small>
                </div>
                <div class="icon-shape bg-warning-light text-warning rounded-circle p-3">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Economia -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Economia Gerada por Mês</h6>
            </div>
            <div class="card-body">
                <canvas id="savingsChart" style="height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Sugestões Recentes -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Diagnóstico Rápido</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($recent_analysis)): ?>
                        <div class="text-center p-4 text-muted">Nenhuma análise realizada ainda.</div>
                    <?php else: ?>
                        <?php foreach ($recent_analysis as $item): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 text-truncate" style="max-width: 200px;"><?= htmlspecialchars($item['item_name_xml']) ?></h6>
                                    <small class="text-muted"><?= date('d/m', strtotime($item['created_at'])) ?></small>
                                </div>
                                <p class="mb-1 small text-muted">NCM: <?= $item['ncm_detectado'] ?></p>
                                <?php if ($item['savings_potential'] > 0): ?>
                                    <div class="fw-bold text-success small"><i class="fas fa-arrow-up"></i> +R$ <?= number_format($item['savings_potential'], 2, ',', '.') ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabela Detalhada -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary">Auditoria Detalhada de Itens</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Produto</th>
                        <th>NCM Detectado</th>
                        <th>CFOP Entrada</th>
                        <th>Status</th>
                        <th>Economia Est.</th>
                        <th>Sugestão da IA/Regras</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_analysis as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['item_name_xml']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= $row['ncm_detectado'] ?></span></td>
                            <td><?= $row['cfop_entrada'] ?></td>
                            <td>
                                <?php if ($row['alert_level'] == 'info'): ?>
                                    <span class="badge bg-info">Info</span>
                                <?php elseif ($row['alert_level'] == 'warning'): ?>
                                    <span class="badge bg-warning text-dark">Atenção</span>
                                <?php elseif ($row['alert_level'] == 'critical'): ?>
                                    <span class="badge bg-danger">Erro</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Validado</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-success fw-bold">
                                <?= $row['savings_potential'] > 0 ? 'R$ ' . number_format($row['savings_potential'], 2, ',', '.') : '-' ?>
                            </td>
                            <td class="small text-muted fst-italic">
                                "<?= htmlspecialchars($row['ai_suggestion']) ?>"
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Configuração do Gráfico
const ctx = document.getElementById('savingsChart').getContext('2d');
const savingsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Economia Identificada (R$)',
            data: <?= json_encode($chart_values) ?>,
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
            borderColor: '#198754',
            borderWidth: 2,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#198754',
            pointRadius: 5,
            fill: true,
            tension: 0.4
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
                grid: { borderDash: [2, 2] }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>

<?php include_once '../includes/rodape.php'; ?>
