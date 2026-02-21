<?php
// views/widgets/financeiro_profit.php
// Requer $total_profit_period e $forecast_data
?>
<!-- Profit & Forecast -->
<div class="col-12 col-md-6 col-xl-4" data-id="financeiro_profit">
    <div class="d-flex flex-column gap-4 h-100">
        <div class="card card-dashboard flex-grow-1 p-4 d-flex flex-row align-items-center justify-content-between">
            <div>
                <div class="kpi-label mb-1">Lucro Líquido</div>
                <h3 class="fw-bold mb-0 text-success">
                    R$ <?php echo number_format($total_profit_period ?? 8500.00, 2, ',', '.'); ?>
                </h3>
            </div>
            <div class="icon-circle bg-success-subtle text-success">
                <i class="fas fa-wallet fa-lg"></i>
            </div>
        </div>
        
        <div class="card card-dashboard flex-grow-1 p-4 d-flex flex-row align-items-center justify-content-between">
            <div>
                <div class="kpi-label mb-1">Previsão (30d)</div>
                <h3 class="fw-bold mb-0 text-primary">
                    R$ <?php echo number_format($forecast_data['next_month_sales'] ?? 42000.00, 2, ',', '.'); ?>
                </h3>
            </div>
            <div class="icon-circle bg-primary-subtle text-primary">
                <i class="fas fa-chart-line fa-lg"></i>
            </div>
        </div>
    </div>
</div>
