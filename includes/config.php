<?php
// Configuration file for FitClass

// Session configuration (must be set before session_start)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
}

// Define file paths
define('DATA_DIR', __DIR__ . '/../data/');
define('USERS_FILE', DATA_DIR . 'users.json');
define('CLASSES_FILE', DATA_DIR . 'classes.json');
define('TRAINERS_FILE', DATA_DIR . 'trainers.json');
define('BOOKINGS_FILE', DATA_DIR . 'bookings.json');
define('PAYMENTS_FILE', DATA_DIR . '/payments.json');
define('ACTIVITY_LOG_FILE', DATA_DIR . 'activity_log.json');

// Create data directory if it doesn't exist
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Initialize JSON files if they don't exist
$default_files = [
    USERS_FILE => [
        'users' => [
            [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'yuriwise@gmail.com',
                'password' => 'admin123',
                'type' => 'admin',
                'created_at' => '2025-01-01 00:00:00'
            ],
            [
                'id' => 2,
                'name' => 'Arjun Calixtro',
                'email' => 'arjuncalixtro@gmail.com',
                'password' => 'password123',
                'type' => 'user',
                'created_at' => '2025-01-15 10:30:00'
            ]
        ]
    ],
    CLASSES_FILE => [
        'classes' => [
            [
                'id' => 1,
                'name' => 'High-Intensity Interval Training',
                'description' => 'A challenging workout combining cardio and strength training for maximum calorie burn.',
                'trainer_id' => 1,
                'duration' => 45,
                'capacity' => 15,
                'price' => 1200.00,
                'time' => '09:00:00'
            ],
            [
                'id' => 2,
                'name' => 'Yoga Flow',
                'description' => 'Gentle flowing movements to improve flexibility, balance, and inner peace.',
                'trainer_id' => 2,
                'duration' => 60,
                'capacity' => 20,
                'price' => 800.00,
                'time' => '07:00:00'
            ],
            [
                'id' => 3,
                'name' => 'CrossFit Training',
                'description' => 'Functional fitness movements performed at high intensity for overall strength.',
                'trainer_id' => 1,
                'duration' => 60,
                'capacity' => 12,
                'price' => 1500.00,
                'time' => '18:00:00'
            ]
        ]
    ],
    TRAINERS_FILE => [
        'trainers' => [
            [
                'id' => 1,
                'name' => 'Marcus Rodriguez',
                'email' => 'marcus@fitclass.com',
                'phone' => '+63 917 123 4567',
                'specialization' => 'HIIT & CrossFit',
                'experience' => 8,
                'bio' => 'Certified personal trainer specializing in high-intensity workouts.'
            ],
            [
                'id' => 2,
                'name' => 'Sofia Chen',
                'email' => 'sofia@fitclass.com',
                'phone' => '+63 917 234 5678',
                'specialization' => 'Yoga & Mindfulness',
                'experience' => 6,
                'bio' => 'Registered yoga instructor with expertise in Hatha, Vinyasa, and restorative yoga.'
            ]
        ]
    ],
    BOOKINGS_FILE => ['bookings' => []],
    ACTIVITY_LOG_FILE => []
];

foreach ($default_files as $file => $default_data) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($default_data, JSON_PRETTY_PRINT));
    }
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Manila');

// Application constants
define('APP_NAME', 'FitClass');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '/');

// Security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function require_admin() {
    if (!is_admin()) {
        header('Location: ../login.php');
        exit();
    }
}

// CSRF Protection
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Include enhanced JSON database management
require_once 'functions.php';
require_once 'json_database.php';
?>