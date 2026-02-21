<?php
// modules/crm/views/clientes.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';
require_once __DIR__ . '/../../../includes/cabecalho.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../../login.php'); exit; }
if (!check_permission('crm', 'leitura')) { header('Location: index.php?error=acesso_negado'); exit; }

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];
$search = sanitize_input($_GET['search'] ?? '');

// Pagination
$limit = 50;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Base Query
$sql_base = "FROM clientes WHERE empresa_id = ?";
$params_base = [$empresa_id];

if ($search) {
    $sql_base .= " AND (nome LIKE ? OR email LIKE ? OR cpf_cnpj LIKE ?)";
    $params_base[] = "%$search%";
    $params_base[] = "%$search%";
    $params_base[] = "%$search%";
}

// Count Total
$stmt_count = $conn->prepare("SELECT COUNT(*) $sql_base");
$stmt_count->execute($params_base);
$total_rows = $stmt_count->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Fetch Pages (Need to emulate bindValue for LIMIT/OFFSET if strict mode, string concat is safe here for INT casted vars)
// Using string injection for limit/offset is standard in simple PDO scripts where bindParam is tedious for these ints
$sql = "SELECT * $sql_base ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params_base);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$can_edit = check_permission('crm', 'escrita');
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1">Clientes</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php">CRM</a></li>
                    <li class="breadcrumb-item active">Listagem</li>
                </ol>
            </nav>
        </div>
        <div>
            <?php if($can_edit): ?>
            <a href="cliente_form.php" class="btn btn-trust-primary"><i class="fas fa-plus me-2"></i>Novo Cliente</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <form class="d-flex gap-2">
                <input type="text" name="search" class="form-control border-0" placeholder="Buscar por nome, email ou CPF/CNPJ..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-light"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <div class="card card-dashboard border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-secondary small fw-bold text-uppercase">Nome / Razão Social</th>
                            <th class="text-secondary small fw-bold text-uppercase">Tipo</th>
                            <th class="text-secondary small fw-bold text-uppercase">Contato</th>
                            <th class="text-secondary small fw-bold text-uppercase">Localização</th>
                            <th class="pe-4 text-end text-secondary small fw-bold text-uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($clientes as $cli): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-navy d-block"><?= htmlspecialchars($cli['nome']) ?></span>
                                <small class="text-muted"><?= htmlspecialchars($cli['cpf_cnpj']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border"><?= $cli['tipo'] ?></span>
                            </td>
                            <td>
                                <div class="d-flex flex-column small">
                                    <span><i class="fas fa-envelope me-1 text-muted"></i> <?= htmlspecialchars($cli['email']) ?></span>
                                    <span><i class="fas fa-phone me-1 text-muted"></i> <?= htmlspecialchars($cli['telefone']) ?></span>
                                </div>
                            </td>
                            <td class="small text-muted">
                                <?= htmlspecialchars(mb_strimwidth($cli['endereco'], 0, 30, "...")) ?>
                            </td>
                            <td class="text-end pe-4">
                                <?php if($can_edit): ?>
                                <a href="cliente_form.php?id=<?= $cli['id'] ?>" class="btn btn-sm btn-light text-primary"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($clientes)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">Nenhum cliente encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <span class="text-muted small">Mostrando <?= count($clientes) ?> de <?= $total_rows ?> registros (Página <?= $page ?> de <?= $total_pages ?>)</span>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Anterior</a>
                </li>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if($i == $page || $i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                    <?php elseif($i == $page - 3 || $i == $page + 3): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Próximo</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<style>
    .text-navy { color: #0A2647; }
    .btn-trust-primary { background-color: #0A2647; color: white; border: none; }
</style>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
