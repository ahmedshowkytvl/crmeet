// Date picker configuration for dd/mm/yyyy format
document.addEventListener('DOMContentLoaded', function() {
    setupDatePickers();
    setupDateInputs();
});

// Function to setup date pickers with dd/mm/yyyy format
function setupDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Remove any existing event listeners to avoid duplicates
        input.removeEventListener('change', handleDateChange);
        input.removeEventListener('focus', handleDateFocus);
        input.removeEventListener('blur', handleDateBlur);
        
        // Add event listeners
        input.addEventListener('change', handleDateChange);
        input.addEventListener('focus', handleDateFocus);
        input.addEventListener('blur', handleDateBlur);
        
        // Add custom attributes for better UX
        input.setAttribute('data-format', 'dd/mm/yyyy');
        input.setAttribute('placeholder', 'dd/mm/yyyy');
        
        // Style the input
        input.style.textAlign = 'center';
        input.style.fontWeight = '500';
    });
}

// Setup date inputs with custom styling
function setupDateInputs() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Add custom CSS class
        input.classList.add('custom-date-input');
        
        // Create wrapper for better styling
        const wrapper = document.createElement('div');
        wrapper.className = 'date-input-wrapper';
        
        // Insert wrapper before input
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        // Add calendar icon
        const icon = document.createElement('i');
        icon.className = 'fas fa-calendar-alt date-input-icon';
        wrapper.appendChild(icon);
    });
}

// Handle date change event
function handleDateChange(event) {
    const input = event.target;
    if (input.value) {
        // Convert from yyyy-mm-dd to dd/mm/yyyy for display
        const date = new Date(input.value);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const formattedDate = `${day}/${month}/${year}`;
        
        // Create a temporary display element
        const displayElement = document.createElement('div');
        displayElement.className = 'form-text text-success date-display fw-bold';
        displayElement.innerHTML = `<i class="fas fa-check-circle me-1"></i>تم اختيار: ${formattedDate}`;
        
        // Remove any existing display element
        const existingDisplay = input.parentNode.querySelector('.date-display');
        if (existingDisplay) {
            existingDisplay.remove();
        }
        
        // Add the new display element
        input.parentNode.appendChild(displayElement);
        
        // Add success styling to input
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
    } else {
        // Remove display element if no date selected
        const existingDisplay = input.parentNode.querySelector('.date-display');
        if (existingDisplay) {
            existingDisplay.remove();
        }
        
        // Remove validation classes
        input.classList.remove('is-valid', 'is-invalid');
    }
}

// Handle date focus event
function handleDateFocus(event) {
    const input = event.target;
    input.classList.add('date-focused');
    
    // Add focus styling
    const wrapper = input.parentNode;
    if (wrapper.classList.contains('date-input-wrapper')) {
        wrapper.classList.add('focused');
    }
}

// Handle date blur event
function handleDateBlur(event) {
    const input = event.target;
    input.classList.remove('date-focused');
    
    // Remove focus styling
    const wrapper = input.parentNode;
    if (wrapper.classList.contains('date-input-wrapper')) {
        wrapper.classList.remove('focused');
    }
}

// Re-setup date pickers when new content is loaded (for dynamic content)
function reinitializeDatePickers() {
    setupDatePickers();
    setupDateInputs();
}

// Enhanced function to handle dynamic content
function setupDynamicDatePickers() {
    // Use MutationObserver to watch for new date inputs
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Check if the added node is a date input
                        if (node.type === 'date') {
                            setupSingleDatePicker(node);
                        }
                        // Check for date inputs within the added node
                        const dateInputs = node.querySelectorAll ? node.querySelectorAll('input[type="date"]') : [];
                        dateInputs.forEach(setupSingleDatePicker);
                    }
                });
            }
        });
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

// Setup a single date picker
function setupSingleDatePicker(input) {
    // Remove any existing event listeners
    input.removeEventListener('change', handleDateChange);
    input.removeEventListener('focus', handleDateFocus);
    input.removeEventListener('blur', handleDateBlur);
    
    // Add event listeners
    input.addEventListener('change', handleDateChange);
    input.addEventListener('focus', handleDateFocus);
    input.addEventListener('blur', handleDateBlur);
    
    // Add custom attributes
    input.setAttribute('data-format', 'dd/mm/yyyy');
    input.setAttribute('placeholder', 'dd/mm/yyyy');
    input.classList.add('custom-date-input');
    
    // Style the input
    input.style.textAlign = 'center';
    input.style.fontWeight = '500';
    
    // Create wrapper if not exists
    if (!input.parentNode.classList.contains('date-input-wrapper')) {
        const wrapper = document.createElement('div');
        wrapper.className = 'date-input-wrapper';
        
        // Insert wrapper before input
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        // Add calendar icon
        const icon = document.createElement('i');
        icon.className = 'fas fa-calendar-alt date-input-icon';
        wrapper.appendChild(icon);
    }
}

// Initialize dynamic date picker observer
document.addEventListener('DOMContentLoaded', function() {
    setupDynamicDatePickers();
});

// Export functions for global use
window.setupDatePickers = setupDatePickers;
window.reinitializeDatePickers = reinitializeDatePickers;
window.setupDynamicDatePickers = setupDynamicDatePickers;
window.setupSingleDatePicker = setupSingleDatePicker;
