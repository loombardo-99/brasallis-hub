<?php
require_once __DIR__ . '/includes/db_config.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = 'admin@teste.com'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User Found:\n";
        print_r($user);
        
        if (is_null($user['empresa_id'])) {
            echo "\nWARNING: empresa_id is NULL!\n";
        } else {
            echo "\nempresa_id is set to: " . $user['empresa_id'] . "\n";
        }
    } else {
        echo "User admin@teste.com not found.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
