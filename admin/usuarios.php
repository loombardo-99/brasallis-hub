<?php
/**
 * Shim: admin/usuarios.php
 * Serve como ponte para o UserController moderno dentro da estrutura legada.
 */

// Carrega o container da aplicação
$container = require_once __DIR__ . '/../bootstrap/app.php';

use App\Core\Request;
use App\Core\Response;
use App\Modules\RH\Controllers\UserController;

try {
    // Resolve as dependências da requisição e do controller através do Container
    $request  = $container->make(Request::class);
    $response = $container->make(Response::class);
    $controller = $container->make(UserController::class);

    $uri = $_SERVER['REQUEST_URI'];
    
    // Roteamento básico no Shim
    if (strpos($uri, '/store') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->store($request, $response);
    } elseif (preg_match('/\/delete\/(\d+)/', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->destroy($request, $response, ['id' => $matches[1]]);
    } else {
        // Executa a ação padrão (listagem de usuários)
        $controller->index($request, $response);
    }
} catch (Throwable $e) {
    // Caso ocorra erro na resolução, exibe de forma amigável (ou log)
    error_log("Erro no Shim Usuarios: " . $e->getMessage());
    http_response_code(500);
    echo "<h1>Erro Interno</h1><p>Não foi possível carregar o módulo de Equipe.</p>";
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo "<div style='background: #fee; padding: 15px; border: 1px solid #f00; border-radius: 8px; font-family: monospace;'>";
        echo "<strong>Mensagem:</strong> " . $e->getMessage() . "<br><br>";
        echo "<strong>Local:</strong> " . $e->getFile() . " na linha " . $e->getLine() . "<br><br>";
        echo "<strong>Stack Trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    }
}
