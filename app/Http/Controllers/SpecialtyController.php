<?php

class SpecialtyController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function Get($lang_code = 'vi', $filter = null) {
        $lang_code = htmlspecialchars($lang_code, ENT_QUOTES, 'UTF-8') == "en" ? "en_us" : "vi";
        $filter = htmlspecialchars($filter, ENT_QUOTES, 'UTF-8');

        // Build dynamic query based on filter
        $whereClause = "WHERE c.status = 'published' 
            AND language_meta.reference_type = 'Botble\\\\Doctor\\\\Models\\\\Category'
            AND language_meta.lang_meta_code = ?
            ";
        $params = [$lang_code];
        
        if ($filter && trim($filter) !== '') {
            $filter = htmlspecialchars(trim($filter), ENT_QUOTES, 'UTF-8');
            $filterPattern = '%' . $filter . '%';
            $whereClause .= " AND (c.name LIKE ?)";
            $params[] = $filterPattern;
        }

        $specialties = $this->db->query("
            select c.id, c.name, c.clinical_specialty_rid, c.status, c.order, c.created_at, c.updated_at, language_meta.lang_meta_code
            from doctors_categories c
            INNER JOIN language_meta ON language_meta.reference_id = c.id
            $whereClause
            ORDER BY c.order;
        ", $params);
        // echo ($whereClause . ' Params: ' . json_encode($params));

        $formatted_specialties = [];
        foreach ($specialties as $specialty) {
            $formatted_specialties[] = [
                'id' => (int)$specialty['id'],
                'clinical_specialty_rid' => (int)$specialty['clinical_specialty_rid'],
                'name' => $specialty['name'] ?? 'Unknown Specialty',
                'language' => $specialty['lang_meta_code'] ?? $lang_code
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $formatted_specialties,
            'language' => $lang_code
        ], JSON_UNESCAPED_UNICODE);
    }
    
    public function GetById($id) {
        $specialty = $this->db->queryOne("
            select c.id, c.name, c.clinical_specialty_rid, c.status, c.order, c.created_at, c.updated_at from doctors_categories c
            WHERE c.id = ?
            AND c.status = 'published'
        ", [(int)$id]);
        
        header('Content-Type: application/json');
        if ($specialty) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => (int)$specialty['id'],
                    'clinical_specialty_rid' => (int)$specialty['clinical_specialty_rid'],
                    'name' => $specialty['name'] ?? 'Unknown Specialty'
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Specialty not found',
                'error' => 'The requested specialty does not exist'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
