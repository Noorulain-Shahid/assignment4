<?php
session_start();
require_once 'admin-api/db_connect.php';

echo "<h1>System Debug Analysis</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .section{margin:20px 0;border:1px solid #ccc;padding:15px;} .error{color:red;} .success{color:green;} .warning{color:orange;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f2f2f2;}</style>";

// 1. Check Database Connection
echo "<div class='section'>";
echo "<h2>1. Database Connection</h2>";
if ($conn->connect_error) {
    echo "<p class='error'>Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p class='success'>Database connected successfully</p>";
    echo "<p>Database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "</p>";
}
echo "</div>";

// 2. Check All Tables
echo "<div class='section'>";
echo "<h2>2. Database Tables</h2>";
$tablesQuery = "SHOW TABLES";
$tablesResult = $conn->query($tablesQuery);
if ($tablesResult) {
    echo "<table><tr><th>Table Name</th><th>Row Count</th><th>Actions</th></tr>";
    while ($table = $tablesResult->fetch_row()) {
        $tableName = $table[0];
        $countResult = $conn->query("SELECT COUNT(*) FROM `$tableName`");
        $count = $countResult ? $countResult->fetch_row()[0] : 'Error';
        echo "<tr><td>$tableName</td><td>$count</td><td><a href='#$tableName'>View Structure</a></td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>Error fetching tables: " . $conn->error . "</p>";
}
echo "</div>";

// 3. Check Table Structures
echo "<div class='section'>";
echo "<h2>3. Table Structures</h2>";
$tables = ['users', 'products', 'cart', 'orders', 'order_items'];
foreach ($tables as $table) {
    echo "<h3 id='$table'>$table Table</h3>";
    $descResult = $conn->query("DESCRIBE `$table`");
    if ($descResult) {
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $descResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>Table '$table' does not exist</p>";
    }
}
echo "</div>";

// 4. Check Session
echo "<div class='section'>";
echo "<h2>4. Session Information</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID in session: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>Username in session: " . ($_SESSION['username'] ?? 'Not set') . "</p>";
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'testuser';
    echo "<p class='warning'>Set test session: user_id = 1</p>";
}
echo "</div>";

// 5. Check Users Table Content
echo "<div class='section'>";
echo "<h2>5. Users Data</h2>";
$usersResult = $conn->query("SELECT * FROM users LIMIT 3");
if ($usersResult && $usersResult->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Address</th></tr>";
    while ($user = $usersResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>" . ($user['full_name'] ?? 'N/A') . "</td>";
        echo "<td>" . ($user['email'] ?? 'N/A') . "</td>";
        echo "<td>" . ($user['address'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>No users found or error: " . $conn->error . "</p>";
}
echo "</div>";

// 6. Check Products Table Content
echo "<div class='section'>";
echo "<h2>6. Products Data</h2>";
$productsResult = $conn->query("SELECT * FROM products LIMIT 5");
if ($productsResult && $productsResult->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Image URL</th></tr>";
    while ($product = $productsResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>{$product['price']}</td>";
        echo "<td>" . ($product['stock_quantity'] ?? $product['stock'] ?? 'N/A') . "</td>";
        echo "<td>" . ($product['image_url'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>No products found or error: " . $conn->error . "</p>";
}
echo "</div>";

// 7. Check Cart Content
echo "<div class='section'>";
echo "<h2>7. Cart Data</h2>";
$cartResult = $conn->query("SELECT c.*, p.name, p.price FROM cart c LEFT JOIN products p ON c.product_id = p.id");
if ($cartResult && $cartResult->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>User ID</th><th>Product ID</th><th>Product Name</th><th>Price</th><th>Quantity</th></tr>";
    while ($cart = $cartResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$cart['id']}</td>";
        echo "<td>{$cart['user_id']}</td>";
        echo "<td>{$cart['product_id']}</td>";
        echo "<td>" . ($cart['name'] ?? 'N/A') . "</td>";
        echo "<td>" . ($cart['price'] ?? 'N/A') . "</td>";
        echo "<td>{$cart['quantity']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>No cart items found or error: " . $conn->error . "</p>";
    // Try to add some test items
    $testUserId = $_SESSION['user_id'];
    $addTestItems = $conn->query("SELECT id FROM products LIMIT 2");
    if ($addTestItems && $addTestItems->num_rows > 0) {
        echo "<p class='warning'>Adding test cart items...</p>";
        while ($product = $addTestItems->fetch_assoc()) {
            $insertCart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
            $insertCart->bind_param("ii", $testUserId, $product['id']);
            if ($insertCart->execute()) {
                echo "<p class='success'>Added product {$product['id']} to cart</p>";
            } else {
                echo "<p class='error'>Error adding to cart: " . $insertCart->error . "</p>";
            }
        }
    }
}
echo "</div>";

// 8. Check Orders Table
echo "<div class='section'>";
echo "<h2>8. Orders Data</h2>";
$ordersResult = $conn->query("SELECT * FROM orders");
if ($ordersResult && $ordersResult->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>User ID</th><th>Order Number</th><th>Total Amount</th><th>Status</th><th>Created At</th></tr>";
    while ($order = $ordersResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$order['id']}</td>";
        echo "<td>{$order['user_id']}</td>";
        echo "<td>{$order['order_number']}</td>";
        echo "<td>" . ($order['final_amount'] ?? $order['total_amount'] ?? 'N/A') . "</td>";
        echo "<td>{$order['status']}</td>";
        echo "<td>{$order['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>No orders found</p>";
}
echo "</div>";

// 9. Test API Endpoints
echo "<div class='section'>";
echo "<h2>9. API Endpoint Tests</h2>";

// Test cart API
echo "<h3>Cart API Test</h3>";
$cartApiUrl = "http://localhost/assignment4/cart_api.php";
$cartContext = stream_context_create([
    'http' => ['method' => 'GET', 'header' => 'Cookie: ' . $_SERVER['HTTP_COOKIE'] ?? '']
]);
$cartApiResponse = @file_get_contents($cartApiUrl, false, $cartContext);
if ($cartApiResponse) {
    $cartData = json_decode($cartApiResponse, true);
    echo "<p class='success'>Cart API Response:</p>";
    echo "<pre>" . json_encode($cartData, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p class='error'>Cart API failed to respond</p>";
}

echo "</div>";

// 10. PHP Error Checking
echo "<div class='section'>";
echo "<h2>10. PHP Configuration</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Error Reporting: " . ini_get('display_errors') . "</p>";
echo "<p>Log Errors: " . ini_get('log_errors') . "</p>";
echo "<p>Error Log: " . ini_get('error_log') . "</p>";

// Check if place_order_api.php has syntax errors
echo "<h3>Syntax Check: place_order_api.php</h3>";
$syntaxCheck = shell_exec("php -l place_order_api.php 2>&1");
if (strpos($syntaxCheck, 'No syntax errors') !== false) {
    echo "<p class='success'>No syntax errors found</p>";
} else {
    echo "<p class='error'>Syntax errors found:</p>";
    echo "<pre>$syntaxCheck</pre>";
}
echo "</div>";

$conn->close();
?>

<div class="section">
<h2>11. Quick Tests</h2>
<p><a href="cart.php" target="_blank">Test Cart Page</a></p>
<p><a href="place_order_api.php" target="_blank">Test Place Order API (will show error - expected)</a></p>
<p><a href="orders.php" target="_blank">Test Orders Page</a></p>
</div>