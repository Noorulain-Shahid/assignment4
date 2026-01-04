<?php
session_start();
header('Content-Type: application/json');

require_once 'admin-api/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$productId = intval($data['product_id']);
$orderId = intval($data['order_id']);
$rating = intval($data['rating']);
$comment = trim($data['comment']);

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating']);
    exit;
}

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a comment']);
    exit;
}

// Verify that the user actually purchased this product in this order and the order is delivered
$verifyQuery = "
    SELECT o.id 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id = ? AND o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'
";

$stmt = $conn->prepare($verifyQuery);
$stmt->bind_param("iii", $orderId, $userId, $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You can only review products from delivered orders']);
    exit;
}

// Check if review already exists
$checkQuery = "SELECT id FROM reviews WHERE order_id = ? AND product_id = ? AND user_id = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("iii", $orderId, $productId, $userId);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this item']);
    exit;
}

// Insert review
$reviewTitle = "Customer Review";
$isVerified = 1;
$isApproved = 1; // Auto-approve for now

$insertQuery = "INSERT INTO reviews (user_id, product_id, order_id, rating, review_title, review_text, is_verified_purchase, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$insertStmt = $conn->prepare($insertQuery);
$insertStmt->bind_param("iiiissii", $userId, $productId, $orderId, $rating, $reviewTitle, $comment, $isVerified, $isApproved);

if ($insertStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
} else {
    // Log the error for debugging
    error_log("Review submission error: " . $insertStmt->error);
    echo json_encode(['success' => false, 'message' => 'Error submitting review: ' . $insertStmt->error]);
}

$conn->close();
?>
