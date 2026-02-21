<?php
// modules/crm/views/kanban.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';
require_once __DIR__ . '/../../../includes/cabecalho.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../../login.php'); exit; }
if (!check_permission('crm', 'leitura')) { header('Location: index.php?error=acesso_negado'); exit; }

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// 1. Get Stages
$stmt = $conn->prepare("SELECT * FROM crm_etapas WHERE empresa_id = ? ORDER BY ordem ASC");
$stmt->execute([$empresa_id]);
$etapas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Se não houver etapas, criar seed inicial
if (empty($etapas)) {
    $seed_sql = "INSERT INTO crm_etapas (empresa_id, nome, ordem, cor_hex) VALUES 
        (?, 'Prospecção', 1, '#6c757d'),
        (?, 'Qualificação', 2, '#0dcaf0'),
        (?, 'Negociação', 3, '#ffc107'),
        (?, 'Fechamento', 4, '#198754')";
    $stmtSeed = $conn->prepare($seed_sql);
    $stmtSeed->execute([$empresa_id, $empresa_id, $empresa_id, $empresa_id]);
    
    // Refresh
    $stmt->execute([$empresa_id]);
    $etapas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 2. Get Opportunities
$stmtOps = $conn->prepare("
    SELECT o.*, c.nome as cliente_nome 
    FROM crm_oportunidades o 
    LEFT JOIN clientes c ON o.cliente_id = c.id
    WHERE o.empresa_id = ?
");
$stmtOps->execute([$empresa_id]);
$oportunidades = $stmtOps->fetchAll(PDO::FETCH_ASSOC);

// Group by Stage
$kanban_data = [];
foreach($etapas as $etapa) {
    $kanban_data[$etapa['id']] = [
        'info' => $etapa,
        'items' => []
    ];
}
foreach($oportunidades as $op) {
    if(isset($kanban_data[$op['etapa_id']])) {
        $kanban_data[$op['etapa_id']]['items'][] = $op;
    }
}
?>

<div class="container-fluid py-4 h-100 d-flex flex-column">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1">Pipeline de Vendas</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php">CRM</a></li>
                    <li class="breadcrumb-item active">Kanban</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-trust-primary" data-bs-toggle="modal" data-bs-target="#newDealModal">
            <i class="fas fa-plus me-2"></i>Nova Oportunidade
        </button>
    </div>

    <!-- KANBAN BOARD -->
    <div class="d-flex gap-3 overflow-auto flex-fill pb-3 kanban-container">
        <?php foreach($kanban_data as $stage_id => $col): ?>
        <div class="kanban-column bg-light rounded-3 p-2 d-flex flex-column" style="min-width: 300px; max-width: 300px;">
            <div class="d-flex justify-content-between align-items-center p-2 mb-2">
                <h6 class="fw-bold mb-0 text-uppercase small" style="border-bottom: 3px solid <?= $col['info']['cor_hex'] ?>; padding-bottom: 4px;">
                    <?= htmlspecialchars($col['info']['nome']) ?> 
                    <span class="text-muted ms-2 fw-normal">(<?= count($col['items']) ?>)</span>
                </h6>
            </div>
            
            <div class="kanban-dropzone flex-fill" data-stage-id="<?= $stage_id ?>">
                <?php foreach($col['items'] as $item): ?>
                <div class="card border-0 shadow-sm mb-2 kanban-item cursor-grab" draggable="true" data-id="<?= $item['id'] ?>">
                    <div class="card-body p-3">
                        <small class="text-muted d-block mb-1"><?= htmlspecialchars($item['cliente_nome'] ?? 'Sem Cliente') ?></small>
                        <h6 class="fw-bold mb-2 text-navy"><?= htmlspecialchars($item['titulo']) ?></h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-success bg-opacity-10 text-success">R$ <?= number_format($item['valor_estimado'], 2, ',', '.') ?></span>
                            <?php if($item['responsavel_id']): ?>
                                <div class="rounded-circle bg-secondary text-white small d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">U</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal New Deal -->
<div class="modal fade" id="newDealModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Nova Oportunidade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="api_save_deal.php"> 
                <!-- Mock form action for now, ideally JS or sep file -->
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Título do Negócio</label>
                        <input type="text" name="titulo" class="form-control" placeholder="ex: Projeto Site Institucional" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Cliente</label>
                        <select name="cliente_id" class="form-select">
                            <option value="">Selecione...</option>
                            <?php
                                $stmtCli = $conn->prepare("SELECT id, nome FROM clientes WHERE empresa_id = ?");
                                $stmtCli->execute([$empresa_id]);
                                while($c = $stmtCli->fetch()) {
                                    echo "<option value='{$c['id']}'>{$c['nome']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Valor Estimado (R$)</label>
                        <input type="number" step="0.01" name="valor" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-trust-primary">Criar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .text-navy { color: #0A2647; }
    .btn-trust-primary { background-color: #0A2647; color: white; border: none; }
    .kanban-item:active { cursor: grabbing; opacity: 0.8; }
    .kanban-dropzone { min-height: 100px; }
</style>

<script>
    // Simple Drag and Drop Logic
    document.addEventListener('DOMContentLoaded', () => {
        const items = document.querySelectorAll('.kanban-item');
        const dropzones = document.querySelectorAll('.kanban-dropzone');

        items.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', item.getAttribute('data-id'));
                item.classList.add('dragging');
            });
            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
            });
        });

        dropzones.forEach(zone => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('bg-white'); // Highlight
            });
            zone.addEventListener('dragleave', () => {
                zone.classList.remove('bg-white');
            });
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('bg-white');
                const id = e.dataTransfer.getData('text/plain');
                const draggable = document.querySelector(`[data-id="${id}"]`);
                const newStageId = zone.getAttribute('data-stage-id');
                
                zone.appendChild(draggable);
                
                // AJAX Update
                fetch('api_update_deal_stage.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id=${id}&stage_id=${newStageId}`
                });
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
