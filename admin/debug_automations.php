<?php

echo "<pre>"; // Usando <pre> para formatar a saída

include_once '../includes/cabecalho.php';
require_once '../includes/funcoes.php';

echo "Iniciando depuração das automações...\n\n";

$conn = connect_db();

if (!$conn) {
    die("FALHA AO CONECTAR COM O BANCO DE DADOS!\n");
}

echo "Conexão com o banco de dados bem-sucedida.\n";

// --- Lógica de depuração para Estoque Baixo ---
echo "\n--- VERIFICANDO ESTOQUE BAIXO ---\n";
try {
    $stmt_low_stock = $conn->query("SELECT id, name, quantity, minimum_stock FROM produtos WHERE quantity <= minimum_stock");
    $low_stock_products = $stmt_low_stock->fetchAll(PDO::FETCH_ASSOC);

    if (empty($low_stock_products)) {
        echo "Nenhum produto com estoque baixo encontrado.\n";
    } else {
        echo "Encontrados " . count($low_stock_products) . " produto(s) com estoque baixo.\n";
        foreach ($low_stock_products as $product) {
            echo "\nProcessando produto: '" . htmlspecialchars($product['name']) . "' (ID: " . $product['id'] . ")\n";
            echo "  - Quantidade: " . $product['quantity'] . ", Mínimo: " . $product['minimum_stock'] . "\n";

            $stmt_check = $conn->prepare("SELECT id FROM notificacoes WHERE product_id = :product_id AND type = 'low_stock' AND is_read = 0");
            $stmt_check->execute(['product_id' => $product['id']]);

            if ($stmt_check->rowCount() > 0) {
                echo "  - AVISO: Uma notificação de estoque baixo não lida já existe para este produto. Nenhuma nova notificação será criada.\n";
            } else {
                echo "  - OK: Nenhuma notificação ativa encontrada. Criando nova notificação...\n";
                $message = "Estoque baixo para o produto: <b>" . htmlspecialchars($product['name']) . "</b>. Quantidade atual: " . $product['quantity'] . ", mínimo: " . $product['minimum_stock'] . ".";
                $stmt_insert = $conn->prepare("INSERT INTO notificacoes (type, message, product_id)");
                $stmt_insert->execute(['message' => $message, 'product_id' => $product['id']]);
                echo "  - SUCESSO: Notificação criada e salva no banco de dados.\n";
            }
        }
    }
} catch (PDOException $e) {
    echo "ERRO na automação de estoque baixo: " . $e->getMessage() . "\n";
}

// --- Lógica de depuração para Vencimento ---
echo "\n--- VERIFICANDO PRODUTOS PRÓXIMOS AO VENCIMENTO (30 dias) ---\n";
try {
    $threshold_date = date('Y-m-d', strtotime('+30 days'));
    $stmt_expiring = $conn->prepare("SELECT id, name, validade FROM products WHERE validade IS NOT NULL AND validade BETWEEN CURDATE() AND :threshold_date");
    $stmt_expiring->execute(['threshold_date' => $threshold_date]);
    $expiring_products = $stmt_expiring->fetchAll(PDO::FETCH_ASSOC);

    if (empty($expiring_products)) {
        echo "Nenhum produto próximo ao vencimento encontrado.\n";
    } else {
        echo "Encontrados " . count($expiring_products) . " produto(s) próximo(s) ao vencimento.\n";
        foreach ($expiring_products as $product) {
            $formatted_date = date('d/m/Y', strtotime($product['validade']));
            echo "\nProcessando produto: '" . htmlspecialchars($product['name']) . "' (ID: " . $product['id'] . ")\n";
            echo "  - Data de Validade: " . $formatted_date . "\n";

            $stmt_check = $conn->prepare("SELECT id FROM notifications WHERE product_id = :product_id AND type = 'nearing_expiration' AND is_read = 0");
            $stmt_check->execute(['product_id' => $product['id']]);

            if ($stmt_check->rowCount() > 0) {
                echo "  - AVISO: Uma notificação de vencimento não lida já existe para este produto. Nenhuma nova notificação será criada.\n";
            } else {
                echo "  - OK: Nenhuma notificação ativa encontrada. Criando nova notificação...\n";
                $message = "Produto próximo ao vencimento: <b>" . htmlspecialchars($product['name']) . "</b>. Vence em: " . $formatted_date . ".";
                $stmt_insert = $conn->prepare("INSERT INTO notifications (type, message, product_id) VALUES ('nearing_expiration', :message, :product_id)");
                $stmt_insert->execute(['message' => $message, 'product_id' => $product['id']]);
                echo "  - SUCESSO: Notificação criada e salva no banco de dados.\n";
            }
        }
    }
} catch (PDOException $e) {
    echo "ERRO na automação de vencimento: " . $e->getMessage() . "\n";
}

echo "\nDepuração concluída.\n";

include_once '../includes/rodape.php';

echo "</pre>";
