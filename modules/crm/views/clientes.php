<?php
// modules/crm/views/clientes.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';

// Check Auth & Permission
if (!isset($_SESSION['user_id'])) { header('Location: ../../../login.php'); exit; }
if (!check_permission('crm', 'leitura')) { header('Location: ../../../admin/painel_admin.php?error=acesso_negado'); exit; }

$params = check_permission('crm', 'escrita'); 
$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// Handle Actions (e.g., Disable/Enable Client)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $params) {
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
        $id = (int)$_POST['id'];
        $novo_status = $_POST['status'] === 'ativo' ? 'inativo' : 'ativo';
        try {
            $stmt = $conn->prepare("UPDATE clientes SET status = ? WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$novo_status, $id, $empresa_id]);
            header("Location: clientes.php?msg=status_updated");
            exit;
        } catch (Exception $e) {}
    }
}

// Fetch Clientes
try {
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE empresa_id = ? ORDER BY nome ASC");
    $stmt->execute([$empresa_id]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $clientes = [];
}

require_once __DIR__ . '/../../../includes/cabecalho.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1"><i class="fas fa-users me-2 text-primary"></i>Base de Clientes</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="../../../admin/painel_admin.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="index.php">CRM & Vendas</a></li>
                    <li class="breadcrumb-item active">Clientes</li>
                </ol>
            </nav>
        </div>
        <div>
            <?php if ($params): ?>
            <a href="cliente_form.php" class="btn btn-trust-primary shadow-sm fw-bold">
                <i class="fas fa-user-plus me-2"></i>Novo Cliente
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Feedback Messages -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'status_updated'): ?>
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>Status do cliente atualizado com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 px-4 text-secondary text-uppercase" style="font-size: 0.8rem;">Cliente</th>
                        <th class="py-3 px-4 text-secondary text-uppercase" style="font-size: 0.8rem;">Contato</th>
                        <th class="py-3 px-4 text-secondary text-uppercase" style="font-size: 0.8rem;">Documento (CPF/CNPJ)</th>
                        <th class="py-3 px-4 text-secondary text-uppercase text-center" style="font-size: 0.8rem;">Status</th>
                        <th class="py-3 px-4 text-end text-secondary text-uppercase" style="font-size: 0.8rem;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Nenhum cliente cadastrado. Clique em "Novo Cliente" para começar.</td></tr>
                    <?php else: ?>
                        <?php foreach($clientes as $c): ?>
                        <tr>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon-shape bg-primary text-white rounded-circle me-3" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                        <?= strtoupper(substr($c['nome'] ?? 'C', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($c['nome']) ?></div>
                                        <small class="text-muted">Cadastro: <?= date('d/m/Y', strtotime($c['created_at'])) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-dark"><i class="fas fa-envelope me-2 text-muted"></i><?= htmlspecialchars($c['email'] ?: 'Não informado') ?></div>
                                <small class="text-muted"><i class="fas fa-phone me-2"></i><?= htmlspecialchars($c['telefone'] ?: 'Não informado') ?></small>
                            </td>
                            <td class="py-3 px-4 text-dark font-monospace"><?= htmlspecialchars($c['cpf_cnpj'] ?: '-') ?></td>
                            <td class="py-3 px-4 text-center">
                                <?php if($c['status'] == 'ativo'): ?>
                                    <span class="badge bg-success-light text-success px-3 py-2 rounded-pill">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-light text-danger px-3 py-2 rounded-pill">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <?php if($params): ?>
                                    <!-- Ações do CRM: Adicionar ao Kanban, Editar -->
                                    <a href="kanban.php?action=new&cliente_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Nova Oportunidade">
                                        <i class="fas fa-bullhorn"></i>
                                    </a>
                                    <a href="cliente_form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-circle me-1" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $c['status'] ?>">
                                        <button type="submit" class="btn btn-sm <?= $c['status'] == 'ativo' ? 'btn-outline-danger' : 'btn-outline-success' ?> rounded-circle" title="<?= $c['status'] == 'ativo' ? 'Inativar' : 'Reativar' ?>">
                                            <i class="fas <?= $c['status'] == 'ativo' ? 'fa-ban' : 'fa-check' ?>"></i>
                                        </button>
                                    </form>
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
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
</style>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
