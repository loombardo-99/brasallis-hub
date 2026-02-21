<?php
// admin/check_status.php
require_once '../includes/funcoes.php';

header('Content-Type: application/json');

$id = $_GET['id'];
$conn = connect_db();

$stmt = $conn->prepare("SELECT status FROM pagamentos WHERE id = ?");
$stmt->execute([$id]);
$status = $stmt->fetchColumn();

echo json_encode(['approved' => ($status === 'approved')]);
