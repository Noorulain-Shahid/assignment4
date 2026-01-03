<?php
session_start();
require_once 'admin-api/db_connect.php';

echo "=== DEBUG SCRIPT ===" . PHP_EOL;
echo "Current session data:" . PHP_EOL;
print_r($_SESSION);

echo PHP_EOL . "Cart table contents:" . PHP_EOL;
$result = $conn->query('SELECT * FROM cart');
if ($result) {
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $count++;
        print_r($row);
    }
    echo "Total cart rows: $count" . PHP_EOL;
} else {
    echo 'Cart query failed: ' . $conn->error . PHP_EOL;
}

echo PHP_EOL . "Products table sample:" . PHP_EOL;  
$result = $conn->query('SELECT id, name, price FROM products LIMIT 3');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo 'Products query failed: ' . $conn->error . PHP_EOL;
}

echo PHP_EOL . "Users table:" . PHP_EOL;  
$result = $conn->query('SELECT id, username, email FROM users LIMIT 3');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo 'Users query failed: ' . $conn->error . PHP_EOL;
}

echo PHP_EOL . "Testing cart API directly..." . PHP_EOL;

// Simulate what cart_api.php does
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    echo "User ID from session: $userId" . PHP_EOL;
    
    $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $cartItems = [];
        while ($row = $result->fetch_assoc()) {
            $cartItems[] = $row;
        }
        
        echo "Cart items for user $userId: " . count($cartItems) . " items" . PHP_EOL;
        foreach ($cartItems as $item) {
            print_r($item);
        }
        $stmt->close();
    } else {
        echo "Failed to prepare cart query: " . $conn->error . PHP_EOL;
    }
} else {
    echo "No user_id in session!" . PHP_EOL;
}

$conn->close();
?>