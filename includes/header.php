<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'WiseFlow'; ?></title>
    <link rel="icon" type="image/jpeg" href="/gerenciador_de_estoque/assets/img/logu.jpeg">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Font: Roboto (Google Standard) -->
    <link rel="preconnect" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom CSS -->
    <link href="assets/css/landing.css?v=14.0" rel="stylesheet">
</head>
<body>

    <!-- NAVBAR TRUST (Light & Glass) -->
    <nav class="navbar navbar-expand-lg navbar-trust sticky-top">
        <div class="container">
            <a class="navbar-brand brand-gradient d-flex align-items-center gap-2" href="index.php" style="font-family: 'Outfit', sans-serif; font-weight: 700; letter-spacing: -0.5px; font-size: 1.5rem;">
                <img src="assets/img/logu.jpeg" alt="Logo" width="40" height="40" class="rounded-circle">
                WiseFlow
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-4">
                    <li class="nav-item">
                        <a class="nav-link active text-primary" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Sobre Nós</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Funcionalidades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php" style="font-weight: 600;">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="register.php" class="btn btn-trust-primary btn-sm px-4">Criar Conta</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
