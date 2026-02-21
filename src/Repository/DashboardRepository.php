<?php

namespace App\Repository;

use PDO;

class DashboardRepository
{
    private $conn;
    private $empresa_id;

    public function __construct(PDO $conn, $empresa_id)
    {
        $this->conn = $conn;
        $this->empresa_id = $empresa_id;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    // --- MÉTODOS PARA OS CARDS ---

    private function calculate_percentage_change($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return (($current - $previous) / $previous) * 100;
    }

    public function getDashboardKPIs()
    {
        $kpis = [];

        // 1. Valor Total em Estoque (snapshot, sem tendência)
        $stmt_stock_value = $this->conn->prepare("SELECT SUM(quantity * cost_price) as total FROM produtos WHERE empresa_id = ?");
        $stmt_stock_value->execute([$this->empresa_id]);
        $kpis['total_stock_value'] = [
            'current' => $stmt_stock_value->fetchColumn() ?: 0,
        ];

        // 2. Compras no Mês (vs Mês Anterior)
        $stmt_compras = $this->conn->prepare("
            SELECT 
                SUM(CASE WHEN purchase_date >= CURDATE() - INTERVAL (DAYOFMONTH(CURDATE()) - 1) DAY THEN total_amount ELSE 0 END) as current_month,
                SUM(CASE WHEN purchase_date >= DATE_SUB(CURDATE() - INTERVAL (DAYOFMONTH(CURDATE()) - 1) DAY, INTERVAL 1 MONTH) AND purchase_date < CURDATE() - INTERVAL (DAYOFMONTH(CURDATE()) - 1) DAY THEN total_amount ELSE 0 END) as previous_month
            FROM compras 
            WHERE empresa_id = ? AND purchase_date >= DATE_SUB(CURDATE() - INTERVAL (DAYOFMONTH(CURDATE()) - 1) DAY, INTERVAL 1 MONTH)
        ");
        $stmt_compras->execute([$this->empresa_id]);
        $compras = $stmt_compras->fetch(PDO::FETCH_ASSOC);
        $current_compras = $compras['current_month'] ?: 0;
        $previous_compras = $compras['previous_month'] ?: 0;
        $kpis['total_sales_month'] = [ // A chave pode permanecer a mesma para não quebrar a view, mas a lógica agora é de compras
            'current' => $current_compras,
            'previous' => $previous_compras,
            'change' => $this->calculate_percentage_change($current_compras, $previous_compras)
        ];

        // 3. Produtos com Estoque Baixo (Snapshot, pois tendência é complexa sem histórico)
        $stmt_low_stock = $this->conn->prepare("SELECT COUNT(id) FROM produtos WHERE empresa_id = ? AND quantity <= minimum_stock AND quantity > 0");
        $stmt_low_stock->execute([$this->empresa_id]);
        $kpis['low_stock_items'] = [
            'current' => $stmt_low_stock->fetchColumn() ?: 0,
        ];

        // 4. Compras para Revisão (Snapshot)
        $stmt_pending = $this->conn->prepare("SELECT COUNT(dnf.compra_id) FROM dados_nota_fiscal dnf JOIN compras c ON dnf.compra_id = c.id WHERE dnf.status = 'pendente_confirmacao' AND c.empresa_id = ?");
        $stmt_pending->execute([$this->empresa_id]);
        $kpis['pending_review_purchases'] = [
            'current' => $stmt_pending->fetchColumn() ?: 0,
        ];

        return $kpis;
    }

    public function getDashboardInsights()
    {
        $insights = [];

        // Insight 1: Produtos de alta venda com estoque baixo (Prioridade Alta)
        $sql_low_stock_top_selling = "
            SELECT p.id, p.name, p.quantity, p.minimum_stock
            FROM produtos p
            JOIN (
                SELECT product_id, SUM(quantity) as total_sold
                FROM venda_itens vi
                JOIN vendas v ON vi.venda_id = v.id
                WHERE v.empresa_id = ? AND v.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY product_id
                ORDER BY total_sold DESC
                LIMIT 10
            ) as top_selling ON p.id = top_selling.product_id
            WHERE p.empresa_id = ? AND p.quantity <= p.minimum_stock
            LIMIT 3
        ";
        $stmt = $this->conn->prepare($sql_low_stock_top_selling);
        $stmt->execute([$this->empresa_id, $this->empresa_id]);
        $low_stock_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($low_stock_products as $product) {
            $insights[] = [
                'priority' => 1,
                'type' => 'danger',
                'icon' => 'fa-store-slash',
                'title' => 'Risco de Ruptura de Estoque',
                'description' => "Seu produto chave '<strong>" . htmlspecialchars($product['name']) . "</strong>' está com estoque baixo (" . $product['quantity'] . " un.).",
                'action_link' => 'compras.php',
                'action_text' => 'Criar Pedido de Compra'
            ];
        }

        // Insight 2: Produtos próximos da validade (Prioridade Média)
        $produtos_validade = $this->getProdutosProximosValidade(1);
        if (!empty($produtos_validade)) {
            $product = $produtos_validade[0];
            $insights[] = [
                'priority' => 2,
                'type' => 'warning',
                'icon' => 'fa-calendar-times',
                'title' => 'Validade Próxima',
                'description' => "O produto '<strong>" . htmlspecialchars($product['name']) . "</strong>' vence em " . date('d/m/Y', strtotime($product['validade'])) . ". Considere uma promoção.",
                'action_link' => 'produtos.php?search=' . urlencode($product['name']),
                'action_text' => 'Ver Produto'
            ];
        }

        // Insight 3: Compras pendentes de revisão (Prioridade Baixa)
        $pending_count = $this->getDashboardKPIs()['pending_review_purchases']['current'];
        if ($pending_count > 0) {
            $insights[] = [
                'priority' => 3,
                'type' => 'info',
                'icon' => 'fa-clipboard-check',
                'title' => 'Compras para Revisão',
                'description' => "Você tem <strong>" . $pending_count . "</strong> nota(s) fiscal(is) aguardando sua aprovação para entrada no estoque.",
                'action_link' => 'compras.php?filter=pending_review',
                'action_text' => 'Revisar Compras'
            ];
        }
        
        // Ordena os insights por prioridade
        usort($insights, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $insights;
    }

    // --- MÉTODOS PARA GRÁFICOS ---

    public function getStockActivityLast7Days()
    {
        $data = ['dates' => [], 'entradas' => [], 'saidas' => []];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $data['dates'][] = date('d/m', strtotime($date));

            $stmt = $this->conn->prepare("
                SELECT
                    (SELECT SUM(quantity) FROM historico_estoque WHERE action = 'entrada' AND empresa_id = :empresa_id AND DATE(created_at) = :date) as entradas,
                    (SELECT SUM(quantity) FROM historico_estoque WHERE action = 'saida' AND empresa_id = :empresa_id AND DATE(created_at) = :date) as saidas
            ");
            $stmt->execute([':empresa_id' => $this->empresa_id, ':date' => $date]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $data['entradas'][] = (int)($result['entradas'] ?? 0);
            $data['saidas'][] = (int)($result['saidas'] ?? 0);
        }
        return $data;
    }

    public function getCategoryDistribution()
    {
        $stmt = $this->conn->prepare("
            SELECT c.nome, COUNT(p.id) as count 
            FROM produtos p 
            JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.empresa_id = ? 
            GROUP BY c.nome 
            ORDER BY count DESC
        ");
        $stmt->execute([$this->empresa_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- MÉTODOS PARA TABELAS E LISTAS ---

    public function getLowStockProducts($limit = 5)
    {
        $stmt = $this->conn->prepare("
            SELECT id, name, quantity, minimum_stock 
            FROM produtos 
            WHERE quantity <= minimum_stock AND empresa_id = ? 
            ORDER BY quantity ASC 
            LIMIT ?
        ");
        $stmt->bindParam(1, $this->empresa_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestStockUpdates($limit = 5)
    {
        $stmt = $this->conn->prepare("
            SELECT h.action, h.quantity, h.created_at, p.name as product_name, u.username as user_name 
            FROM historico_estoque h 
            JOIN produtos p ON h.product_id = p.id 
            JOIN usuarios u ON h.user_id = u.id 
            WHERE h.empresa_id = ? 
            ORDER BY h.created_at DESC 
            LIMIT ?
        ");
        $stmt->bindParam(1, $this->empresa_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProdutosProximosValidade($limit = 5)
    {
        $stmt = $this->conn->prepare("
            SELECT name, validade, quantity
            FROM produtos
            WHERE validade IS NOT NULL 
              AND validade BETWEEN CURDATE() AND CURDATE() + INTERVAL 30 DAY
              AND empresa_id = ?
            ORDER BY validade ASC
            LIMIT ?
        ");
        $stmt->bindParam(1, $this->empresa_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUltimasCompras($limit = 5)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                c.id, 
                c.purchase_date as data_compra, 
                s.name as fornecedor_nome, 
                c.total_amount as total,
                COALESCE(dnf.status, 'pendente') as status
            FROM compras c
            LEFT JOIN fornecedores s ON c.supplier_id = s.id
            LEFT JOIN dados_nota_fiscal dnf ON c.id = dnf.compra_id
            WHERE c.empresa_id = ?
            ORDER BY c.purchase_date DESC, c.id DESC
            LIMIT ?
        ");
        $stmt->bindParam(1, $this->empresa_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPaymentMethodDistribution()
    {
        $stmt = $this->conn->prepare("
            SELECT payment_method, COUNT(id) as count
            FROM vendas
            WHERE empresa_id = ?
            GROUP BY payment_method
            ORDER BY count DESC
        ");
        $stmt->execute([$this->empresa_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesAndProfitOverTime($months = 6)
    {
        // Gera uma lista dos últimos X meses para garantir que todos apareçam no gráfico
        $month_labels = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month_labels[date('Y-m', strtotime("-$i month"))] = [
                'label' => date('M/Y', strtotime("-$i month")),
                'sales' => 0,
                'profit' => 0
            ];
        }

        // 1. Query Vendas (Total Sales) - Robust against missing items
        $sql_sales = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(total_amount) as total_sales
            FROM vendas
            WHERE empresa_id = ? 
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY month
        ";
        $stmt_sales = $this->conn->prepare($sql_sales);
        $stmt_sales->execute([$this->empresa_id, $months]);
        $sales_results = $stmt_sales->fetchAll(PDO::FETCH_ASSOC);

        foreach ($sales_results as $row) {
            if (isset($month_labels[$row['month']])) {
                $month_labels[$row['month']]['sales'] = (float)$row['total_sales'];
            }
        }

        // 2. Query Profit (Requires items and products)
        $sql_profit = "
            SELECT 
                DATE_FORMAT(v.created_at, '%Y-%m') as month,
                SUM(vi.quantity * (vi.unit_price - COALESCE(p.cost_price, 0))) as total_profit
            FROM vendas v
            JOIN venda_itens vi ON v.id = vi.venda_id
            LEFT JOIN produtos p ON vi.product_id = p.id
            WHERE v.empresa_id = ? 
              AND v.created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY month
        ";
        $stmt_profit = $this->conn->prepare($sql_profit);
        $stmt_profit->execute([$this->empresa_id, $months]);
        $profit_results = $stmt_profit->fetchAll(PDO::FETCH_ASSOC);

        foreach ($profit_results as $row) {
            if (isset($month_labels[$row['month']])) {
                $month_labels[$row['month']]['profit'] = (float)$row['total_profit'];
            }
        }

        return $month_labels;
    }

    public function getSalesData($period = 'month')
    {
        $dateFormat = '%Y-%m';
        $interval = '12 MONTH';
        $groupBy = 'month';
        
        if ($period === 'day') {
            $dateFormat = '%Y-%m-%d';
            $interval = '30 DAY';
            $groupBy = 'day';
        } elseif ($period === 'year') {
            $dateFormat = '%Y';
            $interval = '5 YEAR';
            $groupBy = 'year';
        }

        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '$dateFormat') as label,
                SUM(total_amount) as total_sales
            FROM vendas
            WHERE empresa_id = ? 
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL $interval)
            GROUP BY label
            ORDER BY label ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->empresa_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopSellingProducts($limit = 10)
    {
        $sql = "
            SELECT 
                p.name,
                SUM(vi.quantity) as total_quantity_sold
            FROM venda_itens vi
            JOIN produtos p ON vi.product_id = p.id
            JOIN vendas v ON vi.venda_id = v.id
            WHERE v.empresa_id = ?
            GROUP BY vi.product_id, p.name
            ORDER BY total_quantity_sold DESC
            LIMIT ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $this->empresa_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPurchaseBySupplier()
    {
        $sql = "
            SELECT 
                f.name,
                SUM(c.total_amount) as total_purchased
            FROM compras c
            JOIN fornecedores f ON c.supplier_id = f.id
            WHERE c.empresa_id = ? AND c.total_amount > 0
            GROUP BY c.supplier_id, f.name
            ORDER BY total_purchased DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->empresa_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopProfitableProducts($limit = 10)
    {
        $sql = "
            SELECT 
                p.name,
                SUM(vi.quantity * (vi.unit_price - p.cost_price)) as total_profit
            FROM venda_itens vi
            JOIN produtos p ON vi.product_id = p.id
            JOIN vendas v ON vi.venda_id = v.id
            WHERE v.empresa_id = ?
            GROUP BY vi.product_id, p.name
            HAVING total_profit > 0
            ORDER BY total_profit DESC
            LIMIT ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $this->empresa_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
