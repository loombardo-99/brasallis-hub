<?php
// admin/agentes_ia.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/funcoes.php';
require_once '../classes/AIAgent.php';

// Check auth
if (!isset($_SESSION['empresa_id'])) {
    header('Location: ../login.php');
    exit;
}

$conn = connect_db();
$aiAgent = new App\AIAgent($conn);
$empresa_id = $_SESSION['empresa_id'];

// Initial Fetch
$agents = $aiAgent->getAll($empresa_id);
$stats = $aiAgent->getUsageStats($empresa_id);

include_once '../includes/cabecalho.php';
?>

<style>
    /* Premium UI for Agents */
    .agent-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .agent-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 20px;
    }
    .status-active { background: #e6f4ea; color: #1e8e3e; }
    .status-inactive { background: #f1f3f4; color: #5f6368; }

    /* Stats Card */
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        height: 100%;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a73e8;
    }
    .stat-label {
        color: #5f6368;
        font-size: 0.9rem;
        font-weight: 500;
    }
</style>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h1 class="h3 text-gray-800 fw-bold">Agentes Especialistas IA</h1>
        <p class="text-muted">Gerencie e monitore seus assistentes virtuais.</p>
    </div>
    <div class="col-auto">
        <a href="criar_agente.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-plus me-2"></i>Novo Agente
        </a>
    </div>
</div>

<?php
// Carregar Status do Plano
require_once '../classes/AIPlanManager.php';
$planManager = new App\AIPlanManager($conn, $empresa_id); // $conn comes from line 15
$planStatus = $planManager->getPlanStatus();
?>

<!-- AI Consumption Widget -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                
                <!-- Plan Info -->
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white p-3 rounded-circle shadow-sm text-<?php echo $planStatus['color']; ?>">
                        <i class="fas fa-microchip fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Seu Plano</h6>
                        <h3 class="fw-bold mb-0 text-dark">
                            <?php echo $planStatus['label']; ?>
                            <span class="badge bg-<?php echo $planStatus['color']; ?> align-middle ms-2" style="font-size: 0.5em; vertical-align: middle;">ATIVO</span>
                        </h3>
                    </div>
                </div>

                <!-- Usage Bar -->
                <div class="flex-grow-1 mx-lg-5" style="min-width: 250px;">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-bold text-muted">Consumo Mensal</span>
                        <span class="small fw-bold text-<?php echo $planStatus['color']; ?>">
                            <?php echo number_format($planStatus['used']); ?> / <?php echo $planStatus['limit'] > 999999 ? 'Ilimitado' : number_format($planStatus['limit']); ?> tokens
                        </span>
                    </div>
                    <?php if ($planStatus['limit'] > 999999): ?>
                        <div class="progress" style="height: 10px; background-color: #e9ecef;">
                             <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%"></div>
                        </div>
                    <?php else: ?>
                        <div class="progress" style="height: 10px; background-color: #d1d5db;">
                            <div class="progress-bar bg-<?php echo $planStatus['color']; ?>" role="progressbar" 
                                 style="width: <?php echo $planStatus['percentage']; ?>%" 
                                 aria-valuenow="<?php echo $planStatus['percentage']; ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="text-end mt-1">
                        <small class="text-muted" style="font-size: 0.75rem;">Renova em 01/<?php echo date('m', strtotime('+1 month')); ?></small>
                    </div>
                </div>

                <!-- CTA -->
                <div>
                     <?php if ($planStatus['plan'] === 'free'): ?>
                        <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm pulse-btn" onclick="openUpgradeModal()">
                            <i class="fas fa-bolt"></i>
                            <span>Fazer Upgrade</span>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary d-flex align-items-center gap-2" onclick="openUpgradeModal()">
                            <i class="fas fa-cog"></i>
                            <span>Gerenciar Plano</span>
                        </button>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.pulse-btn {
    animation: pulse-primary 2s infinite;
}
@keyframes pulse-primary {
    0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
    100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
}
</style>

<!-- Stats Overview -->
<div class="row g-4 mb-5">

    <?php
    $total_agents = count($agents);
    $active_agents = count(array_filter($agents, fn($a) => $a['status'] === 'active'));
    $total_uses = array_sum(array_column($stats, 'total_uses'));
    $total_tokens = array_sum(array_column($stats, 'total_input')) + array_sum(array_column($stats, 'total_output'));
    ?>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary-subtle p-3 text-primary">
                <i class="fas fa-robot fa-lg"></i>
            </div>
            <div>
                <div class="stat-value"><?php echo $total_agents; ?></div>
                <div class="stat-label">Agentes Criados</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success-subtle p-3 text-success">
                <i class="fas fa-check-circle fa-lg"></i>
            </div>
            <div>
                <div class="stat-value"><?php echo $active_agents; ?></div>
                <div class="stat-label">Agentes Ativos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="rounded-circle bg-info-subtle p-3 text-info">
                <i class="fas fa-comments fa-lg"></i>
            </div>
            <div>
                <div class="stat-value"><?php echo number_format($total_uses); ?></div>
                <div class="stat-label">Interações Totais</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning-subtle p-3 text-warning">
                <i class="fas fa-coins fa-lg"></i>
            </div>
            <div>
                <div class="stat-value"><?php echo number_format($total_tokens); ?></div>
                <div class="stat-label">Tokens Consumidos</div>
            </div>
        </div>
    </div>
</div>

<!-- Agents Grid -->
<div class="row g-4">
    <?php if (empty($agents)): ?>
        <div class="col-12 text-center py-5">
            <img src="../assets/img/empty_state_robot.svg" alt="Sem agentes" style="max-width: 200px; opacity: 0.5;" onerror="this.src='https://placehold.co/200?text=No+Agents'">
            <h4 class="mt-4 text-muted">Nenhum agente criado ainda</h4>
            <p class="text-muted">Comece criando seu primeiro assistente virtual para ajudar em suas tarefas.</p>
            <a href="criar_agente.php" class="btn btn-outline-primary mt-2">Criar Primeiro Agente</a>
        </div>
    <?php else: ?>
        <?php foreach ($agents as $agent): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card agent-card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-gradient bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                                    <i class="fas fa-brain"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($agent['name']); ?></h5>
                                    <small class="text-muted"><?php echo htmlspecialchars($agent['role']); ?></small>
                                </div>
                            </div>
                            <?php 
                                $statusClass = $agent['status'] === 'active' ? 'status-active' : 'status-inactive';
                                $statusLabel = $agent['status'] === 'active' ? 'Ativo' : 'Inativo';
                            ?>
                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                        </div>
                        
                        <p class="text-muted small mb-4 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo htmlspecialchars($agent['system_instruction']); ?>
                        </p>

                        <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                            <div class="d-flex flex-column">
                                <span class="small text-muted fw-bold">Modelo</span>
                                <span class="small badge bg-light text-dark border"><?php echo htmlspecialchars($agent['model']); ?></span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="editar_agente.php?id=<?php echo $agent['id']; ?>" class="btn btn-sm btn-light rounded-circle" title="Editar"><i class="fas fa-edit text-muted"></i></a>
                                <button onclick="window.openAgentChat(<?php echo $agent['id']; ?>)" class="btn btn-sm btn-light rounded-circle" title="Iniciar Conversa"><i class="fas fa-play text-success"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Usage Charts Section (Optional V1) -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-4">Monitoramento de Uso por Agente</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 rounded-start ps-4">Agente</th>
                            <th class="border-0">Uso Total</th>
                            <th class="border-0">Tokens (Entrada)</th>
                            <th class="border-0">Tokens (Saída)</th>
                            <th class="border-0 rounded-end">Último Uso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Sem dados de uso ainda.</td></tr>
                        <?php else: ?>
                            <?php foreach ($stats as $stat): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($stat['agent_name']); ?></td>
                                <td><?php echo $stat['total_uses']; ?></td>
                                <td><?php echo number_format($stat['total_input']); ?></td>
                                <td><?php echo number_format($stat['total_output']); ?></td>
                                <td><?php echo $stat['last_used'] ? date('d/m/Y H:i', strtotime($stat['last_used'])) : '-'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'modal_planos.php'; ?>
<?php include_once '../includes/rodape.php'; ?>
