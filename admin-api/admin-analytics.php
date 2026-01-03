<?php
require('auth_session.php');
require('db_connect.php');

// Monthly Sales (Last 6 months) - global, based on orders.created_at and total_amount
$months = [];
$monthly_sales = [];
for ($i = 5; $i >= 0; $i--) {
    $month_start = date('Y-m-01', strtotime("-$i months"));
    $month_end = date('Y-m-t', strtotime("-$i months"));
    $months[] = date('M Y', strtotime($month_start));
    
    $query = "SELECT SUM(total_amount) as total FROM orders WHERE created_at BETWEEN '$month_start' AND '$month_end'";
    $result = mysqli_query($conn, $query);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    $monthly_sales[] = ($row && $row['total']) ? $row['total'] : 0;
}

// Orders by Status - global
$status_labels = [];
$status_counts = [];
$query = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$result = mysqli_query($conn, $query);
while ($result && $row = mysqli_fetch_assoc($result)) {
    $status_labels[] = $row['status'];
    $status_counts[] = $row['count'];
}

// Products by Category - global
$category_labels = [];
$category_counts = [];
$query = "SELECT c.name, COUNT(p.id) as count FROM categories c LEFT JOIN products p ON c.id = p.category_id WHERE c.is_active = 1 GROUP BY c.id";
$result = mysqli_query($conn, $query);
while ($result && $row = mysqli_fetch_assoc($result)) {
    $category_labels[] = $row['name'];
    $category_counts[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - E-Commerce Admin Panel</title>
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
                <a href="admin-categories.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="admin-customers.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <a href="admin-analytics.php" class="nav-item active">
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
                <h1>Analytics & Reports</h1>
                <div class="topbar-right">
                    <div class="admin-info">
                        <span id="adminName"><?php echo $_SESSION['name']; ?></span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <!-- Monthly Sales Chart -->
                <div class="dashboard-card" style="margin-bottom: 25px;">
                    <h2>Monthly Sales Overview</h2>
                    <canvas id="monthlySalesChart" height="100"></canvas>
                </div>

                <div class="dashboard-grid">
                    <!-- Order Status Chart -->
                    <div class="dashboard-card">
                        <h2>Orders by Status</h2>
                        <canvas id="orderStatusChart"></canvas>
                    </div>

                    <!-- Category Distribution Chart -->
                    <div class="dashboard-card">
                        <h2>Products by Category</h2>
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../admin-js/admin-ui.js"></script>
    <script>
        // Monthly Sales Chart
        new Chart(document.getElementById('monthlySalesChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Monthly Sales',
                    data: <?php echo json_encode($monthly_sales); ?>,
                    backgroundColor: '#3498db',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Order Status Chart
        new Chart(document.getElementById('orderStatusChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($status_counts); ?>,
                    backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c', '#95a5a6']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Category Chart
        new Chart(document.getElementById('categoryChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($category_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($category_counts); ?>,
                    backgroundColor: ['#9b59b6', '#3498db', '#e67e22', '#1abc9c', '#34495e']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>
