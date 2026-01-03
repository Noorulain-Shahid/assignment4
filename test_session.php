<?php
session_start();
require_once 'admin-api/db_connect.php';

header('Content-Type: text/plain');

echo "=== CART DEBUG INFO ===" . PHP_EOL;
echo "Session ID: " . session_id() . PHP_EOL;
echo "Session data: " . print_r($_SESSION, true) . PHP_EOL;

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    echo "User logged in with ID: $userId" . PHP_EOL;
    
    // Test the exact query from cart.php (without sale_price)
    $cartQuery = "SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = ? 
                  ORDER BY COALESCE(c.updated_at, c.created_at) DESC";
    $cartStmt = $conn->prepare($cartQuery);
    if ($cartStmt) {
        $cartStmt->bind_param("i", $userId);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();
        
        $cartItems = [];
        while ($row = $cartResult->fetch_assoc()) {
            $cartItems[] = $row;
        }
        
        echo "Found " . count($cartItems) . " cart items:" . PHP_EOL;
        foreach ($cartItems as $item) {
            echo "- " . $item['name'] . " (qty: " . $item['quantity'] . ", price: $" . $item['price'] . ")" . PHP_EOL;
        }
        $cartStmt->close();
    } else {
        echo "Failed to prepare cart query: " . $conn->error . PHP_EOL;
        
        // Try the fallback query (without sale_price)
        $fallbackQuery = "SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
                          FROM cart c 
                          JOIN products p ON c.product_id = p.id 
                          WHERE c.user_id = ? 
                          ORDER BY c.id DESC";
        $fallbackStmt = $conn->prepare($fallbackQuery);
        if ($fallbackStmt) {
            $fallbackStmt->bind_param("i", $userId);
            $fallbackStmt->execute();
            $fallbackResult = $fallbackStmt->get_result();
            
            $cartItems = [];
            while ($row = $fallbackResult->fetch_assoc()) {
                $cartItems[] = $row;
            }
            
            echo "Fallback query found " . count($cartItems) . " cart items:" . PHP_EOL;
            foreach ($cartItems as $item) {
                echo "- " . $item['name'] . " (qty: " . $item['quantity'] . ", price: $" . $item['price'] . ")" . PHP_EOL;
            }
            $fallbackStmt->close();
        } else {
            echo "Fallback query also failed: " . $conn->error . PHP_EOL;
        }
    }
} else {
    echo "USER NOT LOGGED IN!" . PHP_EOL;
    echo "Available session keys: " . implode(', ', array_keys($_SESSION)) . PHP_EOL;
}

echo PHP_EOL . "Raw cart table for user_id 4:" . PHP_EOL;
$result = $conn->query("SELECT * FROM cart WHERE user_id = 4");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Cart ID {$row['id']}: Product {$row['product_id']}, Qty {$row['quantity']}" . PHP_EOL;
    }
}

$conn->close();
?>