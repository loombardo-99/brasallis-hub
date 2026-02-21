<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Apenas administradores podem gerenciar usuários
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    // Redireciona para uma página de acesso negado ou para o painel principal
    header('Location: painel_admin.php');
    exit;
}

require_once '../includes/funcoes.php';

$conn = connect_db();

// Busca setores para o dropdown
$setoresDisponiveis = [];
try {
    $stmtSetor = $conn->prepare("SELECT id, nome FROM setores WHERE empresa_id = ? ORDER BY nome ASC");
    $stmtSetor->execute([$_SESSION['empresa_id']]);
    $setoresDisponiveis = $stmtSetor->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* silent fail */ }

// --- LÓGICA DE MANIPULAÇÃO (POST REQUESTS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $empresa_id = $_SESSION['empresa_id'];
    $message = '';
    $message_type = 'danger';

    try {
        // Adicionar Usuário
        if ($action === 'add') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $user_type = $_POST['user_type'];
            
            $stmt = $conn->prepare("INSERT INTO usuarios (empresa_id, username, email, password, user_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$empresa_id, $username, $email, $password, $user_type]);
            $newUserId = $conn->lastInsertId();
            
            // Salvar Setor/Cargo
            if (!empty($_POST['setor_id'])) {
                $cargoId = !empty($_POST['cargo_id']) ? $_POST['cargo_id'] : null;
                
                // Lógica de Cargo Dinâmico (Criar na hora)
                if ($cargoId === 'new' && !empty($_POST['novo_cargo_nome'])) {
                    $novoCargo = sanitize_input($_POST['novo_cargo_nome']);
                    // Verifica se já existe para evitar duplicatas simples
                    $stmtCheck = $conn->prepare("SELECT id FROM cargos WHERE setor_id = ? AND nome = ?");
                    $stmtCheck->execute([$_POST['setor_id'], $novoCargo]);
                    $existing = $stmtCheck->fetchColumn();
                    
                    if ($existing) {
                        $cargoId = $existing;
                    } else {
                        $stmtNewCargo = $conn->prepare("INSERT INTO cargos (setor_id, nome, nivel_hierarquia) VALUES (?, ?, 1)");
                        $stmtNewCargo->execute([$_POST['setor_id'], $novoCargo]);
                        $cargoId = $conn->lastInsertId();
                    }
                }

                $stmtLink = $conn->prepare("INSERT INTO usuario_setor (user_id, setor_id, cargo_id) VALUES (?, ?, ?)");
                $stmtLink->execute([$newUserId, $_POST['setor_id'], $cargoId]);
            }

            $message = 'Usuário adicionado com sucesso!';
            $message_type = 'success';
        }

        // Editar Usuário
        if ($action === 'edit') {
            $id = $_POST['id'];
            $username = $_POST['username'];
            $email = $_POST['email'];
            $user_type = $_POST['user_type'];

            // Atualiza a senha apenas se uma nova for fornecida
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE usuarios SET username=?, email=?, user_type=?, password=? WHERE id=? AND empresa_id = ?");
                $stmt->execute([$username, $email, $user_type, $password, $id, $empresa_id]);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET username=?, email=?, user_type=? WHERE id=? AND empresa_id = ?");
                $stmt->execute([$username, $email, $user_type, $id, $empresa_id]);
            }
            
            // Atualizar Setor/Cargo (Remove anterior e adiciona novo)
            $conn->prepare("DELETE FROM usuario_setor WHERE user_id = ?")->execute([$id]);
            
            if (!empty($_POST['setor_id'])) {
                $cargoId = !empty($_POST['cargo_id']) ? $_POST['cargo_id'] : null;

                // Lógica de Cargo Dinâmico (Edição)
                if ($cargoId === 'new' && !empty($_POST['novo_cargo_nome'])) {
                    $novoCargo = sanitize_input($_POST['novo_cargo_nome']);
                    $stmtCheck = $conn->prepare("SELECT id FROM cargos WHERE setor_id = ? AND nome = ?");
                    $stmtCheck->execute([$_POST['setor_id'], $novoCargo]);
                    $existing = $stmtCheck->fetchColumn();
                    
                    if ($existing) {
                        $cargoId = $existing;
                    } else {
                        $stmtNewCargo = $conn->prepare("INSERT INTO cargos (setor_id, nome, nivel_hierarquia) VALUES (?, ?, 1)");
                        $stmtNewCargo->execute([$_POST['setor_id'], $novoCargo]);
                        $cargoId = $conn->lastInsertId();
                    }
                }

                $stmtLink = $conn->prepare("INSERT INTO usuario_setor (user_id, setor_id, cargo_id) VALUES (?, ?, ?)");
                $stmtLink->execute([$id, $_POST['setor_id'], $cargoId]);
            }

            $message = 'Usuário atualizado com sucesso!';
            $message_type = 'success';
        }

        // Deletar Usuário
        if ($action === 'delete') {
            // Impede que o usuário se auto-delete
            if ($_POST['id'] != $_SESSION['user_id']) {
                $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ? AND empresa_id = ?");
                $stmt->execute([$_POST['id'], $empresa_id]);
                $message = 'Usuário excluído com sucesso!';
                $message_type = 'success';
            } else {
                $message = 'Você não pode excluir sua própria conta!';
            }
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Código de erro para violação de integridade (chave duplicada)
            $message = 'Erro: O e-mail já está em uso por outro usuário.';
        } else {
            $message = 'Ocorreu um erro no banco de dados. Tente novamente.';
            error_log("Erro em usuarios.php: " . $e->getMessage());
        }
    } catch (Exception $e) {
        $message = 'Ocorreu um erro inesperado. Tente novamente.';
        error_log("Erro inesperado em usuarios.php: " . $e->getMessage());
    }

    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $message_type;
    header("Location: usuarios.php");
    exit;
}

