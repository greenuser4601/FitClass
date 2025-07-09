<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'book') {
        $class_id = (int)($_POST['class_id'] ?? 0);
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $recurring_days = $_POST['recurring_days'] ?? [];

        // Validation
        if ($class_id <= 0 || empty($start_date) || empty($end_date) || empty($recurring_days)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        // Validate dates
        $date_validation = validateBookingDates($start_date, $end_date);
        if (!$date_validation['valid']) {
            echo json_encode(['success' => false, 'message' => $date_validation['message']]);
            exit();
        }

        // Check if class exists
        $classes = getAllClasses();
        $class = findClassById($classes, $class_id);
        if (!$class) {
            echo json_encode(['success' => false, 'message' => 'Selected class not found']);
            exit();
        }

        // Generate booking dates based on recurring days
        $booking_dates = [];
        $current_date = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);

        $day_mapping = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 0
        ];

        while ($current_date <= $end_date_obj) {
            $day_of_week = (int)$current_date->format('w'); // 0 = Sunday, 1 = Monday, etc.

            foreach ($recurring_days as $recurring_day) {
                if (isset($day_mapping[$recurring_day]) && $day_mapping[$recurring_day] === $day_of_week) {
                    $booking_dates[] = $current_date->format('Y-m-d');
                    break;
                }
            }

            $current_date->add(new DateInterval('P1D'));
        }

        if (empty($booking_dates)) {
            echo json_encode(['success' => false, 'message' => 'No booking dates match the selected recurring days']);
            exit();
        }

        // Check for existing bookings and capacity
        $conflicting_dates = [];
        $full_dates = [];

        foreach ($booking_dates as $booking_date) {
            // Check for existing user booking
            if (hasBookingConflict($_SESSION['user_id'], $class_id, $booking_date)) {
                $conflicting_dates[] = $booking_date;
            }

            // Check class capacity
            if (isClassFull($class_id, $booking_date)) {
                $full_dates[] = $booking_date;
            }
        }

        if (!empty($conflicting_dates)) {
            echo json_encode(['success' => false, 'message' => 'You already have bookings for this class on: ' . implode(', ', $conflicting_dates)]);
            exit();
        }

        if (!empty($full_dates)) {
            echo json_encode(['success' => false, 'message' => 'Class is full on: ' . implode(', ', $full_dates)]);
            exit();
        }

        // Create bookings for all dates
        $booking_count = 0;
        $recurring_group = date('Y-m-d-H-i-s') . '-' . $_SESSION['user_id'];

        foreach ($booking_dates as $booking_date) {
            $new_booking = [
                'user_id' => $_SESSION['user_id'],
                'class_id' => $class_id,
                'booking_date' => $booking_date,
                'status' => 'pending',
                'recurring_group' => $recurring_group
            ];

            if (addBooking($new_booking)) {
                $booking_count++;
            }
        }

        if ($booking_count > 0) {
            $message = $booking_count === 1 ? 
                'Class booked successfully!' : 
                "Successfully booked {$booking_count} classes from {$start_date} to {$end_date}!";

            logActivity('booking_created', $_SESSION['user_id'], [
                'class_id' => $class_id,
                'class_name' => $class['name'],
                'booking_count' => $booking_count,
                'date_range' => "{$start_date} to {$end_date}"
            ]);

            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save bookings']);
        }
    }

    if ($action === 'update_status' && $_SESSION['user_type'] === 'admin') {
        $booking_id = (int)($_POST['booking_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($booking_id <= 0 || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Invalid booking ID or status']);
            exit();
        }

        $valid_statuses = ['pending', 'confirmed', 'payment_required', 'paid', 'ongoing', 'completed', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status value']);
            exit();
        }

        // Get booking details to update entire group
        $all_bookings = getAllBookings();
        $booking = null;
        foreach ($all_bookings as $b) {
            if ($b['id'] == $booking_id) {
                $booking = $b;
                break;
            }
        }

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit();
        }

        // Special validation for completing ongoing bookings
        if ($status === 'completed' && $booking['status'] === 'ongoing') {
            // Get all bookings in the same recurring group to find the end date
            $group_bookings = array_filter($all_bookings, function($b) use ($booking) {
                return $b['recurring_group'] === $booking['recurring_group'];
            });
            
            // Find the latest booking date in the group
            $latest_date = '';
            foreach ($group_bookings as $group_booking) {
                if (empty($latest_date) || $group_booking['booking_date'] > $latest_date) {
                    $latest_date = $group_booking['booking_date'];
                }
            }
            
            // Check if today is at least the latest booking date
            $today = date('Y-m-d');
            if ($today < $latest_date) {
                echo json_encode(['success' => false, 'message' => 'Cannot complete booking before the scheduled end date (' . date('M j, Y', strtotime($latest_date)) . ')']);
                exit();
            }
        }

        // Update entire booking group status
        if (updateBookingGroupStatus($booking['recurring_group'], $status)) {
            logActivity('booking_status_updated', $_SESSION['user_id'], [
                'booking_id' => $booking_id,
                'recurring_group' => $booking['recurring_group'],
                'new_status' => $status
            ]);
            echo json_encode(['success' => true, 'message' => 'Booking status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update booking status']);
        }
    }

    if ($action === 'approve' && $_SESSION['user_type'] === 'admin') {
        $booking_id = (int)($_POST['booking_id'] ?? 0);
        
        if ($booking_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
            exit();
        }

        // Get booking details
        $all_bookings = getAllBookings();
        $booking = null;
        foreach ($all_bookings as $b) {
            if ($b['id'] == $booking_id) {
                $booking = $b;
                break;
            }
        }

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit();
        }

        // Update status to payment_required
        if (updateBookingGroupStatus($booking['recurring_group'], 'payment_required')) {
            logActivity('booking_approved', $_SESSION['user_id'], [
                'booking_id' => $booking_id,
                'recurring_group' => $booking['recurring_group']
            ]);
            echo json_encode(['success' => true, 'message' => 'Booking approved! User can now proceed with payment.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to approve booking']);
        }
    }

    if ($action === 'decline' && $_SESSION['user_type'] === 'admin') {
        $booking_id = (int)($_POST['booking_id'] ?? 0);
        
        if ($booking_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
            exit();
        }

        // Get booking details
        $all_bookings = getAllBookings();
        $booking = null;
        foreach ($all_bookings as $b) {
            if ($b['id'] == $booking_id) {
                $booking = $b;
                break;
            }
        }

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit();
        }

        // Delete booking group instead of cancelling
        if (deleteBookingGroup($booking['recurring_group'])) {
            logActivity('booking_declined', $_SESSION['user_id'], [
                'booking_id' => $booking_id,
                'recurring_group' => $booking['recurring_group']
            ]);
            echo json_encode(['success' => true, 'message' => 'Booking declined and deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to decline booking']);
        }
    }

    if ($action === 'pay') {
        $recurring_group = $_POST['recurring_group'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';
        $amount = (float)($_POST['amount'] ?? 0);

        if (empty($recurring_group) || empty($payment_method) || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Missing payment information']);
            exit();
        }

        // Check if user owns this booking group
        $all_bookings = getAllBookings();
        $user_booking = null;
        foreach ($all_bookings as $booking) {
            if ($booking['recurring_group'] === $recurring_group && $booking['user_id'] == $_SESSION['user_id']) {
                $user_booking = $booking;
                break;
            }
        }

        if (!$user_booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found or access denied']);
            exit();
        }

        // Check if payment already exists
        if (hasPaymentForBookingGroup($recurring_group)) {
            echo json_encode(['success' => false, 'message' => 'Payment already processed for this booking']);
            exit();
        }

        // Create payment record
        $payment_data = [
            'user_id' => $_SESSION['user_id'],
            'recurring_group' => $recurring_group,
            'amount' => $amount,
            'payment_method' => $payment_method,
            'status' => 'completed',
            'transaction_id' => 'TXN-' . date('YmdHis') . '-' . rand(1000, 9999)
        ];

        if (addPayment($payment_data)) {
            // Update booking status to paid
            if (updateBookingGroupStatus($recurring_group, 'paid')) {
                logActivity('payment_completed', $_SESSION['user_id'], [
                    'recurring_group' => $recurring_group,
                    'amount' => $amount,
                    'payment_method' => $payment_method
                ]);
                echo json_encode(['success' => true, 'message' => 'Payment processed successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Payment processed but failed to update booking status']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to process payment']);
        }
    }

    if ($action === 'cancel_group') {
        $recurring_group = $_POST['recurring_group'] ?? '';

        if (empty($recurring_group)) {
            echo json_encode(['success' => false, 'message' => 'Invalid booking group']);
            exit();
        }

        // Check if user owns this booking group
        $all_bookings = getAllBookings();
        $user_booking = null;
        foreach ($all_bookings as $booking) {
            if ($booking['recurring_group'] === $recurring_group && $booking['user_id'] == $_SESSION['user_id']) {
                $user_booking = $booking;
                break;
            }
        }

        if (!$user_booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found or access denied']);
            exit();
        }

        // Only allow cancellation if status is pending
        if ($user_booking['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => 'Can only cancel pending bookings']);
            exit();
        }

        // Delete entire booking group instead of cancelling
        if (deleteBookingGroup($recurring_group)) {
            logActivity('booking_deleted_by_user', $_SESSION['user_id'], [
                'recurring_group' => $recurring_group
            ]);
            echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete booking']);
        }
    }

    if ($action === 'cancel' && isset($_POST['booking_id'])) {
        $booking_id = (int)$_POST['booking_id'];

        // Get booking details
        $all_bookings = getAllBookings();
        $booking = null;
        foreach ($all_bookings as $b) {
            if ($b['id'] == $booking_id) {
                $booking = $b;
                break;
            }
        }

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit();
        }

        // Check if user owns this booking or is admin
        if ($booking['user_id'] != $_SESSION['user_id'] && $_SESSION['user_type'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'You can only cancel your own bookings']);
            exit();
        }

        if (updateBooking($booking_id, ['status' => 'cancelled'])) {
            logActivity('booking_cancelled', $_SESSION['user_id'], ['booking_id' => $booking_id]);
            echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to cancel booking']);
        }
    }

    // Bulk operations (admin only)
    if (strpos($action, 'bulk_') === 0 && $_SESSION['user_type'] === 'admin') {
        $bulk_action = substr($action, 5); // Remove 'bulk_' prefix
        $booking_ids_json = $_POST['booking_ids'] ?? '';

        if (empty($booking_ids_json)) {
            echo json_encode(['success' => false, 'message' => 'No booking IDs provided']);
            exit();
        }

        $booking_ids = json_decode($booking_ids_json, true);
        if (!is_array($booking_ids) || empty($booking_ids)) {
            echo json_encode(['success' => false, 'message' => 'Invalid booking IDs']);
            exit();
        }

        $success_count = 0;
        $total_count = count($booking_ids);

        foreach ($booking_ids as $booking_id) {
            $booking_id = (int)$booking_id;

            if ($bulk_action === 'delete') {
                if (deleteBooking($booking_id)) {
                    $success_count++;
                }
            }
        }

        if ($success_count > 0) {
            $action_text = $bulk_action === 'delete' ? 'deleted' : ($bulk_action . 'd');
            $message = "Successfully {$action_text} {$success_count} out of {$total_count} booking(s)";

            logActivity('bulk_booking_action', $_SESSION['user_id'], [
                'action' => $bulk_action,
                'booking_count' => $success_count,
                'total_count' => $total_count
            ]);

            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to process any bookings']);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['export']) && $_GET['export'] === 'csv' && $_SESSION['user_type'] === 'admin') {
        // Export bookings as CSV
        $bookings = getAllBookings();
        $users = getAllUsers();
        $classes = getAllClasses();

        $csv_content = "ID,User,Email,Class,Booking Date,Booking Time,Status,Recurring Group\n";

        foreach ($bookings as $booking) {
            $user = findUserById($users, $booking['user_id']);
            $class = findClassById($classes, $booking['class_id']);

            $csv_content .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",%s,%s,%s,\"%s\"\n",
                $booking['id'],
                $user ? $user['name'] : 'Unknown',
                $user ? $user['email'] : 'Unknown',
                $class ? $class['name'] : 'Unknown',
                $booking['booking_date'],
                $booking['booking_time'] ?? '',
                $booking['status'],
                $booking['recurring_group'] ?? ''
            );
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bookings-export-' . date('Y-m-d') . '.csv"');
        echo $csv_content;
        exit();
    }

    // Return bookings data
    if ($_SESSION['user_type'] === 'admin') {
        $bookings = getAllBookings();
    } else {
        $bookings = getUserBookings($_SESSION['user_id']);
    }

    echo json_encode(['success' => true, 'bookings' => $bookings]);
}
?>