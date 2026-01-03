<?php
session_start();
require_once 'admin-api/db_connect.php';

// Ensure cart table exists (matches current schema with created/updated timestamps)
$conn->query("CREATE TABLE IF NOT EXISTS cart (\n    id INT AUTO_INCREMENT PRIMARY KEY,\n    user_id INT NOT NULL,\n    product_id INT NOT NULL,\n    quantity INT NOT NULL DEFAULT 1,\n    size VARCHAR(10),\n    color VARCHAR(50),\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n) ENGINE=InnoDB;");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get cart items
$cartError = null;
$cartQuery = "SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = ? 
              ORDER BY COALESCE(c.updated_at, c.created_at) DESC";
$cartStmt = $conn->prepare($cartQuery);
if (!$cartStmt) {
    // Fallback if updated_at/created_at columns differ
    $cartError = $conn->error;
    error_log('Cart query prepare failed (primary): ' . $conn->error . ' SQL: ' . $cartQuery);
    $cartQuery = "SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = ? 
                  ORDER BY c.id DESC";
    $cartStmt = $conn->prepare($cartQuery);
}

if ($cartStmt) {
    $cartStmt->bind_param("i", $userId);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();
} else {
    // Final failure: show friendly message on page
    $cartError = $cartError ?: $conn->error;
    error_log('Cart query prepare failed (fallback): ' . $conn->error);
    $cartResult = false;
}

$cartItems = [];
$subtotal = 0;
if ($cartResult) {
    while ($row = $cartResult->fetch_assoc()) {
        $price = $row['price'];
        $row['item_total'] = $price * $row['quantity'];
        $subtotal += $row['item_total'];
        $cartItems[] = $row;
    }
}

// Get categories for navigation
$categoriesQuery = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row;
}

