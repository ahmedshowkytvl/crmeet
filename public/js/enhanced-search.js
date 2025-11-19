/**
 * Enhanced Search Form JavaScript
 * Provides advanced search functionality with autocomplete, suggestions, and keyboard shortcuts
 */

class EnhancedSearch {
    constructor() {
        this.searchForm = document.querySelector('.enhanced-search-form') || document.querySelector('.modern-search-form');
        this.searchInput = document.querySelector('.enhanced-search-input') || document.querySelector('.modern-search-input');
        this.searchBtn = document.querySelector('.enhanced-search-btn') || document.querySelector('.modern-search-btn');
        this.clearBtn = document.querySelector('.enhanced-search-clear') || document.querySelector('.modern-search-clear');
        this.suggestions = document.querySelector('.search-suggestions');
        this.shortcuts = document.querySelector('.keyboard-shortcuts');
        
        this.currentSuggestionIndex = -1;
        this.searchHistory = JSON.parse(localStorage.getItem('searchHistory') || '[]');
        this.isInitialized = false;
        
        this.init();
    }

    init() {
        if (!this.searchForm || !this.searchInput || !this.searchBtn) {
            console.warn('Enhanced Search: Required elements not found');
            return;
        }

        this.setupEventListeners();
        this.updateClearButton();
        this.loadSearchHistory();
        this.showShortcuts();
        this.isInitialized = true;
        
        console.log('Enhanced Search initialized successfully');
    }

