<?php
require('auth_session.php');
require('db_connect.php');

// Handle reset stats functionality
if (isset($_POST['reset_stats'])) {
    // Reset orders (this will affect sales and order counts)
    mysqli_query($conn, "DELETE FROM order_items");
    mysqli_query($conn, "DELETE FROM orders");
    
    // Optional: Reset other stats if needed
    // mysqli_query($conn, "DELETE FROM feedback"); // if you have feedback table
    
    // Redirect to refresh the page and show updated stats
    header("Location: admin-dashboard.php");
    exit();
}

// Optional: admin id if needed later
$user_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;

// Fetch stats from existing tables
$total_sales = 0;
$total_orders = 0;
$total_products = 0;
$total_customers = 0;
$total_categories = 0;

// Get total sales (use final_amount column from orders table)
$result = mysqli_query($conn, "SELECT SUM(COALESCE(final_amount, total_amount, 0)) as total FROM orders");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $total_sales = $row['total'] ? $row['total'] : 0;
}

// Get total orders
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $total_orders = $row['count'];
}

// Get total products
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $total_products = $row['count'];
}

// Get total customers (users)
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $total_customers = $row['count'];
}

// Get total categories
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM categories");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $total_categories = $row['count'];
} else {
    // If categories table doesn't exist, set to 0
    $total_categories = 0;
}

// Fetch recent orders
$recent_orders = [];
$query = "SELECT o.*, u.full_name as customer_name, u.username 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC LIMIT 5";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Fallback to username if full_name is not available
        if (empty($row['customer_name'])) {
            $row['customer_name'] = $row['username'] ? $row['username'] : 'Guest Customer';
        }
        $recent_orders[] = $row;
    }
}

// Fetch sales for last 7 days
$dates = [];
$sales = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('M d', strtotime($date));
    
    // Sum final_amount per day from orders table
    $query = "SELECT SUM(COALESCE(final_amount, total_amount, 0)) as total FROM orders WHERE DATE(created_at) = '$date'";
    $result = mysqli_query($conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $sales[] = $row['total'] ? $row['total'] : 0;
    } else {
        $sales[] = 0;
    }
}

// Fetch top products
$top_products = [];
$query = "SELECT * FROM products LIMIT 5";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $top_products[] = $row;
    }
}

