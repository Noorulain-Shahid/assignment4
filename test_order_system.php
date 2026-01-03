<?php
session_start();
require_once 'admin-api/db_connect.php';

// Set a test user session if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Assuming user ID 1 exists
    $_SESSION['username'] = 'testuser';
    echo "Set session for test user<br>";
}

echo "<h2>Order System Test</h2>";

// Check if products exist
$productsQuery = "SELECT id, name, price FROM products LIMIT 3";
$productsResult = $conn->query($productsQuery);

if ($productsResult && $productsResult->num_rows > 0) {
    echo "<h3>Available Products:</h3>";
    $products = [];
    while ($row = $productsResult->fetch_assoc()) {
        $products[] = $row;
        echo "Product ID: {$row['id']}, Name: {$row['name']}, Price: PKR {$row['price']}<br>";
    }
    
    // Add products to cart if cart is empty
    $cartCheckQuery = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $cartCheckStmt = $conn->prepare($cartCheckQuery);
    $cartCheckStmt->bind_param("i", $_SESSION['user_id']);
    $cartCheckStmt->execute();
    $cartCheckResult = $cartCheckStmt->get_result();
    $cartCount = $cartCheckResult->fetch_assoc()['count'];
    
    if ($cartCount == 0) {
        echo "<br><h3>Adding test items to cart...</h3>";
        foreach ($products as $product) {
            $quantity = rand(1, 2);
            $insertQuery = "INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("iii", $_SESSION['user_id'], $product['id'], $quantity);
            $insertStmt->execute();
            echo "Added {$quantity}x {$product['name']} to cart<br>";
        }
    } else {
        echo "<br>Cart already has {$cartCount} items.<br>";
    }
    
    echo "<br><h3>Test Links:</h3>";
    echo "<a href='cart.php' target='_blank'>View Cart</a><br>";
    echo "<a href='orders.php' target='_blank'>View Orders</a><br>";
    echo "<a href='products.php' target='_blank'>View Products</a><br>";
    
} else {
    echo "No products found. Please add some products first.";
}

$conn->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background-color: #f8f9fa;
}
h2, h3 {
    color: #333;
}
a {
    display: inline-block;
    margin: 5px 0;
    padding: 8px 16px;
    background-color: #D4C5B0;
    color: #3E3E3E;
    text-decoration: none;
    border-radius: 4px;
}
a:hover {
    background-color: #C4B5A0;
}
</style>