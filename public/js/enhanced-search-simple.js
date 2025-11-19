/**
 * Enhanced Search Form - Simple Version
 * Provides basic enhanced search functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.enhanced-search-form');
    const searchInput = document.querySelector('.enhanced-search-input');
    const searchBtn = document.querySelector('.enhanced-search-btn');
    const clearBtn = document.querySelector('.enhanced-search-clear');
    
    if (!searchForm || !searchInput || !searchBtn) {
        console.warn('Enhanced Search: Required elements not found');
        return;
    }
    
    // Add focus effects
    searchInput.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
    });
    
    searchInput.addEventListener('blur', function() {
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
        
        // Remove loading state after a short delay
        setTimeout(() => {
            searchBtn.classList.remove('loading');
            searchBtn.innerHTML = '<i class="fas fa-search search-icon"></i>';
        }, 1000);
    });
    
    // Add hover effects for clear button
    if (clearBtn) {
        clearBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-50%) scale(1.1) rotate(90deg)';
        });
        
        clearBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-50%) scale(1) rotate(0deg)';
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
    
    // Global keyboard shortcut (Ctrl+K)
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });
    
    console.log('Enhanced Search initialized successfully');
});
