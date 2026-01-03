<?php
require('auth_session.php');
require('db_connect.php');

$email = $_SESSION['email'];
$message = "";
$error = "";
$db_error = "";

// Fetch current admin user from admin_users (if table/row exists)
$query = "SELECT *, full_name AS name FROM admin_users WHERE email='$email' AND is_active = 1 LIMIT 1";
$result = mysqli_query($conn, $query);
if ($result) {
    $user = mysqli_fetch_assoc($result);
} else {
    // If admin_users table/query fails (e.g., demo login only), fall back to session data
    $db_error = mysqli_error($conn);
    $user = [
        'name' => isset($_SESSION['name']) ? $_SESSION['name'] : 'Administrator',
        'email' => $email,
        'password' => '',
        'role' => isset($_SESSION['role']) ? $_SESSION['role'] : 'admin',
    ];
}

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $new_email = mysqli_real_escape_string($conn, trim($_POST['email']));
    
    // Validation
    if (empty($name)) {
        $error = "Name cannot be empty.";
    } elseif (empty($new_email)) {
        $error = "Email cannot be empty.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email is already taken by another admin
        if ($new_email !== $email) {
            $check_query = "SELECT id FROM admin_users WHERE email='$new_email' AND email!='$email'";
            $check_result = mysqli_query($conn, $check_query);
            if (mysqli_num_rows($check_result) > 0) {
                $error = "This email is already registered.";
            }
        }
        
        // If no errors, proceed with update
        if (empty($error)) {
            $update_query = "UPDATE admin_users SET full_name='$name', email='$new_email' WHERE email='$email'";
            if (mysqli_query($conn, $update_query)) {
                $_SESSION['email'] = $new_email; // Update session email if changed
                $_SESSION['name'] = $name;
                $email = $new_email; // Update local variable
                $message = "Profile updated successfully!";
                // Refresh user data
                $query = "SELECT *, full_name AS name FROM admin_users WHERE email='$email' AND is_active = 1 LIMIT 1";
                $result = mysqli_query($conn, $query);
                $user = mysqli_fetch_assoc($result);
            } else {
                $error = "Error updating profile: " . mysqli_error($conn);
            }
        }
    }
}

// Handle Password Change
if (isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validation
    if (empty($current_password)) {
        $error = "Please enter your current password.";
    } elseif (empty($new_password)) {
        $error = "Please enter a new password.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif ($current_password === $new_password) {
        $error = "New password must be different from current password.";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "Incorrect current password.";
    } else {
        // All validations passed, update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE admin_users SET password='$hashed_password' WHERE email='$email'";
        if (mysqli_query($conn, $update_query)) {
            $message = "Password changed successfully!";
            // Refresh user data
            $query = "SELECT *, full_name AS name FROM admin_users WHERE email='$email' AND is_active = 1 LIMIT 1";
            $result = mysqli_query($conn, $query);
            if ($result) {
                $user = mysqli_fetch_assoc($result);
            }
        } else {
            $error = "Error changing password: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Profile - E-Commerce Admin Panel</title>
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
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
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
                <a href="admin-profile.php" class="nav-item active">
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
                <h1>Profile Management</h1>
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
                <?php if($error != "") { ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php } ?>
                <?php if($db_error != "") { ?>
                    <div class="alert alert-danger">Database error: <?php echo htmlspecialchars($db_error); ?></div>
                <?php } ?>

                <!-- Profile Header Card -->
                <div class="profile-header-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-header-info">
                        <h2 id="profileHeaderName"><?php echo $user['name']; ?></h2>
                        <p id="profileHeaderEmail"><?php echo $user['email']; ?></p>
                        <span class="role-badge"><?php echo $user['role']; ?></span>
                    </div>
                </div>

                <div class="profile-grid">
                    <!-- Profile Information -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2><i class="fas fa-user-edit"></i> Profile Information</h2>
                        </div>
                        <form id="profileForm" method="post" action="">
                            <div class="form-group">
                                <label for="adminFullName"><i class="fas fa-user"></i> Full Name</label>
                                <input type="text" id="adminFullName" name="name" value="<?php echo $user['name']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="adminEmail"><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" id="adminEmail" name="email" value="<?php echo $user['email']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="adminRole"><i class="fas fa-shield-alt"></i> Role</label>
                                <input type="text" id="adminRole" value="<?php echo $user['role']; ?>" readonly>
                            </div>
                            <button type="submit" name="update_profile" class="btn-primary">
                                <i class="fas fa-save"></i>
                                Update Profile
                            </button>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h2><i class="fas fa-lock"></i> Change Password</h2>
                        </div>
                        <form id="passwordForm" method="post" action="">
                            <div class="form-group">
                                <label for="currentPassword"><i class="fas fa-key"></i> Current Password</label>
                                <div class="password-input">
                                    <input type="password" id="currentPassword" name="current_password" required>
                                    <button type="button" class="toggle-password" onclick="togglePasswordField('currentPassword')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="newPassword"><i class="fas fa-lock"></i> New Password</label>
                                <div class="password-input">
                                    <input type="password" id="newPassword" name="new_password" required minlength="6" pattern=".{6,}" title="Password must be at least 6 characters">
                                    <button type="button" class="toggle-password" onclick="togglePasswordField('newPassword')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small><i class="fas fa-info-circle"></i> Minimum 6 characters, must be different from current password</small>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword"><i class="fas fa-lock"></i> Confirm New Password</label>
                                <div class="password-input">
                                    <input type="password" id="confirmPassword" name="confirm_password" required>
                                    <button type="button" class="toggle-password" onclick="togglePasswordField('confirmPassword')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" name="change_password" class="btn-primary">
                                <i class="fas fa-key"></i>
                                Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/admin-ui.js"></script>
    <script>
        function togglePasswordField(id) {
            const passwordInput = document.getElementById(id);
            const icon = passwordInput.nextElementSibling.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
