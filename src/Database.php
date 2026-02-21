<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $conn;

    // Deprecated: Use Dependency Injection via bootstrap.php instead
    private function __construct()
    {
        require_once __DIR__ . '/../includes/db_config.php';

        try {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }
    }

    // Deprecated: Use Dependency Injection via bootstrap.php instead
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
