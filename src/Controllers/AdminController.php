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
        // Run automations if needed
        if (file_exists(__DIR__ . '/../../admin/run_automations.php')) {
            require_once __DIR__ . '/../../admin/run_automations.php';
            runNotificationAutomations($this->dashboardRepo->getConnection(), $this->empresa_id);
        }

        try {
            $dashboardRepo = $this->dashboardRepo;
            $empresa_id = $this->empresa_id;

            // Fetch Data
            $kpis = $dashboardRepo->getDashboardKPIs();
            $avisos = $dashboardRepo->getConnection()->query("SELECT * FROM avisos_globais WHERE active = 1 ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
            $produtos_validade = $dashboardRepo->getProdutosProximosValidade(5);
            $ultimas_compras = $dashboardRepo->getUltimasCompras(5);
            $chart_data = $dashboardRepo->getSalesAndProfitOverTime('month');
            $forecast_data = $dashboardRepo->getSalesForecast(7);

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