$tax = $subtotal * 0.08; // 8% tax
$shipping = $subtotal > 5000 ? 0 : 500; // Free shipping over PKR 5000
$total = $subtotal + $tax + $shipping;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Trendy Wear</title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- NAVIGATION BAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm" id="navbar">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="images/logo.png" alt="Trendy Wear Logo" class="logo me-3" height="85">
                <span class="brand-name">Trendy Wear</span>
            </a>
            <div class="permanent-icons d-flex align-items-center gap-2">
                <a href="orders.php" class="nav-link permanent-icon d-flex align-items-center gap-2">
                    <i class="fas fa-box"></i>
                    <span>My Orders</span>
                </a>
                <a href="cart.php" class="nav-link permanent-icon d-flex align-items-center gap-2 active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Cart</span>
                    <span class="badge bg-secondary cart-count ms-1" id="cartCount"><?= count($cartItems) ?></span>
                </a>
            </div>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav nav-menu-hidden mx-auto" id="navMenu">
                    <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="about.html" class="nav-link">About Us</a></li>
                    <li class="nav-item nav-item-dropdown">
                        <a href="products.php" class="nav-link">
                            Products <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <ul class="products-dropdown">
                            <?php foreach ($categories as $category): ?>
                                <li><a href="products.php?category=<?= urlencode($category['name']) ?>"><?= htmlspecialchars($category['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="contact.html" class="nav-link">Contact Us</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            
            <div class="menu-arrow-container">
                <i class="fas fa-chevron-down menu-arrow"></i>
            </div>
        </div>
    </nav>

    <!-- PAGE HERO -->
    <section class="cart-hero about-hero">
        <div class="container">
            <h1 class="page-title">Shopping Cart</h1>
            <p class="page-subtitle">Review your items and proceed to checkout</p>
        </div>
    </section>

    <!-- SHOPPING CART -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($cartItems)): ?>
                <!-- Empty Cart -->
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center py-5">
                        <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                        <h3 class="mb-3">Your cart is empty</h3>
                        <p class="text-muted mb-4">Add some products to get started!</p>
                        <a href="products.php" class="btn btn-lg px-5" style="background-color: #D4C5B0; color: #3E3E3E; border: none;">Shop Now</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Cart Items -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-bottom">
                                <h4 class="mb-0 fw-bold">Cart Items (<?= count($cartItems) ?>)</h4>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($cartItems as $index => $item): ?>
                                    <?php $price = $item['price']; ?>
                                    <div class="cart-item-row p-4 <?= $index < count($cartItems) - 1 ? 'border-bottom' : '' ?>" data-cart-id="<?= $item['id'] ?>">
                                        <div class="row align-items-center">
                                            <div class="col-md-2 col-3">
                                                <div class="cart-item-image">
                                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                         class="img-fluid rounded" 
                                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                                         style="height: 80px; width: 80px; object-fit: cover;">
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-9">
                                                <h5 class="cart-item-name mb-1"><?= htmlspecialchars($item['name']) ?></h5>
                                                <div class="cart-item-price text-muted">
                                                    <strong>PKR <?= number_format($item['price'], 0) ?></strong>
                                                </div>
                                                <?php if ($item['size']): ?>
                                                    <small class="text-muted d-block">Size: <?= htmlspecialchars($item['size']) ?></small>
                                                <?php endif; ?>
                                                <?php if ($item['color']): ?>
                                                    <small class="text-muted d-block">Color: <?= htmlspecialchars($item['color']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="quantity-controls d-flex align-items-center justify-content-center">
                                                    <button class="btn btn-outline-secondary btn-sm" 
                                                            onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)"
                                                            <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>
                                                            style="width: 35px; height: 35px;">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <span class="mx-3 fw-bold fs-5"><?= $item['quantity'] ?></span>
                                                    <button class="btn btn-outline-secondary btn-sm" 
                                                            onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)"
                                                            <?= $item['quantity'] >= $item['stock_quantity'] ? 'disabled' : '' ?>
                                                            style="width: 35px; height: 35px;">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                <?php if ($item['quantity'] >= $item['stock_quantity']): ?>
                                                    <small class="text-warning d-block text-center mt-1">Max quantity</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="text-center flex-grow-1">
                                                        <div class="fw-bold fs-6">PKR <?= number_format($item['item_total'], 0) ?></div>
                                                    </div>
                                                    <div class="text-center">
                                                        <button class="btn btn-outline-danger btn-sm ms-3" 
                                                                onclick="removeFromCart(<?= $item['id'] ?>)"
                                                                title="Remove item"
                                                                style="width: 35px; height: 35px;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Continue Shopping -->
                        <div class="mt-4">
                            <a href="products.php" class="btn px-4" style="background-color: #F5F5DC; color: #3E3E3E; border: 1px solid #D4C5B0;">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 position-sticky" style="top: 100px;">
                            <div class="card-header bg-white border-bottom">
                                <h4 class="mb-0 fw-bold">Order Summary</h4>
                            </div>
                            <div class="card-body">
                                <div class="summary-item d-flex justify-content-between mb-3">
                                    <span>Subtotal:</span>
                                    <span class="fw-semibold">PKR <?= number_format($subtotal, 0) ?></span>
                                </div>
                                <div class="summary-item d-flex justify-content-between mb-3">
                                    <span>Tax (8%):</span>
                                    <span class="fw-semibold">PKR <?= number_format($tax, 0) ?></span>
                                </div>
                                <div class="summary-item d-flex justify-content-between mb-4">
                                    <span>Shipping:</span>
                                    <span class="fw-semibold <?= $shipping > 0 ? '' : 'text-success' ?>"><?= $shipping > 0 ? 'PKR ' . number_format($shipping, 0) : 'Free' ?></span>
                                </div>
                                <?php if ($subtotal < 5000 && $shipping > 0): ?>
                                    <div class="alert alert-info small border-0 mb-4" style="background-color: #f8f9fa;">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Add PKR <?= number_format(5000 - $subtotal, 0) ?> more for free shipping!
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Payment Method Selection -->
                                <div class="payment-method-section mb-4">
                                    <h6 class="fw-bold mb-3">Select Payment Method</h6>
                                    <div class="payment-options">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="paymentMethod" id="cashOnDelivery" value="cash_on_delivery" checked>
                                            <label class="form-check-label" for="cashOnDelivery">
                                                <i class="fas fa-money-bill-wave me-2 text-success"></i>
                                                Cash on Delivery
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="paymentMethod" id="cardPayment" value="card_payment">
                                            <label class="form-check-label" for="cardPayment">
                                                <i class="fas fa-credit-card me-2 text-primary"></i>
                                                Card Payment
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="my-3">
                                <div class="summary-total d-flex justify-content-between mb-4">
                                    <strong class="fs-5">Total:</strong>
                                    <strong class="fs-5" style="color: #D4C5B0;">PKR <?= number_format($total, 0) ?></strong>
                                </div>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-lg py-3" onclick="placeOrder()" 
                                           style="background-color: #D4C5B0; color: #3E3E3E; border: none; font-weight: 600;">
                                        <span id="checkoutBtnText">
                                            Place Order
                                            <i class="fas fa-arrow-right ms-2"></i>
                                        </span>
                                    </button>
                                </div>
                                
                                <!-- Security badges -->
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-lock me-1"></i>
                                        Secure checkout with 256-bit SSL encryption
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer-section">
        <div class="container">
            <div class="footer-content py-5">
                <div class="row g-4">
                    <!-- Brand Column -->
                    <div class="col-lg-4 col-md-6">
                        <h5 class="footer-title mb-4">About Us</h5>
                        <p class="footer-description mb-4">Your destination for trendy and affordable fashion. Discover your style, define your elegance.</p>
                        <div class="social-links">
                            <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-icon" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-icon" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
                        </div>
                    </div>

                    <!-- Quick Links Column -->
                    <div class="col-lg-2 col-md-6">
                        <h5 class="footer-title mb-4">Quick Links</h5>
                        <ul class="footer-links list-unstyled">
                            <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                            <li><a href="products.php"><i class="fas fa-chevron-right"></i> Products</a></li>
                            <li><a href="about.html"><i class="fas fa-chevron-right"></i> About Us</a></li>
                            <li><a href="contact.html"><i class="fas fa-chevron-right"></i> Contact</a></li>
                        </ul>
                    </div>

                    <!-- Shop Categories Column -->
                    <div class="col-lg-3 col-md-6">
                        <h5 class="footer-title mb-4">Shop By</h5>
                        <ul class="footer-links list-unstyled">
                            <li><a href="products.php?gender=Men"><i class="fas fa-chevron-right"></i> Men's Collection</a></li>
                            <li><a href="products.php?gender=Women"><i class="fas fa-chevron-right"></i> Women's Collection</a></li>
                            <li><a href="products.php?gender=Kids"><i class="fas fa-chevron-right"></i> Kids Collection</a></li>
                            <li><a href="orders.php"><i class="fas fa-chevron-right"></i> My Orders</a></li>
                        </ul>
                    </div>

                    <!-- Contact Info Column -->
                    <div class="col-lg-3 col-md-6">
                        <h5 class="footer-title mb-4">Contact Info</h5>
                        <ul class="footer-contact list-unstyled">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>123 Fashion Street<br>Style City, SC 12345</span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span>(123) 456-7890</span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span>info@trendywear.com</span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span>Mon - Sat: 9AM - 8PM</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom py-4">
                <div class="text-center">
                    <p class="mb-0">&copy; 2026 <strong>Trendy Wear</strong>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Order Confirmation Modal -->
    <div class="modal fade" id="orderConfirmationModal" tabindex="-1" aria-labelledby="orderConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 text-center">
                    <div class="w-100">
                        <div class="text-success mb-3">
                            <i class="fas fa-check-circle fa-4x"></i>
                        </div>
                        <h4 class="modal-title" id="orderConfirmationModalLabel">Order Placed Successfully!</h4>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="order-summary-modal">
                        <div class="row mb-3">
                            <div class="col-6"><strong>Order Number:</strong></div>
                            <div class="col-6" id="orderNumber">#ORD-<?= date('Ymd') ?>-<?= rand(1000, 9999) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6"><strong>Payment Method:</strong></div>
                            <div class="col-6" id="paymentMethodDisplay"></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6"><strong>Total Items:</strong></div>
                            <div class="col-6"><?= count($cartItems) ?> items</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6"><strong>Subtotal:</strong></div>
                            <div class="col-6">PKR <?= number_format($subtotal, 0) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6"><strong>Tax (8%):</strong></div>
                            <div class="col-6">PKR <?= number_format($tax, 0) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6"><strong>Shipping:</strong></div>
                            <div class="col-6"><?= $shipping > 0 ? 'PKR ' . number_format($shipping, 0) : 'Free' ?></div>
                        </div>
                        <hr>
                        <div class="row mb-4">
                            <div class="col-6"><strong class="fs-5">Total Amount:</strong></div>
                            <div class="col-6"><strong class="fs-5" style="color: #D4C5B0;">PKR <?= number_format($total, 0) ?></strong></div>
                        </div>
                        <div class="alert alert-info border-0" style="background-color: #f8f9fa;">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Estimated Delivery:</strong> 3-5 business days
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn px-4" style="background-color: #D4C5B0; color: #3E3E3E; border: none;" onclick="location.href='orders.php'">
                        View My Orders
                    </button>
                    <button type="button" class="btn px-4" style="background-color: #F5F5DC; color: #3E3E3E; border: 1px solid #D4C5B0;" onclick="location.href='products.php'">
                        Continue Shopping
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Place order function
        async function placeOrder() {
            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
            const checkoutBtn = document.getElementById('checkoutBtnText');
            const originalText = checkoutBtn.innerHTML;
            
            // Show loading state
            checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Order...';
            document.querySelector('button[onclick="placeOrder()"]').disabled = true;
            
            try {
                const response = await fetch('place_order_clean.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        payment_method: paymentMethod
                    })
                });
                
                const responseText = await response.text();
                console.log('API Response Text:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    throw new Error('Invalid JSON response: ' + responseText);
                }
                
                if (data.success) {
                    // Update payment method display in modal
                    const paymentDisplay = document.getElementById('paymentMethodDisplay');
                    paymentDisplay.innerHTML = paymentMethod === 'cash_on_delivery' 
                        ? '<i class="fas fa-money-bill-wave text-success"></i> Cash on Delivery'
                        : '<i class="fas fa-credit-card text-primary"></i> Card Payment';
                    
                    // Update order number
                    document.getElementById('orderNumber').textContent = data.order.order_number;
                    
                    // Show success modal
                    const modal = new bootstrap.Modal(document.getElementById('orderConfirmationModal'));
                    modal.show();
                    
                    // Redirect to orders page after modal is shown
                    setTimeout(() => {
                        window.location.href = 'orders.php';
                    }, 3000);
                    
                } else {
                    alert(data.message || 'Error placing order. Please try again.');
                    console.error('API Error:', data);
                }
                
            } catch (error) {
                console.error('Order error:', error);
                alert('Error placing order: ' + error.message);
            } finally {
                // Reset button state
                checkoutBtn.innerHTML = originalText;
                document.querySelector('button[onclick="placeOrder()"]').disabled = false;
            }
        }
        // Update cart item quantity
        async function updateQuantity(cartId, newQuantity) {
            if (newQuantity < 1) return;
            
            try {
                const response = await fetch('cart_api.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cart_id: cartId,
                        quantity: newQuantity
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    location.reload(); // Refresh page to update totals
                } else {
                    alert(data.message || 'Error updating quantity');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating quantity');
            }
        }
        
        // Remove item from cart
        async function removeFromCart(cartId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }
            
            try {
                const response = await fetch(`cart_api.php?cart_id=${cartId}`, {
                    method: 'DELETE'
                });
                
                const data = await response.json();
                if (data.success) {
                    location.reload(); // Refresh page
                } else {
                    alert(data.message || 'Error removing item');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error removing item');
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>