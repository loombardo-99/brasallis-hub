<?php
// debug_auth.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/includes/db_config.php';

echo "<h1>Debug de Autenticação</h1>";

// 1. Check Database Connection
echo "<h2>1. Conexão com Banco de Dados</h2>";
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>Conexão OK!</p>";
} catch (PDOException $e) {
    die("<p style='color:red'>Erro de Conexão: " . $e->getMessage() . "</p>");
}

// 2. Check Session
echo "<h2>2. Sessão</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";
echo "<p>Conteúdo da Sessão:</p><pre>" . print_r($_SESSION, true) . "</pre>";

// 3. List Users
echo "<h2>3. Usuários no Banco</h2>";
$stmt = $conn->query("SELECT id, username, email, user_type, password FROM usuarios");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "<p style='color:red'>Nenhum usuário encontrado!</p>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Type</th><th>Password Hash (Prefix)</th></tr>";
    foreach ($users as $u) {
        echo "<tr>";
        echo "<td>{$u['id']}</td>";
        echo "<td>{$u['username']}</td>";
        echo "<td>{$u['email']}</td>";
        echo "<td>{$u['user_type']}</td>";
        echo "<td>" . substr($u['password'], 0, 10) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Test Login Form
echo "<h2>4. Teste de Login (Simulação)</h2>";
?>
<form method="POST" style="background:#f0f0f0; padding:20px; border:1px solid #ccc;">
    <p>Teste se a senha bate com o hash (sem redirecionar):</p>
    Email: <input type="text" name="test_email" value="admin@teste.com">
    Senha: <input type="text" name="test_pass" value="123456">
    <button type="submit" name="action" value="test_login">Testar Login</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'test_login') {
    $email = $_POST['test_email'];
    $pass = $_POST['test_pass']; // Raw password, assuming sanitize mimics this or we test raw

    // Simulate sanitize from funcoes.php
    $sanitized_pass = htmlspecialchars(stripslashes(trim($pass)));
    
    echo "<h3>Resultado do Teste:</h3>";
    echo "Email: $email<br>";
    echo "Senha Digitada: $pass<br>";
    echo "Senha Sanitizada (como no login.php): $sanitized_pass<br>";

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "Usuário encontrado!<br>";
        if (password_verify($sanitized_pass, $user['password'])) {
            echo "<p style='color:green; font-weight:bold;'>SUCESSO: Senha CORRETA!</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>FALHA: Senha INCORRETA.</p>";
            echo "Hash no banco: " . $user['password'] . "<br>";
            echo "Hash gerado agora (para comparação): " . password_hash($sanitized_pass, PASSWORD_DEFAULT) . "<br>";
        }
    } else {
        echo "<p style='color:red'>Usuário NÃO encontrado.</p>";
    }
}

// 5. Reset Password Tool
echo "<h2>5. Ferramenta de Reset de Senha</h2>";
?>
<form method="POST" style="background:#ffebee; padding:20px; border:1px solid #ffcdd2;">
    <p>Forçar nova senha para um usuário:</p>
    ID do Usuário: <input type="number" name="reset_id" style="width:50px">
    Nova Senha: <input type="text" name="new_pass" value="123456">
    <button type="submit" name="action" value="force_reset">Redefinir Senha</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'force_reset') {
    $id = $_POST['reset_id'];
    $new_pass = $_POST['new_pass'];
    
    // Sanitize as per system logic
    $sanitized_new_pass = htmlspecialchars(stripslashes(trim($new_pass)));
    $hash = password_hash($sanitized_new_pass, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE usuarios SET password = :p WHERE id = :id");
    if ($stmt->execute([':p' => $hash, ':id' => $id])) {
        echo "<p style='color:green'>Senha atualizada com sucesso para o ID $id. Nova senha: $new_pass</p>";
    } else {
        echo "<p style='color:red'>Erro ao atualizar.</p>";
    }
}
?>
