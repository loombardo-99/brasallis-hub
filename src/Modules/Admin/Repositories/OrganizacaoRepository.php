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
                nome_fantasia = ?, 
                razao_social = ?, 
                cnpj = ?, 
                email_contato = ?, 
                telefone = ?, 
                endereco = ? 
             WHERE id = ?"
        );
        return $stmt->execute([
            $data['nome_fantasia'],
            $data['razao_social'],
            $data['cnpj'],
            $data['email_contato'],
            $data['telefone'],
            $data['endereco'],
            $this->empresaId
        ]);
    }
}
