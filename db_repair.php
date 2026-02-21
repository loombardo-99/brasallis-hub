<?php
// db_repair.php (Versão V2 - Inteligente)
// Força criação de tabelas e resolve compatibilidade de FK

require_once 'includes/db_config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Diagnóstico e Reparo de Banco de Dados (v2)</h2>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Garante Banco
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    $pdo->exec("USE `" . DB_NAME . "`");
    echo "<p style='color:green'>✓ Conectado ao banco " . DB_NAME . "</p>";

    // 2. Garante Tabela Principal (EMPRESAS)
    // Verifica se existe
    $tableExists = $pdo->query("SHOW TABLES LIKE 'empresas'")->rowCount() > 0;
    
    $empresaIdType = "INT(11)"; // Default fallback
    
    if (!$tableExists) {
        echo "<p style='color:orange'>⚠ Tabela 'empresas' não existia. Criando...</p>";
        $pdo->exec("
            CREATE TABLE empresas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                owner_user_id INT DEFAULT 0,
                ai_plan VARCHAR(50) DEFAULT 'free',
                ai_token_limit INT DEFAULT 1000,
                max_users INT DEFAULT 5,
                support_level VARCHAR(50) DEFAULT 'standard',
                ai_tokens_used_month INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    } else {
        // Inspeciona o tipo do ID para garantir compatibilidade da FK
        $stmt = $pdo->query("DESCRIBE empresas id");
        $col = $stmt->fetch(PDO::FETCH_ASSOC);
        $empresaIdType = strtoupper($col['Type']); // ex: INT(11) ou BIGINT(20) UNSIGNED
        echo "<p>ℹ️ Tabela 'empresas' detectada. Tipo do ID: <strong>$empresaIdType</strong></p>";
    }
    
    // Garante Tabela de Usuários (para FK de usuario_setor)
    $userTableExists = $pdo->query("SHOW TABLES LIKE 'usuarios'")->rowCount() > 0;
    $userIdType = "INT(11)";
     if (!$userTableExists) {
        echo "<p style='color:orange'>⚠ Tabela 'usuarios' não existia. Criando...</p>";
        $pdo->exec("
            CREATE TABLE usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                empresa_id INT NOT NULL,
                username VARCHAR(100) NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                user_type VARCHAR(20) DEFAULT 'employee',
                plan VARCHAR(20) DEFAULT 'free',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    } else {
         $stmt = $pdo->query("DESCRIBE usuarios id");
         $col = $stmt->fetch(PDO::FETCH_ASSOC);
         $userIdType = strtoupper($col['Type']);
         echo "<p>ℹ️ Tabela 'usuarios' detectada. Tipo do ID: <strong>$userIdType</strong></p>";
    }

    // 3. RECRIAR Tabelas Filhas (Setores -> Cargos -> Usuario_Setor)
    
    // Drop para recriar limpo se necessário (ajuda a resolver constraints quebradas antigas)
    // Ordem inversa de dependência
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    // Comente as linhas abaixo se não quiser perder dados das tabelas novas! 
    // Como acabou de dar erro de criação, assumo que estão vazias ou inexistentes/quebradas.
    // $pdo->exec("DROP TABLE IF EXISTS usuario_setor");
    // $pdo->exec("DROP TABLE IF EXISTS permissoes_setor");
    // $pdo->exec("DROP TABLE IF EXISTS cargos");
    // $pdo->exec("DROP TABLE IF EXISTS setores");
    // $pdo->exec("DROP TABLE IF EXISTS modulos");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // MODULOS
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS modulos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        slug VARCHAR(50) NOT NULL UNIQUE,
        icone VARCHAR(50),
        descricao TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // SETORES (Com FK Dinâmica)
    // Extrai apenas o tipo base (INT, BIGINT) e se é UNSIGNED
    // Ex: "int(11) unsigned" -> "INT UNSIGNED" (ignora tamanho display em MySQL 8+ as vezes, mas manteremos simples)
    
    $cleanEmpresaIdType = $empresaIdType; // Confia no DESCRIBE direto
    
    $sqlSetores = "
    CREATE TABLE IF NOT EXISTS setores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        empresa_id $cleanEmpresaIdType NOT NULL,
        nome VARCHAR(100) NOT NULL,
        cor_hex VARCHAR(7) DEFAULT '#6c757d',
        responsavel_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sqlSetores);
    echo "<p style='color:green'>✓ Tabela 'setores' criada/verificada (FK compatível).</p>";

    // PERMISSOES_SETOR
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS permissoes_setor (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setor_id INT NOT NULL,
        modulo_id INT NOT NULL,
        nivel_acesso ENUM('leitura', 'escrita', 'admin') DEFAULT 'leitura',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (setor_id) REFERENCES setores(id) ON DELETE CASCADE,
        FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // CARGOS
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS cargos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setor_id INT NOT NULL,
        nome VARCHAR(100) NOT NULL,
        nivel_hierarquia INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (setor_id) REFERENCES setores(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "<p style='color:green'>✓ Tabela 'cargos' criada/verificada.</p>";

    // USUARIO_SETOR_VINCULO (FK Dinâmica para User)
    $cleanUserIdType = $userIdType;
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS usuario_setor (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id $cleanUserIdType NOT NULL,
        setor_id INT NOT NULL,
        cargo_id INT DEFAULT NULL,
        is_chefe BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (setor_id) REFERENCES setores(id) ON DELETE CASCADE,
        FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "<p style='color:green'>✓ Tabela 'usuario_setor' criada/verificada.</p>";

    // Seed Modules
    $count = $pdo->query("SELECT COUNT(*) FROM modulos")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("
        INSERT INTO modulos (nome, slug, icone, descricao) VALUES
        ('Estoque Inteligente', 'estoque', 'fas fa-boxes', 'Gestão de produtos, movimentações e inventário.'),
        ('Gestão Financeira', 'financeiro', 'fas fa-chart-line', 'Fluxo de caixa, DRE gerencial e contas.'),
        ('Recursos Humanos', 'rh', 'fas fa-users', 'Gestão de colaboradores, folhas e benefícios.'),
        ('CRM & Vendas', 'crm', 'fas fa-handshake', 'Gestão de clientes, leads e funil de vendas.'),
        ('Fiscal & Tributário', 'fiscal', 'fas fa-file-invoice-dollar', 'Emissão de NF-e e inteligência tributária.');
        ");
        echo "<p style='color:green'>✓ Módulos padrão inseridos.</p>";
    }

    echo "<h3 style='color:blue'>Reparo Concluído! Tente usar o sistema agora.</h3>";
    echo "<a href='admin/organizacao.php' class='btn'>Voltar para Admin</a>";

} catch (PDOException $e) {
    echo "<div style='background:#fdd; padding:10px; border:1px solid red; color:red'>";
    echo "<h3>ERRO: " . $e->getMessage() . "</h3>";
    echo "</div>";
}
?>
