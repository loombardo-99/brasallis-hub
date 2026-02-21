<?php
// modules/financeiro/views/index.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';
require_once __DIR__ . '/../../../includes/cabecalho.php';

// Check Auth & Permission
if (!isset($_SESSION['user_id'])) { header('Location: ../../../login.php'); exit; }
if (!check_permission('financeiro', 'leitura')) { header('Location: ../../../admin/painel_admin.php?error=acesso_negado'); exit; }

$params = check_permission('financeiro', 'escrita'); 
$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// Metrics Logic
$receita_mes = 0;
$despesa_mes = 0;
$saldo = 0;
$mes_atual = date('m');
$ano_atual = date('Y');

try {
    // Receitas (Mês Atual)
    $stmt = $conn->prepare("SELECT SUM(valor) FROM fin_movimentacoes WHERE empresa_id = ? AND tipo = 'receita' AND MONTH(data_vencimento) = ? AND YEAR(data_vencimento) = ? AND status != 'cancelado'");
    $stmt->execute([$empresa_id, $mes_atual, $ano_atual]);
    $receita_mes = $stmt->fetchColumn() ?: 0;

    // Despesas (Mês Atual)
    $stmt = $conn->prepare("SELECT SUM(valor) FROM fin_movimentacoes WHERE empresa_id = ? AND tipo = 'despesa' AND MONTH(data_vencimento) = ? AND YEAR(data_vencimento) = ? AND status != 'cancelado'");
    $stmt->execute([$empresa_id, $mes_atual, $ano_atual]);
    $despesa_mes = $stmt->fetchColumn() ?: 0;

    // Saldo Total (Considerando tudo que foi pago/recebido até hoje)
    // Entradas Pagas
    $stmt = $conn->prepare("SELECT SUM(valor) FROM fin_movimentacoes WHERE empresa_id = ? AND tipo = 'receita' AND status = 'pago'");
    $stmt->execute([$empresa_id]);
    $total_entradas = $stmt->fetchColumn() ?: 0;

    // Saídas Pagas
    $stmt = $conn->prepare("SELECT SUM(valor) FROM fin_movimentacoes WHERE empresa_id = ? AND tipo = 'despesa' AND status = 'pago'");
    $stmt->execute([$empresa_id]);
    $total_saidas = $stmt->fetchColumn() ?: 0;

    $saldo = $total_entradas - $total_saidas;

} catch (Exception $e) {
    // Silent fail
}

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1"><i class="fas fa-chart-line me-2"></i>Gestão Financeira</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="../../../admin/painel_admin.php">Home</a></li>
                    <li class="breadcrumb-item active">Financeiro</li>
                </ol>
            </nav>
        </div>
        <div>
            <?php if ($params): ?>
            <a href="movimentacao_form.php?type=receita" class="btn btn-trust-primary me-2"><i class="fas fa-plus me-2"></i>Nova Receita</a>
            <a href="movimentacao_form.php?type=despesa" class="btn btn-outline-danger"><i class="fas fa-minus me-2"></i>Nova Despesa</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- METRICS -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-dashboard h-100 p-4 border-0 shadow-sm border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary text-uppercase small fw-bold">Receitas (Mês)</h6>
                    <div class="icon-shape bg-success bg-opacity-10 text-success rounded-circle">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-navy">R$ <?= number_format($receita_mes, 2, ',', '.') ?></h3>
                <small class="text-success"><i class="fas fa-chart-line me-1"></i>Previsão mensal</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard h-100 p-4 border-0 shadow-sm border-start border-danger border-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary text-uppercase small fw-bold">Despesas (Mês)</h6>
                    <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-circle">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-navy">R$ <?= number_format($despesa_mes, 2, ',', '.') ?></h3>
                <small class="text-danger">Previsão mensal</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard h-100 p-4 border-0 shadow-sm bg-navy text-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-white-50 text-uppercase small fw-bold">Saldo em Caixa</h6>
                    <div class="icon-shape bg-white bg-opacity-25 text-white rounded-circle">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <h3 class="fw-bold">R$ <?= number_format($saldo, 2, ',', '.') ?></h3>
                <small class="text-white-50">Realizado (Pago - Recebido)</small>
            </div>
        </div>
    </div>

    <!-- ACTIONS -->
    <h5 class="fw-bold text-navy mb-3">Atalhos Financeiros</h5>
    <div class="row g-3">
        <?php
        $actions = [
            ['label' => 'Extrato Geral', 'icon' => 'fas fa-list', 'link' => 'movimentacoes.php', 'perm' => true],
            ['label' => 'Contas a Pagar', 'icon' => 'fas fa-file-invoice-dollar', 'link' => 'movimentacoes.php?type=despesa', 'perm' => $params],
            ['label' => 'Contas a Receber', 'icon' => 'fas fa-hand-holding-usd', 'link' => 'movimentacoes.php?type=receita', 'perm' => $params],
            ['label' => 'Nova Movimentação', 'icon' => 'fas fa-plus-circle', 'link' => 'movimentacao_form.php', 'perm' => $params],
        ];

        foreach($actions as $act):
            if(!$act['perm']) continue;
        ?>
        <div class="col-6 col-md-4 col-lg-3">
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
