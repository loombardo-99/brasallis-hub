<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../includes/funcoes.php';

$conn = connect_db();
$user_id = $_SESSION['user_id'];
$empresa_id = $_SESSION['empresa_id'];

// --- LÓGICA DE MANIPULAÇÃO (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notification_id = $_POST['id'] ?? null;

    // Lógica de UPSERT (UPDATE ou INSERT) para o status do usuário
    $upsert_sql = "INSERT INTO notificacao_status_usuario (notificacao_id, user_id, is_read, is_dismissed) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE is_read = VALUES(is_read), is_dismissed = VALUES(is_dismissed)";
    
    if ($action === 'mark_read' && $notification_id) {
        $stmt = $conn->prepare($upsert_sql);
        $stmt->execute([$notification_id, $user_id, 1, 0]);
    }
    if ($action === 'delete' && $notification_id) {
        $stmt = $conn->prepare($upsert_sql);
        $stmt->execute([$notification_id, $user_id, 1, 1]); // Dispensar também marca como lido
    }
    if ($action === 'mark_all_read') {
        // Pega todas as notificações não dispensadas para o usuário e as marca como lidas
        // FIX: Usar query especifica para NÃO alterar o is_dismissed de registros existentes
        $sql_mark_all = "INSERT INTO notificacao_status_usuario (notificacao_id, user_id, is_read, is_dismissed) VALUES (?, ?, 1, 0) ON DUPLICATE KEY UPDATE is_read = 1";
        
        $sql_get_all = "SELECT id FROM notificacoes WHERE empresa_id = ?";
        $stmt_get_all = $conn->prepare($sql_get_all);
        $stmt_get_all->execute([$empresa_id]);
        while($row = $stmt_get_all->fetch(PDO::FETCH_ASSOC)) {
            $conn->prepare($sql_mark_all)->execute([$row['id'], $user_id]);
        }
    }
    if ($action === 'delete_all_read') {
        // Pega todas as notificações lidas (e não dispensadas) para o usuário e as dispensa
        $sql_get_read = "SELECT n.id FROM notificacoes n LEFT JOIN notificacao_status_usuario s ON n.id = s.notificacao_id AND s.user_id = ? WHERE n.empresa_id = ? AND s.is_read = 1 AND (s.is_dismissed = 0 OR s.is_dismissed IS NULL)";
        $stmt_get_read = $conn->prepare($sql_get_read);
        $stmt_get_read->execute([$user_id, $empresa_id]);
        while($row = $stmt_get_read->fetch(PDO::FETCH_ASSOC)) {
            $conn->prepare($upsert_sql)->execute([$row['id'], $user_id, 1, 1]);
        }
    }

    header("Location: notificacoes.php" . (isset($_GET['filter']) ? '?filter=' . $_GET['filter'] : ''));
    exit;
}

$filter = $_GET['filter'] ?? 'all'; // all, unread, read

// --- LÓGICA DE EXIBIÇÃO (GET) ---
$sql = "
    SELECT 
        n.id, n.type, n.message, n.created_at,
        COALESCE(s.is_read, 0) as is_read,
        COALESCE(s.is_dismissed, 0) as is_dismissed
    FROM notificacoes n
    LEFT JOIN notificacao_status_usuario s ON n.id = s.notificacao_id AND s.user_id = :user_id
    WHERE n.empresa_id = :empresa_id AND COALESCE(s.is_dismissed, 0) = 0
";
$params = [':user_id' => $user_id, ':empresa_id' => $empresa_id];

if ($filter === 'unread') {
    $sql .= " AND COALESCE(s.is_read, 0) = 0";
} elseif ($filter === 'read') {
    $sql .= " AND COALESCE(s.is_read, 0) = 1";
}
$sql .= " ORDER BY n.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once '../includes/cabecalho.php';

function get_icon_for_notification($type) {
    switch ($type) {
        case 'low_stock': return 'fas fa-exclamation-triangle text-danger';
        case 'nearing_expiration': return 'fas fa-hourglass-half text-warning';
        default: return 'fas fa-info-circle text-info';
    }
}
?>

<h1 class="mb-4">Central de Notificações</h1>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="btn-group" role="group">
            <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary'; ?>">Todas</a>
            <a href="?filter=unread" class="btn <?php echo $filter === 'unread' ? 'btn-primary' : 'btn-outline-secondary'; ?>">Não Lidas</a>
            <a href="?filter=read" class="btn <?php echo $filter === 'read' ? 'btn-primary' : 'btn-outline-secondary'; ?>">Lidas</a>
        </div>
        <div class="d-flex gap-2">
            <form action="notificacoes.php" method="POST" class="d-inline"><input type="hidden" name="action" value="mark_all_read"><button type="submit" class="btn btn-sm btn-secondary">Marcar todas como lidas</button></form>
            <form action="notificacoes.php" method="POST" class="d-inline"><input type="hidden" name="action" value="delete_all_read"><button type="submit" class="btn btn-sm btn-danger">Limpar lidas</button></form>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($notifications)): ?>
            <div class="text-center text-muted p-5">
                <p class="fs-4">Tudo em ordem!</p>
                <p>Nenhuma notificação para exibir.</p>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item list-group-item-action d-flex gap-3 py-3 <?php echo $notification['is_read'] ? 'bg-light text-muted' : ''; ?>">
                        <i class="<?php echo get_icon_for_notification($notification['type']); ?> fa-lg mt-1"></i>
                        <div class="d-flex gap-2 w-100 justify-content-between">
                            <div>
                                <p class="mb-0 <?php echo !$notification['is_read'] ? 'fw-bold' : ''; ?>"><?php echo $notification['message']; ?></p>
                                <small class="d-block opacity-75"><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></small>
                            </div>
                            <div class="text-end d-flex gap-2 align-items-center">
                                <?php if (!$notification['is_read']): ?>
                                    <form action="notificacoes.php" method="POST" class="d-inline"><input type="hidden" name="action" value="mark_read"><input type="hidden" name="id" value="<?php echo $notification['id']; ?>"><button type="submit" class="btn btn-sm btn-outline-primary">Marcar como lida</button></form>
                                <?php endif; ?>
                                <form action="notificacoes.php" method="POST" class="d-inline"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $notification['id']; ?>"><button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button></form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/rodape.php'; ?>
