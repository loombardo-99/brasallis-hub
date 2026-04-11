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
    <div class="row align-items-center mb-5 pb-4 border-bottom border-light">
        <div class="col-lg-8">
            <div class="metric-label mb-2"><i class="fas fa-list-ul me-1 text-primary"></i> Brasallis Catalog</div>
            <h1 class="greeting">Produtos</h1>
            <p class="text-muted mb-0 mt-2" style="font-weight: 500;">Gerenciamento de SKUs e precificação.</p>
        </div>
        <div class="col-lg-4 text-end">
            <a href="categorias.php" class="btn btn-white shadow-sm rounded-pill px-4 py-2 fw-bold me-2" style="font-size: 0.8rem; background: white;">
                <i class="fas fa-tags me-2 opacity-50"></i> Categorias
            </a>
            <button type="button" class="btn btn-dark shadow-sm rounded-pill px-4 py-2 fw-bold" style="font-size: 0.8rem;" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fas fa-plus me-2 opacity-50"></i> Novo Produto
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

    <div class="exec-card p-0 mb-4" style="background: rgba(255, 255, 255, 0.4); border: 1px solid rgba(0,0,0,0.05);">
        <div class="px-4 py-3">
            <form action="produtos.php" method="GET" class="d-flex flex-wrap gap-3 align-items-center">
                <div class="input-group" style="max-width: 350px; background: rgba(0,0,0,0.03); border-radius: 12px; padding: 2px 10px;">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted opacity-50"></i></span>
                    <input type="text" name="search" class="form-control bg-transparent border-0 ps-0 shadow-none" placeholder="Buscar SKU ou nome..." value="<?php echo htmlspecialchars($search_term); ?>" style="font-size: 0.85rem; font-weight: 500;">
                </div>
                
                <select name="categoria_id" class="form-select border-0 shadow-sm rounded-4" style="max-width: 220px; font-size: 0.85rem; font-weight: 500; background-color: #fff;" onchange="this.form.submit()">
                    <option value="all">Todas as Categorias</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo ($selected_category == $category['id']) ? 'selected' : ''; ?> >
                            <?php echo htmlspecialchars($category['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="form-check form-switch d-flex align-items-center ms-auto">
                    <input class="form-check-input me-3" type="checkbox" name="filter" value="low_stock" id="lowStockFilter" <?php echo $filter_low_stock ? 'checked' : ''; ?> onchange="this.form.submit()" style="width: 40px; height: 20px; cursor: pointer;">
                    <label class="metric-label fw-bold" for="lowStockFilter" style="cursor: pointer; opacity: 0.7;">Alerta de Estoque</label>
                </div>
            </form>
        </div>
    </div>
    <div class="apple-table-container">
        <div class="table-responsive">
            <table class="table apple-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-5">Item</th>
                        <th>SKU / Ref</th>
                        <th>Categoria</th>
                        <th>Preço Unitário</th>
                        <th>Saldo Disponível</th>
                        <th class="text-end pe-5">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($products)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">Nenhum item encontrado no catálogo.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="ps-5">
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <?php if($product['quantity'] <= $product['minimum_stock']): ?>
                                        <div class="text-danger small fw-bold mt-1" style="font-size: 0.65rem; letter-spacing: 0.3px;"><i class="fas fa-exclamation-triangle me-1"></i>REPOSIÇÃO NECESSÁRIA</div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-muted border-0 fw-bold" style="font-size: 0.72rem; padding: 6px 10px;"><?php echo htmlspecialchars($product['sku']); ?></span></td>
                                <td>
                                    <?php if(isset($product['categoria_nome'])): ?>
                                    <span class="badge bg-primary bg-opacity-5 text-primary rounded-pill border-0 px-3"><?php echo htmlspecialchars($product['categoria_nome']); ?></span>
                                    <?php else: ?>
                                    <span class="text-muted opacity-50">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold">R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="<?php echo $product['quantity'] <= $product['minimum_stock'] ? 'text-danger fw-bold' : 'text-dark'; ?>">
                                            <?php echo $product['quantity']; ?>
                                        </span> 
                                        <small class="text-muted fw-bold" style="font-size: 0.7rem;"><?php echo strtoupper($product['unidade_medida']); ?></small>
                                    </div>
                                </td>
                                <td class="text-end pe-5">
                                    <button type="button" class="btn btn-icon-action edit-btn me-1" data-id="<?php echo $product['id']; ?>" data-bs-toggle="modal" data-bs-target="#editProductModal"><i class="fas fa-pencil-alt"></i></button>
                                    <button type="button" class="btn btn-icon-action text-danger delete-btn" data-id="<?php echo $product['id']; ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-bs-toggle="modal" data-bs-target="#deleteProductModal"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .btn-icon-action {
            width: 32px; height: 32px; border-radius: 10px; border: none; background: rgba(0,0,0,0.03);
            color: #86868b; display: inline-flex; align-items: center; justify-content: center;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .btn-icon-action:hover { background: rgba(0,0,0,0.06); color: #1d1d1f; transform: translateY(-2px); }
        .btn-icon-action:active { transform: scale(0.9); }
    </style>

    <?php if($total_pages > 1): ?>
    <div class="mt-4 d-flex justify-content-center">
        <nav>
            <ul class="pagination pagination-apple">
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
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden;">
      <form action="produtos.php" method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-header border-0 px-4 pt-4 pb-2">
          <h5 class="fw-bold text-dark mb-0">Novo Produto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-4">
          <p class="text-muted small mb-4">Insira as informações técnicas para o catálogo.</p>
          <div class="row g-3">
            <div class="col-md-8"><label class="metric-label d-block mb-2">Nome do Item</label><input type="text" name="name" class="form-control rounded-4 border-light bg-light bg-opacity-50" required placeholder="Ex: Marmore Carrara"></div>
            <div class="col-md-4"><label class="metric-label d-block mb-2">SKU / Ref</label><input type="text" name="sku" class="form-control rounded-4 border-light bg-light bg-opacity-50" placeholder="Código"></div>
            
            <div class="col-12"><label class="metric-label d-block mb-2">Descrição Curta</label><textarea name="description" class="form-control rounded-4 border-light bg-light bg-opacity-50" rows="2"></textarea></div>
            
            <div class="col-md-6"><label class="metric-label d-block mb-2">Preço de Custo (R$)</label><input type="number" step="0.01" name="cost_price" class="form-control rounded-4 border-light" required></div>
            <div class="col-md-6"><label class="metric-label d-block mb-2">Preço de Venda (R$)</label><input type="number" step="0.01" name="price" class="form-control rounded-4 border-light" required></div>
            
            <div class="col-md-6">
                <label class="metric-label d-block mb-2">Unidade</label>
                <select name="unidade_medida" class="form-select rounded-4 border-light" required>
                    <option value="un">Unidade (un)</option><option value="kg">Quilograma (kg)</option><option value="g">Grama (g)</option><option value="l">Litro (l)</option><option value="ml">Mililitro (ml)</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="metric-label d-block mb-2">Categoria</label>
                <select name="categoria_id" class="form-select rounded-4 border-light">
                    <option value="">Selecione categoria</option>
                    <?php foreach ($categories as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option><?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-6"><label class="metric-label d-block mb-2">Estoque Inicial</label><input type="number" name="quantity" class="form-control rounded-4 border-light" required></div>
            <div class="col-md-6"><label class="metric-label d-block mb-2">Estoque Segurança</label><input type="number" name="minimum_stock" class="form-control rounded-4 border-light" required></div>
            
            <!-- SEÇÃO AVANÇADA -->
            <div class="col-12 mt-4">
                <button type="button" class="btn btn-link text-decoration-none p-0 metric-label fw-bold text-primary mb-3" data-bs-toggle="collapse" data-bs-target="#advancedAddFields" aria-expanded="false">
                    <i class="fas fa-chevron-down me-1"></i> Informações Adicionais
                </button>
                <div class="collapse" id="advancedAddFields">
                    <div class="row g-3 p-3 bg-light rounded-4 border-light">
                        <div class="col-md-6">
                            <label class="metric-label d-block mb-2">Lote</label>
                            <input type="text" name="lote" class="form-control rounded-4 border-0">
                        </div>
                        <div class="col-md-6">
                            <label class="metric-label d-block mb-2">Data de Validade</label>
                            <input type="date" name="validade" class="form-control rounded-4 border-0">
                        </div>
                        <div class="col-12">
                            <label class="metric-label d-block mb-2">Observações Internas</label>
                            <textarea name="observacoes" class="form-control rounded-4 border-0" rows="2" placeholder="Notas sobre o fornecedor, armazenamento, etc."></textarea>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 px-4 pb-4">
          <button type="button" class="btn btn-link text-muted text-decoration-none fw-bold small" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold">Criar Item</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar Produto -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden;">
      <form action="produtos.php" method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="editProductId">
        <div class="modal-header border-0 px-4 pt-4 pb-2">
          <h5 class="fw-bold text-dark mb-0">Detalhes do Produto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-4">
          <div class="row g-3">
            <div class="col-md-8"><label class="metric-label d-block mb-2">Nome do Item</label><input type="text" name="name" id="editProductName" class="form-control rounded-4 border-light" required></div>
            <div class="col-md-4"><label class="metric-label d-block mb-2">SKU / Ref</label><input type="text" name="sku" id="editProductSku" class="form-control rounded-4 border-light"></div>
            
            <div class="col-12"><label class="metric-label d-block mb-2">Descrição</label><textarea name="description" id="editProductDescription" class="form-control rounded-4 border-light" rows="2"></textarea></div>
            
            <div class="col-md-6"><label class="metric-label d-block mb-2">Preço de Custo (R$)</label><input type="number" step="0.01" name="cost_price" id="editProductCostPrice" class="form-control rounded-4 border-light" required></div>
            <div class="col-md-6"><label class="metric-label d-block mb-2">Preço de Venda (R$)</label><input type="number" step="0.01" name="price" id="editProductPrice" class="form-control rounded-4 border-light" required></div>
            
            <div class="col-md-6">
                <label class="metric-label d-block mb-2">Unidade</label>
                <select name="unidade_medida" id="editProductUnidadeMedida" class="form-select rounded-4 border-light" required>
                    <option value="un">Unidade (un)</option><option value="kg">Quilograma (kg)</option><option value="g">Grama (g)</option><option value="l">Litro (l)</option><option value="ml">Mililitro (ml)</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="metric-label d-block mb-2">Categoria</label>
                <select name="categoria_id" id="editProductCategoriaId" class="form-select rounded-4 border-light">
                    <option value="">Selecione categoria</option>
                    <?php foreach ($categories as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option><?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-6"><label class="metric-label d-block mb-2">Quantidade Atual</label><input type="number" name="quantity" id="editProductQuantity" class="form-control rounded-4 border-light" required></div>
            <div class="col-md-6"><label class="metric-label d-block mb-2">Estoque Segurança</label><input type="number" name="minimum_stock" id="editProductMinimumStock" class="form-control rounded-4 border-light" required></div>

            <!-- SEÇÃO AVANÇADA EDIT -->
            <div class="col-12 mt-4">
                <button type="button" class="btn btn-link text-decoration-none p-0 metric-label fw-bold text-primary mb-3" data-bs-toggle="collapse" data-bs-target="#advancedEditFields" aria-expanded="false">
                    <i class="fas fa-chevron-down me-1"></i> Informações Adicionais
                </button>
                <div class="collapse" id="advancedEditFields">
                    <div class="row g-3 p-3 bg-light rounded-4 border-light">
                        <div class="col-md-6">
                            <label class="metric-label d-block mb-2">Lote</label>
                            <input type="text" name="lote" id="editProductLote" class="form-control rounded-4 border-0">
                        </div>
                        <div class="col-md-6">
                            <label class="metric-label d-block mb-2">Data de Validade</label>
                            <input type="date" name="validade" id="editProductValidade" class="form-control rounded-4 border-0">
                        </div>
                        <div class="col-12">
                            <label class="metric-label d-block mb-2">Observações Internas</label>
                            <textarea name="observacoes" id="editProductObservacoes" class="form-control rounded-4 border-0" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 px-4 pb-4">
          <button type="button" class="btn btn-link text-muted text-decoration-none fw-bold small" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm">Salvar Alterações</button>
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
                    
                    // Preenchimento dos campos colapsáveis
                    if(form.lote) form.lote.value = data.lote || '';
                    if(form.validade) form.validade.value = data.validade || '';
                    if(form.observacoes) form.observacoes.value = data.observacoes || '';
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