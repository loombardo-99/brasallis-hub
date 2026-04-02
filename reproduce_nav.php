<?php
// Mocking session and environment
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['empresa_id'] = 1;
$_SESSION['username'] = 'TestUser';
$_SESSION['user_type'] = 'admin';
$_SESSION['plano'] = 'basico';
$_SESSION['empresa_nome'] = 'Brasallis Test';

// Mocking function dependencies if needed (funcoes.php is required by cabecalho.php)
// cabecalho.php line 5: require_once __DIR__ . '/funcoes.php';

// Capture output
ob_start();
require_once 'includes/cabecalho.php';
// We don't even need the dashboard, just the header is where the navbar is.
$html = ob_get_clean();

// Analysis
echo "--- HTML ANALYSIS ---\n";
$logos = [];
preg_match_all('/<img[^>]+logo\.svg[^>]*>/i', $html, $logos);
echo "Logos found: " . count($logos[0]) . "\n";
foreach($logos[0] as $i => $l) {
    echo "Logo " . ($i+1) . ": " . htmlspecialchars($l) . "\n";
}

$bars = [];
preg_match_all('/<i[^>]+fa-bars[^>]*>/i', $html, $bars);
echo "\nMenu icons (fa-bars) found: " . count($bars[0]) . "\n";
foreach($bars[0] as $i => $b) {
    echo "Bar " . ($i+1) . ": " . htmlspecialchars($b) . "\n";
}

// Check for duplicated includes
$headers = substr_count($html, '<header>');
echo "\n<header> tags count: $headers\n";

$navs = substr_count($html, '<nav');
echo "<nav tags count: $navs\n";

file_put_contents('rendered_output.html', $html);
echo "\nFull HTML saved to rendered_output.html\n";
?>
