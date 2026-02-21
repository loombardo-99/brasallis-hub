<?php
// modules/fiscal/views/notas.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';
require_once __DIR__ . '/../../../includes/cabecalho.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../../../login.php'); exit; }
if (!check_permission('fiscal', 'leitura')) { header('Location: index.php?error=acesso_negado'); exit; }

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];
$search = $_GET['search'] ?? '';

// Filtros básicos
$where = "WHERE empresa_id = :empresa_id";
$params = [':empresa_id' => $empresa_id];

if ($search) {
    $where .= " AND (numero LIKE :search OR emitente_destinatario LIKE :search OR chave_acesso LIKE :search)";
    $params[':search'] = "%$search%";
}

$query = "
    SELECT * FROM fiscal_notas
    $where
    ORDER BY data_emissao DESC
";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1">Notas Fiscais</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php">Fiscal</a></li>
                    <li class="breadcrumb-item active">Livro de Notas</li>
                </ol>
            </nav>
        </div>
        <?php if (check_permission('fiscal', 'escrita')): ?>
        <a href="nota_form.php" class="btn btn-trust-primary"><i class="fas fa-plus me-2"></i>Lançar Nota</a>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Número / Série</th>
                            <th>Tipo</th>
                            <th>Emitente/Destinatário</th>
                            <th>Emissão</th>
                            <th>Valor Total</th>
                            <th>Impostos</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notas)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-file-invoice fa-3x mb-3 opacity-25 d-block"></i>
                                Nenhuma nota fiscal encontrada.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($notas as $nota): ?>
                            <tr>
                                <td class="ps-4 fw-bold">
                                    <div class="text-dark">Nº <?= htmlspecialchars($nota['numero']) ?></div>
                                    <small class="text-muted">Série: <?= htmlspecialchars($nota['serie']) ?></small>
                                </td>
                                <td>
                                    <?php if ($nota['tipo'] == 'entrada'): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">ENTRADA</span>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-10 text-success">SAÍDA</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-secondary mb-0"><?= htmlspecialchars($nota['emitente_destinatario']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($nota['cpf_cnpj']) ?></small>
                                </td>
                                <td><?= date('d/m/Y', strtotime($nota['data_emissao'])) ?></td>
                                <td class="fw-bold">R$ <?= number_format($nota['valor_total'], 2, ',', '.') ?></td>
                                <td class="text-danger small fw-bold">R$ <?= number_format($nota['valor_impostos'], 2, ',', '.') ?></td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'autorizada' => 'bg-success text-white',
                                        'cancelada' => 'bg-danger text-white',
                                        'denegada' => 'bg-warning text-dark',
                                        'rascunho' => 'bg-secondary text-white'
                                    ];
                                    ?>
                                    <span class="badge <?= $statusClass[$nota['status']] ?? 'bg-light text-dark' ?>">
                                        <?= strtoupper($nota['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (check_permission('fiscal', 'escrita')): ?>
                                    <a href="invoice_view.php?id=<?= $nota['id'] ?>" target="_blank" class="btn btn-sm btn-light border" title="Imprimir DANFE"><i class="fas fa-print"></i></a>
                                    <a href="xml_export.php?id=<?= $nota['id'] ?>" target="_blank" class="btn btn-sm btn-light border" title="Baixar XML"><i class="fas fa-file-code"></i></a>
                                    <a href="nota_form.php?id=<?= $nota['id'] ?>" class="btn btn-sm btn-light border" title="Editar"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .text-navy { color: #0A2647; }
    .btn-trust-primary { background-color: #0A2647; color: white; border: none; }
    .btn-trust-primary:hover { background-color: #0d325e; color: white; }
</style>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
