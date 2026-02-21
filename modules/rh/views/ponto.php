<?php
// modules/rh/views/ponto.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';
require_once __DIR__ . '/../../../includes/cabecalho.php';

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// --- ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'bater_ponto') {
        $tipo = $_POST['tipo']; // entrada_1, saida_1, entrada_2, saida_2
        $now = date('H:i:s');
        
        // Verificar se já existe registro hoje
        $check = $conn->prepare("SELECT id FROM rh_ponto WHERE user_id = ? AND data_registro = ?");
        $check->execute([$user_id, $today]);
        $pontoId = $check->fetchColumn();

        if ($pontoId) {
            $upd = $conn->prepare("UPDATE rh_ponto SET $tipo = ? WHERE id = ?");
            $upd->execute([$now, $pontoId]);
        } else {
            $ins = $conn->prepare("INSERT INTO rh_ponto (empresa_id, user_id, data_registro, $tipo) VALUES (?, ?, ?, ?)");
            $ins->execute([$empresa_id, $user_id, $today, $now]);
        }
        $_SESSION['message'] = "Ponto registrado: $now";
        $_SESSION['message_type'] = "success";
    }
}

// Fetch Today's Record
$stmt = $conn->prepare("SELECT * FROM rh_ponto WHERE user_id = ? AND data_registro = ?");
$stmt->execute([$user_id, $today]);
$pontoHoje = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch History (Last 30 days)
$hist = $conn->prepare("SELECT * FROM rh_ponto WHERE user_id = ? ORDER BY data_registro DESC LIMIT 30");
$hist->execute([$user_id]);
$historico = $hist->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1">Meu Ponto</h2>
            <p class="text-secondary small mb-0"><?= date('d/m/Y') ?></p>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold m-0 text-navy">Registrar Agora</h6>
                </div>
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h1 class="display-4 fw-bold text-dark mb-4" id="clock">00:00:00</h1>
                    
                    <form method="POST" class="d-grid gap-3">
                        <input type="hidden" name="action" value="bater_ponto">
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="submit" name="tipo" value="entrada_1" class="btn btn-outline-success w-100 py-3" <?= ($pontoHoje['entrada_1'] ?? null) ? 'disabled' : '' ?>>
                                    <i class="fas fa-sign-in-alt mb-1"></i><br>Entrada
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="submit" name="tipo" value="saida_1" class="btn btn-outline-danger w-100 py-3" <?= (!($pontoHoje['entrada_1'] ?? null) || ($pontoHoje['saida_1'] ?? null)) ? 'disabled' : '' ?>>
                                    <i class="fas fa-sign-out-alt mb-1"></i><br>Almoço
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="submit" name="tipo" value="entrada_2" class="btn btn-outline-success w-100 py-3" <?= (!($pontoHoje['saida_1'] ?? null) || ($pontoHoje['entrada_2'] ?? null)) ? 'disabled' : '' ?>>
                                    <i class="fas fa-utensils mb-1"></i><br>Volta
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="submit" name="tipo" value="saida_2" class="btn btn-outline-danger w-100 py-3" <?= (!($pontoHoje['entrada_2'] ?? null) || ($pontoHoje['saida_2'] ?? null)) ? 'disabled' : '' ?>>
                                    <i class="fas fa-home mb-1"></i><br>Saída
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
             <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold m-0 text-navy">Minhas Marcações</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="bg-light">
                            <tr>
                                <th>Data</th>
                                <th>Entrada</th>
                                <th>Almoço</th>
                                <th>Volta</th>
                                <th>Saída</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($historico as $h): ?>
                            <tr>
                                <td class="fw-bold"><?= date('d/m', strtotime($h['data_registro'])) ?></td>
                                <td><?= $h['entrada_1'] ? substr($h['entrada_1'], 0, 5) : '-' ?></td>
                                <td><?= $h['saida_1'] ? substr($h['saida_1'], 0, 5) : '-' ?></td>
                                <td><?= $h['entrada_2'] ? substr($h['entrada_2'], 0, 5) : '-' ?></td>
                                <td><?= $h['saida_2'] ? substr($h['saida_2'], 0, 5) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateClock() {
    const now = new Date();
    document.getElementById('clock').innerText = now.toLocaleTimeString('pt-BR');
}
setInterval(updateClock, 1000);
updateClock();
</script>

<style>
    .text-navy { color: #0A2647; }
</style>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
