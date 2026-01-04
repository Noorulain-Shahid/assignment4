<?php
session_start();
require_once 'admin-api/db_connect.php';

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($productId <= 0) {
    header('Location: products.php');
    exit;
}

// Fetch categories for navbar dropdown
$categories = [];
$categoriesQuery = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$categoriesResult = $conn->query($categoriesQuery);
if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch the requested product
$stmt = $conn->prepare("SELECT p.*, c.name AS category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.is_active = 1 AND p.id = ? LIMIT 1");
$stmt->bind_param('i', $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Decode optional JSON fields
$product['additional_images'] = json_decode($product['additional_images'] ?? '[]', true) ?: [];
$product['sizes'] = json_decode($product['sizes'] ?? '[]', true) ?: [];
$product['colors'] = json_decode($product['colors'] ?? '[]', true) ?: [];
$primaryColor = $product['colors'][0] ?? null;

// Fetch related products (same category, excluding current)
$relatedProducts = [];
if (!empty($product['category_id'])) {
    $relStmt = $conn->prepare("SELECT p.* 
                               FROM products p 
                               WHERE p.is_active = 1 AND p.category_id = ? AND p.id <> ? 
                               ORDER BY p.created_at DESC 
                               LIMIT 8");
    $relStmt->bind_param('ii', $product['category_id'], $productId);
    $relStmt->execute();
    $relResult = $relStmt->get_result();
    while ($row = $relResult->fetch_assoc()) {
        $relatedProducts[] = $row;
    }
    $relStmt->close();
}

// Fetch reviews
$reviews = [];
$avgRating = 0;
$reviewCount = 0;

$reviewsQuery = "
    SELECT r.*, u.full_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
";
$reviewsStmt = $conn->prepare($reviewsQuery);
$reviewsStmt->bind_param("i", $productId);
$reviewsStmt->execute();
$reviewsResult = $reviewsStmt->get_result();

while ($row = $reviewsResult->fetch_assoc()) {
    $reviews[] = $row;
    $avgRating += $row['rating'];
}
$reviewCount = count($reviews);
if ($reviewCount > 0) {
    $avgRating = round($avgRating / $reviewCount, 1);
}
$reviewsStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Trendy Wear</title>
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
                        <a href="products.php" class="nav-link active">
                            Products <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <ul class="products-dropdown">
                            <?php foreach ($categories as $cat): ?>
                                <li><a href="products.php?category=<?= urlencode($cat['name']) ?>"><?php echo htmlspecialchars($cat['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="contact.html" class="nav-link">Contact Us</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                                <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a href="login.php" class="nav-link"><i class="fas fa-user"></i> Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="product-details-section" data-product-id="<?php echo $product['id']; ?>">
        <div class="container">
            <a href="products.php" class="back-link mb-4">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Products</span>
            </a>

            <div class="product-details-wrapper">
                <!-- Images column -->
                <div class="product-images">
                    <div class="main-image">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <div class="thumbnail-images">
                        <?php
                        $thumbs = !empty($product['additional_images']) ? $product['additional_images'] : [$product['image_url']];
                        foreach ($thumbs as $img): ?>
                            <div class="thumbnail">
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Info column -->
                <div class="product-info-detailed">
                    <?php if (!empty($product['category_name'])): ?>
                        <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    <?php endif; ?>

                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                    <div class="product-rating">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="rating-text">(4.5) 128 Reviews</span>
                    </div>

                    <div class="product-price-detailed">
                        <?php if (!empty($product['sale_price'] ?? null)): ?>
                            <span class="text-muted text-decoration-line-through" style="font-size: 24px;">Rs <?php echo number_format($product['price'], 0); ?></span>
                            <span class="ms-2">Rs <?php echo number_format($product['sale_price'], 0); ?></span>
                        <?php else: ?>
                            <span>Rs <?php echo number_format($product['price'], 0); ?></span>
                        <?php endif; ?>
                    </div>

                    <p class="product-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                    <?php if (!empty($product['sizes'])): ?>
                        <div class="size-selection">
                            <h3>Select Size:</h3>
                            <div class="size-options" id="sizeOptions">
                                <?php foreach ($product['sizes'] as $size): ?>
                                    <button type="button" class="size-option" onclick="selectSize(this)"><?php echo htmlspecialchars($size); ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($primaryColor)): ?>
                        <div class="color-selection">
                            <h3>Select Color:</h3>
                            <div class="color-options" id="colorOptions">
                                <button type="button" class="color-option active" onclick="selectColor(this)"><?php echo htmlspecialchars($primaryColor); ?></button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($product['stock_quantity'] > 0): ?>
                        <div class="quantity-selection">
                            <h3>Quantity:</h3>
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn" onclick="decreaseQuantity()">-</button>
                                <span class="quantity-display" id="quantityDisplay">1</span>
                                <button type="button" class="quantity-btn" onclick="increaseQuantity()">+</button>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button type="button" class="add-to-cart-large" onclick="addToCartDetailed(<?php echo $product['id']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3">This product is currently out of stock.</div>
                    <?php endif; ?>

                    <div class="product-features">
                        <div class="feature-item">
                            <div class="feature-icon"><i class="fas fa-shipping-fast"></i></div>
                            <div class="feature-text">
                                <h4>Fast Delivery</h4>
                                <p>Delivered to your doorstep quickly</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="fas fa-undo"></i></div>
                            <div class="feature-text">
                                <h4>Easy Returns</h4>
                                <p>30-day return policy</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                            <div class="feature-text">
                                <h4>Premium Quality</h4>
                                <p>Carefully selected materials</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="fas fa-headset"></i></div>
                            <div class="feature-text">
                                <h4>24/7 Support</h4>
                                <p>We are here to help you</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($relatedProducts)): ?>
        <section class="related-products">
            <div class="container">
                <h2 class="section-title text-center mb-5">You May Also Like</h2>
                <div class="row g-4">
                    <?php foreach ($relatedProducts as $rel): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="product-card h-100 border-0 shadow-sm" data-product-id="<?php echo $rel['id']; ?>">
                                <div class="product-image position-relative">
                                    <img src="<?php echo htmlspecialchars($rel['image_url']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                                    <?php if (!empty($rel['sale_price'] ?? null)): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">Sale</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h5 class="product-name mb-2"><?php echo htmlspecialchars($rel['name']); ?></h5>
                                    <div class="d-flex justify-content-between align-items-center product-actions">
                                        <div class="price-section">
                                            <?php if (!empty($rel['sale_price'] ?? null)): ?>
                                                <span class="text-decoration-line-through text-muted me-1">Rs <?php echo number_format($rel['price'], 0); ?></span>
                                                <span class="product-price">Rs <?php echo number_format($rel['sale_price'], 0); ?></span>
                                            <?php else: ?>
										<span class="product-price">Rs <?php echo number_format($rel['price'], 0); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="product-details.php?id=<?php echo $rel['id']; ?>" class="view-details-btn">View Details</a>
                                            <?php if ($rel['stock_quantity'] > 0): ?>
                                                <button class="add-to-cart-btn" onclick="addToCart(<?php echo $rel['id']; ?>)">
                                                    <i class="fas fa-cart-plus"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Reviews Section -->
    <section class="reviews-section py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">Customer Reviews</h2>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <h1 class="display-4 fw-bold"><?= $avgRating ?></h1>
                            <div class="text-warning mb-2">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="<?= $i <= $avgRating ? 'fas' : ($i - 0.5 <= $avgRating ? 'fas fa-star-half-alt' : 'far') ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted">Based on <?= $reviewCount ?> review<?= $reviewCount != 1 ? 's' : '' ?></p>
                        </div>
                    </div>

                    <?php if (empty($reviews)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                        </div>
                    <?php else: ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="card shadow-sm mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><?= htmlspecialchars($review['full_name']) ?></h6>
                                            <small class="text-muted"><?= date('M j, Y', strtotime($review['created_at'])) ?></small>
                                        </div>
                                        <div class="text-warning mb-2">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <i class="<?= $i <= $review['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                                        
                                        <?php if (!empty($review['reply'])): ?>
                                            <div class="mt-3 p-3 bg-light rounded border-start border-4 border-primary">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="fas fa-user-shield text-primary me-2"></i>
                                                    <strong class="text-primary">Trendy Wear Response</strong>
                                                </div>
                                                <p class="mb-0 small text-muted"><?= nl2br(htmlspecialchars($review['reply'])) ?></p>
                                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                                    <?= date('M j, Y', strtotime($review['reply_date'])) ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        let quantity = 1;
        let selectedSize = null;
        let selectedColor = null;

        function increaseQuantity() {
            quantity++;
            document.getElementById('quantityDisplay').textContent = quantity;
        }

        function decreaseQuantity() {
            if (quantity > 1) {
                quantity--;
                document.getElementById('quantityDisplay').textContent = quantity;
            }
        }

        function selectSize(button) {
            document.querySelectorAll('.size-option').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            selectedSize = button.textContent.trim();
        }

        function selectColor(button) {
            document.querySelectorAll('.color-option').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            selectedColor = button.textContent.trim();
        }

        async function addToCartDetailed(productId) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert('Please login to add items to cart');
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            try {
                const response = await fetch('cart_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Item added to cart!');
                    updateCartCount();
                } else {
                    alert(data.message || 'Error adding item to cart');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error adding item to cart');
            }
        }

        // Override script.js version to use server cart
        async function updateCartCount() {
            <?php if (isset($_SESSION['user_id'])): ?>
            try {
                const response = await fetch('cart_api.php');
                const data = await response.json();
                if (data.success) {
                    document.getElementById('cartCount').textContent = data.item_count;
                }
            } catch (error) {
                console.error('Error updating cart count:', error);
            }
            <?php endif; ?>
        }

        // Initialize cart badge on load
        updateCartCount();
    </script>
</body>
</html>
<?php $conn->close(); ?>
