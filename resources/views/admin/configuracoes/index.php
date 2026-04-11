<?php
/**
 * View: admin/configuracoes/index (Brasallis Solar v4.0)
 * Redesign Ultra-Clean & AI Hub Connectivity (Tabs Layout)
 */
$title = "Configurações da Organização";
require_once BASE_PATH . '/includes/navigation-brasallis.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

    :root {
        --sl-primary: #0070F2;
        --sl-secondary: #0f172a;
        --sl-surface: #ffffff;
        --sl-border: rgba(226, 232, 240, 0.8);
        --sl-muted: #64748b;
    }

    body { background-color: #f8fafc; font-family: 'Outfit', sans-serif; }

    /* Solar Layout */
    .solar-header { margin-bottom: 2.5rem; }
    .solar-title { font-size: 2.5rem; font-weight: 800; color: var(--sl-secondary); letter-spacing: -1.5px; }
    .solar-subtitle { color: var(--sl-muted); font-weight: 500; font-size: 1rem; }

    .solar-card { 
        background: white; border-radius: 24px; padding: 2rem; border: 1px solid var(--sl-border);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
    }

    .solar-label { font-size: 0.75rem; font-weight: 700; color: var(--sl-muted); text-transform: uppercase; letter-spacing: 1.2px; margin-bottom: 0.5rem; display: block; }
    .solar-input { 
        border-radius: 12px; border: 1.5px solid #e2e8f0; padding: 0.8rem 1rem; font-weight: 500; 
        color: var(--sl-secondary); transition: 0.2s; background: #fbfcfd;
    }
    .solar-input:focus { border-color: var(--sl-primary); box-shadow: 0 0 0 4px rgba(0, 112, 242, 0.1); outline: none; background: white; }

    /* Vertical Navigation Pills */
    .config-nav {
        background: white; border-radius: 20px; padding: 1rem;
        border: 1px solid var(--sl-border); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);
    }
    .config-nav .nav-link {
        color: var(--sl-muted); font-weight: 600; border-radius: 12px; padding: 1rem 1.5rem;
        display: flex; align-items: center; gap: 12px; transition: all 0.3s ease;
        margin-bottom: 0.5rem;
    }
    .config-nav .nav-link i { font-size: 1.2rem; width: 24px; text-align: center; }
    .config-nav .nav-link:hover { background: #f1f5f9; color: var(--sl-secondary); }
    .config-nav .nav-link.active {
        background: var(--sl-secondary); color: white;
        box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.15);
    }
    
    .config-nav .nav-link.active i { color: #fff; }

    /* AI Hub Section */
    .ai-hub-badge { 
        background: linear-gradient(135deg, rgba(0, 112, 242, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
        padding: 5px 12px; border-radius: 99px; font-size: 0.7rem; font-weight: 800; color: var(--sl-primary);
        text-transform: uppercase; display: inline-block; margin-bottom: 1rem;
    }
    
    .icon-box { 
        width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center;
        background: #f1f5f9; color: var(--sl-secondary); font-size: 1.4rem;
    }

    /* Secret Input */
    .secret-container { position: relative; }
    .secret-toggle { 
        position: absolute; right: 15px; top: 50%; transform: translateY(-50%); 
        cursor: pointer; color: var(--sl-muted); transition: 0.2s;
    }
    .secret-toggle:hover { color: var(--sl-primary); }

    .btn-solar-save { 
        background: var(--sl-primary); color: white; padding: 1rem 2.5rem; border-radius: 99px; 
        font-weight: 700; border: none; transition: 0.3s;
        box-shadow: 0 10px 15px -3px rgba(0, 112, 242, 0.3);
    }
    .btn-solar-save:hover { transform: translateY(-2px); background: #005bb5; box-shadow: 0 20px 25px -5px rgba(0, 112, 242, 0.4); }

    /* Enterprise Card */
    .enterprise-panel { 
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; border-radius: 20px; padding: 2.5rem;
        position: relative; overflow: hidden;
    }
    .enterprise-panel::after {
        content: ''; position: absolute; top: -50%; right: -20%; width: 250px; height: 250px;
        background: rgba(0, 112, 242, 0.15); filter: blur(60px); border-radius: 50%;
    }

</style>

<div class="container-fluid py-4">
    <!-- Solar Header -->
    <div class="solar-header">
        <h1 class="solar-title">Painel de Controle HUB</h1>
        <p class="solar-subtitle">Controle arquitetural da <?= htmlspecialchars($empresa['name'] ?? 'Empresa') ?>.</p>
    </div>

    <?php if (isset($_SESSION['success']) || isset($_GET['success'])): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 p-4 d-flex align-items-center">
        <i class="fas fa-check-circle text-success me-3 fs-3"></i>
        <div><strong class="d-block text-success">Sincronização Concluída</strong> As suas configurações globais foram atualizadas com sucesso.</div>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <form action="/admin/configuracoes.php" method="POST">
        <div class="row g-4">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="config-nav nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active" id="v-institucional-tab" data-bs-toggle="pill" data-bs-target="#v-institucional" type="button" role="tab">
                        <i class="fas fa-building"></i> Institucional
                    </button>
                    <button class="nav-link" id="v-planos-tab" data-bs-toggle="pill" data-bs-target="#v-planos" type="button" role="tab">
                        <i class="fas fa-credit-card"></i> Planos & Faturamento
                    </button>
                    <button class="nav-link" id="v-apis-tab" data-bs-toggle="pill" data-bs-target="#v-apis" type="button" role="tab">
                        <i class="fas fa-plug"></i> Conexões & API Keys
                    </button>
                    <button class="nav-link" id="v-privacidade-tab" data-bs-toggle="pill" data-bs-target="#v-privacidade" type="button" role="tab">
                        <i class="fas fa-user-shield"></i> Dados & Privacidade
                    </button>
                    <button class="nav-link" id="v-suporte-tab" data-bs-toggle="pill" data-bs-target="#v-suporte" type="button" role="tab">
                        <i class="fas fa-life-ring"></i> Suporte Hub
                    </button>
                </div>
                
                <div class="mt-4 text-center">
                    <button type="submit" class="btn-solar-save w-100 mb-3">
                        <i class="fas fa-cloud-arrow-up me-2"></i> SALVAR
                    </button>
                    <p class="text-muted small">Brasallis 360 v4.0<br>Sistema Seguro</p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-lg-9">
                <div class="tab-content" id="v-pills-tabContent">
                    
                    <!-- TAB: INSTITUCIONAL -->
                    <div class="tab-pane fade show active" id="v-institucional" role="tabpanel">
                        <div class="solar-card">
                            <div class="d-flex align-items-center mb-5">
                                <div class="icon-box me-3"><i class="fas fa-building"></i></div>
                                <div>
                                    <h4 class="fw-bold mb-0">Dados da Empresa</h4>
                                    <p class="small text-muted mb-0">Identidade jurídica e informações públicas do negócio.</p>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="solar-label">Nome Fantasia</label>
                                    <input type="text" name="nome_fantasia" class="form-control solar-input" value="<?= htmlspecialchars($empresa['name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="solar-label">Razão Social</label>
                                    <input type="text" name="razao_social" class="form-control solar-input" value="<?= htmlspecialchars($empresa['razao_social'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="solar-label">CNPJ</label>
                                    <input type="text" name="cnpj" class="form-control solar-input" value="<?= htmlspecialchars($empresa['cnpj'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="solar-label">Email Operacional</label>
                                    <input type="email" name="email_contato" class="form-control solar-input" value="<?= htmlspecialchars($empresa['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="solar-label">Telefone / WhatsApp</label>
                                    <input type="text" name="telefone" class="form-control solar-input" value="<?= htmlspecialchars($empresa['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="solar-label">Endereço de Matriz</label>
                                    <input type="text" name="endereco" class="form-control solar-input" value="<?= htmlspecialchars($empresa['address'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: PLANOS E FATURAMENTO -->
                    <div class="tab-pane fade" id="v-planos" role="tabpanel">
                        <div class="enterprise-panel mb-4 shadow-sm">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="fw-bold opacity-75 mb-3 text-uppercase ls-1" style="font-size: 0.75rem;">SEU PLANO ATUAL</h5>
                                    <div class="h1 fw-bold mb-2">Plano <?= ucfirst(htmlspecialchars($empresa['ai_plan'] ?? 'Enterprise')) ?></div>
                                    <div class="d-flex align-items-center gap-2 mt-3">
                                        <span class="badge bg-success rounded-pill px-3 py-2">ATIVO</span>
                                        <span class="small opacity-75">100% de performance habilitada</span>
                                    </div>
                                </div>
                                <i class="fas fa-crown fs-1 opacity-25"></i>
                            </div>
                            <div class="mt-5 pt-4 border-top border-light border-opacity-25 pb-2">
                                <p class="small mb-0 opacity-75">Sua licença da inteligência Brasallis Hub renova no próximo mês. <br><a href="#" class="text-white fw-bold text-decoration-underline mt-2 d-inline-block">Ver Faturas Anteriores</a></p>
                            </div>
                        </div>
                        
                        <div class="solar-card text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 200px;">
                            <div class="icon-box mb-3" style="background:#e0e7ff; color: #4338ca;"><i class="fas fa-credit-card"></i></div>
                            <h5 class="fw-bold">Método de Pagamento</h5>
                            <p class="text-muted small">Cartão de Crédito final <b>4242</b> gerenciado pelo MercadoPago.</p>
                            <button type="button" class="btn btn-outline-secondary rounded-pill mt-2 px-4 shadow-sm disabled">Alterar Cartão</button>
                        </div>
                    </div>

                    <!-- TAB: APIs & INTEGRAÇÕES -->
                    <div class="tab-pane fade" id="v-apis" role="tabpanel">
                        <div class="solar-card mb-4">
                            <div class="ai-hub-badge">Inteligência Artificial</div>
                            <div class="d-flex align-items-center mb-4 mt-2">
                                <div class="icon-box me-3"><i class="fas fa-brain text-primary"></i></div>
                                <div>
                                    <h4 class="fw-bold mb-0">Modelos de IA (LLMs)</h4>
                                    <p class="small text-muted mb-0">Conecte Google e OpenAI para o IQ da empresa.</p>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="solar-label">Google Gemini API Key</label>
                                    <div class="secret-container">
                                        <input type="password" name="gemini_api_key" class="form-control solar-input w-100" value="<?= htmlspecialchars($empresa['gemini_api_key'] ?? '') ?>" placeholder="AIzaSy...">
                                        <i class="fas fa-eye secret-toggle" onclick="toggleSecret(this)"></i>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="solar-label">OpenAI API Key (ChatGPT / Whisper)</label>
                                    <div class="secret-container">
                                        <input type="password" name="openai_api_key" class="form-control solar-input w-100" value="<?= htmlspecialchars($empresa['openai_api_key'] ?? '') ?>" placeholder="sk-...">
                                        <i class="fas fa-eye secret-toggle" onclick="toggleSecret(this)"></i>
                                    </div>
                                    <p class="text-xs text-muted mt-2" style="font-size: 0.75rem;"><i class="fas fa-shield-alt text-success me-1"></i> Chaves encriptadas no banco de dados.</p>
                                </div>
                            </div>
                        </div>

                        <div class="solar-card">
                            <div class="ai-hub-badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">Checkouts de Terceiros</div>
                            <div class="d-flex align-items-center mb-4 mt-2">
                                <div class="icon-box me-3"><i class="fas fa-shopping-bag text-warning"></i></div>
                                <div>
                                    <h4 class="fw-bold mb-0">Meios de Pagamento</h4>
                                    <p class="small text-muted mb-0">Gateways usados nas cobranças digitais.</p>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="solar-label">Mercado Pago Access Token</label>
                                    <div class="secret-container">
                                        <input type="password" name="mp_access_token" class="form-control solar-input w-100" value="<?= htmlspecialchars($empresa['mp_access_token'] ?? '') ?>" placeholder="APP_USR-...">
                                        <i class="fas fa-eye secret-toggle" onclick="toggleSecret(this)"></i>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="solar-label">Pagar.me API Key</label>
                                    <div class="secret-container">
                                        <input type="password" name="pagarme_key" class="form-control solar-input w-100" value="<?= htmlspecialchars($empresa['pagarme_key'] ?? '') ?>" placeholder="ak_live...">
                                        <i class="fas fa-eye secret-toggle" onclick="toggleSecret(this)"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: DADOS E PRIVACIDADE -->
                    <div class="tab-pane fade" id="v-privacidade" role="tabpanel">
                        <div class="solar-card">
                            <div class="d-flex align-items-center mb-4">
                                <div class="icon-box me-3"><i class="fas fa-user-shield text-dark"></i></div>
                                <div>
                                    <h4 class="fw-bold mb-0">LGPD & Privacidade</h4>
                                    <p class="small text-muted mb-0">Controle do núcleo de segurança dos dados da plataforma.</p>
                                </div>
                            </div>

                            <div class="list-group list-group-flush mb-4">
                                <div class="list-group-item bg-transparent border-bottom px-0 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark mb-1">Backup Redundante Diário</div>
                                            <div class="text-muted small">Cópia completa e segura em nossos servidores Cloud.</div>
                                        </div>
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">ATIVO</span>
                                    </div>
                                </div>
                                <div class="list-group-item bg-transparent border-bottom px-0 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark mb-1">Exclusão Contínua (Retenção)</div>
                                            <div class="text-muted small">Apagar atividades de logs e chats com IAs antigas após 6 meses.</div>
                                        </div>
                                        <div class="form-check form-switch fs-4">
                                            <input class="form-check-input" type="checkbox" checked role="switch">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-secondary border-0 mb-0 d-flex gap-3 mt-4 rounded-4 px-4 py-3">
                                <i class="fas fa-file-contract text-secondary fs-4"></i>
                                <div>
                                    <p class="mb-1 fw-bold">Termos e Acordos Governamentais</p>
                                    <a href="#" class="small text-decoration-underline text-secondary">Ler Termos de Uso e DPAs</a>
                                </div>
                            </div>
                            
                            <div class="mt-5 border-top pt-4">
                                <h6 class="fw-bold text-danger">ZONA DE RISCO</h6>
                                <p class="small text-muted mb-3">Apagar permanentemente a conta, clientes e histórico desta empresa.</p>
                                <button type="button" class="btn btn-outline-danger">Solicitar Exclusão de Conta</button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: SUPORTE -->
                    <div class="tab-pane fade" id="v-suporte" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="solar-card text-center d-flex flex-column align-items-center justify-content-center">
                                    <div class="icon-box mb-4" style="width:64px;height:64px;font-size:2rem;background:#ecfdf5;color:#10b981;"><i class="fab fa-whatsapp"></i></div>
                                    <h5 class="fw-bold">Assistente Humanizado</h5>
                                    <p class="text-muted small px-3">Atendimento imediato pelo WhatsApp para seu plano.</p>
                                    <a href="#" class="btn btn-success rounded-pill px-4 mt-2">Chamar Especialista</a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="solar-card text-center d-flex flex-column align-items-center justify-content-center">
                                    <div class="icon-box mb-4" style="width:64px;height:64px;font-size:2rem;background:#eff6ff;color:#3b82f6;"><i class="fas fa-book-open"></i></div>
                                    <h5 class="fw-bold">Wiki & Base de Ajuda</h5>
                                    <p class="text-muted small px-3">Guias de uso do Brasallis, PDV e Automações.</p>
                                    <a href="#" class="btn btn-primary rounded-pill px-4 mt-2">Acessar Tutoriais</a>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <div class="solar-card border-0" style="background:#1e293b; color: white;">
                                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between p-2">
                                        <div class="d-flex align-items-center mb-3 mb-md-0 gap-3">
                                            <i class="fas fa-headset fs-1 text-primary"></i>
                                            <div>
                                                <h5 class="mb-1 fw-bold">Atendimento Premium IA</h5>
                                                <p class="mb-0 small opacity-75">Resolução de chamados técnicos diretos pelo sistema.</p>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold">Abrir Ticket</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<script>
function toggleSecret(el) {
    const input = el.previousElementSibling;
    if (input.type === "password") {
        input.type = "text";
        el.classList.remove("fa-eye");
        el.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        el.classList.remove("fa-eye-slash");
        el.classList.add("fa-eye");
    }
}
</script>

<?php require_once BASE_PATH . '/includes/rodape.php'; ?>
