<?php
session_start();
require_once 'admin-api/db_connect.php';

// Get products to show in the Featured Products section
$featuredProducts = [];
$featuredError = null;

// 1) Try to load explicitly featured products (prefer these)
$featuredQuery = "SELECT * FROM products WHERE is_active = 1 AND is_featured = 1 ORDER BY created_at DESC LIMIT 8";
$featuredResult = $conn->query($featuredQuery);

if ($featuredResult === false) {
    // SQL error
    $featuredError = $conn->error;
} else {
    while ($row = $featuredResult->fetch_assoc()) {
        $featuredProducts[] = $row;
    }

    // 2) If no explicitly featured products, fall back to latest active products
    if (empty($featuredProducts)) {
        $featuredQuery = "SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8";
        $featuredResult = $conn->query($featuredQuery);

        if ($featuredResult === false) {
            $featuredError = $conn->error;
        } else {
            while ($row = $featuredResult->fetch_assoc()) {
                $featuredProducts[] = $row;
            }
        }
    }
}

// Add one hat to featured products if not already present
$hatQuery = "SELECT p.* FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.is_active = 1 AND c.name = 'Hat' 
             LIMIT 1";
$hatResult = $conn->query($hatQuery);
if ($hatResult && $hatRow = $hatResult->fetch_assoc()) {
    $isDuplicate = false;
    foreach ($featuredProducts as $fp) {
        if ($fp['id'] == $hatRow['id']) {
            $isDuplicate = true;
            break;
        }
    }
    if (!$isDuplicate) {
        $featuredProducts[] = $hatRow;
    }
}

// Add one sweatshirt to featured products if not already present
$sweatshirtQuery = "SELECT p.* FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.is_active = 1 AND c.name = 'Sweatshirt' 
             LIMIT 1";
$sweatshirtResult = $conn->query($sweatshirtQuery);
if ($sweatshirtResult && $ssRow = $sweatshirtResult->fetch_assoc()) {
    $isDuplicate = false;
    foreach ($featuredProducts as $fp) {
        if ($fp['id'] == $ssRow['id']) {
            $isDuplicate = true;
            break;
        }
    }
    if (!$isDuplicate) {
        $featuredProducts[] = $ssRow;
    }
}

// Get categories
$categories = [];
$categoriesError = null;
$categoriesQuery = "SELECT * FROM categories WHERE is_active = 1";
$categoriesResult = $conn->query($categoriesQuery);
if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row;
    }
} else {
    $categoriesError = $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trendy Wear - Fashion E-Commerce</title>
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
                <img src="images/logo.png" alt="Trendy Wear Logo" class="logo me-3" height="20">
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
                    <li class="nav-item"><a href="index.php" class="nav-link active">Home</a></li>
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
                    <?php if (isset($_SESSION['user_id'])): ?>
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
                    <?php else: ?>
                        <li class="nav-item"><a href="login.php" class="nav-link"><i class="fas fa-user"></i> Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="menu-arrow-container">
                <i class="fas fa-chevron-down menu-arrow"></i>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero bg-light" style="margin-top: 0;">
        <div class="container-fluid py-5 position-relative" style="min-height:680px;">
            <div class="hero-center text-center d-flex flex-column align-items-center justify-content-center" style="min-height:650px;">
                <h3 class="display-3 fw-bold hero-title mb-4">Welcome to Trendy Wear</h3>
                <p class="lead hero-subtitle mb-4">Discover Your Style, Define Your Elegance</p>
                <a href="products.php" class="btn btn-lg cta-button px-5">Shop Now</a>
            </div>

            <div class="hero-side-left" style="position:absolute;left:3%;top:57%;transform:translateY(-50%);">
                <img id="leftHeroImg" src="images/skin color sweater for kids.png" alt="Kids Sweater" style="width:520px;height:700px;object-fit:cover;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.15);">
            </div>
            <div class="hero-side-right" style="position:absolute;right:3%;top:57%;transform:translateY(-50%);">
                <img id="rightHeroImg" src="images/white hoodie.png" alt="White Hoodie" style="width:520px;height:700px;object-fit:cover;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.15);">
            </div>
        </div>
    </section>



    <!-- FEATURED PRODUCTS SECTION WITH SLIDER -->
    <section class="featured-products featured-section py-5">
        <div class="container position-center">
            <h2 class="section-title text-center mb-4">Featured Products</h2>

            <div class="products-carousel-wrapper position-relative mt-3">
                <!-- Carousel Controls -->
                <button type="button" class="carousel-control-btn carousel-prev" onclick="moveCarousel(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" class="carousel-control-btn carousel-next" onclick="moveCarousel(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>

                <!-- Products Carousel -->
                <div class="products-carousel row">
                    <?php foreach ($featuredProducts as $product): ?>
                        <?php $salePrice = isset($product['sale_price']) ? $product['sale_price'] : null; ?>
                        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                            <div class="product-card card h-100 border-0 shadow-sm">
                                <div class="position-relative product-image">
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                                    <?php if (!empty($salePrice)): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">Sale</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title product-name"><?= htmlspecialchars($product['name']) ?></h5>
                                    <div class="price-section mb-3 product-price">
                                        <?php if (!empty($salePrice)): ?>
                                            <span class="text-decoration-line-through text-muted">Rs <?= number_format($product['price'], 0) ?></span>
                                            <span class="text-danger fw-bold ms-2">Rs <?= number_format($salePrice, 0) ?></span>
                                        <?php else: ?>
                                            <span class="fw-bold">Rs <?= number_format($product['price'], 0) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2 product-actions">
                                        <a href="product-details.php?id=<?= $product['id'] ?>" class="btn btn-outline-beige flex-grow-1">View Details</a>
                                        <button class="btn btn-beige" onclick="addToCart(<?= $product['id'] ?>)">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
        // Update cart count on page load
        updateCartCount();
        
        // Function to add item to cart
        async function addToCart(productId) {
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
                        quantity: 1
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
        
        // Function to update cart count
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
    </script>
</body>
</html>
<?php $conn->close(); ?>