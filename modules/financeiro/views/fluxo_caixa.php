<?php
// modules/financeiro/views/fluxo_caixa.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';

// Check Auth & Permission
if (!isset($_SESSION['user_id'])) { header('Location: ../../../login.php'); exit; }
if (!check_permission('financeiro', 'leitura')) { header('Location: ../../../admin/painel_admin.php?error=acesso_negado'); exit; }

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// Filtro de Mês/Ano
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

// Fetch Receitas do Mês
try {
    $stmtR = $conn->prepare("SELECT data_vencimento as data, descricao, valor, status, 'receita' as tipo FROM contas_receber WHERE empresa_id = ? AND MONTH(data_vencimento) = ? AND YEAR(data_vencimento) = ? ORDER BY data_vencimento ASC");
    $stmtR->execute([$empresa_id, $mes, $ano]);
    $receitas = $stmtR->fetchAll(PDO::FETCH_ASSOC);

    $stmtP = $conn->prepare("SELECT data_vencimento as data, descricao, valor, status, 'despesa' as tipo FROM contas_pagar WHERE empresa_id = ? AND MONTH(data_vencimento) = ? AND YEAR(data_vencimento) = ? ORDER BY data_vencimento ASC");
    $stmtP->execute([$empresa_id, $mes, $ano]);
    $despesas = $stmtP->fetchAll(PDO::FETCH_ASSOC);

    // Merge and Sort by Date
    $lancamentos = array_merge($receitas, $despesas);
    usort($lancamentos, function($a, $b) {
        return strtotime($a['data']) - strtotime($b['data']);
    });

} catch (Exception $e) {
    $lancamentos = [];
}

// Calculate Summaries
$total_entradas = 0;
$total_saidas = 0;
$entradas_realizadas = 0;
$saidas_realizadas = 0;

foreach ($lancamentos as $l) {
    if ($l['tipo'] === 'receita') {
        $total_entradas += $l['valor'];
        if ($l['status'] === 'recebido') $entradas_realizadas += $l['valor'];
    } else {
        $total_saidas += $l['valor'];
        if ($l['status'] === 'pago') $saidas_realizadas += $l['valor'];
    }
}
$saldo_projetado = $total_entradas - $total_saidas;
$saldo_realizado = $entradas_realizadas - $saidas_realizadas;

require_once __DIR__ . '/../../../includes/cabecalho.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1"><i class="fas fa-chart-line me-2 text-primary"></i>Fluxo de Caixa</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="../../../admin/painel_admin.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Financeiro</a></li>
                    <li class="breadcrumb-item active">Fluxo de Caixa</li>
                </ol>
            </nav>
        </div>
        <div>
            <form method="GET" class="d-flex gap-2">
                <select name="mes" class="form-select border-0 shadow-sm" onchange="this.form.submit()">
                    <?php for($i=1; $i<=12; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $mes ? 'selected' : '' ?>><?= sprintf('%02d', $i) ?> - <?= date('M', mktime(0,0,0,$i,10)) ?></option>
                    <?php endfor; ?>
                </select>
                <select name="ano" class="form-select border-0 shadow-sm" style="width: 100px;" onchange="this.form.submit()">
                    <?php for($i = date('Y')-1; $i <= date('Y')+1; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $ano ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- METRICS -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-dashboard h-100 p-4 border-0 shadow-sm">
                <h6 class="text-secondary text-uppercase small fw-bold mb-3">Entradas (Previsto)</h6>
                <h4 class="fw-bold text-success">R$ <?= number_format($total_entradas, 2, ',', '.') ?></h4>
                <small class="text-muted">R$ <?= number_format($entradas_realizadas, 2, ',', '.') ?> já recebidos</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard h-100 p-4 border-0 shadow-sm">
                <h6 class="text-secondary text-uppercase small fw-bold mb-3">Saídas (Previsto)</h6>
                <h4 class="fw-bold text-danger">R$ <?= number_format($total_saidas, 2, ',', '.') ?></h4>
                <small class="text-muted">R$ <?= number_format($saidas_realizadas, 2, ',', '.') ?> já pagos</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-dashboard h-100 p-4 border-0 shadow-sm bg-navy text-white">
                <div class="row w-100">
                    <div class="col-6 border-end border-white-10">
                        <h6 class="text-white-50 text-uppercase small fw-bold mb-3">Saldo Projetado</h6>
                        <h3 class="fw-bold <?= $saldo_projetado < 0 ? 'text-danger' : 'text-white' ?>">R$ <?= number_format($saldo_projetado, 2, ',', '.') ?></h3>
                    </div>
                    <div class="col-6 ps-4">
                        <h6 class="text-white-50 text-uppercase small fw-bold mb-3">Saldo Realizado</h6>
                        <h3 class="fw-bold <?= $saldo_realizado < 0 ? 'text-danger' : 'text-success' ?>">R$ <?= number_format($saldo_realizado, 2, ',', '.') ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lancamentos Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-navy">Lançamentos do Mês</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 px-4 text-secondary text-uppercase" style="font-size: 0.8rem;">Data</th>
                        <th class="py-3 px-4 text-secondary text-uppercase" style="font-size: 0.8rem;">Descrição</th>
                        <th class="py-3 px-4 text-secondary text-uppercase" style="font-size: 0.8rem;">Tipo</th>
                        <th class="py-3 px-4 text-secondary text-uppercase text-center" style="font-size: 0.8rem;">Status</th>
                        <th class="py-3 px-4 text-end text-secondary text-uppercase" style="font-size: 0.8rem;">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lancamentos)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Nenhum lançamento encontrado para este período.</td></tr>
                    <?php else: ?>
                        <?php foreach($lancamentos as $l): ?>
                        <tr>
                            <td class="py-3 px-4 text-muted"><?= date('d/m/Y', strtotime($l['data'])) ?></td>
                            <td class="py-3 px-4 fw-bold text-dark"><?= htmlspecialchars($l['descricao']) ?></td>
                            <td class="py-3 px-4">
                                <?php if($l['tipo'] == 'receita'): ?>
                                    <span class="text-success"><i class="fas fa-arrow-down me-1"></i>Receita</span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="fas fa-arrow-up me-1"></i>Despesa</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if($l['status'] == 'pago' || $l['status'] == 'recebido'): ?>
                                    <span class="badge bg-success-light text-success"><i class="fas fa-check-circle me-1"></i>Efetivado</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-light text-warning"><i class="fas fa-clock me-1"></i>Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-end fw-bold <?= $l['tipo'] == 'receita' ? 'text-success' : 'text-danger' ?>">
                                <?= $l['tipo'] == 'receita' ? '+' : '-' ?> R$ <?= number_format($l['valor'], 2, ',', '.') ?>
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
    .bg-navy { background-color: #0A2647; }
    .border-white-10 { border-color: rgba(255,255,255,0.1) !important; }
    .bg-success-light { background-color: rgba(40,167,69,0.1); }
    .bg-warning-light { background-color: rgba(255,193,7,0.1); }
</style>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
