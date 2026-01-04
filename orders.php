<?php
session_start();
require_once 'admin-api/db_connect.php';

// Check if user is logged in
// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Check if orders table exists
$tablesQuery = "SHOW TABLES LIKE 'orders'";
$tablesResult = $conn->query($tablesQuery);
$ordersTableExists = $tablesResult->num_rows > 0;

$orders = [];
if ($ordersTableExists) {
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
    while ($row = $ordersResult->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Get categories for navigation (handle if categories table doesn't exist)
$categoriesQuery = "SHOW TABLES LIKE 'categories'";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
if ($categoriesResult->num_rows > 0) {
    $categoriesQuery = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
    $categoriesResult = $conn->query($categoriesQuery);
    if ($categoriesResult) {
        while ($row = $categoriesResult->fetch_assoc()) {
            $categories[] = $row;
        }
    }
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
            
            <div class="menu-arrow-container">
                <i class="fas fa-chevron-down menu-arrow"></i>
            </div>
        </div>
    </nav>

    <!-- PAGE HERO -->
    <section class="about-hero">
        <div class="container">
            <h1 class="page-title">My Orders</h1>
            <p class="page-subtitle">View and track your order history</p>
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
                                            <span class="badge bg-success">Cash on Delivery</span>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="text-muted">Items:</small><br>
                                            <span><?= $order['item_count'] ?> item<?= $order['item_count'] != 1 ? 's' : '' ?></span>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="text-muted">Total:</small><br>
                                            <strong class="text-primary">PKR <?= number_format($order['final_amount'] ?? $order['total_amount'], 0) ?></strong>
                                        </div>
                                        <div class="col-md-1">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="toggleOrderDetails(<?= $order['id'] ?>, '<?= $order['status'] ?>')">
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
                                                // Try to decode JSON shipping address, or use the text directly
                                                $shipping_address = json_decode($order['shipping_address'], true);
                                                if (!$shipping_address && !empty($order['shipping_address'])) {
                                                    // If not JSON, treat as plain text
                                                    $shipping_address = ['address' => $order['shipping_address']];
                                                }
                                                if ($shipping_address || !empty($order['shipping_city'])):
                                                ?>
                                                <h6>Shipping Address:</h6>
                                                <address class="small">
                                                    <?php if (isset($shipping_address['name'])): ?>
                                                        <?= htmlspecialchars($shipping_address['name']) ?><br>
                                                    <?php endif; ?>
                                                    <?php if (isset($shipping_address['address'])): ?>
                                                        <?= htmlspecialchars($shipping_address['address']) ?><br>
                                                    <?php elseif (!empty($order['shipping_address'])): ?>
                                                        <?= htmlspecialchars($order['shipping_address']) ?><br>
                                                    <?php endif; ?>
                                                    <?php if (!empty($order['shipping_city'])): ?>
                                                        <?= htmlspecialchars($order['shipping_city']) ?>
                                                        <?php if (!empty($order['shipping_postal_code'])): ?>
                                                            <?= htmlspecialchars($order['shipping_postal_code']) ?>
                                                        <?php endif; ?><br>
                                                    <?php endif; ?>
                                                </address>
                                                <?php endif; ?>
                                                
                                                <!-- Order Actions -->
                                                <div class="mt-3">
                                                    
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
                            <li><a href="products.php?category=Cargo Pant"><i class="fas fa-chevron-right"></i> Cargo Pant</a></li>
                            <li><a href="products.php?category=Sweater"><i class="fas fa-chevron-right"></i> Sweater</a></li>
                            <li><a href="products.php?category=Hoodie"><i class="fas fa-chevron-right"></i> Hoodie</a></li>
                            <li><a href="products.php?category=Jacket"><i class="fas fa-chevron-right"></i> Jacket</a></li>
                            <li><a href="products.php?category=Sweatshirt"><i class="fas fa-chevron-right"></i> Sweatshirt</a></li>
                            <li><a href="products.php?category=Hat"><i class="fas fa-chevron-right"></i> Hat</a></li>
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

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Write a Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm">
                        <input type="hidden" id="reviewProductId">
                        <input type="hidden" id="reviewOrderId">
                        <div class="mb-3 text-center">
                            <label class="form-label d-block">Rating</label>
                            <div class="rating-stars" style="font-size: 2rem; color: #ffc107; cursor: pointer;">
                                <i class="far fa-star" data-rating="1" onclick="setRating(1)"></i>
                                <i class="far fa-star" data-rating="2" onclick="setRating(2)"></i>
                                <i class="far fa-star" data-rating="3" onclick="setRating(3)"></i>
                                <i class="far fa-star" data-rating="4" onclick="setRating(4)"></i>
                                <i class="far fa-star" data-rating="5" onclick="setRating(5)"></i>
                            </div>
                            <input type="hidden" id="reviewRating" required>
                        </div>
                        <div class="mb-3">
                            <label for="reviewComment" class="form-label">Your Review</label>
                            <textarea class="form-control" id="reviewComment" rows="4" required placeholder="Share your experience with this product..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitReview()">Submit Review</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update cart count on page load
        updateCartCount();
        
        // Toggle order details
        function toggleOrderDetails(orderId, status) {
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
                    loadOrderItems(orderId, status);
                }
            }
        }
        
        // Load order items via AJAX
        async function loadOrderItems(orderId, status) {
            const itemsContainer = document.getElementById(`items-${orderId}`);
            
            try {
                const response = await fetch(`order_items_api.php?order_id=${orderId}`);
                const data = await response.json();
                
                if (data.success) {
                    let itemsHtml = '';
                    data.items.forEach(item => {
                        let reviewBtn = '';
                        if (status === 'delivered') {
                            reviewBtn = `
                                <button class="btn btn-sm btn-outline-warning mt-2" 
                                        onclick="openReviewModal(${item.product_id}, ${orderId})">
                                    <i class="fas fa-star"></i> Leave Feedback
                                </button>
                            `;
                        }

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
                                    ${reviewBtn}
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">PKR ${parseFloat(item.price).toFixed(0)} each</small><br>
                                    <span class="fw-bold">PKR ${parseFloat(item.subtotal || item.total).toFixed(0)}</span>
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

        // Review functions
        let reviewModal;
        
        function openReviewModal(productId, orderId) {
            document.getElementById('reviewProductId').value = productId;
            document.getElementById('reviewOrderId').value = orderId;
            document.getElementById('reviewRating').value = '';
            document.getElementById('reviewComment').value = '';
            setRating(0); // Reset stars
            
            if (!reviewModal) {
                reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
            }
            reviewModal.show();
        }

        function setRating(rating) {
            document.getElementById('reviewRating').value = rating;
            const stars = document.querySelectorAll('.rating-stars i');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('far');
                    star.classList.add('fas');
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                }
            });
        }

        async function submitReview() {
            const productId = document.getElementById('reviewProductId').value;
            const orderId = document.getElementById('reviewOrderId').value;
            const rating = document.getElementById('reviewRating').value;
            const comment = document.getElementById('reviewComment').value;

            if (!rating) {
                alert('Please select a rating');
                return;
            }
            if (!comment) {
                alert('Please write a comment');
                return;
            }

            try {
                const response = await fetch('submit_review.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: productId,
                        order_id: orderId,
                        rating: rating,
                        comment: comment
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Review submitted successfully!');
                    reviewModal.hide();
                } else {
                    alert(data.message || 'Error submitting review');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error submitting review');
            }
        }
        
        // Reorder items
        async function reorderItems(orderId) {
            if (!confirm('Add all items from this order to your cart?')) return;
            
            try {
                const response = await fetch('reorder_api.php', {
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
                const response = await fetch('cancel_order_api.php', {
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
                const response = await fetch('cart_api.php');
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