<?php
require 'includes/db_config.php';
require 'includes/funcoes.php';

$conn = connect_db();
$stmt = $conn->query("SELECT * FROM modulos");
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "ID | Nome | Slug\n";
echo "---|---|---\n";
foreach ($modules as $m) {
    echo "{$m['id']} | {$m['nome']} | {$m['slug']}\n";
}
?>
