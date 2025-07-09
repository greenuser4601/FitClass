<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get real-time statistics
$stats = getSystemStats();

// Add additional stats for dashboard
$recent_bookings = getAllBookings();
usort($recent_bookings, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
$recent_bookings = array_slice($recent_bookings, 0, 5);

// Calculate monthly revenue (current month)
$current_month = date('Y-m');
$monthly_revenue = 0;
$classes = getAllClasses();

foreach (getAllBookings() as $booking) {
    if ($booking['status'] === 'confirmed' && 
        substr($booking['booking_date'], 0, 7) === $current_month) {
        $class = findClassById($classes, $booking['class_id']);
        if ($class) {
            $monthly_revenue += $class['price'];
        }
    }
}

// Get today's bookings
$today = date('Y-m-d');
$todays_bookings = array_filter(getAllBookings(), function($booking) use ($today) {
    return $booking['booking_date'] === $today;
});

$response = [
    'success' => true,
    'stats' => [
        'total_users' => $stats['total_users'],
        'total_classes' => $stats['total_classes'],
        'total_trainers' => $stats['total_trainers'],
        'total_bookings' => $stats['total_bookings'],
        'total_revenue' => $stats['total_revenue'],
        'monthly_revenue' => $monthly_revenue,
        'confirmed_bookings' => $stats['confirmed_bookings'],
        'pending_bookings' => $stats['pending_bookings'],
        'cancelled_bookings' => $stats['cancelled_bookings'],
        'todays_bookings' => count($todays_bookings)
    ],
    'recent_bookings' => $recent_bookings,
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response);
?>
