<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Trendy Wear</title>
    <link rel="icon" type="image/png" href="images/logo.png">
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
                    <li class="nav-item"><a href="login.php" class="nav-link"><i class="fas fa-user"></i> Login</a></li>
                </ul>
            </div>
            
            <div class="menu-arrow-container">
                <i class="fas fa-chevron-down menu-arrow"></i>
            </div>
        </div>
    </nav>

    <section class="auth-section">
        <div class="auth-container signup-only">
            <div class="auth-form-container">
                <h1>Create Account</h1>
                <p class="auth-subtitle">Sign up to start shopping</p>
                
                <form id="signupForm" class="auth-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fullName"><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" id="fullName" name="fullName" required aria-describedby="fullNameError">
                            <span class="error-message" id="fullNameError" role="alert" aria-live="assertive"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="email" name="email" required aria-describedby="emailError">
                            <span class="error-message" id="emailError" role="alert" aria-live="assertive"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" required aria-describedby="passwordError">
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                            </div>
                            <div id="passwordStrength" class="password-strength" aria-hidden="true"></div>
                            <span class="error-message" id="passwordError" role="alert" aria-live="assertive"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="confirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="confirmPassword" name="confirmPassword" required aria-describedby="confirmPasswordError">
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmPassword')"></i>
                            </div>
                            <span class="error-message" id="confirmPasswordError" role="alert" aria-live="assertive"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender"><i class="fas fa-venus-mars"></i> Gender</label>
                            <select id="gender" name="gender" required aria-describedby="genderError">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                            <span class="error-message" id="genderError" role="alert" aria-live="assertive"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="dob"><i class="fas fa-calendar"></i> Date of Birth</label>
                            <input type="date" id="dob" name="dob" required aria-describedby="dobError">
                            <span class="error-message" id="dobError" role="alert" aria-live="assertive"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                            <input type="tel" id="phone" name="phone" required aria-describedby="phoneError" placeholder="+92 300 0000000" inputmode="tel">
                            <span class="error-message" id="phoneError" role="alert" aria-live="assertive"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="address"><i class="fas fa-home"></i> Address</label>
                            <textarea id="address" name="address" rows="3" required aria-describedby="addressError"></textarea>
                            <span class="error-message" id="addressError" role="alert" aria-live="assertive"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="checkbox-group">
                            <input type="checkbox" id="terms" name="terms" required aria-describedby="termsError">
                            <label for="terms">I agree to the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a></label>
                        </div>
                        <span class="error-message" id="termsError" role="alert" aria-live="assertive"></span>
                    </div>

                    <button type="submit" class="auth-btn">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer-section">
        <div class="container">
            <div class="footer-content py-5">
                <div class="row g-4">
                    <!-- Brand Column -->
                    <div class="col-lg-4 col-md-6">
                        <h5 class="footer-title mb-4">About Us</h5>
                        <p class="footer-description mb-4">Your destination for trendy and affordable fashion. Discover your style, define your elegance.</p>
                        <div class="social-links">
                            <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-icon" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-icon" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
                        </div>
                    </div>

                    <!-- Quick Links Column -->
                    <div class="col-lg-2 col-md-6">
                        <h5 class="footer-title mb-4">Quick Links</h5>
                        <ul class="footer-links list-unstyled">
                            <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                            <li><a href="products.php"><i class="fas fa-chevron-right"></i> Products</a></li>
                            <li><a href="about.html"><i class="fas fa-chevron-right"></i> About Us</a></li>
                            <li><a href="contact.html"><i class="fas fa-chevron-right"></i> Contact</a></li>
                        </ul>
                    </div>

                    <!-- Shop Categories Column -->
                    <div class="col-lg-3 col-md-6">
                        <h5 class="footer-title mb-4">Shop By</h5>
                        <ul class="footer-links list-unstyled">
                            <li><a href="products.php?category=Cargo Pant"><i class="fas fa-chevron-right"></i> Cargo Pant</a></li>
                            <li><a href="products.php?category=Sweater"><i class="fas fa-chevron-right"></i> Sweater</a></li>
                            <li><a href="products.php?category=Hoodie"><i class="fas fa-chevron-right"></i> Hoodie</a></li>
                            <li><a href="products.php?category=Jacket"><i class="fas fa-chevron-right"></i> Jacket</a></li>
                            <li><a href="products.php?category=Sweatshirt"><i class="fas fa-chevron-right"></i> Sweatshirt</a></li>
                            <li><a href="products.php?category=Hat"><i class="fas fa-chevron-right"></i> Hat</a></li>
                        </ul>
                    </div>

                    <!-- Contact Info Column -->
                    <div class="col-lg-3 col-md-6">
                        <h5 class="footer-title mb-4">Contact Info</h5>
                        <ul class="footer-contact list-unstyled">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>123 Fashion Street<br>Style City, SC 12345</span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span>(123) 456-7890</span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span>info@trendywear.com</span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span>Mon - Sat: 9AM - 8PM</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom py-4">
                <div class="text-center">
                    <p class="mb-0">&copy; 2026 <strong>Trendy Wear</strong>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
            <div class="footer-bottom text-center">
                <p class="mb-0" style="color: var(--primary-beige);">&copy; 2025 Trendy Wear. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/api.js"></script>
    <script src="js/script.js"></script>
    <script src="js/signup-new.js"></script>
</body>
</html>
