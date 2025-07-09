<?php
require_once '../includes/config.php';

// Start output buffering to prevent header issues
ob_start();

session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get all users from users.json
    $users = getAllUsers();

    if (!$users) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to load users data'
        ]);
        exit();
    }

    // Format users data (without booking count)
    $users_list = [];

    foreach ($users as $user) {
        // Ensure user has required fields
        if (!isset($user['id']) || !isset($user['name'])) {
            continue;
        }

        $users_list[] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'] ?? '',
            'type' => $user['type'] ?? 'user',
            'created_at' => $user['created_at'] ?? ''
        ];
    }

    // Sort users by name
    usort($users_list, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    echo json_encode([
        'success' => true,
        'users' => $users_list,
        'total_count' => count($users_list)
    ]);

} catch (Exception $e) {
    error_log('Users API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>