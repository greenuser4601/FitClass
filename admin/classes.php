<?php
session_start();
require_once '../includes/config.php';
require_admin();

$classes = getAllClasses();
$trainers = getAllTrainers();

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes - <?php echo APP_NAME; ?></title>
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
                        <a class="nav-link active" href="classes.php">
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
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-2">Manage Classes</h1>
                        <p class="text-muted">Add, edit, and manage fitness classes</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                        <i class="fas fa-plus me-2"></i>Add New Class
                    </button>
                </div>
            </div>
        </div>

        <!-- Classes Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-dumbbell me-2"></i>All Classes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($classes)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-dumbbell fa-3x text-muted mb-3"></i>
                                <h5>No Classes Found</h5>
                                <p class="text-muted">Add your first fitness class to get started.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                                    <i class="fas fa-plus me-2"></i>Add First Class
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Class Name</th>
                                            <th>Trainer</th>
                                            <th>Duration</th>
                                            <th>Capacity</th>
                                            <th>Price</th>
                                            <th>Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($classes as $class): ?>
                                            <?php $trainer = findTrainerById($trainers, $class['trainer_id']); ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($class['name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars(substr($class['description'], 0, 50)); ?>...</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($trainer): ?>
                                                        <div>
                                                            <div class="fw-bold"><?php echo htmlspecialchars($trainer['name']); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($trainer['specialization']); ?></small>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">No trainer assigned</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $class['duration']; ?> mins</td>
                                                <td><?php echo $class['capacity']; ?> people</td>
                                                <td class="fw-bold text-success">₱<?php echo number_format($class['price'], 2); ?></td>
                                                <td><?php echo date('g:i A', strtotime($class['time'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="editClass(<?php echo htmlspecialchars(json_encode($class)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteClass(<?php echo $class['id']; ?>, '<?php echo htmlspecialchars($class['name']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
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

    <!-- Add Class Modal -->
    <div class="modal fade" id="addClassModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Add New Class
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addClassForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="add_name" class="form-label">Class Name</label>
                                <input type="text" class="form-control" id="add_name" name="name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="add_price" class="form-label">Price (₱)</label>
                                <input type="number" class="form-control" id="add_price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_description" class="form-label">Description</label>
                            <textarea class="form-control" id="add_description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_trainer_id" class="form-label">Trainer</label>
                                <select class="form-control" id="add_trainer_id" name="trainer_id" required>
                                    <option value="">Select Trainer</option>
                                    <?php foreach ($trainers as $trainer): ?>
                                        <option value="<?php echo $trainer['id']; ?>">
                                            <?php echo htmlspecialchars($trainer['name']); ?> - <?php echo htmlspecialchars($trainer['specialization']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="add_time" class="form-label">Class Time</label>
                                <input type="time" class="form-control" id="add_time" name="time" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" id="add_duration" name="duration" min="15" max="180" required>
                            </div>
                            <div class="col-md-6">
                                <label for="add_capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control" id="add_capacity" name="capacity" min="1" max="50" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Class
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <div class="modal fade" id="editClassModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Class
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editClassForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="class_id" id="edit_class_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="edit_name" class="form-label">Class Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_price" class="form-label">Price (₱)</label>
                                <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_trainer_id" class="form-label">Trainer</label>
                                <select class="form-control" id="edit_trainer_id" name="trainer_id" required>
                                    <option value="">Select Trainer</option>
                                    <?php foreach ($trainers as $trainer): ?>
                                        <option value="<?php echo $trainer['id']; ?>">
                                            <?php echo htmlspecialchars($trainer['name']); ?> - <?php echo htmlspecialchars($trainer['specialization']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_time" class="form-label">Class Time</label>
                                <input type="time" class="form-control" id="edit_time" name="time" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" id="edit_duration" name="duration" min="15" max="180" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control" id="edit_capacity" name="capacity" min="1" max="50" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Class
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    
    <script>
        // Handle add class form
        document.getElementById('addClassForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/classes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('addClassModal')).hide();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        });

        // Handle edit class form
        document.getElementById('editClassForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/classes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editClassModal')).hide();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        });

        // Edit class function
        function editClass(classData) {
            document.getElementById('edit_class_id').value = classData.id;
            document.getElementById('edit_name').value = classData.name;
            document.getElementById('edit_description').value = classData.description;
            document.getElementById('edit_trainer_id').value = classData.trainer_id;
            document.getElementById('edit_duration').value = classData.duration;
            document.getElementById('edit_capacity').value = classData.capacity;
            document.getElementById('edit_price').value = classData.price;
            
            // Convert time format
            const timeValue = classData.time.substring(0, 5); // Remove seconds
            document.getElementById('edit_time').value = timeValue;
            
            const modal = new bootstrap.Modal(document.getElementById('editClassModal'));
            modal.show();
        }

        // Delete class function
        function deleteClass(classId, className) {
            if (!confirm(`Are you sure you want to delete "${className}"? This action cannot be undone.`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('class_id', classId);
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            
            fetch('../api/classes.php', {
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
