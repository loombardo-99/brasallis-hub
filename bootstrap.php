<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/db_config.php';

// --- CONFIGURAÇÕES DE SEGURANÇA DE SESSÃO ---
if (session_status() === PHP_SESSION_NONE) {
    // Configura cookies de sessão para serem mais seguros
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);

    session_start();
}

// --- HELPER DE PREVENÇÃO DE XSS ---
/**
 * Atalho para htmlspecialchars para proteção contra XSS.
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Gera um campo hidden com o token CSRF.
 */
function csrf_field() {
    // Session is now started at the top of bootstrap.php
    
    // Gera se não existir (redundância com o middleware)
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return '<input type="hidden" name="_csrf_token" value="' . $_SESSION['_csrf_token'] . '">';
}

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
    // Session is now started at the top of bootstrap.php
    
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
