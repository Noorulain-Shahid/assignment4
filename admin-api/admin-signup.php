<?php
require('db_connect.php');

$message = "";
$error = "";

if (isset($_REQUEST['name'])) {
    $name = stripslashes($_REQUEST['name']);
    $name = mysqli_real_escape_string($conn, $name);
    $email = stripslashes($_REQUEST['email']);
    $email = mysqli_real_escape_string($conn, $email);
    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($conn, $password);
    $phone = stripslashes($_REQUEST['phone']);
    $phone = mysqli_real_escape_string($conn, $phone);

    // Check if email already exists
    $check_query = "SELECT * FROM `users` WHERE email='$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Email already exists!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT into `users` (name, email, password, phone, role)
                  VALUES ('$name', '$email', '$hashed_password', '$phone', 'Administrator')";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $message = "You are registered successfully. <a href='admin-login.php'>Login here</a>";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Signup - E-Commerce Admin Panel</title>
        <link rel="icon" type="image/png" href="../images/favicon.png">
        <link rel="stylesheet" href="../admin-css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .message {
            color: green;
            margin-bottom: 15px;
            text-align: center;
        }
        .error {
            color: #ff4444;
            margin-bottom: 15px;
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #3498db;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-user-plus"></i>
                <h1>Admin Sign Up</h1>
                <p>Create New Admin Account</p>
            </div>
            <form class="login-form" method="post" action="">
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i>
                        Full Name
                    </label>
                    <input type="text" id="name" name="name" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" required placeholder="admin@ecommerce.com">
                </div>
                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i>
                        Phone Number
                    </label>
                    <input type="text" id="phone" name="phone" placeholder="+1234567890">
                </div>
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required placeholder="Create a password">
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <?php if($message != "") { ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php } ?>
                <?php if($error != "") { ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php } ?>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Sign Up
                </button>
                
                <div class="login-link">
                    <p>Already have an account? <a href="admin-login.php">Login</a></p>
                </div>
            </form>
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
