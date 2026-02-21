<?php
// modules/rh/views/colaboradores.php
session_start();
require_once __DIR__ . '/../../../includes/funcoes.php';
require_once __DIR__ . '/../../../includes/cabecalho.php';

$conn = connect_db();
$empresa_id = $_SESSION['empresa_id'];

// Fetch Users (Simple List)
$stmt = $conn->prepare("SELECT id, username, email, user_type FROM usuarios WHERE empresa_id = ?");
$stmt->execute([$empresa_id]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-navy mb-1">Colaboradores</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php">Recursos Humanos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Lista de Colaboradores</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card card-dashboard border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-secondary small fw-bold text-uppercase">Nome</th>
                            <th class="text-secondary small fw-bold text-uppercase">Email</th>
                            <th class="text-secondary small fw-bold text-uppercase">Cargo/Tipo</th>
                            <th class="pe-4 text-end text-secondary small fw-bold text-uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($usuarios as $user): ?>
                        <tr>
                            <td class="ps-4 fw-bold">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-navy text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                        <?= strtoupper(substr($user['username'], 0, 2)) ?>
                                    </div>
                                    <?= htmlspecialchars($user['username']) ?>
                                </div>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge <?= $user['user_type'] === 'admin' ? 'bg-navy' : 'bg-secondary' ?> bg-opacity-10 <?= $user['user_type'] === 'admin' ? 'text-navy' : 'text-secondary' ?> border border-opacity-25 rounded-pill px-3">
                                    <?= ucfirst($user['user_type']) ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-muted"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/rodape.php'; ?>
