<?php

class Database {
    private static $instance = null;
    private $connection;
    private $connectionType = 'None';
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        // Database configuration
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbname = $_ENV['DB_DATABASE'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
        $charset = 'utf8mb4';
        
        // Try PDO first
        $dsn = "mysql:host=$host:$port;dbname=$dbname;charset=$charset";
        
        try {
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $this->connectionType = 'PDO';
        } catch (PDOException $e) {
            // Try MySQLi as fallback
            try {
                $this->connection = new mysqli($host, $username, $password, $dbname, (int)$port);
                if ($this->connection->connect_error) {
                    throw new Exception($this->connection->connect_error);
                }
                $this->connection->set_charset($charset);
                $this->connectionType = 'MySQLi';
            } catch (Exception $e2) {
                // Fallback to sample data if database not available
                $this->connection = null;
                $this->connectionType = 'None';
                // Log connection error for debugging
                error_log("Database connection failed: " . $e->getMessage() . " / " . $e2->getMessage());
            }
        }
    }
    
    public function query($sql, $params = []) {
        try {
            if ($this->connectionType === 'PDO') {
                $stmt = $this->connection->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll();
            } else if ($this->connectionType === 'MySQLi') {
                // Convert ? placeholders to mysqli format
                if (!empty($params)) {
                    $types = str_repeat('s', count($params)); // assume all strings
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    return $result->fetch_all(MYSQLI_ASSOC);
                } else {
                    $result = $this->connection->query($sql);
                    return $result->fetch_all(MYSQLI_ASSOC);
                }
            }
        } catch (Exception $e) {
            return null;
        }

        return null;
    }
    
    public function queryOne($sql, $params = []) {
       
        try {
            if ($this->connectionType === 'PDO') {
                $stmt = $this->connection->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetch();
            } else if ($this->connectionType === 'MySQLi') {
                // Convert ? placeholders to mysqli format
                if (!empty($params)) {
                    $types = str_repeat('s', count($params)); // assume all strings
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    return $result->fetch_assoc();
                } else {
                    $result = $this->connection->query($sql);
                    return $result->fetch_assoc();
                }
            }
        } catch (Exception $e) {
            return null;
        }

        return null;
    }
    
    
    public function isConnected() {
        return $this->connection !== null;
    }
    
    public function getConnectionInfo() {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbname = $_ENV['DB_DATABASE'];
        $username = $_ENV['DB_USERNAME'];
        
        return [
            'host' => $host,
            'port' => $port,
            'database' => $dbname,
            'username' => $username,
            'connected' => $this->isConnected(),
            'connection_type' => $this->connectionType,
            'dsn' => "mysql:host=$host:$port;dbname=$dbname;charset=utf8mb4"
        ];
    }
}
