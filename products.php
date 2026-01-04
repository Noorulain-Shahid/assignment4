<?php
session_start();
require_once 'admin-api/db_connect.php';

// Get filter parameters
$category = $_GET['category'] ?? '';
$gender = $_GET['gender'] ?? '';
$search = $_GET['search'] ?? '';
$page = intval($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query
$whereClause = "WHERE p.is_active = 1";
$params = [];
$types = "";

if (!empty($category)) {
    $whereClause .= " AND c.name LIKE ?";
    $params[] = "%$category%";
    $types .= "s";
}

if (!empty($gender)) {
    $whereClause .= " AND p.gender = ?";
    $params[] = $gender;
    $types .= "s";
}

if (!empty($search)) {
    $whereClause .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Get products
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          $whereClause 
          ORDER BY p.created_at DESC 
          LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $row['additional_images'] = json_decode($row['additional_images'], true) ?: [];
    $row['sizes'] = json_decode($row['sizes'], true) ?: [];
    $row['colors'] = json_decode($row['colors'], true) ?: [];
    $products[] = $row;
}

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total 
               FROM products p 
               LEFT JOIN categories c ON p.category_id = c.id 
               $whereClause";

$countStmt = $conn->prepare($countQuery);
if (!empty($types) && count($params) > 2) {
    $countTypes = substr($types, 0, -2);
    $countParams = array_slice($params, 0, -2);
    if (!empty($countTypes)) {
        $countStmt->bind_param($countTypes, ...$countParams);
    }
}
$countStmt->execute();
$totalResult = $countStmt->get_result();
$total = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

// Get categories for filters
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
    <title>Products - Trendy Wear</title>
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
                                <li><a href="products.php?category=<?= urlencode($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
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

    <!-- Category Hero (same style as Get In Touch) -->
    <section class="contact-hero">
        <div class="container">
            <h1 class="page-title">Shop By Category</h1>
            <p class="page-subtitle">Find the perfect style for everyone</p>
        </div>
    </section>

    <div class="container pt-5">
        <!-- Search and Filter Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search products..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn" style="background-color: var(--accent-beige); border-color: var(--accent-beige); color: var(--text-dark);">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-4">
                <form method="GET">
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name']) ?>" 
                                    <?= ($category === $cat['name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Results Info -->
        <div class="row mb-4">
            <div class="col-12">
                <h2>Products <?= $category ? "in " . htmlspecialchars($category) : "" ?> 
                    <?= $search ? "matching '" . htmlspecialchars($search) . "'" : "" ?></h2>
                <p class="text-muted">Showing <?= count($products) ?> of <?= $total ?> products</p>
            </div>
        </div>

        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="row">
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h3>No products found</h3>
                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                    <a href="products.php" class="btn btn-primary">View All Products</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                    <?php $salePrice = isset($product['sale_price']) ? $product['sale_price'] : null; ?>
                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12">
                        <div class="product-card card h-100 border-0 shadow-sm" data-product-id="<?= $product['id'] ?>">
                            <div class="position-relative product-image">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" class="card-img-top">
                                <?php if (!empty($salePrice)): ?>
                                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">Sale</span>
                                <?php endif; ?>
                                <?php if ($product['stock_quantity'] <= 0): ?>
                                    <span class="badge bg-secondary position-absolute top-0 end-0 m-2">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column product-info">
                                <h5 class="card-title product-name mb-2"><?= htmlspecialchars($product['name']) ?></h5>
                                <div class="price-section mb-3 product-price">
                                    <?php if (!empty($salePrice)): ?>
                                        <span class="text-decoration-line-through text-muted">Rs <?= number_format($product['price'], 0) ?></span>
                                        <span class="text-danger fw-bold ms-2">Rs <?= number_format($salePrice, 0) ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold">Rs <?= number_format($product['price'], 0) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex gap-2 product-actions">
                                    <a href="product-details.php?id=<?= $product['id'] ?>" class="btn btn-outline-beige flex-grow-1">
                                        View Details
                                    </a>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <button class="btn btn-beige" onclick="addToCart(<?= $product['id'] ?>)">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Products pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

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