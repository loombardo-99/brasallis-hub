<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/funcoes.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'index.php' && basename($_SERVER['PHP_SELF']) !== 'developers.php' && basename($_SERVER['PHP_SELF']) !== 'esqueceu_senha.php' && basename($_SERVER['PHP_SELF']) !== 'enviar_link_redefinicao.php' && basename($_SERVER['PHP_SELF']) !== 'verificar_codigo.php' && basename($_SERVER['PHP_SELF']) !== 'redefinir_senha.php') {
    header('Location: ../index.php');
    exit();
}

// --- LÓGICA DE VERIFICAÇÃO DE PLANO E TRIAL ---
if (isset($_SESSION['subscription_status']) && $_SESSION['subscription_status'] === 'trialing') {
    $trial_ends_at = new DateTime($_SESSION['trial_ends_at']);
    $now = new DateTime();

    if ($now > $trial_ends_at) {
        // O trial expirou. Muda o plano do usuário na sessão para um plano limitado.
        $_SESSION['user_plan'] = 'trial_expirado';
        $_SESSION['subscription_status'] = 'expired';
        
        // Define uma mensagem persistente de que o trial acabou
        $_SESSION['trial_expired_message'] = 'Seu período de teste gratuito acabou. <a href="/gerenciador_de_estoque/admin/planos.php" class="alert-link">Faça um upgrade</a> para reativar as funcionalidades.';
    }
}

