<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once '../includes/funcoes.php';

use App\ProdutoRepository;

$empresa_id = $_SESSION['empresa_id'];
$produtoRepository = new ProdutoRepository($empresa_id);

// --- LÓGICA DE MANIPULAÇÃO (POST REQUESTS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $produtoRepository->add($_POST);
            $_SESSION['message'] = 'Produto adicionado com sucesso!';
            $_SESSION['message_type'] = 'success';
        }

        if ($action === 'edit') {
            $produtoRepository->update($_POST);
            $_SESSION['message'] = 'Produto atualizado com sucesso!';
            $_SESSION['message_type'] = 'success';
        }

        if ($action === 'delete') {
            $produtoRepository->delete($_POST['id']);
            $_SESSION['message'] = 'Produto excluído com sucesso!';
            $_SESSION['message_type'] = 'success';
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) { // Erro de entrada duplicada (para o SKU)
            $_SESSION['message'] = 'Erro: O SKU informado já existe para outro produto.';
            $_SESSION['message_type'] = 'danger';
        } elseif ($e->errorInfo[1] == 1451) { // Erro de chave estrangeira (produto em uso)
            $_SESSION['message'] = 'Erro: Não é possível excluir este produto pois ele possui movimentações (vendas ou compras) associadas.';
            $_SESSION['message_type'] = 'danger';
        } else {
            $_SESSION['message'] = 'Ocorreu um erro no banco de dados: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    }

    header("Location: produtos.php");
    exit;
}

include_once '../includes/cabecalho.php';

// --- LÓGICA DE VISUALIZAÇÃO ---
$search_term = $_GET['search'] ?? '';
$selected_category = $_GET['categoria_id'] ?? 'all';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$filter_low_stock = ($_GET['filter'] ?? '') === 'low_stock';

$total_results = $produtoRepository->countAll($search_term, $selected_category, $filter_low_stock);
$total_pages = ceil($total_results / $limit);

