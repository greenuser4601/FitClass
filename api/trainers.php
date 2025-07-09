<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

$trainers = getAllTrainers();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['success' => true, 'trainers' => $trainers]);
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
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $specialization = sanitize_input($_POST['specialization'] ?? '');
        $experience = (int)($_POST['experience'] ?? 0);
        $bio = sanitize_input($_POST['bio'] ?? '');
        
        // Validation
        if (empty($name) || empty($email) || empty($phone) || empty($specialization) || $experience < 0 || empty($bio)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            exit();
        }
        
        if (!isValidEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
            exit();
        }
        
        // Check for duplicate email
        $existing_trainer = findTrainerByEmail($trainers, $email);
        if ($existing_trainer) {
            echo json_encode(['success' => false, 'message' => 'A trainer with this email already exists']);
            exit();
        }
        
        $new_trainer = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'specialization' => $specialization,
            'experience' => $experience,
            'bio' => $bio
        ];
        
        if (addTrainer($new_trainer)) {
            logActivity('trainer_added', $_SESSION['user_id'], ['trainer_name' => $name]);
            echo json_encode(['success' => true, 'message' => 'Trainer added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save trainer']);
        }
    }
    
    if ($action === 'delete') {
        $trainer_id = (int)($_POST['trainer_id'] ?? 0);
        
        if ($trainer_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid trainer ID']);
            exit();
        }
        
        // Check if trainer is assigned to any classes
        $classes = getAllClasses();
        $assigned_classes = array_filter($classes, function($class) use ($trainer_id) {
            return $class['trainer_id'] == $trainer_id;
        });
        
        if (!empty($assigned_classes)) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete trainer. They are assigned to ' . count($assigned_classes) . ' class(es). Please reassign or delete those classes first.']);
            exit();
        }
        
        // Find trainer name for logging
        $trainer = findTrainerById($trainers, $trainer_id);
        $trainer_name = $trainer ? $trainer['name'] : 'Unknown';
        
        if (deleteTrainer($trainer_id)) {
            logActivity('trainer_deleted', $_SESSION['user_id'], ['trainer_name' => $trainer_name]);
            echo json_encode(['success' => true, 'message' => 'Trainer deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete trainer']);
        }
    }
    
    if ($action === 'update') {
        $trainer_id = (int)($_POST['trainer_id'] ?? 0);
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $specialization = sanitize_input($_POST['specialization'] ?? '');
        $experience = (int)($_POST['experience'] ?? 0);
        $bio = sanitize_input($_POST['bio'] ?? '');
        
        // Validation
        if ($trainer_id <= 0 || empty($name) || empty($email) || empty($phone) || empty($specialization) || $experience < 0 || empty($bio)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            exit();
        }
        
        if (!isValidEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
            exit();
        }
        
        // Check for duplicate email (excluding current trainer)
        foreach ($trainers as $trainer) {
            if ($trainer['email'] === $email && $trainer['id'] != $trainer_id) {
                echo json_encode(['success' => false, 'message' => 'A trainer with this email already exists']);
                exit();
            }
        }
        
        $update_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'specialization' => $specialization,
            'experience' => $experience,
            'bio' => $bio
        ];
        
        if (updateTrainer($trainer_id, $update_data)) {
            logActivity('trainer_updated', $_SESSION['user_id'], ['trainer_name' => $name]);
            echo json_encode(['success' => true, 'message' => 'Trainer updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update trainer']);
        }
    }
}

// Helper function to find trainer by email
function findTrainerByEmail($trainers, $email) {
    foreach ($trainers as $trainer) {
        if ($trainer['email'] === $email) {
            return $trainer;
        }
    }
    return null;
}
?>
