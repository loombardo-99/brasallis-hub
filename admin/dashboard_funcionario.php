<?php
// admin/dashboard_funcionario.php
session_start();
// NÃO incluir cabecalho.php para evitar menu lateral
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Restrito - Gerenciador de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #F5F5F7; font-family: 'Outfit', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card-auth { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); max-width: 500px; width: 100%; }
        .text-navy { color: #0A2647; }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="card card-auth p-5 text-center bg-white">
        <div class="mb-4 text-warning opacity-75">
            <i class="fas fa-user-lock fa-4x"></i>
        </div>
        
        <h2 class="fw-bold text-navy mb-3">Acesso Pendente</h2>
        
        <p class="text-secondary mb-4">
            Olá, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Colaborador') ?></strong>. 
            <br>Sua conta foi criada, mas ainda não está vinculada a nenhum departamento (Setor).
        </p>

        <div class="alert alert-light border small text-muted mb-4">
            <i class="fas fa-info-circle me-1"></i> Por favor, solicite ao administrador ou gerente para configurar seu <strong>Setor</strong> e <strong>Cargo</strong>.
        </div>

        <a href="../sair.php" class="btn btn-outline-danger w-100 rounded-pill fw-bold">
            <i class="fas fa-sign-out-alt me-2"></i>Sair do Sistema
        </a>
    </div>
</div>

</body>
</html>
