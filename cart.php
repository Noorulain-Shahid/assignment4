<?php
session_start();
require_once 'admin-api/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$userId = $_SESSION['user_id'];

// Get cart items
$cartQuery = "SELECT c.*, p.name, p.price, p.sale_price, p.image_url, p.stock_quantity 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = ? 
              ORDER BY c.added_at DESC";
$cartStmt = $conn->prepare($cartQuery);
$cartStmt->bind_param("i", $userId);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();

$cartItems = [];
$subtotal = 0;
while ($row = $cartResult->fetch_assoc()) {
    $price = $row['sale_price'] ?: $row['price'];
    $row['item_total'] = $price * $row['quantity'];
    $subtotal += $row['item_total'];
    $cartItems[] = $row;
}

// Get categories for navigation
$categoriesQuery = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row;
}

$tax = $subtotal * 0.08; // 8% tax
$shipping = $subtotal > 100 ? 0 : 10; // Free shipping over $100
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
        </div>
    </nav>

    <!-- PAGE HERO -->
    <section class="cart-hero bg-light py-5 mt-5">
        <div class="container">
            <h1 class="display-4 fw-bold text-center">Shopping Cart</h1>
            <p class="lead text-center text-muted">Review your items and proceed to checkout</p>
        </div>
    </section>

    <!-- SHOPPING CART -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($cartItems)): ?>
                <!-- Empty Cart -->
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                        <h3>Your cart is empty</h3>
                        <p class="text-muted mb-4">Add some products to get started!</p>
                        <a href="products.php" class="btn btn-primary btn-lg px-5">Shop Now</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Cart Items -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h4 class="mb-0">Cart Items (<?= count($cartItems) ?>)</h4>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($cartItems as $index => $item): ?>
                                    <?php $price = $item['sale_price'] ?: $item['price']; ?>
                                    <div class="cart-item p-4 <?= $index < count($cartItems) - 1 ? 'border-bottom' : '' ?>" data-cart-id="<?= $item['id'] ?>">
                                        <div class="row align-items-center">
                                            <div class="col-md-2 col-4">
                                                <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                     class="img-fluid rounded" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                                     style="height: 80px; width: 80px; object-fit: cover;">
                                            </div>
                                            <div class="col-md-4 col-8">
                                                <h5><?= htmlspecialchars($item['name']) ?></h5>
                                                <?php if ($item['size']): ?>
                                                    <small class="text-muted">Size: <?= htmlspecialchars($item['size']) ?></small><br>
                                                <?php endif; ?>
                                                <?php if ($item['color']): ?>
                                                    <small class="text-muted">Color: <?= htmlspecialchars($item['color']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2 col-4">
                                                <div class="price">
                                                    <?php if ($item['sale_price']): ?>
                                                        <span class="text-decoration-line-through text-muted small">$<?= number_format($item['price'], 2) ?></span><br>
                                                        <span class="text-danger fw-bold">$<?= number_format($item['sale_price'], 2) ?></span>
                                                    <?php else: ?>
                                                        <span class="fw-bold">$<?= number_format($item['price'], 2) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-2 col-4">
                                                <div class="quantity-controls d-flex align-items-center">
                                                    <button class="btn btn-outline-secondary btn-sm" 
                                                            onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)"
                                                            <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <span class="mx-3 fw-bold"><?= $item['quantity'] ?></span>
                                                    <button class="btn btn-outline-secondary btn-sm" 
                                                            onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)"
                                                            <?= $item['quantity'] >= $item['stock_quantity'] ? 'disabled' : '' ?>>
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                <?php if ($item['quantity'] >= $item['stock_quantity']): ?>
                                                    <small class="text-warning">Max quantity</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2 col-4">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold">$<?= number_format($item['item_total'], 2) ?></span>
                                                    <button class="btn btn-outline-danger btn-sm ms-2" 
                                                            onclick="removeFromCart(<?= $item['id'] ?>)"
                                                            title="Remove item">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Continue Shopping -->
                        <div class="mt-4">
                            <a href="products.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm position-sticky" style="top: 100px;">
                            <div class="card-header bg-white">
                                <h4 class="mb-0">Order Summary</h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>$<?= number_format($subtotal, 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax (8%):</span>
                                    <span>$<?= number_format($tax, 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Shipping:</span>
                                    <span><?= $shipping > 0 ? '$' . number_format($shipping, 2) : 'Free' ?></span>
                                </div>
                                <?php if ($subtotal < 100 && $shipping > 0): ?>
                                    <div class="alert alert-info small">
                                        <i class="fas fa-info-circle"></i>
                                        Add $<?= number_format(100 - $subtotal, 2) ?> more for free shipping!
                                    </div>
                                <?php endif; ?>
                                <hr>
                                <div class="d-flex justify-content-between mb-4">
                                    <strong>Total:</strong>
                                    <strong class="text-primary">$<?= number_format($total, 2) ?></strong>
                                </div>
                                <div class="d-grid">
                                    <a href="checkout.php" class="btn btn-primary btn-lg">
                                        Proceed to Checkout
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
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
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Trendy Wear</h5>
                    <p>Your destination for trendy and affordable fashion.</p>
                </div>
                <div class="col-md-4">
                    <h5>Customer Service</h5>
                    <ul class="list-unstyled">
                        <li><a href="contact.php" class="text-white-50">Contact Us</a></li>
                        <li><a href="#" class="text-white-50">Shipping Info</a></li>
                        <li><a href="#" class="text-white-50">Return Policy</a></li>
                        <li><a href="#" class="text-white-50">Size Guide</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <p class="text-white-50">
                        <i class="fas fa-envelope"></i> info@trendywear.com<br>
                        <i class="fas fa-phone"></i> (123) 456-7890<br>
                        <i class="fas fa-map-marker-alt"></i> 123 Fashion Street, Style City
                    </p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; 2026 Trendy Wear. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update cart item quantity
        async function updateQuantity(cartId, newQuantity) {
            if (newQuantity < 1) return;
            
            try {
                const response = await fetch('api/cart.php', {
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
                const response = await fetch(`api/cart.php?cart_id=${cartId}`, {
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