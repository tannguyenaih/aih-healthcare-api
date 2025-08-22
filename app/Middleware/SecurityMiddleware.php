<?php

class SecurityMiddleware {
    
    public static function rateLimiting() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_" . md5($ip);
        $limit = 60; // requests per minute
        $window = 60; // seconds
        
        // Simple file-based rate limiting
        $cacheDir = __DIR__ . '/../storage/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . '/' . $key . '.txt';
        $now = time();
        
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            $requests = array_filter($data['requests'], function($timestamp) use ($now, $window) {
                return ($now - $timestamp) < $window;
            });
            
            if (count($requests) >= $limit) {
                http_response_code(429);
                echo json_encode([
                    'success' => false,
                    'message' => 'Rate limit exceeded',
                    'error' => 'Too many requests. Please try again later.'
                ], JSON_UNESCAPED_UNICODE);
                return false;
            }
            
            $requests[] = $now;
        } else {
            $requests = [$now];
        }
        
        file_put_contents($cacheFile, json_encode(['requests' => $requests]));
        return true;
    }
    
    public static function inputValidation() {
        // Sanitize all inputs
        $_GET = array_map('htmlspecialchars', $_GET);
        $_POST = array_map('htmlspecialchars', $_POST);
        
        // Check for SQL injection patterns
        $dangerous_patterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
            '/[\'";]/',
            '/--/',
            '/\/\*/',
            '/\*\//'
        ];
        
        $input_string = http_build_query($_GET) . http_build_query($_POST);
        
        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $input_string)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid input detected',
                    'error' => 'Malicious input patterns detected'
                ], JSON_UNESCAPED_UNICODE);
                return false;
            }
        }
        
        return true;
    }
    
    public static function corsHeaders() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        return true;
    }
    
    public static function securityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Content-Security-Policy: default-src \'self\'');
        
        return true;
    }
}
