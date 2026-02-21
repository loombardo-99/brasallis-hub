<?php
require_once 'includes/db_config.php';
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    
    $tables = ['vendas', 'itens_venda', 'usuarios'];
    $schema = [];
    
    foreach($tables as $t) {
        $stmt = $conn->query("DESCRIBE $t");
        $schema[$t] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode($schema, JSON_PRETTY_PRINT);
} catch(Exception $e) {
    echo $e->getMessage();
}
