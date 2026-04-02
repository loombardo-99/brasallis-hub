<?php
$rootDir = __DIR__;

// Mock context
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1;
$_SESSION['empresa_id'] = 1;
$_SESSION['username'] = 'Admin';
$_SESSION['user_type'] = 'admin';

ob_start();
try {
    require_once 'admin/configuracoes.php';
} catch (Throwable $e) {
    echo "SHIM_TEST_ERROR: " . $e;
}
$output = ob_get_clean();

echo "OUTPUT_LENGTH: " . strlen($output) . "\n";
echo "CONTAINS_TITLE: " . (strpos($output, 'Configurações') !== false ? 'YES' : 'NO') . "\n";


