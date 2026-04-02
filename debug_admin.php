<?php
$rootDir = __DIR__;
require_once $rootDir . '/vendor/autoload.php';
require_once $rootDir . '/includes/db_config.php';

// Mock Session
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1;
$_SESSION['empresa_id'] = 1;
$_SESSION['username'] = 'Test User';
$_SESSION['user_type'] = 'admin';
$_SESSION['user_plan'] = 'growth';

if (!defined('BASE_PATH')) define('BASE_PATH', $rootDir);

use App\Controllers\AdminController;
use App\DashboardRepository;

ob_start();
try {
    $repo = new DashboardRepository(1);
    $controller = new AdminController($repo);
    $controller->index();
} catch (Throwable $e) {
    echo "RENDER_ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
$html = ob_get_clean();

file_put_contents('debug_render.html', $html);

$lines = explode("\n", $html);
echo "TOTAL_LINES: " . count($lines) . "\n";
if (isset($lines[923])) {
    echo "LINE 924: " . trim($lines[923]) . "\n";
    echo "CONTEXT:\n";
    for ($i = 920; $i <= 930; $i++) {
         if(isset($lines[$i-1])) echo "    $i: " . $lines[$i-1] . "\n";
    }
} else {
    echo "Line 924 not found.\n";
}
