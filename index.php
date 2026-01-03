<?php
session_start();
require_once 'admin-api/db_connect.php';

// Get products to show on home page (latest 6 active products)
$featuredProducts = [];
$featuredError = null;
$featuredQuery = "SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6";
$featuredResult = $conn->query($featuredQuery);
if ($featuredResult) {
    while ($row = $featuredResult->fetch_assoc()) {
        $featuredProducts[] = $row;
    }
} else {
    $featuredError = $conn->error;
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
                        <li class="nav-item"><a href="login.html" class="nav-link"><i class="fas fa-user"></i> Login</a></li>
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

    <!-- CATEGORIES SECTION -->
    <section class="categories-section py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">Shop By Category</h2>
            <div class="row g-4 justify-content-center">
                <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                    <div class="col-lg-4 col-md-6">
                        <a href="products.php?category=<?= urlencode($category['name']) ?>" class="category-card text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm hover-lift">
                                <img src="<?= htmlspecialchars($category['image_url'] ?: 'images/default-category.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($category['name']) ?>">
                                <div class="card-body text-center">
                                    <h4 class="card-title mb-2"><?= htmlspecialchars($category['name']) ?></h4>
                                    <p class="text-muted"><?= htmlspecialchars($category['description']) ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- FEATURED PRODUCTS SECTION -->
    <section class="featured-products py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Featured Products</h2>
            <div class="row g-4">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="product-card card h-100 border-0 shadow-sm">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php if ($product['sale_price']): ?>
                                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">Sale</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                                <div class="price-section mb-3">
                                    <?php if ($product['sale_price']): ?>
                                        <span class="text-decoration-line-through text-muted">$<?= number_format($product['price'], 2) ?></span>
                                        <span class="text-danger fw-bold ms-2">$<?= number_format($product['sale_price'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold">$<?= number_format($product['price'], 2) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="product-details.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary flex-grow-1">View Details</a>
                                    <button class="btn btn-primary" onclick="addToCart(<?= $product['id'] ?>)">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="products.php" class="btn btn-primary btn-lg px-5">View All Products</a>
            </div>
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
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50">Home</a></li>
                        <li><a href="products.php" class="text-white-50">Products</a></li>
                        <li><a href="about.html" class="text-white-50">About Us</a></li>
                        <li><a href="contact.html" class="text-white-50">Contact</a></li>
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
    <script src="js/script.js"></script>
    <script>
        // Update cart count on page load
        updateCartCount();
        
        // Function to add item to cart
        async function addToCart(productId) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert('Please login to add items to cart');
                window.location.href = 'login.html';
                return;
            <?php endif; ?>
            
            try {
                const response = await fetch('api/cart.php', {
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
                const response = await fetch('api/cart.php');
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