$products = $produtoRepository->getAll($search_term, $selected_category, $limit, $offset, $filter_low_stock);
$categories = $produtoRepository->getCategories();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-1">Catálogo de Produtos</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="../modules/estoque/views/index.php">Estoque</a></li>
                    <li class="breadcrumb-item active">Produtos</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="categorias.php" class="btn btn-light border mx-2"><i class="fas fa-tags me-2"></i>Categorias</a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fas fa-plus me-2"></i>Novo Produto
            </button>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])) : ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <form action="produtos.php" method="GET" class="d-flex flex-wrap gap-2">
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Buscar produto ou SKU..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                
                <select name="categoria_id" class="form-select" style="max-width: 200px;" onchange="this.form.submit()">
                    <option value="all">Todas as Categorias</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo ($selected_category == $category['id']) ? 'selected' : ''; ?> >
                            <?php echo htmlspecialchars($category['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="form-check form-switch d-flex align-items-center ms-2">
                    <input class="form-check-input me-2" type="checkbox" name="filter" value="low_stock" id="lowStockFilter" <?php echo $filter_low_stock ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <label class="form-check-label small fw-bold text-secondary" for="lowStockFilter">Baixo Estoque</label>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive table-responsive-card">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Produto</th>
                            <th>SKU</th>
                            <th>Categoria</th>
                            <th>Preço Venda</th>
                            <th>Estoque</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($products)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">Nenhum produto encontrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td class="ps-4" data-label="Produto">
                                        <div class="fw-bold text-primary"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <?php if($product['quantity'] <= $product['minimum_stock']): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 0.65rem;">BAIXO ESTOQUE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="SKU"><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($product['sku']); ?></span></td>
                                    <td data-label="Categoria">
                                        <?php if(isset($product['categoria_nome'])): ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary"><?php echo htmlspecialchars($product['categoria_nome']); ?></span>
                                        <?php else: ?>
                                        <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-success" data-label="Preço Venda">R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></td>
                                    <td data-label="Estoque">
                                        <span class="<?php echo $product['quantity'] <= $product['minimum_stock'] ? 'text-danger fw-bold' : ''; ?>">
                                            <?php echo $product['quantity']; ?>
                                        </span> 
                                        <small class="text-muted"><?php echo $product['unidade_medida']; ?></small>
                                    </td>
                                    <td class="text-end pe-4 no-label" data-label="Ações">
                                        <button type="button" class="btn btn-sm btn-light border edit-btn" data-id="<?php echo $product['id']; ?>" data-bs-toggle="modal" data-bs-target="#editProductModal"><i class="fas fa-pencil-alt"></i></button>
                                        <button type="button" class="btn btn-sm btn-light border text-danger delete-btn" data-id="<?php echo $product['id']; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-bs-toggle="modal" data-bs-target="#deleteProductModal"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if($total_pages > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-end">
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search_term; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>



<!-- Modal Adicionar Produto -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form action="produtos.php" method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-header">
          <h5 class="modal-title" id="addProductModalLabel">Adicionar Novo Produto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8 mb-3"><label class="form-label">Nome*</label><input type="text" name="name" class="form-control" required></div>
            <div class="col-md-4 mb-3"><label class="form-label">SKU / Cód. de Barras</label><input type="text" name="sku" class="form-control"></div>
          </div>
          <div class="mb-3"><label class="form-label">Descrição</label><textarea name="description" class="form-control" rows="2"></textarea></div>
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Valor de Custo*</label><input type="number" step="0.01" name="cost_price" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Valor de Venda*</label><input type="number" step="0.01" name="price" class="form-control" required></div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Unidade de Medida*</label>
                <select name="unidade_medida" class="form-select" required>
                    <option value="un">Unidade (un)</option><option value="kg">Quilograma (kg)</option><option value="g">Grama (g)</option><option value="l">Litro (l)</option><option value="ml">Mililitro (ml)</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Categoria</label>
                <select name="categoria_id" class="form-select">
                    <option value="">Selecione uma categoria</option>
                    <?php foreach ($categories as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option><?php endforeach; ?>
                </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Quantidade Inicial*</label><input type="number" name="quantity" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Estoque Mínimo*</label><input type="number" name="minimum_stock" class="form-control" required></div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Lote</label><input type="text" name="lote" class="form-control"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Validade</label><input type="date" name="validade" class="form-control"></div>
          </div>
          <div class="mb-3"><label class="form-label">Observações</label><textarea name="observacoes" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar Produto</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar Produto -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form action="produtos.php" method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="editProductId">
        <div class="modal-header">
          <h5 class="modal-title" id="editProductModalLabel">Editar Produto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8 mb-3"><label class="form-label">Nome*</label><input type="text" name="name" id="editProductName" class="form-control" required></div>
            <div class="col-md-4 mb-3"><label class="form-label">SKU / Cód. de Barras</label><input type="text" name="sku" id="editProductSku" class="form-control"></div>
          </div>
          <div class="mb-3"><label class="form-label">Descrição</label><textarea name="description" id="editProductDescription" class="form-control" rows="2"></textarea></div>
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Valor de Custo*</label><input type="number" step="0.01" name="cost_price" id="editProductCostPrice" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Valor de Venda*</label><input type="number" step="0.01" name="price" id="editProductPrice" class="form-control" required></div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Unidade de Medida*</label>
                <select name="unidade_medida" id="editProductUnidadeMedida" class="form-select" required>
                    <option value="un">Unidade (un)</option><option value="kg">Quilograma (kg)</option><option value="g">Grama (g)</option><option value="l">Litro (l)</option><option value="ml">Mililitro (ml)</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Categoria</label>
                <select name="categoria_id" id="editProductCategoriaId" class="form-select">
                    <option value="">Selecione uma categoria</option>
                    <?php foreach ($categories as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option><?php endforeach; ?>
                </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Quantidade*</label><input type="number" name="quantity" id="editProductQuantity" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Estoque Mínimo*</label><input type="number" name="minimum_stock" id="editProductMinimumStock" class="form-control" required></div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Lote</label><input type="text" name="lote" id="editProductLote" class="form-control"></div>
            <div class="col-md-6 mb-3"><label class="form-label">Validade</label><input type="date" name="validade" id="editProductValidade" class="form-control"></div>
          </div>
          <div class="mb-3"><label class="form-label">Observações</label><textarea name="observacoes" id="editProductObservacoes" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Deletar Produto -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="produtos.php" method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteProductId">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteProductModalLabel">Confirmar Exclusão</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja excluir o produto <strong id="deleteProductName"></strong>?</p>
          <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Excluir</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once '../includes/rodape.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editProductModal = document.getElementById('editProductModal');
    if(editProductModal) {
        editProductModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const productId = button.getAttribute('data-id');
            
            fetch(`../api/get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if(data.error) { alert(data.error); return; }
                    const form = editProductModal.querySelector('form');
                    form.id.value = data.id;
                    form.name.value = data.name;
                    form.sku.value = data.sku;
                    form.description.value = data.description;
                    form.cost_price.value = data.cost_price;
                    form.price.value = data.price;
                    form.unidade_medida.value = data.unidade_medida;
                    form.categoria_id.value = data.categoria_id;
                    form.quantity.value = data.quantity;
                    form.minimum_stock.value = data.minimum_stock;
                    form.lote.value = data.lote;
                    form.validade.value = data.validade;
                    form.observacoes.value = data.observacoes;
                });
        });
    }

    const deleteProductModal = document.getElementById('deleteProductModal');
    if(deleteProductModal) {
        deleteProductModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const productId = button.getAttribute('data-id');
            const productName = button.getAttribute('data-name');
            
            deleteProductModal.querySelector('#deleteProductId').value = productId;
            deleteProductModal.querySelector('#deleteProductName').textContent = productName;
        });
    }
});
</script>