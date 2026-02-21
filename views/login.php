<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WiseFlow</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            /* Modern Trust Palette */
            --sys-navy: #0A2647;
            --sys-navy-light: #143C6D;
            --sys-emerald: #2C7865;
            --sys-emerald-light: #399D85;
            --sys-surface: #F8FAFC;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--sys-surface);
            overflow-x: hidden;
        }
        h1, h2, h3, .brand-text {
            font-family: 'Outfit', sans-serif;
        }
        .split-screen {
            min-height: 100vh;
            display: flex;
        }
        .left-pane {
            /* Navy Gradient (Modern) instead of Generic Blue */
            background: linear-gradient(135deg, var(--sys-navy) 0%, #061830 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            position: relative;
            overflow: hidden;
            /* No Circles */
        }
        .right-pane {
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
        }
        .form-control {
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            background-color: #F8FAFC;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: var(--sys-navy);
            box-shadow: 0 0 0 3px rgba(10, 38, 71, 0.1);
            background-color: white;
        }
        .input-group-text {
            border-radius: 12px 0 0 12px;
            border: 1px solid #E2E8F0;
            background-color: #F8FAFC;
            color: #64748B;
        }
        .form-control.border-start-0 {
            border-left: 0;
            border-radius: 0 12px 12px 0;
        }
        
        /* Modern Button */
        .btn-primary {
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
            background-color: var(--sys-navy);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: var(--sys-emerald);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 120, 101, 0.2);
        }
        
        .brand-logo {
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 2rem;
        }

        @media (max-width: 992px) {
            .left-pane { display: none; }
        }
    </style>
</head>
<body>

    <div class="split-screen row g-0">
        <!-- Left Pane: Marketing (Original Structure) -->
        <div class="col-lg-6 left-pane">
            <!-- No Circles here -->
            
            <div class="brand-logo mb-5">
                <!-- Using logo img to keep it consistent, but structure is simple -->
                <span class="text-white brand-text" style="font-size: 2rem;">WiseFlow</span>
            </div>
            
            <h1 class="display-4 fw-bold mb-4">Bem-vindo de volta.</h1>
            <p class="lead mb-4 opacity-75">Acesse seu painel e continue transformando dados em decisões inteligentes.</p>
            
            <div class="d-flex gap-3 mt-auto">
                <small class="opacity-50">&copy; <?php echo date('Y'); ?> WiseFlow</small>
            </div>
        </div>

        <!-- Right Pane: Login Form -->
        <div class="col-lg-6 right-pane">
            <div class="login-box">
                <div class="text-center mb-5 d-lg-none">
                    <h3 class="brand-text fw-bold" style="color: var(--sys-navy); font-size: 2rem;">WiseFlow</h3>
                </div>

                <h2 class="fw-bold mb-2" style="color: var(--sys-navy);">Login</h2>
                <p class="text-secondary mb-4">Insira seus dados para acessar.</p>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger rounded-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold small text-uppercase text-secondary" style="font-size: 0.75rem;">E-mail</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control border-start-0 ps-2" id="email" name="email" placeholder="seu@email.com" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <label for="password" class="form-label fw-bold small text-uppercase text-secondary" style="font-size: 0.75rem;">Senha</label>
                            <a href="esqueceu_senha.php" class="small text-decoration-none fw-bold" style="color: var(--sys-emerald);">Esqueceu?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control border-start-0 ps-2" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg">Entrar no Sistema</button>
                    </div>
                </form>

                <div class="text-center mt-5">
                    <p class="text-secondary small mb-3">Ainda não tem uma conta?</p>
                    <a href="register.php" class="btn btn-outline-secondary w-100 rounded-3 fw-bold" style="border: 2px solid #E2E8F0; color: var(--sys-navy);">
                        Criar Conta
                    </a>
                    <div class="mt-4">
                        <a href="index.php" class="text-secondary small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Voltar para Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
