<?php
// admin/organizacao.php
session_start();
require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/cabecalho.php';

// Check Auth & Permission (Only Admin for now)
if ($_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// --- HANDLE ACTIONS ---
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_sector') {
        $nome = sanitize_input($_POST['nome']);
        $cor = sanitize_input($_POST['cor']);
        
        if ($nome) {
            $stmt = $conn->prepare("INSERT INTO setores (empresa_id, nome, cor_hex) VALUES (?, ?, ?)");
            $stmt->execute([$empresa_id, $nome, $cor]);
            $msg = '<div class="alert alert-success">Setor criado com sucesso!</div>';
        }
    }
}

// --- FETCH DATA ---
try {
    // Get Sectors
    $stmt = $conn->prepare("
        SELECT s.*, 
               (SELECT COUNT(*) FROM usuario_setor us WHERE us.setor_id = s.id) as total_users
        FROM setores s 
        WHERE s.empresa_id = ? 
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$empresa_id]);
    $setores = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $msg = '<div class="alert alert-danger">Erro ao carregar dados: ' . $e->getMessage() . '</div>';
    $setores = [];
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1">Minha Organização</h2>
            <p class="text-secondary small">Gerencie os departamentos e a estrutura hierárquica da sua empresa.</p>
        </div>
        <button class="btn btn-primary btn-trust-primary" data-bs-toggle="modal" data-bs-target="#modalNewSector">
            <i class="fas fa-plus me-2"></i>Novo Setor
        </button>
    </div>

    <?= $msg ?>

    <!-- PYRAMID VIEW (Visual Representation) -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <div class="card card-dashboard border-0 shadow-sm">
                <div class="card-body p-5 text-center bg-light rounded-3" style="background-image: radial-gradient(#e5e7eb 1px, transparent 1px); background-size: 20px 20px;">
                    <div class="d-inline-block p-3 rounded-circle bg-navy text-white mb-3 shadow-lg" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        <i class="fas fa-building"></i>
                    </div>
                    <h4 class="fw-bold text-navy"><?= $_SESSION['empresa_nome'] ?? 'Matriz' ?></h4>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill mb-4">CEO / Diretoria</span>
                    
                    <!-- Connectors -->
                    <div class="d-flex justify-content-center my-3">
                        <div style="width: 2px; height: 40px; background: #cbd5e1;"></div>
                    </div>
                    <div class="d-flex justify-content-center position-relative" style="margin-top: -10px;">
                        <div style="height: 2px; background: #cbd5e1; width: 60%;"></div>
                    </div>
                    <div class="d-flex justify-content-around mt-0">
                         <?php foreach($setores as $index => $setor): ?>
                            <div class="d-flex flex-column align-items-center" style="margin-top: -2px;"> <!-- Pull up to touch line -->
                                <div style="width: 2px; height: 20px; background: #cbd5e1;"></div>
                                <div class="card border-0 shadow-sm mt-2 card-hover-effect" style="width: 180px;">
                                    <div class="card-body p-3 text-start border-top border-4" style="border-color: <?= $setor['cor_hex'] ?> !important;">
                                        <h6 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($setor['nome']) ?></h6>
                                        <small class="text-muted d-block mb-2">
                                            <i class="fas fa-users me-1 text-secondary"></i> <?= $setor['total_users'] ?> membros
                                        </small>
                                        <a href="setor_dashboard.php?id=<?= $setor['id'] ?>" class="btn btn-sm btn-trust-primary w-100 mb-1">Acessar</a>
                                        <a href="setor_config.php?id=<?= $setor['id'] ?>" class="btn btn-sm btn-outline-light text-secondary w-100 border bg-light">Configurar</a>
                                    </div>
                                </div>
                            </div>
                         <?php endforeach; ?>
                         <?php if(empty($setores)): ?>
                            <div class="text-muted small mt-3">Nenhum setor criado. Adicione departamentos para ver a estrutura.</div>
                         <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LIST VIEW -->
    <h5 class="fw-bold text-navy mb-3"><i class="fas fa-list me-2"></i>Detalhamento dos Setores</h5>
    <div class="row g-4">
        <?php foreach ($setores as $setor): ?>
        <div class="col-md-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="icon-shape rounded-3 text-white" style="background-color: <?= $setor['cor_hex'] ?>;">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-0" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="setor_config.php?id=<?= $setor['id'] ?>">Editar</a></li>
                                <li><form method="POST" onsubmit="return confirm('Tem certeza?');" style="display:inline;"><input type="hidden" name="action" value="delete_sector"><input type="hidden" name="id" value="<?= $setor['id'] ?>"><button type="submit" class="dropdown-item text-danger">Excluir</button></form></li>
                            </ul>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($setor['nome']) ?></h5>
                    <p class="text-secondary small mb-3">Sem responsável definido</p>
                    
                    <div class="d-flex align-items-center gap-2 mb-3">
                         <!-- Avatars would go here -->
                         <div class="small text-muted"><i class="fas fa-user mb-1"></i> <?= $setor['total_users'] ?> colaboradores</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="setor_dashboard.php?id=<?= $setor['id'] ?>" class="btn btn-trust-primary btn-sm">Acessar Painel</a>
                        <a href="setor_config.php?id=<?= $setor['id'] ?>" class="btn btn-outline-secondary btn-sm">Configurar</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL NEW SECTOR (CATALOG) -->
