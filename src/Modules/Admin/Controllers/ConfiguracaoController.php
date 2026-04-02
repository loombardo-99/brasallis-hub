<?php

namespace App\Modules\Admin\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Modules\Admin\Repositories\OrganizacaoRepository;
use Exception;

/**
 * ConfiguracaoController — gerencia definições da empresa e sistema.
 */
class ConfiguracaoController
{
    public function __construct(private OrganizacaoRepository $repo) {}

    public function index(Request $request, Response $response): void
    {
        $empresa = $this->repo->find();
        $response->view('admin/configuracoes/index', compact('empresa'));
    }

    public function update(Request $request, Response $response): void
    {
        $data = $request->all();
        try {
            $this->repo->update($data);
            $response->redirect('/admin/configuracoes', ['success' => 'Configurações atualizadas com sucesso!']);
        } catch (Exception $e) {
            $response->redirect('/admin/configuracoes', ['error' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
    }
}
