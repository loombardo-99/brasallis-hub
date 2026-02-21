<?php
// views/landing.php
require_once __DIR__ . '/../includes/header.php';
?>

<!-- AOS Animation -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<!-- BACKGROUND HERO -->
<!-- BACKGROUND HERO -->
<style>
    /* Direct application to section-hero for guaranteed visibility */
    .section-hero {
        background: linear-gradient(135deg, #0A2647 0%, #0F3D39 100%) !important; /* Navy to Dark Emerald */
        position: relative;
        overflow: hidden;
        color: white; /* Base text color */
        padding-top: 120px;
        padding-bottom: 100px;
        z-index: 1; /* Ensure distinct stacking context */
    }
    
    /* LIGHT BUTTON FIX for Dark Backgrounds */
    .btn-trust-outline-light {
        background: transparent;
        color: white !important;
        border: 1px solid rgba(255,255,255, 0.4);
        padding: 14px 28px;
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    .btn-trust-outline-light:hover {
        background: white;
        color: #0A2647 !important; /* Navy Text on Hover */
        border-color: white;
    }

    /* Radial Overlay for depth - using pseudo-element on the section itself */
<!-- [Preserved Lines logic handled by strict replacement, assuming context match] -->
<div class="bg-hero-gradient"></div>

<!-- HERO SECTION: Layered Depth -->
<section class="section-hero position-relative">
    <!-- bg-pattern removed for clean gradient -->
    <div class="container position-relative z-2">
        <div class="row align-items-center gy-5">
            <!-- Content -->
            <div class="col-lg-5" data-aos="fade-right">
                <span class="d-inline-block py-1 px-3 rounded-pill bg-light border border-secondary border-opacity-25 text-secondary fw-bold small mb-4">

    .section-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: 
            radial-gradient(circle at 10% 20%, rgba(44, 120, 101, 0.2) 0%, transparent 40%),
            radial-gradient(circle at 90% 80%, rgba(10, 38, 71, 0.4) 0%, transparent 40%);
        pointer-events: none;
        z-index: -1; /* Behind content */
    }

    /* Force text colors to white for contrast */
    .section-hero .lead {
        color: rgba(255,255,255, 0.9) !important;
    }
    .section-hero h1, .section-hero h2, .section-hero h3 {
        color: #ffffff !important;
    }
    
    /* Mockup Shadow */
    .mockup-container {
        box-shadow: 0 20px 50px rgba(0,0,0,0.4) !important;
    }
</style>

<!-- HERO SECTION: Layered Depth -->
<section class="section-hero position-relative">
    <!-- bg-pattern removed for clean gradient -->
    <div class="container position-relative z-2">
        <div class="row align-items-center gy-5">
            <!-- Content -->
            <div class="col-lg-5" data-aos="fade-right">
                <span class="d-inline-block py-1 px-3 rounded-pill bg-light border border-secondary border-opacity-25 text-secondary fw-bold small mb-4">
                    <i class="fas fa-check-circle text-success me-2"></i> ERP Homologado
                </span>
                
                <h1 class="display-4 fw-bold mb-4 lh-sm">
                    A inteligência que seu varejo precisa para <span style="color: var(--sys-emerald);">lucrar mais.</span>
                </h1>
                
                <p class="lead mb-5">
                    Deixe a complexidade fiscal com a gente. O WiseFlow automatiza estoque, vendas e impostos para você focar em crescer.
                </p>
                
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="register.php" class="btn btn-trust-primary">
                        Experimentar Grátis
                    </a>
                    <a href="#features" class="btn btn-trust-outline-light">
                        Ver Recursos
                    </a>
                </div>
                
                <div class="mt-5 border-top pt-4">
                    <p class="small text-muted mb-2">Confiado por:</p>
                    <div class="d-flex gap-4 opacity-50 grayscale-logos">
                        <span class="fw-bold text-dark"><i class="fas fa-store"></i> LOJA 1</span>
                        <span class="fw-bold text-dark"><i class="fas fa-shopping-bag"></i> SHOP X</span>
                        <span class="fw-bold text-dark"><i class="fas fa-truck"></i> DISTRIB E</span>
                    </div>
                </div>
            </div>
            
            <!-- Fiscal Depth Mockup (Interactive) -->
            <div class="col-lg-7 position-relative" data-aos="fade-left">
                <div class="mockup-container bg-white p-4">
                    <!-- Chart Container -->
                    <div style="height: 350px;">
                        <canvas id="heroChart"></canvas>
                    </div>
                </div>
                
                <!-- Floating Elements (Depth) -->
                <div class="float-badge badge-pos-1">
                    <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size: 10px;">Status NF-e</small>
                        <span class="fw-bold text-dark small">Autorizada</span>
                    </div>
                </div>
                
                <div class="float-badge badge-pos-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size: 10px;">Receita Hoje</small>
                        <span class="fw-bold text-dark small">R$ 14.500</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT SECTION: Mission & Authority -->
<section id="about" class="section-white">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-5 order-lg-2 ms-lg-auto" data-aos="fade-left">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="d-block bg-primary rounded-circle" style="width: 8px; height: 8px;"></span>
                    <span class="text-uppercase small fw-bold text-primary tracking-wide">Sobre Nós</span>
                </div>
                <h2 class="display-5 fw-bold mb-4">Engenharia aplicada ao Varejo.</h2>
                <p class="lead mb-4">
                    Nascemos com uma missão clara: <strong>Acabar com a complexidade fiscal</strong> que impede pequenos e médios varejistas de crescerem.
                </p>
                <p class="text-secondary mb-4">
                    O WiseFlow não é apenas um software. É uma camada de inteligência que protege seu CNPJ. Processamos mais de R$ 500 milhões em vendas anuais, garantindo que cada centavo de imposto seja calculado com precisão cirúrgica.
                </p>
                
                <div class="d-flex gap-4 mt-5">
                    <div>
                        <h3 class="fw-bold mb-0 text-primary">5+</h3>
                        <small class="text-secondary text-uppercase fw-bold" style="font-size: 0.7rem;">Anos de Mercado</small>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-primary">2k+</h3>
                        <small class="text-secondary text-uppercase fw-bold" style="font-size: 0.7rem;">Lojas Ativas</small>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-primary">99.9%</h3>
                        <small class="text-secondary text-uppercase fw-bold" style="font-size: 0.7rem;">Uptime (SLA)</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 order-lg-1" data-aos="fade-right">
                <!-- CHART: The WiseFlow Effect -->
                <div class="card-trust p-4 border-primary border-opacity-10" style="background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h6 class="fw-bold mb-1" style="color: var(--sys-navy);">O Efeito WiseFlow</h6>
                            <small class="text-secondary">Crescimento Médio vs. Gestão Manual</small>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                            <i class="fas fa-arrow-trend-up me-1"></i> +40% Receita
                        </span>
                    </div>
                    
                    <div style="height: 320px;">
                        <canvas id="chartAbout"></canvas>
                    </div>
                    
                    <!-- Micro interactions / Insights -->
                    <div class="row mt-4 pt-3 border-top g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-danger bg-opacity-10 p-2 d-flex justify-content-center align-items-center" style="width:32px; height:32px;">
                                    <i class="fas fa-arrow-down text-danger" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <span class="d-block fw-bold small">-15h</span>
                                    <small class="text-secondary" style="font-size: 0.7rem;">Tempo gasto/semana</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-success bg-opacity-10 p-2 d-flex justify-content-center align-items-center" style="width:32px; height:32px;">
                                    <i class="fas fa-check text-success" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <span class="d-block fw-bold small">Zero</span>
                                    <small class="text-secondary" style="font-size: 0.7rem;">Multas Fiscais</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- INTERACTIVE FEATURES (Tabs) -->
<section id="features" class="section-alt">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Gestão Completa</h2>
            <p class="text-secondary">Uma conta, todas as soluções.</p>
        </div>
        
        <!-- Tabs Nav -->
        <ul class="nav nav-pills-trust justify-content-center mb-5" id="pills-tab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="pills-fiscal-tab" data-bs-toggle="pill" data-bs-target="#pills-fiscal" type="button">Fiscal & NF-e</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="pills-stock-tab" data-bs-toggle="pill" data-bs-target="#pills-stock" type="button">Estoque Inteligente</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="pills-finance-tab" data-bs-toggle="pill" data-bs-target="#pills-finance" type="button">Gestão Financeira</button>
            </li>
        </ul>
        
        <!-- Tabs Content -->
        <div class="tab-content" id="pills-tabContent">
            <!-- Fiscal -->
            <div class="tab-pane fade show active" id="pills-fiscal" role="tabpanel">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <h3 class="mb-3">Emissor Turbo Connect</h3>
                        <p class="text-secondary mb-4">
                            Emita notas fiscais em segundos. Nosso sistema valida NCMs automaticamente e calcula impostos como ICMS-ST e Difal sem você precisar de um contador ao lado.
                        </p>
                        <ul class="list-unstyled d-flex flex-column gap-3">
                            <li class="d-flex align-items-center gap-3">
                                <div class="icon-box-trust mb-0" style="width: 40px; height: 40px;"><i class="fas fa-bolt text-primary"></i></div>
                                <span>Emissão em 2 segundos</span>
                            </li>
                            <li class="d-flex align-items-center gap-3">
                                <div class="icon-box-trust mb-0" style="width: 40px; height: 40px;"><i class="fas fa-search text-primary"></i></div>
                                <span>Auditoria de NCM Automática</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <div class="card-trust p-4 h-100">
                             <h6 class="text-secondary text-uppercase small fw-bold mb-3">Volume de Emissões (Últimos 6 Meses)</h6>
                             <div style="height: 300px;">
                                <canvas id="chartFiscal"></canvas>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stock -->
            <div class="tab-pane fade" id="pills-stock" role="tabpanel">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <h3 class="mb-3">Controle que evita perdas</h3>
                        <p class="text-secondary mb-4">
                            Saiba exatamente o que tem na prateleira. O sistema avisa produtos perto do vencimento e sugere reposição baseada no giro de vendas.
                        </p>
                        <ul class="list-unstyled d-flex flex-column gap-3">
                            <li class="d-flex align-items-center gap-3">
                                <div class="icon-box-trust mb-0" style="width: 40px; height: 40px;"><i class="fas fa-box-open text-primary"></i></div>
                                <span>Previsão de Ruptura (IA)</span>
                            </li>
                            <li class="d-flex align-items-center gap-3">
                                <div class="icon-box-trust mb-0" style="width: 40px; height: 40px;"><i class="fas fa-rotate text-primary"></i></div>
                                <span>Cálculo de Giro Automático</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <div class="card-trust p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-secondary text-uppercase small fw-bold mb-0">Saúde do Estoque</h6>
                                <span class="badge bg-success bg-opacity-10 text-success">Saudável</span>
                            </div>
                            <div style="height: 300px; position: relative;">
                                <canvas id="chartStock"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Finance -->
            <div class="tab-pane fade" id="pills-finance" role="tabpanel">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <h3 class="mb-3">DRE e Lucro Real</h3>
                        <p class="text-secondary mb-4">
                            Não espere o fim do mês para saber se teve lucro. Acompanhe o DRE gerencial em tempo real, fluxo de caixa e conciliação bancária.
                        </p>
                         <ul class="list-unstyled d-flex flex-column gap-3">
                            <li class="d-flex align-items-center gap-3">
                                <div class="icon-box-trust mb-0" style="width: 40px; height: 40px;"><i class="fas fa-chart-line text-primary"></i></div>
                                <span>DRE em Tempo Real</span>
                            </li>
                            <li class="d-flex align-items-center gap-3">
                                <div class="icon-box-trust mb-0" style="width: 40px; height: 40px;"><i class="fas fa-file-invoice-dollar text-primary"></i></div>
                                <span>Conciliação Bancária</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                         <div class="card-trust p-4 h-100">
                             <h6 class="text-secondary text-uppercase small fw-bold mb-3">DRE Gerencial (Tempo Real)</h6>
                             <div style="height: 300px;">
                                <canvas id="chartFinance"></canvas>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TRUST FOOTER -->
<footer class="footer-trust mt-0">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                    <img src="assets/img/logu.jpeg" width="32" class="rounded-circle" alt="Logo">
                    WiseFlow
                </h5>
                <p class="small text-white-50">
                    Plataforma líder em gestão fiscal e comercial para varejo de alta performance.
                </p>
                <div class="d-flex gap-3 mt-4">
                    <a href="#"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#"><i class="fab fa-linkedin fa-lg"></i></a>
                    <a href="#"><i class="fab fa-whatsapp fa-lg"></i></a>
                </div>
            </div>
            
            <div class="col-6 col-lg-2">
                <h6 class="fw-bold mb-3 small text-uppercase text-white-50">Produto</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#">Fiscal</a></li>
                    <li class="mb-2"><a href="#">Estoque</a></li>
                    <li class="mb-2"><a href="#">Financeiro</a></li>
                    <li class="mb-2"><a href="developers.php">API Developers</a></li>
                </ul>
            </div>
            
            <div class="col-6 col-lg-2">
                <h6 class="fw-bold mb-3 small text-uppercase text-white-50">Empresa</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#about">Sobre Nós</a></li>
                    <li class="mb-2"><a href="#">Contato</a></li>
                    <li class="mb-2"><a href="#">Blog</a></li>
                </ul>
            </div>
            
            <div class="col-lg-4">
                <div class="p-4 rounded-3 border border-white border-opacity-10 bg-white bg-opacity-10">
                    <h6 class="fw-bold mb-2">Precisa de ajuda?</h6>
                    <p class="small text-white-50 mb-3">Nosso time de suporte fiscal está online.</p>
                    <a href="#" class="btn btn-sm btn-light text-primary fw-bold w-100">Falar com Consultor</a>
                </div>
            </div>
        </div>
        
        <div class="border-top border-white border-opacity-10 mt-5 pt-4 text-center small text-white-50">
            &copy; 2026 WiseFlow Tecnologia. Todos os direitos reservados.
        </div>
    </div>
</footer>

<!-- Style Override to Fix Icons/Text -->
<style>.grayscale-logos { filter: grayscale(100%); opacity: 0.6; }</style>

<!-- AOS Init -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 800, once: true });

  // SCROLLSPY (Blue Highlight)
  const sections = document.querySelectorAll('section');
  const navLi = document.querySelectorAll('.navbar-nav .nav-item .nav-link');

  window.addEventListener('scroll', () => {
    let current = '';
    
    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.clientHeight;
      if (scrollY >= (sectionTop - 200)) {
        current = section.getAttribute('id');
      }
    });

    if (scrollY < 200) current = ''; 
    
    navLi.forEach(li => {
      li.classList.remove('active', 'text-primary');
      const href = li.getAttribute('href');

      if (current === '') {
        // Top of page -> Highlight Home only
        if (li.textContent.trim() === 'Home') { 
             li.classList.add('active', 'text-primary');
        }
      } else {
        // Scrolled -> Highlight matching section (if not Home)
        if (href.includes(current) && li.textContent.trim() !== 'Home') {
             li.classList.add('active', 'text-primary');
        }
      }
    });
  });

  // CHART CONFIGURATION (WiseFlow Identity)
  Chart.defaults.font.family = "'Outfit', sans-serif";
  Chart.defaults.color = '#64748B';
  const navy = '#0A2647';
  const emerald = '#2C7865';
  const emeraldLight = 'rgba(44, 120, 101, 0.2)';

  // 1. HERO CHART (Revenue Growth)
  const ctxHero = document.getElementById('heroChart');
  if (ctxHero) {
      new Chart(ctxHero, {
          type: 'line',
          data: {
              labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
              datasets: [{
                  label: 'Crescimento de Receita',
                  data: [12000, 19000, 15000, 25000, 32000, 45000],
                  borderColor: emerald,
                  backgroundColor: emeraldLight,
                  borderWidth: 3,
                  tension: 0.4,
                  fill: true,
                  pointBackgroundColor: '#fff',
                  pointBorderColor: emerald,
                  pointRadius: 6
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: {
                  y: { display: false, grid: { display: false } },
                  x: { grid: { display: false } }
              }
          }
      });
  }

  // 2. FISCAL CHART (Sales Volume)
  const ctxFiscal = document.getElementById('chartFiscal');
  if (ctxFiscal) {
      new Chart(ctxFiscal, {
          type: 'bar',
          data: {
              labels: ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
              datasets: [{
                  label: 'Notas Emitidas',
                  data: [45, 59, 80, 81, 156, 120],
                  backgroundColor: navy,
                  borderRadius: 4
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: { y: { beginAtZero: true } }
          }
      });
  }

  // 3. STOCK CHART (Health)
  const ctxStock = document.getElementById('chartStock');
  if (ctxStock) {
      new Chart(ctxStock, {
          type: 'doughnut',
          data: {
              labels: ['Estoque Saudável', 'Baixo Giro', 'Crítico'],
              datasets: [{
                  data: [300, 50, 20],
                  backgroundColor: [emerald, '#F59E0B', '#EF4444'],
                  borderWidth: 0,
                  hoverOffset: 4
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { 
                  legend: { position: 'bottom', labels: { usePointStyle: true } } 
              },
              cutout: '70%'
          }
      });
  }

  // 4. FINANCE CHART (DRE)
  const ctxFinance = document.getElementById('chartFinance');
  if (ctxFinance) {
      new Chart(ctxFinance, {
          type: 'bar',
          data: {
              labels: ['Receita', 'Custos', 'Despesas', 'Lucro'],
              datasets: [{
                  label: 'DRE Consolidado',
                  data: [100000, -40000, -20000, 40000],
                  backgroundColor: [emerald, '#EF4444', '#F59E0B', navy],
                  borderRadius: 6
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              indexAxis: 'y'
          }
      });
  }

  // 5. ABOUT CHART (Growth Story - The "WiseFlow Effect")
  const ctxAbout = document.getElementById('chartAbout');
  if (ctxAbout) {
      new Chart(ctxAbout, {
          type: 'line',
          data: {
              labels: ['Ano 1', 'Ano 2', 'Ano 3', 'Ano 4', 'Ano 5'],
              datasets: [
                {
                  label: 'Com WiseFlow',
                  data: [100, 180, 290, 450, 680],
                  borderColor: navy,
                  backgroundColor: 'rgba(10, 38, 71, 0.05)',
                  borderWidth: 3,
                  tension: 0.4,
                  fill: true,
                  pointBackgroundColor: navy
                },
                {
                  label: 'Gestão Manual',
                  data: [100, 120, 135, 145, 155],
                  borderColor: '#94A3B8',
                  borderWidth: 2,
                  borderDash: [5, 5],
                  tension: 0.4,
                  pointRadius: 0
                  // No fill for manual
                }
              ]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { 
                legend: { position: 'bottom' },
                tooltip: { mode: 'index', intersect: false }
              },
              scales: {
                  y: { display: false },
                  x: { grid: { display: false } }
              }
          }
      });
  }
</script>

<?php /* Custom footer used above */ ?>
</body>
</html>
