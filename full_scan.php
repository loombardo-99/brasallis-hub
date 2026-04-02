<?php
$rootDir = 'c:/Users/jrlom/OneDrive/Área de Trabalho/TCC 2025/gerenciador_de_estoque';
$dir = new RecursiveDirectoryIterator($rootDir);
$iterator = new RecursiveIteratorIterator($dir);

$results = [];

foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (strpos($path, 'vendor') !== false || strpos($path, '.git') !== false || strpos($path, 'node_modules') !== false) continue;
    if (pathinfo($path, PATHINFO_EXTENSION) !== 'php' && pathinfo($path, PATHINFO_EXTENSION) !== 'css') continue;
    
    $content = file_get_contents($path);
    $bars = substr_count($content, 'fa-bars');
    $logos = substr_count($content, 'logo.svg');
    
    if ($bars > 0 || $logos > 0) {
        $relPath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $path);
        $results[] = "File: $relPath | Bars: $bars | Logos: $logos";
    }
}

sort($results);
echo implode("\n", $results) . "\n";
?>
