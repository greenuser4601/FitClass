<?php
session_start();
require_once 'includes/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get user's bookings with improved grouping
$bookings = getUserBookingsGrouped($user_id);
$classes = getAllClasses();
$trainers = getAllTrainers();

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-dark">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-dumbbell me-2"></i><?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="classes.php">Classes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">My Bookings</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user_name); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="logout()">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <h1 class="card-title">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
                        <p class="card-text mb-0">Ready to continue your fitness journey? Check out your upcoming classes below.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-calendar-check fa-2x text-primary mb-3"></i>
                        <h3 class="fw-bold"><?php echo count($bookings); ?></h3>
                        <p class="text-muted mb-0">Active Booking Groups</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-dumbbell fa-2x text-success mb-3"></i>
                        <h3 class="fw-bold"><?php echo count($classes); ?></h3>
                        <p class="text-muted mb-0">Available Classes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-warning mb-3"></i>
                        <h3 class="fw-bold"><?php echo count($trainers); ?></h3>
                        <p class="text-muted mb-0">Expert Trainers</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>Your Booking Groups
                        </h5>
                        <a href="classes.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Book New Class
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bookings)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5>No Bookings Yet</h5>
                                <p class="text-muted">Start your fitness journey by booking your first class!</p>
                                <a href="classes.php" class="btn btn-primary">Browse Classes</a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($bookings as $booking): ?>
                                    <?php
                                    $class = findClassById($classes, $booking['class_id']);
                                    $trainer = $class ? findTrainerById($trainers, $class['trainer_id']) : null;
                                    ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="fw-bold mb-1"><?php echo $class ? htmlspecialchars($class['name']) : 'Unknown Class'; ?></h6>
                                                        <small class="text-muted">
                                                            <i class="fas fa-user me-1"></i>
                                                            <?php echo $trainer ? htmlspecialchars($trainer['name']) : 'Unknown Trainer'; ?>
                                                        </small>
                                                    </div>
                                                    <span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo $booking['date_range']; ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo date('g:i A', strtotime($class['time'] ?? '00:00:00')); ?>
                                                        (<?php echo $class['duration'] ?? 0; ?> mins)
                                                    </small>
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-repeat me-1"></i>
                                                        <?php echo $booking['session_count']; ?> session<?php echo $booking['session_count'] > 1 ? 's' : ''; ?>
                                                        <?php if ($booking['recurring_days']): ?>
                                                            on <?php echo $booking['recurring_days']; ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                
                                                <?php if ($class): ?>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong class="text-primary">
                                                            Total: ₱<?php echo number_format($class['price'] * $booking['session_count'], 2); ?>
                                                        </strong>
                                                        <small class="text-muted">
                                                            ₱<?php echo number_format($class['price'], 2); ?> per session
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($bookings)): ?>
                        <div class="card-footer text-center">
                            <a href="bookings.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-1"></i>View All Bookings
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                const formData = new FormData();
                formData.append('action', 'logout');
                
                fetch('api/auth.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'index.php';
                    }
                })
                .catch(error => {
                    window.location.href = 'index.php';
                });
            }
        }
    </script>
</body>
</html>
