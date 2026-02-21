<?php
require_once 'vendor/autoload.php';
use App\Database;

header('Content-Type: text/plain');
$conn = Database::getInstance()->getConnection();

echo "--- COMPRAS FIELDS ---\n";
$stmt = $conn->query("DESCRIBE compras");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . "\n";
}
?>
