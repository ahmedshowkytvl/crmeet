// Home Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Modern Search Bar Enhancement
    enhanceModernSearchBar();
    // Search functionality
    const searchBtn = document.getElementById('searchBtn');
    const contactsBtn = document.getElementById('contactsBtn');
    const searchPopup = document.getElementById('searchPopup');
    const searchOverlay = document.getElementById('searchOverlay');
    const closeSearch = document.getElementById('closeSearch');
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    
    // Contact circles functionality
    const contactCirclesModal = document.getElementById('contactCirclesModal');
    const circlesOverlay = document.getElementById('circlesOverlay');
    const closeCircles = document.getElementById('closeCircles');
    const circlesGrid = document.getElementById('circlesGrid');
    
    let searchTimeout;
    let contacts = [];
    
    // Load contacts data
    loadContacts();
    
    // Search button click
    searchBtn.addEventListener('click', function() {
        showSearchPopup();
    });
    
    // Contacts button click
    contactsBtn.addEventListener('click', function() {
        showContactCircles();
    });
    
    // Close search popup
    closeSearch.addEventListener('click', function() {
        hideSearchPopup();
    });
    
    searchOverlay.addEventListener('click', function() {
        hideSearchPopup();
    });
    
    // Search input functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            showSearchPlaceholder();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    // Close contact circles modal
    closeCircles.addEventListener('click', function() {
        hideContactCircles();
    });
    
    circlesOverlay.addEventListener('click', function() {
        hideContactCircles();
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to open search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            showSearchPopup();
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            hideSearchPopup();
            hideContactCircles();
        }
    });
    
    // Functions
    function showSearchPopup() {
        searchPopup.classList.add('show');
        searchInput.focus();
        document.body.style.overflow = 'hidden';
    }
    
    function hideSearchPopup() {
        searchPopup.classList.remove('show');
        searchInput.value = '';
        showSearchPlaceholder();
        document.body.style.overflow = '';
    }
    
    function showSearchPlaceholder() {
        searchResults.innerHTML = `
            <div class="search-placeholder">
                <i class="fas fa-search"></i>
                <p>ابدأ بالكتابة للبحث عن الموظفين</p>
            </div>
        `;
    }
    
    function performSearch(query) {
        // Show loading
        searchResults.innerHTML = `
            <div class="search-placeholder">
                <i class="fas fa-spinner fa-spin"></i>
                <p>جاري البحث...</p>
            </div>
        `;
        
        // Make AJAX request
        fetch(`/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
                searchResults.innerHTML = `
                    <div class="search-placeholder">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>حدث خطأ في البحث</p>
                    </div>
                `;
            });
    }
    
    function displaySearchResults(results) {
        if (results.length === 0) {
            searchResults.innerHTML = `
                <div class="search-placeholder">
                    <i class="fas fa-search"></i>
                    <p>لم يتم العثور على نتائج</p>
                </div>
            `;
            return;
        }
        
        const resultsHTML = results.map(contact => `
            <a href="${contact.url}" class="search-result-item">
                <img src="${contact.profile_picture || '/images/default-avatar.svg'}" 
                     alt="${contact.name}" 
                     class="search-result-avatar"
                     onerror="this.src='/images/default-avatar.svg'">
                <div class="search-result-info">
                    <h6>${contact.name}</h6>
                    <p>${contact.department} - ${contact.role}</p>
                    <small>${contact.email} ${contact.phone ? '• ' + contact.phone : ''}</small>
                </div>
            </a>
        `).join('');
        
        searchResults.innerHTML = resultsHTML;
        
        // Add click handler to close popup
        searchResults.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', function() {
                hideSearchPopup();
            });
        });
    }
    
    function loadContacts() {
        // Load contacts from the page data
        const contactsData = @json($contacts ?? []);
        contacts = contactsData.map(contact => ({
            id: contact.id,
            name: contact.name,
            email: contact.email,
            department: contact.department ? contact.department.name : 'غير محدد',
            role: contact.role ? contact.role.name_ar : 'غير محدد',
            profile_picture: contact.profile_picture ? `/${contact.profile_picture}` : null,
            url: `/users/${contact.id}`
        }));
        
        // Show contacts button if we have contacts
        if (contacts.length > 0) {
            addContactsButton();
        }
    }
    
    function addContactsButton() {
        const searchBtn = document.getElementById('searchBtn');
        const contactsBtn = document.createElement('button');
        contactsBtn.className = 'search-btn ms-3';
        contactsBtn.innerHTML = '<i class="fas fa-users me-2"></i>جهات الاتصال';
        contactsBtn.addEventListener('click', showContactCircles);
        searchBtn.parentNode.appendChild(contactsBtn);
    }
    
    function showContactCircles() {
        contactCirclesModal.classList.add('show');
        displayContactCircles();
        document.body.style.overflow = 'hidden';
    }
    
    function hideContactCircles() {
        contactCirclesModal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    function displayContactCircles() {
        const circlesHTML = contacts.map(contact => `
            <a href="${contact.url}" class="contact-circle">
                <img src="${contact.profile_picture || '/images/default-avatar.svg'}" 
                     alt="${contact.name}" 
                     class="contact-circle-avatar"
                     onerror="this.src='/images/default-avatar.svg'">
                <div class="contact-circle-name">${contact.name}</div>
                <div class="contact-circle-department">${contact.department}</div>
            </a>
        `).join('');
        
        circlesGrid.innerHTML = circlesHTML;
        
        // Add click handler to close modal
        circlesGrid.querySelectorAll('.contact-circle').forEach(circle => {
            circle.addEventListener('click', function() {
                hideContactCircles();
            });
        });
    }
    
    // Task completion functionality
    window.toggleTask = function(taskId, completed) {
        // Here you would typically make an AJAX request to update the task
        // For now, we'll just update the UI
        const taskItem = event.target.closest('.todo-item');
        if (completed) {
            taskItem.classList.add('completed');
        } else {
            taskItem.classList.remove('completed');
        }
        
        // You can add AJAX call here to update the task status
        // fetch(`/tasks/${taskId}/toggle`, { method: 'POST' })
        //     .then(response => response.json())
        //     .then(data => console.log('Task updated'))
        //     .catch(error => console.error('Error:', error));
    };
    
    // Add some nice animations
    const todoItems = document.querySelectorAll('.todo-item');
    todoItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('fade-in');
    });
    
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('slide-up');
    });
});

// Add some utility functions
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Add smooth scrolling for better UX
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Modern Search Bar Enhancement Function
function enhanceModernSearchBar() {
    const searchForm = document.querySelector('.modern-search-form');
    const searchInput = document.querySelector('.modern-search-input');
    const searchBtn = document.querySelector('.modern-search-btn');
    
    if (!searchForm || !searchInput || !searchBtn) return;
    
    // Add focus effects
    searchInput.addEventListener('focus', function() {
        searchForm.classList.add('searching');
        this.parentElement.style.transform = 'scale(1.02)';
    });
    
    searchInput.addEventListener('blur', function() {
        searchForm.classList.remove('searching');
        this.parentElement.style.transform = 'scale(1)';
    });
    
    // Add typing effects
    searchInput.addEventListener('input', function() {
        if (this.value.length > 0) {
            searchForm.style.borderColor = '#667eea';
            searchBtn.style.background = 'linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%)';
        } else {
            searchForm.style.borderColor = 'transparent';
            searchBtn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        }
    });
    
    // Add loading state on form submit
    searchForm.addEventListener('submit', function() {
        searchBtn.classList.add('loading');
        searchBtn.innerHTML = '<i class="fas fa-spinner search-icon"></i>';
        
        // Remove loading state after a short delay (simulate search)
        setTimeout(() => {
            searchBtn.classList.remove('loading');
            searchBtn.innerHTML = '<i class="fas fa-search search-icon"></i>';
        }, 1000);
    });
    
    // Add hover effects for clear button
    const clearBtn = document.querySelector('.modern-search-clear');
    if (clearBtn) {
        clearBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(90deg)';
        });
        
        clearBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    }
    
    // Add keyboard shortcuts
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            this.blur();
            if (clearBtn) {
                clearBtn.click();
            }
        }
    });
}
