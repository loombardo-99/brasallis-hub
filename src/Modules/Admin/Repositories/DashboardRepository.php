<?php

namespace App\Modules\Admin\Repositories;

use PDO;

/**
 * DashboardRepository — busca métricas e dados para o painel principal.
 */
class DashboardRepository
{
    public function __construct(
        private PDO $pdo,
        private int $empresaId
    ) {}

    public function getKpis(): array
    {
        return [
            'revenue_today' => [
                'current' => $this->getRevenueToday(),
            ],
            'low_stock_items' => [
                'current' => $this->getLowStockCount(),
            ],
        ];
    }

    public function getRevenueToday(): float
    {
        // Exemplo simplificado (vendas do dia)
        $stmt = $this->pdo->prepare(
            "SELECT SUM(total) FROM vendas 
             WHERE empresa_id = ? AND DATE(data_venda) = CURDATE()"
        );
        $stmt->execute([$this->empresaId]);
        return (float)$stmt->fetchColumn();
    }

    public function getLowStockCount(): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM produtos 
             WHERE empresa_id = ? AND quantity <= minimum_stock"
        );
        $stmt->execute([$this->empresaId]);
        return (int)$stmt->fetchColumn();
    }

    public function getUltimasCompras(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT c.*, f.nome as fornecedor_nome 
             FROM compras c 
             LEFT JOIN fornecedores f ON c.fornecedor_id = f.id 
             WHERE c.empresa_id = ? 
             ORDER BY c.data_compra DESC, c.id DESC LIMIT ?"
        );
        $stmt->execute([$this->empresaId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLayout(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT layout_json FROM dashboard_layouts WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        
        if ($row) {
            return json_decode($row['layout_json'], true);
        }

        return [
            'row1' => ['financeiro_revenue', 'financeiro_profit'],
            'row2' => ['sales_chart', 'estoque_saude']
        ];
    }
}
