<?php
require('auth_session.php');
require('db_connect.php');

$message = "";
$db_error = "";

// Handle Update Status
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $query = "UPDATE orders SET status='$status' WHERE id=$order_id";
    if (mysqli_query($conn, $query)) {
        $message = "Order status updated successfully!";
    } else {
        $message = "Error updating status: " . mysqli_error($conn);
    }
}

// Handle Update Payment Status
if (isset($_POST['update_payment'])) {
    $order_id = intval($_POST['order_id']);
    $payment_status = mysqli_real_escape_string($conn, $_POST['payment_status']);
    $query = "UPDATE orders SET payment_status='$payment_status' WHERE id=$order_id";
    if (mysqli_query($conn, $query)) {
        $message = "Payment status updated successfully!";
    } else {
        $message = "Error updating payment status: " . mysqli_error($conn);
    }
}

// Fetch Stats
$pending_count = 0;
$processing_count = 0;
$shipped_count = 0;
$delivered_count = 0;

$result = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM orders GROUP BY status");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $s = strtolower($row['status']);
        if ($s == 'pending') $pending_count = $row['count'];
        if ($s == 'processing') $processing_count = $row['count'];
        if ($s == 'shipped') $shipped_count = $row['count'];
        if ($s == 'delivered') $delivered_count = $row['count'];
    }
} else {
    $db_error = mysqli_error($conn);
}

// Fetch Orders
$orders = [];
$query = "SELECT o.*, u.first_name, u.last_name, u.email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
} else {
    $db_error = $db_error ?: mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - E-Commerce Admin Panel</title>
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
        .order-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .order-stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .order-stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .order-stat-icon.pending { background: #fff3cd; color: #856404; }
        .order-stat-icon.processing { background: #cce5ff; color: #004085; }
        .order-stat-icon.shipped { background: #d1ecf1; color: #0c5460; }
        .order-stat-icon.delivered { background: #d4edda; color: #155724; }
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
                <a href="admin-orders.php" class="nav-item active">
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
            <header class="topbar">
                <button class="sidebar-toggle mobile-only" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Order Management</h1>
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
                <?php if($db_error != "") { ?>
                    <div class="alert alert-danger">Database error: <?php echo htmlspecialchars($db_error); ?></div>
                <?php } ?>

                <!-- Order Stats Overview -->
                <div class="order-stats-grid">
                    <div class="order-stat-card">
                        <div class="order-stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="order-stat-info">
                            <h4><?php echo $pending_count; ?></h4>
                            <p>Pending</p>
                        </div>
                    </div>
                    <div class="order-stat-card">
                        <div class="order-stat-icon processing">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="order-stat-info">
                            <h4><?php echo $processing_count; ?></h4>
                            <p>Processing</p>
                        </div>
                    </div>
                    <div class="order-stat-card">
                        <div class="order-stat-icon shipped">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="order-stat-info">
                            <h4><?php echo $shipped_count; ?></h4>
                            <p>Shipped</p>
                        </div>
                    </div>
                    <div class="order-stat-card">
                        <div class="order-stat-icon delivered">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="order-stat-info">
                            <h4><?php echo $delivered_count; ?></h4>
                            <p>Delivered</p>
                        </div>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="action-bar" style="margin-bottom: 20px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <button class="btn-primary" onclick="openAddOrderModal()">
                        <i class="fas fa-plus"></i> Add New Order
                    </button>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="orderSearch" placeholder="Search orders..." onkeyup="filterOrders()" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <select id="statusFilter" onchange="filterOrders()" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTable">
                                <?php foreach ($orders as $order) { ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo $order['customer_name']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td>Rs <?php echo number_format($order['total_amount'], 0); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span>
                                    </td>
                                    <td>
                                        <button class="action-btn edit" onclick='openStatusModal(<?php echo $order['id']; ?>, "<?php echo $order['status']; ?>")' title="Update Status">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php if (empty($orders)) { ?>
                                    <tr><td colspan="6" class="text-center">No orders yet</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Order Status</h2>
                <button class="close-btn" onclick="closeStatusModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="post" action="">
                    <input type="hidden" id="statusOrderId" name="order_id">
                    <div class="form-group">
                        <label>Status</label>
                        <select id="statusSelect" name="status" required>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeStatusModal()">Cancel</button>
                        <button type="submit" name="update_status" class="btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Order Modal -->
    <div id="addOrderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Add New Order</h2>
                <button class="close-btn" onclick="closeAddOrderModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addOrderForm" method="post" action="">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Customer Name</label>
                        <select id="newOrderCustomer" name="customer_id" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers as $cust) { ?>
                                <option value="<?php echo $cust['id']; ?>"><?php echo $cust['name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-box"></i> Select Product</label>
                        <select id="newOrderProduct" name="product_id" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $prod) { ?>
                                <option value="<?php echo $prod['id']; ?>"><?php echo $prod['name']; ?> (Rs <?php echo $prod['price']; ?>)</option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-sort-numeric-up"></i> Quantity</label>
                            <input type="number" id="newOrderQuantity" name="quantity" min="1" value="1" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-tasks"></i> Order Status</label>
                        <select id="newOrderStatus" name="status" required>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeAddOrderModal()">Cancel</button>
                        <button type="submit" name="add_order" class="btn-primary">
                            <i class="fas fa-check"></i> Create Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../admin-js/admin-ui.js"></script>
    <script>
        function openStatusModal(id, status) {
            document.getElementById('statusModal').style.display = 'block';
            document.getElementById('statusOrderId').value = id;
            document.getElementById('statusSelect').value = status;
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        function openAddOrderModal() {
            document.getElementById('addOrderModal').style.display = 'block';
        }

        function closeAddOrderModal() {
            document.getElementById('addOrderModal').style.display = 'none';
        }

        function filterOrders() {
            var input = document.getElementById("orderSearch");
            var filter = input.value.toUpperCase();
            var statusFilter = document.getElementById("statusFilter").value.toUpperCase();
            var table = document.getElementById("ordersTable");
            var tr = table.getElementsByTagName("tr");

            for (var i = 0; i < tr.length; i++) {
                var tdId = tr[i].getElementsByTagName("td")[0]; // ID
                var tdName = tr[i].getElementsByTagName("td")[1]; // Name
                var tdStatus = tr[i].getElementsByTagName("td")[4]; // Status
                
                if (tdId && tdName) {
                    var txtValueId = tdId.textContent || tdId.innerText;
                    var txtValueName = tdName.textContent || tdName.innerText;
                    var txtValueStatus = tdStatus.textContent || tdStatus.innerText;
                    
                    // Check if it's a "No orders" row
                    if (tdId.getAttribute('colspan')) continue;

                    var matchesSearch = txtValueId.toUpperCase().indexOf(filter) > -1 || txtValueName.toUpperCase().indexOf(filter) > -1;
                    var matchesStatus = statusFilter === "" || txtValueStatus.toUpperCase().indexOf(statusFilter) > -1;

                    if (matchesSearch && matchesStatus) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const statusModal = document.getElementById('statusModal');
            const addOrderModal = document.getElementById('addOrderModal');
            if (event.target == statusModal) {
                closeStatusModal();
            }
            if (event.target == addOrderModal) {
                closeAddOrderModal();
            }
        }
    </script>
</body>
</html>
