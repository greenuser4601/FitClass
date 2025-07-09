<?php
session_start();
require_once 'includes/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: admin/index.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-bg text-light">
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center position-relative">
        <div class="row w-100 justify-content-center">
            <div class="col-md-6 col-lg-4">
                <!-- Login Card -->
                <div class="card shadow-lg border-0" id="loginCard">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-dumbbell text-primary fa-3x mb-3"></i>
                            <h3 class="fw-bold"><?php echo APP_NAME; ?></h3>
                            <p class="text-muted">Sign in to your account</p>
                        </div>
                        
                        <form id="loginForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Don't have an account? 
                                <a href="#" id="showRegister" class="text-primary fw-bold">Sign Up</a>
                            </p>
                        </div>
                        
                        <!-- Demo Credentials -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <small class="text-muted">
                                <strong>Demo Credentials:</strong><br>
                                Admin: yuriwise@gmail.com / admin123<br>
                                User: arjuncalixtro@gmail.com / password123
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Register Card -->
                <div class="card shadow-lg border-0 d-none" id="registerCard">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-plus text-success fa-3x mb-3"></i>
                            <h3 class="fw-bold">Create Account</h3>
                            <p class="text-muted">Join our fitness community</p>
                        </div>
                        
                        <form id="registerForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <div class="mb-3">
                                <label for="register_name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="register_name" name="name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="register_email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="register_email" name="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="register_password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="register_password" name="password" required minlength="6">
                                </div>
                                <div class="form-text">Password must be at least 6 characters long</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? 
                                <a href="#" id="showLogin" class="text-primary fw-bold">Sign In</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Back to Home -->
        <a href="index.php" class="position-absolute top-0 start-0 m-3 btn btn-outline-light">
            <i class="fas fa-arrow-left me-2"></i>Back to Home
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Toggle between login and register forms
        document.getElementById('showRegister').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('loginCard').classList.add('d-none');
            document.getElementById('registerCard').classList.remove('d-none');
        });
        
        document.getElementById('showLogin').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('registerCard').classList.add('d-none');
            document.getElementById('loginCard').classList.remove('d-none');
        });
        
        // Handle login form
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'login');
            
            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Redirect immediately without delay
                    if (data.user_type === 'admin') {
                        window.location.href = 'admin/';
                    } else {
                        window.location.href = 'dashboard.php';
                    }
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        });
        
        // Handle register form
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('register_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                showNotification('Passwords do not match', 'error');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('action', 'register');
            
            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        // Switch to login form after successful registration
                        document.getElementById('registerCard').classList.add('d-none');
                        document.getElementById('loginCard').classList.remove('d-none');
                        // Pre-fill email in login form
                        document.getElementById('email').value = document.getElementById('register_email').value;
                    }, 1500);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        });
    </script>
</body>
</html>
