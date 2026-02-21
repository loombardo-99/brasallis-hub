<?php
// modules/rh/views/folha.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';
require_once __DIR__ . '/../../../includes/cabecalho.php';

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'gerar_folha') {
        $user_id = $_POST['user_id'];
        $mes = $_POST['mes']; // YYYY-MM
        $salario = (float)$_POST['salario'];
        
        // Cálculos Simplificados (Simulação 2024)
        $inss = calculateINSS($salario);
        $irrf = calculateIRRF($salario - $inss);
        $liquido = $salario - $inss - $irrf;

        $detalhes = json_encode([
            'inss' => $inss,
            'irrf' => $irrf,
            'outros' => 0
        ]);

        // Check if employee exists in rh_funcionarios, if not, create placeholder
        $check = $conn->prepare("SELECT id FROM rh_funcionarios WHERE user_id = ?");
        $check->execute([$user_id]);
        $funcId = $check->fetchColumn();
        
        if(!$funcId) {
            $insF = $conn->prepare("INSERT INTO rh_funcionarios (empresa_id, user_id, salario_base) VALUES (?, ?, ?)");
            $insF->execute([$empresa_id, $user_id, $salario]);
            $funcId = $conn->lastInsertId();
        }

        // Insert Folha
        $stmt = $conn->prepare("INSERT INTO rh_folha_pagamento (empresa_id, funcionario_id, mes_referencia, salario_base, proventos, descontos, liquido, detalhes_json, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'fechado')");
        $stmt->execute([$empresa_id, $funcId, $mes . '-01', $salario, $salario, ($inss + $irrf), $liquido, $detalhes]);
        
        $_SESSION['message'] = "Folha gerada com sucesso!";
        $_SESSION['message_type'] = "success";
    }
}

// Helpers
function calculateINSS($salario) {
    // Tabela progressiva simplificada
    if ($salario <= 1412) return $salario * 0.075;
    if ($salario <= 2666.68) return $salario * 0.09; // aprox
    if ($salario <= 4000.03) return $salario * 0.12;
    return $salario * 0.14; // Teto não aplicado para simplificar
}

function calculateIRRF($base) {
    if ($base <= 2112) return 0;
    if ($base <= 2826.65) return ($base * 0.075) - 158.40;
    if ($base <= 3751.05) return ($base * 0.15) - 354.80;
    if ($base <= 4664.68) return ($base * 0.225) - 636.13;
    return ($base * 0.275) - 869.36;
}

// Fetch Employees
$users = $conn->prepare("SELECT u.id, u.username, f.salario_base, f.id as func_id FROM usuarios u LEFT JOIN rh_funcionarios f ON u.id = f.user_id WHERE u.empresa_id = ? AND u.user_type != 'admin'");
$users->execute([$empresa_id]);
$colaboradores = $users->fetchAll(PDO::FETCH_ASSOC);

// Fetch History
$history = $conn->prepare("
    SELECT fp.*, u.username 
    FROM rh_folha_pagamento fp 
    JOIN rh_funcionarios rf ON fp.funcionario_id = rf.id 
    JOIN usuarios u ON rf.user_id = u.id 
    WHERE fp.empresa_id = ? ORDER BY fp.data_geracao DESC
");
$history->execute([$empresa_id]);
$folhas = $history->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1">Folha de Pagamento</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php">RH</a></li>
                    <li class="breadcrumb-item active">Folha</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-trust-primary" data-bs-toggle="modal" data-bs-target="#gerarFolhaModal"><i class="fas fa-calculator me-2"></i>Gerar Folha</button>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Referência</th>
                            <th>Colaborador</th>
                            <th>Salário Base</th>
                            <th>Descontos</th>
                            <th>Líquido</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($folhas as $f): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= date('m/Y', strtotime($f['mes_referencia'])) ?></td>
                            <td><?= htmlspecialchars($f['username']) ?></td>
                            <td>R$ <?= number_format($f['salario_base'], 2, ',', '.') ?></td>
                            <td class="text-danger">R$ <?= number_format($f['descontos'], 2, ',', '.') ?></td>
                            <td class="fw-bold text-success">R$ <?= number_format($f['liquido'], 2, ',', '.') ?></td>
                            <td><span class="badge bg-success bg-opacity-10 text-success">FECHADO</span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light border" onclick="printHolerite(<?= $f['id'] ?>)"><i class="fas fa-print"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gerar -->
<div class="modal fade" id="gerarFolhaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="gerar_folha">
                <div class="modal-header">
                    <h5 class="modal-title">Gerar Holerite</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Colaborador</label>
                        <select name="user_id" class="form-select" required id="colabSelect">
                            <option value="">Selecione...</option>
                            <?php foreach($colaboradores as $c): ?>
                                <option value="<?= $c['id'] ?>" data-salary="<?= $c['salario_base'] ?: 2000 ?>"><?= htmlspecialchars($c['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mês Referência</label>
                        <input type="month" name="mes" class="form-control" value="<?= date('Y-m') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Salário Base (R$)</label>
                        <input type="number" step="0.01" name="salario" id="inputSalario" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Processar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('colabSelect').addEventListener('change', function() {
    const salary = this.options[this.selectedIndex].getAttribute('data-salary');
    document.getElementById('inputSalario').value = salary;
});

function printHolerite(id) {
    // Abrir janela de impressão (implementação futura ou simples alert)
    alert('Simulação: Imprimindo Holerite #' + id);
}
</script>

<style>
    .text-navy { color: #0A2647; }
    .btn-trust-primary { background-color: #0A2647; color: white; border: none; }
</style>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
