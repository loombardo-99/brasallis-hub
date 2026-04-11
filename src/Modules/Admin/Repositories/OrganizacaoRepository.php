<?php

namespace App\Modules\Admin\Repositories;

use PDO;

/**
 * OrganizacaoRepository — gerencia dados da empresa/tenant.
 */
class OrganizacaoRepository
{
    public function __construct(
        private PDO $pdo,
        private int $empresaId
    ) {}

    public function find(): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM empresas WHERE id = ?");
        $stmt->execute([$this->empresaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function update(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE empresas SET 
                name = ?, 
                razao_social = ?, 
                cnpj = ?, 
                email = ?, 
                phone = ?, 
                address = ?,
                openai_api_key = ?,
                gemini_api_key = ?,
                mp_access_token = ?,
                pagarme_key = ?
             WHERE id = ?"
        );
        return $stmt->execute([
            $data['nome_fantasia'] ?? '',
            $data['razao_social'] ?? '',
            $data['cnpj'] ?? '',
            $data['email_contato'] ?? '',
            $data['telefone'] ?? '',
            $data['endereco'] ?? '',
            $data['openai_api_key'] ?? null,
            $data['gemini_api_key'] ?? null,
            $data['mp_access_token'] ?? null,
            $data['pagarme_key'] ?? null,
            $this->empresaId
        ]);
    }
}
