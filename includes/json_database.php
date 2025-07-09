<?php
// Enhanced JSON database functions for FitClass

// Enhanced cache system for JSON files
$json_cache = [];
$cache_timestamps = [];

function clearJsonCache() {
    global $json_cache, $cache_timestamps;
    $json_cache = [];
    $cache_timestamps = [];
}

// Check if cache is valid (file hasn't been modified)
function isCacheValid($file) {
    global $cache_timestamps;
    if (!isset($cache_timestamps[$file])) {
        return false;
    }
    return $cache_timestamps[$file] >= filemtime($file);
}

function read_json_file($file) {
    global $json_cache, $cache_timestamps;

    // Check cache first and validate timestamp
    if (isset($json_cache[$file]) && isCacheValid($file)) {
        return $json_cache[$file];
    }

    if (!file_exists($file)) {
        error_log("JSON file not found: $file");
        return null;
    }

    $content = file_get_contents($file);
    if ($content === false) {
        error_log("Failed to read JSON file: $file");
        return null;
    }

    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error in file $file: " . json_last_error_msg());
        return null;
    }

    // Cache the result with timestamp
    $json_cache[$file] = $data;
    $cache_timestamps[$file] = filemtime($file);
    return $data;
}



// Enhanced data access functions with caching
function getAllUsers() {
    global $json_cache;
    if (!isset($json_cache['users'])) {
        $data = json_decode(file_get_contents(USERS_FILE), true);
        $json_cache['users'] = $data['users'] ?? [];
    }
    return $json_cache['users'];
}

function getAllClasses() {
    global $json_cache;
    if (!isset($json_cache['classes'])) {
        $data = json_decode(file_get_contents(CLASSES_FILE), true);
        $json_cache['classes'] = $data['classes'] ?? [];
    }
    return $json_cache['classes'];
}

function getAllTrainers() {
    global $json_cache;
    if (!isset($json_cache['trainers'])) {
        $data = json_decode(file_get_contents(TRAINERS_FILE), true);
        $json_cache['trainers'] = $data['trainers'] ?? [];
    }
    return $json_cache['trainers'];
}

function getAllBookings() {
    global $json_cache;
    if (!isset($json_cache['bookings'])) {
        $data = json_decode(file_get_contents(BOOKINGS_FILE), true);
        $json_cache['bookings'] = $data['bookings'] ?? [];
    }
    return $json_cache['bookings'];
}

// User management functions
function addUser($user_data) {
    $users_data = json_decode(file_get_contents(USERS_FILE), true);

    // Generate unique ID
    $user_data['id'] = generateUniqueId($users_data['users']);
    $user_data['created_at'] = date('Y-m-d H:i:s');

    $users_data['users'][] = $user_data;

    // Clear cache after write
    clearJsonCache();
    return write_json_file(USERS_FILE, $users_data);
}

function updateUser($user_id, $update_data) {
    $users_data = json_decode(file_get_contents(USERS_FILE), true);

    foreach ($users_data['users'] as &$user) {
        if ($user['id'] == $user_id) {
            foreach ($update_data as $key => $value) {
                $user[$key] = $value;
            }
            break;
        }
    }

    return write_json_file(USERS_FILE, $users_data);
}

function deleteUser($user_id) {
    $users_data = json_decode(file_get_contents(USERS_FILE), true);

    $users_data['users'] = array_filter($users_data['users'], function($user) use ($user_id) {
        return $user['id'] != $user_id;
    });
    $users_data['users'] = array_values($users_data['users']);

    return write_json_file(USERS_FILE, $users_data);
}

// Booking management functions
function addBooking($booking_data) {
    $bookings_data = json_decode(file_get_contents(BOOKINGS_FILE), true);

    // Generate unique ID and timestamps
    $booking_data['id'] = generateUniqueId($bookings_data['bookings']);
    $booking_data['created_at'] = date('Y-m-d H:i:s');
    $booking_data['booking_time'] = date('H:i:s');

    $bookings_data['bookings'][] = $booking_data;

    // Clear cache after write
    clearJsonCache();
    return write_json_file(BOOKINGS_FILE, $bookings_data);
}

function updateBooking($booking_id, $update_data) {
    $bookings_data = json_decode(file_get_contents(BOOKINGS_FILE), true);

    foreach ($bookings_data['bookings'] as &$booking) {
        if ($booking['id'] == $booking_id) {
            foreach ($update_data as $key => $value) {
                $booking[$key] = $value;
            }
            break;
        }
    }

    return write_json_file(BOOKINGS_FILE, $bookings_data);
}

function deleteBooking($booking_id) {
    $bookings_data = json_decode(file_get_contents(BOOKINGS_FILE), true);

    $bookings_data['bookings'] = array_filter($bookings_data['bookings'], function($booking) use ($booking_id) {
        return $booking['id'] != $booking_id;
    });
    $bookings_data['bookings'] = array_values($bookings_data['bookings']);

    // Clear cache after delete
    clearJsonCache();
    return write_json_file(BOOKINGS_FILE, $bookings_data);
}

