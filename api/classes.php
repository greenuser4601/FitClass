<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

// Load data
$classes = getAllClasses();
$trainers = getAllTrainers();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Return all classes with trainer info
    $classes_with_trainers = array_map(function($class) use ($trainers) {
        $trainer = findTrainerById($trainers, $class['trainer_id']);
        $class['trainer'] = $trainer;
        return $class;
    }, $classes);

    echo json_encode(['success' => true, 'classes' => $classes_with_trainers]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $trainer_id = (int)($_POST['trainer_id'] ?? 0);
        $duration = (int)($_POST['duration'] ?? 0);
        $capacity = (int)($_POST['capacity'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $time = $_POST['time'] ?? '';

        // Validation
        if (empty($name) || empty($description) || $trainer_id <= 0 || $duration <= 0 || $capacity <= 0 || $price <= 0 || empty($time)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            exit();
        }

        // Validate trainer exists
        $trainer = findTrainerById($trainers, $trainer_id);
        if (!$trainer) {
            echo json_encode(['success' => false, 'message' => 'Selected trainer not found']);
            exit();
        }

        // Validate time format (accept both HH:MM and HH:MM:SS)
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time)) {
            echo json_encode(['success' => false, 'message' => 'Invalid time format. Use HH:MM']);
            exit();
        }

        // Add seconds if not provided (HTML time input only gives HH:MM)
        if (strlen($time) === 5) {
            $time .= ':00';
        }

        $new_class = [
            'name' => $name,
            'description' => $description,
            'trainer_id' => $trainer_id,
            'duration' => $duration,
            'capacity' => $capacity,
            'price' => $price,
            'time' => $time
        ];

        if (addClass($new_class)) {
            logActivity('class_added', $_SESSION['user_id'], ['class_name' => $name]);
            echo json_encode(['success' => true, 'message' => 'Class added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save class']);
        }
    }

    if ($action === 'delete') {
        $class_id = (int)($_POST['class_id'] ?? 0);

        if ($class_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid class ID']);
            exit();
        }

        // Check if class has bookings
        $bookings = getAllBookings();
        $class_bookings = array_filter($bookings, function($booking) use ($class_id) {
            return $booking['class_id'] == $class_id;
        });

        if (!empty($class_bookings)) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete class. There are ' . count($class_bookings) . ' booking(s) for this class.']);
            exit();
        }

        // Find class name for logging
        $class = findClassById($classes, $class_id);
        $class_name = $class ? $class['name'] : 'Unknown';

        if (deleteClass($class_id)) {
            logActivity('class_deleted', $_SESSION['user_id'], ['class_name' => $class_name]);
            echo json_encode(['success' => true, 'message' => 'Class deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete class']);
        }
    }

    if ($action === 'update') {
        $class_id = (int)($_POST['class_id'] ?? 0);
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $trainer_id = (int)($_POST['trainer_id'] ?? 0);
        $duration = (int)($_POST['duration'] ?? 0);
        $capacity = (int)($_POST['capacity'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $time = $_POST['time'] ?? '';

        // Validation
        if ($class_id <= 0 || empty($name) || empty($description) || $trainer_id <= 0 || $duration <= 0 || $capacity <= 0 || $price <= 0 || empty($time)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            exit();
        }

        // Validate trainer exists
        $trainer = findTrainerById($trainers, $trainer_id);
        if (!$trainer) {
            echo json_encode(['success' => false, 'message' => 'Selected trainer not found']);
            exit();
        }

        // Validate time format (accept both HH:MM and HH:MM:SS)
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time)) {
            echo json_encode(['success' => false, 'message' => 'Invalid time format. Use HH:MM']);
            exit();
        }

        // Add seconds if not provided (HTML time input only gives HH:MM)
        if (strlen($time) === 5) {
            $time .= ':00';
        }

        $update_data = [
            'name' => $name,
            'description' => $description,
            'trainer_id' => $trainer_id,
            'duration' => $duration,
            'capacity' => $capacity,
            'price' => $price,
            'time' => $time
        ];

        if (updateClass($class_id, $update_data)) {
            logActivity('class_updated', $_SESSION['user_id'], ['class_name' => $name]);
            echo json_encode(['success' => true, 'message' => 'Class updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update class']);
        }
    }
}
?>