// --- RENDERIZAÇÃO DA PÁGINA ---
include_once '../includes/cabecalho.php';

// --- LÓGICA DE VISUALIZAÇÃO (GET) ---
$search_term = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql_where = " WHERE empresa_id = ?";
$params_where = [$_SESSION['empresa_id']];
if (!empty($search_term)) {
    $sql_where .= " AND (username LIKE ? OR email LIKE ?)";
    $params_where = array_merge($params_where, ['%' . $search_term . '%', '%' . $search_term . '%']);
}

$total_stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios" . $sql_where);
$total_stmt->execute($params_where);
$total_results = $total_stmt->fetchColumn();
$total_pages = ceil($total_results / $limit);

$users_stmt = $conn->prepare("SELECT id, username, email, user_type FROM usuarios" . $sql_where . " ORDER BY username ASC LIMIT ? OFFSET ?");
$i = 1;
foreach ($params_where as $param) {
    $users_stmt->bindValue($i++, $param);
}
$users_stmt->bindValue($i++, $limit, PDO::PARAM_INT);
$users_stmt->bindValue($i, $offset, PDO::PARAM_INT);
$users_stmt->execute();
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
<?php endif; ?>
<h1 class="mb-4">Gerenciamento de Usuários</h1>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-3">
        <form action="usuarios.php" method="GET" class="d-flex flex-wrap gap-2">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nome ou email..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-plus me-2"></i> Adicionar Usuário</button>
    </div>
    <div class="card-body">
        <div class="table-responsive table-responsive-card">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>Nome</th><th>Email</th><th>Tipo</th><th>Ações</th></tr></thead>
                <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td data-label="Nome"><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td class="text-muted" data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td data-label="Tipo"><span class="badge bg-<?php echo $user['user_type'] === 'admin' ? 'primary' : 'secondary'; ?>"><?php echo ucfirst($user['user_type']); ?></span></td>
                                <td class="no-label" data-label="Ações">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn" data-id="<?php echo $user['id']; ?>" data-bs-toggle="modal" data-bs-target="#editUserModal"><i class="fas fa-pencil-alt"></i></button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): // Não permitir auto-exclusão ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['username']); ?>" data-bs-toggle="modal" data-bs-target="#deleteUserModal"><i class="fas fa-trash-alt"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <nav><ul class="pagination justify-content-center mb-0">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
        </ul></nav>
    </div>
