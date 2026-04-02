<?php
// admin/movimentacoes.php
session_start();
require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/cabecalho.php';

// Auth
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
if (!check_permission('estoque', 'escrita')) { header('Location: painel_admin.php?error=acesso_negado'); exit; }

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];
$type = $_GET['type'] ?? 'entrada'; // 'entrada' ou 'saida'

// POST HANDLER FOR MANUAL ADJUSTMENT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $tipo = $_POST['tipo'];
    $qtd = (float)$_POST['quantity'];
    $motivo = $_POST['motivo'];
    $user_id = $_SESSION['user_id'];

    if ($product_id && $qtd > 0) {
        try {
            $conn->beginTransaction();
            
            // Get current stock
            $stmt = $conn->prepare("SELECT quantity FROM produtos WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$product_id, $empresa_id]);
            $current = $stmt->fetchColumn();

            if ($tipo === 'saida' && $current < $qtd) {
                throw new Exception("Estoque insuficiente.");
            }

            // Update Stock
            if ($tipo === 'entrada') {
                $upd = $conn->prepare("UPDATE produtos SET quantity = quantity + ? WHERE id = ?");
            } else {
                $upd = $conn->prepare("UPDATE produtos SET quantity = quantity - ? WHERE id = ?");
            }
            $upd->execute([$qtd, $product_id]);

            // History Log
            $log = $conn->prepare("INSERT INTO historico_estoque (empresa_id, product_id, user_id, action, quantity, details, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $action = ($tipo === 'entrada' ? 'entrada_manual' : 'saida_manual');
            $log->execute([$empresa_id, $product_id, $user_id, $action, $qtd, $motivo]);

            $conn->commit();
            $msg = "Ajuste realizado com sucesso!";
            $msg_type = "success";
        } catch (Exception $e) {
            $conn->rollBack();
            $msg = "Erro: " . $e->getMessage();
            $msg_type = "danger";
        }
    }
}

// Fetch Log
$stmt = $conn->prepare("
    SELECT h.*, p.name as produto, u.username as usuario 
    FROM historico_estoque h
    JOIN produtos p ON h.product_id = p.id
    LEFT JOIN usuarios u ON h.user_id = u.id
    WHERE h.empresa_id = ? AND (h.action = 'entrada_manual' OR h.action = 'saida_manual')
    ORDER BY h.created_at DESC LIMIT 50
");
$stmt->execute([$empresa_id]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Products for Select
$prods = $conn->prepare("SELECT id, name, quantity, sku FROM produtos WHERE empresa_id = ? ORDER BY name ASC");
$prods->execute([$empresa_id]);
$products = $prods->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1">Ajuste Manual de Estoque</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="../modules/estoque/views/index.php">Estoque</a></li>
                    <li class="breadcrumb-item active">Movimentações</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (isset($msg)): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show shadow-sm" role="alert">
        <?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- FORM -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold m-0 text-navy"><i class="fas fa-edit me-2"></i>Novo Ajuste</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Tipo de Movimentação</label>
                            <select name="tipo" class="form-select" id="moveType">
                                <option value="entrada" <?= $type == 'entrada' ? 'selected' : '' ?>>Entrada ( + )</option>
                                <option value="saida" <?= $type == 'saida' ? 'selected' : '' ?>>Saída ( - )</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Produto</label>
                            <select name="product_id" class="form-select select2" required>
                                <option value="">Selecione...</option>
                                <?php foreach($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (Atual: <?= $p['quantity'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Quantidade</label>
                            <input type="number" name="quantity" step="0.001" class="form-control" required min="0.001">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Motivo / Observação</label>
                            <textarea name="motivo" class="form-control" rows="3" placeholder="Ex: Ajuste de contagem, Perda, Brinde..."></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-trust-primary">Confirmar Ajuste</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- LOG -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold m-0 text-navy"><i class="fas fa-history me-2"></i>Histórico Recente (Manual)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Data</th>
                                    <th>Produto</th>
                                    <th>Tipo</th>
                                    <th>Qtd</th>
                                    <th>Resp.</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($logs)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Nenhum ajuste manual recente.</td></tr>
                                <?php else: ?>
                                    <?php foreach($logs as $log): ?>
                                    <tr>
                                        <td class="ps-4 text-muted small"><?= date('d/m H:i', strtotime($log['created_at'])) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($log['produto']) ?></td>
                                        <td>
                                            <?php if($log['action'] == 'entrada_manual'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success">ENTRADA</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger">SAÍDA</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold"><?= $log['quantity'] ?></td>
                                        <td class="small"><?= htmlspecialchars($log['usuario']) ?></td>
                                        <td class="small text-muted"><?= htmlspecialchars($log['details']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-navy { color: #0A2647; }
    .btn-trust-primary { background-color: #0A2647; color: white; border: none; }
    .btn-trust-primary:hover { background-color: #0d325e; color: white; }
</style>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
