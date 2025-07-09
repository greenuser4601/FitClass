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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - <?php echo APP_NAME; ?></title>
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="classes.php">Classes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="bookings.php">My Bookings</a>
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
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-2">My Bookings</h1>
                        <p class="text-dark" style="opacity: 0.8;">Manage your fitness class reservations</p>
                    </div>
                    <a href="classes.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Book New Class
                    </a>
                </div>
            </div>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h3>No Bookings Found</h3>
                    <p class="text-muted mb-4">You haven't booked any classes yet. Start your fitness journey today!</p>
                    <a href="classes.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-dumbbell me-2"></i>Browse Classes
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Header Row -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card bg-light border-0">
                        <div class="card-body py-2 px-3">
                            <div class="row align-items-center text-muted fw-semibold" style="font-size: 0.85rem;">
                                <div class="col-md-3">CLASS DETAILS</div>
                                <div class="col-md-2 text-center">SCHEDULE</div>
                                <div class="col-md-1 text-center">SESSIONS</div>
                                <div class="col-md-2 text-center">PRICE</div>
                                <div class="col-md-2 text-center">STATUS</div>
                                <div class="col-md-2 text-center">ACTIONS</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <?php foreach ($bookings as $booking): ?>
                    <?php
                    $class = findClassById($classes, $booking['class_id']);
                    $trainer = $class ? findTrainerById($trainers, $class['trainer_id']) : null;
                    $total_price = $class ? $class['price'] * $booking['session_count'] : 0;
                    ?>
                    <div class="col-12 mb-2">
                        <div class="card shadow-sm booking-card-compact">
                            <div class="card-body py-2 px-3">
                                <div class="row align-items-center booking-info-compact">
                                    <!-- Class Info -->
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                <i class="fas fa-dumbbell"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold mb-0" style="font-size: 0.95rem;"><?php echo $class ? htmlspecialchars($class['name']) : 'Unknown Class'; ?></div>
                                                <?php if ($trainer): ?>
                                                    <small class="text-dark" style="font-size: 0.8rem; opacity: 0.8;"><?php echo htmlspecialchars($trainer['name']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Schedule Info -->
                                    <div class="col-md-2 text-center">
                                        <div class="fw-bold text-primary" style="font-size: 0.85rem;">
                                            <i class="fas fa-calendar-alt me-1"></i><?php echo $booking['date_range']; ?>
                                        </div>
                                        <small class="text-dark" style="font-size: 0.75rem; opacity: 0.8;">
                                            <i class="fas fa-clock me-1"></i><?php echo date('g:i A', strtotime($class['time'] ?? '00:00:00')); ?>
                                            • <?php echo $class['duration'] ?? 0; ?>min
                                        </small>
                                    </div>

                                    <!-- Sessions -->
                                    <div class="col-md-1 text-center">
                                        <div class="fw-bold" style="font-size: 1.1rem;"><?php echo $booking['session_count']; ?></div>
                                        <small class="text-dark" style="font-size: 0.75rem; opacity: 0.8;">session<?php echo $booking['session_count'] > 1 ? 's' : ''; ?></small>
                                        <?php if ($booking['recurring_days']): ?>
                                            <div class="text-info mt-1" style="font-size: 0.7rem; font-weight: 500;"><?php echo $booking['recurring_days']; ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Price -->
                                    <div class="col-md-2 text-center">
                                        <div class="fw-bold text-primary" style="font-size: 0.95rem;">₱<?php echo number_format($total_price, 2); ?></div>
                                        <small class="text-dark" style="font-size: 0.75rem; opacity: 0.8;">₱<?php echo number_format($class['price'] ?? 0, 2); ?> each</small>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-2 text-center">
                                        <?php
                                        $status_colors = [
                                            'pending' => ['bg' => 'warning', 'text' => 'dark', 'icon' => 'clock'],
                                            'confirmed' => ['bg' => 'info', 'text' => 'white', 'icon' => 'check-circle'],
                                            'payment_required' => ['bg' => 'primary', 'text' => 'white', 'icon' => 'credit-card'],
                                            'paid' => ['bg' => 'success', 'text' => 'white', 'icon' => 'check-double'],
                                            'ongoing' => ['bg' => 'info', 'text' => 'white', 'icon' => 'play-circle'],
                                            'completed' => ['bg' => 'success', 'text' => 'white', 'icon' => 'check-circle'],
                                            'cancelled' => ['bg' => 'danger', 'text' => 'white', 'icon' => 'times-circle']
                                        ];
                                        $status_config = $status_colors[$booking['status']] ?? ['bg' => 'secondary', 'text' => 'white', 'icon' => 'question'];
                                        ?>
                                        <span class="badge bg-<?php echo $status_config['bg']; ?> text-<?php echo $status_config['text']; ?>" style="font-size: 0.75rem; padding: 0.5rem 0.75rem;">
                                            <i class="fas fa-<?php echo $status_config['icon']; ?> me-1"></i>
                                            <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                                        </span>
                                    </div>

                                    <!-- Actions -->
                                    <div class="col-md-2 text-center">
                                        <?php if ($booking['status'] === 'payment_required'): ?>
                                            <button class="btn btn-sm btn-success" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;" onclick="openPaymentModal('<?php echo $booking['recurring_group']; ?>', <?php echo $total_price; ?>)">
                                                <i class="fas fa-credit-card me-1"></i>Pay Now
                                            </button>
                                        <?php elseif ($booking['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-outline-danger" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;" onclick="cancelBooking('<?php echo $booking['recurring_group']; ?>')">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </button>
                                        <?php elseif ($booking['status'] === 'confirmed'): ?>
                                            <small class="text-muted" style="font-size: 0.7rem;">Ready to attend</small>
                                        <?php elseif ($booking['status'] === 'ongoing'): ?>
                                            <small class="text-info" style="font-size: 0.7rem;">
                                                <i class="fas fa-play-circle me-1"></i>In Progress
                                            </small>
                                        <?php elseif ($booking['status'] === 'completed'): ?>
                                            <small class="text-success" style="font-size: 0.7rem;">
                                                <i class="fas fa-check-circle me-1"></i>Completed
                                            </small>
                                        <?php elseif ($booking['status'] === 'cancelled'): ?>
                                            <small class="text-danger" style="font-size: 0.7rem;">
                                                <i class="fas fa-times-circle me-1"></i>Cancelled
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted" style="font-size: 0.7rem;">-</small>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary Card -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card bg-gradient-primary text-white">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="h4 fw-bold"><?php echo count($bookings); ?></div>
                                    <div>Booking Groups</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="h4 fw-bold">
                                        <?php 
                                        $total_sessions = array_sum(array_column($bookings, 'session_count'));
                                        echo $total_sessions;
                                        ?>
                                    </div>
                                    <div>Total Sessions</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="h4 fw-bold">
                                        <?php 
                                        $confirmed_count = count(array_filter($bookings, function($b) { return $b['status'] === 'confirmed'; }));
                                        echo $confirmed_count;
                                        ?>
                                    </div>
                                    <div>Confirmed</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="h4 fw-bold">
                                        ₱<?php 
                                        $total_amount = 0;
                                        foreach ($bookings as $booking) {
                                            $class = findClassById($classes, $booking['class_id']);
                                            if ($class) {
                                                $total_amount += $class['price'] * $booking['session_count'];
                                            }
                                        }
                                        echo number_format($total_amount, 2);
                                        ?>
                                    </div>
                                    <div>Total Value</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-credit-card me-2"></i>Payment Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <input type="hidden" id="paymentRecurringGroup" name="recurring_group">
                        <input type="hidden" id="paymentAmount" name="amount">

                        <div class="mb-3">
                            <label class="form-label">Payment Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" class="form-control" id="displayAmount" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="">Select payment method</option>
                                <option value="gcash">GCash</option>
                                <option value="paymaya">PayMaya</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="paymentTerms" required>
                                <label class="form-check-label" for="paymentTerms">
                                    I agree to the payment terms and conditions
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="processPayment()">
                        <i class="fas fa-credit-card me-2"></i>Process Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        function openPaymentModal(recurringGroup, amount) {
            document.getElementById('paymentRecurringGroup').value = recurringGroup;
            document.getElementById('paymentAmount').value = amount;
            document.getElementById('displayAmount').value = amount.toFixed(2);

            const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            paymentModal.show();
        }

        function processPayment() {
            const form = document.getElementById('paymentForm');
            const formData = new FormData(form);
            formData.append('action', 'pay');

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const submitBtn = document.querySelector('#paymentModal .btn-success');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

            fetch('api/bookings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Payment failed. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i>Process Payment';
            });
        }

        function cancelBooking(recurringGroup) {
            if (!confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'cancel_group');
            formData.append('recurring_group', recurringGroup);

            fetch('api/bookings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Booking deleted successfully', 'success');
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

        function showNotification(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentElement) {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(notification);
                    bsAlert.close();
                }
            }, 5000);
        }

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