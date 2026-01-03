<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Trendy Wear</title>
    <link rel="shortcut icon" href="images/logo.png?v=1">
    <link rel="icon" type="image/x-icon" href="images/logo.png?v=1">
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
                        <a href="products.php" class="nav-link">
                            Products <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <ul class="products-dropdown">
                            <li><a href="products.php?search=cargo">Cargo Pant</a></li>
                            <li><a href="products.php?search=hat">Hat</a></li>
                            <li><a href="products.php?search=hoodie">Hoodie</a></li>
                            <li><a href="products.php?search=jacket">Jacket</a></li>
                            <li><a href="products.php?search=sweater">Sweater</a></li>
                            <li><a href="products.php?search=sweatshirt">Sweatshirt</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="contact.html" class="nav-link">Contact Us</a></li>
                    <li class="nav-item"><a href="login.php" class="nav-link active"><i class="fas fa-user"></i> Login</a></li>
                </ul>
            </div>
            
            <div class="menu-arrow-container">
                <i class="fas fa-chevron-down menu-arrow"></i>
            </div>
        </div>
    </nav>

    <!-- LOGIN FORM SECTION -->
    <section class="auth-section">
        <div class="auth-container">
            <div class="auth-image">
                <img src="images/login page.png" alt="Fashion" onerror="this.src='https://via.placeholder.com/600x800/F5F5DC/3E3E3E?text=Trendy+Wear'">
                <div class="auth-overlay">
                    <h2>Welcome Back!</h2>
                    <p>Login to continue your fashion journey</p>
                </div>
            </div>
            
            <div class="auth-form-container">
                <h1>Login</h1>
                <p class="auth-subtitle">Enter your credentials to access your account</p>
                
                <form id="loginForm" class="auth-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" id="email" name="email" required>
                            <span class="error-message" id="emailError"></span>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" required>
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                            </div>
                            <span class="error-message" id="passwordError"></span>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="remember-forgot">
                            <div class="remember-me">
                                <input type="checkbox" id="rememberMe" name="rememberMe">
                                <label for="rememberMe">Remember Me</label>
                            </div>
                            <a href="https://support.google.com/accounts/answer/41078" class="forgot-password">Forgot Password?</a>
                        </div>
                    </div>
                    
                    <button type="submit" class="auth-btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <div class="social-login">
                    <p class="social-login-title">Or continue with</p>
                    <div class="social-buttons">
                        <a href="https://accounts.google.com/" class="social-btn">
                            <i class="fab fa-google"></i> Google
                        </a>
                        <a href="https://www.facebook.com" class="social-btn">
                            <i class="fab fa-facebook"></i> Facebook
                        </a>
                    </div>
                </div>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
                </div>
            </div>
        </div>
    </section>
<!-- FOOTER -->
    <footer class="footer bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4 footer-content">
                <div class="col-lg-3 col-md-6 footer-section">
                    <h3 class="h5 mb-3">About Trendy Wear</h3>
                    <p class="text-white-50">Your destination for elegant and trendy fashion. We bring you the latest styles with premium quality.</p>
                </div>
                <div class="col-lg-3 col-md-6 footer-section">
                    <h3 class="h5 mb-3">Quick Links</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="about.html" class="text-white-50 text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="contact.html" class="text-white-50 text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 footer-section">
                    <h3 class="h5 mb-3">Customer Service</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Shipping Info</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Returns</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">FAQ</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Size Guide</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 footer-section">
                    <h3 class="h5 mb-3">Follow Us</h3>
                    <div class="social-links d-flex gap-3">
                        <a href="https://www.facebook.com" class="text-white fs-4"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com" class="text-white fs-4"><i class="fab fa-instagram"></i></a>
                        <a href="https://x.com" class="text-white fs-4"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.pinterest.com" class="text-white fs-4"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="footer-bottom text-center">
                <p class="mb-0" style="color: var(--primary-beige);">&copy; 2025 Trendy Wear. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/api.js"></script>
    <script src="js/script.js"></script>
    <script src="js/login.js"></script>
</body>
</html>
