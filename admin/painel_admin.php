<?php

// Bootstrap da aplicação
$container = require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\AdminController;
use App\DashboardRepository;

// Verifica se o usuário está logado (redundância, mas útil no legacy)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['empresa_id'])) {
    header('Location: ../login.php');
    exit;
}

// Injeção de dependência simples
try {
    $dashboardRepo = resolve(DashboardRepository::class);
} catch (Exception $e) {
    $dashboardRepo = null; // Controller tentará o fallback
}

// Instancia e executa o Controller
$adminController = new AdminController($dashboardRepo);
$adminController->index();
