<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/funcoes.php';
$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// Lógica de POST para salvar as configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Atualizar informações da empresa
    if ($action === 'update_company_info') {
        $stmt = $conn->prepare("UPDATE empresas SET name = ?, address = ?, phone = ?, email = ?, cnpj = ?, website = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['address'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['cnpj'],
            $_POST['website'],
            $empresa_id
        ]);
    }

    // Mudar plano
    if ($action === 'change_plan') {
        $stmt = $conn->prepare("UPDATE usuarios SET plan = ? WHERE empresa_id = ? AND user_type = 'admin'");
        $stmt->execute([$_POST['plan'], $empresa_id]);
    }

    // Configuração IA
    if ($action === 'update_ai_config') {
        $stmt = $conn->prepare("UPDATE empresas SET gemini_api_key = ? WHERE id = ?");
        $stmt->execute([$_POST['gemini_api_key'], $empresa_id]);
    }

    // Configuração IA
    if ($action === 'update_ai_config') {
        $stmt = $conn->prepare("UPDATE empresas SET gemini_api_key = ? WHERE id = ?");
        $stmt->execute([$_POST['gemini_api_key'], $empresa_id]);
    }

    // Configuração de Branding (Identidade Visual)
    if ($action === 'update_branding') {
        $primary = $_POST['branding_primary_color'] ?? '#2563eb';
        $style = $_POST['branding_bg_style'] ?? 'original';

        $stmt = $conn->prepare("UPDATE empresas SET branding_primary_color = ?, branding_bg_style = ? WHERE id = ?");
        $stmt->execute([$primary, $style, $empresa_id]);
        
        // Update session immediately for feedback
        if (isset($_SESSION['branding'])) {
            $_SESSION['branding']['branding_primary_color'] = $primary;
            $_SESSION['branding']['branding_bg_style'] = $style;
        } else {
            $_SESSION['branding'] = [
                'branding_primary_color' => $primary,
                'branding_secondary_color' => '#1e293b',
                'branding_bg_style' => $style
            ];
        }
    }

    // Remover conta
    if ($action === 'delete_account') {
        // Adicionar uma verificação de segurança extra, como confirmar a senha
        $stmt = $conn->prepare("DELETE FROM empresas WHERE id = ?");
        $stmt->execute([$empresa_id]);
        // Destruir a sessão e redirecionar para a página de login
        session_destroy();
        header("Location: ../index.php");
        exit;
    }

    header("Location: configuracoes.php?success=1");
    exit;
}

// Buscar dados atuais para preencher o formulário
$stmt_empresa = $conn->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt_empresa->execute([$empresa_id]);
$empresa = $stmt_empresa->fetch(PDO::FETCH_ASSOC);

$stmt_usuario = $conn->prepare("SELECT * FROM usuarios WHERE empresa_id = ? AND user_type = 'admin'");
$stmt_usuario->execute([$empresa_id]);
$usuario_admin = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

include_once '../includes/cabecalho.php';
?>

<h1 class="mb-4">Configurações</h1>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Configurações salvas com sucesso!</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#info">Informações da Empresa</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#plan">Plano e Assinatura</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#branding">Identidade Visual</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ai_config">Integração IA</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#account">Gerenciamento da Conta</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <!-- Informações da Empresa -->
            <div class="tab-pane fade show active" id="info">
                <form action="configuracoes.php" method="POST">
                    <input type="hidden" name="action" value="update_company_info">
                    <div class="mb-3"><label class="form-label">Nome da Empresa</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($empresa['name'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">Endereço</label><textarea name="address" class="form-control"><?php echo htmlspecialchars($empresa['address'] ?? ''); ?></textarea></div>
                    <div class="mb-3"><label class="form-label">Telefone</label><input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($empresa['phone'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">E-mail de Contato</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($empresa['email'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">CNPJ</label><input type="text" name="cnpj" class="form-control" value="<?php echo htmlspecialchars($empresa['cnpj'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">Website</label><input type="text" name="website" class="form-control" value="<?php echo htmlspecialchars($empresa['website'] ?? ''); ?>"></div>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </form>
            </div>

            <!-- Plano e Assinatura -->
            <div class="tab-pane fade" id="plan">
                <div class="text-center py-4">
                    <h5 class="text-muted mb-3">Seu Plano Atual</h5>
                    <div class="d-inline-block p-3 rounded-4 bg-light mb-4 position-relative">
                        <?php 
                            $currentPlan = $empresa['ai_plan'] ?? 'free';
                            $badgeColor = match($currentPlan) {
                                'free' => 'secondary',
                                'growth' => 'success',
                                'enterprise' => 'primary',
                                default => 'secondary'
                            };
                        ?>
                        <span class="badge bg-<?php echo $badgeColor; ?> fs-4 px-4 py-2 text-uppercase mb-2"><?php echo ucfirst($currentPlan); ?></span>
                        
                        <?php if($empresa['plan_expires_at']): ?>
                            <p class="mb-0 text-muted small"><i class="fas fa-calendar-alt me-1"></i> Expira em: <?php echo date('d/m/Y', strtotime($empresa['plan_expires_at'])); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if($currentPlan !== 'enterprise'): ?>
                        <div class="d-block">
                            <p class="text-muted mb-3">Deseja liberar mais recursos?</p>
                            <a href="planos.php" class="btn btn-gradient-primary rounded-pill px-5 py-3 fw-bold shadow text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
                                <i class="fas fa-rocket me-2"></i> Ver Opções de Upgrade
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-primary d-inline-block">
                            <i class="fas fa-crown me-2"></i> Você possui o plano máximo!
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Identidade Visual (Branding) -->
            <div class="tab-pane fade" id="branding">
                <h4 class="mb-4 text-primary">Identidade Visual</h4>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    A identidade visual do sistema está definida como <strong>Padrão Corporativo (WiseFlow)</strong> para garantir consistência e usabilidade em todos os módulos.
                </div>
                <div class="row">
                    <div class="col-md-7">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Tema Atual</label>
                            <div class="p-3 border rounded bg-light d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Sistema Unificado</h6>
                                    <small class="text-muted">Tema Otimizado para Alta Performance e Leitura</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Integração IA -->
            <div class="tab-pane fade" id="ai_config">
                <h4 class="mb-4 text-primary">Configuração de Inteligência Artificial</h4>
                <form action="configuracoes.php" method="POST">
                    <input type="hidden" name="action" value="update_ai_config">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Gemini API Key</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" name="gemini_api_key" class="form-control" value="<?php echo htmlspecialchars($empresa['gemini_api_key'] ?? ''); ?>" placeholder="Ex: AIzaSy..." autocomplete="off">
                        </div>
                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle me-1"></i> Necessária para que os Agentes IA funcionem. 
                            <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-decoration-none fw-bold">Obter chave <i class="fas fa-external-link-alt small"></i></a>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Salvar Chave</button>
                </form>
            </div>

            <!-- Gerenciamento da Conta -->
            <div class="tab-pane fade" id="account">
                <h4>Remover Conta</h4>
                <p>Esta ação é irreversível e irá apagar todos os dados da sua empresa, incluindo produtos, vendas e usuários.</p>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">Remover Minha Conta</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação para Remover Conta -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Remoção da Conta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Você tem certeza que deseja remover sua conta? Todos os seus dados serão permanentemente apagados.</p>
            </div>
            <div class="modal-footer">
                <form action="configuracoes.php" method="POST">
                    <input type="hidden" name="action" value="delete_account">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sim, Remover Conta</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/rodape.php'; ?>
