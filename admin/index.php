<?php
session_start();
require_once '../includes/config.php';
require_admin();

// Get dashboard statistics
$users = getAllUsers();
$classes = getAllClasses();
$trainers = getAllTrainers();
$bookings = getAllBookings();

$total_users = count($users);
$total_classes = count($classes);
$total_trainers = count($trainers);
$total_bookings = count($bookings);

// Calculate revenue
$total_revenue = 0;
foreach ($bookings as $booking) {
    if ($booking['status'] === 'confirmed') {
        $class = findClassById($classes, $booking['class_id']);
        if ($class) {
            $total_revenue += $class['price'];
        }
    }
}

// Recent bookings (last 5)
$recent_bookings = array_slice(array_reverse($bookings), 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
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
                        <a class="nav-link active" href="index.php">
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
                        <a class="nav-link" href="bookings.php">
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
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <h1 class="card-title">Admin Dashboard</h1>
                        <p class="card-text mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Here's your fitness center overview.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card text-center h-100 clickable-card" data-stat="total_users" style="cursor: pointer;" onclick="showUsersModal()">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-primary mb-3"></i>
                        <h3 class="fw-bold text-primary"><?php echo $total_users; ?></h3>
                        <p class="text-muted mb-0">Total Users</p>
                        <small class="text-muted">Click to view details</small>
                    </div>
                </div>
            </div></div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card text-center h-100" data-stat="total_classes">
                    <div class="card-body">
                        <i class="fas fa-dumbbell fa-2x text-success mb-3"></i>
                        <h3 class="fw-bold text-success"><?php echo $total_classes; ?></h3>
                        <p class="text-muted mb-0">Active Classes</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card text-center h-100" data-stat="total_trainers">
                    <div class="card-body">
                        <i class="fas fa-user-tie fa-2x text-warning mb-3"></i>
                        <h3 class="fw-bold text-warning"><?php echo $total_trainers; ?></h3>
                        <p class="text-muted mb-0">Trainers</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card text-center h-100" data-stat="total_revenue">
                    <div class="card-body">
                        <i class="fas fa-peso-sign fa-2x text-info mb-3"></i>
                        <h3 class="fw-bold text-info">₱<?php echo number_format($total_revenue, 2); ?></h3>
                        <p class="text-muted mb-0">Total Revenue</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Bookings -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>Recent Bookings
                        </h5>
                        <a href="bookings.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>View All
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_bookings)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No recent bookings</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Class</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_bookings as $booking): ?>
                                            <?php
                                            $user = findUserById($users, $booking['user_id']);
                                            $class = findClassById($classes, $booking['class_id']);
                                            ?>
                                            <tr>
                                                <td><?php echo $user ? htmlspecialchars($user['name']) : 'Unknown'; ?></td>
                                                <td><?php echo $class ? htmlspecialchars($class['name']) : 'Unknown'; ?></td>
                                                <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
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

            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="classes.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add New Class
                            </a>
                            <a href="trainers.php" class="btn btn-success">
                                <i class="fas fa-user-plus me-2"></i>Add New Trainer
                            </a>
                            <a href="bookings.php" class="btn btn-info">
                                <i class="fas fa-calendar-check me-2"></i>Manage Bookings
                            </a>
                            <button class="btn btn-warning" onclick="exportBookings()">
                                <i class="fas fa-download me-2"></i>Export Data
                            </button>
                        </div>
                    </div>
                </div>

                <!-- System Info -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>System Info
                        </h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <div class="mb-2">
                                <strong>App Version:</strong> <?php echo APP_VERSION; ?>
                            </div>
                            <div class="mb-2">
                                <strong>Total Bookings:</strong> <?php echo $total_bookings; ?>
                            </div>
                            <div class="mb-2">
                                <strong>Last Updated:</strong> <?php echo date('M j, Y g:i A'); ?>
                            </div>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Modal -->
    <div class="modal fade" id="usersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-users me-2"></i>Users Overview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                        <tr>
                                            <th>User Name</th>
                                            <th>Complete Bookings</th>
                                        </tr>
                                    </thead>
                            <tbody id="usersTableBody">
                                <!-- Users will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>

    <script>
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

        function exportBookings() {
            showNotification('Preparing export...', 'info');
            window.location.href = '../api/bookings.php?export=csv';
        }

        function showUsersModal() {
            // Show loading state
            document.getElementById('usersTableBody').innerHTML = `
                <tr>
                    <td colspan="2" class="text-center">
                        <i class="fas fa-spinner fa-spin me-2"></i>Loading users...
                    </td>
                </tr>
            `;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('usersModal'));
            modal.show();

            // Fetch users data
            fetch('../api/users.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Users API response:', data); // Debug log
                    if (data.success && data.users) {
                        displayUsersData(data.users);
                    } else {
                        console.error('API Error:', data.message || 'Unknown error');
                        document.getElementById('usersTableBody').innerHTML = `
                            <tr>
                                <td colspan="2" class="text-center text-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>${data.message || 'Error loading users'}
                                </td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    document.getElementById('usersTableBody').innerHTML = `
                        <tr>
                            <td colspan="2" class="text-center text-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>Network error loading users
                            </td>
                        </tr>
                    `;
                });
        }

        function displayUsersData(users) {
            const tbody = document.getElementById('usersTableBody');

            if (!users || users.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            <i class="fas fa-users me-2"></i>No users found
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            users.forEach(user => {
                // Safely get user name and complete bookings with fallbacks
                const userName = user.name || 'Unknown User';
                const completeBookings = user.complete_bookings || 0;
                const userInitial = userName.charAt(0).toUpperCase();

                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                    ${userInitial}
                                </div>
                                <strong>${userName}</strong>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">${completeBookings}</span>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }
    </script>
</body>
</html>