<?php
// reset_super_admin.php
require 'includes/db_config.php';
require 'includes/funcoes.php';

echo "<h1>Reset Super Admin</h1>";

try {
    $conn = connect_db();
    $conn->beginTransaction();

    // 1. Delete existing
    $stmtDel = $conn->prepare("DELETE FROM usuarios WHERE user_type = 'super_admin'");
    $stmtDel->execute();
    echo "Existing super admins deleted.<br>";

    // 2. Create New
    $pass = 'SuperAdmin2026!';
    $hashed = password_hash($pass, PASSWORD_DEFAULT);
    
    // Check if empresa_id 0 exists or if FK prevents it.
    // If FK constraint exists on empresa_id, we might need a dummy company 0 or similar.
    // Assuming empresa_id is NOT nullable but allows 0 if strictly not constrained to non-existent ID.
    // Let's check table structure first. If FK to empresas(id), then ID 0 must exist.
    // Standard approach: Check if ID 0 exists in empresas. If not, insert it (System Company).
    
    $checkEmp = $conn->query("SELECT id FROM empresas WHERE id = 0");
    if (!$checkEmp->fetch()) {
        // Create System Company (ID 0 hack if auto_increment allows, otherwise ID 1 is system)
        // Usually auto_increment starts at 1. forcing 0 might fail on some SQL modes.
        // Let's try inserting a dummy company first for the super admin to belong to contextually, 
        // OR simply set to NULL if allowed.
        // Let's assume NULL is allowed?
        // Checking previous code: `INSERT INTO usuarios ...`
        // I will try NULL first. If fails, I will use a valid ID.
        // BUT user said "registered in a company record". He wants it separate.
        // If DB schema forces not null, I'll create a "System Admin" company.
    }
    
    // Attempt insert with NULL for now. 
    // If it fails, I'll catch and try with a new Company "ADMINISTRACAO".
} catch (Exception $e) {
    $conn->rollBack();
    die("Error setup: " . $e->getMessage());
}

try {
    // Try allow NULL
    $stmt = $conn->prepare("INSERT INTO usuarios (username, email, password, user_type, empresa_id) VALUES (?, ?, ?, 'super_admin', ?)");
    // Try NULL
    try {
        $stmt->execute(['superadmin', 'superadmin@sistema.com', $hashed, null]);
        echo "Super Admin created with NULL empresa_id.<br>";
    } catch (PDOException $e) {
        // If NULL fails (likely NOT NULL constraint), create a placeholder company
        echo "NULL empresa_id failed. Creating System Company...<br>";
        
        $stmtEmp = $conn->prepare("INSERT INTO empresas (name, owner_user_id, ai_plan, created_at) VALUES ('SISTEMA CENTRAL', 0, 'enterprise', NOW())");
        $stmtEmp->execute();
        $empId = $conn->lastInsertId();
        
        $stmt->execute(['superadmin', 'superadmin@sistema.com', $hashed, $empId]);
        echo "Super Admin created with Company ID $empId (System Placeholder).<br>";
    }

    $conn->commit();
    
    echo "<h2>Success!</h2>";
    echo "User: <strong>superadmin@sistema.com</strong><br>";
    echo "Pass: <strong>$pass</strong><br>";
    echo "<a href='login.php'>Go to Login</a>";

} catch (Exception $e) {
    $conn->rollBack();
    echo "Error inserting user: " . $e->getMessage();
}
?>
