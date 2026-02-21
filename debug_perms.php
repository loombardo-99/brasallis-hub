<?php
require 'includes/db_config.php';
require 'includes/funcoes.php';

$conn = connect_db();

$username = 'Fernando'; // Adjust or pass via GET
if (isset($_GET['user'])) $username = $_GET['user'];

echo "<h1>Debug Permissions for '$username'</h1>";

// 1. Get User
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE username LIKE ?");
$stmt->execute(['%'.$username.'%']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("User not found.");

    echo "User Type: <strong>{$user['user_type']}</strong><br>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";

// 2. Get Role
$stmt = $conn->prepare("SELECT * FROM usuario_setor WHERE user_id = ?");
$stmt->execute([$user['id']]);
$userSetor = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Role Assignment (usuario_setor)</h3>";
if ($userSetor) {
    echo "<pre>";
    print_r($userSetor);
    echo "</pre>";
    
    // 3. Get Role Details
    $stmt = $conn->prepare("SELECT * FROM cargos WHERE id = ?");
    $stmt->execute([$userSetor['cargo_id']]);
    $cargo = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Role: <strong>{$cargo['nome']}</strong> (ID: {$cargo['id']})<br>";

    // 4. Get Role Permissions
    $stmtPerms = $conn->prepare("
        SELECT m.nome, m.slug, pc.nivel_acesso 
        FROM permissoes_cargo pc
        JOIN modulos m ON pc.modulo_id = m.id
        WHERE pc.cargo_id = ?
    ");
    $stmtPerms->execute([$cargo['id']]);
    $perms = $stmtPerms->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Permissions (permissoes_cargo)</h3>";
    echo "<table border='1'><tr><th>Module</th><th>Slug</th><th>Level</th></tr>";
    foreach ($perms as $p) {
        echo "<tr><td>{$p['nome']}</td><td>{$p['slug']}</td><td>{$p['nivel_acesso']}</td></tr>";
    }
    echo "</table>";

} else {
    echo "User has no role assigned.";
}

?>
