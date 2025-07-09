<?php
session_start();
require_once '../includes/config.php';
require_admin();

$bookings = getAllBookings();
$users = getAllUsers();
$classes = getAllClasses();
$trainers = getAllTrainers();

// Sort bookings by date (newest first)
usort($bookings, function($a, $b) {
    return strcmp($b['booking_date'], $a['booking_date']);
});

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-dumbbell me-2"></i><?php echo APP_NAME; ?> Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-chart-bar me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="classes.php">
                            <i class="fas fa-dumbbell me-1"></i>Classes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="trainers.php">
                            <i class="fas fa-users me-1"></i>Trainers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="bookings.php">
                            <i class="fas fa-calendar-alt me-1"></i>Bookings
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../dashboard.php">
                                <i class="fas fa-user me-2"></i>User View
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="logout()">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-2">Manage Bookings</h1>
                        <p class="text-muted">View and manage all class bookings</p>
                    </div>
                    <div>
                        <button class="btn btn-success" onclick="exportBookings()">
                            <i class="fas fa-download me-2"></i>Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <?php
            $total_bookings = count($bookings);
            $confirmed_bookings = count(array_filter($bookings, function($b) { return $b['status'] === 'confirmed'; }));
            $pending_bookings = count(array_filter($bookings, function($b) { return $b['status'] === 'pending'; }));
            $cancelled_bookings = count(array_filter($bookings, function($b) { return $b['status'] === 'cancelled'; }));
            ?>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-calendar-alt fa-2x text-primary mb-3"></i>
                        <h3 class="fw-bold"><?php echo $total_bookings; ?></h3>
                        <p class="text-muted mb-0">Total Bookings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                        <h3 class="fw-bold text-success"><?php echo $confirmed_bookings; ?></h3>
                        <p class="text-muted mb-0">Confirmed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-clock fa-2x text-warning mb-3"></i>
                        <h3 class="fw-bold text-warning"><?php echo $pending_bookings; ?></h3>
                        <p class="text-muted mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-times-circle fa-2x text-danger mb-3"></i>
                        <h3 class="fw-bold text-danger"><?php echo $cancelled_bookings; ?></h3>
                        <p class="text-muted mb-0">Cancelled</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>All Bookings
                        </h5>
                        <div id="bulkActionsPanel" class="d-none">
                            <div class="input-group">
                                <select class="form-select" id="bulkActionSelect">
                                    <option value="">Select Action</option>
                                    <option value="delete">Delete Selected</option>
                                </select>
                                <button class="btn btn-primary" onclick="performBulkAction()">
                                    <i class="fas fa-play me-1"></i>Apply
                                </button>
                                <button class="btn btn-secondary" onclick="clearSelection()">
                                    <i class="fas fa-times me-1"></i>Clear
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bookings)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5>No Bookings Found</h5>
                                <p class="text-muted">No bookings have been made yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                                </div>
                                            </th>
                                            <th>ID</th>
                                            <th>User</th>
                                            <th>Class</th>
                                            <th>Trainer</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Recurring Group</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <?php
                                            $user = findUserById($users, $booking['user_id']);
                                            $class = findClassById($classes, $booking['class_id']);
                                            $trainer = $class ? findTrainerById($trainers, $class['trainer_id']) : null;
                                            ?>
                                            <tr class="align-middle">
                                                <td class="text-center">
                                                    <div class="form-check d-flex justify-content-center mb-0">
                                                        <input class="form-check-input booking-checkbox item-checkbox" type="checkbox" value="<?php echo $booking['id']; ?>" onchange="toggleBulkActions()">
                                                    </div>
                                                </td>
                                                <td class="fw-bold text-center">#<?php echo $booking['id']; ?></td>
                                                <td>
                                                    <?php if ($user): ?>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-semibold mb-1"><?php echo htmlspecialchars($user['name']); ?></span>
                                                            <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">Unknown User</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($class): ?>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-semibold mb-1"><?php echo htmlspecialchars($class['name']); ?></span>
                                                            <small class="text-success fw-medium">₱<?php echo number_format($class['price'], 2); ?></small>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">Unknown Class</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($trainer): ?>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-semibold mb-1"><?php echo htmlspecialchars($trainer['name']); ?></span>
                                                            <small class="text-muted"><?php echo htmlspecialchars($trainer['specialization']); ?></small>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">No Trainer</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold mb-1"><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></span>
                                                        <small class="text-muted"><?php echo date('l', strtotime($booking['booking_date'])); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($class): ?>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-semibold mb-1"><?php echo date('g:i A', strtotime($class['time'])); ?></span>
                                                            <small class="text-muted"><?php echo $class['duration']; ?> mins</small>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $status_colors = [
                                                        'pending' => 'warning',
                                                        'confirmed' => 'info',
                                                        'payment_required' => 'primary',
                                                        'paid' => 'success',
                                                        'ongoing' => 'info',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $color = $status_colors[$booking['status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?> px-3 py-2 rounded-pill">
                                                        <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (isset($booking['recurring_group'])): ?>
                                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded">
                                                            #<?php echo substr($booking['recurring_group'], -8); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded">
                                                            Single
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-cog me-1"></i>Actions
                                                        </button>
                                                        <ul class="dropdown-menu shadow-sm border-0">
                                                            <?php if ($booking['status'] === 'pending'): ?>
                                                                <li><a class="dropdown-item d-flex align-items-center py-2 text-success" href="#" onclick="approveBooking(<?php echo $booking['id']; ?>)">
                                                                    <i class="fas fa-check me-3"></i>Approve
                                                                </a></li>
                                                                <li><hr class="dropdown-divider my-1"></li>
                                                                <li><a class="dropdown-item d-flex align-items-center py-2 text-danger" href="#" onclick="declineBooking(<?php echo $booking['id']; ?>)">
                                                                    <i class="fas fa-times me-3"></i>Decline
                                                                </a></li>
                                                            <?php endif; ?>
                                                            <?php if ($booking['status'] === 'paid'): ?>
                                                                <li><a class="dropdown-item d-flex align-items-center py-2 text-info" href="#" onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'ongoing')">
                                                                    <i class="fas fa-play me-3"></i>Start Class
                                                                </a></li>
                                                            <?php endif; ?>
                                                            <?php if ($booking['status'] === 'ongoing'): ?>
                                                                <li><a class="dropdown-item d-flex align-items-center py-2 text-success" href="#" onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'completed')">
                                                                    <i class="fas fa-flag-checkered me-3"></i>Complete
                                                                </a></li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>

    <script>
        // The bulk action functionality is now handled by the existing functions below

        // Update booking status
        function updateBookingStatus(bookingId, status) {
            if (!confirm(`Are you sure you want to ${status} this booking?`)) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('booking_id', bookingId);
            formData.append('status', status);
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');

            fetch('../api/bookings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        }

        // Export bookings
        function exportBookings() {
            showNotification('Preparing export...', 'info');
            window.location.href = '../api/bookings.php?export=csv';
        }

        // Bulk operations functions
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.booking-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });

            toggleBulkActions();
        }

        function toggleBulkActions() {
            const checkedBoxes = document.querySelectorAll('.booking-checkbox:checked');
            const bulkPanel = document.getElementById('bulkActionsPanel');

            if (checkedBoxes.length > 0) {
                bulkPanel.classList.remove('d-none');
            } else {
                bulkPanel.classList.add('d-none');
                document.getElementById('selectAll').checked = false;
            }
        }

        function clearSelection() {
            document.getElementById('selectAll').checked = false;
            document.querySelectorAll('.booking-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            toggleBulkActions();
        }

        function performBulkAction() {
            const action = document.getElementById('bulkActionSelect').value;
            const checkedBoxes = document.querySelectorAll('.booking-checkbox:checked');

            if (!action) {
                showNotification('Please select an action', 'warning');
                return;
            }

            if (checkedBoxes.length === 0) {
                showNotification('Please select at least one booking', 'warning');
                return;
            }

            const bookingIds = Array.from(checkedBoxes).map(cb => cb.value);
            const actionText = action === 'delete' ? 'permanently delete' : action;

            if (!confirm(`Are you sure you want to ${actionText} ${bookingIds.length} booking(s)?`)) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'bulk_' + action);
            formData.append('booking_ids', JSON.stringify(bookingIds));
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');

            fetch('../api/bookings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        }

        function approveBooking(bookingId) {
            if (!confirm('Are you sure you want to approve this booking? The user will be able to proceed with payment.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'approve');
            formData.append('booking_id', bookingId);
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');

            fetch('../api/bookings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        }

        function declineBooking(bookingId) {
            if (!confirm('Are you sure you want to decline this booking? This action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'decline');
            formData.append('booking_id', bookingId);
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');

            fetch('../api/bookings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                const formData = new FormData();
                formData.append('action', 'logout');

                fetch('../api/auth.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '../index.php';
                    }
                })
                .catch(error => {
                    window.location.href = '../index.php';
                });
            }
        }
    </script>
</body>
</html>
```

The code has been modified to improve the table layout and spacing, enhancing the visual design.