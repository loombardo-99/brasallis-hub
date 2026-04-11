<?php include_once __DIR__ . '/../../includes/navigation-brasallis.php'; ?>

<style>
    :root {
        --apple-bg: #f5f5f7;
        --apple-card: rgba(255, 255, 255, 0.82);
        --apple-text: #1d1d1f;
        --apple-text-secondary: #86868b;
        --apple-navy: #0A2647;
        --apple-emerald: #2C7865;
        --apple-border: rgba(0, 0, 0, 0.04);
        --apple-shadow: 0 8px 30px rgba(0,0,0,0.04);
    }

    body { background-color: var(--apple-bg); font-family: 'SF Pro Display', 'Inter', system-ui, sans-serif; color: var(--apple-text); }
    
    .commander-container { padding: 3rem 2rem; max-width: 1400px; margin: 0 auto; }
    
    /* Apple Header */
    .greeting { font-size: 2.8rem; font-weight: 700; color: var(--apple-text); letter-spacing: -1.2px; }
    .executive-text { color: var(--apple-text-secondary); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.8; }

    /* Apple Cards (Glassmorphism) */
    .exec-card {
        background: var(--apple-card);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border-radius: 28px;
        padding: 2rem;
        border: 1px solid var(--apple-border);
        box-shadow: var(--apple-shadow);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .exec-card:hover { 
        transform: scale(1.02); 
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08); 
    }
    .exec-card:active {
        transform: scale(0.98);
    }
    
    .metric-value { font-size: 2.2rem; font-weight: 700; color: var(--apple-text); letter-spacing: -1px; margin-bottom: 0.25rem; }
    .metric-label { font-size: 0.85rem; font-weight: 500; color: var(--apple-text-secondary); letter-spacing: -0.2px; }
    
    .trend-up { color: #008000; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 4px; }
    .trend-down { color: #ff3b30; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 4px; }

    /* Progress & Goals (Thin Apple Style) */
    .progress-track { background-color: rgba(0,0,0,0.05); border-radius: 99px; height: 6px; overflow: hidden; width: 100%; margin-top: 1rem; }
    .progress-fill { height: 100%; border-radius: 99px; transition: width 1s ease-in-out; background: var(--apple-navy); }

    /* Funnel (Subtle Layering) */
    .funnel-stage { 
        padding: 1.2rem; 
        border-radius: 20px; 
        background: rgba(0,0,0,0.02); 
        border: 1px solid transparent; 
        margin-bottom: 1rem; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        transition: all 0.2s ease;
    }
    .funnel-stage:hover { background: rgba(0,0,0,0.04); border-color: rgba(0,0,0,0.05); }

    /* Dark Panel (Executive Black) */
    .dark-panel { background: #1d1d1f; color: white; border-radius: 28px; }
    .dark-panel .metric-value { color: white; }
    .dark-panel .metric-label { color: #86868b; }

    .clickable-card { text-decoration: none !important; color: inherit; }
    .has-tooltip { cursor: help; }
</style>

<div class="commander-container">
    <!-- Header -->
    <div class="row align-items-center mb-5 pb-4 border-bottom border-light">
        <div class="col-lg-8">
            <div class="executive-text mb-2"><i class="fas fa-sparkles me-1 text-primary"></i> Brasallis Executive Hub</div>
            <h1 class="greeting"><?= htmlspecialchars($_SESSION['empresa_nome'] ?? 'Dashboard') ?></h1>
            <p class="text-muted mb-0 mt-2" style="font-weight: 500;">Visão estratégica analítica da operação.</p>
        </div>
        <div class="col-lg-4 text-end d-none d-lg-block">
             <button class="btn btn-white shadow-sm border-0 rounded-pill px-4 py-2 fw-bold" style="font-size: 0.8rem; background: white;" onclick="window.location.reload()">
                <i class="fas fa-rotate me-2 opacity-50"></i> Sincronizar Dados
            </button>
        </div>
    </div>

    <!-- SECTION 1: Alertas e Insights -->
    <?php 
    $critical_insights = array_filter($insights, function($i) { return $i['type'] === 'danger' || $i['priority'] === 0; });
    if (!empty($critical_insights)): ?>
    <div class="row g-3 mb-5">
        <?php foreach(array_slice($critical_insights, 0, 2) as $insight): ?>
        <div class="col-md-6">
            <div class="exec-card p-3 d-flex align-items-center" style="background: <?= $insight['type'] === 'danger' ? 'rgba(255, 59, 48, 0.05)' : 'rgba(0, 122, 255, 0.05)' ?>; border: none;">
                <i class="fas <?= $insight['icon'] ?> fa-xl me-3 <?= $insight['type'] === 'danger' ? 'text-danger' : 'text-primary' ?> opacity-70"></i>
                <div>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;"><?= $insight['title'] ?></h6>
                    <span class="small text-muted" style="font-size: 0.75rem;"><?= $insight['description'] ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- SECTION 2: Saúde Financeira e Metas -->
    <div class="mb-5">
        <div class="row g-4">
            <!-- Vendas Meta -->
            <div class="col-md-4">
                <a href="relatorios.php" class="clickable-card">
                    <div class="exec-card">
                        <div class="d-flex justify-content-between mb-4">
                            <span class="metric-label">Meta de Faturamento</span>
                            <i class="fas fa-bullseye text-dark opacity-10 fs-5"></i>
                        </div>
                        <div class="metric-value">R$ <?= number_format($metas_exec['vendas']['atual'], 2, ',', '.') ?></div>
                        <div class="metric-label mb-3">de meta calculada R$ <?= number_format($metas_exec['vendas']['meta'], 2, ',', '.') ?></div>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between small fw-bold mb-2">
                                <span class="metric-label">Atingimento</span>
                                <span class="text-dark"><?= number_format($metas_exec['vendas']['progresso_percent'], 1) ?>%</span>
                            </div>
                            <div class="progress-track"><div class="progress-fill" style="width: <?= $metas_exec['vendas']['progresso_percent'] ?>%"></div></div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Saúde Financeira (Focused Dark) -->
            <div class="col-md-4">
                <a href="relatorios_avancados.php" class="clickable-card">
                    <div class="exec-card dark-panel">
                        <div class="d-flex justify-content-between mb-4">
                            <span class="metric-label">Margem Líquida</span>
                            <i class="fas fa-hand-holding-dollar opacity-20 fs-5 text-white"></i>
                        </div>
                        <div class="metric-value"><?= number_format($exec_health['margem_lucro'], 1) ?>%</div>
                        <div class="trend-up mt-2" style="color: #34c759;"><i class="fas fa-arrow-up me-1"></i> ROI: <?= number_format($exec_health['roi'], 1) ?>%</div>
                        <p class="small mt-auto pt-3 mb-0 border-top border-white border-opacity-10 text-muted" style="font-size: 0.75rem;">Rentabilidade dos últimos 30 dias.</p>
                    </div>
                </a>
            </div>

            <!-- Inadimplência -->
            <div class="col-md-4">
                <a href="../modules/financeiro/views/dashboard.php" class="clickable-card">
                    <div class="exec-card">
                        <div class="d-flex justify-content-between mb-4">
                            <span class="metric-label">Pendência Financeira</span>
                            <i class="fas fa-triangle-exclamation text-danger opacity-40 fs-5"></i>
                        </div>
                        <div class="metric-value" style="color: #ff3b30;">R$ <?= number_format($fin_kpis['receivables']['overdue'], 2, ',', '.') ?></div>
                        <div class="metric-label mb-3">Títulos Vencidos</div>
                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="metric-label">A Receber Total:</span>
                            <span class="fw-bold text-dark">R$ <?= number_format($fin_kpis['receivables']['total_pending'], 2, ',', '.') ?></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- SECTION 3: CRM & Pipeline -->
    <div class="mb-5">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-4">
                <a href="../modules/crm/views/kanban.php" class="clickable-card">
                    <div class="exec-card">
                        <div class="d-flex justify-content-between mb-4">
                            <span class="metric-label">Taxa de Conversão</span>
                            <i class="fas fa-trophy text-warning opacity-40 fs-5"></i>
                        </div>
                        <div class="metric-value"><?= number_format($crm_projects['win_rate'], 1) ?>%</div>
                        <div class="metric-label mb-3">Eficiência do Pipeline</div>
                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="metric-label">Oportunidades Ativas:</span>
                            <span class="fw-bold text-primary">R$ <?= number_format($crm_kpis['deals_value'], 2, ',', '.') ?></span>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-8">
                <div class="exec-card" style="background: rgba(0,0,0,0.01);">
                    <div class="d-flex flex-column h-100">
                        <div class="funnel-stage">
                            <span class="fw-bold text-muted"><i class="fas fa-search me-2 opacity-50"></i> Prospecção (<?= $crm_projects['funil']['prospeccao']['qtd'] ?>)</span>
                            <span class="fw-bold text-dark">R$ <?= number_format($crm_projects['funil']['prospeccao']['valor'], 0, ',', '.') ?></span>
                        </div>
                        <div class="funnel-stage" style="width: 95%; margin-left: auto; margin-right: auto; background: rgba(0, 122, 255, 0.05);">
                            <span class="fw-bold text-primary"><i class="fas fa-comments me-2 opacity-50"></i> Negociação (<?= $crm_projects['funil']['negociacao']['qtd'] ?>)</span>
                            <span class="fw-bold text-primary">R$ <?= number_format($crm_projects['funil']['negociacao']['valor'], 0, ',', '.') ?></span>
                        </div>
                        <div class="funnel-stage mb-0" style="width: 90%; margin-left: auto; margin-right: auto; background: rgba(44, 120, 101, 0.08);">
                            <span class="fw-bold text-success"><i class="fas fa-check-double me-2 opacity-50"></i> Fechados (<?= $crm_projects['funil']['ganho']['qtd'] ?>)</span>
                            <span class="fw-bold text-success">R$ <?= number_format($crm_projects['funil']['ganho']['valor'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 4: Relatório Analítico e Operações -->
    <div class="mb-4">
        <div class="row g-4">
            <!-- Main Chart -->
            <div class="col-lg-8">
                <div class="exec-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Receita & Crescimento</h6>
                            <p class="text-muted small mb-0">Monitoramento analítico de vendas e lucratividade.</p>
                        </div>
                    </div>
                    <div style="height: 380px;"><canvas id="salesChart"></canvas></div>
                </div>
            </div>

            <!-- Operacional & Logistics -->
            <div class="col-lg-4 d-flex flex-column gap-4">
                
                <!-- Capital Humano Access Card -->
                <a href="../modules/rh/views/index.php" class="clickable-card title="Gerenciar Equipe">
                    <div class="exec-card py-4">
                        <div class="d-flex justify-content-between align-items-center mb-0">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Capital Humano</h6>
                                <p class="text-muted small mb-0">Operadores ativos no sistema.</p>
                            </div>
                            <div class="text-primary opacity-20">
                                <i class="fas fa-users-gear fs-2"></i>
                            </div>
                        </div>
                        <div class="mt-auto d-flex align-items-end gap-2">
                            <span class="display-5 fw-bold text-dark lh-1"><?= $active_employees_count ?></span>
                            <span class="metric-label fw-bold mb-1">Colaboradores</span>
                        </div>
                    </div>
                </a>

                <!-- Relatório Logística -->
                <a href="registrar_compra.php" class="clickable-card" title="Logística">
                    <div class="exec-card py-4" style="background: rgba(44, 120, 101, 0.05);">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                             <h6 class="fw-bold text-success mb-0">Logística</h6>
                             <i class="fas fa-truck-fast text-success opacity-30 fs-4"></i>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small fw-bold mb-2">
                                <span class="text-success small opacity-75">Nível de Serviço</span> 
                                <span class="text-success"><?= $operacoes['fulfillment_rate'] ?>%</span>
                            </div>
                            <div class="progress-track" style="background: rgba(255,255,255,0.4);"><div class="progress-fill" style="width: <?= $operacoes['fulfillment_rate'] ?>%; background: #2C7865;"></div></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-4 shadow-sm mt-auto">
                            <div>
                                <div class="small fw-bold text-muted" style="font-size: 0.7rem;">Cargas na Recepção</div>
                                <div class="h4 fw-bold mb-0 text-dark"><?= $operacoes['compras_pendentes'] ?> Lote(s)</div>
                            </div>
                            <i class="fas <?= $operacoes['compras_pendentes'] > 0 ? 'fa-box-open text-warning' : 'fa-check text-success' ?> fs-3 opacity-50"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>


<?php include_once __DIR__ . '/../../includes/rodape.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        let salesChart;

        function initChart(labels, salesData, costData, profitData) {
            if (salesChart) salesChart.destroy();
            
            const salesGradient = ctx.createLinearGradient(0, 0, 0, 400);
            salesGradient.addColorStop(0, 'rgba(10, 38, 71, 0.1)');
            salesGradient.addColorStop(1, 'rgba(10, 38, 71, 0)');

            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Receita',
                            data: salesData,
                            borderColor: '#0A2647',
                            backgroundColor: salesGradient,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#0A2647',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 2
                        },
                        {
                            label: 'Lucro',
                            data: profitData,
                            borderColor: '#2C7865',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.4,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(29, 29, 31, 0.9)',
                            padding: 12,
                            cornerRadius: 10,
                            titleFont: { size: 13, weight: '600' },
                            bodyFont: { size: 13 },
                            displayColors: false
                        }
                    },
                    scales: {
                        y: { 
                            display: false,
                            grid: { display: false }
                        },
                        x: { 
                            grid: { display: false },
                            ticks: { 
                                color: '#86868b',
                                font: { size: 11, weight: '500' }
                            }
                        }
                    }
                }
            });
        }

        const initialLabels = <?= $chart_labels ?: '[]' ?>;
        const initialSales = <?= $chart_sales ?: '[]' ?>;
        const initialProfit = <?= $chart_profit ?: '[]' ?>;
        const initialCost = <?= $chart_cost ?: '[]' ?>;
        
        initChart(initialLabels, initialSales, initialCost, initialProfit);
    });
</script>
