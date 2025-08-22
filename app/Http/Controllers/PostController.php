<?php

class PostController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get list of posts with pagination and filtering
     */
    public function Get($page = 1, $page_size = 10, $lang_code = 'vi', $filter = null) {
        try {
            // Validate and sanitize input parameters
            $page = max(1, (int)$page);
            $page_size = max(1, min(100, (int)$page_size)); // Limit max page size to 100
            $lang_code = htmlspecialchars($lang_code, ENT_QUOTES, 'UTF-8')=="en"?"en_us":"vi";
            $offset = ($page - 1) * $page_size;
            
            // Build dynamic query based on filter
            $whereClause = "WHERE p.status = 'published' 
                AND lm.lang_meta_code = ?
                AND lm.reference_type = 'Botble\\\\Blog\\\\Models\\\\Post'";
            $params = [$lang_code];
            
            if ($filter && trim($filter) !== '') {
                $filter = htmlspecialchars(trim($filter), ENT_QUOTES, 'UTF-8');
                $filterPattern = '%' . $filter . '%';
                $whereClause .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.content LIKE ?)";
                $params[] = $filterPattern;
                $params[] = $filterPattern;
                $params[] = $filterPattern;
            }

            // Get posts list from database
            $posts = $this->db->query("
                SELECT 
                    lm.lang_meta_code,
                    p.id,
                    p.name,
                    p.description,
                    p.content,
                    p.status,
                    CONCAT('https://aih.com.vn/storage/', p.image) as image,
                    p.views,
                    p.created_at,
                    p.updated_at,
                    p.is_featured,
                    c.name as category_name,
                    c.id as category_id,
                    s.key as slugable,
                    GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') as tags_name
                FROM posts p
                    INNER JOIN language_meta lm ON lm.reference_id = p.id 
                    INNER JOIN post_categories pc ON pc.post_id = p.id
                    INNER JOIN categories c ON c.id = pc.category_id
                    LEFT JOIN slugs s ON s.reference_id = p.id AND s.reference_type = 'Botble\\\\Blog\\\\Models\\\\Post'
                    LEFT JOIN post_tags pt ON pt.post_id = p.id
                    LEFT JOIN tags t ON t.id = pt.tag_id
                $whereClause
                GROUP BY p.id, lm.lang_meta_code, c.id, s.key
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?
            ", array_merge($params, [$page_size, $offset]));

            // Get total count
            $total_result = $this->db->queryOne("
                SELECT COUNT(DISTINCT p.id) as total 
                FROM posts p
                    INNER JOIN language_meta lm ON lm.reference_id = p.id 
                    INNER JOIN post_categories pc ON pc.post_id = p.id
                    INNER JOIN categories c ON c.id = pc.category_id
                $whereClause
            ", $params);

            $total = $total_result['total'] ?? count($posts);
            
            $formatted_posts = [];
            foreach ($posts as $post) {
                $formatted_posts[] = [
                    'id' => (int)$post['id'],
                    'name' => $post['name'] ?? '',
                    'description' => $post['description'] ?? '',
                    'content' => $post['content'] ?? '',
                    'status' => $post['status'] ?? 'published',
                    'image' => $post['image'] ?? '',
                    'views' => (int)($post['views'] ?? 0),
                    'is_featured' => (bool)($post['is_featured'] ?? false),
                    'category_id' => (int)($post['category_id'] ?? 0),
                    'category_name' => $post['category_name'] ?? '',
                    'slug' => $post['slugable'] ?? '',
                    'tags' => $post['tags_name'] ?? '',
                    'language' => $post['lang_meta_code'] ?? $lang_code,
                    'created_at' => $post['created_at'] ?? '',
                    'updated_at' => $post['updated_at'] ?? ''
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $formatted_posts,
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
                'error' => 'Failed to retrieve posts list',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Get post by ID
     */
    public function GetById($id) {
        try {
            // Validate and sanitize input
            $id = (int)$id;
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid post ID',
                    'error' => 'Post ID must be a positive integer'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $post = $this->db->queryOne("
                SELECT 
                    lm.lang_meta_code,
                    p.id,
                    p.name,
                    p.description,
                    p.content,
                    p.status,
                    CONCAT('https://aih.com.vn/storage/', p.image) as image,
                    p.views,
                    p.created_at,
                    p.updated_at,
                    p.is_featured,
                    c.name as category_name,
                    c.id as category_id,
                    s.key as slugable,
                    GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') as tags_name
                FROM posts p
                    INNER JOIN language_meta lm ON lm.reference_id = p.id 
                    INNER JOIN post_categories pc ON pc.post_id = p.id
                    INNER JOIN categories c ON c.id = pc.category_id
                    LEFT JOIN slugs s ON s.reference_id = p.id AND s.reference_type = 'Botble\\\\Blog\\\\Models\\\\Post'
                    LEFT JOIN post_tags pt ON pt.post_id = p.id
                    LEFT JOIN tags t ON t.id = pt.tag_id
                WHERE p.id = ?
                GROUP BY p.id, lm.lang_meta_code, c.id, s.key
            ", [$id]);
                    
            if ($post) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'id' => (int)$post['id'],
                        'name' => $post['name'] ?? '',
                        'description' => $post['description'] ?? '',
                        'content' => $post['content'] ?? '',
                        'status' => $post['status'] ?? 'published',
                        'image' => $post['image'] ?? '',
                        'views' => (int)($post['views'] ?? 0),
                        'is_featured' => (bool)($post['is_featured'] ?? false),
                        'category_id' => (int)($post['category_id'] ?? 0),
                        'category_name' => $post['category_name'] ?? '',
                        'slug' => $post['slugable'] ?? '',
                        'tags' => $post['tags_name'] ?? '',
                        'language' => $post['lang_meta_code'] ?? 'vi',
                        'created_at' => $post['created_at'] ?? '',
                        'updated_at' => $post['updated_at'] ?? ''
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Post not found',
                    'error' => 'The requested post does not exist'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'error' => 'Failed to retrieve post information',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    
    /**
     * Get posts by category
     */
    public function GetListByCategory($category_id, $page = 1, $page_size = 10, $lang_code = 'vi') {
        try {
            // Validate and sanitize input parameters
            $category_id = (int)$category_id;
            $page = max(1, (int)$page);
            $page_size = max(1, min(100, (int)$page_size));
            $lang_code = htmlspecialchars($lang_code, ENT_QUOTES, 'UTF-8')=="en"?"en_us":"vi";
            $offset = ($page - 1) * $page_size;
            
            if ($category_id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid category ID',
                    'error' => 'Category ID must be a positive integer'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Get posts list from database
            $posts = $this->db->query("
                SELECT 
                    lm.lang_meta_code,
                    p.id,
                    p.name,
                    p.description,
                    p.content,
                    p.status,
                    CONCAT('https://aih.com.vn/storage/', p.image) as image,
                    p.views,
                    p.created_at,
                    p.updated_at,
                    p.is_featured,
                    c.name as category_name,
                    c.id as category_id,
                    s.key as slugable,
                    GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') as tags_name
                FROM posts p
                    INNER JOIN language_meta lm ON lm.reference_id = p.id 
                    INNER JOIN post_categories pc ON pc.post_id = p.id
                    INNER JOIN categories c ON c.id = pc.category_id
                    LEFT JOIN slugs s ON s.reference_id = p.id AND s.reference_type = 'Botble\\\\Blog\\\\Models\\\\Post'
                    LEFT JOIN post_tags pt ON pt.post_id = p.id
                    LEFT JOIN tags t ON t.id = pt.tag_id
                WHERE p.status = 'published' 
                    AND lm.lang_meta_code = ?
                    AND lm.reference_type = 'Botble\\\\Blog\\\\Models\\\\Post'
                    AND c.id = ?
                GROUP BY p.id, lm.lang_meta_code, c.id, s.key
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?
            ", [$lang_code, $category_id, $page_size, $offset]);

            // Get total count
            $total_result = $this->db->queryOne("
                SELECT COUNT(DISTINCT p.id) as total 
                FROM posts p
                    INNER JOIN language_meta lm ON lm.reference_id = p.id 
                    INNER JOIN post_categories pc ON pc.post_id = p.id
                    INNER JOIN categories c ON c.id = pc.category_id
                WHERE p.status = 'published' 
                    AND lm.lang_meta_code = ?
                    AND lm.reference_type = 'Botble\\\\Blog\\\\Models\\\\Post'
                    AND c.id = ?
            ", [$lang_code, $category_id]);

            $total = $total_result['total'] ?? count($posts);
            
            $formatted_posts = [];
            foreach ($posts as $post) {
                $formatted_posts[] = [
                    'id' => (int)$post['id'],
                    'name' => $post['name'] ?? '',
                    'description' => $post['description'] ?? '',
                    'content' => $post['content'] ?? '',
                    'status' => $post['status'] ?? 'published',
                    'image' => $post['image'] ?? '',
                    'views' => (int)($post['views'] ?? 0),
                    'is_featured' => (bool)($post['is_featured'] ?? false),
                    'category_id' => (int)($post['category_id'] ?? 0),
                    'category_name' => $post['category_name'] ?? '',
                    'slug' => $post['slugable'] ?? '',
                    'tags' => $post['tags_name'] ?? '',
                    'language' => $post['lang_meta_code'] ?? $lang_code,
                    'created_at' => $post['created_at'] ?? '',
                    'updated_at' => $post['updated_at'] ?? ''
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $formatted_posts,
                'total' => (int)$total,
                'page' => $page,
                'page_size' => $page_size,
                'language' => $lang_code,
                'category_id' => $category_id
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'error' => 'Failed to retrieve posts by category',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
