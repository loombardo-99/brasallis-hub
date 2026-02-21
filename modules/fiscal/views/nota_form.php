<?php
// modules/fiscal/views/nota_form.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';

// 1. Auth & Permissions
if (!isset($_SESSION['user_id'])) { header('Location: ../../../login.php'); exit; }
if (!check_permission('fiscal', 'escrita')) { header('Location: index.php?error=acesso_negado'); exit; }

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$nota = null;

// 2. Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = sanitize_input($_POST['numero']);
    $serie = sanitize_input($_POST['serie']);
    $tipo = sanitize_input($_POST['tipo']);
    $modelo = sanitize_input($_POST['modelo']);
    $chave = sanitize_input($_POST['chave_acesso']);
    $emitente = sanitize_input($_POST['emitente_destinatario']);
    $cpf_cnpj = sanitize_input($_POST['cpf_cnpj']);
    $data_emissao = sanitize_input($_POST['data_emissao']);
    $valor_total = str_replace(',', '.', str_replace('.', '', $_POST['valor_total']));
    // Detailed Taxes
    $icms_base = str_replace(',', '.', str_replace('.', '', $_POST['icms_base']));
    $icms_valor = str_replace(',', '.', str_replace('.', '', $_POST['icms_valor']));
    $ipi_valor = str_replace(',', '.', str_replace('.', '', $_POST['ipi_valor']));
    $pis_valor = str_replace(',', '.', str_replace('.', '', $_POST['pis_valor']));
    $cofins_valor = str_replace(',', '.', str_replace('.', '', $_POST['cofins_valor']));
    
    // Auto-sum total taxes just in case JS failed, or trust the posted total
    $valor_impostos = str_replace(',', '.', str_replace('.', '', $_POST['valor_impostos']));
    
    $status = sanitize_input($_POST['status']);

    if ($id) {
        $stmt = $conn->prepare("UPDATE fiscal_notas SET numero=?, serie=?, tipo=?, modelo=?, chave_acesso=?, emitente_destinatario=?, cpf_cnpj=?, data_emissao=?, valor_total=?, valor_impostos=?, icms_base=?, icms_valor=?, ipi_valor=?, pis_valor=?, cofins_valor=?, status=? WHERE id=? AND empresa_id=?");
        $stmt->execute([$numero, $serie, $tipo, $modelo, $chave, $emitente, $cpf_cnpj, $data_emissao, $valor_total, $valor_impostos, $icms_base, $icms_valor, $ipi_valor, $pis_valor, $cofins_valor, $status, $id, $empresa_id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO fiscal_notas (empresa_id, numero, serie, tipo, modelo, chave_acesso, emitente_destinatario, cpf_cnpj, data_emissao, valor_total, valor_impostos, icms_base, icms_valor, ipi_valor, pis_valor, cofins_valor, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$empresa_id, $numero, $serie, $tipo, $modelo, $chave, $emitente, $cpf_cnpj, $data_emissao, $valor_total, $valor_impostos, $icms_base, $icms_valor, $ipi_valor, $pis_valor, $cofins_valor, $status]);
    }
    
    header('Location: notas.php?msg=saved');
    exit;
}

// 3. Fetch Record if Edit
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM fiscal_notas WHERE id = ? AND empresa_id = ?");
    $stmt->execute([$id, $empresa_id]);
    $nota = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$nota) {
        require_once __DIR__ . '/../../../includes/cabecalho.php';
        echo "<div class='container py-5'><div class='alert alert-danger'>Nota não encontrada. <a href='notas.php'>Voltar</a></div></div>";
        require_once __DIR__ . '/../../../includes/rodape.php';
        exit;
    }
}

