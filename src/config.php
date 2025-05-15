<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
    function getDBConnection() {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbName = $_ENV['DB_NAME'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];
    
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbName";
        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return null;
        }
    }