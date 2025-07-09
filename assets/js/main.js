// Main JavaScript for FitClass User Interface

document.addEventListener('DOMContentLoaded', function() {
    // Initialize animated counters for homepage
    initializeCounters();

    // Initialize the application
    initializeApp();

    // Initialize date pickers with minimum date as today (only on pages with these elements)
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    if (startDateInput || endDateInput) {
        const today = new Date().toISOString().split('T')[0];

        if (startDateInput) {
            startDateInput.min = today;
            startDateInput.value = today;

            startDateInput.addEventListener('change', function() {
                if (endDateInput) {
                    endDateInput.min = this.value;
                    if (endDateInput.value < this.value) {
                        endDateInput.value = this.value;
                    }
                }
            });
        }

        if (endDateInput) {
            endDateInput.min = today;
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            endDateInput.value = tomorrow.toISOString().split('T')[0];
        }
    }

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert && alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            if (href && href !== '#') {
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Initialize real-time form validation
    initializeFormValidation();
});

// Initialize animated counters
function initializeCounters() {
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const increment = target / 100;
        let count = 0;

        const updateCounter = () => {
            if (count < target) {
                count += increment;
                counter.textContent = Math.ceil(count);
                setTimeout(updateCounter, 20);
            } else {
                counter.textContent = target;
            }
        };
        updateCounter();
    });
}

function initializeApp() {
    // Initialize common features
    initializeForms();
    initializeModals();

    // Initialize page-specific features
    const currentPage = getCurrentPage();
    switch(currentPage) {
        case 'dashboard':
            initializeDashboard();
            break;
        case 'classes':
            initializeClasses();
            break;
        case 'bookings':
            initializeBookings();
            break;
    }
}

// Get current page
function getCurrentPage() {
    const path = window.location.pathname;
    if (path.includes('dashboard')) return 'dashboard';
    if (path.includes('classes')) return 'classes';
    if (path.includes('bookings')) return 'bookings';
    return 'home';
}

// Initialize forms
function initializeForms() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 5000);
            }
        });
    });

    // Enhanced form validation on submit
    const validationForms = document.querySelectorAll('form[data-validate]');
    validationForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showNotification('Please check the form for errors', 'error');
            }
        });
    });
}

// Initialize modals
function initializeModals() {
    // Clear modal content when hidden
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                form.querySelectorAll('.is-invalid, .is-valid').forEach(field => {
                    field.classList.remove('is-invalid', 'is-valid');
                });
            }
        });
    });
}

// Initialize real-time form validation
function initializeFormValidation() {
    // Real-time form validation
    document.addEventListener('input', function(e) {
        if (e.target.matches('input, select, textarea')) {
            const input = e.target;
            const value = input.value.trim();
            let valid = true;

            if (input.hasAttribute('required') && !value) {
                valid = false;
            } else if (value && input.type === 'email') {
                valid = FormValidator.email(value);
            } else if (value && input.type === 'password' && input.hasAttribute('data-min-length')) {
                valid = value.length >= parseInt(input.getAttribute('data-min-length'));
            }

            if (valid) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            } else {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
            }
        }
    });
}

// Initialize dashboard, classes, bookings
function initializeDashboard() {
    console.log('Dashboard initialized');
}

function initializeClasses() {
    console.log('Classes page initialized');
}

function initializeBookings() {
    console.log('Bookings page initialized');
}

// Book Class Function
function bookClass(classId) {
    // Set class ID in modal
    document.getElementById('class_id').value = classId;

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').setAttribute('min', today);
    document.getElementById('end_date').setAttribute('min', today);

    // Show booking modal
    const bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
    bookingModal.show();
}