require_once __DIR__ . '/../../../includes/cabecalho.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1"><?= $id ? 'Editar Nota Fiscal' : 'Lançar Nota Fiscal' ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php">Fiscal</a></li>
                    <li class="breadcrumb-item"><a href="notas.php">Livro de Notas</a></li>
                    <li class="breadcrumb-item active"><?= $id ? 'Editar' : 'Novo Lançamento' ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST">
                
                <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-info-circle me-1"></i> Dados Básicos de Emissão</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-secondary">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="entrada" <?= ($nota['tipo'] ?? '') === 'entrada' ? 'selected' : '' ?>>Entrada (Compra)</option>
                            <option value="saida" <?= ($nota['tipo'] ?? '') === 'saida' ? 'selected' : '' ?>>Saída (Venda)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-secondary">Modelo</label>
                        <select name="modelo" class="form-select" required>
                            <option value="nfe" <?= ($nota['modelo'] ?? '') === 'nfe' ? 'selected' : '' ?>>NF-e (55)</option>
                            <option value="nfse" <?= ($nota['modelo'] ?? '') === 'nfse' ? 'selected' : '' ?>>NFS-e (Serviço)</option>
                            <option value="cte" <?= ($nota['modelo'] ?? '') === 'cte' ? 'selected' : '' ?>>CT-e (Transporte)</option>
                            <option value="cupom" <?= ($nota['modelo'] ?? '') === 'cupom' ? 'selected' : '' ?>>NFC-e / Cupom</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-secondary">Número</label>
                        <input type="text" name="numero" class="form-control" required value="<?= $nota['numero'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-secondary">Série</label>
                        <input type="text" name="serie" class="form-control" value="<?= $nota['serie'] ?? '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-secondary">Chave de Acesso (44 dígitos)</label>
                        <input type="text" name="chave_acesso" class="form-control" maxlength="44" value="<?= $nota['chave_acesso'] ?? '' ?>" placeholder="Sem pontos ou espaços">
                    </div>
                </div>

                <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-users me-1"></i> Dados das Partes</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-secondary">Emitente / Destinatário</label>
                        <input type="text" name="emitente_destinatario" class="form-control" required value="<?= $nota['emitente_destinatario'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-secondary">CPF / CNPJ</label>
                        <input type="text" name="cpf_cnpj" class="form-control" value="<?= $nota['cpf_cnpj'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-secondary">Data de Emissão</label>
                        <input type="date" name="data_emissao" class="form-control" required value="<?= $nota['data_emissao'] ?? date('Y-m-d') ?>">
                    </div>
                </div>

                <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-coins me-1"></i> Valores e Status</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-secondary">Valor Total da Nota (R$)</label>
                        <input type="text" name="valor_total" class="form-control valor-mask" required value="<?= $nota ? number_format($nota['valor_total'], 2, ',', '.') : '' ?>" placeholder="0,00">
                    </div>
                    <div class="col-md-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-secondary">Status da Nota</label>
                        <select name="status" class="form-select" required>
                            <option value="autorizada" <?= ($nota['status'] ?? '') === 'autorizada' ? 'selected' : '' ?>>Autorizada (Produção)</option>
                            <option value="rascunho" <?= ($nota['status'] ?? '') === 'rascunho' ? 'selected' : '' ?>>Rascunho / Digitação</option>
                            <option value="cancelada" <?= ($nota['status'] ?? '') === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                            <option value="denegada" <?= ($nota['status'] ?? '') === 'denegada' ? 'selected' : '' ?>>Denegada</option>
                        </select>
                    </div>
                </div>

                <h6 class="fw-bold text-secondary mb-3 mt-4"><i class="fas fa-calculator me-1"></i> Detalhamento Tributário</h6>
                <div class="row g-3 p-3 bg-light rounded border">
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Base de Cálculo ICMS</label>
                        <input type="text" name="icms_base" class="form-control valor-mask tax-calc" value="<?= $nota ? number_format($nota['icms_base'] ?? 0, 2, ',', '.') : '' ?>" placeholder="0,00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Valor ICMS</label>
                        <input type="text" name="icms_valor" class="form-control valor-mask tax-calc" value="<?= $nota ? number_format($nota['icms_valor'] ?? 0, 2, ',', '.') : '' ?>" placeholder="0,00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Valor IPI</label>
                        <input type="text" name="ipi_valor" class="form-control valor-mask tax-calc" value="<?= $nota ? number_format($nota['ipi_valor'] ?? 0, 2, ',', '.') : '' ?>" placeholder="0,00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Valor PIS</label>
                        <input type="text" name="pis_valor" class="form-control valor-mask tax-calc" value="<?= $nota ? number_format($nota['pis_valor'] ?? 0, 2, ',', '.') : '' ?>" placeholder="0,00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Valor COFINS</label>
                        <input type="text" name="cofins_valor" class="form-control valor-mask tax-calc" value="<?= $nota ? number_format($nota['cofins_valor'] ?? 0, 2, ',', '.') : '' ?>" placeholder="0,00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-navy">Total Impostos</label>
                        <input type="text" name="valor_impostos" id="total_impostos" class="form-control valor-mask fw-bold bg-white" readonly value="<?= $nota ? number_format($nota['valor_impostos'], 2, ',', '.') : '' ?>" placeholder="0,00">
                        <small class="text-muted" style="font-size: 0.7rem;">Soma automática</small>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="notas.php" class="btn btn-light text-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-trust-primary px-4">Salvar Nota</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Mask for multiple currency fields
    function applyMask(input) {
        let v = input.value.replace(/\D/g, '');
        v = (v/100).toFixed(2) + '';
        v = v.replace(".", ",");
        v = v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
        v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
        input.value = v;
    }

    document.querySelectorAll('.valor-mask').forEach(input => {
        input.addEventListener('keyup', function(e) {
            applyMask(e.target);
        });
        // Initial mask if value exists
        if(input.value) applyMask(input);
    });

    // Auto Calc Taxes
    document.querySelectorAll('.tax-calc').forEach(input => {
        input.addEventListener('keyup', function() {
            let total = 0;
            document.querySelectorAll('.tax-calc').forEach(el => {
                if(el.name === 'icms_base') return; // Skip base, sum values only
                let val = parseFloat(el.value.replace(/\./g, '').replace(',', '.') || 0);
                total += val;
            });
            
            // Format back to money
            let formatter = new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
            document.getElementById('total_impostos').value = formatter.format(total);
        });
    });
</script>

<style>
    .text-navy { color: #0A2647; }
    .btn-trust-primary { background-color: #0A2647; color: white; border: none; }
    .btn-trust-primary:hover { background-color: #0d325e; color: white; }
</style>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
