<!DOCTYPE html>
<html>
<head>
    <title>Database Setup - Trendy Wear</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h1>Trendy Wear Database Setup</h1>
    
    <?php
    // Database connection details
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "trendy_wear_db";

    try {
        // Create connection without database first
        $conn = new mysqli($servername, $username, $password);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Create database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
        if ($conn->query($sql) === TRUE) {
            echo "<p class='success'>✓ Database '$dbname' created/verified successfully</p>";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
        
        // Close connection and reconnect with database
        $conn->close();
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Read and execute the SQL file (if it exists) to set up schema + sample data
        $successCount = 0;
        $errorCount = 0;
        if (file_exists('database_setup.sql')) {
            $sqlFile = file_get_contents('database_setup.sql');
            if ($sqlFile === false) {
                throw new Exception("Could not read database_setup.sql file");
            }
            
            // Split SQL file into individual statements
            $statements = explode(';', $sqlFile);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    if ($conn->query($statement) === TRUE) {
                        $successCount++;
                    } else {
                        echo "<p class='warning'>Warning executing statement: " . htmlspecialchars($conn->error) . "</p>";
                        $errorCount++;
                    }
                }
            }
        }
        
        echo "<p class='success'>✓ Database setup completed successfully!</p>";
        echo "<p class='success'>✓ $successCount statements executed successfully</p>";
        if ($errorCount > 0) {
            echo "<p class='warning'>⚠ $errorCount statements had warnings</p>";
        }
        
        // Verify tables were created (and create any missing critical tables)
        $tables = [
            'users', 'admin_users', 'categories', 'products', 'cart', 
            'orders', 'order_items', 'reviews', 'coupons', 'contact_submissions', 'user_sessions'
        ];
        
        echo "<h2>Table Verification:</h2>";
        foreach ($tables as $table) {
            $result = $conn->query("SELECT COUNT(*) as count FROM $table");
            if (!$result) {
                // Try to create missing table
                $createSql = '';
                if ($table === 'admin_users') {
                    $createSql = "CREATE TABLE IF NOT EXISTS admin_users (\n" .
                        "    id INT AUTO_INCREMENT PRIMARY KEY,\n" .
                        "    username VARCHAR(50) UNIQUE NOT NULL,\n" .
                        "    email VARCHAR(100) UNIQUE NOT NULL,\n" .
                        "    password VARCHAR(255) NOT NULL,\n" .
                        "    full_name VARCHAR(100) NOT NULL,\n" .
                        "    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',\n" .
                        "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n" .
                        "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" .
                        "    is_active BOOLEAN DEFAULT TRUE\n" .
                        ")";
                } elseif ($table === 'reviews') {
                    $createSql = "CREATE TABLE IF NOT EXISTS reviews (\n" .
                        "    id INT AUTO_INCREMENT PRIMARY KEY,\n" .
                        "    product_id INT NOT NULL,\n" .
                        "    user_id INT NOT NULL,\n" .
                        "    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),\n" .
                        "    review_title VARCHAR(255),\n" .
                        "    review_text TEXT,\n" .
                        "    is_verified_purchase BOOLEAN DEFAULT FALSE,\n" .
                        "    is_approved BOOLEAN DEFAULT TRUE,\n" .
                        "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n" .
                        "    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,\n" .
                        "    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE\n" .
                        ")";
                } elseif ($table === 'coupons') {
                    $createSql = "CREATE TABLE IF NOT EXISTS coupons (\n" .
                        "    id INT AUTO_INCREMENT PRIMARY KEY,\n" .
                        "    code VARCHAR(50) UNIQUE NOT NULL,\n" .
                        "    type ENUM('percentage', 'fixed') NOT NULL,\n" .
                        "    value DECIMAL(10,2) NOT NULL,\n" .
                        "    minimum_amount DECIMAL(10,2) DEFAULT 0,\n" .
                        "    maximum_discount DECIMAL(10,2),\n" .
                        "    usage_limit INT DEFAULT 1,\n" .
                        "    used_count INT DEFAULT 0,\n" .
                        "    expires_at DATETIME,\n" .
                        "    is_active BOOLEAN DEFAULT TRUE,\n" .
                        "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n" .
                        ")";
                } elseif ($table === 'contact_submissions') {
                    $createSql = "CREATE TABLE IF NOT EXISTS contact_submissions (\n" .
                        "    id INT AUTO_INCREMENT PRIMARY KEY,\n" .
                        "    name VARCHAR(100) NOT NULL,\n" .
                        "    email VARCHAR(100) NOT NULL,\n" .
                        "    subject VARCHAR(255),\n" .
                        "    message TEXT NOT NULL,\n" .
                        "    status ENUM('new', 'read', 'responded') DEFAULT 'new',\n" .
                        "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n" .
                        ")";
                } elseif ($table === 'user_sessions') {
                    $createSql = "CREATE TABLE IF NOT EXISTS user_sessions (\n" .
                        "    id INT AUTO_INCREMENT PRIMARY KEY,\n" .
                        "    user_id INT NOT NULL,\n" .
                        "    session_token VARCHAR(255) NOT NULL,\n" .
                        "    expires_at DATETIME NOT NULL,\n" .
                        "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n" .
                        "    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE\n" .
                        ")";
                }

                if ($createSql !== '' && $conn->query($createSql) === TRUE) {
                    echo "<p class='warning'>Table '$table' was missing and has now been created automatically.</p>";
                    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
                }
            }

            if ($result) {
                $row = $result->fetch_assoc();
                echo "<p class='success'>✓ Table '$table' exists with {$row['count']} records</p>";
            } else {
                echo "<p class='error'>✗ Table '$table' not found</p>";
            }
        }

        // Ensure required columns exist on categories table for admin management
        echo "<h2>Categories Table Schema Check:</h2>";
        $categorySchemaUpdates = [
            "ALTER TABLE categories ADD COLUMN IF NOT EXISTS description TEXT NULL",
            "ALTER TABLE categories ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) NULL",
            "ALTER TABLE categories ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE",
            "ALTER TABLE categories ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        ];

        foreach ($categorySchemaUpdates as $alterSql) {
            if ($conn->query($alterSql) === TRUE) {
                // Column added or already existed
            } else {
                echo "<p class='warning'>Categories table update warning: " . htmlspecialchars($conn->error) . "</p>";
            }
        }
        echo "<p class='success'>✓ Categories table columns verified/updated for admin panel</p>";

        // Ensure required columns exist on products table for admin management
        echo "<h2>Products Table Schema Check:</h2>";
        $productSchemaUpdates = [
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INT NULL AFTER description",
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) NULL",
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS additional_images JSON NULL",
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS stock_quantity INT DEFAULT 0",
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS sku VARCHAR(100) NULL",
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS sizes JSON NULL",
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS colors JSON NULL",
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS gender ENUM('Men','Women','Kids','Unisex') DEFAULT 'Unisex'",
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS is_featured BOOLEAN DEFAULT FALSE",
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE",
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "ALTER TABLE products ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];

        foreach ($productSchemaUpdates as $alterSql) {
            if ($conn->query($alterSql) === TRUE) {
                // Column added or already existed (IF NOT EXISTS)
            } else {
                echo "<p class='warning'>Products table update warning: " . htmlspecialchars($conn->error) . "</p>";
            }
        }
        echo "<p class='success'>✓ Products table columns verified/updated for admin panel</p>";

        // Map existing products to image files in the images/ folder
        echo "<h2>Product Images Mapping:</h2>";
        $productImages = [
            "Beanie Woolen Hat" => "images/winter hat girl.png",
            "Women's Hooded Trench Coat" => "images/red sweater for women.png",
            "Hat Scarf Set" => "images/winter hat black boy.png",
            "Cable-Knit Sweater" => "images/red sweater for boys.png",
            "Pom Pom Hat and Gloves" => "images/winter hat red black kids.png",
            "Striped Oversized Sweater" => "images/white and baige sweater girl.png",
            "Pom Pom Hat" => "images/winter hat red girl.png",
            "Varsity Jacket (Unisex)" => "images/black white varsity jacket.png",
            "Capreze Men Winter Jacket" => "images/black boy jacket.png",
            "Blue Denim Jacket" => "images/Denim Jacket for boys.png",
            "Raglan Sweater" => "images/skin sweater for boys.png",
            "Green Varsity Jacket" => "images/Green white varsity jacket for kids.png",
            "Oversized vintage denim varsity" => "images/Blue white varsity jacket for girls.png",
            "Varsity Embroidered Jacket" => "images/red white Varsity Jacket for kids.png",
            "Loose Fit Black Cargo Skate Pant" => "images/Black cargo pant for boys.png",
            "Nature Varsity Jacket" => "images/musturd and white Varsity Jacket for men.png",
            "Military Hooded Jacket" => "images/green jacket.png",
            "Puffer Jacket" => "images/black jacket for boys kids.png",
            "Windproof Coat Jacket" => "images/skin color sweater for kids.png",
            "Beanie Warm Hat" => "images/winter hat white boy.png",
            "Fawn Mock Neck Puffer Jacket" => "images/skin jacket for boys.png",
            "Sherpa Collar Jacket" => "images/red jackets for boys.png",
            "Mowbeat Baseball Jacket Varsity Style" => "images/Orange and white varsity jacket for kids.png",
            "Khaki Green Cargo Trouser" => "images/Green Cargo pants for boys.png",
            "Cargo Joggers Pant" => "images/Gray cargo pant for boys.png",
            "Crew Neck Sweater" => "images/brown sweater boy.png",
            "Fur Collar Down Puffer Jacket" => "images/black jacket.png",
            "Walmart Hooded Jacket" => "images/black jacket for kids.png",
            "Pocketed Denim Jacket" => "images/Black Denim Jacket for boys.png",
            "Varsity Jacket Casual Baseball Outwear" => "images/Black and white varsity jacket for kids.png",
            "Striped Knit Zip Collar Pullover Sweater" => "images/black and white lining sweater girls.png",
            "French Terry Hoodie" => "images/grey hoodie for boys.png",
            "Sand Drop Shoulder Hoodie" => "images/grey hoodie for girls.png",
            "Pink Butterfly and Stars Printed Hoodie" => "images/pink hoodie.png",
            "Lilac Basic Fleece Hoodie" => "images/purple hoodie for girls.png",
            "Plain White Hoodie" => "images/white hoodie.png",
            "Crew Neck Long Sleeve Hoodie" => "images/yellow hoodie for boys.png",
            "Accolade Crew Neck Sweatshirt" => "images/beige sweatshirt for boys.png",
            "Levy Essential Fleece Hoodie" => "images/black hoodie for boys.png",
            "Plain Fleece Full Sleeves Pull Over Sweatshirt" => "images/black sweat shirt for men.png",
            "Oversized Active Sweatshirt" => "images/pink sweat shirt for girls.png",
            "Line Art Fleece Full Sleeves Sweatshirt" => "images/pink sweatshirt.png",
            "Block Sweatshirt" => "images/pink white grey sweatshirt.png",
            "Lightweight Sweatshirt" => "images/sky blue sweatshirt for boys.png",
            "Top Crew Sweatshirt" => "images/white blue sweatshirt for kids.png",
            "Oversized Sweatshirt" => "images/white sweatshirt for women.png",
            "White Hoodie" => "images/white hoodie.png",
            "Grey Hoodie for Girls" => "images/grey hoodie for girls.png",
            "Denim Jacket for Boys" => "images/Denim Jacket for boys.png",
            "Skin Color Sweater for Kids" => "images/skin color sweater for kids.png",
            "Black Cargo Pants" => "images/black cargo pant.png",
            "Orange Hat" => "images/orange hat.png",
            "Blue Sweatshirt" => "images/blue sweatshirt.png"
        ];

        $updatedCount = 0;
        foreach ($productImages as $name => $path) {
            $nameEsc = $conn->real_escape_string($name);
            $pathEsc = $conn->real_escape_string($path);
            $updateSql = "UPDATE products SET image_url='$pathEsc' WHERE name='$nameEsc' AND (image_url IS NULL OR image_url='')";
            if ($conn->query($updateSql) === TRUE && $conn->affected_rows > 0) {
                $updatedCount += $conn->affected_rows;
            }
        }
        echo "<p class='success'>✓ Product images mapped from images/ folder for $updatedCount products (where names matched)</p>";

        // Ensure client categories (All/Women/Men/Kids) exist in categories table
        echo "<h2>Client Categories Sync:</h2>";
        $clientCategories = [
            ['name' => 'Women', 'description' => 'Women\'s collection from client site'],
            ['name' => 'Men', 'description' => 'Men\'s collection from client site'],
            ['name' => 'Kids', 'description' => 'Kids collection from client site'],
            ['name' => 'Cargo Pant', 'description' => 'Cargo pant products from client categories'],
            ['name' => 'Hat', 'description' => 'Hat products from client categories'],
            ['name' => 'Hoodie', 'description' => 'Hoodie products from client categories'],
            ['name' => 'Jacket', 'description' => 'Jacket products from client categories'],
            ['name' => 'Sweater', 'description' => 'Sweater products from client categories'],
            ['name' => 'Sweatshirt', 'description' => 'Sweatshirt products from client categories']
        ];

        $catCreated = 0;
        foreach ($clientCategories as $cat) {
            $nameEsc = $conn->real_escape_string($cat['name']);
            $descEsc = $conn->real_escape_string($cat['description']);
            $exists = $conn->query("SELECT id FROM categories WHERE name='$nameEsc' LIMIT 1");
            if ($exists && $exists->num_rows === 0) {
                if ($conn->query("INSERT INTO categories (name, description, is_active) VALUES ('$nameEsc', '$descEsc', 1)")) {
                    $catCreated++;
                }
            }
        }
        // Deactivate old group-style categories and activate only the specific product-type categories
        $conn->query("UPDATE categories SET is_active = 0 WHERE name IN ('Women','Men','Kids')");
        $conn->query("UPDATE categories SET is_active = 1 WHERE name IN ('Cargo Pant','Hat','Hoodie','Jacket','Sweater','Sweatshirt')");

        echo "<p class='success'>✓ Client categories synced: $catCreated new category records added, and active categories limited to Cargo Pant / Hat / Hoodie / Jacket / Sweater / Sweatshirt</p>";

        // Ensure there is at least one default admin user
        $adminCheck = $conn->query("SELECT COUNT(*) AS count FROM admin_users");
        if ($adminCheck && ($row = $adminCheck->fetch_assoc()) && (int)$row['count'] === 0) {
            $defaultPasswordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // 'password'
            $conn->query("INSERT INTO admin_users (username, email, password, full_name, role) VALUES ('admin', 'admin@trendywear.com', '$defaultPasswordHash', 'Administrator', 'super_admin')");
            echo "<p class='success'>✓ Default admin user created (email: admin@trendywear.com)</p>";
        }
        
        echo "<h2>Next Steps:</h2>";
        echo "<ol>";
        echo "<li>Your database is now set up with sample data</li>";
        echo "<li>Default admin login: username 'admin', password 'admin123'</li>";
        echo "<li>Sample products have been added</li>";
        echo "<li>You can now test the website functionality</li>";
        echo "<li><strong>Important:</strong> Delete this setup.php file after setup is complete for security</li>";
        echo "</ol>";
        
        echo "<h2>Access Points:</h2>";
        echo "<ul>";
        echo "<li><a href='index.php'>Main Website (Frontend)</a></li>";
        echo "<li><a href='admin-api/admin-login.php'>Admin Panel</a></li>";
        echo "<li><a href='products.php'>Products Page</a></li>";
        echo "<li><a href='signup.html'>Customer Registration</a></li>";
        echo "</ul>";
        
        $conn->close();
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    }
    ?>
</body>
</html>