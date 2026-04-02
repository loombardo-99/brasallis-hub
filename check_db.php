<?php
require_once __DIR__ . '/includes/funcoes.php';
$conn = connect_db();
$stmt = $conn->query("DESCRIBE setores");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
