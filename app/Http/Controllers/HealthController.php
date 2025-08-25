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
            // 'database' => $dbInfo,
            'response_time' => round((microtime(true) - API_START_TIME) * 1000, 2) . 'ms'
        ], JSON_UNESCAPED_UNICODE);
    }
}
