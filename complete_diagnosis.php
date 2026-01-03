<?php
// Complete diagnostic and fix script
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Complete System Analysis & Fix</h1>";
echo "<style>
body{font-family:Arial;padding:20px;background:#f8f9fa;} 
.section{margin:20px 0;padding:20px;border:1px solid #ddd;border-radius:8px;background:white;} 
.error{color:#dc3545;background:#f8d7da;padding:10px;border-radius:5px;margin:10px 0;} 
.success{color:#155724;background:#d4edda;padding:10px;border-radius:5px;margin:10px 0;} 
.warning{color:#856404;background:#fff3cd;padding:10px;border-radius:5px;margin:10px 0;} 
.info{color:#0c5460;background:#d1ecf1;padding:10px;border-radius:5px;margin:10px 0;}
table{width:100%;border-collapse:collapse;margin:10px 0;} 
th,td{border:1px solid #ddd;padding:8px;text-align:left;} 
th{background-color:#f2f2f2;}
pre{background:#f8f9fa;padding:15px;border:1px solid #ddd;border-radius:5px;overflow-x:auto;}
.fix-button{background:#28a745;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;margin:5px;}
.test-button{background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;margin:5px;}
</style>";

require_once 'admin-api/db_connect.php';

// Set test session if not set
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'testuser';
    echo "<div class='info'>✅ Set test session: user_id = 1</div>";
}

echo "<div class='section'>";
echo "<h2>📊 System Status</h2>";

// 1. Database Connection
if ($conn->connect_error) {
    echo "<div class='error'>❌ Database connection failed: " . $conn->connect_error . "</div>";
    exit;
} else {
    echo "<div class='success'>✅ Database connected successfully</div>";
}

// 2. Check Tables
$tables = ['users', 'products', 'cart', 'orders', 'order_items'];
$tableStatus = [];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $exists = $result && $result->num_rows > 0;
    $count = 0;
    
    if ($exists) {
        $countResult = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $countResult->fetch_assoc()['count'];
    }
    
    $tableStatus[$table] = ['exists' => $exists, 'count' => $count];
    
    $status = $exists ? "✅" : "❌";
    $color = $exists ? "success" : "error";
    echo "<div class='$color'>$status Table '$table': " . ($exists ? "EXISTS ($count rows)" : "NOT EXISTS") . "</div>";
}

echo "</div>";

// 3. Fix Missing Data
echo "<div class='section'>";
echo "<h2>🔧 Data Setup & Fixes</h2>";

// Ensure we have a test user
if ($tableStatus['users']['count'] == 0) {
    echo "<div class='warning'>⚠️ No users found. Creating test user...</div>";
    $conn->query("INSERT INTO users (username, password, full_name, email, address, city, postal_code, phone) VALUES 
        ('testuser', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'Test User', 'test@example.com', '123 Test Street', 'Test City', '12345', '555-0123')");
    echo "<div class='success'>✅ Test user created</div>";
}

// Ensure we have products
if ($tableStatus['products']['count'] == 0) {
    echo "<div class='warning'>⚠️ No products found. Creating test products...</div>";
    $testProducts = [
        ['T-Shirt', 1500, 'images/tshirt.jpg', 50],
        ['Jeans', 3500, 'images/jeans.jpg', 30],
        ['Sneakers', 5000, 'images/sneakers.jpg', 20]
    ];
    
    foreach ($testProducts as $product) {
        $conn->query("INSERT INTO products (name, price, image_url, stock_quantity) VALUES 
            ('{$product[0]}', {$product[1]}, '{$product[2]}', {$product[3]})");
    }
    echo "<div class='success'>✅ Test products created</div>";
}

// Ensure we have cart items for testing
if ($tableStatus['cart']['count'] == 0) {
    echo "<div class='warning'>⚠️ No cart items found. Adding test cart items...</div>";
    $conn->query("INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) 
        SELECT 1, id, 1, NOW(), NOW() FROM products LIMIT 2");
    echo "<div class='success'>✅ Test cart items added</div>";
}

echo "</div>";

// 4. Test the API Directly
echo "<div class='section'>";
echo "<h2>🧪 API Testing</h2>";

echo "<button class='test-button' onclick='testOrderAPI()'>Test Order API</button>";
echo "<div id='apiTestResult'></div>";

echo "<script>
async function testOrderAPI() {
    const resultDiv = document.getElementById('apiTestResult');
    resultDiv.innerHTML = '<div class=\"info\">🔄 Testing API...</div>';
    
    try {
        console.log('Testing place_order_api.php...');
        
        const response = await fetch('place_order_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                payment_method: 'cash_on_delivery'
            }),
            credentials: 'same-origin'
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));
        
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            resultDiv.innerHTML = '<div class=\"error\">❌ Invalid JSON Response:</div><pre>' + responseText + '</pre>';
            return;
        }
        
        if (data.success) {
            resultDiv.innerHTML = '<div class=\"success\">✅ Order API Test PASSED!</div><pre>' + JSON.stringify(data, null, 2) + '</pre>';
        } else {
            resultDiv.innerHTML = '<div class=\"error\">❌ Order API Test FAILED:</div><pre>' + JSON.stringify(data, null, 2) + '</pre>';
        }
        
    } catch (error) {
        console.error('API Test Error:', error);
        resultDiv.innerHTML = '<div class=\"error\">❌ Network/Connection Error: ' + error.message + '</div>';
    }
}
</script>";

echo "</div>";

// 5. Show Current Data
echo "<div class='section'>";
echo "<h2>📋 Current Data Status</h2>";

// Show users
$usersResult = $conn->query("SELECT id, username, full_name FROM users LIMIT 3");
if ($usersResult && $usersResult->num_rows > 0) {
    echo "<h4>Users:</h4><table><tr><th>ID</th><th>Username</th><th>Full Name</th></tr>";
    while ($user = $usersResult->fetch_assoc()) {
        echo "<tr><td>{$user['id']}</td><td>{$user['username']}</td><td>{$user['full_name']}</td></tr>";
    }
    echo "</table>";
}

// Show products
$productsResult = $conn->query("SELECT id, name, price FROM products LIMIT 5");
if ($productsResult && $productsResult->num_rows > 0) {
    echo "<h4>Products:</h4><table><tr><th>ID</th><th>Name</th><th>Price</th></tr>";
    while ($product = $productsResult->fetch_assoc()) {
        echo "<tr><td>{$product['id']}</td><td>{$product['name']}</td><td>PKR {$product['price']}</td></tr>";
    }
    echo "</table>";
}

// Show cart
$cartResult = $conn->query("SELECT c.*, p.name FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = 1");
if ($cartResult && $cartResult->num_rows > 0) {
    echo "<h4>Cart Items (User 1):</h4><table><tr><th>ID</th><th>Product</th><th>Quantity</th></tr>";
    while ($cart = $cartResult->fetch_assoc()) {
        echo "<tr><td>{$cart['id']}</td><td>{$cart['name']}</td><td>{$cart['quantity']}</td></tr>";
    }
    echo "</table>";
}

echo "</div>";

// 6. Links to test pages
echo "<div class='section'>";
echo "<h2>🔗 Test Links</h2>";
echo "<a href='cart.php' target='_blank' class='test-button'>Open Cart Page</a>";
echo "<a href='orders.php' target='_blank' class='test-button'>Open Orders Page</a>";
echo "<a href='test_js_order.html' target='_blank' class='test-button'>JavaScript Test Page</a>";
echo "</div>";

$conn->close();
?>

<!-- Auto-test the API on page load -->
<script>
window.onload = function() {
    setTimeout(function() {
        testOrderAPI();
    }, 1000);
};
</script>