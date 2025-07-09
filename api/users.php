
<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get all users
    $users = getAllUsers();
    $bookings = getAllBookings();
    
    // Count complete bookings for each user
    $users_with_stats = [];
    
    foreach ($users as $user) {
        $complete_bookings = 0;
        
        // Count completed bookings for this user
        foreach ($bookings as $booking) {
            if ($booking['user_id'] == $user['id'] && in_array($booking['status'], ['confirmed', 'paid', 'completed'])) {
                $complete_bookings++;
            }
        }
        
        $users_with_stats[] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'type' => $user['type'],
            'complete_bookings' => $complete_bookings,
            'created_at' => $user['created_at']
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
    ]);
    
} catch (Exception $e) {
    error_log('Users API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching users data'
    ]);
}
?>
