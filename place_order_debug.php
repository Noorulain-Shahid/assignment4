<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'place_order_debug.log');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'admin-api/db_connect.php';

// Debug logging function
function debugLog($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, 'place_order_debug.log');
    echo json_encode(['debug' => $message]);
    ob_flush();
}

debugLog("Script started");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    debugLog("User not logged in");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to place order']);
    exit;
}

debugLog("User logged in: " . $_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debugLog("Invalid method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
debugLog("Input received: " . json_encode($input));

if (!$input) {
    debugLog("Invalid JSON input");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$userId = $_SESSION['user_id'];
$paymentMethod = $input['payment_method'] ?? 'cash_on_delivery';

debugLog("Processing order for user: $userId, payment: $paymentMethod");

try {
    // Start transaction
    $conn->autocommit(FALSE);
    debugLog("Transaction started");
    
    // Check if cart has items first
    $cartCheckStmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $cartCheckStmt->bind_param("i", $userId);
    $cartCheckStmt->execute();
    $cartCheckResult = $cartCheckStmt->get_result();
    $cartCount = $cartCheckResult->fetch_assoc()['count'];
    debugLog("Cart items count: $cartCount");
    
    if ($cartCount == 0) {
        throw new Exception("Cart is empty");
    }
    
    // Create orders table if not exists
    $createOrdersTable = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        final_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        payment_method VARCHAR(50) NOT NULL,
        shipping_address TEXT,
        shipping_city VARCHAR(100),
        shipping_postal_code VARCHAR(20),
        billing_address TEXT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    
    if (!$conn->query($createOrdersTable)) {
        debugLog("Error creating orders table: " . $conn->error);
        throw new Exception("Error creating orders table: " . $conn->error);
    }
    debugLog("Orders table created/verified");

    // Create order_items table if not exists
    $createOrderItemsTable = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255),
        product_image VARCHAR(500),
        quantity INT NOT NULL,
        size VARCHAR(50),
        color VARCHAR(50),
        price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    
    if (!$conn->query($createOrderItemsTable)) {
        debugLog("Error creating order_items table: " . $conn->error);
        throw new Exception("Error creating order_items table: " . $conn->error);
    }
    debugLog("Order_items table created/verified");
    
    // Get cart items for this user
    $cartStmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.image_url 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $cartStmt->bind_param("i", $userId);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();
    
    $cartItems = [];
    $subtotal = 0;
    
    while ($row = $cartResult->fetch_assoc()) {
        $itemTotal = $row['price'] * $row['quantity'];
        $row['item_total'] = $itemTotal;
        $subtotal += $itemTotal;
        $cartItems[] = $row;
    }
    $cartStmt->close();
    
    debugLog("Cart items retrieved: " . count($cartItems) . ", subtotal: $subtotal");
    
    if (empty($cartItems)) {
        throw new Exception('Cart is empty or products not found');
    }
    
    // Calculate totals
    $tax = $subtotal * 0.08;
    $shipping = $subtotal > 5000 ? 0 : 500;
    $total = $subtotal + $tax + $shipping;
    
    debugLog("Totals calculated - subtotal: $subtotal, tax: $tax, shipping: $shipping, total: $total");
    
    // Generate order number
    $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    debugLog("Order number generated: $orderNumber");
    
    // Get user address for shipping
    $userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();
    $userStmt->close();
    
    debugLog("User data retrieved: " . ($user ? 'Yes' : 'No'));
    
    $shippingAddress = json_encode([
        'name' => $user['full_name'] ?? $user['username'] ?? 'N/A',
        'phone' => $user['phone'] ?? '',
        'address' => $user['address'] ?? '',
        'city' => $user['city'] ?? '',
        'postal_code' => $user['postal_code'] ?? ''
    ]);
    
    // Insert order
    $orderStmt = $conn->prepare("
        INSERT INTO orders (user_id, order_number, total_amount, discount_amount, final_amount, 
                           status, payment_method, shipping_address, shipping_city, shipping_postal_code, billing_address) 
        VALUES (?, ?, ?, 0, ?, 'pending', ?, ?, ?, ?, ?)
    ");
    $shippingCity = $user['city'] ?? '';
    $shippingPostalCode = $user['postal_code'] ?? '';
    $billingAddress = $user['address'] ?? '';
    $orderStmt->bind_param("isddssss", $userId, $orderNumber, $total, $total, $paymentMethod, $shippingAddress, $shippingCity, $shippingPostalCode, $billingAddress);
    
    if (!$orderStmt->execute()) {
        debugLog("Error inserting order: " . $orderStmt->error);
        throw new Exception("Error inserting order: " . $orderStmt->error);
    }
    
    $orderId = $conn->insert_id;
    $orderStmt->close();
    
    debugLog("Order inserted with ID: $orderId");
    
    // Insert order items
    $orderItemStmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, size, color, price, subtotal) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($cartItems as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $size = $item['size'] ?? null;
        $color = $item['color'] ?? null;
        $orderItemStmt->bind_param("iissiisdd", $orderId, $item['product_id'], $item['name'], $item['image_url'], 
                                  $item['quantity'], $size, $color, $item['price'], $itemTotal);
        if (!$orderItemStmt->execute()) {
            debugLog("Error inserting order item: " . $orderItemStmt->error);
            throw new Exception("Error inserting order item: " . $orderItemStmt->error);
        }
    }
    $orderItemStmt->close();
    
    debugLog("Order items inserted");
    
    // Clear user's cart
    $clearCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearCartStmt->bind_param("i", $userId);
    $clearCartStmt->execute();
    $clearCartStmt->close();
    
    debugLog("Cart cleared");
    
    // Commit transaction
    $conn->commit();
    $conn->autocommit(TRUE);
    
    debugLog("Transaction committed successfully");
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order' => [
            'id' => $orderId,
            'order_number' => $orderNumber,
            'total_amount' => $total,
            'final_amount' => $total,
            'payment_method' => $paymentMethod,
            'item_count' => count($cartItems)
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    $conn->autocommit(TRUE);
    
    $errorMsg = "Order placement error: " . $e->getMessage();
    debugLog($errorMsg);
    error_log($errorMsg);
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error placing order: ' . $e->getMessage(),
        'debug' => true
    ]);
}

$conn->close();
debugLog("Script ended");
?>