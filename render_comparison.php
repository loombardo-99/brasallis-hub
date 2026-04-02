<?php
// Root directory
$rootDir = 'c:/Users/jrlom/OneDrive/Área de Trabalho/TCC 2025/gerenciador_de_estoque';
require_once $rootDir . '/vendor/autoload.php';

// Mocking session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['empresa_id'] = 1;
$_SESSION['username'] = 'TestUser';
$_SESSION['user_type'] = 'admin';
$_SESSION['plano'] = 'basico';
$_SESSION['empresa_nome'] = 'Brasallis Test';

echo "--- LEGACY DASHBOARD RENDER ---\n";
ob_start();
// Mocking AdminController dependency
$dashboardRepo = null; 
require_once $rootDir . '/src/Controllers/AdminController.php';
// We just want to see what 'views/admin/dashboard.php' does
include_once $rootDir . '/includes/cabecalho.php';
$htmlLegacy = ob_get_clean();
analyze($htmlLegacy);

echo "\n--- MODERN DASHBOARD RENDER ---\n";
ob_start();
// Modern requires BASE_PATH
define('BASE_PATH', $rootDir);
$kpis = ['revenue_today' => ['current' => 100], 'low_stock_items' => ['current' => 5]];
$ultimas_compras = [];
$layout = [];
$empresa_nome = 'Brasallis Modern';
$username = 'ModernUser';
$chart_labels = '[]'; $chart_sales = '[]'; $chart_cost = '[]'; $chart_forecast = '[]';
$userInitials = 'MU';

require BASE_PATH . '/resources/views/layouts/header.php';
$htmlModern = ob_get_clean();
analyze($htmlModern);

function analyze($html) {
    preg_match_all('/logo\.svg/i', $html, $logos);
    preg_match_all('/fa-bars/i', $html, $bars);
    echo "Logos: " . count($logos[0]) . " | Bars: " . count($bars[0]) . "\n";
    
    // Show snippets for context
    if (count($logos[0]) > 1) {
        echo "Multiple logos detected!\n";
    }
    if (count($bars[0]) > 1) {
        echo "Multiple bars detected!\n";
    }
}
?>
