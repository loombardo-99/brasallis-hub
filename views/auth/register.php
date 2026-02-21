<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crie sua Conta - WiseFlow</title>
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
            /* Navy Gradient (Modern) */
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
        .register-box {
            width: 100%;
            max-width: 480px;
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

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 1.05rem;
            color: rgba(255,255,255,0.9);
        }
        .benefit-icon {
            width: 32px; height: 32px;
            background: rgba(44, 120, 101, 0.2);
            border: 1px solid rgba(44, 120, 101, 0.4);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #4ADE80;
        }

        @media (max-width: 992px) {
            .left-pane { display: none; }
        }
    </style>
</head>
<body>

    <div class="split-screen row g-0">
        <!-- Left Pane: Value Prop (Original Structure) -->
        <div class="col-lg-5 left-pane">
            
            <div class="brand-logo mb-5">
                <img src="assets/img/logu.jpeg" alt="Logo" width="48" height="48" class="rounded-circle shadow-sm">
                <span class="text-white">WiseFlow</span>
            </div>
            
            <h1 class="display-5 fw-bold mb-4">Comece a organizar sua empresa hoje.</h1>
            <p class="lead mb-5 text-white-50">Junte-se a milhares de gestores que transformaram o caos em controle.</p>
            
            <div class="benefits">
                <div class="benefit-item">
                    <div class="benefit-icon"><i class="fas fa-check"></i></div>
                    <span>Controle de estoque em tempo real</span>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon"><i class="fas fa-check"></i></div>
                    <span>Leitura de notas fiscais com IA</span>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon"><i class="fas fa-check"></i></div>
                    <span>Relatórios financeiros detalhados</span>
                </div>
            </div>

            <!-- Social Proof (Was in original) -->
            <div class="mt-5">
                <p class="small opacity-75 text-uppercase fw-bold mb-3">Empresas que confiam na gente</p>
                <div class="d-flex gap-4 opacity-50">
                    <i class="fas fa-building fa-2x"></i>
                    <i class="fas fa-store fa-2x"></i>
                    <i class="fas fa-industry fa-2x"></i>
                    <i class="fas fa-utensils fa-2x"></i>
                </div>
                <p class="small mt-3 opacity-75">Mais de 500+ empresas ativas.</p>
            </div>

            <div class="mt-auto pt-5">
                <small class="opacity-50">Plano selecionado: <strong class="text-white text-uppercase"><?php echo htmlspecialchars($plano_selecionado); ?></strong></small>
            </div>
        </div>

        <!-- Right Pane: Register Form -->
        <div class="col-lg-7 right-pane">
            <div class="register-box">
                <div class="text-center mb-5 d-lg-none">
                     <img src="assets/img/logu.jpeg" alt="Logo" width="60" class="rounded-circle mb-3 shadow-sm">
                     <h3 class="fw-bold" style="color: var(--sys-navy);">WiseFlow</h3>
                </div>

                <div class="mb-4">
                    <h2 class="fw-bold mb-2" style="color: var(--sys-navy);">Crie sua conta</h2>
                    <p class="text-secondary">Preencha os dados abaixo para começar.</p>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger rounded-3 d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <div><?php echo htmlspecialchars($error_message); ?></div>
                    </div>
                <?php endif; ?>

                <form action="registrar_action.php" method="POST">
                    <input type="hidden" name="plan" value="<?php echo htmlspecialchars($plano_selecionado); ?>">

                    <div class="mb-3">
                        <label for="company_name" class="form-label fw-bold small text-uppercase text-secondary" style="font-size: 0.75rem;">Nome da Empresa</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($form_data['company_name'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold small text-uppercase text-secondary" style="font-size: 0.75rem;">Seu Nome Completo</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold small text-uppercase text-secondary" style="font-size: 0.75rem;">Seu Melhor E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="password" class="form-label fw-bold small text-uppercase text-secondary" style="font-size: 0.75rem;">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label fw-bold small text-uppercase text-secondary" style="font-size: 0.75rem;">Confirmar Senha</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            Criar Conta Gratuita <i class="fas fa-arrow-right ms-2" style="font-size: 0.8em;"></i>
                        </button>
                        
                        <!-- Trust Badges (Kept from modern version as they are non-intrusive and good for conversion) -->
                        <div class="d-flex justify-content-center gap-3 text-secondary small opacity-75 mt-3">
                            <div class="d-flex align-items-center gap-1"><i class="fas fa-lock text-success"></i> SSL Seguro</div>
                            <div class="d-flex align-items-center gap-1"><i class="fas fa-shield-alt text-primary"></i> Dados Criptografados</div>
                        </div>
                    </div>
                </form>

                <div class="text-center pt-4 border-top">
                    <p class="text-secondary small mb-2">Já tem uma conta?</p>
                    <a href="login.php" class="fw-bold text-decoration-none" style="color: var(--sys-navy);">Fazer Login</a>
                    <div class="mt-3">
                        <a href="index.php" class="text-secondary small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Voltar para Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