function deleteBookingGroup($recurring_group) {
    $bookings_data = json_decode(file_get_contents(BOOKINGS_FILE), true);

    $bookings_data['bookings'] = array_filter($bookings_data['bookings'], function($booking) use ($recurring_group) {
        return $booking['recurring_group'] !== $recurring_group;
    });
    $bookings_data['bookings'] = array_values($bookings_data['bookings']);

    // Clear cache after delete
    clearJsonCache();
    return write_json_file(BOOKINGS_FILE, $bookings_data);
}

// Enhanced function to get user bookings with proper grouping
function getUserBookingsGrouped($user_id) {
    $all_bookings = getAllBookings();
    $user_bookings = array_filter($all_bookings, function($booking) use ($user_id) {
        return $booking['user_id'] == $user_id;
    });

    // Group bookings by recurring_group
    $grouped_bookings = [];
    foreach ($user_bookings as $booking) {
        $group_key = $booking['recurring_group'] ?? 'single_' . $booking['id'];

        if (!isset($grouped_bookings[$group_key])) {
            $grouped_bookings[$group_key] = [
                'recurring_group' => $group_key,
                'class_id' => $booking['class_id'],
                'user_id' => $booking['user_id'],
                'status' => $booking['status'],
                'bookings' => [],
                'session_count' => 0,
                'start_date' => $booking['booking_date'],
                'end_date' => $booking['booking_date']
            ];
        }

        $grouped_bookings[$group_key]['bookings'][] = $booking;
        $grouped_bookings[$group_key]['session_count']++;

        // Update date range
        if ($booking['booking_date'] < $grouped_bookings[$group_key]['start_date']) {
            $grouped_bookings[$group_key]['start_date'] = $booking['booking_date'];
        }
        if ($booking['booking_date'] > $grouped_bookings[$group_key]['end_date']) {
            $grouped_bookings[$group_key]['end_date'] = $booking['booking_date'];
        }
    }

    // Format the grouped bookings for display
    $formatted_bookings = [];
    foreach ($grouped_bookings as $group) {
        // Get recurring days from the booking dates
        $days_of_week = [];
        foreach ($group['bookings'] as $booking) {
            $day_of_week = date('l', strtotime($booking['booking_date']));
            if (!in_array($day_of_week, $days_of_week)) {
                $days_of_week[] = $day_of_week;
            }
        }

        $formatted_booking = [
            'recurring_group' => $group['recurring_group'],
            'class_id' => $group['class_id'],
            'user_id' => $group['user_id'],
            'status' => $group['status'],
            'session_count' => $group['session_count'],
            'date_range' => formatDateRange($group['start_date'], $group['end_date']),
            'recurring_days' => getRecurringDaysString(array_map('strtolower', $days_of_week)),
            'start_date' => $group['start_date'],
            'end_date' => $group['end_date']
        ];

        $formatted_bookings[] = $formatted_booking;
    }

    // Sort by start date (most recent first)
    usort($formatted_bookings, function($a, $b) {
        return strcmp($b['start_date'], $a['start_date']);
    });

    return $formatted_bookings;
}

// Get user's individual bookings (not grouped)
function getUserBookings($user_id) {
    $all_bookings = getAllBookings();
    return array_filter($all_bookings, function($booking) use ($user_id) {
        return $booking['user_id'] == $user_id;
    });
}

// Class management functions
function addClass($class_data) {
    $classes_data = json_decode(file_get_contents(CLASSES_FILE), true);

    $class_data['id'] = generateUniqueId($classes_data['classes']);
    $classes_data['classes'][] = $class_data;

    return write_json_file(CLASSES_FILE, $classes_data);
}

function updateClass($class_id, $update_data) {
    $classes_data = json_decode(file_get_contents(CLASSES_FILE), true);

    foreach ($classes_data['classes'] as &$class) {
        if ($class['id'] == $class_id) {
            foreach ($update_data as $key => $value) {
                $class[$key] = $value;
            }
            break;
        }
    }

    return write_json_file(CLASSES_FILE, $classes_data);
}

function deleteClass($class_id) {
    $classes_data = json_decode(file_get_contents(CLASSES_FILE), true);

    $classes_data['classes'] = array_filter($classes_data['classes'], function($class) use ($class_id) {
        return $class['id'] != $class_id;
    });
    $classes_data['classes'] = array_values($classes_data['classes']);

    return write_json_file(CLASSES_FILE, $classes_data);
}

// Trainer management functions
function addTrainer($trainer_data) {
    $trainers_data = json_decode(file_get_contents(TRAINERS_FILE), true);

    $trainer_data['id'] = generateUniqueId($trainers_data['trainers']);
    $trainers_data['trainers'][] = $trainer_data;

    return write_json_file(TRAINERS_FILE, $trainers_data);
}

