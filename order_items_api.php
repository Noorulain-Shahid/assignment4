<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'admin-api/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$orderId = intval($_GET['order_id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Verify order belongs to user
    $verifyStmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $verifyStmt->bind_param("ii", $orderId, $userId);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    // Get order items
    $itemsStmt = $conn->prepare("
        SELECT oi.*, 
               COALESCE(oi.product_name, p.name) as name,
               COALESCE(oi.product_image, p.image_url) as image_url
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
        ORDER BY oi.id
    ");
    $itemsStmt->bind_param("i", $orderId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    $items = [];
    while ($row = $itemsResult->fetch_assoc()) {
        $items[] = [
            'id' => $row['id'],
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => $row['quantity'],
            'total' => $row['subtotal'],
            'subtotal' => $row['subtotal'],
            'image_url' => $row['image_url'],
            'size' => $row['size'] ?? null,
            'color' => $row['color'] ?? null
        ];
    }
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
    $verifyStmt->close();
    $itemsStmt->close();
    
} catch (Exception $e) {
    error_log("Order items API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading order items']);
}

$conn->close();
?>
