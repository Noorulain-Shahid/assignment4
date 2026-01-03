<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../admin-api/db_connect.php';

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

// Extract form data
$firstName = trim($input['firstName'] ?? '');
$lastName = trim($input['lastName'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$confirmPassword = $input['confirmPassword'] ?? '';
$phone = trim($input['phone'] ?? '');
$address = trim($input['address'] ?? '');
$city = trim($input['city'] ?? '');
$state = trim($input['state'] ?? '');
$zipcode = trim($input['zipcode'] ?? '');
$country = trim($input['country'] ?? '');

// Basic validation
if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
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
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone, address, city, state, zipcode, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $firstName, $lastName, $email, $hashedPassword, $phone, $address, $city, $state, $zipcode, $country);
    
    if ($stmt->execute()) {
        // Start session
        $userId = $conn->insert_id;
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully! Welcome to Trendy Wear.',
            'user' => [
                'id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'name' => $firstName . ' ' . $lastName
            ],
            'redirect' => 'home.html'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating account. Please try again.']);
    }
    
    $stmt->close();
    $checkStmt->close();
    
} catch (Exception $e) {
    error_log("Signup error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again later.']);
}

$conn->close();
?>
