<?php
// superadmin/index.php
session_start();
require_once '../includes/funcoes.php';

// Proteção
checkSuperAdmin();

// Conexão
$conn = connect_db();

// --- Métricas ---

// 1. Total Empresas
$stmt = $conn->query("SELECT COUNT(*) as total FROM empresas");
$total_empresas = $stmt->fetchColumn();

// 2. MRR (Receita Recorrente Mensal)
$stmt = $conn->query("SELECT 
    SUM(CASE 
        WHEN ai_plan = 'growth' THEN 99 
        WHEN ai_plan = 'enterprise' THEN 0 
        ELSE 0 
    END) as mrr 
    FROM empresas");
$mrr = $stmt->fetchColumn() ?: 0;

// 3. Tokens Consumidos (Global)
$stmt = $conn->query("SELECT SUM(ai_tokens_used_month) as total_tokens FROM empresas");
$total_tokens = $stmt->fetchColumn() ?: 0;

// 4. Últimas Empresas
$stmt = $conn->query("SELECT id, name, created_at, ai_plan, support_level FROM empresas ORDER BY created_at DESC LIMIT 10");
$empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Dados para Gráficos
$stmt = $conn->query("SELECT ai_plan, COUNT(*) as count FROM empresas GROUP BY ai_plan");
$plan_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $conn->query("SELECT name, ai_tokens_used_month FROM empresas ORDER BY ai_tokens_used_month DESC LIMIT 5");
$top_token_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Dados de Receita (Real)
// Agrupa pagamentos aprovados por mês (Últimos 6 meses)
$stmt = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as mes, SUM(amount) as total 
    FROM pagamentos 
    WHERE status = 'approved' 
    GROUP BY mes 
    ORDER BY mes DESC 
    LIMIT 6
");
$revenue_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['2025-12' => 500.00, ...]

// Preencher meses vazios e ordenar cronologicamente
$revenue_labels = [];
$revenue_values = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $monthName = date('M', strtotime("-$i months")); // Jan, Feb...
    
    // Tradução simples para PT
    $meses_pt = ['Jan' => 'Jan', 'Feb' => 'Fev', 'Mar' => 'Mar', 'Apr' => 'Abr', 'May' => 'Mai', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago', 'Sep' => 'Set', 'Oct' => 'Out', 'Nov' => 'Nov', 'Dec' => 'Dez'];
    
    $revenue_labels[] = $meses_pt[$monthName] ?? $monthName;
    $revenue_values[] = $revenue_raw[$date] ?? 0;
}

require_once 'includes/header.php';
?>

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-dark m-0">Command Center</h2>
            <p class="text-muted m-0">Visão geral do desempenho do SaaS</p>
        </div>
        <button class="btn btn-dark rounded-pill px-4"><i class="fas fa-download me-2"></i>Relatório Mensal</button>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
        <!-- MRR -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">MRR Mensal</p>
                        <h3 class="big-number">R$ <?php echo number_format($mrr, 2, ',', '.'); ?></h3>
                    </div>
                    <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="mt-3 d-flex align-items-center gap-2 text-sm">
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fas fa-arrow-up me-1"></i> +12%</span>
                    <span class="text-muted small">vs. mês anterior</span>
                </div>
            </div>
        </div>

        <!-- Active Companies -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Empresas Ativas</p>
                        <h3 class="big-number"><?php echo $total_empresas; ?></h3>
                    </div>
                    <div class="stat-icon-box bg-success bg-opacity-10 text-success">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted small">
                    Base total de clientes cadastrados
                </div>
            </div>
        </div>

        <!-- Token Usage -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Consumo IA</p>
                        <h3 class="big-number"><?php echo number_format($total_tokens / 1000, 1); ?>k</h3>
                    </div>
                    <div class="stat-icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-bolt"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted small">
                    Tokens consumidos este mês
                </div>
            </div>
        </div>

        <!-- Support Tickets -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Chamados Abertos</p>
                        <h3 class="big-number">3</h3> <!-- Placeholder dinâmico idealmente -->
                    </div>
                    <div class="stat-icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted small">
                    Requerem atenção imediata
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Lists -->
    <div class="row g-4">
        <!-- Left Column: Charts -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between">
                    <h5 class="fw-bold m-0">Performance Financeira</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light rounded-pill px-3" type="button"><i class="fas fa-calendar me-2"></i>Este Ano</button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                         <div class="card-body p-4 text-center">
                            <h6 class="text-muted fw-bold mb-4">Distribuição de Planos</h6>
                            <div style="width: 180px; margin: 0 auto;">
                                <canvas id="planChart"></canvas>
                            </div>
                         </div>
                    </div>
                </div>
                <div class="col-md-6">
                     <div class="card border-0 shadow-sm rounded-4 h-100">
                         <div class="card-body p-4">
                            <h6 class="text-muted fw-bold mb-4">Top Consumidores (IA)</h6>
                            <canvas id="tokenChart"></canvas>
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Recent Activity -->
        <div class="col-xl-4">
            <div class="premium-table h-100">
                <div class="p-4 border-bottom border-light">
                    <h5 class="fw-bold m-0">Novas Empresas</h5>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <tbody>
                            <?php foreach($empresas as $emp): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-secondary" style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($emp['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h6 class="m-0 fw-bold text-dark fs-6"><?php echo htmlspecialchars($emp['name']); ?></h6>
                                            <small class="text-muted" style="font-size: 0.75rem;">Desde <?php echo date('d/m', strtotime($emp['created_at'])); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="badge-premium bg-<?php echo $emp['ai_plan']; ?>">
                                        <?php echo ucfirst($emp['ai_plan']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Distribuição de Planos
        const planLabels = <?php echo json_encode(array_keys($plan_data)); ?>;
        const planValues = <?php echo json_encode(array_values($plan_data)); ?>;
        new Chart(document.getElementById('planChart'), {
            type: 'doughnut',
            data: {
                labels: planLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                datasets: [{
                    data: planValues,
                    backgroundColor: ['#94a3b8', '#22c55e', '#6366f1'],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: { plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } } }
        });

        // Top Consumo
        const tokenLabels = <?php echo json_encode(array_column($top_token_users, 'name')); ?>;
        const tokenValues = <?php echo json_encode(array_column($top_token_users, 'ai_tokens_used_month')); ?>;
        new Chart(document.getElementById('tokenChart'), {
            type: 'bar',
            data: {
                labels: tokenLabels,
                datasets: [{
                    label: 'Tokens',
                    data: tokenValues,
                    backgroundColor: '#fbbf24',
                    borderRadius: 4,
                    barThickness: 20
                }]
            },
            options: { 
                indexAxis: 'y',
                scales: { x: { display: false }, y: { grid: { display: false } } },
                plugins: { legend: { display: false } }
            }
        });

        // Revenue Mockup
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [{
                    label: 'Receita (R$)',
                    data: [1200, 1900, 3000, 5000, 4500, 8000],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.05)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0
                }]
            },
            options: {
                scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>
