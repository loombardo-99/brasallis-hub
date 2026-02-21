<?php
// modules/crm/views/cliente_form.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';

// 1. Auth & Permissions (Before any output)
if (!isset($_SESSION['user_id'])) { header('Location: ../../../login.php'); exit; }
if (!check_permission('crm', 'escrita')) { header('Location: index.php?error=acesso_negado'); exit; }

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$cliente = null;

// 2. Handle POST (Save/Update) - Must happen before any HTML output for redirects to work
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitize_input($_POST['nome']);
    $tipo = sanitize_input($_POST['tipo']);
    $cpf_cnpj = sanitize_input($_POST['cpf_cnpj']);
    $email = sanitize_input($_POST['email']);
    $telefone = sanitize_input($_POST['telefone']);
    $endereco = sanitize_input($_POST['endereco']);
    
    if ($id) {
        $stmt = $conn->prepare("UPDATE clientes SET nome=?, tipo=?, cpf_cnpj=?, email=?, telefone=?, endereco=? WHERE id=? AND empresa_id=?");
        $stmt->execute([$nome, $tipo, $cpf_cnpj, $email, $telefone, $endereco, $id, $empresa_id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO clientes (empresa_id, nome, tipo, cpf_cnpj, email, telefone, endereco) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$empresa_id, $nome, $tipo, $cpf_cnpj, $email, $telefone, $endereco]);
    }
    // Redirect after save
    header('Location: clientes.php?msg=saved');
    exit;
}

// 3. Handle Fetch (if editing)
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ? AND empresa_id = ?");
    $stmt->execute([$id, $empresa_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        // If client not found, show error page (requires header execution now)
        require_once __DIR__ . '/../../../includes/cabecalho.php';
        echo "<div class='container py-5'><div class='alert alert-danger'>Cliente não encontrado. <a href='clientes.php'>Voltar</a></div></div>";
        require_once __DIR__ . '/../../../includes/rodape.php';
        exit;
    }
}

// 4. Finally, include the header content
require_once __DIR__ . '/../../../includes/cabecalho.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1"><?= $id ? 'Editar Cliente' : 'Novo Cliente' ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php">CRM</a></li>
                    <li class="breadcrumb-item"><a href="clientes.php">Clientes</a></li>
                    <li class="breadcrumb-item active"><?= $id ? 'Editar' : 'Novo' ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-secondary">Nome Completo / Razão Social</label>
                        <input type="text" name="nome" class="form-control" required value="<?= $cliente['nome'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-secondary">Tipo</label>
                        <select name="tipo" class="form-select">
                            <option value="PF" <?= ($cliente['tipo'] ?? '') === 'PF' ? 'selected' : '' ?>>Pessoa Física</option>
                            <option value="PJ" <?= ($cliente['tipo'] ?? '') === 'PJ' ? 'selected' : '' ?>>Pessoa Jurídica</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-secondary">CPF / CNPJ</label>
                        <input type="text" name="cpf_cnpj" class="form-control" value="<?= $cliente['cpf_cnpj'] ?? '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-secondary">E-mail</label>
                        <input type="email" name="email" class="form-control" value="<?= $cliente['email'] ?? '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-secondary">Telefone / WhatsApp</label>
                        <input type="text" name="telefone" class="form-control" value="<?= $cliente['telefone'] ?? '' ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small text-secondary">Endereço Completo</label>
                        <textarea name="endereco" class="form-control" rows="2"><?= $cliente['endereco'] ?? '' ?></textarea>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="clientes.php" class="btn btn-light text-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-trust-primary px-4">Salvar Cadastro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .text-navy { color: #0A2647; }
    .btn-trust-primary { background-color: #0A2647; color: white; border: none; }
    .btn-trust-primary:hover { background-color: #0d325e; color: white; }
</style>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
