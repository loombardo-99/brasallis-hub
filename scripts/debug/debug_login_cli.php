<?php
require_once __DIR__ . '/includes/db_config.php';

echo "Testing Database Connection...\n";
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection Successful!\n";
} catch (PDOException $e) {
    echo "Connection Failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nChecking Users...\n";
$stmt = $conn->query("SELECT id, username, email, user_type, password FROM usuarios");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "No users found in the database.\n";
} else {
    echo "Found " . count($users) . " users:\n";
    foreach ($users as $user) {
        echo "- ID: {$user['id']}, User: {$user['username']}, Email: {$user['email']}, Type: {$user['user_type']}\n";
    }
}

echo "\nSession Save Path: " . session_save_path() . "\n";
echo "Session Status: " . session_status() . "\n";
?>
