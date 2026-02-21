<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/funcoes.php';

$conn = connect_db();

// --- LÓGICA DE MANIPULAÇÃO (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Lógica para Adicionar Compra
    if ($action === 'add') {
        if (empty($_POST['supplier_id'])) {
            $_SESSION['message'] = 'Erro: Você precisa selecionar um fornecedor.';
            $_SESSION['message_type'] = 'danger';
        } else {
            $conn->beginTransaction();
            try {
                $fiscal_note_path = null;
                if (isset($_FILES['fiscal_note']) && $_FILES['fiscal_note']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/';
                    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
                    $file_ext = strtolower(pathinfo($_FILES['fiscal_note']['name'], PATHINFO_EXTENSION));
                    if (in_array($file_ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                        $new_file_name = 'compra_' . uniqid() . '.' . $file_ext;
                        if (move_uploaded_file($_FILES['fiscal_note']['tmp_name'], $upload_dir . $new_file_name)) {
                            $fiscal_note_path = 'uploads/' . $new_file_name;
                        }
                    } else {
                        throw new Exception("Formato de arquivo inválido. Apenas PDF, JPG e PNG são permitidos.");
                    }
                }

                $stmt = $conn->prepare("INSERT INTO compras (empresa_id, supplier_id, purchase_date, user_id, fiscal_note_path, total_amount) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->execute([$_SESSION['empresa_id'], $_POST['supplier_id'], $_POST['purchase_date'], $_SESSION['user_id'], $fiscal_note_path]);
                $conn->commit();
                
                $_SESSION['message'] = 'Compra registrada com sucesso! Agora você pode enviar a nota para análise da IA.';
                $_SESSION['message_type'] = 'success';

            } catch (Exception $e) {
                $conn->rollBack();
                error_log("Erro ao criar compra: " . $e->getMessage());
                $_SESSION['message'] = 'Erro ao criar compra: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
    } 
    // Lógica para Deletar Compra
    elseif ($action === 'delete') {
        $purchase_id = $_POST['id'];
        $empresa_id = $_SESSION['empresa_id'];
        $conn->beginTransaction();
        try {
            $items_stmt = $conn->prepare("SELECT product_id, quantity FROM itens_compra WHERE purchase_id = ?");
            $items_stmt->execute([$purchase_id]);
            foreach ($items_stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
                $conn->prepare("UPDATE produtos SET quantity = quantity - ? WHERE id = ? AND empresa_id = ?")->execute([$item['quantity'], $item['product_id'], $empresa_id]);
            }
            $conn->prepare("DELETE FROM itens_compra WHERE purchase_id = ?")->execute([$purchase_id]);
            $conn->prepare("DELETE FROM compras WHERE id = ? AND empresa_id = ?")->execute([$purchase_id, $empresa_id]);
            $conn->commit();
            $_SESSION['message'] = 'Compra excluída com sucesso.';
            $_SESSION['message_type'] = 'info';
        } catch (PDOException $e) {
            $conn->rollBack();
            $_SESSION['message'] = 'Erro ao excluir a compra.';
            $_SESSION['message_type'] = 'danger';
        }
    }

    // Redirecionamento único para todas as ações POST
    header("Location: compras.php");
    exit;
}

include_once '../includes/cabecalho.php';
?>

<h1 class="mb-4">Gerenciamento de Compras</h1>

<?php
// Bloco de exibição de mensagens seguro, com sintaxe de chaves {}
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">' .
         htmlspecialchars($_SESSION['message']) . 
         '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' .
         '</div>';
    
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<?php
// --- LÓGICA DE VISUALIZAÇÃO (GET) ---
$search_term = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$filter_pending_review = ($_GET['filter'] ?? '') === 'pending_review';

$sql_where = " WHERE c.empresa_id = ?";
$params_where = [$_SESSION['empresa_id']];

if ($filter_pending_review) {
    $sql_where .= " AND dnf.status = 'pendente_confirmacao'";
}

if (!empty($search_term)) {
    $sql_where .= " AND (COALESCE(s.name, '') LIKE ? OR COALESCE(u.username, '') LIKE ? OR COALESCE(dnf.numero_nota, '') LIKE ?)";
    $params_where[] = '%' . $search_term . '%';
    $params_where[] = '%' . $search_term . '%';
    $params_where[] = '%' . $search_term . '%';
}

$base_query = "FROM compras c " . 
              "LEFT JOIN fornecedores s ON c.supplier_id = s.id " . 
              "LEFT JOIN usuarios u ON c.user_id = u.id " . 
              "LEFT JOIN dados_nota_fiscal dnf ON c.id = dnf.compra_id " . $sql_where;

$total_stmt = $conn->prepare("SELECT COUNT(c.id) " . $base_query);
$total_stmt->execute($params_where);
$total_results = $total_stmt->fetchColumn();
$total_pages = ceil($total_results / $limit);

$purchases_sql = "SELECT c.id, c.purchase_date, c.total_amount, c.fiscal_note_path, s.name as supplier_name, u.username as user_name, dnf.numero_nota, dnf.status as nf_status " . $base_query . " ORDER BY c.purchase_date DESC, c.id DESC LIMIT ? OFFSET ?";
$purchases_stmt = $conn->prepare($purchases_sql);
$i = 1;
foreach ($params_where as $param) { $purchases_stmt->bindValue($i++, $param); }
$purchases_stmt->bindValue($i++, $limit, PDO::PARAM_INT);
$purchases_stmt->bindValue($i, $offset, PDO::PARAM_INT);
$purchases_stmt->execute();
$purchases = $purchases_stmt->fetchAll(PDO::FETCH_ASSOC);

$suppliers_stmt = $conn->prepare("SELECT id, name FROM fornecedores WHERE empresa_id = ? ORDER BY name ASC");
$suppliers_stmt->execute([$_SESSION['empresa_id']]);
$suppliers_list = $suppliers_stmt->fetchAll(PDO::FETCH_ASSOC);

$products_stmt = $conn->prepare("SELECT id, name, price FROM produtos WHERE empresa_id = ? ORDER BY name ASC");
$products_stmt->execute([$_SESSION['empresa_id']]);
$products_list_json = json_encode($products_stmt->fetchAll(PDO::FETCH_ASSOC));
?>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-3">
        <form action="compras.php" method="GET" class="d-flex flex-wrap gap-2">
            <div class="input-group"><input type="text" name="search" class="form-control" placeholder="Buscar por fornecedor, nº da nota..." value="<?= htmlspecialchars($search_term) ?>"><button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button></div>
        </form>
        <a href="registrar_compra.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Registrar Compra</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>ID</th><th>Data</th><th>Fornecedor</th><th>Nº Nota Fiscal</th><th>Fiscal</th><th>Status IA</th><th>Total</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php if (empty($purchases)): ?>
                        <tr><td colspan="7" class="text-center text-muted">Nenhuma compra encontrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($purchases as $purchase): ?>
                            <tr>
                                <td>#<?= $purchase['id'] ?></td>
                                <td><?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?></td>
                                <td><?= htmlspecialchars($purchase['supplier_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($purchase['numero_nota'] ?? '-') ?></td>
                                <td>
                                    <?php 
                                    // Verificação simplificada de alertas (Fase 1)
                                    // Se a nota tiver status 'processado', assumimos OK por enquanto.
                                    // Na Fase 4, isso buscará da tabela `analise_tributaria`
                                    if (($purchase['nf_status'] ?? '') === 'processado') {
                                        echo '<span class="badge bg-success" title="Sem alertas críticos"><i class="fas fa-check-circle"></i> OK</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">-</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $status = $purchase['nf_status'] ?? 'nao_enviado';
                                    $badges = ['processado' => 'success', 'pendente' => 'warning', 'erro' => 'danger', 'nao_enviado' => 'secondary', 'pendente_confirmacao' => 'info'];
                                    $status_text = str_replace('_', ' ', $status);
                                    echo sprintf('<span class="badge bg-%s">%s</span>', $badges[$status] ?? 'light', ucfirst($status_text));
                                    ?>
                                </td>
                                <td>R$ <?= number_format($purchase['total_amount'], 2, ',', '.') ?></td>
                                <td>
                                    <a href="detalhes_compra.php?id=<?= $purchase['id'] ?>" class="btn btn-sm btn-info" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                                    <?php if ($status === 'pendente_confirmacao'): ?>
                                        <a href="confirmar_compra.php?id=<?= $purchase['id'] ?>" class="btn btn-sm btn-success" title="Revisar e Confirmar Itens"><i class="fas fa-check-double"></i> Revisar</a>
                                    <?php endif; ?>
                                    <?php if ($purchase['fiscal_note_path'] && !in_array($status, ['processado', 'pendente_confirmacao', 'pendente'])): ?>
                                        <a href="processar_nota_action.php?id=<?= $purchase['id'] ?>&path=<?= urlencode($purchase['fiscal_note_path']) ?>" class="btn btn-sm btn-outline-primary" title="Processar Nota com IA"><i class="fas fa-robot"></i> Enviar para IA</a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $purchase['id'] ?>" data-name="#<?= $purchase['id'] ?>" data-bs-toggle="modal" data-bs-target="#deletePurchaseModal" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <nav><ul class="pagination justify-content-center mb-0">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="compras.php?page=<?= $i ?>&search=<?= urlencode($search_term) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul></nav>
    </div>
</div>

<div class="modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="purchaseForm" action="compras.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="purchaseId">
                <div class="modal-header"><h5 class="modal-title" id="purchaseModalLabel">Registrar Nova Compra</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Basta preencher os dados básicos e anexar a nota fiscal. A nossa IA cuidará do resto.</div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Fornecedor*</label><select name="supplier_id" id="supplierId" class="form-select" required><option value="">Selecione...</option><?php foreach($suppliers_list as $s) { echo "<option value=\"" . $s['id'] . "\">" . htmlspecialchars($s['name']) . "</option>"; } ?></select></div>
                        <div class="col-md-6"><label class="form-label">Data da Compra*</label><input type="date" name="purchase_date" id="purchaseDate" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                    </div>
                    <div class="mt-3"><label for="fiscal_note" class="form-label">Anexar Nota Fiscal (PDF, JPG, PNG)*</label><input class="form-control" type="file" name="fiscal_note" id="fiscalNote" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Salvar Compra</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deletePurchaseModal" tabindex="-1" aria-labelledby="deletePurchaseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="compras.php" method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deletePurchaseId">
        <div class="modal-header"><h5 class="modal-title">Confirmar Exclusão</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><p>Você tem certeza que deseja excluir a compra <strong id="deletePurchaseName"></strong>? Esta ação reverterá a entrada de itens no estoque e removerá os dados da IA.</p></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger">Excluir</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Detalhes IA -->
<div class="modal fade" id="aiDetailsModal" tabindex="-1" aria-labelledby="aiDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="aiDetailsModalLabel">Detalhes da Extração da IA</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="aiDetailsBody">
        <!-- O conteúdo será preenchido por JavaScript -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<?php include_once '../includes/rodape.php'; ?>
