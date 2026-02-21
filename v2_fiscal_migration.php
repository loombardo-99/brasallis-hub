<?php
// v2_fiscal_migration.php
require_once __DIR__ . '/includes/funcoes.php';

try {
    $conn = connect_db();
    echo "Iniciando migração fiscal V2...\n";

    // Columns to add
    $columns = [
        "icms_base DECIMAL(10,2) DEFAULT 0.00",
        "icms_valor DECIMAL(10,2) DEFAULT 0.00",
        "ipi_valor DECIMAL(10,2) DEFAULT 0.00",
        "pis_valor DECIMAL(10,2) DEFAULT 0.00",
        "cofins_valor DECIMAL(10,2) DEFAULT 0.00"
    ];

    foreach ($columns as $col) {
        try {
            // Check if column exists is hard in raw SQL without querying schema, 
            // but harmless to try adding if we catch duplicate column error.
            // Better: Query information_schema
            $colName = explode(' ', $col)[0];
            
            // This check is specific to MySQL
            $check = $conn->query("SHOW COLUMNS FROM fiscal_notas LIKE '$colName'");
            
            if ($check->rowCount() == 0) {
                $sql = "ALTER TABLE fiscal_notas ADD COLUMN $col";
                $conn->exec($sql);
                echo "Coluna adicionada: $colName\n";
            } else {
                echo "Coluna ja existe: $colName\n";
            }
        } catch (PDOException $e) {
            echo "Erro ao adicionar $col: " . $e->getMessage() . "\n";
        }
    }

    echo "Migração concluída com sucesso!\n";

} catch (Exception $e) {
    die("Erro crítico: " . $e->getMessage());
}
