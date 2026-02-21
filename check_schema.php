<?php
require 'includes/db_config.php';
require 'includes/funcoes.php';

$conn = connect_db();
$stmt = $conn->query("DESCRIBE historico_estoque");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Columns in historico_estoque:\n";
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
?>
