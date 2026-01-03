<?php
// Direct test of place_order_api.php without session complications

echo "<h1>Direct API Test</h1>";
echo "<style>body{padding:20px;font-family:Arial;} .error{color:red;background:#ffe6e6;padding:10px;margin:10px 0;} .success{color:green;background:#e6ffe6;padding:10px;margin:10px 0;} .info{color:blue;background:#e6f3ff;padding:10px;margin:10px 0;}</style>";

session_start();

// Force set session
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

echo "<div class='info'>Session set: user_id = 1</div>";

// Check database connection
require_once 'admin-api/db_connect.php';

if ($conn->connect_error) {
    echo "<div class='error'>Database connection failed: " . $conn->connect_error . "</div>";
    exit;
} else {
    echo "<div class='success'>Database connected successfully</div>";
}

// Check if we have products and cart items
$productsCount = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$cartCount = $conn->query("SELECT COUNT(*) FROM cart WHERE user_id = 1")->fetch_row()[0];

echo "<div class='info'>Products in database: $productsCount</div>";
echo "<div class='info'>Cart items for user 1: $cartCount</div>";

if ($cartCount == 0 && $productsCount > 0) {
    // Add some test items to cart
    $conn->query("INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) SELECT 1, id, 1, NOW(), NOW() FROM products LIMIT 2");
    $cartCount = $conn->query("SELECT COUNT(*) FROM cart WHERE user_id = 1")->fetch_row()[0];
    echo "<div class='info'>Added test items to cart. New count: $cartCount</div>";
}

// Now simulate the order API call
if ($cartCount > 0) {
    echo "<h2>Simulating Order Placement</h2>";
    
    // Simulate POST request to place_order_api.php
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Capture the output
    ob_start();
    
    // Include the API file but catch any output
    try {
        // Mock the input
        $GLOBALS['mock_input'] = '{"payment_method":"cash_on_delivery"}';
        
        // Override file_get_contents for this test
        function file_get_contents($filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null) {
            if ($filename === 'php://input') {
                return $GLOBALS['mock_input'];
            }
            return call_user_func_array('file_get_contents', func_get_args());
        }
        
        include 'place_order_api.php';
        
    } catch (Exception $e) {
        echo "<div class='error'>Exception occurred: " . $e->getMessage() . "</div>";
    }
    
    $output = ob_get_clean();
    
    echo "<h3>API Response:</h3>";
    echo "<pre style='background:#f8f9fa;padding:15px;border:1px solid #ddd;'>$output</pre>";
    
    // Try to decode as JSON
    $decoded = json_decode($output, true);
    if ($decoded) {
        if ($decoded['success'] ?? false) {
            echo "<div class='success'>Order placed successfully!</div>";
        } else {
            echo "<div class='error'>Order failed: " . ($decoded['message'] ?? 'Unknown error') . "</div>";
        }
    }
    
} else {
    echo "<div class='error'>No cart items available for testing</div>";
}

$conn->close();
?>