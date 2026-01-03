<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../admin-api/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to reorder items']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderId = intval($input['order_id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Verify order belongs to user and is delivered
    $verifyStmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'delivered'");
    $verifyStmt->bind_param("ii", $orderId, $userId);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found or not eligible for reorder']);
        exit;
    }
    
    // Get order items
    $itemsStmt = $conn->prepare("
        SELECT oi.product_id, oi.quantity, oi.size, oi.color, p.stock_quantity, p.is_active
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $itemsStmt->bind_param("i", $orderId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    $addedItems = 0;
    $skippedItems = [];
    
    while ($item = $itemsResult->fetch_assoc()) {
        // Check if product is still active and in stock
        if (!$item['is_active']) {
            $skippedItems[] = "Product no longer available";
            continue;
        }
        
        if ($item['stock_quantity'] < $item['quantity']) {
            $skippedItems[] = "Insufficient stock for some items";
            continue;
        }
        
        // Check if item already exists in cart
        $checkCartStmt = $conn->prepare("
            SELECT id, quantity FROM cart 
            WHERE user_id = ? AND product_id = ? AND size = ? AND color = ?
        ");
        $checkCartStmt->bind_param("iiss", $userId, $item['product_id'], $item['size'], $item['color']);
        $checkCartStmt->execute();
        $checkResult = $checkCartStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Update existing cart item
            $cartItem = $checkResult->fetch_assoc();
            $newQuantity = $cartItem['quantity'] + $item['quantity'];
            
            if ($newQuantity > $item['stock_quantity']) {
                $newQuantity = $item['stock_quantity'];
            }
            
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $newQuantity, $cartItem['id']);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            // Add new cart item
            $insertStmt = $conn->prepare("
                INSERT INTO cart (user_id, product_id, quantity, size, color) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $insertStmt->bind_param("iiiss", $userId, $item['product_id'], $item['quantity'], $item['size'], $item['color']);
            $insertStmt->execute();
            $insertStmt->close();
        }
        
        $addedItems++;
        $checkCartStmt->close();
    }
    
    $message = "Added $addedItems item" . ($addedItems != 1 ? 's' : '') . " to cart";
    if (!empty($skippedItems)) {
        $message .= ". Some items were skipped: " . implode(', ', array_unique($skippedItems));
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'added_items' => $addedItems,
        'skipped_items' => count($skippedItems)
    ]);
    
    $verifyStmt->close();
    $itemsStmt->close();
    
} catch (Exception $e) {
    error_log("Reorder API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error processing reorder']);
}

$conn->close();
?>