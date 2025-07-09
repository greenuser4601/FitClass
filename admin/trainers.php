<?php
session_start();
require_once '../includes/config.php';
require_admin();

$trainers = getAllTrainers();

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trainers - <?php echo APP_NAME; ?></title>
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
                        <a class="nav-link active" href="trainers.php">
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
                        <h1 class="mb-2">Manage Trainers</h1>
                        <p class="text-muted">Add, edit, and manage fitness trainers</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTrainerModal">
                        <i class="fas fa-plus me-2"></i>Add New Trainer
                    </button>
                </div>
            </div>
        </div>

        <!-- Trainers Grid -->
        <div class="row">
            <?php if (empty($trainers)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No Trainers Found</h5>
                            <p class="text-muted">Add your first trainer to get started.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTrainerModal">
                                <i class="fas fa-plus me-2"></i>Add First Trainer
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($trainers as $trainer): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                        <i class="fas fa-user fa-2x"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($trainer['name']); ?></h5>
                                        <small class="text-muted"><?php echo htmlspecialchars($trainer['specialization']); ?></small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-envelope text-muted me-2"></i>
                                        <small><?php echo htmlspecialchars($trainer['email']); ?></small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-phone text-muted me-2"></i>
                                        <small><?php echo htmlspecialchars($trainer['phone']); ?></small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-medal text-muted me-2"></i>
                                        <small><?php echo $trainer['experience']; ?> years experience</small>
                                    </div>
                                </div>
                                
                                <p class="card-text text-muted small">
                                    <?php echo htmlspecialchars(substr($trainer['bio'], 0, 100)); ?>
                                    <?php if (strlen($trainer['bio']) > 100): ?>...<?php endif; ?>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editTrainer(<?php echo htmlspecialchars(json_encode($trainer)); ?>)">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm flex-fill" onclick="deleteTrainer(<?php echo $trainer['id']; ?>, '<?php echo htmlspecialchars($trainer['name']); ?>')">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Trainer Modal -->
    <div class="modal fade" id="addTrainerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Add New Trainer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTrainerForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="add_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="add_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="add_email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="add_phone" name="phone" required>
                            </div>
                            <div class="col-md-6">
                                <label for="add_experience" class="form-label">Years of Experience</label>
                                <input type="number" class="form-control" id="add_experience" name="experience" min="0" max="50" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="add_specialization" name="specialization" required>
                            <div class="form-text">e.g., HIIT & CrossFit, Yoga & Mindfulness, etc.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_bio" class="form-label">Bio/Description</label>
                            <textarea class="form-control" id="add_bio" name="bio" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Trainer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Trainer Modal -->
    <div class="modal fade" id="editTrainerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Trainer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editTrainerForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="trainer_id" id="edit_trainer_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_experience" class="form-label">Years of Experience</label>
                                <input type="number" class="form-control" id="edit_experience" name="experience" min="0" max="50" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="edit_specialization" name="specialization" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_bio" class="form-label">Bio/Description</label>
                            <textarea class="form-control" id="edit_bio" name="bio" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Trainer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    
    <script>
        // Handle add trainer form
        document.getElementById('addTrainerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/trainers.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('addTrainerModal')).hide();
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

        // Handle edit trainer form
        document.getElementById('editTrainerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/trainers.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editTrainerModal')).hide();
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

        // Edit trainer function
        function editTrainer(trainerData) {
            document.getElementById('edit_trainer_id').value = trainerData.id;
            document.getElementById('edit_name').value = trainerData.name;
            document.getElementById('edit_email').value = trainerData.email;
            document.getElementById('edit_phone').value = trainerData.phone;
            document.getElementById('edit_specialization').value = trainerData.specialization;
            document.getElementById('edit_experience').value = trainerData.experience;
            document.getElementById('edit_bio').value = trainerData.bio;
            
            const modal = new bootstrap.Modal(document.getElementById('editTrainerModal'));
            modal.show();
        }

        // Delete trainer function
        function deleteTrainer(trainerId, trainerName) {
            if (!confirm(`Are you sure you want to delete "${trainerName}"? This action cannot be undone.`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('trainer_id', trainerId);
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            
            fetch('../api/trainers.php', {
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
