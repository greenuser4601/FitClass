// Admin JavaScript for FitClass

document.addEventListener('DOMContentLoaded', function() {
    // Initialize admin dashboard features
    initializeAdminFeatures();

    // Initialize data tables if available
    if (typeof DataTable !== 'undefined') {
        initializeDataTables();
    }

    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
});

// Initialize admin-specific features
function initializeAdminFeatures() {
    // Auto-refresh dashboard stats every 30 seconds
    if (window.location.pathname.includes('/admin/') && window.location.pathname.includes('index.php')) {
        setInterval(refreshDashboardStats, 30000);
    }

    // Initialize confirmation dialogs
    initializeConfirmationDialogs();

    // Initialize bulk actions
    initializeBulkActions();

    // Initialize form validation
    initializeFormValidation();
}

// Refresh dashboard stats
function refreshDashboardStats() {
    fetch('../api/dashboard-stats.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    updateDashboardStats(data.stats);
                }
            } catch (e) {
                console.error('JSON Parse Error:', e);
                console.error('Response text:', text);
            }
        })
        .catch(error => {
            console.error('Error refreshing stats:', error);
        });
}

// Update dashboard stats in DOM
function updateDashboardStats(stats) {
    // Update stats cards if they exist
    const totalUsersEl = document.querySelector('[data-stat="total-users"]');
    const totalBookingsEl = document.querySelector('[data-stat="total-bookings"]');
    const totalRevenueEl = document.querySelector('[data-stat="total-revenue"]');

    if (totalUsersEl) totalUsersEl.textContent = stats.total_users;
    if (totalBookingsEl) totalBookingsEl.textContent = stats.total_bookings;
    if (totalRevenueEl) totalRevenueEl.textContent = '₱' + parseFloat(stats.total_revenue).toLocaleString();
}

// Update booking status
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

// Export bookings to CSV
function exportBookings() {
    showNotification('Preparing export...', 'info');

    // Determine correct API path based on current location
    const apiPath = window.location.pathname.includes('/admin/') ? '../api/bookings.php?export=csv' : 'api/bookings.php?export=csv';

    // Create CSV export request
    fetch(apiPath)
        .then(response => {
            if (response.ok) {
                return response.text();
            }
            throw new Error('Export failed');
        })
        .then(csvContent => {
            downloadCSV(csvContent, 'bookings-export-' + new Date().toISOString().split('T')[0] + '.csv');
            showNotification('Export completed successfully!', 'success');
        })
        .catch(error => {
            console.error('Export error:', error);
            showNotification('Export failed. Please try again.', 'error');
        });
}

// Download CSV file
function downloadCSV(csvContent, filename) {
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');

    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    } else {
        // Fallback for older browsers
        window.open('data:text/csv;charset=utf-8,' + encodeURIComponent(csvContent));
    }
}

// Update stats cards with animation (alias for updateDashboardStats)
function updateStatsCards(stats) {
    updateDashboardStats(stats);
}

// Enhanced updateDashboardStats function
function updateDashboardStats(stats) {
    // Update stats cards if they exist
    const totalUsersEl = document.querySelector('[data-stat="total_users"]');
    const totalClassesEl = document.querySelector('[data-stat="total_classes"]');
    const totalTrainersEl = document.querySelector('[data-stat="total_trainers"]');
    const totalBookingsEl = document.querySelector('[data-stat="total_bookings"]');
    const totalRevenueEl = document.querySelector('[data-stat="total_revenue"]');

    if (totalUsersEl) {
        const valueEl = totalUsersEl.querySelector('.fw-bold, h3');
        if (valueEl) valueEl.textContent = stats.total_users || 0;
    }
    if (totalClassesEl) {
        const valueEl = totalClassesEl.querySelector('.fw-bold, h3');
        if (valueEl) valueEl.textContent = stats.total_classes || 0;
    }
    if (totalTrainersEl) {
        const valueEl = totalTrainersEl.querySelector('.fw-bold, h3');
        if (valueEl) valueEl.textContent = stats.total_trainers || 0;
    }
    if (totalBookingsEl) {
        const valueEl = totalBookingsEl.querySelector('.fw-bold, h3');
        if (valueEl) valueEl.textContent = stats.total_bookings || 0;
    }
    if (totalRevenueEl) {
        const valueEl = totalRevenueEl.querySelector('.fw-bold, h3');
        if (valueEl) valueEl.textContent = '₱' + parseFloat(stats.total_revenue || 0).toLocaleString();
    }
}

// Initialize confirmation dialogs
function initializeConfirmationDialogs() {
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// Initialize bulk actions
function initializeBulkActions() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionBtn = document.getElementById('bulkActionBtn');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkActions();
        });
    }

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkActions);
    });

    function toggleBulkActions() {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        if (bulkActionBtn) {
            bulkActionBtn.style.display = checkedItems.length > 0 ? 'block' : 'none';
        }
    }
}

