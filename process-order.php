<?php
session_start();
require_once 'admin-api/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
       header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Get cart items
    $cartQuery = "SELECT c.*, p.name, p.price, p.sale_price, p.stock_quantity 
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
        // Check stock availability
        if ($row['stock_quantity'] < $row['quantity']) {
            throw new Exception("Insufficient stock for " . $row['name']);
        }
        
        $price = $row['sale_price'] ?: $row['price'];
        $row['item_total'] = $price * $row['quantity'];
        $subtotal += $row['item_total'];
        $cartItems[] = $row;
    }
    
    if (empty($cartItems)) {
        throw new Exception("Cart is empty");
    }
    
    // Calculate totals
    $tax = $subtotal * 0.08;
    $shipping = $subtotal > 100 ? 0 : 10;
    $total = $subtotal + $tax + $shipping;
    
    // Generate order number
    $orderNumber = 'TW' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
    // Prepare shipping address
    $shippingAddress = json_encode([
        'first_name' => $_POST['firstName'],
        'last_name' => $_POST['lastName'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'zipcode' => $_POST['zipcode'],
        'country' => $_POST['country']
    ]);
    
    // For now, billing address is same as shipping
    $billingAddress = $shippingAddress;
    
    // Create order
    $orderStmt = $conn->prepare("
        INSERT INTO orders (
            user_id, order_number, total_amount, tax_amount, shipping_amount, 
            status, payment_status, payment_method, shipping_address, billing_address, notes
        ) VALUES (?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?)
    ");
    
    $paymentMethod = $_POST['paymentMethod'];
    $orderNotes = $_POST['orderNotes'] ?? '';
    
    $orderStmt->bind_param("isdddsssss", 
        $userId, $orderNumber, $total, $tax, $shipping,
        $paymentMethod, $shippingAddress, $billingAddress, $orderNotes
    );
    
    if (!$orderStmt->execute()) {
        throw new Exception("Error creating order");
    }
    
    $orderId = $conn->insert_id;
    
    // Create order items and update stock
    $orderItemStmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, size, color, price, total) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $updateStockStmt = $conn->prepare("
        UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?
    ");
    
    foreach ($cartItems as $item) {
        $price = $item['sale_price'] ?: $item['price'];
        
        // Add order item
        $orderItemStmt->bind_param("iiissdd", 
            $orderId, $item['product_id'], $item['quantity'], 
            $item['size'], $item['color'], $price, $item['item_total']
        );
        
        if (!$orderItemStmt->execute()) {
            throw new Exception("Error adding order item");
        }
        
        // Update stock
        $updateStockStmt->bind_param("ii", $item['quantity'], $item['product_id']);
        if (!$updateStockStmt->execute()) {
            throw new Exception("Error updating stock");
        }
    }
    
    // Clear cart
    $clearCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearCartStmt->bind_param("i", $userId);
    if (!$clearCartStmt->execute()) {
        throw new Exception("Error clearing cart");
    }
    
    // Simulate payment processing
    if ($paymentMethod === 'credit_card') {
        // In a real application, you would integrate with a payment gateway
        // For demo purposes, we'll just mark it as completed
        $updatePaymentStmt = $conn->prepare("UPDATE orders SET payment_status = 'completed', status = 'processing' WHERE id = ?");
        $updatePaymentStmt->bind_param("i", $orderId);
        $updatePaymentStmt->execute();
    } else if ($paymentMethod === 'paypal') {
        // PayPal integration would go here
        $updatePaymentStmt = $conn->prepare("UPDATE orders SET payment_status = 'completed', status = 'processing' WHERE id = ?");
        $updatePaymentStmt->bind_param("i", $orderId);
        $updatePaymentStmt->execute();
    }
    // Cash on delivery stays as 'pending' payment
    
    // Commit transaction
    $conn->commit();
    
    // Store order ID in session for confirmation page
    $_SESSION['last_order_id'] = $orderId;
    $_SESSION['last_order_number'] = $orderNumber;
    
    // Redirect to confirmation page
    header('Location: order-confirmation.php');
    exit;
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    
    // Log error
    error_log("Order processing error: " . $e->getMessage());
    
    // Redirect back to checkout with error
    $_SESSION['checkout_error'] = $e->getMessage();
    header('Location: checkout.php');
    exit;
}

$conn->close();
?>