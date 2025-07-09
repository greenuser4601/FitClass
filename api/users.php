
<?php
session_start();
require_once '../includes/config.php';

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
    
    // Get all bookings from bookings.json
    $bookings = getAllBookings();
    if (!$bookings) {
        $bookings = [];
    }
    
    // Process each user and count their complete bookings
    $users_with_stats = [];
    
    foreach ($users as $user) {
        // Ensure user has required fields
        if (!isset($user['id']) || !isset($user['name'])) {
            continue;
        }
        
        $complete_bookings = 0;
        
        // Count completed bookings for this user (status: confirmed, paid, completed)
        foreach ($bookings as $booking) {
            if (isset($booking['user_id']) && isset($booking['status']) && 
                $booking['user_id'] == $user['id'] && 
                in_array($booking['status'], ['confirmed', 'paid', 'completed'])) {
                $complete_bookings++;
            }
        }
        
        // Add user with their complete booking count (0 if none)
        $users_with_stats[] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'] ?? '',
            'type' => $user['type'] ?? 'user',
            'complete_bookings' => $complete_bookings,
            'created_at' => $user['created_at'] ?? ''
        ];
    }
    
    // Sort users by complete bookings (descending)
    usort($users_with_stats, function($a, $b) {
        return $b['complete_bookings'] <=> $a['complete_bookings'];
    });
    
    echo json_encode([
        'success' => true,
        'users' => $users_with_stats,
        'total_count' => count($users_with_stats)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log('Users API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
