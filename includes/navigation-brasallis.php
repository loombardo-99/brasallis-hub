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
                    'priority' => 10,
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
                    'priority' => 7,
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
                    'priority' => 6,
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
                    'priority' => 8,
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
                    'priority' => 5,
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
                    'priority' => 9,
                    'links' => [
                        ['label' => 'IA Agents', 'url' => '/admin/agentes_ia.php'],
                        ['label' => 'Black Box Status', 'url' => '/admin/analytics_blackbox.php'],
                        ['label' => 'Automações Hub', 'url' => '/admin/debug_automations.php']
                    ]
                ]
            ];

            // Filtra pilares por permissão e ordena por autoridade (1-10)
            $pillars = [];
            foreach ($pillars_raw as $id => $p) {
                if (check_permission($p['perm'], 'leitura')) {
                    $pillars[$id] = $p;
                }
            }

            // --- MOTOR DE AUTORIDADE 360 (v2.19) ---
            uasort($pillars, function($a, $b) {
                return $b['priority'] - $a['priority'];
            });

            // Lógica de Redirecionamento 360 (Home Aware)
            $is_employee = ($user_type === 'employee');
            $home_url = '/admin/painel_admin.php';
            
            if ($is_employee && !empty($pillars)) {
                $top_pillar = reset($pillars); // O de maior autoridade (sorted)
                $home_url = $top_pillar['links'][0]['url'];
            }

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

<!-- [DESKTOP/MOBILE] THE 360 TOPBAR (FLUENT ISLAND) -->
<nav class="brasallis-topbar shadow-sm">
    <!-- 1. BRAND -->
    <div class="d-flex align-items-center">
        <a href="<?= $home_url ?>" class="d-flex align-items-center me-3">
            <img src="/assets/img/pureza.png" alt="Logo" style="height: 28px; width: auto; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.1));">
        </a>
    </div>

    <!-- 2. OMNI-SEARCH (POWER BAR) -->
    <div class="flex-grow-1 d-flex justify-content-center">
        <div class="brasallis-search-container">
            <i class="fas fa-search text-muted opacity-40" style="font-size: 0.8rem;"></i>
            <input type="text" class="brasallis-search-input" placeholder="Pesquisar comando 360... (Cmd + /)" autocomplete="off">
            <div class="d-none d-md-flex align-items-center opacity-75 ms-2">
                <kbd class="mac-shortcut">⌘ /</kbd>
            </div>
        </div>
    </div>

    <!-- 3. ACTION CLUSTER -->
    <div class="d-flex align-items-center gap-2">
        <!-- AI Launcher -->
        <button class="btn-ai-sparkle shadow-none border-0" onclick="window.openAgentChat()" title="Brasallis AI">
            <i class="fas fa-wand-magic-sparkles" style="font-size: 0.85rem;"></i>
        </button>

        <!-- Profile Island -->
        <div class="dropdown ms-2">
            <div class="profile-pill" data-bs-toggle="dropdown">
                <?= substr($user_name, 0, 1) ?>
            </div>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-solar mt-3 shadow-sm border-0" style="min-width: 260px;">
                <li class="px-3 pb-2 pt-1 border-bottom border-light">
                    <div class="d-flex flex-column">
                        <div class="fw-bold text-dark fs-6" style="letter-spacing: -0.3px;"><?= htmlspecialchars($user_name) ?></div>
                        <div class="text-muted" style="font-size: 0.70rem; font-weight: 500;">Administrador • ID #0<?= $_SESSION['empresa_id'] ?? '1' ?></div>
                    </div>
                </li>
                <li class="pt-2"><a class="dropdown-item py-2" href="/perfil.php"><i class="fas fa-user-circle me-2 text-muted"></i> Ajustes de Perfil</a></li>
                <li><a class="dropdown-item py-2" href="#"><i class="fas fa-desktop me-2 text-muted"></i> Ajuste Local</a></li>
                <li><a class="dropdown-item py-2" href="/admin/configuracoes.php"><i class="fas fa-sliders me-2 text-muted"></i> Opções de Sistema</a></li>
                <li class="border-top border-light mt-2 pt-2"><a class="dropdown-item py-2 text-danger fw-bold" href="/sair.php"><i class="fas fa-power-off me-2"></i> Encerrar Sessão</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- [MOBILE] THE 360 LOWER NAVIGATION (5-BUTTON APP RAIL) -->
<?php
// MATRIZ DE AÇÕES RÁPIDAS 360 (Google/Microsoft Strategy)
$default_actions = [
    ['icon' => 'fa-shapes', 'label' => 'Home', 'url' => $home_url],
    ['icon' => 'fa-magnifying-glass', 'label' => 'Busca', 'url' => '#', 'onclick' => 'toggleMobileSearch()'],
    ['icon' => 'fa-box', 'label' => 'Stocks', 'url' => '/admin/produtos.php'],
    ['icon' => 'fa-gear', 'label' => 'Sistema', 'url' => '/admin/configuracoes.php']
];

