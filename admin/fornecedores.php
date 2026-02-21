<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/funcoes.php';

$conn = connect_db();

// --- LÓGICA DE MANIPULAÇÃO (POST REQUESTS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $empresa_id = $_SESSION['empresa_id'];

    // Adicionar ou Editar Fornecedor
    if ($action === 'add' || $action === 'edit') {
        $name = $_POST['name'];
        $contact_person = $_POST['contact_person'] ?? null;
        $phone = $_POST['phone'] ?? null;
        $email = $_POST['email'] ?? null;
        $address = $_POST['address'] ?? null;

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO fornecedores (empresa_id, name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$empresa_id, $name, $contact_person, $phone, $email, $address]);
        } else {
            $id = $_POST['id'];
            $stmt = $conn->prepare("UPDATE fornecedores SET name=?, contact_person=?, phone=?, email=?, address=? WHERE id=? AND empresa_id = ?");
            $stmt->execute([$name, $contact_person, $phone, $email, $address, $id, $empresa_id]);
        }
    }

    // Deletar Fornecedor
    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM fornecedores WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$_POST['id'], $empresa_id]);
    }

    header("Location: fornecedores.php");
    exit;
}

// --- A PARTIR DAQUI, COMEÇA A RENDERIZAÇÃO DA PÁGINA ---
include_once '../includes/cabecalho.php';

// --- LÓGICA DE VISUALIZAÇÃO (GET REQUESTS) ---

// Busca
$search_term = $_GET['search'] ?? '';

// Paginação
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Montagem da Query
$sql_where = " WHERE empresa_id = ?";
$params_where = [$_SESSION['empresa_id']];
if (!empty($search_term)) {
    $sql_where .= " AND (name LIKE ? OR contact_person LIKE ? OR email LIKE ?)";
    $params_where[] = '%' . $search_term . '%';
    $params_where[] = '%' . $search_term . '%';
    $params_where[] = '%' . $search_term . '%';
}

// Total de registros para paginação
$total_stmt = $conn->prepare("SELECT COUNT(*) FROM fornecedores" . $sql_where);
$total_stmt->execute($params_where);
$total_results = $total_stmt->fetchColumn();
$total_pages = ceil($total_results / $limit);

// Busca dos fornecedores com paginação
$suppliers_stmt = $conn->prepare("SELECT * FROM fornecedores" . $sql_where . " ORDER BY name ASC LIMIT ? OFFSET ?");
$i = 1;
foreach ($params_where as $param) {
    $suppliers_stmt->bindValue($i++, $param);
}
$suppliers_stmt->bindValue($i++, $limit, PDO::PARAM_INT);
$suppliers_stmt->bindValue($i, $offset, PDO::PARAM_INT);
$suppliers_stmt->execute();
$suppliers = $suppliers_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h1 class="mb-4">Gerenciamento de Fornecedores</h1>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-3">
        <form action="fornecedores.php" method="GET" class="d-flex flex-wrap gap-2">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nome, contato, email..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="fas fa-plus me-2"></i> Adicionar Fornecedor
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Contato</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($suppliers)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Nenhum fornecedor encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($supplier['name']); ?></strong></td>
                                <td class="text-muted"><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars($supplier['phone']); ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars($supplier['email']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn" data-id="<?php echo $supplier['id']; ?>" data-bs-toggle="modal" data-bs-target="#editSupplierModal">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?php echo $supplier['id']; ?>" data-name="<?php echo htmlspecialchars($supplier['name']); ?>" data-bs-toggle="modal" data-bs-target="#deleteSupplierModal">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination justify-content-center mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>

<!-- Modal Adicionar Fornecedor -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="fornecedores.php" method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-header"><h5 class="modal-title">Adicionar Novo Fornecedor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nome*</label><input type="text" name="name" class="form-control" required></div>
          <div class="row"><div class="col-md-6 mb-3"><label>Pessoa de Contato</label><input type="text" name="contact_person" class="form-control"></div><div class="col-md-6 mb-3"><label>Telefone</label><input type="text" name="phone" class="form-control"></div></div>
          <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
          <div class="mb-3"><label>Endereço</label><textarea name="address" class="form-control" rows="3"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Salvar</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar Fornecedor -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="fornecedores.php" method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="editSupplierId">
        <div class="modal-header"><h5 class="modal-title">Editar Fornecedor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nome*</label><input type="text" name="name" id="editSupplierName" class="form-control" required></div>
          <div class="row"><div class="col-md-6 mb-3"><label>Pessoa de Contato</label><input type="text" name="contact_person" id="editSupplierContact" class="form-control"></div><div class="col-md-6 mb-3"><label>Telefone</label><input type="text" name="phone" id="editSupplierPhone" class="form-control"></div></div>
          <div class="mb-3"><label>Email</label><input type="email" name="email" id="editSupplierEmail" class="form-control"></div>
          <div class="mb-3"><label>Endereço</label><textarea name="address" id="editSupplierAddress" class="form-control" rows="3"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Salvar Alterações</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Deletar Fornecedor -->
<div class="modal fade" id="deleteSupplierModal" tabindex="-1" aria-labelledby="deleteSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="fornecedores.php" method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteSupplierId">
        <div class="modal-header"><h5 class="modal-title">Confirmar Exclusão</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><p>Você tem certeza que deseja excluir o fornecedor <strong id="deleteSupplierName"></strong>?</p></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger">Excluir</button></div>
      </form>
    </div>
  </div>
</div>

<?php include_once '../includes/rodape.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Script para popular o modal de edição
    const editModal = document.getElementById('editSupplierModal');
    if(editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const supplierId = button.getAttribute('data-id');
            fetch(`../api/get_fornecedor.php?id=${supplierId}`)
                .then(response => response.json())
                .then(data => {
                    if(data.error) { alert(data.error); return; }
                    editModal.querySelector('#editSupplierId').value = data.id;
                    editModal.querySelector('#editSupplierName').value = data.name;
                    editModal.querySelector('#editSupplierContact').value = data.contact_person;
                    editModal.querySelector('#editSupplierPhone').value = data.phone;
                    editModal.querySelector('#editSupplierEmail').value = data.email;
                    editModal.querySelector('#editSupplierAddress').value = data.address;
                });
        });
    }

    // Script para popular o modal de deleção
    const deleteModal = document.getElementById('deleteSupplierModal');
    if(deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            deleteModal.querySelector('#deleteSupplierId').value = button.getAttribute('data-id');
            deleteModal.querySelector('#deleteSupplierName').textContent = button.getAttribute('data-name');
        });
    }
});
</script>
