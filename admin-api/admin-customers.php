<?php
require('auth_session.php');
require('db_connect.php');

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total count from frontend users
$total_customers = 0;
$total_pages = 0;
$total_query = "SELECT COUNT(*) as total FROM users WHERE is_active = 1";
$total_result = mysqli_query($conn, $total_query);
if ($total_result) {
    $total_row = mysqli_fetch_assoc($total_result);
    $total_customers = $total_row ? (int)$total_row['total'] : 0;
    $total_pages = $total_customers > 0 ? ceil($total_customers / $records_per_page) : 1;
}

// Fetch Users (Frontend Customers) with order count
$customers = [];
$query = "SELECT u.*, 
          CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as display_name,
          COUNT(DISTINCT o.id) as order_count,
          COALESCE(SUM(COALESCE(o.final_amount, o.total_amount, 0)), 0) as total_spent
          FROM users u 
          LEFT JOIN orders o ON u.id = o.user_id
          WHERE u.is_active = 1 OR u.is_active IS NULL
          GROUP BY u.id
          ORDER BY u.created_at DESC
          LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $customers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - E-Commerce Admin Panel</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="../admin-css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="admin-customers.php" class="nav-item active">
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
                <h1>Customer Management</h1>
                <div class="topbar-right">
                    <div class="admin-info">
                        <span id="adminName"><?php echo $_SESSION['name']; ?></span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <div class="action-bar" style="margin-bottom: 20px;">
                     <input type="text" id="customerSearch" placeholder="Search customers..." onkeyup="filterCustomers()" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 300px;">
                </div>

                <!-- Customers Table -->
                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Location</th>
                                    <th>Total Orders</th>
                                    <th>Total Spent</th>
                                    <th>Registration Date</th>
                                </tr>
                            </thead>
                            <tbody id="customersTable">
                                <?php foreach ($customers as $customer) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['display_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(($customer['city'] ? $customer['city'] . ', ' : '') . ($customer['state'] ?: 'N/A')); ?></td>
                                    <td><span class="badge badge-info"><?php echo $customer['order_count']; ?></span></td>
                                    <td><span class="badge badge-success">PKR <?php echo number_format($customer['total_spent'], 0); ?></span></td>
                                    <td><?php echo date('M j, Y', strtotime($customer['created_at'])); ?></td>
                                </tr>
                                <?php } ?>
                                <?php if (empty($customers)) { ?>
                                    <tr><td colspan="7" class="text-center">No customers yet</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1) { ?>
                <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; align-items: center; gap: 10px;">
                    <?php if ($page > 1) { ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="btn-secondary" style="padding: 10px 18px; text-decoration: none; border-radius: 5px; display: inline-flex; align-items: center; gap: 8px; background: #2c3e50; color: white; font-size: 14px;">
                            <i class="fas fa-chevron-left" style="font-size: 14px;"></i> <span>Previous</span>
                        </a>
                    <?php } else { ?>
                        <span style="padding: 10px 18px; border-radius: 5px; display: inline-flex; align-items: center; gap: 8px; background: #ddd; color: #999; font-size: 14px;">
                            <i class="fas fa-chevron-left" style="font-size: 14px;"></i> <span>Previous</span>
                        </span>
                    <?php } ?>
                    
                    <div style="display: flex; gap: 5px;">
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++) { 
                            if ($i == $page) { ?>
                                <span style="padding: 10px 15px; background: #d4af37; color: white; border-radius: 5px; font-weight: bold; font-size: 14px;"><?php echo $i; ?></span>
                            <?php } else { ?>
                                <a href="?page=<?php echo $i; ?>" style="padding: 10px 15px; background: #f0f0f0; color: #333; text-decoration: none; border-radius: 5px; font-size: 14px; transition: all 0.3s;"><?php echo $i; ?></a>
                            <?php }
                        } ?>
                    </div>

                    <?php if ($page < $total_pages) { ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="btn-secondary" style="padding: 10px 18px; text-decoration: none; border-radius: 5px; display: inline-flex; align-items: center; gap: 8px; background: #2c3e50; color: white; font-size: 14px;">
                            <span>Next</span> <i class="fas fa-chevron-right" style="font-size: 14px;"></i>
                        </a>
                    <?php } else { ?>
                        <span style="padding: 10px 18px; border-radius: 5px; display: inline-flex; align-items: center; gap: 8px; background: #ddd; color: #999; font-size: 14px;">
                            <span>Next</span> <i class="fas fa-chevron-right" style="font-size: 14px;"></i>
                        </span>
                    <?php } ?>
                    
                    <span style="margin-left: 15px; color: #666;">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $total_customers; ?> customers)
                    </span>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="../admin-js/admin-ui.js"></script>
    <script>
    function filterCustomers() {
        var input = document.getElementById("customerSearch");
        var filter = input.value.toUpperCase();
        var table = document.querySelector(".data-table");
        var tr = table.getElementsByTagName("tr");

        for (var i = 1; i < tr.length; i++) { // Start from 1 to skip header
            var tdName = tr[i].getElementsByTagName("td")[0];
            var tdEmail = tr[i].getElementsByTagName("td")[1];
            if (tdName && tdEmail) {
                var txtValueName = tdName.textContent || tdName.innerText;
                var txtValueEmail = tdEmail.textContent || tdEmail.innerText;
                if (txtValueName.toUpperCase().indexOf(filter) > -1 || txtValueEmail.toUpperCase().indexOf(filter) > -1) {
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
