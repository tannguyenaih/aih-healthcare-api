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
            select c.id, c.name, c.clinical_specialty_rid, c.status, c.order, c.created_at, c.updated_at, language_meta.lang_meta_code,
                case  When c.name = N'Khoa nhi' then N'Nhi'
                When c.name = N'Khoa phụ sản' then N'Sản - Phụ khoa'
                When c.name = N'Khoa ngoại tổng hợp' then N'Ngoại tổng quát'
                When c.name = N'Khoa Thận Niệu - Nam khoa' then N'Tiết niệu & Nam khoa'
                When c.name = N'Khoa Tiêu hóa - Gan mật' then N'Tiêu hóa'
                When c.name = N'Trung Tâm Ung Bướu' then N'Ung bướu'
                When c.name = N'Khoa nội tổng quát' then N'Nội tổng hợp'
                When c.name = N'Phòng khám răng hàm mặt' then N'Răng (Nha)'
                When c.name = N'Khoa nội tiết' then N'Nội tiết'
                When c.name = N'Đơn vị hồi sức sơ sinh' then N'Chăm sóc sơ sinh'
                When c.name = N'Khoa mắt' then N'Mắt'
                When c.name = N'Khoa Chấn thương chỉnh hình' then N'Chấn thương chỉnh hình'
                When c.name = N'Khoa tim mạch' then N'Tim mạch'
                When c.name = N'Khoa Vật lý trị liệu - Phục hồi chức năng' then N'PHCN/Vật lý trị liệu'
                When c.name = N'Khoa thẩm mỹ nội khoa - Chống lão hóa' then N'Phẫu thuật thẩm mỹ'
                When c.name = N'Khoa tai mũi họng' then N'Tai mũi họng'
                When c.name = N'Khoa chẩn đoán hình ảnh' then N'Chẩn đoán hình ảnh'
                When c.name = N'Khoa xét nghiệm' then N'Xét nghiệm'
                When c.name = N'Khoa khám bệnh & kiểm tra sức khỏe tổng quát' then N'Khám sức khỏe'
                When c.name = N'Khoa gây mê hồi sức' then N'Gây mê'
                When c.name = N'Khoa dinh dưỡng' then N'Dinh dưỡng'
                When c.name = N'Obstetrics And Gynecology' then N'Obstetrics And Gynecology'
                When c.name = N'Pediatrics' then N'Pediatrics'
                When c.name = N'General Surgery' then N'General Surgery'
                When c.name = N'Urology & Andrology' then N'Urology & Andrology'
                When c.name = N'Gastroenterology - Hepatology' then N'Gastroenterology'
                When c.name = N'Oncology Unit' then N'Oncology'
                When c.name = N'Endocrinology' then N'Endocrinology'
                When c.name = N'Neonatal Intensive Care Unit' then N'Neonatal Intensive Care Unit'
                When c.name = N'Dental Clinic' then N'Dentistry'
                When c.name = N'Ophthalmology' then N'Ophthalmology'
                When c.name = N'Cardiology' then N'Cardiology'
                When c.name = N'Physiotherapy - Rehabilitation' then N'Rehabilitation & Physiotherapy'
                When c.name = N'Internal Medicine' then N'General Internal Medicine'
                When c.name = N'Cosmetic Care' then N'Plastic Surgery (Cosmetic)'
                When c.name = N'Otorhinolaryngology (ENT)' then N'ENT'
                When c.name = N'Imaging' then N'Imaging (Radiology)'
                When c.name = N'Laboratory' then N'Laboratory'
                When c.name = N'Outpatient & Health Check-up' then N'GP & Health Check'
                When c.name = N'Anesthesiology' then N'Anesthesiology'
                When c.name = N'Nutrition' then N'Nutrition'
                When c.name = N'Orthopedics' then N'Orthopedic' 
                else c.name end as value
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
                'clinical_specialty_rid' => $specialty['clinical_specialty_rid'],
                'name' => $specialty['name'] ?? 'Unknown Specialty',
                'language' => $specialty['lang_meta_code'] ?? $lang_code,
                'value' => $specialty['value'] ?? ''
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
            select c.id, c.name, c.clinical_specialty_rid, c.status, c.order, c.created_at, c.updated_at ,
                case  When c.name = N'Khoa nhi' then N'Nhi'
                When c.name = N'Khoa phụ sản' then N'Sản - Phụ khoa'
                When c.name = N'Khoa ngoại tổng hợp' then N'Ngoại tổng quát'
                When c.name = N'Khoa Thận Niệu - Nam khoa' then N'Tiết niệu & Nam khoa'
                When c.name = N'Khoa Tiêu hóa - Gan mật' then N'Tiêu hóa'
                When c.name = N'Trung Tâm Ung Bướu' then N'Ung bướu'
                When c.name = N'Khoa nội tổng quát' then N'Nội tổng hợp'
                When c.name = N'Phòng khám răng hàm mặt' then N'Răng (Nha)'
                When c.name = N'Khoa nội tiết' then N'Nội tiết'
                When c.name = N'Đơn vị hồi sức sơ sinh' then N'Chăm sóc sơ sinh'
                When c.name = N'Khoa mắt' then N'Mắt'
                When c.name = N'Khoa Chấn thương chỉnh hình' then N'Chấn thương chỉnh hình'
                When c.name = N'Khoa tim mạch' then N'Tim mạch'
                When c.name = N'Khoa Vật lý trị liệu - Phục hồi chức năng' then N'PHCN/Vật lý trị liệu'
                When c.name = N'Khoa thẩm mỹ nội khoa - Chống lão hóa' then N'Phẫu thuật thẩm mỹ'
                When c.name = N'Khoa tai mũi họng' then N'Tai mũi họng'
                When c.name = N'Khoa chẩn đoán hình ảnh' then N'Chẩn đoán hình ảnh'
                When c.name = N'Khoa xét nghiệm' then N'Xét nghiệm'
                When c.name = N'Khoa khám bệnh & kiểm tra sức khỏe tổng quát' then N'Khám sức khỏe'
                When c.name = N'Khoa gây mê hồi sức' then N'Gây mê'
                When c.name = N'Khoa dinh dưỡng' then N'Dinh dưỡng'
                When c.name = N'Obstetrics And Gynecology' then N'Obstetrics And Gynecology'
                When c.name = N'Pediatrics' then N'Pediatrics'
                When c.name = N'General Surgery' then N'General Surgery'
                When c.name = N'Urology & Andrology' then N'Urology & Andrology'
                When c.name = N'Gastroenterology - Hepatology' then N'Gastroenterology'
                When c.name = N'Oncology Unit' then N'Oncology'
                When c.name = N'Endocrinology' then N'Endocrinology'
                When c.name = N'Neonatal Intensive Care Unit' then N'Neonatal Intensive Care Unit'
                When c.name = N'Dental Clinic' then N'Dentistry'
                When c.name = N'Ophthalmology' then N'Ophthalmology'
                When c.name = N'Cardiology' then N'Cardiology'
                When c.name = N'Physiotherapy - Rehabilitation' then N'Rehabilitation & Physiotherapy'
                When c.name = N'Internal Medicine' then N'General Internal Medicine'
                When c.name = N'Cosmetic Care' then N'Plastic Surgery (Cosmetic)'
                When c.name = N'Otorhinolaryngology (ENT)' then N'ENT'
                When c.name = N'Imaging' then N'Imaging (Radiology)'
                When c.name = N'Laboratory' then N'Laboratory'
                When c.name = N'Outpatient & Health Check-up' then N'GP & Health Check'
                When c.name = N'Anesthesiology' then N'Anesthesiology'
                When c.name = N'Nutrition' then N'Nutrition'
                When c.name = N'Orthopedics' then N'Orthopedic' 
                else c.name end as value
            from doctors_categories c 
            WHERE c.id = ?
            AND c.status = 'published'
        ", [(int)$id]);
        
        header('Content-Type: application/json');
        if ($specialty) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $specialty['id'],
                    'clinical_specialty_rid' => $specialty['clinical_specialty_rid'],
                    'name' => $specialty['name'] ?? 'Unknown Specialty',
                    'value' => $specialty['value'] ?? ''
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
