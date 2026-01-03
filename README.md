# Trendy Wear E-Commerce Website

A complete e-commerce website built with PHP, MySQL, and Bootstrap featuring both customer-facing frontend and admin panel.

## 🌟 Features

### Customer Features
- **User Registration & Authentication** - Secure user accounts with session management
- **Product Browsing** - Category-based product listing with search and pagination
- **Shopping Cart** - Add/remove items, quantity management
- **Secure Checkout** - Multiple payment options (Credit Card, PayPal, Cash on Delivery)
- **Order Management** - View order history, track orders, reorder functionality
- **Responsive Design** - Works on desktop, tablet, and mobile devices

### Admin Features
- **Customer Management** - View all registered customers and their order history
- **Product Management** - Add, edit, deactivate products with image upload
- **Order Management** - View and update order status, payment status
- **Dashboard Analytics** - Overview of sales, customers, and products
- **Inventory Management** - Track stock levels and product availability

## 🛠️ Technical Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5.3.2, JavaScript
- **Backend**: PHP 8.x
- **Database**: MySQL 8.x
- **Icons**: Font Awesome 6.4.0
- **Server**: Apache (XAMPP recommended)

## 📦 Installation

### Prerequisites
- XAMPP (or LAMP/WAMP) with PHP 8.x and MySQL
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Instructions

1. **Download and Extract**
   ```
   Extract all files to: C:\xampp\htdocs\assignment4\
   ```

2. **Start XAMPP Services**
   - Start Apache
   - Start MySQL

3. **Database Setup**
   - Open your web browser
   - Navigate to: `http://localhost/assignment4/setup.php`
   - Follow the setup wizard to create database and tables
   - **Important**: Delete `setup.php` after successful setup

4. **Access the Website**
   - Frontend: `http://localhost/assignment4/index.php`
   - Admin Panel: `http://localhost/assignment4/admin-api/admin-login.php`

### Default Admin Login
- **Username**: admin
- **Password**: admin123

## 📁 Project Structure

```
assignment4/
├── index.php                 # Homepage
├── products.php             # Product listing page
├── cart.php                 # Shopping cart
├── checkout.php             # Checkout process
├── orders.php               # Order history
├── order-confirmation.php   # Order confirmation
├── logout.php               # User logout
├── signup.html              # User registration
├── login.html               # User login
├── setup.php                # Database setup (delete after use)
├── database_setup.sql       # Database schema
├── api/                     # Frontend API endpoints
│   ├── signup.php          # User registration API
│   ├── login.php           # User authentication API
│   ├── products.php        # Products API
│   ├── cart.php           # Cart management API
│   ├── order-items.php    # Order items API
│   └── reorder.php        # Reorder functionality API
├── admin-api/              # Admin panel
│   ├── admin-login.php    # Admin login
│   ├── admin-dashboard.php # Admin dashboard
│   ├── admin-customers.php # Customer management
│   ├── admin-products.php  # Product management
│   ├── admin-orders.php    # Order management
│   └── db_connect.php     # Database connection
├── css/
│   └── styles.css         # Main stylesheet
├── js/
│   ├── script.js          # Main JavaScript
│   ├── cart.js           # Cart functionality
│   └── products.js       # Product functionality
└── images/                # Product and site images
```

## 🗄️ Database Schema

### Main Tables
- **users** - Customer accounts and profiles
- **products** - Product catalog with categories
- **cart** - Shopping cart items
- **orders** - Order information
- **order_items** - Individual order line items
- **categories** - Product categories
- **admin_users** - Admin accounts
- **reviews** - Product reviews (ready for implementation)

## 🚀 Key Features Explained

### User Registration & Login
- Secure password hashing with PHP's `password_hash()`
- Session-based authentication
- Form validation on both client and server side
- Users automatically appear in admin customer list

### Shopping Cart
- Session-based cart for guest users
- Database-stored cart for logged-in users
- Real-time stock checking
- Quantity limits based on inventory

### Order Processing
- Transaction-safe order creation
- Automatic stock reduction
- Multiple payment method support
- Email confirmation (ready for SMTP integration)

### Admin Panel
- Role-based access control
- Real-time data from frontend
- Order status management
- Customer analytics

## 📱 Responsive Design

The website is fully responsive and tested on:
- Desktop (1920px+)
- Laptop (1024px - 1919px)
- Tablet (768px - 1023px)
- Mobile (320px - 767px)

## 🔒 Security Features

- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF protection for forms
- Password hashing and secure sessions
- Input validation on all forms

## 🛒 Usage Guide

### For Customers
1. **Browse Products**: Visit the homepage or products page
2. **Register Account**: Click login and then "Sign up here"
3. **Add to Cart**: Click "Add to Cart" on any product
4. **Checkout**: Go to cart and click "Proceed to Checkout"
5. **Track Orders**: Use "My Orders" to view order history

### For Administrators
1. **Login**: Access admin panel with provided credentials
2. **Manage Products**: Add new products, update existing ones
3. **Process Orders**: View and update order statuses
4. **Customer Service**: View customer information and order history

## 🔧 Configuration

### Database Connection
Edit `admin-api/db_connect.php` to change database settings:
```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trendy_wear_db";
```

### Site Settings
- Tax rate: 8% (configurable in checkout.php)
- Free shipping threshold: $100
- Items per page: 12 products

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `db_connect.php`

2. **Images Not Loading**
   - Verify images folder exists and has proper permissions
   - Check image paths in database

3. **Session Issues**
   - Clear browser cookies and cache
   - Restart XAMPP services

4. **Admin Panel Access**
   - Use correct login credentials
   - Check if admin_users table has data

## 🔄 Future Enhancements

- Email notifications (SMTP integration)
- Product reviews and ratings
- Wishlist functionality
- Advanced search filters
- Payment gateway integration (Stripe, PayPal)
- Inventory alerts
- Customer support chat
- Mobile app API

## 📞 Support

For technical support or questions:
- Check the troubleshooting section
- Review the database setup
- Ensure all file permissions are correct

## 📄 License

This project is for educational purposes. Feel free to modify and extend as needed.

---

**Author**: Assignment 4 E-Commerce Project  
**Version**: 1.0  
**Last Updated**: January 2026  

🎉 **Enjoy your new e-commerce website!**