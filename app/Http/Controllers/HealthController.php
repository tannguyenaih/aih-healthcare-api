<?php

class HealthController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function status() {
        $dbInfo = $this->db->getConnectionInfo();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'API is healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'server' => 'PHP ' . PHP_VERSION,
            'database' => $dbInfo,
            'response_time' => round((microtime(true) - API_START_TIME) * 1000, 2) . 'ms'
        ], JSON_UNESCAPED_UNICODE);
    }
    
    public function testDatabase() {
        $dbInfo = $this->db->getConnectionInfo();
        
        // Test simple query if connected
        $testResult = null;
        if ($this->db->isConnected()) {
            try {
                $testResult = $this->db->queryOne("SELECT 1 as test_value");
            } catch (Exception $e) {
                $testResult = ['error' => $e->getMessage()];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Database connection test',
            'connection_info' => $dbInfo,
            'test_query' => $testResult,
            'environment' => [
                'DB_HOST' => $_ENV['DB_HOST'] ?? 'not set',
                'DB_PORT' => $_ENV['DB_PORT'] ?? 'not set',
                'DB_DATABASE' => $_ENV['DB_DATABASE'] ?? 'not set',
                'DB_USERNAME' => $_ENV['DB_USERNAME'] ?? 'not set',
                'DB_PASSWORD' => !empty($_ENV['DB_PASSWORD']) ? '***set***' : 'not set'
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
