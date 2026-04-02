<?php

namespace App\Modules\PDV\Services;

use App\Modules\PDV\Repositories\VendaRepository;
use App\Modules\Estoque\Repositories\ProdutoRepository;
use App\Services\FiscalIntegrator;
use PDO;
use Exception;

/**
 * PdvService — lógica de checkout, baixa de estoque e pós-venda.
 */
class PdvService
{
    public function __construct(
        private PDO $pdo,
        private VendaRepository $vendaRepo,
        private ProdutoRepository $produtoRepo
    ) {}

    public function finalizarVenda(array $items, string $paymentMethod): int
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $empresaId = (int)$_SESSION['empresa_id'];
        $userId    = (int)$_SESSION['user_id'];

        try {
            $this->pdo->beginTransaction();

            // 1. Calcular Total e Validar Estoque
            $totalAmount = 0;
            foreach ($items as $item) {
                $productId = (int)$item['id'];
                $qty = (float)$item['qty'];
                
                $produto = $this->produtoRepo->findById($productId);
                if (!$produto || $produto['quantity'] < $qty) {
                    throw new Exception("Estoque insuficiente para o produto: " . ($produto['name'] ?? 'Desconhecido'));
                }

                $totalAmount += ($qty * (float)$item['price']);
            }

            // 2. Criar Venda
            $vendaId = $this->vendaRepo->create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod
            ]);

            // 3. Processar Itens
            foreach ($items as $item) {
                $productId = (int)$item['id'];
                $qty = (float)$item['qty'];
                $price = (float)$item['price'];

                $this->vendaRepo->addItem([
                    'venda_id' => $vendaId,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $qty * $price
                ]);

                // Baixar Estoque
                $this->produtoRepo->atualizarEstoque($productId, $qty, 'saida');
            }

            // 4. Integração Fiscal (Opcional - chamando via Controller ou mantendo no Service)
            // Para manter o service "puro", poderíamos injetar o FiscalIntegrator ou emitir um evento.
            // No PDV legado, é chamado via API após a resposta do commit. 
            // Vamos manter a lógica aqui se quisermos automação total, mas o PDV legado é reativo ao cliente.

            $this->pdo->commit();
            return $vendaId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
