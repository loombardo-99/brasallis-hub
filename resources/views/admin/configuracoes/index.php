<?php
/**
 * View: admin/configuracoes/index
 */
$title = "Configurações da Organização";
require BASE_PATH . '/resources/views/layouts/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold text-navy mb-1">Configurações</h2>
    <p class="text-secondary mb-0">Gerencie as informações institucionais e operacionais da sua empresa.</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-premium border-0 shadow-sm p-4">
            <h5 class="fw-bold text-navy mb-4"><i class="fas fa-building me-2"></i>Dados da Empresa</h5>
            
            <form action="/admin/configuracoes" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Nome Fantasia</label>
                        <input type="text" name="nome_fantasia" class="form-control form-control-premium" value="<?= htmlspecialchars($empresa['nome_fantasia']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Razão Social</label>
                        <input type="text" name="razao_social" class="form-control form-control-premium" value="<?= htmlspecialchars($empresa['razao_social']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">CNPJ</label>
                        <input type="text" name="cnpj" class="form-control form-control-premium" value="<?= htmlspecialchars($empresa['cnpj']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Email de Contato</label>
                        <input type="email" name="email_contato" class="form-control form-control-premium" value="<?= htmlspecialchars($empresa['email_contato']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Telefone / WhatsApp</label>
                        <input type="text" name="telefone" class="form-control form-control-premium" value="<?= htmlspecialchars($empresa['telefone']) ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted text-uppercase">Endereço Completo</label>
                        <textarea name="endereco" class="form-control form-control-premium" rows="2"><?= htmlspecialchars($empresa['endereco']) ?></textarea>
                    </div>
                </div>

                <div class="mt-5 border-top pt-4 text-end">
                    <button type="submit" class="btn btn-premium btn-dark px-5 py-3 shadow-sm">
                        <i class="fas fa-save me-2"></i>Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-premium border-0 shadow-sm p-4 mb-4 bg-navy text-white">
            <h5 class="fw-bold mb-3">Plano Atual</h5>
            <div class="d-flex align-items-center mb-3">
                <div class="h2 fw-bold mb-0">Enterprise</div>
                <span class="badge bg-white text-navy ms-3">Ativo</span>
            </div>
            <p class="small opacity-75 mb-0">Sua assinatura renova automaticamente em 15/10/2026.</p>
        </div>

        <div class="card-premium border-0 shadow-sm p-4 h-100">
            <h5 class="fw-bold text-navy mb-4">Segurança e Acesso</h5>
            <div class="list-group list-group-flush border-0">
                <a href="#" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-navy small">Autenticação de Dois Fatores</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Proteja sua conta com 2FA</div>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>
                <a href="/rh/usuarios" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-navy small">Gerenciar Permissões</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Controle quem acessa o quê</div>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/resources/views/layouts/footer.php'; ?>
