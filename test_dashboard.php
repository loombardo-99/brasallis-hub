<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['empresa_id'] = 1;
$_SESSION['username'] = 'Test';
$_SESSION['user_type'] = 'admin';
$_SESSION['plano'] = 'basico';

ob_start();
require __DIR__ . '/admin/painel_admin.php';
$html = ob_get_clean();

$bars = substr_count($html, 'fa-bars');
$logos = substr_count($html, 'logo.svg');
echo "fa-bars count: $bars\n";
echo "logo.svg count: $logos\n";
?>
