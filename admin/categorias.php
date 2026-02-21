<?php
// A lógica de processamento de formulário DEVE vir antes de qualquer output HTML.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once '../includes/funcoes.php';

use App\CategoriaRepository;

$empresa_id = $_SESSION['empresa_id'];
$categoriaRepository = new CategoriaRepository($empresa_id);

// Apenas processa se for um POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Adicionar Categoria
    if (isset($_POST['add_categoria'])) {
        $nome = trim($_POST['nome']);
        if (!empty($nome)) {
            try {
                $categoriaRepository->add($nome);
                $_SESSION['message'] = 'Categoria adicionada com sucesso!';
                $_SESSION['message_type'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Erro ao adicionar categoria: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        } else {
            $_SESSION['message'] = 'O nome da categoria não pode ser vazio.';
            $_SESSION['message_type'] = 'warning';
        }
    }

    // Editar Categoria
    if (isset($_POST['edit_categoria'])) {
        $id = $_POST['edit_id'];
        $nome = trim($_POST['edit_nome']);
        if (!empty($nome) && !empty($id)) {
            try {
                $categoriaRepository->update($id, $nome);
                $_SESSION['message'] = 'Categoria atualizada com sucesso!';
                $_SESSION['message_type'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Erro ao atualizar categoria: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        } else {
            $_SESSION['message'] = 'O nome da categoria não pode ser vazio.';
            $_SESSION['message_type'] = 'warning';
        }
    }

    // Excluir Categoria
    if (isset($_POST['delete_categoria'])) {
        $id = $_POST['delete_id'];
        try {
            $categoriaRepository->delete($id);
            $_SESSION['message'] = 'Categoria excluída com sucesso!';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Erro ao excluir categoria: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    }

    // Redireciona após o processamento para evitar reenvio
    header("Location: categorias.php");
    exit();
}

// --- A partir daqui, começa a renderização da página ---
include_once '../includes/cabecalho.php';

// --- LÓGICA DE VISUALIZAÇÃO ---
$search = $_GET['search'] ?? '';
$categorias = $categoriaRepository->getAll($search);

?>

<h1 class="mb-4">Gerenciar Categorias</h1>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Minhas Categorias</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="fas fa-plus me-2"></i>Adicionar Categoria</button>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nome..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Data de Criação</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Nenhuma categoria encontrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($categoria['nome']); ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($categoria['created_at'])); ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary edit-btn" data-id="<?php echo $categoria['id']; ?>" data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?php echo $categoria['id']; ?>" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Adicionar Categoria -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Adicionar Nova Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Categoria</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="add_categoria" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Categoria -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Editar Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="edit_id">
                    <div class="mb-3">
                        <label for="edit_nome" class="form-label">Nome da Categoria</label>
                        <input type="text" class="form-control" id="edit_nome" name="edit_nome" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="edit_categoria" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Excluir Categoria -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCategoryModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza de que deseja excluir esta categoria? Os produtos associados a ela não serão excluídos, mas ficarão sem categoria.</p>
                    <input type="hidden" id="delete_id" name="delete_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="delete_categoria" class="btn btn-danger">Excluir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../includes/rodape.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Script para popular o modal de edição
    const editCategoryModal = document.getElementById('editCategoryModal');
    editCategoryModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const categoryId = button.getAttribute('data-id');
        
        // Usaremos um endpoint da API para buscar os dados da categoria
        fetch(`../api/get_categoria.php?id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    const modalBody = editCategoryModal.querySelector('.modal-body');
                    modalBody.querySelector('#edit_id').value = data.id;
                    modalBody.querySelector('#edit_nome').value = data.nome;
                }
            })
            .catch(error => console.error('Error fetching category data:', error));
    });

    // Script para popular o modal de exclusão
    const deleteCategoryModal = document.getElementById('deleteCategoryModal');
    deleteCategoryModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const categoryId = button.getAttribute('data-id');
        const modalBody = deleteCategoryModal.querySelector('.modal-body');
        modalBody.querySelector('#delete_id').value = categoryId;
    });
});
</script>