<?php
namespace App;

use PDO;

class AIAgent {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create($empresa_id, $name, $role, $model, $instruction, $temperature) {
        $sql = "INSERT INTO ai_agents (empresa_id, name, role, model, system_instruction, temperature) 
                VALUES (:empresa_id, :name, :role, :model, :instruction, :temperature)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':empresa_id' => $empresa_id,
            ':name' => $name,
            ':role' => $role,
            ':model' => $model,
            ':instruction' => $instruction,
            ':temperature' => $temperature
        ]);
    }

    public function getAll($empresa_id) {
        $sql = "SELECT * FROM ai_agents WHERE empresa_id = :empresa_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':empresa_id' => $empresa_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id, $empresa_id) {
        $sql = "SELECT * FROM ai_agents WHERE id = :id AND empresa_id = :empresa_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':empresa_id' => $empresa_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $empresa_id, $data) {
        $fields = [];
        $params = [':id' => $id, ':empresa_id' => $empresa_id];

        if (isset($data['name'])) { $fields[] = "name = :name"; $params[':name'] = $data['name']; }
        if (isset($data['role'])) { $fields[] = "role = :role"; $params[':role'] = $data['role']; }
        if (isset($data['model'])) { $fields[] = "model = :model"; $params[':model'] = $data['model']; }
        if (isset($data['instruction'])) { $fields[] = "system_instruction = :instruction"; $params[':instruction'] = $data['instruction']; }
        if (isset($data['temperature'])) { $fields[] = "temperature = :temperature"; $params[':temperature'] = $data['temperature']; }
        if (isset($data['status'])) { $fields[] = "status = :status"; $params[':status'] = $data['status']; }

        if (empty($fields)) return false;

        $sql = "UPDATE ai_agents SET " . implode(', ', $fields) . " WHERE id = :id AND empresa_id = :empresa_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $empresa_id) {
        $sql = "DELETE FROM ai_agents WHERE id = :id AND empresa_id = :empresa_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':empresa_id' => $empresa_id]);
    }

    public function logUsage($agent_id, $user_id, $input_tokens, $output_tokens) {
        $sql = "INSERT INTO ai_agent_logs (agent_id, user_id, input_tokens, output_tokens) 
                VALUES (:agent_id, :user_id, :input_tokens, :output_tokens)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':agent_id' => $agent_id,
            ':user_id' => $user_id,
            ':input_tokens' => $input_tokens,
            ':output_tokens' => $output_tokens
        ]);
    }

    public function getUsageStats($empresa_id) {
        $sql = "
            SELECT 
                a.name as agent_name,
                COUNT(l.id) as total_uses,
                SUM(l.input_tokens) as total_input,
                SUM(l.output_tokens) as total_output,
                MAX(l.created_at) as last_used
            FROM ai_agents a
            LEFT JOIN ai_agent_logs l ON a.id = l.agent_id
            WHERE a.empresa_id = :empresa_id
            GROUP BY a.id, a.name
            ORDER BY total_uses DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':empresa_id' => $empresa_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
