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
        // Se estiver no Heroku, parseia a URL do banco de dados
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
        // Se não estiver no Heroku, usa as configurações locais
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
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
 *
 * @param string $data O dado a ser limpo.
 * @return string O dado limpo.
 */
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// --- Funções de Gerenciamento de Planos ---

/**
 * Retorna o plano da empresa do usuário logado.
 * Em um sistema real, isso viria do banco de dados na hora do login.
 * @return string O plano do usuário (ex: 'gratis', 'padrão', 'premium').
 */
function get_user_plan() {
    // Para simulação, vamos ler da sessão. O padrão é 'gratis'.
    // Adicionaremos a lógica para carregar isso do BD no login.
    return $_SESSION['user_plan'] ?? 'gratis';
}

/**
 * Verifica se o usuário logado pode acessar uma funcionalidade com base no seu plano.
 * @param string $funcionalidade O nome da funcionalidade a ser verificada.
 * @return bool True se o acesso for permitido, false caso contrário.
 */
function podeAcessar($funcionalidade) {
    $config = get_planos_config();
    $plano_atual = get_user_plan();

    if (!isset($config['permissoes'][$funcionalidade])) {
        return true; // Se não há regra, o acesso é livre por padrão.
    }

    return in_array($plano_atual, $config['permissoes'][$funcionalidade]);
}

/**
 * Retorna o limite de uma entidade (ex: 'produtos') para o plano atual.
 * @param string $entidade O nome da entidade (ex: 'produtos', 'fornecedores').
 * @return int O limite numérico para a entidade.
 */
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
    // Enforce Trial Logic globally
    checkSubscription();
}

// --- Trial Logic ---
function checkSubscription() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Ignorar se for Super Admin
    if (isSuperAdmin()) return;

    // Ignorar se não estiver logado
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['empresa_id'])) return;

    // Ignorar na própria página de bloqueio para evitar loop
    // E permitir acesso ao fluxo de pagamento e suporte
    $allowed_pages = [
        'subscription_expired.php',
        'checkout.php',
        'processa_pix.php',
        'processa_preference.php',
        'check_status.php',
        'sucesso.php',
        'suporte.php',
        'sair.php' // Importante para permitir logout
    ];

    if (in_array(basename($_SERVER['PHP_SELF']), $allowed_pages)) return;

    // Conexão Singleton (evitar múltiplas conexões)
    global $conn;
    if (!$conn) $conn = connect_db();

    // Buscar plano e data de criação da empresa
    $stmt = $conn->prepare("SELECT ai_plan, created_at FROM empresas WHERE id = ?");
    $stmt->execute([$_SESSION['empresa_id']]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($empresa && $empresa['ai_plan'] === 'free') {
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

/**
 * Verifica se o usuário tem permissão para acessar um módulo (Slug)
 * @param string $slug Slug do módulo (ex: 'estoque', 'financeiro')
 * @param string $nivel_minimo Nível exigido ('leitura', 'escrita', 'admin')
 * @return bool
 */
function check_permission($slug, $nivel_minimo = 'leitura') {
    // 1. Super Admin do sistema
    if (isSuperAdmin()) return true;
    
    // 2. Admin da conta (User Type) - Permissão total (Legacy/Fallback)
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') return true;

    // 3. Verifica permissões de setor (RBAC)
    $perms = $_SESSION['permissions'] ?? [];
    
    // Se for string 'all' (caso admin hardcoded na sessão)
    if ($perms === 'all') return true;
    
    if (!is_array($perms)) return false; // Se não carregou, nega.

    if (!isset($perms[$slug])) return false; // Sem acesso ao módulo

    $userLevel = $perms[$slug];

    // Hierarquia: admin > escrita > leitura
    $levels = ['leitura' => 1, 'escrita' => 2, 'admin' => 3];
    
    // Verifica se os níveis são válidos
    if (!isset($levels[$userLevel]) || !isset($levels[$nivel_minimo])) return false;

    return $levels[$userLevel] >= $levels[$nivel_minimo];
}

/**
 * Alias para check_permission (mais curto para usar em views)
 */
function has_permission($slug, $nivel = 'leitura') {
    return check_permission($slug, $nivel);
}
?>
