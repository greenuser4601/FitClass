<?php
// Enhanced functions for FitClass application

// User helper functions
function findUserById($users, $user_id) {
    foreach ($users as $user) {
        if ($user['id'] == $user_id) {
            return $user;
        }
    }
    return null;
}

function findUserByEmail($users, $email) {
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    return null;
}

// Class helper functions
function findClassById($classes, $class_id) {
    foreach ($classes as $class) {
        if ($class['id'] == $class_id) {
            return $class;
        }
    }
    return null;
}

// Trainer helper functions
function findTrainerById($trainers, $trainer_id) {
    foreach ($trainers as $trainer) {
        if ($trainer['id'] == $trainer_id) {
            return $trainer;
        }
    }
    return null;
}

// Generate unique ID
function generateUniqueId($existing_items) {
    if (empty($existing_items)) {
        return 1;
    }
    
    $max_id = 0;
    foreach ($existing_items as $item) {
        if (isset($item['id']) && $item['id'] > $max_id) {
            $max_id = $item['id'];
        }
    }
    
    return $max_id + 1;
}

// Write JSON file safely
function write_json_file($file_path, $data) {
    $json_data = json_encode($data, JSON_PRETTY_PRINT);
    if ($json_data === false) {
        return false;
    }
    
    $temp_file = $file_path . '.tmp';
    $result = file_put_contents($temp_file, $json_data, LOCK_EX);
    
    if ($result !== false) {
        return rename($temp_file, $file_path);
    }
    
    return false;
}

// Activity logging
function logActivity($action, $user_id, $details = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'user_id' => $user_id,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $activity_log = [];
    if (file_exists(ACTIVITY_LOG_FILE)) {
        $existing_log = file_get_contents(ACTIVITY_LOG_FILE);
        if ($existing_log) {
            $activity_log = json_decode($existing_log, true) ?: [];
        }
    }
    
    $activity_log[] = $log_entry;
    
    // Keep only last 1000 entries
    if (count($activity_log) > 1000) {
        $activity_log = array_slice($activity_log, -1000);
    }
    
    file_put_contents(ACTIVITY_LOG_FILE, json_encode($activity_log, JSON_PRETTY_PRINT));
}

// Format date range for display
function formatDateRange($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    
    if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
        return $start->format('M j, Y');
    } else {
        return $start->format('M j') . ' - ' . $end->format('M j, Y');
    }
}

// Get recurring days string
function getRecurringDaysString($days) {
    if (empty($days)) {
        return '';
    }
    
    $day_names = [
        'monday' => 'Mon',
        'tuesday' => 'Tue',
        'wednesday' => 'Wed',
        'thursday' => 'Thu',
        'friday' => 'Fri',
        'saturday' => 'Sat',
        'sunday' => 'Sun'
    ];
    
    $day_strings = [];
    foreach ($days as $day) {
        if (isset($day_names[$day])) {
            $day_strings[] = $day_names[$day];
        }
    }
    
    return implode(', ', $day_strings);
}

// Validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate password strength
function isValidPassword($password) {
    return strlen($password) >= 6;
}

// Get user's booking statistics
function getUserBookingStats($user_id) {
    $bookings = getAllBookings();
    $user_bookings = array_filter($bookings, function($booking) use ($user_id) {
        return $booking['user_id'] == $user_id;
    });
    
    $stats = [
        'total' => count($user_bookings),
        'confirmed' => 0,
        'pending' => 0,
        'cancelled' => 0
    ];
    
    foreach ($user_bookings as $booking) {
        if (isset($stats[$booking['status']])) {
            $stats[$booking['status']]++;
        }
    }
    
    return $stats;
}

// Get system statistics for admin dashboard
function getSystemStats() {
    $users = getAllUsers();
    $classes = getAllClasses();
    $trainers = getAllTrainers();
    $bookings = getAllBookings();
    
    $total_revenue = 0;
    foreach ($bookings as $booking) {
        if ($booking['status'] === 'confirmed') {
            $class = findClassById($classes, $booking['class_id']);
            if ($class) {
                $total_revenue += $class['price'];
            }
        }
    }
    
    return [
        'total_users' => count($users),
        'total_classes' => count($classes),
        'total_trainers' => count($trainers),
        'total_bookings' => count($bookings),
        'total_revenue' => $total_revenue,
        'confirmed_bookings' => count(array_filter($bookings, function($b) { return $b['status'] === 'confirmed'; })),
        'pending_bookings' => count(array_filter($bookings, function($b) { return $b['status'] === 'pending'; })),
        'cancelled_bookings' => count(array_filter($bookings, function($b) { return $b['status'] === 'cancelled'; }))
    ];
}

// Validate booking dates
function validateBookingDates($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $today = new DateTime('today');
    $max_date = new DateTime('+30 days');
    
    if ($start < $today) {
        return ['valid' => false, 'message' => 'Start date cannot be in the past'];
    }
    
    if ($end < $start) {
        return ['valid' => false, 'message' => 'End date must be after start date'];
    }
    
    if ($end > $max_date) {
        return ['valid' => false, 'message' => 'Cannot book more than 30 days in advance'];
    }
    
    return ['valid' => true, 'message' => ''];
}
?>
