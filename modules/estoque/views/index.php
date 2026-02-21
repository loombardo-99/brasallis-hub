<?php
// modules/estoque/views/index.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';
require_once __DIR__ . '/../../../includes/cabecalho.php';

// Check Auth & Permission
if (!isset($_SESSION['user_id'])) { header('Location: ../../../login.php'); exit; }
if (!check_permission('estoque', 'leitura')) { header('Location: ../../../admin/painel_admin.php?error=acesso_negado'); exit; }

$params = check_permission('estoque', 'escrita'); // Boolean for UI control

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// Metrics
$total_produtos = 0;
$baixo_estoque = 0;
$valor_estoque = 0;

try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN estoque_atual <= estoque_minimo THEN 1 ELSE 0 END) as baixo, SUM(estoque_atual * preco_compra) as valor FROM produtos WHERE empresa_id = ?");
    $stmt->execute([$empresa_id]);
    $metrics = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_produtos = $metrics['total'] ?? 0;
    $baixo_estoque = $metrics['baixo'] ?? 0;
    $valor_estoque = $metrics['valor'] ?? 0;
} catch (Exception $e) { /* silent */ }
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1"><i class="fas fa-boxes me-2"></i>Estoque Inteligente</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="../../../admin/painel_admin.php">Home</a></li>
                    <li class="breadcrumb-item active">Gestão de Estoque</li>
                </ol>
            </nav>
        </div>
        <div>
            <?php if ($params): ?>
            <a href="../../../admin/produtos.php?action=new" class="btn btn-trust-primary"><i class="fas fa-plus me-2"></i>Novo Produto</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- METRICS -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-dashboard h-100 p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary text-uppercase small fw-bold">Total de Itens</h6>
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-circle">
                        <i class="fas fa-cube"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-navy"><?= $total_produtos ?></h3>
                <small class="text-muted">Produtos cadastrados</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard h-100 p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary text-uppercase small fw-bold">Alerta de Estoque</h6>
                    <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-circle">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-navy"><?= $baixo_estoque ?></h3>
                <small class="text-danger fw-bold"><i class="fas fa-arrow-down me-1"></i>Itens abaixo do mínimo</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard h-100 p-4 border-0 shadow-sm border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary text-uppercase small fw-bold">Valor em Estoque</h6>
                    <div class="icon-shape bg-success bg-opacity-10 text-success rounded-circle">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-navy">R$ <?= number_format($valor_estoque, 2, ',', '.') ?></h3>
                <small class="text-success">Custo total armazenado</small>
            </div>
        </div>
    </div>

    <!-- ACTIONS -->
    <h5 class="fw-bold text-navy mb-3">Operações Rápidas</h5>
    <div class="row g-3">
        <?php
        $actions = [
            ['label' => 'Catálogo de Produtos', 'icon' => 'fas fa-list', 'link' => '../../../admin/produtos.php', 'perm' => true],
            ['label' => 'Ajuste de Entrada', 'icon' => 'fas fa-plus-circle', 'link' => '../../../admin/movimentacoes.php?type=entrada', 'perm' => $params],
            ['label' => 'Ajuste de Saída', 'icon' => 'fas fa-minus-circle', 'link' => '../../../admin/movimentacoes.php?type=saida', 'perm' => $params],
            ['label' => 'Baixo Estoque', 'icon' => 'fas fa-exclamation-triangle', 'link' => '../../../admin/produtos.php?filter=low_stock', 'perm' => $params],
            ['label' => 'Fornecedores', 'icon' => 'fas fa-handshake', 'link' => '../../../admin/fornecedores.php', 'perm' => true],
            ['label' => 'Relatórios', 'icon' => 'fas fa-chart-bar', 'link' => '../../../admin/relatorios.php', 'perm' => true],
        ];

        foreach($actions as $act):
            if(!$act['perm']) continue;
        ?>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="<?= $act['link'] ?>" class="card h-100 border-0 shadow-sm text-decoration-none card-hover-effect">
                <div class="card-body text-center p-3">
                    <div class="text-secondary mb-2" style="font-size: 1.5rem;"><i class="<?= $act['icon'] ?>"></i></div>
                    <span class="text-dark small fw-bold d-block"><?= $act['label'] ?></span>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .text-navy { color: #0A2647; }
    .card-hover-effect:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
