<?php
/**
 * Script: force_sync_financeiro.php
 * Finalidade: Reconstruir as tabelas de contas a pagar/receber e restaurar dados existentes.
 * Execução: CLI ou Navegador.
 */
require_once __DIR__ . '/includes/db_config.php';
require_once __DIR__ . '/includes/funcoes.php';

header('Content-Type: text/plain; charset=utf-8');
echo "--- INICIANDO SINCRONIZAÇÃO FORÇADA FINANCEIRA ---\n";

try {
    $conn = connect_db();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. BACKUP DE DADOS EXISTENTES
    $backup_pagar = [];
    $backup_receber = [];

    // Tenta ler dados de pagar
    try { $st = $conn->query("SELECT * FROM contas_pagar"); $backup_pagar = $st->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) { echo "Aviso: Tabela contas_pagar ausente no backup.\n"; }
    // Tenta ler dados de receber
    try { $st = $conn->query("SELECT * FROM contas_receber"); $backup_receber = $st->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) { echo "Aviso: Tabela contas_receber ausente no backup.\n"; }

    echo "Dados em backup: " . count($backup_pagar) . " CP, " . count($backup_receber) . " CR.\n";

    // 2. RECONSTRUÇÃO (DROP & CREATE)
    echo "Limpando tabelas antigas...\n";
    $conn->exec("DROP TABLE IF EXISTS `contas_pagar` CASCADE");
    $conn->exec("DROP TABLE IF EXISTS `contas_receber` CASCADE");

    echo "Criando tabelas novas...\n";
    
    // Contas a Pagar
    $conn->exec("
        CREATE TABLE `contas_pagar` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `empresa_id` int(11) NOT NULL,
            `descricao` varchar(255) NOT NULL,
            `valor` decimal(10,2) NOT NULL,
            `data_vencimento` date NOT NULL,
            `data_pagamento` date DEFAULT NULL,
            `status` enum('pendente','pago','atrasado','cancelado') NOT NULL DEFAULT 'pendente',
            `categoria_id` int(11) DEFAULT NULL,
            `obs` text DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_empresa_pagar` (`empresa_id`),
            KEY `idx_status_pagar` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Contas a Receber
    $conn->exec("
        CREATE TABLE `contas_receber` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `empresa_id` int(11) NOT NULL,
            `descricao` varchar(255) NOT NULL,
            `valor` decimal(10,2) NOT NULL,
            `data_vencimento` date NOT NULL,
            `data_recebimento` date DEFAULT NULL,
            `status` enum('pendente','recebido','atrasado','cancelado') NOT NULL DEFAULT 'pendente',
            `categoria_id` int(11) DEFAULT NULL,
            `obs` text DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_empresa_receber` (`empresa_id`),
            KEY `idx_status_receber` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 3. RESTAURAÇÃO DE DADOS
    echo "Restaurando dados...\n";
    
    if (!empty($backup_pagar)) {
        $stmt = $conn->prepare("INSERT INTO contas_pagar (id, empresa_id, descricao, valor, data_vencimento, data_pagamento, status, categoria_id, obs, created_at) VALUES (?,?,?,?,?,?,?,?,?,?)");
        foreach($backup_pagar as $row) {
            $stmt->execute([$row['id'], $row['empresa_id'], $row['descricao'], $row['valor'], $row['data_vencimento'], $row['data_pagamento'], $row['status'], $row['categoria_id'], $row['obs'], $row['created_at']]);
        }
    }

    if (!empty($backup_receber)) {
        $stmt = $conn->prepare("INSERT INTO contas_receber (id, empresa_id, descricao, valor, data_vencimento, data_recebimento, status, categoria_id, obs, created_at) VALUES (?,?,?,?,?,?,?,?,?,?)");
        foreach($backup_receber as $row) {
            $stmt->execute([$row['id'], $row['empresa_id'], $row['descricao'], $row['valor'], $row['data_vencimento'], $row['data_recebimento'], $row['status'], $row['categoria_id'], $row['obs'], $row['created_at']]);
        }
    }

    echo "Sincronização concluída com sucesso!\n";

} catch (Exception $e) {
    echo "ERRO CRÍTICO NA SINCRONIZAÇÃO: " . $e->getMessage() . "\n";
}
