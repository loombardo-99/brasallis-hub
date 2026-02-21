<?php
// superadmin/empresas.php
session_start();
require_once '../includes/funcoes.php';

// Proteção
checkSuperAdmin();

// Conexão
$conn = connect_db();

// Listar todas as empresas
$stmt = $conn->query("SELECT * FROM empresas ORDER BY created_at DESC");
$empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

    <div class="page-header">
        <div>
            <h2 class="page-title">Gestão de Empresas</h2>
            <p class="page-subtitle">Gerencie todos os tenants da plataforma</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold"><i class="fas fa-plus me-2"></i> Nova Empresa (Manual)</button>
    </div>

    <div class="premium-table border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Empresa</th>
                        <th>Plano Atual</th>
                        <th>Usuários</th>
                        <th>Utilização de Tokens</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($empresas as $emp): 
                        $uso_tokens = number_format($emp['ai_tokens_used_month']);
                        $limite_tokens = $emp['ai_token_limit'] > 999999 ? '∞' : number_format($emp['ai_token_limit']);
                        $percent = $emp['ai_token_limit'] > 0 ? min(100, round(($emp['ai_tokens_used_month'] / $emp['ai_token_limit']) * 100)) : 0;
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-secondary" style="width: 40px; height: 40px;">
                                    <?php echo strtoupper(substr($emp['name'], 0, 1)); ?>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($emp['name']); ?></span>
                                    <span class="text-muted" style="font-size: 0.75rem;">ID: #<?php echo $emp['id']; ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-premium bg-<?php echo $emp['ai_plan']; ?>">
                                <?php echo ucfirst($emp['ai_plan']); ?>
                            </span>
                        </td>
                        <td class="text-muted fw-bold"><?php echo $emp['max_users'] > 100 ? '∞' : $emp['max_users']; ?></td>
                        <td style="min-width: 150px;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <small class="fw-bold text-dark"><?php echo $percent; ?>%</small>
                                <small class="text-muted ms-auto" style="font-size: 0.7rem;"><?php echo $uso_tokens; ?> / <?php echo $limite_tokens; ?></small>
                            </div>
                            <div class="progress" style="height: 6px; background-color: #f1f5f9;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percent; ?>%"></div>
                            </div>
                        </td>
                        <td><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Ativo</span></td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v text-muted"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 overflow-hidden p-0">
                                    <li><a class="dropdown-item py-3 px-4" href="editar_empresa.php?id=<?php echo $emp['id']; ?>"><i class="fas fa-edit me-2 text-primary"></i> Editar Plano</a></li>
                                    <li><a class="dropdown-item py-3 px-4" href="#"><i class="fas fa-key me-2 text-warning"></i> Resetar Senha Admin</a></li>
                                    <li><a class="dropdown-item py-3 px-4 text-danger bg-light" href="#"><i class="fas fa-ban me-2"></i> Bloquear Acesso</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once 'includes/footer.php'; ?>
