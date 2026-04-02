<?php
// admin/organizacao.php
session_start();
require_once __DIR__ . '/../includes/funcoes.php';

// Check Auth & Admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];
$msg = '';

// --- HANDLE POST ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // 1. ADD SECTOR
        if ($_POST['action'] === 'add_setor') {
            $nome = sanitize_input($_POST['nome']);
            $cor = $_POST['cor_hex'] ?? '#0d6efd';
            if ($nome) {
                $stmt = $conn->prepare("INSERT INTO setores (empresa_id, nome, cor_hex) VALUES (?, ?, ?)");
                $stmt->execute([$empresa_id, $nome, $cor]);
                $msg = '<div class="alert alert-success border-0 shadow-sm">Setor criado com sucesso!</div>';
            }
        }
        
        // 2. DELETE SECTOR
        if ($_POST['action'] === 'delete_setor') {
            $id = (int)$_POST['id'];
            // Verify ownership
            $check = $conn->prepare("SELECT id FROM setores WHERE id = ? AND empresa_id = ?");
            $check->execute([$id, $empresa_id]);
            if ($check->fetch()) {
                $stmt = $conn->prepare("DELETE FROM setores WHERE id = ?");
                $stmt->execute([$id]);
                $msg = '<div class="alert alert-success border-0 shadow-sm">Setor removido.</div>';
            }
        }
    }
}

// --- FETCH DATA ---
$stmt = $conn->prepare("SELECT s.*, 
    (SELECT COUNT(*) FROM usuario_setor us WHERE us.setor_id = s.id) as total_users,
    (SELECT COUNT(*) FROM cargos c WHERE c.setor_id = s.id) as total_cargos
    FROM setores s WHERE s.empresa_id = ? ORDER BY s.nome ASC");
$stmt->execute([$empresa_id]);
$setores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// General Stats
$total_colaboradores = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE empresa_id = ?");
$total_colaboradores->execute([$empresa_id]);
$total_colab = $total_colaboradores->fetchColumn();

require_once __DIR__ . '/../includes/cabecalho.php';
?>

<div class="container-fluid py-4 bg-light min-vh-100">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-navy mb-1"><i class="fas fa-sitemap me-2 text-primary"></i>Organização</h2>
            <p class="text-muted">Gerencie a estrutura de departamentos e hierarquia da sua empresa.</p>
        </div>
    </div>

    <?= $msg ?>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 16px;">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-circle me-3" style="width: 54px; height: 54px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-building fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small text-uppercase fw-bold">Estrutura</h6>
                        <h3 class="fw-bold mb-0 text-navy"><?= count($setores) ?> Setores</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 16px;">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-success bg-opacity-10 text-success rounded-circle me-3" style="width: 54px; height: 54px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small text-uppercase fw-bold">Capital Humano</h6>
                        <h3 class="fw-bold mb-0 text-navy"><?= $total_colab ?> Usuários</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-trust-primary text-white" style="border-radius: 16px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalAddSetor">
                <div class="d-flex align-items-center justify-content-center h-100">
                    <i class="fas fa-plus-circle me-2"></i>
                    <span class="fw-bold">Novo Departamento</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Setores Grid -->
    <div class="row g-4">
        <?php if(empty($setores)): ?>
            <div class="col-12 text-center py-5">
                <img src="../assets/img/empty_setores.svg" alt="Sem setores" style="max-width: 200px; opacity: 0.5;" onerror="this.src='https://illustrations.popsy.co/gray/team-building.svg'">
                <h5 class="mt-4 text-muted">Sua organização ainda não tem setores.</h5>
                <p class="text-muted small">Comece criando departamentos como "Financeiro", "RH" ou "Vendas".</p>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalAddSetor">Criar Primeiro Setor</button>
            </div>
        <?php else: ?>
            <?php foreach($setores as $s): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 transition-hover" style="border-radius: 20px; overflow: hidden;">
                        <div style="height: 6px; background-color: <?= $s['cor_hex'] ?>"></div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-box rounded-3 p-3" style="background-color: <?= $s['cor_hex'] ?>22;">
                                    <i class="fas fa-folder-open text-dark" style="color: <?= $s['cor_hex'] ?> !important;"></i>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-item" href="setor_config.php?id=<?= $s['id'] ?>"><i class="fas fa-cog me-2"></i>Configurar Hierarquia</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" onsubmit="return confirm('Excluir este setor e todas as suas configurações?')">
                                                <input type="hidden" name="action" value="delete_setor">
                                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Excluir</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <h5 class="fw-bold text-navy mb-1"><?= htmlspecialchars($s['nome']) ?></h5>
                            <div class="d-flex gap-3 mt-3">
                                <div class="small">
                                    <span class="text-muted">Cargos:</span> <span class="fw-bold"><?= $s['total_cargos'] ?></span>
                                </div>
                                <div class="small">
                                    <span class="text-muted">Equipe:</span> <span class="fw-bold"><?= $s['total_users'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 p-3">
                            <a href="setor_config.php?id=<?= $s['id'] ?>" class="btn btn-light w-100 rounded-pill text-navy fw-bold small">
                                Gerenciar Acessos <i class="fas fa-arrow-right ms-2 small"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Add Setor -->
<div class="modal fade" id="modalAddSetor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <form method="POST">
                <input type="hidden" name="action" value="add_setor">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-navy">Novo Departamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Nome do Setor</label>
                        <input type="text" name="nome" class="form-control form-control-lg bg-light border-0" placeholder="Ex: Financeiro, Vendas, TI..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Cor de Identificação</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php 
                            $cores = ['#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#6610f2', '#6f42c1', '#d63384', '#fd7e14'];
                            foreach($cores as $c): ?>
                                <div class="color-option">
                                    <input type="radio" name="cor_hex" value="<?= $c ?>" id="c_<?= str_replace('#','',$c) ?>" class="d-none" <?= $c == '#0d6efd' ? 'checked' : '' ?>>
                                    <label for="c_<?= str_replace('#','',$c) ?>" class="rounded-circle border-4" style="width: 35px; height: 35px; background-color: <?= $c ?>; cursor: pointer; border-color: rgba(0,0,0,0.1);"></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-trust-primary rounded-pill px-5 fw-bold">Criar Setor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .text-navy { color: #0A2647; }
    .bg-trust-primary { background: linear-gradient(135deg, #0A2647 0%, #205295 100%); }
    .transition-hover:hover { transform: translateY(-5px); transition: transform 0.3s ease; }
    .color-option input:checked + label { border-color: #0A2647 !important; border: 3px solid #0A2647 !important; transform: scale(1.15); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(10, 38, 71, 0.1); }
</style>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