// Handle booking form submission
document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate date range
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (new Date(endDate) < new Date(startDate)) {
                showNotification('End date must be after start date.', 'error');
                return;
            }

            // Validate at least one recurring day is selected
            const recurringDays = document.querySelectorAll('input[name="recurring_days[]"]:checked');
            if (recurringDays.length === 0) {
                showNotification('Please select at least one day of the week.', 'error');
                return;
            }

            const formData = new FormData(this);
            formData.append('action', 'book');

            fetch('api/bookings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();

                    // Reset form
                    bookingForm.reset();
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('start_date').value = today;
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    document.getElementById('end_date').value = tomorrow.toISOString().split('T')[0];

                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                const submitBtn = bookingForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm Booking';
                }
            });
        });
    }
});

// Enhanced form validation
const FormValidator = {
    email: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    password: function(password) {
        return password && password.length >= 6;
    },

    required: function(value) {
        return value && value.trim().length > 0;
    },

    phone: function(phone) {
        const re = /^[\+]?[1-9][\d]{0,15}$/;
        return re.test(phone.replace(/\s/g, ''));
    }
};

// Form validation helper
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

    inputs.forEach(input => {
        const value = input.value.trim();
        let valid = true;

        // Required validation
        if (input.hasAttribute('required') && !value) {
            valid = false;
        }

        // Type-specific validation
        if (value && input.type === 'email') {
            valid = FormValidator.email(value);
        } else if (value && input.type === 'password' && input.hasAttribute('data-min-length')) {
            valid = value.length >= parseInt(input.getAttribute('data-min-length'));
        }

        // Update UI
        if (valid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            isValid = false;
        }
    });

    return isValid;
}

// API Helper
const API = {
    async request(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const mergedOptions = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, mergedOptions);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    },

    async get(url) {
        return this.request(url);
    },

    async post(url, data) {
        return this.request(url, {
            method: 'POST',
            body: data instanceof FormData ? data : JSON.stringify(data),
            headers: data instanceof FormData ? {} : { 'Content-Type': 'application/json' }
        });
    }
};

// Loading state management
const LoadingManager = {
    show: function(element, text = 'Loading...') {
        if (element) {
            element.disabled = true;
            element.dataset.originalText = element.innerHTML;
            element.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${text}`;
        }
    },

    hide: function(element) {
        if (element && element.dataset.originalText) {
            element.disabled = false;
            element.innerHTML = element.dataset.originalText;
            delete element.dataset.originalText;
        }
    }
};

// Enhanced date formatting
const DateUtils = {
    formatDate: function(dateString, format = 'short') {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid Date';

        const options = {
            short: { month: 'short', day: 'numeric', year: 'numeric' },
            long: { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' },
            time: { hour: '2-digit', minute: '2-digit' },
            datetime: {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit'
            }
        };

        return date.toLocaleDateString('en-US', options[format] || options.short);
    },

    formatTime: function(timeString) {
        const time = new Date(`1970-01-01T${timeString}`);
        if (isNaN(time.getTime())) return 'Invalid Time';

        return time.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    },

    getDayName: function(dayIndex) {
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return days[dayIndex] || 'Invalid Day';
    }
};

// Show notification function
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification-toast');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification-toast alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 350px;
        max-width: 500px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        border-radius: 12px;
        border: none;
    `;

    const icons = {
        success: 'check-circle',
        error: 'exclamation-triangle', 
        warning: 'exclamation-circle',
        info: 'info-circle'
    };

    const icon = icons[type] || icons.info;

    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${icon} me-3 flex-shrink-0" style="font-size: 1.2rem;"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(notification);
            bsAlert.close();
        }
    }, 5000);
}

// Update booking status (for user bookings page)
function updateBookingStatus(bookingId, status) {
    if (!confirm(`Are you sure you want to ${status} this booking?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('booking_id', bookingId);
    formData.append('status', status);

    fetch('api/bookings.php', {
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

// Error handling for unhandled promise rejections
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    // Prevent the default browser behavior
    event.preventDefault();
});

// Global error handler
window.addEventListener('error', function(event) {
    console.error('Global error:', event.error);
});