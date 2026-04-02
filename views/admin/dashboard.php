<?php include_once __DIR__ . '/../../includes/navigation-brasallis.php'; ?>

<style>
    :root {
        --m3-surface: #f8f9fa;
        --m3-primary: #111827;
        --m3-on-primary: #ffffff;
        --m3-primary-container: #f3f4f6;
        --m3-secondary: #4b5563;
        --m3-accent: #2563eb;
        --m3-success: #10b981;
        --m3-warning: #f59e0b;
        --m3-danger: #ef4444;
    }

    body { background-color: var(--m3-surface); font-family: 'Inter', system-ui, sans-serif; }
    
    .commander-container { padding: 2rem; max-width: 1600px; margin: 0 auto; }
    
    /* Commander Header */
    .greeting { font-size: 2.25rem; font-weight: 900; color: #111827; letter-spacing: -1px; }
    .executive-text { color: #6b7280; font-weight: 500; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px; }

    /* Executive Cards */
    .exec-card {
        background: white; border-radius: 24px; padding: 1.5rem; border: 1px solid #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%; display: flex; flex-direction: column;
    }
    .exec-card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    
    .metric-value { font-size: 2rem; font-weight: 800; color: #111827; letter-spacing: -0.5px; }
    .metric-label { font-size: 0.85rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
    
    .trend-up { color: var(--m3-success); font-weight: 700; font-size: 0.9rem; }
    .trend-down { color: var(--m3-danger); font-weight: 700; font-size: 0.9rem; }
    .trend-neutral { color: var(--m3-secondary); font-weight: 700; font-size: 0.9rem; }

    /* Progress & Goals */
    .progress-track { background-color: #f3f4f6; border-radius: 99px; height: 12px; overflow: hidden; width: 100%; mt-2;}
    .progress-fill { height: 100%; border-radius: 99px; transition: width 1s ease-in-out; }
    .bg-gradient-primary { background: linear-gradient(90deg, #2563eb 0%, #3b82f6 100%); }
    .bg-gradient-success { background: linear-gradient(90deg, #059669 0%, #10b981 100%); }

    /* Funnel & Projects */
    .funnel-stage { padding: 1rem; border-radius: 16px; background: #f9fafb; border: 1px dashed #d1d5db; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center; }
    .funnel-stage strong { color: #374151; font-size: 1.1rem; }

    /* Dark Panel */
    .dark-panel { background: #111827; color: white; border-radius: 24px; padding: 1.5rem; }
    .dark-panel .metric-value { color: white; }
    .dark-panel .metric-label { color: #9ca3af; }

    /* Interactive Elevate */
    .hover-lift { transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease; }
    .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important; z-index: 10; position:relative;}
    .clickable-card { text-decoration: none !important; color: inherit; display: block; height: 100%; }
    .has-tooltip { cursor: help; }
    .funnel-stage:hover { transform: scale(1.02); background-color: #f8fafc; border-color: #3b82f6 !important; }
</style>

<div class="commander-container">
    <!-- Header -->
    <div class="row align-items-end mb-4 pb-2 border-bottom">
        <div class="col-lg-8 pb-3">
            <div class="executive-text mb-1"><i class="fas fa-chess-king me-2"></i>Brasallis Commander</div>
            <h1 class="greeting">Painel Executivo: <?= htmlspecialchars($_SESSION['empresa_nome'] ?? 'Empresa') ?></h1>
            <p class="text-muted mb-0 mt-2">Visão estratégica atualizada em tempo real para tomada de decisão.</p>
        </div>
        <!-- HIDDEN ACTION TEMPLATE (Elite v3) -->
        <div id="page-quick-actions" class="d-none">
            <a href="relatorios_avancados.php" class="btn btn-dark rounded-4 px-3 py-2 fw-bold shadow-sm d-inline-flex align-items-center" style="font-size: 0.85rem;">
                <i class="fas fa-file-invoice-dollar me-2"></i> Relatórios DRE
            </a>
            <button class="btn btn-outline-primary rounded-4 px-3 py-2 fw-bold" style="font-size: 0.85rem;" onclick="window.location.reload()">
                <i class="fas fa-sync-alt me-2"></i> Atualizar Dados
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
            <div class="alert alert-<?= $insight['type'] === 'danger' ? 'danger' : 'primary' ?> border-0 shadow-sm rounded-4 d-flex align-items-center m-0" style="background-color: <?= $insight['type'] === 'danger' ? '#fef2f2' : '#eff6ff' ?>;">
                <i class="fas <?= $insight['icon'] ?> fa-2x me-3 <?= $insight['type'] === 'danger' ? 'text-danger' : 'text-primary' ?> opacity-75"></i>
                <div>
                    <h6 class="fw-bold mb-1 text-dark"><?= $insight['title'] ?></h6>
                    <span class="small mb-0 text-muted"><?= $insight['description'] ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- SECTION 2: Saúde Financeira e Metas (O Caixa) -->
    <div class="mb-5 p-4 rounded-4" style="background-color: #ffffff; border: 1px solid #e5e7eb; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
        <h5 class="fw-bold mb-4 d-flex align-items-center text-dark">
            <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-size: 1rem;">1</span>
            Desempenho Financeiro e Metas
        </h5>
        <div class="row g-4">
            <!-- Vendas Meta -->
            <div class="col-md-4">
                <a href="relatorios.php" class="clickable-card hover-lift">
                    <div class="exec-card" style="box-shadow: none; border-color: #f3f4f6; background-color: #f8fafc;">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="metric-label">Meta de Faturamento</span>
                            <i class="fas fa-bullseye text-primary bg-primary-subtle p-2 rounded-circle"></i>
                        </div>
                        <div class="metric-value mb-1">R$ <?= number_format($metas_exec['vendas']['atual'], 2, ',', '.') ?></div>
                        <p class="small text-muted fw-bold mb-3">de meta calculada R$ <?= number_format($metas_exec['vendas']['meta'], 2, ',', '.') ?></p>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span>Atingimento</span>
                                <span class="text-primary"><?= number_format($metas_exec['vendas']['progresso_percent'], 1) ?>%</span>
                            </div>
                            <div class="progress-track"><div class="progress-fill bg-gradient-primary" style="width: <?= $metas_exec['vendas']['progresso_percent'] ?>%"></div></div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Saúde Financeira -->
            <div class="col-md-4">
                <a href="relatorios_avancados.php" class="clickable-card hover-lift">
                    <div class="exec-card dark-panel border-0 shadow-lg" style="background: linear-gradient(135deg, #111827 0%, #1f2937 100%);">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="metric-label has-tooltip" style="color: #9ca3af;" title="Calculado sobre os últimos 30 dias, subtraindo o preço de custo histórico cadastrado nos lotes.">Margem Liquida Estimada <i class="fas fa-circle-question opacity-50 ms-1"></i></span>
                            <i class="fas fa-hand-holding-dollar text-success bg-white bg-opacity-10 p-2 rounded-circle"></i>
                        </div>
                        <div class="metric-value mb-1 text-white"><?= number_format($exec_health['margem_lucro'], 1) ?>%</div>
                        <div class="trend-up mt-2 text-success has-tooltip" title="Retorno Sobre o Investimento: Demonstra o lucro percentual sobre o dinheiro que você gastou neste estoque vendido (30 dias)."><i class="fas fa-chart-line me-1"></i> ROI Retorno: <?= number_format($exec_health['roi'], 1) ?>% <i class="fas fa-circle-question opacity-50 small ms-1"></i></div>
                        <p class="small mt-auto pt-3 mb-0 border-top border-secondary" style="color: #9ca3af;">Últimos 30 dias de vendas vs custo.</p>
                    </div>
                </a>
            </div>

            <!-- Inadimplência -->
            <div class="col-md-4">
                <a href="../../financeiro/" class="clickable-card hover-lift" title="Ir para módulo Financeiro">
                    <div class="exec-card" style="box-shadow: none; border-color: #f3f4f6; background-color: #fef2f2;">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="metric-label text-danger">Risco Financeiro</span>
                            <i class="fas fa-triangle-exclamation text-danger bg-white p-2 rounded-circle shadow-sm"></i>
                        </div>
                        <div class="metric-value mb-1 text-danger">R$ <?= number_format($fin_kpis['receivables']['overdue'], 2, ',', '.') ?></div>
                        <p class="small text-danger fw-bold mb-3 opacity-75">Títulos Vencidos (Em Aberto)</p>
                        <div class="mt-auto pt-3 border-top border-danger border-opacity-25 d-flex justify-content-between align-items-center">
                            <span class="small fw-bold text-danger opacity-75">A Receber Total:</span>
                            <span class="fw-bold text-danger">R$ <?= number_format($fin_kpis['receivables']['total_pending'], 2, ',', '.') ?></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- SECTION 3: Motor de Crescimento (CRM) -->
    <div class="mb-5 p-4 rounded-4" style="background-color: #f5f8ff; border: 1px dashed rgba(0, 64, 176, 0.2);">
        <h5 class="fw-bold mb-4 d-flex align-items-center text-dark">
            <span class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-size: 1rem;">2</span>
            Geração de Demanda e CRM
        </h5>
        <div class="row g-4 align-items-center">
            <!-- Projetos Conversão -->
            <div class="col-lg-4">
                <a href="../../modules/crm/views/kanban.php" class="clickable-card hover-lift" title="Acessar Pipeline do CRM">
                    <div class="exec-card border-0 bg-white shadow-sm">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="metric-label">Taxa de Eficiência (Win-Rate)</span>
                            <i class="fas fa-trophy text-warning bg-warning bg-opacity-10 p-2 rounded-circle"></i>
                        </div>
                        <div class="metric-value mb-1"><?= number_format($crm_projects['win_rate'], 1) ?>%</div>
                        <p class="small text-muted fw-bold mb-3">Negócios Fechados com Sucesso</p>
                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="small fw-bold text-muted">Pipeline Total Ativo:</span>
                            <span class="fw-bold text-primary">R$ <?= number_format($crm_kpis['deals_value'], 2, ',', '.') ?></span>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Funnel Preview -->
            <div class="col-lg-8">
                <a href="../../modules/crm/views/kanban.php" class="clickable-card text-decoration-none">
                    <div class="exec-card border-0 bg-transparent p-0" style="box-shadow: none;">
                        <div class="funnel-stage shadow-sm bg-white" style="border-left: 4px solid #9ca3af;">
                            <span class="fw-bold text-muted"><i class="fas fa-search me-2"></i> 1. Prospecção (<?= $crm_projects['funil']['prospeccao']['qtd'] ?>)</span>
                            <strong class="text-dark">R$ <?= number_format($crm_projects['funil']['prospeccao']['valor'], 0, ',', '.') ?></strong>
                        </div>
                        <div class="funnel-stage shadow-sm bg-white" style="width: 90%; margin: 0 auto 0.75rem auto; border-left: 4px solid var(--brand-blue);">
                            <span class="fw-bold text-primary"><i class="fas fa-comments me-2"></i> 2. Negociação (<?= $crm_projects['funil']['negociacao']['qtd'] ?>)</span>
                            <strong class="text-primary">R$ <?= number_format($crm_projects['funil']['negociacao']['valor'], 0, ',', '.') ?></strong>
                        </div>
                        <div class="funnel-stage shadow-sm bg-success-subtle" style="width: 80%; margin: 0 auto; border-left: 4px solid var(--brand-green);">
                            <span class="fw-bold text-success"><i class="fas fa-check-double me-2"></i> 3. Fechados / Ganhos (<?= $crm_projects['funil']['ganho']['qtd'] ?>)</span>
                            <strong class="text-success">R$ <?= number_format($crm_projects['funil']['ganho']['valor'], 0, ',', '.') ?></strong>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- SECTION 4: Relatório Analítico e Operações -->
    <div class="mb-4">
        <h5 class="fw-bold mb-4 d-flex align-items-center text-dark">
            <span class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-size: 1rem;">3</span>
            Histórico P&L e Logística
        </h5>
        <div class="row g-4">
            <!-- Main Chart -->
            <div class="col-lg-8">
                <div class="exec-card p-4 shadow-sm border-0">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Evolução do P&L (Lucros & Perdas)</h6>
                            <p class="text-muted small mb-0">Cruza receitas vs custos e desenha o lucro histórico no ano</p>
                        </div>
                    </div>
                    <div style="height: 380px;"><canvas id="salesChart"></canvas></div>
                </div>
            </div>

            <!-- Logistics -->
            <div class="col-lg-4">
                <a href="../registrar_compra.php" class="clickable-card hover-lift" title="Verificar pendências de compras e recepção">
                    <div class="exec-card border-0 shadow-sm bg-success-subtle">
                        <h6 class="fw-bold border-bottom border-success border-opacity-25 pb-3 mb-4 text-success"><i class="fas fa-truck-fast me-2"></i>Logística & Entregas</h6>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between small fw-bold mb-2">
                                <span class="text-success">Nível de Serviço (Fulfillment)</span> 
                                <span class="text-success fs-5"><?= $operacoes['fulfillment_rate'] ?>%</span>
                            </div>
                            <div class="progress-track bg-white"><div class="progress-fill bg-gradient-success" style="width: <?= $operacoes['fulfillment_rate'] ?>%"></div></div>
                            <p class="text-muted small mt-2" style="color: #166534 !important;">Capacidade da empresa de entregar pedidos no prazo.</p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-4 bg-white rounded-4 shadow-sm mt-auto">
                            <div>
                                <div class="small fw-bold text-muted mb-1">Compras na Recepção</div>
                                <div class="h3 fw-bold mb-0 text-dark"><?= $operacoes['compras_pendentes'] ?> Lote(s)</div>
                            </div>
                            <div class="rounded-circle p-3 <?= $operacoes['compras_pendentes'] > 0 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success' ?>">
                                <i class="fas <?= $operacoes['compras_pendentes'] > 0 ? 'fa-box-open' : 'fa-check-double' ?> fa-2x"></i>
                            </div>
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
            
            const fillGradient = ctx.createLinearGradient(0, 0, 0, 400);
            fillGradient.addColorStop(0, 'rgba(0, 64, 176, 0.15)');
            fillGradient.addColorStop(1, 'rgba(0, 64, 176, 0)');

            salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            type: 'line',
                            label: 'Lucro (R$)',
                            data: profitData,
                            borderColor: '#10b981', /* M3 Green */
                            borderWidth: 3,
                            fill: false,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#fff',
                            pointBorderWidth: 2,
                            yAxisID: 'y'
                        },
                        {
                            type: 'bar',
                            label: 'Receita (R$)',
                            data: salesData,
                            backgroundColor: '#2563eb', /* Generic Blue */
                            borderRadius: 6,
                            barPercentage: 0.6,
                            yAxisID: 'y'
                        },
                        {
                            type: 'bar',
                            label: 'Custos (R$)',
                            data: costData,
                            backgroundColor: '#e5e7eb',
                            borderRadius: 6,
                            barPercentage: 0.6,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', align: 'end', labels: { boxWidth: 12, usePointStyle: true, font: { weight: '600', family: 'Inter' } } },
                        tooltip: { backgroundColor: '#121212', padding: 12, borderRadius: 8, titleFont: { size: 14, family: 'Inter' } }
                    },
                    scales: {
                        y: { grid: { color: '#f3f4f6', drawBorder: false }, ticks: { font: {family: 'Inter'}, callback: v => 'R$ ' + (v/1000).toFixed(0) + 'k' } },
                        x: { grid: { display: false }, ticks: { font: {family: 'Inter', weight: 'bold'} } }
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
