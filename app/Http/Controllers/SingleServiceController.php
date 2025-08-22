<?php

class SingleServiceController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get list of single services with pagination and filtering
     */
    public function Get($page = 1, $page_size = 10, $lang_code = 'vi', $filter = null) {
        try {
            // Validate and sanitize input parameters
            $page = max(1, (int)$page);
            $page_size = max(1, min(100, (int)$page_size)); // Limit max page size to 100
            $lang_code = htmlspecialchars($lang_code, ENT_QUOTES, 'UTF-8')=="en"?"en_us":"vi";
            $offset = ($page - 1) * $page_size;
            
            // Build dynamic query based on filter
            $whereClause = "WHERE dss.status = 'published' 
                AND lm.lang_meta_code = ?
                AND lm.reference_type = 'Botble\\\\Doctor\\\\Models\\\\SingleService'";
            $params = [$lang_code];
            
            if ($filter && trim($filter) !== '') {
                $filter = htmlspecialchars(trim($filter), ENT_QUOTES, 'UTF-8');
                $filterPattern = '%' . $filter . '%';
                $whereClause .= " AND dss.name LIKE ?";
                $params[] = $filterPattern;
            }

            // Get single services list from database
            $services = $this->db->query("
                SELECT 
                    dss.id, 
                    dss.name, 
                    dss.price, 
                    dss.category_id, 
                    dss.status, 
                    dss.created_at, 
                    dss.updated_at,
                    lm.lang_meta_code
                FROM doctor_single_service dss
                    INNER JOIN language_meta lm ON lm.reference_id = dss.id
                $whereClause
                ORDER BY dss.created_at DESC
                LIMIT ? OFFSET ?
            ", array_merge($params, [$page_size, $offset]));

            // Get total count
            $total_result = $this->db->queryOne("
                SELECT COUNT(DISTINCT dss.id) as total 
                FROM doctor_single_service dss
                    INNER JOIN language_meta lm ON lm.reference_id = dss.id
                $whereClause
            ", $params);

            $total = $total_result['total'] ?? count($services);
            
            $formatted_services = [];
            foreach ($services as $service) {
                $formatted_services[] = [
                    'id' => (int)$service['id'],
                    'name' => $service['name'] ?? '',
                    'price' => (float)($service['price'] ?? 0),
                    'category_id' => (int)($service['category_id'] ?? 0),
                    'status' => $service['status'] ?? 'active',
                    'language' => $service['lang_meta_code'] ?? $lang_code,
                    'created_at' => $service['created_at'] ?? '',
                    'updated_at' => $service['updated_at'] ?? ''
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $formatted_services,
                'total' => (int)$total,
                'page' => $page,
                'page_size' => $page_size,
                'language' => $lang_code,
                'filter' => $filter ?? null
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'error' => 'Failed to retrieve single services list',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Get single service by ID
     */
    public function GetById($id) {
        try {
            // Validate and sanitize input
            $id = (int)$id;
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid service ID',
                    'error' => 'Service ID must be a positive integer'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $service = $this->db->queryOne("
                SELECT 
                    dss.id, 
                    dss.name, 
                    dss.price, 
                    dss.category_id, 
                    dss.status, 
                    dss.created_at, 
                    dss.updated_at,
                    lm.lang_meta_code
                FROM doctor_single_service dss
                    INNER JOIN language_meta lm ON lm.reference_id = dss.id
                WHERE dss.id = ?
            ", [$id]);
                    
            if ($service) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'id' => (int)$service['id'],
                        'name' => $service['name'] ?? '',
                        'price' => (float)($service['price'] ?? 0),
                        'category_id' => (int)($service['category_id'] ?? 0),
                        'status' => $service['status'] ?? 'active',
                        'language' => $service['lang_meta_code'] ?? 'vi',
                        'created_at' => $service['created_at'] ?? '',
                        'updated_at' => $service['updated_at'] ?? ''
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Single service not found',
                    'error' => 'The requested single service does not exist'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'error' => 'Failed to retrieve single service information',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
