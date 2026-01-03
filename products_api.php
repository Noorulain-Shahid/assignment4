<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'admin-api/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $category = $_GET['category'] ?? '';
    $search = $_GET['search'] ?? '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 12;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    try {
        $whereClause = "WHERE p.is_active = 1";
        $params = [];
        $types = "";
        
        if (!empty($category)) {
            $whereClause .= " AND c.name LIKE ?";
            $params[] = "%$category%";
            $types .= "s";
        }
        
        if (!empty($search)) {
            $whereClause .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }
        
        // Get products with category information
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  $whereClause 
                  ORDER BY p.created_at DESC 
                  LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $conn->prepare($query);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            // Parse JSON fields
            $row['additional_images'] = json_decode($row['additional_images'], true) ?: [];
            $row['sizes'] = json_decode($row['sizes'], true) ?: [];
            $row['colors'] = json_decode($row['colors'], true) ?: [];
            $products[] = $row;
        }
        
        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       $whereClause";
        
        $countStmt = $conn->prepare($countQuery);
        if (!empty($types) && count($params) > 2) {
            $countTypes = substr($types, 0, -2); // Remove last two 'ii' for limit and offset
            $countParams = array_slice($params, 0, -2); // Remove limit and offset
            if (!empty($countTypes)) {
                $countStmt->bind_param($countTypes, ...$countParams);
            }
        }
        $countStmt->execute();
        $totalResult = $countStmt->get_result();
        $total = $totalResult->fetch_assoc()['total'];
        
        echo json_encode([
            'success' => true,
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_products' => $total,
                'per_page' => $limit
            ]
        ]);
        
        $stmt->close();
        $countStmt->close();
        
    } catch (Exception $e) {
        error_log("Products API error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching products']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

$conn->close();
?>
