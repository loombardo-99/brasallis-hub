
<?php

// Configurações do banco de dados (Suporte a Variaveis de Ambiente para Produção)
$host = getenv('DB_HOST') ?: '127.0.0.1';
$dbname = getenv('DB_NAME') ?: 'gerenciador_estoque';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

if (!defined('DB_HOST')) define('DB_HOST', $host);
if (!defined('DB_NAME')) define('DB_NAME', $dbname);
if (!defined('DB_USER')) define('DB_USER', $user);
if (!defined('DB_PASS')) define('DB_PASS', $pass);

?>
