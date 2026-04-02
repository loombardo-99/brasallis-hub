<?php

namespace App\Modules\PDV\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Modules\PDV\Services\PdvService;
use App\Modules\Estoque\Repositories\ProdutoRepository;
use Exception;

/**
 * PdvController — gerencia as requisições do Frente de Caixa.
 */
class PdvController
{
    public function __construct(
        private PdvService $service,
        private ProdutoRepository $produtoRepo
    ) {}

    public function index(Request $request, Response $response): void
    {
        $response->view('pdv/index');
    }

    public function processSale(Request $request, Response $response): void
    {
        $data = $request->json();
        $items = $data['items'] ?? [];
        $paymentMethod = $data['payment_method'] ?? 'dinheiro';

        if (empty($items)) {
            $response->json(['success' => false, 'error' => 'Carrinho vazio.'], 400);
            return;
        }

        try {
            $vendaId = $this->service->finalizarVenda($items, $paymentMethod);
            $response->json([
                'success' => true, 
                'venda_id' => $vendaId, 
                'message' => 'Venda finalizada com sucesso!'
            ]);
        } catch (Exception $e) {
            $response->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function searchProducts(Request $request, Response $response): void
    {
        $query = $request->input('q') ?? '';
        // Reaproveitando o ProdutoRepository
        $products = $this->produtoRepo->search($query);
        $response->json($products);
    }
}
