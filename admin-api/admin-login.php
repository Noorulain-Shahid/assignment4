<?php
require('db_connect.php');
session_start();

// If already logged in, redirect to profile (use JS redirect to avoid header issues)
if (isset($_SESSION['email'])) {
    echo "<script>window.location.href='admin-profile.php';</script>";
    exit();
}

$error_message = "";

if (isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 1) Simple, guaranteed demo login (bypasses database)
    if ($email === 'admin@ecommerce.com' && $password === 'admin123') {
        session_regenerate_id(true);
        $_SESSION['email'] = 'admin@ecommerce.com';
        $_SESSION['name'] = 'Administrator';
        $_SESSION['role'] = 'super_admin';
        $_SESSION['admin_id'] = 1;
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['last_activity'] = time();

        echo "<script>window.location.href='admin-dashboard.php';</script>";
        exit();
    }

    // 2) Fallback: check real admin_users table (if you add more admins)
    $email_esc = mysqli_real_escape_string($conn, $email);
    $query = "SELECT * FROM `admin_users` WHERE email='$email_esc' AND is_active = 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $admin = mysqli_fetch_assoc($result);

        if (password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['email'] = $admin['email'];
            $_SESSION['name'] = $admin['full_name'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['last_activity'] = time();

            echo "<script>window.location.href='admin-dashboard.php';</script>";
            exit();
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "Admin user not found or inactive.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E-Commerce Admin Panel</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/admin-style.css">
        <title>Admin Login - Trendy Wear</title>
        <link rel="icon" type="image/png" href="../images/favicon.png">
        <link rel="stylesheet" href="../admin-css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .error-message {
            color: #ff4444;
            margin-bottom: 15px;
            text-align: center;
        }
        .signup-link {
            text-align: center;
            margin-top: 15px;
        }
        .signup-link a {
            color: #3498db;
            text-decoration: none;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-shield-alt"></i>
                <h1>Admin Panel</h1>
                <p>E-Commerce Management System</p>
            </div>
            <form class="login-form" method="post" action="">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" required placeholder="admin@ecommerce.com">
                </div>
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <?php if($error_message != "") { ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php } ?>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
                
                <div class="signup-link">
                    <p>Don't have an account? <a href="admin-signup.php">Sign Up</a></p>
                </div>
            </form>
            <div class="login-footer">
                <p class="demo-credentials">
                    <strong>Demo Credentials:</strong><br>
                    Email: admin@ecommerce.com<br>
                    Password: admin123
                </p>
            </div>
        </div>
    </div>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password i');
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
