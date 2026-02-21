<?php

require_once __DIR__ . '/includes/functions.php';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitiza o e-mail de entrada
    $email = sanitize_input($_POST['email']);

    // Conecta-se ao banco de dados
    $conn = connect_db();

    if ($conn) {
        try {
            // Verifica se o e-mail existe na tabela de usuários
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                // Gera um código de 6 dígitos
                $code = substr(str_shuffle("0123456789"), 0, 6);

                // Armazena o código no banco de dados
                $stmt = $conn->prepare("INSERT INTO redefinicoes_senha (email, code) VALUES (:email, :code) ON DUPLICATE KEY UPDATE code = :code");
                $stmt->execute([
                    ':email' => $email,
                    ':code' => $code
                ]);

                // Simula o envio de e-mail exibindo o código na tela
                header('Location: forgot_password.php?success=Um código de 6 dígitos foi enviado para o seu e-mail (simulado).&code=' . $code);
                exit();

            } else {
                header('Location: esqueceu_senha.php?error=Nenhum usuário encontrado com este endereço de e-mail.');
                exit();
            }
        } catch (PDOException $e) {
            header('Location: forgot_password.php?error=Erro no servidor. Tente novamente mais tarde.');
            error_log("Erro de redefinição de senha: " . $e->getMessage());
            exit();
        }
    }
}

?>