    setupEventListeners() {
        // Input events
        this.searchInput.addEventListener('input', () => this.handleInput());
        this.searchInput.addEventListener('focus', () => this.handleFocus());
        this.searchInput.addEventListener('blur', () => this.handleBlur());
        this.searchInput.addEventListener('keydown', (e) => this.handleKeydown(e));

        // Form events
        this.searchForm.addEventListener('submit', (e) => this.handleSubmit(e));

        // Clear button
        if (this.clearBtn) {
            this.clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.clearSearch();
            });
        }

        // Suggestions
        if (this.suggestions) {
            this.suggestions.addEventListener('click', (e) => this.handleSuggestionClick(e));
        }

        // Filters
        document.querySelectorAll('.filter-chip').forEach(chip => {
            chip.addEventListener('click', () => this.handleFilterClick(chip));
        });

        // Recent searches
        document.querySelectorAll('.recent-item').forEach(item => {
            item.addEventListener('click', () => this.handleRecentClick(item));
        });

        // Global keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleGlobalKeydown(e));

        // Window events
        window.addEventListener('resize', () => this.handleResize());
    }

    handleInput() {
        const value = this.searchInput.value.trim();
        this.updateClearButton();
        
        if (value.length > 0) {
            this.showSuggestions();
            this.addPulseEffect();
            this.debounceSearch(value);
        } else {
            this.hideSuggestions();
            this.removePulseEffect();
        }
    }

    handleFocus() {
        this.addPulseEffect();
        if (this.searchInput.value.trim().length > 0) {
            this.showSuggestions();
        }
    }

    handleBlur() {
        setTimeout(() => {
            this.removePulseEffect();
            this.hideSuggestions();
        }, 200);
    }

    handleKeydown(e) {
        if (!this.suggestions || !this.suggestions.classList.contains('show')) {
            return;
        }

        const suggestions = this.suggestions.querySelectorAll('.suggestion-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.currentSuggestionIndex = Math.min(this.currentSuggestionIndex + 1, suggestions.length - 1);
                this.updateSuggestionSelection();
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.currentSuggestionIndex = Math.max(this.currentSuggestionIndex - 1, -1);
                this.updateSuggestionSelection();
                break;
            case 'Enter':
                if (this.currentSuggestionIndex >= 0) {
                    e.preventDefault();
                    this.selectSuggestion(suggestions[this.currentSuggestionIndex]);
                }
                break;
            case 'Escape':
                this.hideSuggestions();
                this.searchInput.blur();
                break;
        }
    }

    handleSubmit(e) {
        const value = this.searchInput.value.trim();
        if (value) {
            this.addToHistory(value);
            this.showLoadingState();
            
            // Simulate search delay
            setTimeout(() => {
                this.hideLoadingState();
            }, 1000);
        }
    }

    handleSuggestionClick(e) {
        const suggestion = e.target.closest('.suggestion-item');
        if (suggestion) {
            this.selectSuggestion(suggestion);
        }
    }

    handleFilterClick(chip) {
        document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        
        // Add filter animation
        chip.style.transform = 'scale(0.95)';
        setTimeout(() => {
            chip.style.transform = 'scale(1)';
        }, 150);

        // Trigger filter change event
        this.triggerFilterChange(chip.dataset.filter);
    }

    handleRecentClick(item) {
        const searchText = item.dataset.search;
        this.searchInput.value = searchText;
        this.searchInput.focus();
        this.showSuggestions();
        this.addToHistory(searchText);
    }

    handleGlobalKeydown(e) {
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            this.searchInput.focus();
            this.searchInput.select();
        }
    }

    handleResize() {
        // Hide suggestions on mobile when keyboard appears
        if (window.innerWidth < 768) {
            this.hideSuggestions();
        }
    }

    selectSuggestion(suggestion) {
        const searchText = suggestion.dataset.search;
        this.searchInput.value = searchText;
        this.hideSuggestions();
        this.searchInput.focus();
        this.addToHistory(searchText);
    }

    updateSuggestionSelection() {
        const suggestions = this.suggestions?.querySelectorAll('.suggestion-item');
        if (!suggestions) return;

        suggestions.forEach((suggestion, index) => {
            suggestion.classList.toggle('active', index === this.currentSuggestionIndex);
        });
    }

    showSuggestions() {
        if (!this.suggestions) return;
        
        this.suggestions.classList.add('show');
        this.currentSuggestionIndex = -1;
        this.loadSuggestions();
    }

    hideSuggestions() {
        if (!this.suggestions) return;
        
        this.suggestions.classList.remove('show');
        this.currentSuggestionIndex = -1;
    }

    loadSuggestions() {
        if (!this.suggestions) return;

        const searchTerm = this.searchInput.value.trim().toLowerCase();
        
        // Mock suggestions - replace with actual API call
        const mockSuggestions = [
            { name: 'أحمد محمد', role: 'مدير تقنية المعلومات', icon: 'fas fa-user' },
            { name: 'فاطمة علي', role: 'مطور ويب', icon: 'fas fa-user' },
            { name: 'محمد حسن', role: 'محاسب', icon: 'fas fa-user' },
            { name: 'سارة أحمد', role: 'مصممة جرافيك', icon: 'fas fa-user' },
            { name: 'علي محمود', role: 'مدير مبيعات', icon: 'fas fa-user' }
        ];

        const filteredSuggestions = mockSuggestions.filter(item => 
            item.name.toLowerCase().includes(searchTerm) || 
            item.role.toLowerCase().includes(searchTerm)
        );

        this.renderSuggestions(filteredSuggestions);
    }

    renderSuggestions(suggestions) {
        if (!this.suggestions) return;

        const suggestionsHTML = suggestions.map(suggestion => `
            <div class="suggestion-item" data-search="${suggestion.name}">
                <div class="suggestion-icon">
                    <i class="${suggestion.icon}"></i>
                </div>
                <div class="suggestion-content">
                    <div class="suggestion-title">${suggestion.name}</div>
                    <div class="suggestion-subtitle">${suggestion.role}</div>
                </div>
            </div>
        `).join('');

        this.suggestions.innerHTML = suggestionsHTML;

        // Re-attach event listeners
        this.suggestions.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => this.handleSuggestionClick({ target: item }));
        });
    }

    updateClearButton() {
        if (!this.clearBtn) return;

        if (this.searchInput.value.trim().length > 0) {
            this.clearBtn.classList.add('show');
        } else {
            this.clearBtn.classList.remove('show');
        }
    }

    clearSearch() {
        this.searchInput.value = '';
        this.searchInput.focus();
        this.hideSuggestions();
        this.updateClearButton();
        this.removePulseEffect();
    }

    addToHistory(searchTerm) {
        this.searchHistory = this.searchHistory.filter(item => item !== searchTerm);
        this.searchHistory.unshift(searchTerm);
        this.searchHistory = this.searchHistory.slice(0, 10);
        localStorage.setItem('searchHistory', JSON.stringify(this.searchHistory));
        this.loadSearchHistory();
    }

    loadSearchHistory() {
        const recentContainer = document.querySelector('.recent-searches');
        if (!recentContainer || this.searchHistory.length === 0) return;

        const historyHTML = this.searchHistory.map((item, index) => `
            <div class="recent-item" data-search="${item}">
                <span class="recent-text">${item}</span>
                <span class="recent-time">منذ ${this.getTimeAgo(index)}</span>
            </div>
        `).join('');
        
        recentContainer.innerHTML = `
            <h6><i class="fas fa-history"></i>البحث الأخير</h6>
            ${historyHTML}
        `;
        
        // Re-attach event listeners
        recentContainer.querySelectorAll('.recent-item').forEach(item => {
            item.addEventListener('click', () => this.handleRecentClick(item));
        });
    }

    getTimeAgo(index) {
        const times = ['الآن', '5 دقائق', 'ساعة', 'يوم', 'أسبوع'];
        return times[index] || 'وقت طويل';
    }

    showLoadingState() {
        this.searchBtn.classList.add('loading');
        this.searchBtn.innerHTML = '<i class="fas fa-spinner search-icon"></i>';
    }

    hideLoadingState() {
        this.searchBtn.classList.remove('loading');
        this.searchBtn.innerHTML = '<i class="fas fa-search search-icon"></i>';
    }

    addPulseEffect() {
        this.searchForm.classList.add('search-pulse');
    }

    removePulseEffect() {
        this.searchForm.classList.remove('search-pulse');
    }

    showShortcuts() {
        if (!this.shortcuts) return;

        setTimeout(() => {
            this.shortcuts.classList.add('show');
        }, 2000);
        
        setTimeout(() => {
            this.shortcuts.classList.remove('show');
        }, 8000);
    }

    triggerFilterChange(filter) {
        // Dispatch custom event for filter changes
        const event = new CustomEvent('searchFilterChange', {
            detail: { filter }
        });
        document.dispatchEvent(event);
    }

    debounceSearch(searchTerm) {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.performSearch(searchTerm);
        }, 300);
    }

    performSearch(searchTerm) {
        // Dispatch custom event for search
        const event = new CustomEvent('searchPerformed', {
            detail: { searchTerm }
        });
        document.dispatchEvent(event);
    }

    // Public API methods
    setSearchValue(value) {
        this.searchInput.value = value;
        this.updateClearButton();
        this.addToHistory(value);
    }

    getSearchValue() {
        return this.searchInput.value.trim();
    }

    clearHistory() {
        this.searchHistory = [];
        localStorage.removeItem('searchHistory');
        this.loadSearchHistory();
    }

    destroy() {
        // Remove event listeners and clean up
        if (this.searchInput) {
            this.searchInput.removeEventListener('input', this.handleInput);
            this.searchInput.removeEventListener('focus', this.handleFocus);
            this.searchInput.removeEventListener('blur', this.handleBlur);
            this.searchInput.removeEventListener('keydown', this.handleKeydown);
        }

        if (this.searchForm) {
            this.searchForm.removeEventListener('submit', this.handleSubmit);
        }

        if (this.clearBtn) {
            this.clearBtn.removeEventListener('click', this.clearSearch);
        }

        document.removeEventListener('keydown', this.handleGlobalKeydown);
        window.removeEventListener('resize', this.handleResize);

        this.isInitialized = false;
    }
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.enhancedSearch = new EnhancedSearch();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnhancedSearch;
}



