<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/db_config.php';

use App\Database;
use App\DashboardRepository;

// Simple Dependency Injection Container (Service Locator)
$container = [];

// Database Connection Service
$container['db'] = function() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database Connection Error: " . $e->getMessage());
    }
};

// Repository Services
$container[DashboardRepository::class] = function($c) {
    // We need the empresa_id to instantiate the repository.
    // In a real framework, we would have a Request object or Session service.
    // For now, we'll assume the session is started and available.
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $empresa_id = $_SESSION['empresa_id'] ?? null;
    
    if (!$empresa_id) {
        // Handle case where there is no logged in user/company
        // For now, we might return null or throw an exception depending on usage
        return null; 
    }

    return new DashboardRepository($empresa_id);
};

// Helper function to get services
function resolve($key) {
    global $container;
    if (isset($container[$key])) {
        return $container[$key]($container);
    }
    throw new Exception("Service not found: " . $key);
}

return $container;
