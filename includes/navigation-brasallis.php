<?php
// includes/navigation-brasallis.php
// BRASALLIS HUB 360 v4.0 - A Visão de Comando Total (Google Strategy)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/funcoes.php';

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'Usuário';
$user_type = $_SESSION['user_type'] ?? 'admin';
$page_active = basename($_SERVER['PHP_SELF']);

// ELITE BREADCRUMB LOGIC (Context Aware)
$pilar_name = "Estratégico";
$module_name = "Dashboard";
$icon_main = "fa-chess-knight";

if (strpos($page_active, 'produtos') !== false || strpos($page_active, 'categorias') !== false) { 
    $pilar_name = "Operacional"; $module_name = "Estoque"; $icon_main = "fa-box-open"; 
} elseif (strpos($_SERVER['PHP_SELF'], 'modules/rh') !== false) { 
    $pilar_name = "Capital Humano"; $module_name = "RH"; $icon_main = "fa-user-gear"; 
} elseif (strpos($_SERVER['PHP_SELF'], 'financeiro') !== false || strpos($page_active, 'relatorios') !== false) { 
    $pilar_name = "Finanças"; $module_name = "Economia"; $icon_main = "fa-hand-holding-dollar"; 
} elseif (strpos($page_active, 'agentes_ia') !== false) { 
    $pilar_name = "Inteligência"; $module_name = "AI Hub"; $icon_main = "fa-wand-magic-sparkles"; 
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Brasallis 360</title>
    
    <!-- Core Dependencies -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Brasallis Hub Elite Styles -->
    <link rel="stylesheet" href="/assets/css/brasallis-hub.css">
    
    <!-- Brasallis UI Engine v4.0 (360) -->
    <script src="/assets/js/brasallis-ui.js" defer></script>
</head>
<body class="bg-light">

<!-- [DESKTOP] THE 360 RAIL SIDEBAR (Clean Edition) -->
<aside class="brasallis-sidebar">
    <!-- Sidebar Edge Trigger: Menu Icon together with the side menu -->
    <div class="brasallis-sidebar-header d-none d-lg-flex" onclick="toggleBrasallis()">
        <i class="fas fa-bars-staggered"></i>
    </div>

    <!-- Clean Top: Branding Removed for Workplace Focus -->
    <nav class="brasallis-nav">
        <!-- THE 360 BUSINESS HUB (Strategic Pillars) -->
        <div class="brasallis-360-hub px-2">
            
            <?php
            // DEFINIÇÃO DOS 6 PILARES BRASALLIS (Google Style)
            // Slug mapeado para permissões do RH/Gestor de Equipe
            $pillars_raw = [
                'estratégico' => [
                    'label' => 'Estratégico',
                    'icon' => 'fa-chart-pie',
                    'color' => '#111827',
                    'perm' => 'admin',
                    'links' => [
                        ['label' => 'Dashboard Executivo', 'url' => '/admin/painel_admin.php'],
                        ['label' => 'Relatórios P&L', 'url' => '/admin/relatorios.php'],
                        ['label' => 'Análise de Metas', 'url' => '/admin/metas.php']
                    ]
                ],
                'operacional' => [
                    'label' => 'Operacional',
                    'icon' => 'fa-boxes-stacked',
                    'color' => '#2563eb',
                    'perm' => 'estoque',
                    'links' => [
                        ['label' => 'Estoque Central', 'url' => '/admin/produtos.php'],
                        ['label' => 'Categorias & Tags', 'url' => '/admin/categorias.php'],
                        ['label' => 'Movimentações', 'url' => '/admin/movimentacoes.php'],
                        ['label' => 'Gestão de Compras', 'url' => '/admin/registrar_compra.php']
                    ]
                ],
                'pessoas' => [
                    'label' => 'Capital Humano',
                    'icon' => 'fa-user-tie',
                    'color' => '#6366f1',
                    'perm' => 'rh',
                    'links' => [
                        ['label' => 'Equipe (RH)', 'url' => '/modules/rh/views/index.php'],
                        ['label' => 'Folha de Pagto', 'url' => '/modules/rh/views/folha.php'],
                        ['label' => 'Controle de Ponto', 'url' => '/modules/rh/views/ponto.php']
                    ]
                ],
                'financeiro' => [
                    'label' => 'Financeiro',
                    'icon' => 'fa-money-bill-transfer',
                    'color' => '#10b981',
                    'perm' => 'financeiro',
                    'links' => [
                        ['label' => 'Fluxo de Caixa', 'url' => '/modules/financeiro/views/index.php'],
                        ['label' => 'Contas a Pagar', 'url' => '/modules/financeiro/views/contas.php'],
                        ['label' => 'Fiscal Hub', 'url' => '/admin/inteligencia_tributaria.php']
                    ]
                ],
                'comercial' => [
                    'label' => 'Comercial',
                    'icon' => 'fa-rocket',
                    'color' => '#f59e0b',
                    'perm' => 'crm',
                    'links' => [
                        ['label' => 'CRM Pipeline', 'url' => '/modules/crm/views/kanban.php'],
                        ['label' => 'Frente de Caixa (PDV)', 'url' => '/modules/pdv/views/index.php'],
                        ['label' => 'Base de Clientes', 'url' => '/admin/clientes.php']
                    ]
                ],
                'inteligência' => [
                    'label' => 'Inteligência',
                    'icon' => 'fa-wand-magic-sparkles',
                    'color' => '#a855f7',
                    'perm' => 'ai',
                    'links' => [
                        ['label' => 'IA Agents', 'url' => '/admin/agentes_ia.php'],
                        ['label' => 'Automações Hub', 'url' => '/admin/debug_automations.php']
                    ]
                ]
            ];

            // Filtra pilares por permissão
            $pillars = [];
            foreach ($pillars_raw as $id => $p) {
                if (check_permission($p['perm'], 'leitura')) {
                    $pillars[$id] = $p;
                }
            }

            // Lógica de Foco para Funcionários: se tiver apenas 1 pilar, ele abre automático
            $is_employee = ($_SESSION['user_type'] === 'employee');
            $auto_expand_only = $is_employee && (count($pillars) === 1);
            ?>

            <?php foreach ($pillars as $id => $p): 
                $is_active_pillar = (mb_strtolower($pilar_name) == $id) || $auto_expand_only;
            ?>
            <div class="brasallis-pillar <?= $is_active_pillar ? 'active' : '' ?>" data-pillar="<?= $id ?>">
                <div class="brasallis-item <?= $is_active_pillar ? 'active' : '' ?>" title="<?= $p['label'] ?>">
                    <i class="fas <?= $p['icon'] ?>"></i>
                    <span><?= $p['label'] ?></span>
                </div>
                <!-- Accordion Drawer (Desktop Only) -->
                <div class="pillar-accordion shadow-inner">
                    <?php foreach ($p['links'] as $link): ?>
                    <a href="<?= $link['url'] ?>" class="p-link <?= (strpos($_SERVER['PHP_SELF'], $link['url']) !== false) ? 'active fw-bold text-primary' : '' ?>">
                        <?= $link['label'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </nav>

    <div class="brasallis-footer pb-4">
        <?php if (check_permission('admin', 'leitura')): ?>
        <a href="/admin/configuracoes.php" class="brasallis-item" title="Núcleo">
            <i class="fas fa-gear"></i>
            <span>Sistema</span>
        </a>
        <?php endif; ?>
        <a href="/sair.php" class="brasallis-item text-danger">
            <i class="fas fa-right-from-bracket"></i>
            <span>Encerrar</span>
        </a>
    </div>
</aside>

<!-- [DESKTOP/MOBILE] THE 360 TOPBAR -->
<nav class="brasallis-topbar">
    <!-- 1. LEFT: FAGULHA BRAND -->
    <div class="d-flex align-items-center h-100">
        <!-- Mobile Toggle (Mobile Only) -->
        <div class="brasallis-toggle d-lg-none" onclick="history.back()">
            <i class="fas fa-arrow-left text-secondary"></i>
        </div>
        <a href="/admin/painel_admin.php" class="d-flex align-items-center me-4">
            <img src="/assets/img/pureza.png" alt="Fagulha" class="brasallis-fagulha">
        </a>
    </div>

    <!-- 2. CENTER: SEARCH COMMAND BOX -->
    <div class="brasallis-search-container d-none d-xl-flex mx-auto shadow-none">
        <i class="fas fa-terminal text-muted" style="font-size: 0.8rem;"></i>
        <input type="text" class="brasallis-search-input" placeholder="Comando 360... (/)" autocomplete="off">
        <kbd class="ms-2 bg-white border text-muted px-2 py-0 border-0" style="font-size: 0.65rem;">/</kbd>
    </div>

    <!-- 3. RIGHT: PILLAR & PROFILE -->
    <div class="d-flex align-items-center gap-3 h-100 ms-auto">
        <!-- PILLAR CONTEXT -->
        <div class="d-none d-lg-flex align-items-center gap-3 pe-4 border-end h-50">
            <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="fas <?= $icon_main ?>" style="font-size: 0.85rem;"></i>
            </div>
            <div class="fw-bold text-secondary text-uppercase ls-1" style="font-size: 0.75rem;"><?= $module_name ?></div>
        </div>

        <div class="dropdown">
            <div class="profile-pill" data-bs-toggle="dropdown">
                <?= substr($user_name, 0, 1) ?>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 rounded-4 p-2" style="width: 220px;">
                <li class="px-3 py-2 border-bottom mb-2">
                    <div class="small text-muted fw-bold"><?= htmlspecialchars($user_name) ?></div>
                    <div class="text-xs text-muted" style="font-size: 0.65rem;"><?= htmlspecialchars($_SESSION['empresa_nome'] ?? 'Brasallis User') ?></div>
                </li>
                <li><a class="dropdown-item rounded-3 py-2" href="/perfil.php"><i class="fas fa-user-circle me-3 opacity-50"></i>Meu Perfil</a></li>
                <li><a class="dropdown-item rounded-3 py-2 text-danger" href="/sair.php"><i class="fas fa-power-off me-3"></i>Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- [MOBILE] THE 360 LOWER NAVIGATION -->
<?php
$home_url = '/admin/painel_admin.php';
if ($is_employee) {
    // Busca o primeiro link do primeiro pilar disponível como "Home" para o funcionário
    $first_pillar = reset($pillars);
    if ($first_pillar) {
        $home_url = $first_pillar['links'][0]['url'];
    }
}
?>
<nav class="brasallis-bottom-nav">
    <a href="<?= $home_url ?>" class="bottom-nav-item <?= (strpos($_SERVER['PHP_SELF'], $home_url) !== false) ? 'active' : '' ?>">
        <i class="fas fa-shapes"></i>
        <span>Home</span>
    </a>
    <a href="#" class="bottom-nav-item" onclick="toggleMobileSearch()">
        <i class="fas fa-magnifying-glass"></i>
        <span>Busca</span>
    </a>
    <a href="/admin/produtos.php" class="bottom-nav-item">
        <i class="fas fa-box"></i>
        <span>Stocks</span>
    </a>
    <a href="#" class="bottom-nav-item" data-bs-toggle="offcanvas" data-bs-target="#brasallis360Offcanvas">
        <i class="fas fa-grid-2"></i>
        <span>Apps</span>
    </a>
</nav>

<!-- [MOBILE] 360 APP DRAWER (Offcanvas) -->
<div class="offcanvas offcanvas-bottom vh-100 bg-white border-0" tabindex="-1" id="brasallis360Offcanvas">
    <div class="offcanvas-header py-4 border-bottom">
        <div class="d-flex align-items-center">
            <img src="/assets/img/pureza.png" alt="Logo" style="height: 30px;">
            <h5 class="offcanvas-title ms-3 fw-bold">Brasallis 360 Hub</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4 bg-light">
        <div class="d-grid gap-3" style="grid-template-columns: repeat(3, 1fr);">
            <?php foreach ($pillars as $id => $app): ?>
            <div class="col-4">
                <a href="<?= $app['links'][0]['url'] ?>" class="text-decoration-none d-block text-center p-3 rounded-4 bg-white shadow-sm hover-lift">
                    <div class="rounded-4 mb-2 d-flex align-items-center justify-content-center mx-auto" style="width: 50px; height: 50px; background: <?= $app['color'] ?>10; color: <?= $app['color'] ?>;">
                        <i class="fas <?= $app['icon'] ?> fs-4"></i>
                    </div>
                    <div class="small fw-bold text-dark" style="font-size: 0.65rem;"><?= strtoupper($app['label']) ?></div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- MAIN CONTENT WRAPPER -->
<main class="brasallis-main">
    <div class="brasallis-content-container p-3 p-lg-4">
