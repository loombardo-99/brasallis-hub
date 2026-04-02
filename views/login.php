<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sessão Administrativa - Brasallis Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            /* The Corporate Brasallis Hub Blue Palette */
            --sys-navy: #001E3C;
            --sys-navy-light: #0A2647;
            --sys-blue-accent: #0070F2;
            --sys-surface: #FFFFFF;
            --sys-text-muted: #64748B;
        }

        /* Essential: Zero Scroll Architecture */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden !important; /* Programmatic Scroll Lock */
            font-family: 'Inter', sans-serif;
            background-color: var(--sys-navy); /* Fallback to navy for seamless mobile */
        }

        h1, h2, h3, .brand-text {
            font-family: 'Outfit', sans-serif;
        }

        .split-screen {
            height: 100vh;
            display: flex;
        }

        /* --- THE ENTERPRISE RAIL (LEFT/WHITE) --- Hidden on Mobile */
        .left-pane {
            background-color: #ffffff;
            color: var(--sys-navy);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 5rem 8%;
            border-right: 1px solid #E2E8F0;
            width: 42%;
            flex-shrink: 0;
        }

        .brand-header {
            margin-bottom: 3.5rem;
        }

        .big-logo {
            max-width: 260px;
            height: auto;
            display: block;
        }

        .comm-title {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1.2;
            color: var(--sys-navy);
            margin-bottom: 1.5rem;
        }

        .comm-features {
            margin-bottom: 3rem;
        }

        .feature-line {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 0.85rem;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--sys-text-muted);
        }

        .feature-line i {
            color: var(--sys-blue-accent);
            font-size: 0.8rem;
        }

        /* --- THE OPERATION TERMINAL (RIGHT/BLUE) --- */
        .right-pane {
            background-color: var(--sys-navy);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            flex-grow: 1;
            box-shadow: inset 20px 0 30px rgba(0,0,0,0.05);
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
        }

        /* Mobile Logo */
        .mobile-brand-logo {
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .auth-container h2 {
            color: #ffffff;
            font-size: 1.65rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
        }

        .auth-container p {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.9rem;
            margin-bottom: 2.5rem; /* Tighter for mobile fit */
        }

        /* --- BRASALLIS ENTERPRISE INPUTS --- */
        .form-group-suite {
            margin-bottom: 1.75rem;
        }

        .form-label-suite {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 0.7rem;
            display: block;
        }

        .input-suite {
            background: rgba(255, 255, 255, 0.04) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 12px;
            padding: 15px 18px;
            color: #ffffff !important;
            font-size: 1rem;
            transition: all 0.25s ease;
            width: 100%;
            outline: none;
        }

        .input-suite::placeholder {
            color: rgba(255, 255, 255, 0.15);
        }

        .input-suite:focus {
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: var(--sys-blue-accent) !important;
            box-shadow: 0 0 0 1px var(--sys-blue-accent);
        }

        .btn-suite {
            background: var(--sys-blue-accent);
            color: #ffffff;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 0.5rem;
            box-shadow: 0 4px 15px rgba(0, 112, 242, 0.3);
        }

        .btn-suite:hover {
            background: #005DC9;
            transform: translateY(-2px);
        }

        .auth-footer {
            margin-top: 2.5rem;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 1.5rem;
        }

        .auth-footer a {
            color: rgba(255, 255, 255, 0.3);
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            transition: color 0.2s;
        }

        .auth-footer a:hover {
            color: #ffffff;
        }

        /* --- MOBILE LEAN (THE ZERO-SCROLL) --- */
        @media (max-width: 991px) {
            .left-pane { 
                display: none !important; /* Full Shell on Mobile */
            }
            .right-pane {
                width: 100%;
                height: 100vh;
                padding: 1.5rem;
            }
            .auth-container {
                max-width: 100%;
            }
            .auth-container p { margin-bottom: 2rem; }
            .form-group-suite { margin-bottom: 1.25rem; }
            .auth-footer { margin-top: 2rem; padding-top: 1rem; border: none; }
        }

        @media (max-height: 700px) {
            /* Compact for small height devices */
            .mobile-brand-logo { margin-bottom: 1.5rem; }
            .auth-container h2 { font-size: 1.4rem; }
            .auth-container p { margin-bottom: 1.5rem; }
            .form-group-suite { margin-bottom: 1rem; }
        }
    </style>
</head>
<body>

    <div class="split-screen g-0">
        <!-- ENTERPRISE RAIL (LEFT/WHITE) -->
        <div class="left-pane">
            <div class="brand-header">
                <img src="/assets/img/pureza.png" alt="Brasallis Hub" class="big-logo">
            </div>
            
            <h1 class="comm-title">Gestão Corporativa de<br>Alta Performance.</h1>
            
            <div class="comm-features">
                <div class="feature-line"><i class="fas fa-check-circle"></i> Governança de Dados Centralizada</div>
                <div class="feature-line"><i class="fas fa-check-circle"></i> Analytics em Tempo Real</div>
                <div class="feature-line"><i class="fas fa-check-circle"></i> Automação Fiscal Inteligente</div>
            </div>

            <div class="mt-auto pt-5 opacity-40">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-server small"></i>
                    <small class="fw-bold text-uppercase">System Status: Operational</small>
                </div>
            </div>
        </div>

        <!-- OPERATION TERMINAL (RIGHT/BLUE) -->
        <div class="right-pane">
            <div class="auth-container">
                <!-- Mobile Only Branding -->
                <div class="mobile-brand-logo d-lg-none">
                    <img src="/assets/img/pureza.png" alt="Brasallis" height="40">
                </div>

                <h2>Acesso ao Shell</h2>
                <p>Insira suas credenciais para autenticação.</p>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger border-0 bg-danger bg-opacity-20 text-white rounded-3 p-3 mb-4 d-flex align-items-center">
                        <i class="fas fa-shield-exclamation me-3 fs-5"></i>
                        <span class="small fw-bold"><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <form action="/login.php" method="POST">
                    <div class="form-group-suite">
                        <label for="email" class="form-label-suite">E-mail</label>
                        <input type="email" class="input-suite" id="email" name="email" placeholder="usuario@dominio.com" required autocomplete="username">
                    </div>
                    
                    <div class="form-group-suite">
                        <div class="d-flex justify-content-between">
                            <label for="password" class="form-label-suite">Senha</label>
                            <a href="esqueceu_senha.php" class="text-decoration-none" style="color: rgba(255,255,255,0.3); font-size: 0.7rem;">Recuperar?</a>
                        </div>
                        <input type="password" class="input-suite" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn-suite">
                        Iniciar Sessão Segura
                    </button>
                </form>

                <div class="auth-footer">
                    <div class="d-flex justify-content-center gap-4">
                        <a href="register.php">Solicitar Empresa</a>
                        <a href="/" class="opacity-50">Portal Público</a>
                    </div>
                    <p class="mt-3 small opacity-20" style="color: #ffffff; font-size: 0.65rem;">&copy; <?php echo date('Y'); ?> Brasallis Enterprise Hub</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
