<?php
require_once 'includes/db_config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $stmt = $pdo->query("SHOW CREATE TABLE empresas");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($result);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
