<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Database connection
    require_once 'admin-api/db_connect.php';
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Please login to place order']);
        exit;
    }
    
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    
    // Get and validate input
    $inputData = file_get_contents('php://input');
    $input = json_decode($inputData, true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input', 'received' => $inputData]);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $paymentMethod = $input['payment_method'] ?? 'cash_on_delivery';
    
    // Start transaction
    $conn->autocommit(false);
    
    // Create orders table if not exists
    $createOrdersSQL = "CREATE TABLE IF NOT EXISTS orders (
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_order_number (order_number)
    ) ENGINE=InnoDB";
    
    if (!$conn->query($createOrdersSQL)) {
        throw new Exception("Error creating orders table: " . $conn->error);
    }
    
    // Create order_items table if not exists
    $createOrderItemsSQL = "CREATE TABLE IF NOT EXISTS order_items (
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_order_id (order_id),
        INDEX idx_product_id (product_id)
    ) ENGINE=InnoDB";
    
    if (!$conn->query($createOrderItemsSQL)) {
        throw new Exception("Error creating order_items table: " . $conn->error);
    }
    
    // Get cart items
    $cartQuery = "SELECT c.*, p.name, p.price, p.image_url 
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = ?";
    $cartStmt = $conn->prepare($cartQuery);
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
    
    if (empty($cartItems)) {
        throw new Exception('Your cart is empty');
    }
    
    // Calculate totals
    $taxRate = 0.08; // 8%
    $tax = $subtotal * $taxRate;
    $shipping = $subtotal > 5000 ? 0 : 500;
    $total = $subtotal + $tax + $shipping;
    
    // Generate order number
    $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Get user info
    $userQuery = "SELECT * FROM users WHERE id = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();
    
    // Prepare shipping address
    $shippingAddress = json_encode([
        'name' => $user['full_name'] ?? $user['username'] ?? 'Customer',
        'phone' => $user['phone'] ?? '',
        'address' => $user['address'] ?? '',
        'city' => $user['city'] ?? '',
        'postal_code' => $user['postal_code'] ?? ''
    ]);
    
    // Insert order
    $insertOrderQuery = "INSERT INTO orders (
        user_id, order_number, total_amount, discount_amount, final_amount, 
        status, payment_method, shipping_address, shipping_city, 
        shipping_postal_code, billing_address
    ) VALUES (?, ?, ?, 0, ?, 'pending', ?, ?, ?, ?, ?)";
    
    $orderStmt = $conn->prepare($insertOrderQuery);
    $shippingCity = $user['city'] ?? '';
    $shippingPostalCode = $user['postal_code'] ?? '';
    $billingAddress = $user['address'] ?? '';
    
    $orderStmt->bind_param("isddsssss", 
        $userId, 
        $orderNumber, 
        $total, 
        $total, 
        $paymentMethod, 
        $shippingAddress, 
        $shippingCity, 
        $shippingPostalCode, 
        $billingAddress
    );
    
    if (!$orderStmt->execute()) {
        throw new Exception("Error creating order: " . $orderStmt->error);
    }
    
    $orderId = $conn->insert_id;
    
    // Insert order items
    $insertItemQuery = "INSERT INTO order_items (
        order_id, product_id, product_name, product_image, 
        quantity, size, color, price, subtotal
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $itemStmt = $conn->prepare($insertItemQuery);
    
    foreach ($cartItems as $item) {
        $itemSubtotal = $item['price'] * $item['quantity'];
        $size = $item['size'] ?? null;
        $color = $item['color'] ?? null;
        
        $itemStmt->bind_param("iissiisdd", 
            $orderId,
            $item['product_id'],
            $item['name'],
            $item['image_url'],
            $item['quantity'],
            $size,
            $color,
            $item['price'],
            $itemSubtotal
        );
        
        if (!$itemStmt->execute()) {
            throw new Exception("Error adding order item: " . $itemStmt->error);
        }
    }
    
    // Clear cart
    $clearCartQuery = "DELETE FROM cart WHERE user_id = ?";
    $clearStmt = $conn->prepare($clearCartQuery);
    $clearStmt->bind_param("i", $userId);
    $clearStmt->execute();
    
    // Commit transaction
    $conn->commit();
    $conn->autocommit(true);
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order' => [
            'id' => $orderId,
            'order_number' => $orderNumber,
            'total_amount' => $total,
            'final_amount' => $total,
            'payment_method' => $paymentMethod,
            'item_count' => count($cartItems),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    if (isset($conn)) {
        $conn->rollback();
        $conn->autocommit(true);
    }
    
    error_log("Order API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'session_user_id' => $_SESSION['user_id'] ?? 'not_set',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
        ]
    ]);
}

// Clean up
if (isset($conn)) {
    $conn->close();
}
?>