<?php
session_start();

// Set test session
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

echo "<h2>Order API Test</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .result{margin:20px 0;padding:15px;border:1px solid #ccc;} .error{border-color:red;background:#ffe6e6;} .success{border-color:green;background:#e6ffe6;}</style>";

// Test with both debug and regular APIs
$apis = [
    'Debug API' => 'place_order_debug.php',
    'Regular API' => 'place_order_api.php'
];

foreach ($apis as $name => $apiFile) {
    echo "<div class='result'>";
    echo "<h3>$name Test</h3>";
    
    // Prepare POST data
    $postData = json_encode(['payment_method' => 'cash_on_delivery']);
    
    // Set up context for POST request
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Cookie: ' . (isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : session_name() . '=' . session_id())
            ],
            'content' => $postData
        ]
    ]);
    
    // Make the request
    $url = "http://localhost/assignment4/$apiFile";
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "<p class='error'>Failed to connect to $apiFile</p>";
        $error = error_get_last();
        echo "<p>Error: " . $error['message'] . "</p>";
    } else {
        $data = json_decode($response, true);
        if ($data) {
            if ($data['success'] ?? false) {
                echo "<div class='success'>";
                echo "<p><strong>Success!</strong></p>";
                echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
                echo "</div>";
            } else {
                echo "<div class='error'>";
                echo "<p><strong>Error:</strong> " . ($data['message'] ?? 'Unknown error') . "</p>";
                echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
                echo "</div>";
            }
        } else {
            echo "<p class='error'>Invalid JSON response</p>";
            echo "<pre>$response</pre>";
        }
    }
    echo "</div>";
}

// Check if debug log file exists
if (file_exists('place_order_debug.log')) {
    echo "<div class='result'>";
    echo "<h3>Debug Log</h3>";
    echo "<pre>" . file_get_contents('place_order_debug.log') . "</pre>";
    echo "</div>";
}

?>

<div style="margin:20px 0;">
<h3>Manual Tests</h3>
<p><a href="debug_system.php" target="_blank">View System Debug</a></p>
<p><a href="cart.php" target="_blank">View Cart Page</a></p>
<p><a href="orders.php" target="_blank">View Orders Page</a></p>
</div>