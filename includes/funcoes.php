<?php

// Inclui o arquivo de configuração do banco de dados
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/planos_config.php';

/**
 * Conecta-se ao banco de dados.
 *
 * @return PDO|null Retorna um objeto PDO em caso de sucesso ou null em caso de falha.
 */
function connect_db()
{
    // Verifica se a variável de ambiente do Heroku para o banco de dados existe
    $db_url = getenv('JAWSDB_URL');

    if ($db_url) {
        $db_parts = parse_url($db_url);
        $host = $db_parts['host'];
        $user = $db_parts['user'];
        $pass = $db_parts['pass'];
        $dbname = ltrim($db_parts['path'], '/');

        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            error_log("Erro de conexão com o banco de dados Heroku: " . $e->getMessage());
            return null;
        }
    } else {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            $conn = new PDO($dsn, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            error_log("Erro de conexão com o banco de dados local: " . $e->getMessage());
            return null;
        }
    }
}

/**
 * Limpa os dados de entrada do usuário para evitar ataques XSS.
 */
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// --- Funções de Gerenciamento de Planos ---

function get_user_plan() {
    return $_SESSION['user_plan'] ?? 'iniciante';
}

function podeAcessar($funcionalidade) {
    $config = get_planos_config();
    $plano_atual = get_user_plan();

    if (!isset($config['permissoes'][$funcionalidade])) {
        return true; 
    }

    return in_array($plano_atual, $config['permissoes'][$funcionalidade]);
}

function get_limite($entidade) {
    $config = get_planos_config();
    $plano_atual = get_user_plan();

    return $config['limites'][$plano_atual][$entidade] ?? 0;
}

// --- Super Admin Helpers ---

function isSuperAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'super_admin';
}

function checkSuperAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isSuperAdmin()) {
        $_SESSION['error_message'] = "Acesso restrito ao Super Admin.";
        header("Location: ../admin/painel_admin.php");
        exit;
    }
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    checkSubscription();
}

// --- Trial Logic ---
function checkSubscription() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isSuperAdmin()) return;
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['empresa_id'])) return;

    $allowed_pages = [
        'subscription_expired.php',
        'checkout.php',
        'processa_pix.php',
        'processa_preference.php',
        'check_status.php',
        'sucesso.php',
        'suporte.php',
        'sair.php'
    ];

    if (in_array(basename($_SERVER['PHP_SELF']), $allowed_pages)) return;

    global $conn;
    if (!$conn) $conn = connect_db();

    $stmt = $conn->prepare("SELECT ai_plan, created_at FROM empresas WHERE id = ?");
    $stmt->execute([$_SESSION['empresa_id']]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lógica para o Plano Iniciante (MEI) - Expira em 20 dias se for considerado "trial"
    if ($empresa && $empresa['ai_plan'] === 'iniciante') {
        $created_at = new DateTime($empresa['created_at']);
        $now = new DateTime();
        $interval = $created_at->diff($now);
        $days_active = $interval->days;

        if ($days_active > 20) {
            header("Location: ../admin/subscription_expired.php");
            exit;
        }
    }
}

// --- RBAC: Verificação de Permissões ---

function check_permission($slug, $nivel_minimo = 'leitura') {
    if (isSuperAdmin()) return true;
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') return true;

    $perms = $_SESSION['permissions'] ?? [];
    if ($perms === 'all') return true;
    if (!is_array($perms)) return false; 
    if (!isset($perms[$slug])) return false; 

    $userLevel = $perms[$slug];
    $levels = ['leitura' => 1, 'escrita' => 2, 'admin' => 3];
    
    if (!isset($levels[$userLevel]) || !isset($levels[$nivel_minimo])) return false;

    return $levels[$userLevel] >= $levels[$nivel_minimo];
}

function has_permission($slug, $nivel = 'leitura') {
    return check_permission($slug, $nivel);
}
?>
