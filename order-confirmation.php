<?php
session_start();
require_once 'admin-api/db_connect.php';

// Check if user is logged in and has a recent order
if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_order_id'])) {
    header('Location: index.php');
    exit;
}

$orderId = $_SESSION['last_order_id'];
$orderNumber = $_SESSION['last_order_number'];

// Get order details
$orderQuery = "SELECT o.*, 
               JSON_UNQUOTE(JSON_EXTRACT(o.shipping_address, '$.first_name')) as first_name,
               JSON_UNQUOTE(JSON_EXTRACT(o.shipping_address, '$.last_name')) as last_name,
               JSON_UNQUOTE(JSON_EXTRACT(o.shipping_address, '$.email')) as email
               FROM orders o WHERE o.id = ? AND o.user_id = ?";
$orderStmt = $conn->prepare($orderQuery);
$orderStmt->bind_param("ii", $orderId, $_SESSION['user_id']);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$order = $orderResult->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Get order items
$itemsQuery = "SELECT oi.*, p.name, p.image_url 
               FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = ?";
$itemsStmt = $conn->prepare($itemsQuery);
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
$orderItems = [];
while ($row = $itemsResult->fetch_assoc()) {
    $orderItems[] = $row;
}

// Clear the session variables so page can't be accessed again
unset($_SESSION['last_order_id']);
unset($_SESSION['last_order_number']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Trendy Wear</title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- NAVIGATION BAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="images/logo.png" alt="Trendy Wear Logo" class="logo me-3" height="40">
                <span class="brand-name">Trendy Wear</span>
            </a>
            <div class="d-flex align-items-center">
                <a href="orders.php" class="btn btn-outline-primary me-3">
                    <i class="fas fa-box me-2"></i>My Orders
                </a>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Home
                </a>
            </div>
        </div>
    </nav>

    <!-- ORDER CONFIRMATION -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Success Message -->
                <div class="text-center mb-5">
                    <div class="success-icon mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h1 class="display-4 text-success mb-3">Order Confirmed!</h1>
                    <p class="lead">Thank you for your purchase, <?= htmlspecialchars($order['first_name']) ?>!</p>
                    <p class="text-muted">Your order has been received and is being processed.</p>
                </div>

                <!-- Order Details Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Details</h4>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-light text-primary fs-6"><?= htmlspecialchars($order['order_number']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-calendar me-2"></i>Order Date</h6>
                                <p><?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
                                
                                <h6><i class="fas fa-credit-card me-2"></i>Payment Method</h6>
                                <p class="text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($order['payment_method'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-truck me-2"></i>Order Status</h6>
                                <span class="badge bg-<?= $order['status'] === 'pending' ? 'warning' : 'success' ?> mb-3">
                                    <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                </span>
                                
                                <h6><i class="fas fa-money-bill me-2"></i>Payment Status</h6>
                                <span class="badge bg-<?= $order['payment_status'] === 'pending' ? 'warning' : 'success' ?>">
                                    <?= ucfirst(htmlspecialchars($order['payment_status'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Items Ordered</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="row align-items-center mb-3 pb-3 <?= $item !== end($orderItems) ? 'border-bottom' : '' ?>">
                                <div class="col-md-2 col-3">
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                         class="img-fluid rounded" 
                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                         style="height: 60px; width: 60px; object-fit: cover;">
                                </div>
                                <div class="col-md-6 col-9">
                                    <h6><?= htmlspecialchars($item['name']) ?></h6>
                                    <small class="text-muted">
                                        Quantity: <?= $item['quantity'] ?>
                                        <?= $item['size'] ? " | Size: " . htmlspecialchars($item['size']) : "" ?>
                                        <?= $item['color'] ? " | Color: " . htmlspecialchars($item['color']) : "" ?>
                                    </small>
                                </div>
                                <div class="col-md-2">
                                    <p class="mb-0">$<?= number_format($item['price'], 2) ?></p>
                                </div>
                                <div class="col-md-2">
                                    <p class="mb-0 fw-bold">$<?= number_format($item['total'], 2) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <?php 
                                $shipping_address = json_decode($order['shipping_address'], true);
                                ?>
                                <h6><i class="fas fa-shipping-fast me-2"></i>Shipping Address</h6>
                                <address>
                                    <?= htmlspecialchars($shipping_address['first_name'] . ' ' . $shipping_address['last_name']) ?><br>
                                    <?= htmlspecialchars($shipping_address['address']) ?><br>
                                    <?= htmlspecialchars($shipping_address['city'] . ', ' . $shipping_address['state'] . ' ' . $shipping_address['zipcode']) ?><br>
                                    <?= htmlspecialchars($shipping_address['country']) ?>
                                </address>
                            </div>
                            <div class="col-md-6">
                                <div class="order-totals">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>$<?= number_format($order['total_amount'] - $order['tax_amount'] - $order['shipping_amount'], 2) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax:</span>
                                        <span>$<?= number_format($order['tax_amount'], 2) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Shipping:</span>
                                        <span><?= $order['shipping_amount'] > 0 ? '$' . number_format($order['shipping_amount'], 2) : 'Free' ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total:</strong>
                                        <strong class="text-primary">$<?= number_format($order['total_amount'], 2) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>What's Next?</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-envelope text-primary me-3" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6>Email Confirmation</h6>
                                        <p class="mb-0 small text-muted">You'll receive an email confirmation at <?= htmlspecialchars($order['email']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-truck text-primary me-3" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6>Shipping Updates</h6>
                                        <p class="mb-0 small text-muted">We'll notify you when your order ships with tracking information</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-headset text-primary me-3" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6>Customer Support</h6>
                                        <p class="mb-0 small text-muted">Questions? Contact us at support@trendywear.com</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-redo text-primary me-3" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6>Easy Returns</h6>
                                        <p class="mb-0 small text-muted">30-day return policy on all items</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <a href="orders.php" class="btn btn-outline-primary btn-lg w-100">
                            <i class="fas fa-list me-2"></i>View All Orders
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="products.php" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2026 Trendy Wear. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>