</div>

<!-- Modal Adicionar Usuário -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="usuarios.php" method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-header"><h5 class="modal-title">Adicionar Novo Usuário</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nome de Usuário*</label><input type="text" name="username" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Email*</label><input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Senha*</label><input type="password" name="password" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Tipo de Usuário*</label><select name="user_type" class="form-select" required><option value="employee" selected>Funcionário</option><option value="admin">Admin</option></select></div>
          
          <div class="row">
              <div class="col-md-6 mb-3">
                  <label class="form-label">Setor</label>
                  <select name="setor_id" class="form-select setor-select" id="addSetorSelect">
                      <option value="">Selecione...</option>
                      <?php foreach($setoresDisponiveis as $s): ?>
                          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nome']) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <div class="col-md-6 mb-3">
                  <label class="form-label">Cargo</label>
                  <select name="cargo_id" class="form-select cargo-select" id="addCargoSelect" disabled>
                      <option value="">Selecione o setor primeiro</option>
                  </select>
                  <input type="text" name="novo_cargo_nome" class="form-control mt-2 new-cargo-input" placeholder="Nome do novo cargo..." style="display:none;">
              </div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Salvar</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar Usuário -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="usuarios.php" method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="editUserId">
        <div class="modal-header"><h5 class="modal-title">Editar Usuário</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nome de Usuário*</label><input type="text" name="username" id="editUsername" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Email*</label><input type="email" name="email" id="editEmail" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Nova Senha</label><input type="password" name="password" class="form-control" placeholder="Deixe em branco para não alterar"></div>
          <div class="mb-3"><label class="form-label">Tipo de Usuário*</label><select name="user_type" id="editUserType" class="form-select" required><option value="employee">Funcionário</option><option value="admin">Admin</option></select></div>
          
          <div class="row">
              <div class="col-md-6 mb-3">
                  <label class="form-label">Setor</label>
                  <select name="setor_id" class="form-select setor-select" id="editSetorSelect">
                      <option value="">Selecione...</option>
                      <?php foreach($setoresDisponiveis as $s): ?>
                          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nome']) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <div class="col-md-6 mb-3">
                  <label class="form-label">Cargo</label>
                  <select name="cargo_id" class="form-select cargo-select" id="editCargoSelect">
                      <option value="">Selecione...</option>
                  </select>
                  <input type="text" name="novo_cargo_nome" class="form-control mt-2 new-cargo-input" placeholder="Nome do novo cargo..." style="display:none;">
              </div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Salvar Alterações</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Deletar Usuário -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="usuarios.php" method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteUserId">
        <div class="modal-header"><h5 class="modal-title">Confirmar Exclusão</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><p>Você tem certeza que deseja excluir o usuário <strong id="deleteUsername"></strong>? Esta ação não pode ser desfeita.</p></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger">Excluir</button></div>
      </form>
    </div>
  </div>
</div>

