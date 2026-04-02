<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['empresa_id'] = 1;
$_SESSION['username'] = 'Test';
$_SESSION['user_type'] = 'admin';
$_SESSION['plano'] = 'basico';

ob_start();
include 'includes/cabecalho.php';
$html = ob_get_clean();

$bars = substr_count($html, 'fa-bars');
$logos = substr_count($html, 'logo.svg');
echo "fa-bars count: $bars\n";
echo "logo.svg count: $logos\n";

if ($bars > 1) {
    echo "\nContext of fa-bars:\n";
    $parts = explode('fa-bars', $html);
    foreach($parts as $i => $part) {
        if ($i > 0) {
            echo "Match $i: ... " . substr($parts[$i-1], -50) . " fa-bars " . substr($part, 0, 50) . " ...\n";
        }
    }
}

if ($logos > 1) {
    echo "\nContext of logo.svg:\n";
    $parts = explode('logo.svg', $html);
    foreach($parts as $i => $part) {
        if ($i > 0) {
            echo "Match $i: ... " . substr($parts[$i-1], -50) . " logo.svg " . substr($part, 0, 50) . " ...\n";
        }
    }
}
?>
