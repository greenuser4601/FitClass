
<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $users = getAllUsers();
    
    // Remove sensitive data
    $safe_users = array_map(function($user) {
        unset($user['password']);
        return $user;
    }, $users);
    
    echo json_encode([
        'success' => true,
        'users' => $safe_users,
        'total_count' => count($safe_users)
    ]);
} elseif ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'delete':
            $user_id = intval($_POST['user_id'] ?? 0);
            if ($user_id > 0) {
                if (deleteUser($user_id)) {
                    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
            }
            break;
            
        case 'update':
            $user_id = intval($_POST['user_id'] ?? 0);
            $name = sanitize_input($_POST['name'] ?? '');
            $email = sanitize_input($_POST['email'] ?? '');
            $type = sanitize_input($_POST['type'] ?? '');
            
            if ($user_id > 0 && $name && $email && $type) {
                $user_data = [
                    'name' => $name,
                    'email' => $email,
                    'type' => $type
                ];
                
                if (updateUser($user_id, $user_data)) {
                    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update user']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid user data']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