<?php include_once '../includes/rodape.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- STACKING CONTEXT FIX ---
    // Move modals to body to ensure they sit above the backdrop
    const editModalEl = document.getElementById('editUserModal');
    const deleteModalEl = document.getElementById('deleteUserModal');
    const addUserModalEl = document.getElementById('addUserModal');
    
    if(editModalEl) document.body.appendChild(editModalEl);
    if(deleteModalEl) document.body.appendChild(deleteModalEl);
    if(addUserModalEl) document.body.appendChild(addUserModalEl);

    // --- EDIT MODAL LOGIC ---
    try {
        const editModal = document.getElementById('editUserModal');
        if(editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-id');
                
                // Reset fields
                const setorSelect = editModal.querySelector('#editSetorSelect');
                const cargoSelect = editModal.querySelector('#editCargoSelect');
                if(setorSelect) setorSelect.value = "";
                if(cargoSelect) cargoSelect.innerHTML = '<option value="">Selecione...</option>';
                
                fetch(`../api/get_usuario.php?id=${userId}`)
                    .then(async response => {
                        const contentType = response.headers.get("content-type");
                        if (!response.ok) {
                            const text = await response.text();
                            throw new Error(`Erro ${response.status}: ${text.substring(0, 50)}...`);
                        }
                        if (!contentType || !contentType.includes("application/json")) {
                            const text = await response.text();
                            throw new Error("Resposta inválida (não é JSON): " + text.substring(0, 100));
                        }
                        return response.json();
                    })
                    .then(data => {
                        if(data.error) { alert(data.error); return; }
                        
                        const idInput = editModal.querySelector('#editUserId');
                        const userInput = editModal.querySelector('#editUsername');
                        const emailInput = editModal.querySelector('#editEmail');
                        const typeInput = editModal.querySelector('#editUserType');
                        
                        if(idInput) idInput.value = data.id || '';
                        if(userInput) userInput.value = data.username || '';
                        if(emailInput) emailInput.value = data.email || '';
                        if(typeInput) typeInput.value = data.user_type || 'employee';
                        
                        if (data.setor_id && setorSelect) {
                            setorSelect.value = data.setor_id;
                            if(cargoSelect) carregarCargos(data.setor_id, cargoSelect, data.cargo_id);
                        } else {
                            if(setorSelect) setorSelect.value = "";
                            if(cargoSelect) cargoSelect.innerHTML = '<option value="">Selecione...</option>';
                        }
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        alert('Erro técnico: ' + err.message);
                    });
            });
        }
    } catch(e) { console.error("Erro no Edit Modal:", e); }

    // --- CARGO LOGIC ---
    function carregarCargos(setorId, selectElement, preSelectedId = null) {
        if(!selectElement) return;
        selectElement.disabled = true;
        selectElement.innerHTML = '<option>Carregando...</option>';
        
        if (!setorId) {
            selectElement.innerHTML = '<option value="">Selecione o setor primeiro</option>';
            return;
        }

        fetch(`../api/get_cargos_setor.php?setor_id=${setorId}`)
            .then(res => res.json())
            .then(cargos => {
                selectElement.innerHTML = '<option value="">Selecione um cargo (opcional)</option>';
                cargos.forEach(c => {
                    const selected = (preSelectedId && c.id == preSelectedId) ? 'selected' : '';
                    selectElement.innerHTML += `<option value="${c.id}" ${selected}>${c.nome}</option>`;
                });
                selectElement.innerHTML += '<option value="new" class="fw-bold text-primary">+ Criar Novo Cargo</option>';
                selectElement.disabled = false;
            })
            .catch(() => {
                selectElement.innerHTML = '<option>Erro ao carregar</option>';
                selectElement.disabled = false;
            });
    }

    try {
        document.querySelectorAll('.setor-select').forEach(select => {
            select.addEventListener('change', function() {
                const row = this.closest('.row');
                const cargoSelect = row.querySelector('.cargo-select');
                const newCargoInput = row.querySelector('.new-cargo-input');
                if(newCargoInput) {
                    newCargoInput.style.display = 'none';
                    newCargoInput.value = '';
                }
                carregarCargos(this.value, cargoSelect);
            });
        });

        document.querySelectorAll('.cargo-select').forEach(select => {
            select.addEventListener('change', function() {
                const row = this.closest('.row');
                const newCargoInput = row.querySelector('.new-cargo-input');
                if(newCargoInput) {
                    if (this.value === 'new') {
                        newCargoInput.style.display = 'block';
                        newCargoInput.required = true;
                        newCargoInput.focus();
                    } else {
                        newCargoInput.style.display = 'none';
                        newCargoInput.required = false;
                    }
                }
            });
        });
    } catch(e) { console.error("Erro nos Listeners de Cargo:", e); }

    // --- DELETE MODAL ---
    try {
        const deleteModal = document.getElementById('deleteUserModal');
        if(deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const idInput = deleteModal.querySelector('#deleteUserId');
                const nameSpan = deleteModal.querySelector('#deleteUsername');
                if(idInput) idInput.value = button.getAttribute('data-id');
                if(nameSpan) nameSpan.textContent = button.getAttribute('data-name');
            });
        }
    } catch(e) { console.error("Erro no Delete Modal:", e); }
});
</script>