if (isset($_SESSION['user_id'], $_SESSION['empresa_id'])) {
    // Verifica o tipo de usuário (admin ou funcionário)
    $user_type = $_SESSION['user_type'];

    // Busca o número de notificações não lidas para o usuário logado, usando a nova tabela de status
    $unread_notifications = 0;
    $conn = connect_db();
    if ($conn) {
        try {
            $sql = "
                SELECT COUNT(n.id) 
                FROM notificacoes n
                LEFT JOIN notificacao_status_usuario s ON n.id = s.notificacao_id AND s.user_id = :user_id
                WHERE n.empresa_id = :empresa_id 
                  AND COALESCE(s.is_read, 0) = 0 
                  AND COALESCE(s.is_dismissed, 0) = 0
            ";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':empresa_id' => $_SESSION['empresa_id']
            ]);
            $unread_notifications = $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao buscar contagem de notificações: " . $e->getMessage());
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/gerenciador_de_estoque/assets/img/logu.jpeg">
    <title>Gerenciador de Estoque</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/gerenciador_de_estoque/assets/css/style.css">
    <link rel="stylesheet" href="/gerenciador_de_estoque/assets/css/custom-theme.css?v=3.0">
    <link rel="stylesheet" href="/gerenciador_de_estoque/assets/css/admin-theme.css?v=1.7">
    
    <!-- Branding Overrides Removed for System Identity Consistency -->
    <!-- The theme is now fully controlled by admin-theme.css -->
    <style>
        .status-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }
        .status-dot.ok { background-color: #34A853; } /* Verde */
        .status-dot.low { background-color: #FBBC05; } /* Amarelo */
        .status-dot.out { background-color: #EA4335; } /* Vermelho */

        /* Estilos para a busca global */
        .global-search-wrapper {
            position: relative;
            width: 450px;
        }
        #globalSearchResults {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050; /* Para ficar sobre outros elementos */
            max-height: 400px;
            overflow-y: auto;
            display: none; /* Começa escondido */
        }
        .search-result-category {
            font-weight: bold;
            color: var(--bs-primary);
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            background-color: #f8f9fa;
        }

        /* Forçar rolagem no corpo do modal */
        .modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 210px);
            overflow-y: auto;
        }

        /* --- DASHBOARD STYLES --- */
        .card-dashboard {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            background: white;
        }
        .card-dashboard:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .icon-shape {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.25rem;
        }
        .bg-primary-light { background-color: rgba(25, 118, 210, 0.1); color: var(--bs-primary); }
        .bg-success-light { background-color: rgba(52, 168, 83, 0.1); color: #34A853; }
        .bg-warning-light { background-color: rgba(251, 188, 5, 0.1); color: #FBBC05; }
        .bg-danger-light { background-color: rgba(234, 67, 53, 0.1); color: #EA4335; }
        .bg-info-light { background-color: rgba(66, 133, 244, 0.1); color: #4285F4; }

        /* REFINED SIDEBAR (APPLE/GOOGLE STYLE) */
        
        /* Nav Item Base */
        .nav-link-item, .nav-link-collapse {
            padding: 10px 16px;
            border-radius: 10px; /* More rounded */
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            background: transparent;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: left;
            margin-bottom: 2px;
            /* Color and Background handled by admin-theme.css */
        }

        .icon-wrapper {
            width: 24px;
            display: inline-flex;
            justify-content: center;
            margin-right: 12px;
            opacity: 0.8;
            color: inherit;
        }
        
        /* Ajuste fino para ícones chevron */
        .chevron-icon {
            transition: transform 0.3s ease;
        }
        .nav-link-collapse[aria-expanded="true"] .chevron-icon {
            transform: rotate(180deg);
        }
    </style>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sistema de Temas
        // Sistema de Temas (Removido - Identidade Única)


        // Sistema de Sidebar Collapse e Mobile Drawer
        // Sistema de Sidebar Collapse e Mobile Drawer
        // Definido globalmente para acesso via onclick no HTML
        window.toggleSidebar = function() {
            const body = document.body;
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth < 992) {
                body.classList.toggle('sidebar-open');
                if (body.classList.contains('sidebar-open')) {
                    if(overlay) overlay.classList.add('show');
                } else {
                    if(overlay) overlay.classList.remove('show');
                }
            } else {
                const isCollapsed = body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar_state', isCollapsed ? 'collapsed' : 'expanded');
                if (!isCollapsed) {
                    body.classList.remove('sidebar-hover-show');
                }
            }
        };

        // Inicialização Segura
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            const sidebar = document.getElementById('sidebarMenu');
            const savedSidebar = localStorage.getItem('sidebar_state') || 'expanded';

            // 1. Restaurar estado (apenas desktop)
            if (window.innerWidth >= 992) {
                if (savedSidebar === 'collapsed') {
                    body.classList.add('sidebar-collapsed');
                } else {
                    body.classList.remove('sidebar-collapsed');
                }
            }

            // 2. Listeners de Hover (apenas desktop)
            if (sidebar) {
                sidebar.addEventListener('mouseenter', () => {
                    if (window.innerWidth >= 992 && body.classList.contains('sidebar-collapsed')) {
                        body.classList.add('sidebar-hover-show');
                    }
                });

                sidebar.addEventListener('mouseleave', () => {
                    if (window.innerWidth >= 992) {
                        body.classList.remove('sidebar-hover-show');
                    }
                });
            }
            
            // 3. Garantir que o overlay fecha a sidebar no mobile
            const overlay = document.getElementById('sidebar-overlay');
            if(overlay) {
                overlay.addEventListener('click', function() {
                     body.classList.remove('sidebar-open');
                     overlay.classList.remove('show');
                });
            }
        });
    </script>
</head>
<body class="d-flex flex-column min-vh-100">

<?php if (isset($_SESSION['user_id'])): ?>
<header>
  <!-- Sidebar Overlay (Mobile Backdrop) -->
  <div id="sidebar-overlay" onclick="toggleSidebar()"></div>

  <!-- Sidebar -->
  <nav id="sidebarMenu" class="sidebar">
    <div class="sidebar-header d-flex align-items-center px-3 py-3 mb-2">
      <a class="d-flex align-items-center gap-2 text-decoration-none" href="/gerenciador_de_estoque/admin/painel_admin.php" style="font-family: 'Outfit', sans-serif; font-weight: 700; letter-spacing: -0.5px; font-size: 1.25rem; color: var(--text-sidebar);">
          <img src="/gerenciador_de_estoque/assets/img/logu.jpeg" alt="Logo" width="35" height="35" class="rounded-circle shadow-sm">
          <span class="text-wrapper brand-text">WiseFlow</span>
      </a>
    </div>

    <!-- Scrollable Content Area -->
    <div class="sidebar-content">
      <div class="list-group list-group-flush mx-2 sidebar-nav">
        <?php if ($user_type === 'admin'): ?>
            
            <!-- SEÇÃO: VISÃO GERAL -->
            <div class="sidebar-heading">Início</div>
            <a href="/gerenciador_de_estoque/admin/painel_admin.php" class="nav-link-item d-flex align-items-center <?php echo basename($_SERVER['PHP_SELF']) === 'painel_admin.php' ? 'active' : ''; ?>">
              <span class="icon-wrapper"><i class="fas fa-chart-pie"></i></span>
              <span class="text-wrapper">Painel de Controle</span>
            </a>
            
            <a href="/gerenciador_de_estoque/admin/notificacoes.php" class="nav-link-item d-flex align-items-center <?php echo basename($_SERVER['PHP_SELF']) === 'notificacoes.php' ? 'active' : ''; ?>">
               <span class="icon-wrapper"><i class="fas fa-bell"></i></span>
               <span class="text-wrapper d-flex justify-content-between w-100 align-items-center">
                   Notificações
                   <?php if ($unread_notifications > 0): ?>
                       <span class="badge bg-danger rounded-pill" style="font-size: 0.6rem;"><?php echo $unread_notifications; ?></span>
                   <?php endif; ?>
               </span>
            </a>

            <!-- SEÇÃO: INTELIGÊNCIA (IA) -->
            <div class="sidebar-heading mt-3">Inteligência</div>
            <a href="/gerenciador_de_estoque/admin/agentes_ia.php" class="nav-link-item d-flex align-items-center <?php echo basename($_SERVER['PHP_SELF']) === 'agentes_ia.php' ? 'active' : ''; ?>">
              <span class="icon-wrapper"><i class="fas fa-robot"></i></span>
              <span class="text-wrapper">Agentes de IA</span>
            </a>
            <a href="/gerenciador_de_estoque/admin/relatorios.php" class="nav-link-item d-flex align-items-center <?php echo basename($_SERVER['PHP_SELF']) === 'relatorios.php' ? 'active' : ''; ?>">
              <span class="icon-wrapper"><i class="fas fa-lightbulb"></i></span>
              <span class="text-wrapper">Insights</span>
            </a>

            <!-- ACCORDION SECTIONS -->
            <div class="sidebar-heading mt-3">Gestão</div>
            
            <?php 
                // Determine Active Section
                $page = basename($_SERVER['PHP_SELF']);
                $ops_pages = ['produtos.php', 'categorias.php', 'compras.php', 'fornecedores.php', 'movimentacoes.php'];
                $corp_pages = ['organizacao.php', 'inteligencia_tributaria.php', 'usuarios.php'];
                
                $is_ops_active = in_array($page, $ops_pages);
                $is_corp_active = in_array($page, $corp_pages) || strpos($_SERVER['PHP_SELF'], '/modules/rh/') !== false;
            ?>

            <!-- Operacional Folder -->
            <div class="nav-group">
                <button class="nav-link-collapse w-100 d-flex align-items-center justify-content-between <?php echo $is_ops_active ? '' : 'collapsed'; ?>" 
                        onclick="toggleAccordion('menuOperacional')"
                        aria-expanded="<?php echo $is_ops_active ? 'true' : 'false'; ?>">
                    <div class="d-flex align-items-center">
                        <span class="icon-wrapper"><i class="fas fa-boxes-stacked"></i></span>
                        <span class="text-wrapper">Operacional</span>
                    </div>
                    <i class="fas fa-chevron-down chevron-icon" style="font-size: 0.7rem;"></i>
                </button>
                <div class="collapse <?php echo $is_ops_active || strpos($_SERVER['PHP_SELF'], '/modules/pdv/') !== false ? 'show' : ''; ?>" id="menuOperacional" data-bs-parent=".sidebar-nav">
                    <div class="nav-sub-items ps-3">
                        <?php if (check_permission('pdv', 'leitura')): ?>
                        <a href="/gerenciador_de_estoque/modules/pdv/views/index.php" class="nav-sub-link py-1 fw-bold text-success <?php echo strpos($_SERVER['PHP_SELF'], '/modules/pdv/') !== false ? 'active' : ''; ?>"><i class="fas fa-cash-register me-2"></i>PDV / Caixa</a>
                        <?php endif; ?>
                        
                        <?php if (check_permission('estoque', 'leitura')): ?>
                        <a href="/gerenciador_de_estoque/admin/produtos.php" class="nav-sub-link py-1 <?php echo basename($_SERVER['PHP_SELF']) === 'produtos.php' ? 'active' : ''; ?>">Produtos</a>
                        <a href="/gerenciador_de_estoque/admin/categorias.php" class="nav-sub-link py-1 <?php echo basename($_SERVER['PHP_SELF']) === 'categorias.php' ? 'active' : ''; ?>">Categorias</a>
                        <?php endif; ?>

                        <?php if (check_permission('compras', 'leitura')): ?>
                        <a href="/gerenciador_de_estoque/admin/compras.php" class="nav-sub-link py-1 <?php echo basename($_SERVER['PHP_SELF']) === 'compras.php' ? 'active' : ''; ?>">Compras</a>
                        <a href="/gerenciador_de_estoque/admin/fornecedores.php" class="nav-sub-link py-1 <?php echo basename($_SERVER['PHP_SELF']) === 'fornecedores.php' ? 'active' : ''; ?>">Fornecedores</a>
                        <?php endif; ?>

                        <?php if (check_permission('estoque', 'leitura')): ?>
                        <a href="/gerenciador_de_estoque/admin/movimentacoes.php" class="nav-sub-link py-1 <?php echo basename($_SERVER['PHP_SELF']) === 'movimentacoes.php' ? 'active' : ''; ?>">Movimentações</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Corporativo Folder -->
            <div class="nav-group">
                <button class="nav-link-collapse w-100 d-flex align-items-center justify-content-between <?php echo $is_corp_active ? '' : 'collapsed'; ?>" 
                        onclick="toggleAccordion('menuCorporativo')"
                        aria-expanded="<?php echo $is_corp_active ? 'true' : 'false'; ?>">
                    <div class="d-flex align-items-center">
                        <span class="icon-wrapper"><i class="fas fa-building-user"></i></span>
                        <span class="text-wrapper">Corporativo</span>
                    </div>
                    <i class="fas fa-chevron-down chevron-icon" style="font-size: 0.7rem;"></i>
                </button>
                <div class="collapse <?php echo $is_corp_active ? 'show' : ''; ?>" id="menuCorporativo" data-bs-parent=".sidebar-nav">
                    <div class="nav-sub-items ps-3">
                        <?php if (check_permission('configuracoes', 'leitura')): ?>
                        <a href="/gerenciador_de_estoque/admin/organizacao.php" class="nav-sub-link py-1 <?php echo basename($_SERVER['PHP_SELF']) === 'organizacao.php' ? 'active' : ''; ?>">Empresa</a>
                        <?php endif; ?>

                        <?php if (check_permission('rh', 'leitura')): // RH module might not be in DB yet, but keep logic ?>
                        <a href="/gerenciador_de_estoque/modules/rh/views/index.php" class="nav-sub-link py-1 <?php echo strpos($_SERVER['PHP_SELF'], '/modules/rh/') !== false ? 'active' : ''; ?>">RH & Pessoal</a>
                        <?php endif; ?>

                        <?php if (check_permission('fiscal', 'leitura')): ?>
                        <a href="/gerenciador_de_estoque/admin/inteligencia_tributaria.php" class="nav-sub-link py-1 <?php echo basename($_SERVER['PHP_SELF']) === 'inteligencia_tributaria.php' ? 'active' : ''; ?>">Fiscal</a>
                        <?php endif; ?>

                        <?php if (check_permission('configuracoes', 'admin')): // Only admins or HR admins should see users ?>
                        <a href="/gerenciador_de_estoque/admin/usuarios.php" class="nav-sub-link py-1 <?php echo basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'active' : ''; ?>">Equipe</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Employee Content (Simplified) -->
             <div class="sidebar-heading">Acesso Rápido</div>
             <?php if (!empty($_SESSION['setor_id'])): ?>
            <a href="/gerenciador_de_estoque/admin/setor_dashboard.php?id=<?= $_SESSION['setor_id'] ?>" class="nav-link-item d-flex align-items-center">
               <span class="icon-wrapper"><i class="fas fa-home-user"></i></span>
               <span class="text-wrapper">Meu Painel</span>
            </a>
            <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Fixed Footer (Settings & Profile) -->
    <div class="sidebar-footer border-top border-white-10 p-2">
        <a href="/gerenciador_de_estoque/admin/configuracoes.php" class="nav-link-item d-flex align-items-center <?php echo basename($_SERVER['PHP_SELF']) === 'configuracoes.php' ? 'active' : ''; ?>">
            <span class="icon-wrapper"><i class="fas fa-sliders"></i></span>
            <span class="text-wrapper">Configurações</span>
        </a>
        <a href="/gerenciador_de_estoque/admin/suporte.php" class="nav-link-item d-flex align-items-center <?php echo basename($_SERVER['PHP_SELF']) === 'suporte.php' ? 'active' : ''; ?>">
            <span class="icon-wrapper"><i class="fas fa-headset"></i></span>
            <span class="text-wrapper">Suporte</span>
        </a>
    </div>

  </nav>

  <!-- Sidebar Toggle (Tab Style) - Desktop Only -->
  <button id="sidebarToggle" class="sidebar-toggle-tab d-none d-lg-flex" onclick="toggleSidebar()" title="Expandir/Recolher">
      <i class="fas fa-chevron-left"></i>
  </button>

  <script>
    // Exclusive Accordion Logic
    function toggleAccordion(targetId) {
        const allCollapses = document.querySelectorAll('.sidebar .collapse');
        const target = document.getElementById(targetId);
        
        // Close all others
        allCollapses.forEach(el => {
            if (el.id !== targetId && el.classList.contains('show')) {
                // Use Bootstrap collapse API if available, or manual class toggle
                 new bootstrap.Collapse(el, { toggle: false }).hide();
            }
        });
        
        // Toggle target
        const bsCollapse = new bootstrap.Collapse(target, { toggle: true });
    }
  </script>
  <!-- Navbar -->
  <nav id="main-navbar" class="navbar navbar-expand-lg glass-navbar fixed-top">
    <!-- Container wrapper -->
    <div class="container-fluid">
      <!-- Toggle button -->
      <button
        class="navbar-toggler"
        type="button"
        onclick="window.toggleSidebar()"
        aria-label="Toggle navigation"
      >
        <i class="fas fa-bars"></i>
      </button>

      <!-- Brand (MOBILE ONLY - Visible when sidebar is collapsed/hidden) -->
      <a class="navbar-brand d-flex d-lg-none align-items-center gap-2 me-auto" href="/gerenciador_de_estoque/admin/painel_admin.php">
          <img src="/gerenciador_de_estoque/assets/img/logu.jpeg" alt="Logo" width="32" height="32" class="rounded-circle shadow-sm">
          <!-- Texto oculto em telas muito pequenas, visível em tablets smart -->
          <span class="brand-text d-none d-sm-block" style="font-family: 'Outfit', sans-serif; font-weight: 700; letter-spacing: -0.5px; font-size: 1.2rem; color: var(--text-main);">WiseFlow</span>
      </a>

      <!-- Global Search (Desktop) -->
      <div class="global-search-wrapper mx-auto d-none d-md-block">
          <div class="input-group">
              <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
              <input type="text" class="form-control bg-transparent border-0" id="globalSearchInput" placeholder="O que você procura hoje?" autocomplete="off" style="font-size: 0.95rem;">
          </div>
          <ul class="list-group shadow-lg" id="globalSearchResults"></ul>
      </div>

      <!-- Right links -->
      <ul class="navbar-nav ms-auto d-flex flex-row align-items-center gap-2">
        
        <!-- Mobile Search Trigger -->
        <li class="nav-item d-md-none">
            <a class="nav-link p-2" href="#" id="mobile-search-trigger">
                <i class="fas fa-search fa-lg"></i>
            </a>
        </li>
        
        <!-- (Theme Switcher Removed - System Identity Enforced) -->

        <!-- Notifications -->
        <li class="nav-item">
          <a class="nav-link position-relative" href="/gerenciador_de_estoque/admin/notificacoes.php">
            <i class="fas fa-bell fa-lg text-secondary"></i>
            <?php if ($unread_notifications > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light p-1">
                <?php echo $unread_notifications; ?>
                <span class="visually-hidden">unread messages</span>
              </span>
            <?php endif; ?>
          </a>
        </li>

        <!-- User -->
        <li class="nav-item dropdown">
          <a
            class="nav-link dropdown-toggle d-flex align-items-center gap-2"
            href="#"
            id="navbarDropdownMenuLink"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <div class="rounded-circle bg-gray-200 d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 35px; height: 35px; background: #e9ecef;">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="navbarDropdownMenuLink">
            <li><span class="dropdown-header text-uppercase small fw-bold">Conta</span></li>
            <li><a class="dropdown-item" href="/gerenciador_de_estoque/editar_perfil.php"><i class="fas fa-user-circle me-2"></i>Editar Perfil</a></li>
            <li><hr class="dropdown-divider opacity-10"></li>
            <li>
              <a class="dropdown-item text-danger" href="/gerenciador_de_estoque/sair.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
    <!-- Container wrapper -->
  </nav>

  <!-- Mobile Search Overlay -->
  <div id="mobile-search-overlay" class="d-none">
      <div class="container-fluid h-100 d-flex align-items-center">
          <div class="input-group input-group-lg">
              <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-primary"></i></span>
              <input type="text" class="form-control bg-transparent border-0 shadow-none" id="mobileSearchInput" placeholder="Buscar no sistema..." autocomplete="off">
              <button class="btn btn-link text-muted" type="button" id="close-mobile-search"><i class="fas fa-times"></i></button>
          </div>
      </div>
      <ul class="list-group shadow-lg position-absolute w-100 start-0 top-100" id="mobileSearchResults"></ul>
  </div>
</header>
<main>
  <div class="container-fluid">
  <?php if (isset($_SESSION['trial_expired_message'])): ?>
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo $_SESSION['trial_expired_message']; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>