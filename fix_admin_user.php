<?php
require('admin-api/db_connect.php');

$email = 'admin@ecommerce.com';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$full_name = 'Administrator';
$username = 'admin';

// Check if user exists
$check_query = "SELECT id FROM admin_users WHERE email='$email' OR id=1";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    // Update existing user (id=1 or email match)
    $row = mysqli_fetch_assoc($result);
    $id = $row['id'];
    $update_query = "UPDATE admin_users SET 
        email='$email', 
        password='$hashed_password', 
        full_name='$full_name',
        username='$username',
        is_active=1 
        WHERE id=$id";
    
    if (mysqli_query($conn, $update_query)) {
        echo "Admin user updated successfully.\n";
    } else {
        echo "Error updating admin user: " . mysqli_error($conn) . "\n";
    }
} else {
    // Insert new user
    $insert_query = "INSERT INTO admin_users (username, email, password, full_name, role, is_active) 
        VALUES ('$username', '$email', '$hashed_password', '$full_name', 'super_admin', 1)";
    
    if (mysqli_query($conn, $insert_query)) {
        echo "Admin user created successfully.\n";
    } else {
        echo "Error creating admin user: " . mysqli_error($conn) . "\n";
    }
}
?>
