<?php

class PackageController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get packages by specialty/category
     * Package List - Danh sách các gói dịch vụ tổng thể theo chuyên khoa
     */
    public function GetPackageBySpecialty($category_id, $lang_code = 'vi') {
        try {
            //echo "GetPackageBySpecialty called with category_id: $category_id\n";
            // Validate and sanitize input
            $category_id = (int)$category_id;
            $lang_code = htmlspecialchars($lang_code, ENT_QUOTES, 'UTF-8')=="en"?"en_us":"vi";
            
            if ($category_id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid category ID',
                    'error' => 'Category ID must be a positive integer'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $packages = $this->db->query("
                SELECT 
                    dsp.id, 
                    dsp.cate_id,
                    dsp.name, 
                    dsp.description, 
                    CONCAT('https://aih.com.vn/storage/', dsp.image) as image, 
                    dsp.status, 
                    dsp.category_id, 
                    dsp.created_at, 
                    dsp.updated_at, 
                    c.name as category_name, 
                    c.clinical_specialty_rid, 
                    lm.lang_meta_code  
                FROM doctor_service_packages dsp
                    INNER JOIN doctors_categories c ON c.id = dsp.category_id
                    INNER JOIN language_meta lm ON lm.reference_id = dsp.id 
                WHERE lm.lang_meta_code = ?
                    AND lm.reference_type = 'Botble\\\\Doctor\\\\Models\\\\ServicePackage'
                    AND dsp.category_id = ?
                    AND dsp.status = 'published'
                ORDER BY dsp.created_at DESC
            ", [$lang_code, $category_id]);
            
            $formatted_packages = [];
            foreach ($packages as $package) {
                $formatted_packages[] = [
                    'id' => (int)$package['id'],
                    'cate_id' => (int)($package['cate_id'] ?? 0),
                    'name' => $package['name'] ?? '',
                    'description' => $package['description'] ?? '',
                    'image' => $package['image'] ?? '',
                    'status' => $package['status'] ?? 'active',
                    'category_id' => (int)$package['category_id'],
                    'category_name' => $package['category_name'] ?? '',
                    'clinical_specialty_rid' => $package['clinical_specialty_rid'] ?? '',
                    'language' => $package['lang_meta_code'] ?? $lang_code,
                    'created_at' => $package['created_at'] ?? '',
                    'updated_at' => $package['updated_at'] ?? ''
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $formatted_packages,
                'total' => count($formatted_packages),
                'category_id' => $category_id,
                'language' => $lang_code
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'error' => 'Failed to retrieve packages by specialty',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Get list of package services by package ID
     * Services of package - Danh sách chi tiết gói dịch vụ theo package
     */
    public function GetListPackageServicesByPackageId($package_id, $lang_code = 'vi') {
        try {
            // Validate and sanitize input
            $package_id = (int)$package_id;
            $lang_code = htmlspecialchars($lang_code, ENT_QUOTES, 'UTF-8')=="en"?"en_us":"vi";

            if ($package_id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid package ID',
                    'error' => 'Package ID must be a positive integer'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $services = $this->db->query("
                SELECT  
                    ds.id, 
                    ds.cate_id, 
                    ds.name,
                    ds.description, 
                    ds.content, 
                    ds.price, 
                    ds.duration, 
                    ds.status, 
                    ds.created_at, 
                    ds.updated_at,
                    CONCAT('https://aih.com.vn/storage/', ds.image) as image, 
                    lm.lang_meta_code  
                FROM doctor_services ds 
                    INNER JOIN doctor_service_packages dsp ON dsp.id = ds.service_package_id
                    INNER JOIN language_meta lm ON lm.reference_id = ds.id
                WHERE ds.service_package_id = ?
                    AND lm.lang_meta_code = ?
                    AND lm.reference_type = 'Botble\\\\Doctor\\\\Models\\\\Service'
                    AND ds.status = 'published'
                ORDER BY ds.created_at DESC
            ", [$package_id, $lang_code]);
            
            $formatted_services = [];
            foreach ($services as $service) {
                $formatted_services[] = [
                    'id' => (int)$service['id'],
                    'cate_id' => (int)($service['cate_id'] ?? 0),
                    'name' => $service['name'] ?? '',
                    'description' => $service['description'] ?? '',
                    'content' => $service['content'] ?? '',
                    'price' => (float)($service['price'] ?? 0),
                    'duration' => $service['duration'] ?? '',
                    'status' => $service['status'] ?? 'active',
                    'image' => $service['image'] ?? '',
                    'language' => $service['lang_meta_code'] ?? $lang_code,
                    'created_at' => $service['created_at'] ?? '',
                    'updated_at' => $service['updated_at'] ?? ''
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $formatted_services,
                'total' => count($formatted_services),
                'package_id' => $package_id,
                'language' => $lang_code
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'error' => 'Failed to retrieve package services',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Get list of service items by package service ID
     * Detail of Services of package - Chi tiết dịch vụ trong gói
     */
    public function GetListServiceItemsByPackageServiceId($package_service_id) {
        try {
            // Validate and sanitize input
            $package_service_id = (int)$package_service_id;
            
            if ($package_service_id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid package service ID',
                    'error' => 'Package service ID must be a positive integer'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Complex query to get headers and items separately, then combine
            $service_items = $this->db->query("
                SELECT 
                    id, 
                    cate_id, 
                    service_id, 
                    package_id, 
                    title, 
                    parent_group_id, 
                    price, 
                    category_id,
                    status, 
                    created_at, 
                    updated_at, 
                    service_content, 
                    sort_order,
                    1 as is_header
                FROM (
                    SELECT 
                        id, 
                        cate_id, 
                        service_id, 
                        package_id, 
                        title, 
                        parent_group_id, 
                        price, 
                        category_id,
                        status, 
                        created_at, 
                        updated_at, 
                        service_content, 
                        sort_order,
                        ROW_NUMBER() OVER (PARTITION BY parent_group_id ORDER BY sort_order DESC) as rn
                    FROM doctor_individual_services
                    WHERE service_id = ?
                        AND status = 'published'
                ) t
                WHERE rn = 1
                UNION ALL
                SELECT 
                    id, 
                    cate_id, 
                    service_id, 
                    package_id, 
                    title, 
                    parent_group_id, 
                    price, 
                    category_id,
                    status, 
                    created_at, 
                    updated_at, 
                    service_content, 
                    sort_order,
                    0 as is_header
                FROM (
                    SELECT 
                        id, 
                        cate_id, 
                        service_id, 
                        package_id, 
                        title, 
                        parent_group_id, 
                        price, 
                        category_id,
                        status, 
                        created_at, 
                        updated_at, 
                        service_content, 
                        sort_order,
                        ROW_NUMBER() OVER (PARTITION BY parent_group_id ORDER BY sort_order DESC) as rn
                    FROM doctor_individual_services
                    WHERE service_id = ?
                ) t
                WHERE rn > 1
                ORDER BY parent_group_id ASC, sort_order DESC
            ", [$package_service_id, $package_service_id]);
            
            // Group items by parent_group_id for better structure
            $grouped_items = [];
            $headers = [];
            $items = [];
            
            foreach ($service_items as $item) {
                $formatted_item = [
                    'id' => (int)$item['id'],
                    'cate_id' => (int)($item['cate_id'] ?? 0),
                    'service_id' => (int)$item['service_id'],
                    'package_id' => (int)($item['package_id'] ?? 0),
                    'title' => $item['title'] ?? '',
                    'parent_group_id' => (int)$item['parent_group_id'],
                    'price' => (float)($item['price'] ?? 0),
                    'category_id' => (int)($item['category_id'] ?? 0),
                    'status' => $item['status'] ?? 'active',
                    'service_content' => $item['service_content'] ?? '',
                    'sort_order' => (int)($item['sort_order'] ?? 0),
                    'is_header' => (bool)$item['is_header'],
                    'created_at' => $item['created_at'] ?? '',
                    'updated_at' => $item['updated_at'] ?? ''
                ];
                
                $parent_group_id = $item['parent_group_id'];
                
                if (!isset($grouped_items[$parent_group_id])) {
                    $grouped_items[$parent_group_id] = [
                        'group_id' => (int)$parent_group_id,
                        'header' => null,
                        'items' => []
                    ];
                }
                
                if ($item['is_header']) {
                    $grouped_items[$parent_group_id]['header'] = $formatted_item;
                } else {
                    $grouped_items[$parent_group_id]['items'][] = $formatted_item;
                }
            }
            
            // Convert to indexed array and sort by group_id
            // $final_groups = array_values($grouped_items);
            // usort($final_groups, function($a, $b) {
            //     return $a['group_id'] <=> $b['group_id'];
            // });
            
            echo json_encode([
                'success' => true,
                'data' => [
                    // 'groups' => $final_groups,
                    'raw_items' => array_map(function($item) {
                        return [
                            'id' => (int)$item['id'],
                            'cate_id' => (int)($item['cate_id'] ?? 0),
                            'service_id' => (int)$item['service_id'],
                            'package_id' => (int)($item['package_id'] ?? 0),
                            'title' => $item['title'] ?? '',
                            'parent_group_id' => (int)$item['parent_group_id'],
                            'price' => (float)($item['price'] ?? 0),
                            'category_id' => (int)($item['category_id'] ?? 0),
                            'status' => $item['status'] ?? 'active',
                            'service_content' => $item['service_content'] ?? '',
                            'sort_order' => (int)($item['sort_order'] ?? 0),
                            'is_header' => (bool)$item['is_header'],
                            'created_at' => $item['created_at'] ?? '',
                            'updated_at' => $item['updated_at'] ?? ''
                        ];
                    }, $service_items)
                ],
                // 'total_groups' => count($final_groups),
                'total_items' => count($service_items),
                'package_service_id' => $package_service_id
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'error' => 'Failed to retrieve service items',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
