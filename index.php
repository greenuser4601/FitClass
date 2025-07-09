<?php
session_start();
require_once 'includes/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: admin/');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

// Get stats for homepage
$users_data = json_decode(file_get_contents(USERS_FILE), true);
$classes_data = json_decode(file_get_contents(CLASSES_FILE), true);
$bookings_data = json_decode(file_get_contents(BOOKINGS_FILE), true);

$total_users = count($users_data['users']);
$total_classes = count($classes_data['classes']);
$total_bookings = count($bookings_data['bookings']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Your Fitness Journey Starts Here</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-dumbbell me-2"></i><?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#classes">Classes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#benefits">Benefits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light ms-2 px-3" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section d-flex align-items-center">
        <div class="hero-bg-pattern"></div>
        <div class="container position-relative">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-content text-white fade-in">
                        <h1 class="display-3 fw-bold mb-4">
                            Transform Your Body,<br>
                            <span class="text-gradient">Transform Your Life</span>
                        </h1>
                        <p class="lead mb-4 text-light">
                            Join thousands of fitness enthusiasts in our state-of-the-art facility. 
                            Book classes, track progress, and achieve your fitness goals with expert guidance.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="login.php" class="btn btn-warning btn-lg shadow-lg">
                                <i class="fas fa-rocket me-2"></i>Start Your Journey
                            </a>
                            <a href="#classes" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-calendar-alt me-2"></i>View Classes
                            </a>
                        </div>
                        
                        <!-- Stats Cards -->
                        <div class="row mt-5">
                            <div class="col-md-4">
                                <div class="stats-card bg-white bg-opacity-10 p-3 rounded text-center">
                                    <div class="fw-bold h4 mb-1" data-counter="<?php echo $total_users; ?>"><?php echo $total_users; ?></div>
                                    <small>Happy Members</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card bg-white bg-opacity-10 p-3 rounded text-center">
                                    <div class="fw-bold h4 mb-1" data-counter="<?php echo $total_classes; ?>"><?php echo $total_classes; ?></div>
                                    <small>Fitness Classes</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card bg-white bg-opacity-10 p-3 rounded text-center">
                                    <div class="fw-bold h4 mb-1" data-counter="<?php echo $total_bookings; ?>"><?php echo $total_bookings; ?></div>
                                    <small>Active Bookings</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-visual">
                        <div class="fitness-icons-container">
                            <div class="floating-icon icon-1 text-warning">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <div class="floating-icon icon-2 text-info">
                                <i class="fas fa-running"></i>
                            </div>
                            <div class="floating-icon icon-3 text-success">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="floating-icon icon-4 text-danger">
                                <i class="fas fa-fire"></i>
                            </div>
                            <div class="floating-icon icon-5 text-primary">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="hero-circle">
                                <i class="fas fa-play hero-main-icon text-warning" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="scroll-indicator">
            <div class="scroll-arrow">
                <i class="fas fa-chevron-down"></i>
            </div>
            <small class="text-light">Scroll to explore</small>
        </div>
    </section>

    <!-- Classes Preview Section -->
    <section id="classes" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Popular Classes</h2>
                <p class="lead text-muted">Choose from our diverse range of fitness classes</p>
            </div>
            
            <div class="row" id="classesContainer">
                <!-- Classes will be loaded here -->
            </div>
            
            <div class="text-center mt-5">
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Book Classes
                </a>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Why Choose FitClass?</h2>
                <p class="lead text-muted">Experience the difference with our premium fitness platform</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="benefit-card h-100 p-4 text-center">
                        <div class="benefit-icon text-primary mb-3">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h5 class="fw-bold">Easy Booking</h5>
                        <p class="text-muted">Book classes instantly with our user-friendly interface. Schedule recurring sessions effortlessly.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card h-100 p-4 text-center">
                        <div class="benefit-icon text-success mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="fw-bold">Expert Trainers</h5>
                        <p class="text-muted">Learn from certified professionals with years of experience in fitness and wellness.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card h-100 p-4 text-center">
                        <div class="benefit-icon text-warning mb-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5 class="fw-bold">Track Progress</h5>
                        <p class="text-muted">Monitor your fitness journey with detailed analytics and personalized recommendations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo APP_NAME; ?></h5>
                    <p class="text-muted">Transform your fitness journey with our comprehensive booking platform.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Load classes preview with error handling and loading state
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('classesContainer');
            
            // Show loading state
            container.innerHTML = `
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading classes...</p>
                </div>
            `;
            
            fetch('api/classes.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.classes) {
                        const classes = data.classes.slice(0, 3); // Show only first 3 classes
                        
                        if (classes.length === 0) {
                            container.innerHTML = `
                                <div class="col-12 text-center">
                                    <p class="text-muted">No classes available at the moment.</p>
                                </div>
                            `;
                            return;
                        }
                        
                        container.innerHTML = classes.map(cls => `
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">${escapeHtml(cls.name)}</h5>
                                        <p class="card-text text-muted">${escapeHtml(cls.description.substring(0, 100))}${cls.description.length > 100 ? '...' : ''}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>${cls.duration} mins
                                            </small>
                                            <strong class="text-primary">₱${parseFloat(cls.price).toFixed(2)}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = `
                            <div class="col-12 text-center">
                                <p class="text-muted">Unable to load classes.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading classes:', error);
                    container.innerHTML = `
                        <div class="col-12 text-center">
                            <p class="text-danger">Error loading classes. Please try again later.</p>
                        </div>
                    `;
                });
        });

        // Utility function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Performance monitoring
        window.addEventListener('load', function() {
            const loadTime = performance.now();
            if (loadTime > 3000) {
                console.warn('Page load time is slow:', loadTime + 'ms');
            }
        });
    </script>
</body>
</html>