// Fetch Low Stock Products (uses stock_quantity from products table)
$low_stock_products = [];
$query = "SELECT * FROM products WHERE stock_quantity < 10 LIMIT 5";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $low_stock_products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-Commerce Admin Panel</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="../admin-css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="admin-dashboard.php" class="nav-item active">
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
            <!-- Top Bar -->
            <header class="topbar">
                <button class="sidebar-toggle mobile-only" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Dashboard</h1>
                <div class="topbar-right">
                    <div class="admin-info">
                        <span id="adminName"><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Administrator'; ?></span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="content-area">
                <!-- Welcome Banner -->
                <div class="welcome-banner" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="welcome-content">
                        <h2><i class="fas fa-chart-line"></i> Welcome Back, <span id="welcomeName"><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Administrator'; ?></span>!</h2>
                        <p>Here's what's happening with your store today.</p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <form method="post" onsubmit="return confirm('Are you sure you want to reset your Analytics? \n\nThis will clear all Sales, Orders, and Feedback history.\n\nYour Products, Categories, and Customers will NOT be deleted.');">
                            <button type="submit" name="reset_stats" style="background: #e67e22; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: background 0.3s;">
                                <i class="fas fa-sync-alt"></i> Reset Stats
                            </button>
                        </form>
                        <div class="welcome-icon">
                            <i class="fas fa-store-alt"></i>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card" onclick="window.location.href='admin-analytics.php'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="stat-icon sales">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>PKR <?php echo number_format($total_sales, 0); ?></h3>
                            <p>Total Sales</p>
                            <small style="color: #888; font-size: 0.8em;">View Analytics <i class="fas fa-arrow-right"></i></small>
                        </div>
                    </div>
                    <div class="stat-card" onclick="window.location.href='admin-orders.php'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="stat-icon orders">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_orders; ?></h3>
                            <p>Total Orders</p>
                            <small style="color: #888; font-size: 0.8em;">Manage Orders <i class="fas fa-arrow-right"></i></small>
                        </div>
                    </div>
                    <div class="stat-card" onclick="window.location.href='admin-products.php'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="stat-icon products">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_products; ?></h3>
                            <p>Total Products</p>
                            <small style="color: #888; font-size: 0.8em;">Manage Inventory <i class="fas fa-arrow-right"></i></small>
                        </div>
                    </div>
                    <div class="stat-card" onclick="window.location.href='admin-customers.php'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="stat-icon customers">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_customers; ?></h3>
                            <p>Total Customers</p>
                            <small style="color: #888; font-size: 0.8em;">View Customers <i class="fas fa-arrow-right"></i></small>
                        </div>
                    </div>
                    <div class="stat-card" onclick="window.location.href='admin-categories.php'" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="stat-icon categories">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_categories; ?></h3>
                            <p>Total Categories</p>
                            <small style="color: #888; font-size: 0.8em;">Manage Categories <i class="fas fa-arrow-right"></i></small>
                        </div>
                    </div>
                </div>
                <?php if(!empty($low_stock_products)) { ?>
                <div class="dashboard-card" style="margin-bottom: 20px; border-left: 5px solid #f39c12;">
                    <h3 style="margin-top: 0; color: #f39c12;"><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h3>
                    <div class="table-responsive">
                        <table class="data-table" style="margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock Left</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($low_stock_products as $prod) { ?>
                                    <tr>
                                        <td><?php echo $prod['name']; ?></td>
                                        <td><span class="status-badge warning"><?php echo $prod['stock']; ?></span></td>
                                        <td><a href="admin-products.php" class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none;">Restock</a></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php } ?>
                <!-- Charts and Recent Activity -->
                <div class="dashboard-grid">
                    <!-- Sales Chart -->
                    <div class="dashboard-card">
                        <h2>Sales Overview</h2>
                        <canvas id="salesChart"></canvas>
                    </div>

                    <!-- Top Products -->
                    <div class="dashboard-card">
                        <h2>Top Products</h2>
                        <div id="topProducts">
                            <?php foreach ($top_products as $product) { ?>
                            <div class="product-list-item">
                                <div class="product-list-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="product-list-info">
                                    <h4><?php echo $product['name']; ?></h4>
                                    <p>Rs <?php echo number_format($product['price'], 0); ?></p>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if (empty($top_products)) { ?>
                                <p class="text-center text-muted">No products found</p>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="dashboard-card" style="grid-column: 1 / -1;">
                        <h2>Recent Orders</h2>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recentOrders">
                                    <?php foreach ($recent_orders as $order) { ?>
                                    <tr>
                                        <td>#<?php echo isset($order['id']) ? $order['id'] : 'N/A'; ?></td>
                                        <td><?php echo isset($order['customer_name']) ? htmlspecialchars($order['customer_name']) : 'Guest Customer'; ?></td>
                                        <td>PKR <?php 
                                            $amount = isset($order['final_amount']) ? $order['final_amount'] : (isset($order['total_amount']) ? $order['total_amount'] : 0);
                                            echo number_format($amount, 0); 
                                        ?></td>
                                        <td><span class="status-badge <?php echo isset($order['status']) ? strtolower($order['status']) : 'pending'; ?>"><?php echo isset($order['status']) ? $order['status'] : 'Pending'; ?></span></td>
                                    </tr>
                                    <?php } ?>
                                    <?php if (empty($recent_orders)) { ?>
                                        <tr><td colspan="4" class="text-center">No orders yet</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../admin-js/admin-ui.js"></script>
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Sales',
                    data: <?php echo json_encode($sales); ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 4]
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
