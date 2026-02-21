<?php
require_once 'vendor/autoload.php';
use App\Database;

header('Content-Type: text/plain');

$conn = Database::getInstance()->getConnection();

echo "--- VENDA ITENS (Last 10) ---\n";
$stmt = $conn->query("SELECT * FROM venda_itens ORDER BY id DESC LIMIT 10");
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($itens);

echo "\n--- COUNT ITENS BY VENDA ID ---\n";
$stmt = $conn->query("SELECT venda_id, COUNT(*) as total_itens FROM venda_itens GROUP BY venda_id ORDER BY venda_id DESC LIMIT 10");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- CHECK ORPHAN VENDAS ---\n";
$stmt = $conn->query("SELECT v.id FROM vendas v LEFT JOIN venda_itens vi ON v.id = vi.venda_id WHERE vi.id IS NULL");
$orphans = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Vendas without items: " . implode(', ', $orphans) . "\n";
?>