$module_actions = [
    'Dashboard' => [
        ['icon' => 'fa-chart-pie', 'label' => 'Resumo', 'url' => '/admin/painel_admin.php'],
        ['icon' => 'fa-file-invoice-dollar', 'label' => 'P&L', 'url' => '/admin/relatorios.php'],
        ['icon' => 'fa-user-tie', 'label' => 'Equipe', 'url' => '/modules/rh/views/index.php'],
        ['icon' => 'fa-wand-magic-sparkles', 'label' => 'IA Hub', 'url' => '/admin/agentes_ia.php']
    ],
    'Estratégico' => [
        ['icon' => 'fa-gauge-high', 'label' => 'Painel', 'url' => '/admin/painel_admin.php'],
        ['icon' => 'fa-sack-dollar', 'label' => 'P&L', 'url' => '/admin/relatorios.php'],
        ['icon' => 'fa-users-gear', 'label' => 'Equipe', 'url' => '/modules/rh/views/index.php'],
        ['icon' => 'fa-sliders', 'label' => 'Sistema', 'url' => '/admin/configuracoes.php']
    ],
    'Estoque' => [
        ['icon' => 'fa-plus-circle', 'label' => 'Novo', 'url' => '#', 'data_target' => 'addProductModal'],
        ['icon' => 'fa-cart-shopping', 'label' => 'Compras', 'url' => '/admin/registrar_compra.php'],
        ['icon' => 'fa-arrow-right-arrow-left', 'label' => 'Fluxo', 'url' => '/admin/movimentacoes.php'],
        ['icon' => 'fa-tags', 'label' => 'Categorias', 'url' => '/admin/categorias.php']
    ],
    'RH' => [
        ['icon' => 'fa-clock', 'label' => 'Ponto', 'url' => '/modules/rh/views/ponto.php'],
        ['icon' => 'fa-users-gear', 'label' => 'Time', 'url' => '/modules/rh/views/colaboradores.php'],
        ['icon' => 'fa-money-check-dollar', 'label' => 'Folha', 'url' => '/modules/rh/views/folha.php'],
        ['icon' => 'fa-calendar-day', 'label' => 'Agenda', 'url' => '#']
    ],
    'Financeiro' => [
        ['icon' => 'fa-file-circle-plus', 'label' => 'Receita', 'url' => '/modules/financeiro/views/index.php'],
        ['icon' => 'fa-file-circle-minus', 'label' => 'Despesa', 'url' => '/modules/financeiro/views/movimentacoes.php'],
        ['icon' => 'fa-chart-line', 'label' => 'Fluxo', 'url' => '/modules/financeiro/views/fluxo_caixa.php'],
        ['icon' => 'fa-wallet', 'label' => 'Contas', 'url' => '/modules/financeiro/views/contas_receber.php']
    ],
    'Comercial' => [
        ['icon' => 'fa-rocket', 'label' => 'Novo Deal', 'url' => '/modules/crm/views/kanban.php'],
        ['icon' => 'fa-diagram-project', 'label' => 'Pipeline', 'url' => '/modules/crm/views/kanban.php'],
        ['icon' => 'fa-cash-register', 'label' => 'PDV', 'url' => '/modules/pdv/views/index.php'],
        ['icon' => 'fa-address-book', 'label' => 'Clientes', 'url' => '/admin/clientes.php']
    ],
    'Inteligência' => [
        ['icon' => 'fa-comments', 'label' => 'Chat IA', 'url' => '/admin/agentes_ia.php'],
        ['icon' => 'fa-robot', 'label' => 'Agentes', 'url' => '/admin/agentes_ia.php'],
        ['icon' => 'fa-cube', 'label' => 'Blackbox', 'url' => '/admin/analytics_blackbox.php'],
        ['icon' => 'fa-bolt-lightning', 'label' => 'Config IA', 'url' => '/admin/configuracoes.php']
    ]
];

// Seleciona as ações baseadas no módulo ou usa as padrão
$current_actions = $module_actions[$module_name] ?? $default_actions;
?>

<nav class="brasallis-bottom-nav">
    <?php foreach($current_actions as $index => $act): ?>
    
    <!-- Se for o terceiro item, criamos o botão "HUB" central nativo -->
    <?php if ($index == 2): ?>
    <a href="#" class="bottom-nav-item" data-bs-toggle="offcanvas" data-bs-target="#brasallis360Offcanvas">
        <i class="fas fa-layer-group"></i>
        <span>Hub</span>
    </a>
    <?php endif; ?>

    <a href="<?= $act['url'] ?>" 
       class="bottom-nav-item <?= (strpos($_SERVER['PHP_SELF'], $act['url']) !== false && $act['url'] !== '#') ? 'active' : '' ?>" 
       <?= isset($act['onclick']) ? 'onclick="'.$act['onclick'].'"' : '' ?>
       <?= isset($act['data_target']) ? 'data-bs-toggle="modal" data-bs-target="#'.$act['data_target'].'"' : '' ?>>
        <i class="fas <?= $act['icon'] ?>"></i>
        <span><?= $act['label'] ?></span>
    </a>
    
    <?php endforeach; ?>
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
    <div class="offcanvas-body p-4 pt-1" style="background: rgba(255, 255, 255, 0.5);">
        <div class="d-grid gap-y-4 gap-3" style="grid-template-columns: repeat(4, 1fr);">
            <?php foreach ($pillars as $id => $app): ?>
            <div style="text-align: center;">
                <a href="<?= $app['links'][0]['url'] ?>" class="app-library-icon">
                    <i class="fas <?= $app['icon'] ?> fs-4"></i>
                </a>
                <div class="mt-2 text-dark" style="font-size: 0.60rem; font-weight: 500; letter-spacing: -0.2px;"><?= ucfirst($app['label']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- MAIN CONTENT WRAPPER -->
<main class="brasallis-main">
    <div class="brasallis-content-container p-3 p-lg-4">
