<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>God Mode | Gestor Inteligente</title>
    <link rel="icon" type="image/png" href="/assets/img/pureza.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #4f46e5;
            --dark-bg: #0f172a;
            --card-glass: rgba(255, 255, 255, 0.9);
            --border-glass: rgba(255, 255, 255, 0.6);
        }
        
        body { 
            background-color: #f1f5f9; 
            font-family: 'Outfit', sans-serif; 
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar { 
            width: var(--sidebar-width); 
            height: 100vh; 
            position: fixed; 
            background: var(--dark-bg); 
            color: #94a3b8; 
            padding: 24px; 
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255,255,255,0.05);
            z-index: 1000;
        }

        .brand-box {
            color: white;
            font-weight: 800;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding: 0 12px;
        }

        .nav-link { 
            color: #94a3b8; 
            padding: 14px 16px; 
            border-radius: 12px; 
            margin-bottom: 8px; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active { 
            background: rgba(79, 70, 229, 0.1); 
            color: #818cf8; 
        }

        .nav-link.active {
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.2) 0%, rgba(79, 70, 229, 0) 100%);
            border-left: 3px solid #6366f1;
        }

        /* Main Content */
        .main-content { 
            margin-left: var(--sidebar-width); 
            padding: 50px; 
        }

        /* Glass Cards */
        .stat-card {
            background: var(--card-glass);
            backdrop-filter: blur(20px);
            border: 1px solid white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s;
            height: 100%;
        }
        .stat-card:hover { transform: translateY(-4px); }

        .stat-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 16px;
        }

        .big-number { font-size: 2rem; font-weight: 800; color: #1e293b; letter-spacing: -1px; }

        /* General Tables */
        .premium-table {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .premium-table th {
            background: #f8fafc;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
        }
        .premium-table td { padding: 20px 24px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .premium-table tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge-premium { padding: 6px 12px; border-radius: 30px; font-weight: 600; font-size: 0.75rem; }
        .bg-free { background: #f1f5f9; color: #64748b; }
        .page-header { margin-bottom: 40px; display: flex; justify-content: flex-start; align-items: center; gap: 20px; }
        .page-title { font-weight: 800; color: #1e293b; margin: 0; }
        .page-subtitle { color: #64748b; margin-top: 5px; }

        .mobile-top-bar { display: none; }
        .sidebar-toggle { display: none; }
        .overlay { 
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100vh; 
            background: rgba(15, 23, 42, 0.6); 
            z-index: 1040; /* High z-index */
            backdrop-filter: blur(4px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .overlay.active { display: block; opacity: 1; }

        /* Mobile Responsive */
        @media (max-width: 991px) {
            .sidebar { 
                transform: translateX(-100%); 
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: none;
                z-index: 1050; /* Highest z-index */
            }
            .sidebar.active { 
                transform: translateX(0); 
                box-shadow: 10px 0 30px rgba(0,0,0,0.5);
            }
            
            .main-content { 
                margin-left: 0; 
                padding: 20px; 
                padding-top: 90px; /* Space for mobile header */
            }

            /* Premium Mobile Top Bar */
            .mobile-top-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 70px;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                border-bottom: 1px solid rgba(0,0,0,0.05);
                padding: 0 24px;
                z-index: 1030; /* Below overlay */
                box-shadow: 0 4px 20px -5px rgba(0,0,0,0.05);
            }

            .mobile-logo {
                font-weight: 800;
                font-size: 1.2rem;
                color: #1e293b;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                border: none;
                background: transparent;
                color: #1e293b;
                font-size: 1.5rem;
                padding: 5px;
                cursor: pointer;
            }

            /* Adjust Cards for Mobile */
            .stat-card { padding: 20px; }
            .stat-card .d-flex { flex-direction: column; }
            .stat-card .stat-icon-box { margin-bottom: 15px; margin-top: 5px; width: 40px; height: 40px; font-size: 1rem; }
            .big-number { font-size: 1.75rem; }
            
            .d-flex.justify-content-between.align-items-center.mb-5 {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 15px;
                margin-bottom: 30px !important;
            }
            .d-flex.justify-content-between.align-items-center.mb-5 button {
                width: 100%;
            }
        }

    </style>
</head>
<body>

    <!-- Overlay for Mobile -->
    <div class="overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Mobile Top Bar -->
    <div class="mobile-top-bar">
        <div class="mobile-logo">
            <i class="fas fa-layer-group text-primary"></i> Super Admin
        </div>
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="mainSidebar">
        <!-- Brand Box (Desktop only) -->
        <div class="brand-box d-none d-lg-flex">
            <i class="fas fa-layer-group text-primary"></i> Super Admin
        </div>
        <!-- Mobile Header inside sidebar to close -->
        <div class="d-flex d-lg-none justify-content-between align-items-center mb-4 text-white">
            <span class="fw-bold fs-5">Menu</span>
            <button class="btn btn-sm btn-outline-light border-0" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="flex-grow-1">
            <?php 
                $active_page = basename($_SERVER['PHP_SELF']); 
            ?>
            <a href="index.php" class="nav-link <?php echo $active_page == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="empresas.php" class="nav-link <?php echo $active_page == 'empresas.php' ? 'active' : ''; ?>"><i class="fas fa-building"></i> Empresas</a>
            <a href="avisos.php" class="nav-link <?php echo $active_page == 'avisos.php' ? 'active' : ''; ?>"><i class="fas fa-bell"></i> Avisos Globais</a>
            <a href="suporte.php" class="nav-link <?php echo $active_page == 'suporte.php' ? 'active' : ''; ?>"><i class="fas fa-headset"></i> Suporte</a>
        </nav>
        <div class="mt-auto">
            <a href="../sair.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </div>

    <!-- Script Inline para garantir funcionamento -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('mainSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    </script>

    <!-- Main Content Wrapper -->
    <div class="main-content">