// Initialize data tables
function initializeDataTables() {
    const tables = document.querySelectorAll('.data-table');

    tables.forEach(table => {
        new DataTable(table, {
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: -1 } // Disable sorting on actions column
            ],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries found",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    });
}

// Initialize charts
function initializeCharts() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue (₱)',
                    data: [12000, 19000, 15000, 25000, 22000, 30000],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Bookings Chart
    const bookingsCtx = document.getElementById('bookingsChart');
    if (bookingsCtx) {
        new Chart(bookingsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Confirmed', 'Pending', 'Cancelled'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: ['#059669', '#d97706', '#dc2626'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}

// Enhanced admin notification with better styling
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.admin-notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `admin-notification alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
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
            <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="alert"></button>
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

// Real-time updates using Server-Sent Events (if implemented)
function initializeRealTimeUpdates() {
    if (typeof EventSource !== 'undefined') {
        const eventSource = new EventSource('api/sse-updates.php');

        eventSource.addEventListener('booking-update', function(e) {
            const data = JSON.parse(e.data);
            showNotification(`New booking: ${data.class_name} by ${data.user_name}`, 'info');
            refreshDashboardStats();
        });

        eventSource.addEventListener('error', function(e) {
            console.error('SSE connection error:', e);
            eventSource.close();
        });
    }
}

// Utility functions for admin
const AdminUtils = {
    // Format currency for admin display
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
            minimumFractionDigits: 2
        }).format(amount);
    },

    // Format date for admin display
    formatDateTime: function(dateString) {
        return new Date(dateString).toLocaleDateString('en-PH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // Generate random colors for charts
    generateColors: function(count) {
        const colors = [
            '#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed',
            '#db2777', '#0891b2', '#65a30d', '#ea580c', '#be185d'
        ];

        const result = [];
        for (let i = 0; i < count; i++) {
            result.push(colors[i % colors.length]);
        }
        return result;
    },

    // Debounce function for search inputs
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Search functionality with improved performance
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('[data-search]');

    searchInputs.forEach(input => {
        const targetTable = document.querySelector(input.getAttribute('data-search'));
        if (targetTable) {
            input.addEventListener('input', AdminUtils.debounce(function() {
                filterTable(targetTable, this.value);
            }, 300));
        }
    });
});

// Filter table rows based on search query
function filterTable(table, query) {
    const rows = table.querySelectorAll('tbody tr');
    const searchTerm = query.toLowerCase();
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const isVisible = text.includes(searchTerm);
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
    });

    // Show/hide "no results" message
    const noResultsRow = table.querySelector('.no-results');
    if (noResultsRow) {
        noResultsRow.style.display = visibleCount === 0 && query ? '' : 'none';
    }
}

// Animate counter for admin stats
function animateCounter(element, target, duration = 1500) {
    const start = parseInt(element.textContent.replace(/[^0-9]/g, '')) || 0;
    const increment = (target - start) / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        const value = Math.floor(current);

        if (element.textContent.includes('₱')) {
            element.textContent = AdminUtils.formatCurrency(value);
        } else {
            element.textContent = value.toLocaleString();
        }

        if ((increment > 0 && current >= target) || (increment < 0 && current <= target)) {
            if (element.textContent.includes('₱')) {
                element.textContent = AdminUtils.formatCurrency(target);
            } else {
                element.textContent = target.toLocaleString();
            }
            clearInterval(timer);
        }
    }, 16);
}

// Batch operations for admin
function initializeBatchOperations() {
    const batchForm = document.getElementById('batchOperationsForm');
    if (batchForm) {
        batchForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const selectedItems = document.querySelectorAll('.item-checkbox:checked');
            if (selectedItems.length === 0) {
                showNotification('Please select at least one item.', 'warning');
                return;
            }

            const action = this.querySelector('select[name="batch_action"]').value;
            if (!action) {
                showNotification('Please select an action.', 'warning');
                return;
            }

            if (!confirm(`Are you sure you want to ${action} ${selectedItems.length} item(s)?`)) {
                return;
            }

            // Process batch action
            const formData = new FormData();
            formData.append('action', 'batch_' + action);

            selectedItems.forEach(item => {
                formData.append('selected_ids[]', item.value);
            });

            fetch('api/batch-operations.php', {
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
        });
    }
}

// Initialize notifications (ensure it exists for admin pages)
function initializeNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }
}

// Initialize notifications (ensure it exists for admin pages)
function initializeNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }
}

// Initialize form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });

        form.addEventListener('submit', function(e) {
            let isValid = true;

            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields correctly.', 'error');
            }
        });
    });
}

// Validate form field
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;

    // Required validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
    }

    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
        }
    }

    // Number validation
    if (field.type === 'number' && value) {
        const min = field.getAttribute('min');
        const max = field.getAttribute('max');
        const numValue = parseFloat(value);

        if (min && numValue < parseFloat(min)) {
            isValid = false;
        }
        if (max && numValue > parseFloat(max)) {
            isValid = false;
        }
    }

    // Update field appearance
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }

    return isValid;
}

// Export AdminUtils for global access
window.AdminUtils = AdminUtils;

// Initialize batch operations when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeBatchOperations();
});