<div class="modal fade" id="modalNewSector" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-navy">Adicionar Novo Setor</h5>
                    <p class="text-secondary small mb-0">Selecione um modelo de departamento para começar.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-4">
                <form method="POST" id="formNewSector">
                    <input type="hidden" name="action" value="create_sector">
                    <input type="hidden" name="nome" id="inputNome">
                    <input type="hidden" name="cor" id="inputCor">
                    
                    <div class="row g-3">
                        <!-- Templates -->
                        <?php
                        $templates = [
                            ['nome' => 'Financeiro', 'icon' => 'fas fa-chart-line', 'cor' => '#2C7865', 'desc' => 'Gestão de caixa e contas.'],
                            ['nome' => 'Recursos Humanos', 'icon' => 'fas fa-users', 'cor' => '#D63484', 'desc' => 'Colaboradores e benefícios.'],
                            ['nome' => 'Comercial & Vendas', 'icon' => 'fas fa-handshake', 'cor' => '#FF9800', 'desc' => 'CRM e pipelines.'],
                            ['nome' => 'Tecnologia / TI', 'icon' => 'fas fa-laptop-code', 'cor' => '#0F1035', 'desc' => 'Gestão de sistemas.'],
                            ['nome' => 'Logística', 'icon' => 'fas fa-truck', 'cor' => '#795548', 'desc' => 'Estoque e entregas.'],
                            ['nome' => 'Marketing', 'icon' => 'fas fa-bullhorn', 'cor' => '#9C27B0', 'desc' => 'Campanhas e leads.'],
                            ['nome' => 'Jurídico', 'icon' => 'fas fa-balance-scale', 'cor' => '#607D8B', 'desc' => 'Contratos e legal.'],
                            ['nome' => 'Produção', 'icon' => 'fas fa-industry', 'cor' => '#3E2723', 'desc' => 'Fábrica e processos.'],
                            ['nome' => 'Personalizado', 'icon' => 'fas fa-plus', 'cor' => '#6c757d', 'desc' => 'Criar do zero.']
                        ];
                        
                        foreach($templates as $tpl): 
                            $isCustom = $tpl['nome'] === 'Personalizado';
                        ?>
                        <div class="col-md-4 col-sm-6">
                            <button type="button" class="card h-100 w-100 border-0 shadow-sm template-card text-start p-3 position-relative"
                                    onclick="<?= $isCustom ? 'showCustomForm()' : 'selectTemplate(\''.$tpl['nome'].'\', \''.$tpl['cor'].'\')' ?>">
                                <div class="icon-shape rounded-3 text-white mb-3" style="background-color: <?= $tpl['cor'] ?>; width: 40px; height: 40px; display:flex; align-items:center; justify-content:center;">
                                    <i class="<?= $tpl['icon'] ?>"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1"><?= $tpl['nome'] ?></h6>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem;"><?= $tpl['desc'] ?></p>
                                
                                <div class="hover-overlay position-absolute top-0 start-0 w-100 h-100 rounded-3 d-flex align-items-center justify-content-center" 
                                     style="background: rgba(10, 38, 71, 0.9); opacity: 0; transition: opacity 0.2s;">
                                    <span class="text-white fw-bold"><i class="fas fa-check-circle me-1"></i> Selecionar</span>
                                </div>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Custom Form (Hidden by default) -->
                    <div id="customSectorForm" class="mt-4 p-3 bg-light rounded-3 d-none fade-in">
                        <h6 class="fw-bold mb-3">Detalhes do Novo Setor</h6>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">NOME</label>
                                <input type="text" id="customName" class="form-control" placeholder="Ex: Pesquisa & Desenvolvimento">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">COR</label>
                                <input type="color" id="customColor" class="form-control form-control-color w-100" value="#6c757d">
                            </div>
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-light btn-sm me-2" onclick="hideCustomForm()">Cancelar</button>
                                <button type="button" class="btn btn-primary btn-sm" onclick="submitCustom()">Criar Setor</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function selectTemplate(nome, cor) {
    if(confirm('Criar o setor "' + nome + '"?')) {
        document.getElementById('inputNome').value = nome;
        document.getElementById('inputCor').value = cor;
        document.getElementById('formNewSector').submit();
    }
}

function showCustomForm() {
    // Hide grid? No, just show form below or modal swap. Let's scroll to it.
    const form = document.getElementById('customSectorForm');
    form.classList.remove('d-none');
    form.scrollIntoView({behavior: 'smooth'});
}

function hideCustomForm() {
    document.getElementById('customSectorForm').classList.add('d-none');
}

function submitCustom() {
    const nome = document.getElementById('customName').value;
    const cor = document.getElementById('customColor').value;
    if(!nome) { alert('Digite um nome para o setor.'); return; }
    
    document.getElementById('inputNome').value = nome;
    document.getElementById('inputCor').value = cor;
    document.getElementById('formNewSector').submit();
}
</script>

<style>
    .template-card:hover .hover-overlay { opacity: 1 !important; }
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<style>
    .text-navy { color: #0A2647; }
    .bg-navy { background-color: #0A2647 !important; }
    .btn-trust-primary { background-color: #0A2647; color: white; border: none; }
    .btn-trust-primary:hover { background-color: #0d325e; color: white; }
    .card-hover-effect { transition: transform 0.2s; }
    .card-hover-effect:hover { transform: translateY(-5px); }
</style>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
