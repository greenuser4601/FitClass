<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Input validation
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
        exit();
    }
    
    if (!isValidEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
        exit();
    }
    
    // Get users and attempt login
    $users = getAllUsers();
    $user = findUserByEmail($users, $email);
    
    if ($user && $user['password'] === $password) {
        // Successful login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_type'] = $user['type'];
        
        // Log the activity
        logActivity('user_login', $user['id']);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'user_type' => $user['type']
        ]);
    } else {
        // Failed login
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
}

if ($action === 'register') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Comprehensive input validation
    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
        exit();
    }
    
    // Name validation
    if (strlen($name) < 2) {
        echo json_encode(['success' => false, 'message' => 'Name must be at least 2 characters long']);
        exit();
    }
    
    // Email validation
    if (!isValidEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
        exit();
    }
    
    // Password validation
    if (!isValidPassword($password)) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        exit();
    }
    
    // Password confirmation validation (if provided)
    if (!empty($confirm_password) && $password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit();
    }
    
    // Check if email already exists
    $users = getAllUsers();
    $existing_user = findUserByEmail($users, $email);
    
    if ($existing_user) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists']);
        exit();
    }
    
    // Create new user
    $new_user = [
        'name' => $name,
        'email' => $email,
        'password' => $password, // In production, this should be hashed
        'type' => 'user'
    ];
    
    // Attempt to add user
    if (addUser($new_user)) {
        // Log the activity
        logActivity('user_registered', 0, ['email' => $email, 'name' => $name]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Account created successfully! You can now login with your credentials.'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to create account. Please try again later.'
        ]);
    }
}

if ($action === 'logout') {
    // Log the logout activity if user is logged in
    if (isset($_SESSION['user_id'])) {
        logActivity('user_logout', $_SESSION['user_id']);
    }
    
    // Destroy session
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}
?>
