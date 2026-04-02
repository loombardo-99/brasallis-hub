<?php
// modules/rh/views/colaboradores.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';

// Check Auth & Permission
if (!isset($_SESSION['user_id'])) { header('Location: ../../../login.php'); exit; }
if (!check_permission('rh', 'leitura')) { header('Location: ../../../admin/painel_admin.php?error=acesso_negado'); exit; }

$params = check_permission('rh', 'escrita'); 
$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// Fetch Users (Employees)
try {
    $stmt = $conn->prepare("
        SELECT u.*, u.username as nome, s.nome as setor_nome 
        FROM usuarios u 
        LEFT JOIN setores s ON u.setor_id = s.id 
        WHERE u.empresa_id = ? 
        ORDER BY u.username ASC
    ");
    $stmt->execute([$empresa_id]);
    $colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $colaboradores = [];
}

require_once __DIR__ . '/../../../includes/cabecalho.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1"><i class="fas fa-users me-2 text-info"></i>Colaboradores</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="../../../admin/painel_admin.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Recursos Humanos</a></li>
                    <li class="breadcrumb-item active">Colaboradores</li>
                </ol>
            </nav>
        </div>
        <div>
            <?php if ($params): ?>
            <a href="../../../admin/usuarios.php" class="btn btn-outline-primary shadow-sm fw-bold">
                <i class="fas fa-cog me-2"></i>Gerenciar Acessos (Admin)
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 px-4 text-secondary text-uppercase" style="font-size: 0.8rem;">Colaborador</th>
                        <th class="py-3 px-4 text-secondary text-uppercase" style="font-size: 0.8rem;">E-mail / Contato</th>
                        <th class="py-3 px-4 text-secondary text-uppercase" style="font-size: 0.8rem;">Cargo / Setor</th>
                        <th class="py-3 px-4 text-secondary text-uppercase text-center" style="font-size: 0.8rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($colaboradores)): ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">Nenhum colaborador encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach($colaboradores as $c): ?>
                        <tr>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon-shape bg-primary text-white rounded-circle me-3" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                        <?= strtoupper(substr($c['nome'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($c['nome'] ?: $c['username']) ?></div>
                                        <small class="text-muted">Admissão: <?= date('d/m/Y', strtotime($c['created_at'])) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-dark"><?= htmlspecialchars($c['email']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($c['telefone'] ?? 'Não informado') ?></small>
                            </td>
                            <td class="py-3 px-4">
                                <div class="fw-bold text-dark"><?= ucfirst($c['user_type']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($c['setor_nome'] ?? 'Setor Geral') ?></small>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if($c['status'] == 'ativo'): ?>
                                    <span class="badge bg-success-light text-success px-3 py-2 rounded-pill">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-light text-danger px-3 py-2 rounded-pill">Inativo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .text-navy { color: #0A2647; }
    .bg-success-light { background-color: rgba(40,167,69,0.1); }
    .bg-danger-light { background-color: rgba(220,53,69,0.1); }
</style>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
