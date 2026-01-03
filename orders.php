<?php
session_start();
require_once 'admin-api/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$userId = $_SESSION['user_id'];

// Get user's orders
$ordersQuery = "SELECT o.*, COUNT(oi.id) as item_count 
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.user_id = ? 
                GROUP BY o.id 
                ORDER BY o.created_at DESC";
$ordersStmt = $conn->prepare($ordersQuery);
$ordersStmt->bind_param("i", $userId);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();
$orders = [];
while ($row = $ordersResult->fetch_assoc()) {
    $orders[] = $row;
}

// Get categories for navigation
$categoriesQuery = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Trendy Wear</title>
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
                <a href="orders.php" class="nav-link permanent-icon d-flex align-items-center gap-2 active">
                    <i class="fas fa-box"></i>
                    <span>My Orders</span>
                </a>
                <a href="cart.php" class="nav-link permanent-icon d-flex align-items-center gap-2">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Cart</span>
                    <span class="badge bg-secondary cart-count ms-1" id="cartCount">0</span>
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
    <section class="bg-light py-5 mt-5">
        <div class="container">
            <h1 class="display-4 fw-bold text-center">My Orders</h1>
            <p class="lead text-center text-muted">View and track your order history</p>
        </div>
    </section>

    <!-- ORDERS LIST -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($orders)): ?>
                <!-- No Orders -->
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-box-open fa-5x text-muted mb-4"></i>
                        <h3>No orders found</h3>
                        <p class="text-muted mb-4">You haven't placed any orders yet.</p>
                        <a href="products.php" class="btn btn-primary btn-lg px-5">Start Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Orders List -->
                <div class="row">
                    <?php foreach ($orders as $order): ?>
                        <div class="col-12 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <h6 class="mb-0">Order #<?= htmlspecialchars($order['order_number']) ?></h6>
                                            <small class="text-muted"><?= date('M j, Y', strtotime($order['created_at'])) ?></small>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="badge bg-<?= 
                                                $order['status'] === 'pending' ? 'warning' : 
                                                ($order['status'] === 'processing' ? 'info' : 
                                                ($order['status'] === 'shipped' ? 'primary' : 
                                                ($order['status'] === 'delivered' ? 'success' : 'danger'))) 
                                            ?>">
                                                <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                            </span>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="text-muted">Payment:</small><br>
                                            <span class="badge bg-<?= $order['payment_status'] === 'completed' ? 'success' : 'warning' ?>">
                                                <?= ucfirst(htmlspecialchars($order['payment_status'])) ?>
                                            </span>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="text-muted">Items:</small><br>
                                            <span><?= $order['item_count'] ?> item<?= $order['item_count'] != 1 ? 's' : '' ?></span>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="text-muted">Total:</small><br>
                                            <strong class="text-primary">$<?= number_format($order['total_amount'], 2) ?></strong>
                                        </div>
                                        <div class="col-md-1">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="toggleOrderDetails(<?= $order['id'] ?>)">
                                                <i class="fas fa-chevron-down" id="icon-<?= $order['id'] ?>"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Order Details (Initially Hidden) -->
                                <div class="order-details collapse" id="order-<?= $order['id'] ?>">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <!-- Order Items -->
                                                <h6>Order Items:</h6>
                                                <div id="items-<?= $order['id'] ?>">
                                                    <div class="text-center py-3">
                                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                        <span class="ms-2">Loading items...</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <!-- Shipping Address -->
                                                <?php 
                                                $shipping_address = json_decode($order['shipping_address'], true);
                                                if ($shipping_address):
                                                ?>
                                                <h6>Shipping Address:</h6>
                                                <address class="small">
                                                    <?= htmlspecialchars($shipping_address['first_name'] . ' ' . $shipping_address['last_name']) ?><br>
                                                    <?= htmlspecialchars($shipping_address['address']) ?><br>
                                                    <?= htmlspecialchars($shipping_address['city'] . ', ' . $shipping_address['state'] . ' ' . $shipping_address['zipcode']) ?><br>
                                                    <?= htmlspecialchars($shipping_address['country']) ?>
                                                </address>
                                                <?php endif; ?>
                                                
                                                <!-- Order Actions -->
                                                <div class="mt-3">
                                                    <a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary btn-sm mb-2 w-100">
                                                        <i class="fas fa-eye me-1"></i>View Details
                                                    </a>
                                                    
                                                    <?php if ($order['status'] === 'delivered'): ?>
                                                        <button class="btn btn-success btn-sm mb-2 w-100" onclick="reorderItems(<?= $order['id'] ?>)">
                                                            <i class="fas fa-redo me-1"></i>Reorder
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                                                        <button class="btn btn-outline-danger btn-sm mb-2 w-100" onclick="cancelOrder(<?= $order['id'] ?>)">
                                                            <i class="fas fa-times me-1"></i>Cancel Order
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($order['status'] === 'shipped'): ?>
                                                        <button class="btn btn-outline-info btn-sm mb-2 w-100" onclick="trackOrder(<?= $order['id'] ?>)">
                                                            <i class="fas fa-truck me-1"></i>Track Package
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
                        <li><a href="contact.html" class="text-white-50">Contact Us</a></li>
                        <li><a href="#" class="text-white-50">Shipping Info</a></li>
                        <li><a href="#" class="text-white-50">Return Policy</a></li>
                        <li><a href="#" class="text-white-50">Track Order</a></li>
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
        // Update cart count on page load
        updateCartCount();
        
        // Toggle order details
        function toggleOrderDetails(orderId) {
            const orderDetails = document.getElementById(`order-${orderId}`);
            const icon = document.getElementById(`icon-${orderId}`);
            const itemsContainer = document.getElementById(`items-${orderId}`);
            
            if (orderDetails.classList.contains('show')) {
                orderDetails.classList.remove('show');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                orderDetails.classList.add('show');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
                
                // Load order items if not already loaded
                if (!itemsContainer.dataset.loaded) {
                    loadOrderItems(orderId);
                }
            }
        }
        
        // Load order items via AJAX
        async function loadOrderItems(orderId) {
            const itemsContainer = document.getElementById(`items-${orderId}`);
            
            try {
                const response = await fetch(`api/order-items.php?order_id=${orderId}`);
                const data = await response.json();
                
                if (data.success) {
                    let itemsHtml = '';
                    data.items.forEach(item => {
                        itemsHtml += `
                            <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                                <img src="${item.image_url}" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 small">${item.name}</h6>
                                    <small class="text-muted">
                                        Qty: ${item.quantity}
                                        ${item.size ? ` | Size: ${item.size}` : ''}
                                        ${item.color ? ` | Color: ${item.color}` : ''}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">$${parseFloat(item.price).toFixed(2)} each</small><br>
                                    <span class="fw-bold">$${parseFloat(item.total).toFixed(2)}</span>
                                </div>
                            </div>
                        `;
                    });
                    itemsContainer.innerHTML = itemsHtml;
                    itemsContainer.dataset.loaded = 'true';
                } else {
                    itemsContainer.innerHTML = '<div class="text-danger">Error loading items</div>';
                }
            } catch (error) {
                console.error('Error loading order items:', error);
                itemsContainer.innerHTML = '<div class="text-danger">Error loading items</div>';
            }
        }
        
        // Reorder items
        async function reorderItems(orderId) {
            if (!confirm('Add all items from this order to your cart?')) return;
            
            try {
                const response = await fetch('api/reorder.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('Items added to cart!');
                    updateCartCount();
                } else {
                    alert(data.message || 'Error reordering items');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error reordering items');
            }
        }
        
        // Cancel order
        async function cancelOrder(orderId) {
            if (!confirm('Are you sure you want to cancel this order?')) return;
            
            try {
                const response = await fetch('api/cancel-order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('Order cancelled successfully');
                    location.reload();
                } else {
                    alert(data.message || 'Error cancelling order');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error cancelling order');
            }
        }
        
        // Track order (placeholder)
        function trackOrder(orderId) {
            alert(`Order tracking for #${orderId} will be implemented with shipping provider integration.`);
        }
        
        // Function to update cart count
        async function updateCartCount() {
            try {
                const response = await fetch('api/cart.php');
                const data = await response.json();
                if (data.success) {
                    document.getElementById('cartCount').textContent = data.item_count;
                }
            } catch (error) {
                console.error('Error updating cart count:', error);
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>