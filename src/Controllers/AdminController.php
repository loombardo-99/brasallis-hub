<?php

namespace App\Controllers;

use App\DashboardRepository;
use Exception;
use Throwable;

class AdminController
{
    private $dashboardRepo;
    private $empresa_id;

    public function __construct(DashboardRepository $dashboardRepo = null)
    {
        // Require authentication
        $this->checkAuth();
        
        $this->empresa_id = $_SESSION['empresa_id'];
        
        // Use injected repo or create new one (fallback for legacy/direct usage)
        $this->dashboardRepo = $dashboardRepo ?? new DashboardRepository($this->empresa_id);
    }

    private function checkAuth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['empresa_id'])) {
            header('Location: ../login.php');
            exit;
        }
    }

    public function index()
    {
        // --- 1. SMART REDIRECT FOR EMPLOYEES ---
        // Se for funcionário, não vê o Dashboard Executivo por padrão.
        if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'employee') {
            $moduleUrl = $this->getRecommendedModule();
            if ($moduleUrl) {
                header("Location: $moduleUrl");
                exit;
            } else {
                // Fallback para página de "Acesso Pendente" se não tiver nenhuma permissão
                header("Location: /admin/dashboard_funcionario.php");
                exit;
            }
        }

        // Run automations loop for Admins
        if (file_exists(__DIR__ . '/../../admin/run_automations.php')) {
            require_once __DIR__ . '/../../admin/run_automations.php';
            runNotificationAutomations($this->dashboardRepo->getConnection(), $this->empresa_id);
        }

        try {
            $dashboardRepo = $this->dashboardRepo;
            $empresa_id = $this->empresa_id;

            // Fetch Data
            $kpis = $dashboardRepo->getDashboardKPIs();
            $crm_kpis = $dashboardRepo->getCRMKPIs();
            $fin_kpis = $dashboardRepo->getFinancialKPIs();
            
            $exec_health = $dashboardRepo->getExecutiveHealth();
            $metas_exec = $dashboardRepo->getMetasExecutivas();
            $crm_projects = $dashboardRepo->getProjectCompletionBoard();
            $operacoes = $dashboardRepo->getOperationsLogistics();
            
            $avisos = $dashboardRepo->getConnection()->query("SELECT * FROM avisos_globais WHERE active = 1 ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
            $insights = $dashboardRepo->getDashboardInsights();
            
            $produtos_validade = $dashboardRepo->getProdutosProximosValidade(5);
            $ultimas_compras = $dashboardRepo->getUltimasCompras(5);
            $chart_data = $dashboardRepo->getSalesAndProfitOverTime('month');
            $forecast_data = $dashboardRepo->getSalesForecast(7);
            
            // Controle de Acesso: Funcionários Ativos
            $active_employees_count = $dashboardRepo->getConnection()->query("SELECT COUNT(*) FROM usuarios WHERE empresa_id = {$empresa_id} AND user_type = 'employee'")->fetchColumn();

            // Validação de fallback para evitar Warnings PHP quebrando a sintaxe JS
            if (!is_array($chart_data)) $chart_data = [];
            if (!is_array($forecast_data) || !isset($forecast_data['forecast'])) $forecast_data = ['forecast' => []];

            // Prepare View Data
            $chart_labels = json_encode(array_column($chart_data, 'label')) ?: '[]';
            $chart_sales = json_encode(array_column($chart_data, 'sales')) ?: '[]';
            $chart_profit = json_encode(array_column($chart_data, 'profit')) ?: '[]';
            $chart_cost = json_encode(array_column($chart_data, 'cost')) ?: '[]';
            $chart_forecast = json_encode($forecast_data['forecast']) ?: '[]';

            $total_sales_period = array_sum(array_column($chart_data, 'sales'));
            $total_profit_period = array_sum(array_column($chart_data, 'profit'));
            
            // Render View
            require __DIR__ . '/../../views/admin/dashboard.php';

        } catch (Throwable $e) {
            $this->renderError($e);
        }
    }

    /**
     * Identifica o módulo recomendado baseado nas permissões do funcionário
     */
    private function getRecommendedModule()
    {
        // Prioridade de direcionamento (Comercial > Operacional > Financeiro > RH)
        $priority = [
            'crm'       => '/modules/crm/views/kanban.php',
            'pdv'       => '/modules/pdv/views/index.php',
            'estoque'   => '/admin/produtos.php',
            'financeiro'=> '/modules/financeiro/views/index.php',
            'rh'        => '/modules/rh/views/index.php',
            'fiscal'    => '/admin/inteligencia_tributaria.php'
        ];

        foreach ($priority as $slug => $url) {
            // Reutiliza a lógica global de check_permission se disponível, 
            // mas aqui checamos direto o array de sessão para performance no controller
            $perms = $_SESSION['permissions'] ?? [];
            if (isset($perms[$slug]) && $perms[$slug] !== 'nenhuma') {
                return $url;
            }
        }

        return null;
    }

    private function renderError(Throwable $e)
    {
        echo "<div class='alert alert-danger m-4'>
                <h4>Erro no Dashboard</h4>
                <p>" . $e->getMessage() . "</p>
                <pre>" . $e->getTraceAsString() . "</pre>
              </div>";
        exit;
    }
}
