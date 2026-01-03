<?php
session_start();
require_once 'admin-api/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $orderId = intval($input['order_id'] ?? 0);
    
    if ($orderId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        exit;
    }
    
    try {
        // Verify order belongs to user and can be cancelled
        $orderQuery = "SELECT id, status FROM orders WHERE id = ? AND user_id = ?";
        $orderStmt = $conn->prepare($orderQuery);
        $orderStmt->bind_param("ii", $orderId, $userId);
        $orderStmt->execute();
        $orderResult = $orderStmt->get_result();
        
        if ($orderResult->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        
        $order = $orderResult->fetch_assoc();
        
        // Check if order can be cancelled
        if (!in_array($order['status'], ['pending', 'processing'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled in current status']);
            exit;
        }
        
        // Update order status to cancelled
        $updateQuery = "UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $orderId);
        $updateStmt->execute();
        
        if ($updateStmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
        }
        
    } catch (Exception $e) {
        error_log("Error cancelling order: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
}

$conn->close();
?>