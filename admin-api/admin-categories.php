<?php
require('auth_session.php');
require('db_connect.php');

$message = "";

// Handle Delete (global categories)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "DELETE FROM categories WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        $message = "Category deleted successfully!";
    } else {
        $message = "Error deleting category: " . mysqli_error($conn);
    }
}

// Handle Add/Edit using current categories schema
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    // Generate a URL-friendly unique slug to satisfy the unique 'slug' key
    $rawSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    $rawSlug = trim($rawSlug, '-');
    if ($rawSlug === '') {
        $rawSlug = 'category-' . time();
    }
    $slug = mysqli_real_escape_string($conn, $rawSlug);

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $id = intval($_POST['id']);
        $query = "UPDATE categories SET name='$name', slug='$slug', description='$description' WHERE id=$id";
        if (mysqli_query($conn, $query)) {
            $message = "Category updated successfully!";
        } else {
            $message = "Error updating category: " . mysqli_error($conn);
        }
    } else {
        // Insert
        $query = "INSERT INTO categories (name, slug, description, is_active) VALUES ('$name', '$slug', '$description', 1)";
        if (mysqli_query($conn, $query)) {
            $message = "Category added successfully!";
        } else {
            $message = "Error adding category: " . mysqli_error($conn);
        }
    }
}

// Fetch Categories (global)
$categories = [];
$categories_error = null;
$query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$result = mysqli_query($conn, $query);

if (!$result) {
    // Fallback for schemas without is_active column
    if (strpos(mysqli_error($conn), "Unknown column 'is_active'") !== false) {
        $query = "SELECT * FROM categories";
        $result = mysqli_query($conn, $query);
    }
}

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Get product count for each category
        $cat_id = $row['id'];
        $count_query = "SELECT COUNT(*) as count FROM products WHERE category_id=$cat_id";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = $count_result ? mysqli_fetch_assoc($count_result) : null;
        $row['productCount'] = $count_row ? $count_row['count'] : 0;
        $categories[] = $row;
    }
} else {
    $categories_error = mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - E-Commerce Admin Panel</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="../admin-css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .category-card {
            background: white;
            padding: 30px 30px 30px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 0;
            border: 1px solid #eee;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .category-icon-wrapper {
            width: 80px;
            height: 80px;
            background: #2c3e50;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .category-card i {
            font-size: 2.5rem;
            color: white;
        }
        .category-card img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        .category-info {
            width: 100%;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .category-info h3 {
            margin: 0 0 12px 0;
            font-size: 1.3rem;
            color: #2c3e50;
            font-weight: 600;
        }
        .category-info p {
            margin: 0 0 12px 0;
            color: #666;
            font-size: 0.95rem;
        }
        .category-product-count {
            font-size: 0.9rem;
            color: #888;
            margin-bottom: 20px !important;
        }
        .category-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 15px;
            padding-top: 0;
        }
        .action-btn {
            border: none;
            background: #2c3e50;
            cursor: pointer;
            font-size: 1.3rem;
            padding: 0;
            border-radius: 12px;
            transition: all 0.3s;
            color: white;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .action-btn.edit { 
            background: #2c3e50;
            color: white;
        }
        .action-btn.edit:hover { 
            background: #1a252f;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .action-btn.delete { 
            background: #2c3e50;
            color: white;
        }
        .action-btn.delete:hover { 
            background: #1a252f;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
            gap: 30px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-store"></i>
                <h2>Admin Panel</h2>
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <a href="admin-dashboard.php" class="nav-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="admin-orders.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
                <a href="admin-products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="admin-categories.php" class="nav-item active">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="admin-customers.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <a href="admin-analytics.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
                <a href="admin-feedback.php" class="nav-item">
                    <i class="fas fa-comments"></i>
                    <span>Feedback & Reviews</span>
                </a>
                <a href="admin-profile.php" class="nav-item">
                    <i class="fas fa-user-cog"></i>
                    <span>Profile</span>
                </a>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <header class="topbar">
                <button class="sidebar-toggle mobile-only" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Category Management</h1>
                <div class="topbar-right">
                    <div class="admin-info">
                        <span id="adminName"><?php echo $_SESSION['name']; ?></span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <?php if($message != "") { ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php } ?>
                <?php if($categories_error) { ?>
                    <div class="alert alert-danger">Database error loading categories: <?php echo htmlspecialchars($categories_error); ?></div>
                <?php } ?>

                <!-- Action Bar -->
                <div class="action-bar">
                    <button class="btn-primary" onclick="openCategoryModal()">
                        <i class="fas fa-plus"></i>
                        Add New Category
                    </button>
                </div>

                <!-- Categories Grid -->
                <div class="categories-grid" id="categoriesGrid">
                    <?php foreach ($categories as $category) { ?>
                    <div class="category-card">
                        <div class="category-icon-wrapper">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="category-info">
                            <h3><?php echo $category['name']; ?></h3>
                            <p><?php echo $category['description']; ?></p>
                            <p class="category-product-count"><?php echo $category['productCount']; ?> products</p>
                            <div class="category-actions">
                                <?php 
                                    // Create a lightweight object for JS to avoid huge BLOB data breaking the onclick attribute
                                    $catForJs = [
                                        'id' => $category['id'],
                                        'name' => $category['name'],
                                        'description' => $category['description']
                                    ];
                                ?>
                                <button class="action-btn edit" onclick='editCategory(<?php echo json_encode($catForJs); ?>)' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" onclick="confirmDeleteCategory(<?php echo $category['id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if (empty($categories)) { ?>
                        <p class="text-center">No categories yet</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Category</h2>
                <button class="close-btn" onclick="closeCategoryModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="categoryForm" method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" id="categoryId" name="id">
                    <div class="form-group">
                        <label for="categoryName">Category Name *</label>
                        <input type="text" id="categoryName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="categoryDescription">Description</label>
                        <textarea id="categoryDescription" name="description" rows="3"></textarea>
                    </div>
                    <!-- Image/Icon fields removed for simplified schema -->
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeCategoryModal()">Cancel</button>
                        <button type="submit" name="submit" class="btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../admin-js/admin-ui.js"></script>
    <script>
        function openCategoryModal() {
            document.getElementById('categoryModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Add New Category';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }

        function editCategory(category) {
            document.getElementById('categoryModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Edit Category';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description;
            document.getElementById('categoryIcon').value = category.icon;
        }

        function confirmDeleteCategory(id) {
            if (confirm('Are you sure you want to delete this category?')) {
                window.location.href = 'admin-categories.php?action=delete&id=' + id;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('categoryModal');
            if (event.target == modal) {
                closeCategoryModal();
            }
        }
    </script>
</body>
</html>
