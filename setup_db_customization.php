<?php
// setup_db_customization.php
require_once __DIR__ . '/includes/funcoes.php';

try {
    $conn = connect_db();
    if (!$conn) {
        die("Falha na conexão com o banco de dados.");
    }

    echo "Conectado com sucesso. Iniciando migração...\n";

    // 1. Tabela dashboard_layouts
    $sqlTable = "CREATE TABLE IF NOT EXISTS dashboard_layouts (
        user_id INT NOT NULL,
        layout_json TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sqlTable);
    echo "[OK] Tabela 'dashboard_layouts' verificada/criada.\n";

    // 2. Colunas na tabela empresas
    // Helper para adicionar coluna se não existir
    function addColumnIfNotExists($pdo, $table, $column, $definition) {
        try {
            $check = $pdo->query("SHOW COLUMNS FROM $table LIKE '$column'");
            if ($check->rowCount() == 0) {
                $pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
                echo "[OK] Coluna '$column' adicionada em '$table'.\n";
            } else {
                echo "[SKIP] Coluna '$column' já existe em '$table'.\n";
            }
        } catch (PDOException $e) {
            echo "[ERRO] Falha ao adicionar coluna $column: " . $e->getMessage() . "\n";
        }
    }

    addColumnIfNotExists($conn, 'empresas', 'branding_primary_color', "VARCHAR(7) DEFAULT '#2563eb'");
    addColumnIfNotExists($conn, 'empresas', 'branding_secondary_color', "VARCHAR(7) DEFAULT '#1e293b'");
    addColumnIfNotExists($conn, 'empresas', 'branding_bg_style', "VARCHAR(50) DEFAULT 'original'");

    echo "\nMigração concluída com sucesso!";

} catch (PDOException $e) {
    echo "Erro Fatal: " . $e->getMessage();
}
?>
