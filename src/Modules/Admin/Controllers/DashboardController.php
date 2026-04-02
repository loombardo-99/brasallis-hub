<?php

namespace App\Modules\Admin\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Modules\Admin\Repositories\DashboardRepository;

/**
 * DashboardController — gerencia a exibição do painel principal.
 */
class DashboardController
{
    public function __construct(private DashboardRepository $repository) {}

    public function index(Request $request, Response $response): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = $_SESSION['user_id'] ?? 0;
        
        $kpis            = $this->repository->getKpis();
        $ultimas_compras = $this->repository->getUltimasCompras();
        $layout          = $this->repository->getLayout($userId);
        $empresa_nome    = $_SESSION['empresa_nome'] ?? 'Minha Empresa';
        $username        = $_SESSION['username'] ?? 'Usuário';

        // Dados para o Gráfico (Stubs para compatibilidade com o JS legado no momento)
        $chart_labels   = json_encode(['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun']);
        $chart_sales    = json_encode([12000, 15000, 11000, 18000, 22000, 20000]);
        $chart_cost     = json_encode([8000, 9000, 7500, 10000, 12000, 11000]);
        $chart_forecast = json_encode([]);

        $response->view('admin/dashboard', compact(
            'kpis', 
            'ultimas_compras', 
            'layout', 
            'empresa_nome', 
            'username',
            'chart_labels',
            'chart_sales',
            'chart_cost',
            'chart_forecast'
        ));
    }
}
