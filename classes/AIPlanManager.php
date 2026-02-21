<?php
namespace App;

use PDO;

class AIPlanManager {
    private $pdo;
    private $empresa_id;

    // SaaS Tiers Configuration
    const PLANS = [
        'free' => [
            'label' => 'Free', 
            'limit' => 100000, 
            'color' => 'secondary',
            'max_users' => 1,
            'features' => ['standard_agents']
        ],
        'growth' => [
            'label' => 'Growth', 
            'limit' => 2000000, 
            'color' => 'success', // Green for Growth
            'max_users' => 5,
            'features' => ['standard_agents', 'custom_agents', 'priority_support']
        ],
        'enterprise' => [
            'label' => 'Enterprise', 
            'limit' => 10000000, 
            'color' => 'primary', // Blue for Corp
            'max_users' => 999,
            'features' => ['standard_agents', 'custom_agents', 'autonomous_agents', 'dedicated_support']
        ]
    ];

    public function __construct(PDO $pdo, $empresa_id) {
        $this->pdo = $pdo;
        $this->empresa_id = $empresa_id;
    }

    public function getPlanStatus() {
        $stmt = $this->pdo->prepare("SELECT ai_plan, ai_tokens_used_month FROM empresas WHERE id = :id");
        $stmt->execute([':id' => $this->empresa_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        $planKey = $data['ai_plan'] ?: 'free';
        
        // Fallback if DB has old 'starter'/'pro' values not in array (though database migration should fix this)
        if (!array_key_exists($planKey, self::PLANS)) {
            // Map old to new temporarily if needed, or default/fail safe
            if ($planKey == 'starter') $planKey = 'growth';
            else if ($planKey == 'pro') $planKey = 'enterprise';
            else $planKey = 'free';
        }

        $planConfig = self::PLANS[$planKey];
        $limit = $planConfig['limit'];
        $used = (int)$data['ai_tokens_used_month'];
        $percentage = ($limit > 0) ? min(100, round(($used / $limit) * 100)) : 0;

        return [
            'plan' => $planKey,
            'label' => $planConfig['label'],
            'color' => $planConfig['color'],
            'limit' => $limit,
            'used' => $used,
            'percentage' => $percentage,
            'remaining' => max(0, $limit - $used),
            'is_exhausted' => $used >= $limit,
            'features' => $planConfig['features']
        ];
    }

    public function checkLimit() {
        $status = $this->getPlanStatus();
        if ($status && $status['is_exhausted']) {
            throw new \Exception("Limite de tokens do plano {$status['label']} atingido. Faça upgrade para continuar.");
        }
        return true;
    }

    public function canCreateCustomAgent() {
        $status = $this->getPlanStatus();
        if (!$status) return false;
        return in_array('custom_agents', $status['features']);
    }

    public function incrementUsage($tokens) {
        $sql = "UPDATE empresas SET ai_tokens_used_month = ai_tokens_used_month + :tokens WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tokens' => $tokens, ':id' => $this->empresa_id]);
    }
}
