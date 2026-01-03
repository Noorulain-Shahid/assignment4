<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'admin-api/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to access cart']);
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get cart items
    try {
            $sql = "
                SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ? 
                ORDER BY COALESCE(c.updated_at, c.created_at) DESC
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log('Cart API prepare failed (primary): ' . $conn->error . ' SQL: ' . $sql);
                // Fallback ordering
                $sql = "
                    SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ? 
                    ORDER BY c.id DESC
                ";
                $stmt = $conn->prepare($sql);
            }

            if (!$stmt) {
                throw new Exception('Unable to load cart items: ' . $conn->error);
            }

            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
        
        $cartItems = [];
        $total = 0;
        
        while ($row = $result->fetch_assoc()) {
            $itemTotal = $row['price'] * $row['quantity'];
            $row['item_total'] = $itemTotal;
            $total += $itemTotal;
            $cartItems[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'cart_items' => $cartItems,
            'total' => $total,
            'item_count' => count($cartItems)
        ]);
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Cart GET error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching cart']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add item to cart
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    $productId = intval($input['product_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 1);
    $size = trim($input['size'] ?? '');
    $color = trim($input['color'] ?? '');
    
    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }
    
    try {
        // Check if product exists and has enough stock
        $productStmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ? AND is_active = 1");
        $productStmt->bind_param("i", $productId);
        $productStmt->execute();
        $productResult = $productStmt->get_result();
        
        if ($productResult->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        $product = $productResult->fetch_assoc();
        if ($product['stock_quantity'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            exit;
        }
        
        // Check if item already exists in cart
        $checkStmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ? AND color = ?");
        $checkStmt->bind_param("iiss", $userId, $productId, $size, $color);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Update existing cart item
            $existingItem = $checkResult->fetch_assoc();
            $newQuantity = $existingItem['quantity'] + $quantity;
            
            if ($newQuantity > $product['stock_quantity']) {
                echo json_encode(['success' => false, 'message' => 'Cannot add more items. Stock limit reached.']);
                exit;
            }
            
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $newQuantity, $existingItem['id']);
            $updateStmt->execute();
            $updateStmt->close();
            
        } else {
            // Insert new cart item
            $insertStmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, size, color) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->bind_param("iiiss", $userId, $productId, $quantity, $size, $color);
            $insertStmt->execute();
            $insertStmt->close();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart successfully'
        ]);
        
        $productStmt->close();
        $checkStmt->close();
        
    } catch (Exception $e) {
        error_log("Cart POST error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error adding item to cart']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Update cart item quantity
    $input = json_decode(file_get_contents('php://input'), true);
    
    $cartId = intval($input['cart_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 1);
    
    if ($cartId <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    try {
        // Check if cart item belongs to user and get product stock
        $stmt = $conn->prepare("
            SELECT c.id, p.stock_quantity 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.id = ? AND c.user_id = ?
        ");
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            exit;
        }
        
        $item = $result->fetch_assoc();
        if ($quantity > $item['stock_quantity']) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            exit;
        }
        
        // Update quantity
        $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $quantity, $cartId);
        $updateStmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated successfully'
        ]);
        
        $stmt->close();
        $updateStmt->close();
        
    } catch (Exception $e) {
        error_log("Cart PUT error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error updating cart']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Remove item from cart
    $cartId = intval($_GET['cart_id'] ?? 0);
    
    if ($cartId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from cart'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Cart DELETE error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error removing item from cart']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

$conn->close();
?>