function updateTrainer($trainer_id, $update_data) {
    $trainers_data = json_decode(file_get_contents(TRAINERS_FILE), true);

    foreach ($trainers_data['trainers'] as &$trainer) {
        if ($trainer['id'] == $trainer_id) {
            foreach ($update_data as $key => $value) {
                $trainer[$key] = $value;
            }
            break;
        }
    }

    return write_json_file(TRAINERS_FILE, $trainers_data);
}

function deleteTrainer($trainer_id) {
    $trainers_data = json_decode(file_get_contents(TRAINERS_FILE), true);

    $trainers_data['trainers'] = array_filter($trainers_data['trainers'], function($trainer) use ($trainer_id) {
        return $trainer['id'] != $trainer_id;
    });
    $trainers_data['trainers'] = array_values($trainers_data['trainers']);

    return write_json_file(TRAINERS_FILE, $trainers_data);
}

// Check for conflicts
function hasBookingConflict($user_id, $class_id, $booking_date, $exclude_booking_id = null) {
    $bookings = getAllBookings();

    foreach ($bookings as $booking) {
        if ($exclude_booking_id && $booking['id'] == $exclude_booking_id) {
            continue;
        }

        if ($booking['user_id'] == $user_id && 
            $booking['class_id'] == $class_id && 
            $booking['booking_date'] === $booking_date) {
            return true;
        }
    }

    return false;
}

// Get bookings for a specific class and date
function getClassBookings($class_id, $date) {
    $bookings = getAllBookings();
    return array_filter($bookings, function($booking) use ($class_id, $date) {
        return $booking['class_id'] == $class_id && $booking['booking_date'] === $date;
    });
}

// Check class capacity
function isClassFull($class_id, $date) {
    $classes = getAllClasses();
    $class = findClassById($classes, $class_id);

    if (!$class) {
        return true; // If class doesn't exist, consider it full
    }

    $bookings = getClassBookings($class_id, $date);
    $confirmed_bookings = array_filter($bookings, function($booking) {
        return in_array($booking['status'], ['confirmed', 'payment_required', 'paid', 'ongoing', 'completed']);
    });

    return count($confirmed_bookings) >= $class['capacity'];
}

// Payment management functions
function addPayment($payment_data) {
    $payments_data = json_decode(file_get_contents(PAYMENTS_FILE), true);
    if (!$payments_data) {
        $payments_data = ['payments' => []];
    }

    $payment_data['id'] = generateUniqueId($payments_data['payments']);
    $payment_data['created_at'] = date('Y-m-d H:i:s');
    $payment_data['payment_date'] = date('Y-m-d H:i:s');

    $payments_data['payments'][] = $payment_data;

    clearJsonCache();
    return write_json_file(PAYMENTS_FILE, $payments_data);
}

function getPaymentsByBookingGroup($recurring_group) {
    $payments_data = json_decode(file_get_contents(PAYMENTS_FILE), true);
    $payments = $payments_data['payments'] ?? [];

    return array_filter($payments, function($payment) use ($recurring_group) {
        return $payment['recurring_group'] === $recurring_group;
    });
}

function hasPaymentForBookingGroup($recurring_group) {
    $payments = getPaymentsByBookingGroup($recurring_group);
    return count($payments) > 0;
}

// Update booking status for entire recurring group
function updateBookingGroupStatus($recurring_group, $status) {
    $bookings_data = json_decode(file_get_contents(BOOKINGS_FILE), true);
    $updated = false;

    foreach ($bookings_data['bookings'] as &$booking) {
        if ($booking['recurring_group'] === $recurring_group) {
            $booking['status'] = $status;
            $updated = true;
        }
    }

    if ($updated) {
        clearJsonCache();
        return write_json_file(BOOKINGS_FILE, $bookings_data);
    }

    return false;
}

// JSON Database utilities
function exportJsonData() {
    return [
        'users' => getAllUsers(),
        'classes' => getAllClasses(),
        'trainers' => getAllTrainers(),
        'bookings' => getAllBookings(),
        'exported_at' => date('Y-m-d H:i:s'),
        'version' => '1.0'
    ];
}

function getJsonDatabaseStats() {
    return [
        'users_count' => count(getAllUsers()),
        'classes_count' => count(getAllClasses()),
        'trainers_count' => count(getAllTrainers()),
        'bookings_count' => count(getAllBookings()),
        'storage_size' => [
            'users' => filesize(USERS_FILE),
            'classes' => filesize(CLASSES_FILE),
            'trainers' => filesize(TRAINERS_FILE),
            'bookings' => filesize(BOOKINGS_FILE),
            'activity_log' => filesize(ACTIVITY_LOG_FILE)
        ],
        'last_modified' => [
            'users' => date('Y-m-d H:i:s', filemtime(USERS_FILE)),
            'classes' => date('Y-m-d H:i:s', filemtime(CLASSES_FILE)),
            'trainers' => date('Y-m-d H:i:s', filemtime(TRAINERS_FILE)),
            'bookings' => date('Y-m-d H:i:s', filemtime(BOOKINGS_FILE))
        ]
    ];
}
?>