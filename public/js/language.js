// Language switching functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle language toggle clicks in sidebar
    const languageLinks = document.querySelectorAll('.sidebar .btn[href*="lang/"]');
    
    languageLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> ' + (this.textContent.includes('English') ? 'English' : 'العربية');
            
            // Redirect to language switch URL
            const href = this.getAttribute('href');
            window.location.href = href;
        });
    });
    
    // Add RTL/LTR body class based on current locale
    const currentLocale = document.documentElement.lang;
    if (currentLocale === 'ar') {
        document.body.classList.add('rtl');
    } else {
        document.body.classList.add('ltr');
    }
    
    // Handle form validation messages
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let hasErrors = false;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    hasErrors = true;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (hasErrors) {
                e.preventDefault();
                showAlert('Please fill in all required fields', 'error');
            }
        });
    });
    
    // Show alert function
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.main-content .p-4');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }, 5000);
    }
    
    // Add smooth transitions
    const cards = document.querySelectorAll('.card');
    cards.forEach(function(card, index) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(function() {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Handle table row clicks
    const tableRows = document.querySelectorAll('table tbody tr[data-href]');
    tableRows.forEach(function(row) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
    });
    
    // Add hover effects to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(function(button) {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
