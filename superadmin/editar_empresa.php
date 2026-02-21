<?php
// superadmin/editar_empresa.php
session_start();
require_once '../includes/funcoes.php';

// Proteção
checkSuperAdmin();

$conn = connect_db();
$id = $_GET['id'] ?? 0;
$message = '';

// Buscar dados da empresa
$stmt = $conn->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt->execute([$id]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    header("Location: empresas.php");
    exit;
}

// Processar Atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan = $_POST['ai_plan'];
    $status = $_POST['status'] ?? 'active'; // Se river status na tabela
    
    // Configurar limites baseado no novo plano
    $limits = [
        'free' => ['tokens' => 100000, 'users' => 1, 'support' => 'community'],
        'growth' => ['tokens' => 2000000, 'users' => 5, 'support' => 'priority'],
        'enterprise' => ['tokens' => 10000000, 'users' => 999, 'support' => 'dedicated'],
    ];

    $new_limits = $limits[$plan];

    $update = $conn->prepare("UPDATE empresas SET ai_plan = ?, ai_token_limit = ?, max_users = ?, support_level = ? WHERE id = ?");
    
    if ($update->execute([$plan, $new_limits['tokens'], $new_limits['users'], $new_limits['support'], $id])) {
        $message = "Empresa atualizada com sucesso!";
        // Recarregar dados
        $stmt->execute([$id]);
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = "Erro ao atualizar.";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empresa | Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-4">
                <a href="empresas.php" class="btn btn-light rounded-circle me-3"><i class="fas fa-arrow-left"></i></a>
                <h2 class="fw-bold m-0">Editar Empresa</h2>
            </div>

            <?php if($message): ?>
                <div class="alert alert-success rounded-3 mb-4"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-4 border-bottom">
                    <h5 class="fw-bold m-0"><?php echo htmlspecialchars($empresa['name']); ?></h5>
                    <small class="text-muted">ID: #<?php echo $empresa['id']; ?></small>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted">Plano de Assinatura</label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="ai_plan" id="plan_free" value="free" <?php echo $empresa['ai_plan'] === 'free' ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-secondary w-100 p-3 rounded-3" for="plan_free">
                                        Free
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="ai_plan" id="plan_growth" value="growth" <?php echo $empresa['ai_plan'] === 'growth' ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-success w-100 p-3 rounded-3 fw-bold" for="plan_growth">
                                        Growth
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="ai_plan" id="plan_enterprise" value="enterprise" <?php echo $empresa['ai_plan'] === 'enterprise' ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary w-100 p-3 rounded-3 fw-bold" for="plan_enterprise">
                                        Enterprise
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Limite de Tokens (Automático)</label>
                                <input type="text" class="form-control bg-light" value="<?php echo number_format($empresa['ai_token_limit']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Máximo de Usuários</label>
                                <input type="text" class="form-control bg-light" value="<?php echo $empresa['max_users']; ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch p-0">
                                <label class="form-check-label fw-bold ms-5" for="blockSwitch">Bloquear Acesso da Empresa</label>
                                <input class="form-check-input ms-0" type="checkbox" id="blockSwitch" style="width: 3em; height: 1.5em;" disabled>
                                <small class="d-block text-muted mt-1">Funcionalidade de bloqueio em desenvolvimento.</small>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
