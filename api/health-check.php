
<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

// Check file existence
$files = [
    'users' => USERS_FILE,
    'classes' => CLASSES_FILE,
    'trainers' => TRAINERS_FILE,
    'bookings' => BOOKINGS_FILE
];

foreach ($files as $name => $file) {
    $health['checks'][$name . '_file'] = [
        'status' => file_exists($file) ? 'ok' : 'error',
        'message' => file_exists($file) ? 'File exists' : 'File missing'
    ];
}

// Check data loading
try {
    $users = getAllUsers();
    $health['checks']['users_data'] = [
        'status' => 'ok',
        'message' => count($users) . ' users loaded'
    ];
    
    $classes = getAllClasses();
    $health['checks']['classes_data'] = [
        'status' => 'ok',
        'message' => count($classes) . ' classes loaded'
    ];
    
    $trainers = getAllTrainers();
    $health['checks']['trainers_data'] = [
        'status' => 'ok',
        'message' => count($trainers) . ' trainers loaded'
    ];
    
    $bookings = getAllBookings();
    $health['checks']['bookings_data'] = [
        'status' => 'ok',
        'message' => count($bookings) . ' bookings loaded'
    ];
    
} catch (Exception $e) {
    $health['status'] = 'error';
    $health['checks']['data_loading'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Check permissions
$health['checks']['data_directory'] = [
    'status' => is_writable(DATA_DIR) ? 'ok' : 'warning',
    'message' => is_writable(DATA_DIR) ? 'Directory writable' : 'Directory not writable'
];

echo json_encode($health, JSON_PRETTY_PRINT);
?>
