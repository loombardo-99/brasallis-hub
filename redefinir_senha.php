<?php

include_once 'includes/cabecalho.php';
require_once 'includes/functions.php';

// Verifica se o e-mail foi fornecido
if (!isset($_GET['email'])) {
    header('Location: index.php');
    exit();
}

// Sanitiza o e-mail
$email = sanitize_input($_GET['email']);

// Se o formulário de redefinição de senha foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitiza a nova senha
    $password = sanitize_input($_POST['password']);

    // Conecta-se ao banco de dados
    $conn = connect_db();

    if ($conn) {
        try {
            // Gera o hash da nova senha
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Atualiza a senha do usuário no banco de dados
            $stmt = $conn->prepare("UPDATE usuarios SET password = :password WHERE email = :email");
            $stmt->execute([
                ':password' => $hashed_password,
                ':email' => $email
            ]);

            // Exclui o registro de redefinição de senha do banco de dados
            $stmt = $conn->prepare("DELETE FROM redefinicoes_senha WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Redireciona para a página de login com uma mensagem de sucesso
            header('Location: index.php?success=Sua senha foi redefinida com sucesso!');
            exit();

        } catch (PDOException $e) {
            header('Location: esqueceu_senha.php?error=Erro no servidor. Tente novamente mais tarde.');
            error_log("Erro de redefinição de senha: " . $e->getMessage());
            exit();
        }
    }
}

?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="card-title">Redefinir Senha</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="password" class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Redefinir Senha</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/rodape.php'; ?>