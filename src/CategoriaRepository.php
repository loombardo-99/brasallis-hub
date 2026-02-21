<?php

namespace App;

use PDO;

class CategoriaRepository
{
    private $conn;
    private $empresa_id;

    public function __construct($empresa_id)
    {
        $this->conn = Database::getInstance()->getConnection();
        $this->empresa_id = $empresa_id;
    }

    public function add($nome)
    {
        $stmt = $this->conn->prepare("INSERT INTO categorias (empresa_id, nome) VALUES (?, ?)");
        return $stmt->execute([$this->empresa_id, $nome]);
    }

    public function update($id, $nome)
    {
        $stmt = $this->conn->prepare("UPDATE categorias SET nome = ? WHERE id = ? AND empresa_id = ?");
        return $stmt->execute([$nome, $id, $this->empresa_id]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM categorias WHERE id = ? AND empresa_id = ?");
        return $stmt->execute([$id, $this->empresa_id]);
    }

    public function getAll($search)
    {
        $query = "SELECT id, nome, created_at FROM categorias WHERE empresa_id = ?";
        $params = [$this->empresa_id];

        if (!empty($search)) {
            $query .= " AND nome LIKE ?";
            $params[] = '%' . $search . '%';
        }
        $query .= " ORDER BY nome ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function find($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM categorias WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$id, $this->empresa_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
