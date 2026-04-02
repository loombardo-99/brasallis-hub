<?php
/**
 * View: rh/usuarios/index
 */
require_once BASE_PATH . '/includes/cabecalho.php';
?>

<div class="container-fluid py-4">
    <!-- Mensagens de Feedback -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
            <i class="fas fa-info-circle me-2"></i>
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>

    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item small"><a href="/admin/painel_admin.php" class="text-decoration-none text-muted">Core</a></li>
                    <li class="breadcrumb-item small active" aria-current="page">Equipe</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-1">Equipe & Permissões</h2>
            <p class="text-muted small mb-0">Gerencie os colaboradores e seus níveis de acesso ao sistema.</p>
        </div>
        <button class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddUsuario">
            <i class="fas fa-plus me-2"></i>Novo Colaborador
        </button>
    </div>

    <!-- Stats Row (Bento Style) -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card card-dashboard p-4 border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box bg-primary-light p-3 rounded-4">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total de Colaboradores</div>
                        <div class="fw-bold fs-4"><?= count($usuarios) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard p-4 border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box bg-success-light p-3 rounded-4">
                        <i class="fas fa-shield-check text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Administradores</div>
                        <div class="fw-bold fs-4">
                            <?= count(array_filter($usuarios, fn($u) => $u['user_type'] === 'admin')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dashboard p-4 border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box bg-warning-light p-3 rounded-4">
                        <i class="fas fa-briefcase text-warning"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Setores Ativos</div>
                        <div class="fw-bold fs-4"><?= count($setores) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Card -->
    <div class="card card-dashboard border-0 overflow-hidden mb-5">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase fw-bold">
                    <tr>
                        <th class="ps-4 py-3">Colaborador</th>
                        <th class="py-3">Nível</th>
                        <th class="py-3">E-mail Corporativo</th>
                        <th class="text-end pe-4 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle bg-gray-100 text-primary fw-bold" style="width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($u['username']) ?></div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">Ativo no sistema</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill <?= $u['user_type'] == 'admin' ? 'bg-primary-light text-primary' : 'bg-gray-100 text-dark border' ?> px-3 py-2">
                                    <?= $u['user_type'] == 'admin' ? 'Administrador' : 'Colaborador' ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                    <button class="btn btn-white btn-sm px-3" onclick="editUsuario(<?= $u['id'] ?>)" title="Editar">
                                        <i class="fas fa-pen-to-square text-muted"></i>
                                    </button>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <button class="btn btn-white btn-sm px-3" onclick="deleteUsuario(<?= $u['id'] ?>)" title="Remover">
                                            <i class="fas fa-trash-can text-danger"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Moderno (M3 Style) -->
<div class="modal fade" id="modalAddUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg p-3" style="border-radius: 28px;">
            <form action="/admin/usuarios.php/store" method="POST">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Novo Colaborador</h5>
                        <p class="text-muted small mb-0">Preencha os dados e defina o acesso.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">NOME DE USUÁRIO</label>
                            <input type="text" name="username" class="form-control bg-light border-0 py-2 px-3" style="border-radius: 12px;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">E-MAIL</label>
                            <input type="email" name="email" class="form-control bg-light border-0 py-2 px-3" style="border-radius: 12px;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">SENHA TEMPORÁRIA</label>
                            <input type="password" name="password" class="form-control bg-light border-0 py-2 px-3" style="border-radius: 12px;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">NÍVEL DE ACESSO</label>
                            <select name="user_type" class="form-select bg-light border-0 py-2 px-3" style="border-radius: 12px;" required>
                                <option value="employee">Colaborador (Restrito)</option>
                                <option value="admin">Administrador (Total)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">SETOR / DEPARTAMENTO</label>
                            <select name="setor_id" class="form-select bg-light border-0 py-2 px-3" style="border-radius: 12px;" onchange="carregarCargos(this.value, 'add-cargo-select')">
                                <option value="">Selecione...</option>
                                <?php foreach ($setores as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">CARGO</label>
                            <select name="cargo_id" id="add-cargo-select" class="form-select bg-light border-0 py-2 px-3" style="border-radius: 12px;">
                                <option value="">Selecione o setor primeiro</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm">Cadastrar Agora</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function carregarCargos(setorId, selectId) {
    const select = document.getElementById(selectId);
    if (!setorId) {
        select.innerHTML = '<option value="">Selecione o setor primeiro</option>';
        return;
    }
    // Updated to point to our new legacy API endpoint
    fetch(`/admin/get_cargos.php?setor_id=${setorId}`)
        .then(res => res.json())
        .then(cargos => {
            select.innerHTML = '<option value="">Selecione um cargo</option>';
            cargos.forEach(c => {
                select.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
            });
            select.innerHTML += '<option value="new" class="fw-bold text-primary">+ Novo Cargo...</option>';
        });
}

function deleteUsuario(id) {
    if (confirm('Deseja realmente remover este colaborador?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/usuarios.php/delete/${id}`; // Shim updated action
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = '_method';
        hiddenField.value = 'DELETE';
        form.appendChild(hiddenField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once BASE_PATH . '/includes/rodape.php'; ?>

