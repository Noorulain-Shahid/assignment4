<?php
require('auth_session.php');
require('db_connect.php');

$message = "";

// Handle Delete (soft delete using is_active flag)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "UPDATE products SET is_active = 0 WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $message = "Product deactivated successfully!";
    } else {
        $message = "Error deactivating product: " . mysqli_error($conn);
    }
}

// Handle Add/Edit using current products schema
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);

    $image_url = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../images/uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        $filename = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Save relative path from project root
            $image_url = "images/uploads/" . $filename;
        }
    }

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update existing product
        $id = intval($_POST['id']);
        $update_query = "UPDATE products SET name='$name', description='$description', price='$price', stock_quantity='$stock', category_id='$category_id'";
        if ($image_url) {
            $update_query .= ", image_url='$image_url'";
        }
        $update_query .= " WHERE id=$id";
        
        if (mysqli_query($conn, $update_query)) {
            $message = "Product updated successfully!";
        } else {
            $message = "Error updating product: " . mysqli_error($conn);
        }
    } else {
        // Insert new product
        $image_value = $image_url ? "'$image_url'" : "NULL";
        $query = "INSERT INTO products (name, description, price, stock_quantity, category_id, image_url, is_active) VALUES ('$name', '$description', '$price', '$stock', '$category_id', $image_value, 1)";
        if (mysqli_query($conn, $query)) {
            $message = "Product added successfully!";
        } else {
            $message = "Error adding product: " . mysqli_error($conn);
        }
    }
}

// Fetch Categories for dropdown (global categories)
$categories = [];
$cat_query = "SELECT * FROM categories WHERE is_active = 1";
$cat_result = mysqli_query($conn, $cat_query);
if ($cat_result) {
    while ($row = mysqli_fetch_assoc($cat_result)) {
        $categories[] = $row;
    }
}

// Fetch Products (only active products so deleted ones disappear from the list)
$products = [];
$products_error = null;
// Use id for ordering so it works even if created_at column is missing in older schemas
// Only include products where is_active = 1 so soft-deleted items are hidden
$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY p.id DESC";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
} else {
    $products_error = mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - E-Commerce Admin Panel</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="../admin-css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .product-image-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
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
                <a href="admin-products.php" class="nav-item active">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="admin-categories.php" class="nav-item">
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
                <h1>Product Management</h1>
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
                <?php if($products_error) { ?>
                    <div class="alert alert-danger">Database error loading products: <?php echo htmlspecialchars($products_error); ?></div>
                <?php } ?>

                <!-- Action Bar -->
                <div class="action-bar" style="display: flex; justify-content: space-between; align-items: center;">
                    <button class="btn-primary" onclick="openProductModal()" style="width: auto;">
                        <i class="fas fa-plus"></i>
                        Add New Product
                    </button>
                    <div class="search-wrapper">
                        <input type="text" id="productSearch" onkeyup="filterProducts()" placeholder="Search products..." style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 300px;">
                    </div>
                </div>

                <!-- Products Table -->
                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productsTable">
                                <?php foreach ($products as $product) { ?>
                                <tr>
                                    <td>
                                        <?php if(!empty($product['image_url'])) { ?>
                                            <img src="../<?php echo $product['image_url']; ?>" alt="Product" class="product-image-thumb">
                                        <?php } else { ?>
                                            <i class="fas fa-box fa-2x text-muted"></i>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $product['category_name']; ?></td>
                                    <td>Rs <?php echo number_format($product['price'], 0); ?></td>
                                    <td>
                                        <?php 
                                            $stockClass = 'success';
                                            if($product['stock_quantity'] == 0) $stockClass = 'danger';
                                            else if($product['stock_quantity'] < 10) $stockClass = 'warning';
                                        ?>
                                        <span class="status-badge <?php echo $stockClass; ?>"><?php echo $product['stock_quantity']; ?></span>
                                    </td>
                                    <td>
                                        <button class="action-btn edit" onclick='editProduct(<?php echo json_encode($product); ?>)' title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete" onclick="confirmDeleteProduct(<?php echo $product['id']; ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php if (empty($products)) { ?>
                                    <tr><td colspan="6" class="text-center">No products yet</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Product</h2>
                <button class="close-btn" onclick="closeProductModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="productForm" method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" id="productId" name="id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productName">Product Name *</label>
                            <input type="text" id="productName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="productCategory">Category *</label>
                            <select id="productCategory" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat) { ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="productDescription">Description</label>
                        <textarea id="productDescription" name="description" rows="4"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productPrice">Price (Rs) *</label>
                            <input type="number" id="productPrice" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="productStock">Stock Quantity *</label>
                            <input type="number" id="productStock" name="stock" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="productImage">Product Image</label>
                        <input type="file" id="productImage" name="image" accept="image/*">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeProductModal()">Cancel</button>
                        <button type="submit" name="submit" class="btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../admin-js/admin-ui.js"></script>
    <script>
        function openProductModal() {
            const modal = document.getElementById('productModal');
            modal.classList.add('show');
            modal.style.display = 'flex'; // Ensure flex is set for centering
            
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
        }

        function closeProductModal() {
            const modal = document.getElementById('productModal');
            modal.classList.remove('show');
            modal.style.display = 'none';
        }

        function editProduct(product) {
            const modal = document.getElementById('productModal');
            modal.classList.add('show');
            modal.style.display = 'flex'; // Ensure flex is set for centering
            
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productCategory').value = product.category_id;
            document.getElementById('productDescription').value = product.description;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock_quantity;
        }

        function confirmDeleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                window.location.href = 'admin-products.php?action=delete&id=' + id;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeProductModal();
            }
        }

        function filterProducts() {
            var input = document.getElementById("productSearch");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("productsTable");
            var tr = table.getElementsByTagName("tr");

            for (var i = 0; i < tr.length; i++) {
                var tdName = tr[i].getElementsByTagName("td")[1]; // Product Name column
                var tdCategory = tr[i].getElementsByTagName("td")[2]; // Category column
                
                if (tdName || tdCategory) {
                    var txtValueName = tdName.textContent || tdName.innerText;
                    var txtValueCategory = tdCategory.textContent || tdCategory.innerText;
                    
                    if (txtValueName.toUpperCase().indexOf(filter) > -1 || txtValueCategory.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>
