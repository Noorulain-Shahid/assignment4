<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once 'admin-api/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

// Extract form data (align with current users table: username, email, password, role, full_name, phone, address, city, postal_code)
$fullName = trim($input['full_name'] ?? '');
$firstName = trim($input['firstName'] ?? '');
$lastName = trim($input['lastName'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$confirmPassword = $input['confirmPassword'] ?? ($input['confirm_password'] ?? '');
$phone = trim($input['phone'] ?? '');
$address = trim($input['address'] ?? '');
$city = trim($input['city'] ?? '');
$postalCode = trim($input['postal_code'] ?? $input['zipcode'] ?? '');
$username = trim($input['username'] ?? ($email ? explode('@', $email)[0] : ''));
$role = 'customer';

// Build full name if missing but first/last exist
if (empty($fullName) && ($firstName || $lastName)) {
    $fullName = trim($firstName . ' ' . $lastName);
}
// If still empty, fall back to username/email
if (empty($fullName)) {
    $fullName = $username ?: $email;
}

// Basic validation
$missingFields = [];
if (empty($email)) $missingFields[] = 'Email';
if (empty($password)) $missingFields[] = 'Password';
if (empty($fullName)) $missingFields[] = 'Full Name';
if (empty($username)) $missingFields[] = 'Username';

if (!empty($missingFields)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields: ' . implode(', ', $missingFields)]);
    exit;
}

// If confirm password is empty, trust the already validated frontend and mirror password
if ($confirmPassword === '') {
    $confirmPassword = $password;
}

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit;
}

try {
    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email address already registered']);
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user (matching current schema)
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name, phone, address, city, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $username, $email, $hashedPassword, $role, $fullName, $phone, $address, $city, $postalCode);
    
    if ($stmt->execute()) {
        // Start session
        $userId = $conn->insert_id;
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $fullName;
        
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully! Welcome to Trendy Wear.',
            'user' => [
                'id' => $userId,
                'username' => $username,
                'email' => $email,
                'name' => $fullName
            ],
            'redirect' => 'index.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating account. Please try again.']);
    }
    
    $stmt->close();
    $checkStmt->close();
    
} catch (Exception $e) {
    error_log("Signup error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>
