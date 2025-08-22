<?php

class DoctorController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Build final query with parameters bound for debugging
     */
    private function buildFinalQuery($query, $params) {
        $finalQuery = $query;
        foreach ($params as $param) {
            if (is_string($param)) {
                $finalQuery = preg_replace('/\?/', "'" . addslashes($param) . "'", $finalQuery, 1);
            } else {
                $finalQuery = preg_replace('/\?/', $param, $finalQuery, 1);
            }
        }
        return $finalQuery;
    }
    
    public function Get($page = 1, $page_size = 10, $lang_code = 'vi', $filter = null) {
        try {
            // Validate and sanitize input parameters
            $page = max(1, (int)$page);
            $page_size = max(1, min(100, (int)$page_size)); // Limit max page size to 100
            $lang_code = htmlspecialchars($lang_code, ENT_QUOTES, 'UTF-8')=="en"?"en_us":"vi";
            
            $offset = ($page - 1) * $page_size;
            
            // Build dynamic query based on filter
            $whereClause = "WHERE d.status = 'published' 
                AND language_meta.lang_meta_code = ?
                AND language_meta.reference_type = 'Botble\\\\Doctor\\\\Models\\\\Doctor'";
            $params = [$lang_code];
            
            if ($filter && trim($filter) !== '') {
                $filter = htmlspecialchars(trim($filter), ENT_QUOTES, 'UTF-8');
                $filterPattern = '%' . $filter . '%';
                $whereClause .= " AND (d.name LIKE ? OR d.description LIKE ?)";
                $params[] = $filterPattern;
                $params[] = $filterPattern;
            }

            // Build the complete query for debugging
            $mainQuery = "
                SELECT  
                    JSON_UNQUOTE(JSON_EXTRACT(em.meta_value, '$[0]')) as empId, 
                    language_meta.lang_meta_code, 
                    d.id, 
                    d.name as doctor_name, 
                    d.description, 
                    d.status, 
                    d.sort_order, 
                    d.views, 
                    CONCAT('https://aih.com.vn/storage/', d.image) as image,
                    c.id as specialty_id, 
                    c.name as specialty_name, 
                    c.clinical_specialty_rid, 
                    d.created_at, 
                    d.updated_at,
                    JSON_UNQUOTE(JSON_EXTRACT(ac.meta_value, '$[0]')) as academic_rank, 
                    JSON_UNQUOTE(JSON_EXTRACT(a.meta_value, '$[0]')) as academic_degree_value,
                    JSON_UNQUOTE(JSON_EXTRACT(m.meta_value, '$[0]')) as medical_specialty,
                    JSON_UNQUOTE(JSON_EXTRACT(e.meta_value, '$[0]')) as experience,
                    REPLACE(JSON_UNQUOTE(JSON_EXTRACT(co.meta_value, '$[0]')), '<img src=\"/storage', '<img src=\"https://aih.com.vn/storage') as content_doctor
                FROM doctors d
                    INNER JOIN doctors_post_categories dc ON dc.doctor_id = d.id
                    INNER JOIN doctors_categories c ON c.id = dc.category_id
                    INNER JOIN language_meta ON language_meta.reference_id = d.id 
                    INNER JOIN meta_boxes AS em ON em.reference_id = d.id AND em.meta_key = 'empId'
                    LEFT JOIN meta_boxes AS ac ON ac.reference_id = d.id AND ac.meta_key = 'academic_rank'
                    LEFT JOIN meta_boxes AS a ON a.reference_id = d.id AND a.meta_key = 'academic_degree'
                    LEFT JOIN meta_boxes AS m ON m.reference_id = d.id AND m.meta_key = 'medical_specialty'
                    LEFT JOIN meta_boxes AS e ON e.reference_id = d.id AND e.meta_key = 'experience'
                    LEFT JOIN meta_boxes AS co ON co.reference_id = d.id AND co.meta_key = 'content_doctor'
                $whereClause
                ORDER BY d.sort_order DESC
                LIMIT ? OFFSET ?";

            // Get doctors list from database
            $doctors = $this->db->query($mainQuery, array_merge($params, [$page_size, $offset]));

            // Build count query
            $countQuery = "
                SELECT COUNT(DISTINCT d.id) as total 
                FROM doctors d
                    INNER JOIN doctors_post_categories dc ON dc.doctor_id = d.id
                    INNER JOIN doctors_categories c ON c.id = dc.category_id
                    INNER JOIN language_meta ON language_meta.reference_id = d.id 
                    INNER JOIN meta_boxes ON meta_boxes.reference_id = d.id AND meta_boxes.meta_key = 'empId'
                    LEFT JOIN meta_boxes AS a ON a.reference_id = d.id AND a.meta_key = 'academic_degree'
                    LEFT JOIN meta_boxes AS m ON m.reference_id = d.id AND m.meta_key = 'medical_specialty'
                    LEFT JOIN meta_boxes AS e ON e.reference_id = d.id AND e.meta_key = 'experience'
                    LEFT JOIN meta_boxes AS co ON co.reference_id = d.id AND co.meta_key = 'content_doctor'
                $whereClause";

            // Get total count
            $total_result = $this->db->queryOne($countQuery, $params);

            // Build final executed queries for debug
            $finalMainQuery = $this->buildFinalQuery($mainQuery, array_merge($params, [$page_size, $offset]));
            $finalCountQuery = $this->buildFinalQuery($countQuery, $params);

            $total = $total_result['total'] ?? count($doctors);
            
            $formatted_doctors = [];
            foreach ($doctors as $doctor) {
                $formatted_doctors[] = [
                    'id' => (int)$doctor['id'],
                    'employee_id' => $doctor['empId'] ?? '',
                    'name' => $doctor['doctor_name'] ?? 'Unknown Doctor',
                    'description' => $doctor['description'] ?? '',
                    'specialty_id' => (int)($doctor['specialty_id'] ?? 0),
                    'specialty_name' => $doctor['specialty_name'] ?? 'General',
                    'image' => $doctor['image'] ?? '',
                    'sort_order' => (int)($doctor['sort_order'] ?? 0),
                    'views' => (int)($doctor['views'] ?? 0),
                    'status' => $doctor['status'] ?? 'published',
                    'created_at' => $doctor['created_at'] ?? '',
                    'updated_at' => $doctor['updated_at'] ?? '',
                    'academic_rank' => $doctor['academic_rank'] ?? '',
                    'academic_degree' => $doctor['academic_degree_value'] ?? '',
                    'medical_specialty' => $doctor['medical_specialty'] ?? '',
                    'experience' => $doctor['experience'] ?? '',
                    'content_doctor' => $doctor['content_doctor'] ?? ''
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $formatted_doctors,
                'total' => (int)$total,
                'page' => $page,
                'page_size' => $page_size,
                'language' => $lang_code,
                'filter' => $filter ?? null
                // 'debug' => [
                //     'final_main_query' => $finalMainQuery
                    // ,'final_count_query' => $finalCountQuery,
                    // 'raw_main_query' => $mainQuery,
                    // 'raw_count_query' => $countQuery,
                    // 'parameters' => array_merge($params, [$page_size, $offset]),
                    // 'count_parameters' => $params,
                    // 'where_clause' => $whereClause
                //]
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'error' => 'Failed to retrieve doctors list',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function GetDoctorById($id) {
        try {
            // Validate and sanitize input
            $id = (int)$id;
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid doctor ID',
                    'error' => 'Doctor ID must be a positive integer'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $singleDoctorQuery = "
                        SELECT
                            JSON_UNQUOTE(JSON_EXTRACT(em.meta_value, '$[0]')) as empId, 
                            language_meta.lang_meta_code, 
                            d.id, 
                            d.name as doctor_name, 
                            d.description, 
                            d.status, 
                            d.sort_order, 
                            d.views, 
                            CONCAT('https://aih.com.vn/storage/', d.image) as image,
                            c.id as specialty_id, 
                            c.name as specialty_name, 
                            c.clinical_specialty_rid, 
                            d.created_at, 
                            d.updated_at,
                            JSON_UNQUOTE(JSON_EXTRACT(ac.meta_value, '$[0]')) as academic_rank,
                            JSON_UNQUOTE(JSON_EXTRACT(a.meta_value, '$[0]')) as academic_degree_value,
                            JSON_UNQUOTE(JSON_EXTRACT(m.meta_value, '$[0]')) as medical_specialty,
                            JSON_UNQUOTE(JSON_EXTRACT(e.meta_value, '$[0]')) as experience,
                            REPLACE(JSON_UNQUOTE(JSON_EXTRACT(co.meta_value, '$[0]')), '<img src=\"/storage', '<img src=\"https://aih.com.vn/storage') as content_doctor
                        FROM doctors d
                            INNER JOIN doctors_post_categories dc ON dc.doctor_id = d.id
                            INNER JOIN doctors_categories c ON c.id = dc.category_id
                            INNER JOIN language_meta ON language_meta.reference_id = d.id 
                            INNER JOIN meta_boxes AS em ON em.reference_id = d.id AND em.meta_key = 'empId'
                            INNER JOIN meta_boxes AS ac ON ac.reference_id = d.id AND ac.meta_key = 'academic_rank'
                            LEFT JOIN meta_boxes AS a ON a.reference_id = d.id AND a.meta_key = 'academic_degree'
                            LEFT JOIN meta_boxes AS m ON m.reference_id = d.id AND m.meta_key = 'medical_specialty'
                            LEFT JOIN meta_boxes AS e ON e.reference_id = d.id AND e.meta_key = 'experience'
                            LEFT JOIN meta_boxes AS co ON co.reference_id = d.id AND co.meta_key = 'content_doctor'
                        WHERE d.id = ?";
            
            $doctor = $this->db->queryOne($singleDoctorQuery, [$id]);
            
            // Build final executed query for debug
            $finalSingleQuery = $this->buildFinalQuery($singleDoctorQuery, [$id]);
                    
                    if ($doctor) {
                        echo json_encode([
                            'success' => true,
                            'data' => [
                            'id' => (int)$doctor['id'],
                            'employee_id' => $doctor['empId'] ?? '',
                            'name' => $doctor['doctor_name'] ?? 'Unknown Doctor',
                            'description' => $doctor['description'] ?? '',
                            'specialty_id' => (int)($doctor['specialty_id'] ?? 0),
                            'specialty_name' => $doctor['specialty_name'] ?? 'General',
                            'image' => $doctor['image'] ?? '',
                            'sort_order' => (int)($doctor['sort_order'] ?? 0),
                            'views' => (int)($doctor['views'] ?? 0),
                            'status' => $doctor['status'] ?? 'published',
                            'created_at' => $doctor['created_at'] ?? '',
                            'updated_at' => $doctor['updated_at'] ?? '',
                            'academic_rank' => $doctor['academic_rank'] ?? '',
                            'academic_degree' => $doctor['academic_degree_value'] ?? '',
                            'medical_specialty' => $doctor['medical_specialty'] ?? '',
                            'experience' => $doctor['experience'] ?? '',
                            'content_doctor' => $doctor['content_doctor'] ?? ''
                            ]
                            // 'debug' => [
                            //     'final_query' => $finalSingleQuery,
                            //     'raw_query' => $singleDoctorQuery,
                            //     'parameters' => [$id]
                            // ]
                        ], JSON_UNESCAPED_UNICODE);
                    } else {
                        http_response_code(404);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Doctor not found',
                            'error' => 'The requested doctor does not exist'
                        ], JSON_UNESCAPED_UNICODE);
                    }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'error' => 'Failed to retrieve doctor information',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
