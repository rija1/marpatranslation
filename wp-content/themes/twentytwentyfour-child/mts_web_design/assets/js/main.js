/**
 * MTS Website - Main JavaScript
 * Modern, accessible functionality for Marpa Translation Society
 * 
 * Features:
 * - Responsive navigation
 * - Smooth scrolling
 * - Form handling
 * - Animation observers
 * - Accessibility enhancements
 * - Search functionality
 * - Filter systems
 */

'use strict';

// ===================================================================
// MAIN APPLICATION
// ===================================================================

class MTSWebsite {
    constructor() {
        this.init();
    }
    
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.setupComponents();
            });
        } else {
            this.setupComponents();
        }
    }
    
    setupComponents() {
        // Initialize all components
        this.navigation = new Navigation();
        this.smoothScroll = new SmoothScroll();
        this.animations = new AnimationObserver();
        this.forms = new FormHandler();
        this.search = new SearchHandler();
        this.filters = new FilterHandler();
        
        // Setup accessibility enhancements
        this.setupAccessibility();
        
        // Setup performance optimizations
        this.setupPerformance();
        
        console.log('MTS Website initialized successfully');
    }
    
    setupAccessibility() {
        // Skip link functionality
        const skipLink = document.querySelector('.skip-link');
        if (skipLink) {
            skipLink.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(skipLink.getAttribute('href'));
                if (target) {
                    target.focus();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
        
        // Enhanced keyboard navigation
        this.setupKeyboardNavigation();
        
        // ARIA live regions for dynamic content
        this.setupAriaLiveRegions();
    }
    
    setupKeyboardNavigation() {
        // Escape key handler for closing modals, dropdowns, etc.
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                // Close mobile navigation
                const nav = document.querySelector('.nav-links');
                if (nav && nav.classList.contains('active')) {
                    this.navigation.closeMenu();
                }
                
                // Close any open modals or dropdowns
                document.querySelectorAll('.modal.active, .dropdown.active').forEach(el => {
                    el.classList.remove('active');
                });
            }
        });
        
        // Tab trap for modal dialogs
        document.addEventListener('keydown', this.handleTabTrap.bind(this));
    }
    
    handleTabTrap(e) {
        if (e.key !== 'Tab') return;
        
        const activeModal = document.querySelector('.modal.active');
        if (!activeModal) return;
        
        const focusableElements = activeModal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length === 0) return;
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        if (e.shiftKey) {
            if (document.activeElement === firstElement) {
                lastElement.focus();
                e.preventDefault();
            }
        } else {
            if (document.activeElement === lastElement) {
                firstElement.focus();
                e.preventDefault();
            }
        }
    }
    
    setupAriaLiveRegions() {
        // Create live region for announcements if it doesn't exist
        if (!document.getElementById('live-region')) {
            const liveRegion = document.createElement('div');
            liveRegion.id = 'live-region';
            liveRegion.setAttribute('aria-live', 'polite');
            liveRegion.setAttribute('aria-atomic', 'true');
            liveRegion.style.cssText = 'position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden;';
            document.body.appendChild(liveRegion);
        }
    }
    
    setupPerformance() {
        // Lazy load images
        this.setupLazyLoading();
        
        // Debounce resize events
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleResize();
            }, 150);
        });
    }
    
    setupLazyLoading() {
        // Use Intersection Observer for lazy loading if available
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            imageObserver.unobserve(img);
                        }
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }
    
    handleResize() {
        // Update navigation on resize
        if (this.navigation) {
            this.navigation.handleResize();
        }
        
        // Recalculate animations if needed
        if (this.animations) {
            this.animations.recalculate();
        }
    }
    
    // Utility method to announce messages to screen readers
    announce(message, priority = 'polite') {
        const liveRegion = document.getElementById('live-region');
        if (liveRegion) {
            liveRegion.setAttribute('aria-live', priority);
            liveRegion.textContent = message;
            
            // Clear message after announcement
            setTimeout(() => {
                liveRegion.textContent = '';
            }, 1000);
        }
    }
}

// ===================================================================
// NAVIGATION COMPONENT
// ===================================================================

class Navigation {
    constructor() {
        this.nav = document.querySelector('nav');
        this.navLinks = document.querySelector('.nav-links');
        this.navToggle = document.querySelector('.nav-toggle');
        this.header = document.querySelector('header');
        
        this.init();
    }
    
    init() {
        if (!this.nav) return;
        
        // Create mobile toggle if it doesn't exist
        this.createMobileToggle();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Setup scroll behavior
        this.setupScrollBehavior();
        
        // Mark current page in navigation
        this.setActivePage();
    }
    
    createMobileToggle() {
        if (!this.navToggle && this.navLinks) {
            const toggle = document.createElement('button');
            toggle.className = 'nav-toggle';
            toggle.setAttribute('aria-label', 'Toggle navigation menu');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.innerHTML = '☰';
            
            this.nav.appendChild(toggle);
            this.navToggle = toggle;
        }
    }
    
    setupEventListeners() {
        // Mobile toggle
        if (this.navToggle) {
            this.navToggle.addEventListener('click', () => {
                this.toggleMenu();
            });
        }
        
        // Close menu when clicking links
        if (this.navLinks) {
            this.navLinks.addEventListener('click', (e) => {
                if (e.target.tagName === 'A') {
                    this.closeMenu();
                }
            });
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (this.navLinks && 
                this.navLinks.classList.contains('active') && 
                !this.nav.contains(e.target)) {
                this.closeMenu();
            }
        });
    }
    
    setupScrollBehavior() {
        let lastScrollTop = 0;
        let ticking = false;
        
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    this.handleScroll();
                    ticking = false;
                });
                ticking = true;
            }
        });
    }
    
    handleScroll() {
        if (!this.header) return;
        
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Add shadow when scrolled
        if (scrollTop > 10) {
            this.header.classList.add('scrolled');
        } else {
            this.header.classList.remove('scrolled');
        }
        
        // Auto-hide mobile menu when scrolling
        if (this.navLinks && this.navLinks.classList.contains('active') && window.innerWidth <= 768) {
            this.closeMenu();
        }
    }
    
    toggleMenu() {
        if (!this.navLinks || !this.navToggle) return;
        
        const isActive = this.navLinks.classList.contains('active');
        
        if (isActive) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }
    
    openMenu() {
        if (!this.navLinks || !this.navToggle) return;
        
        this.navLinks.classList.add('active');
        this.navToggle.setAttribute('aria-expanded', 'true');
        this.navToggle.innerHTML = '✕';
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Focus first link
        const firstLink = this.navLinks.querySelector('a');
        if (firstLink) {
            setTimeout(() => firstLink.focus(), 100);
        }
    }
    
    closeMenu() {
        if (!this.navLinks || !this.navToggle) return;
        
        this.navLinks.classList.remove('active');
        this.navToggle.setAttribute('aria-expanded', 'false');
        this.navToggle.innerHTML = '☰';
        
        // Restore body scroll
        document.body.style.overflow = '';
    }
    
    setActivePage() {
        if (!this.navLinks) return;
        
        const currentPath = window.location.pathname;
        const links = this.navLinks.querySelectorAll('a');
        
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href && (currentPath.includes(href) || 
                        (currentPath === '/' && href === 'index.html') ||
                        (currentPath === '/' && href === 'translation_society_website.html'))) {
                link.classList.add('active');
                link.setAttribute('aria-current', 'page');
            }
        });
    }
    
    handleResize() {
        // Close mobile menu on desktop
        if (window.innerWidth > 768 && this.navLinks && this.navLinks.classList.contains('active')) {
            this.closeMenu();
        }
    }
}

// ===================================================================
// SMOOTH SCROLL COMPONENT
// ===================================================================

class SmoothScroll {
    constructor() {
        this.init();
    }
    
    init() {
        // Handle anchor links
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href^="#"]');
            if (!link) return;
            
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            const target = document.getElementById(targetId);
            
            if (target) {
                this.scrollToElement(target);
            }
        });
    }
    
    scrollToElement(element, offset = 80) {
        const elementPosition = element.offsetTop - offset;
        
        window.scrollTo({
            top: elementPosition,
            behavior: 'smooth'
        });
        
        // Update focus for accessibility
        setTimeout(() => {
            element.focus({ preventScroll: true });
        }, 500);
    }
}

// ===================================================================
// ANIMATION OBSERVER
// ===================================================================

class AnimationObserver {
    constructor() {
        this.observers = [];
        this.init();
    }
    
    init() {
        if (!('IntersectionObserver' in window)) return;
        
        this.setupScrollAnimations();
        this.setupCounterAnimations();
    }
    
    setupScrollAnimations() {
        const animationObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    
                    // Add stagger delay for grouped elements
                    const siblings = Array.from(element.parentNode.children);
                    const index = siblings.indexOf(element);
                    const delay = index * 100;
                    
                    setTimeout(() => {
                        element.classList.add('fade-in');
                    }, delay);
                    
                    animationObserver.unobserve(element);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        // Observe elements that should animate in
        document.querySelectorAll('.card, .grid > *, .timeline-item').forEach(el => {
            animationObserver.observe(el);
        });
        
        this.observers.push(animationObserver);
    }
    
    setupCounterAnimations() {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        });
        
        document.querySelectorAll('.stat-number, .counter').forEach(el => {
            counterObserver.observe(el);
        });
        
        this.observers.push(counterObserver);
    }
    
    animateCounter(element) {
        const target = parseInt(element.textContent.replace(/[^0-9]/g, ''));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            element.textContent = this.formatNumber(Math.floor(current));
        }, 16);
    }
    
    formatNumber(num) {
        return num.toLocaleString();
    }
    
    recalculate() {
        // Re-trigger observations if needed
        this.observers.forEach(observer => {
            observer.disconnect();
        });
        this.observers = [];
        this.init();
    }
}

// ===================================================================
// FORM HANDLER
// ===================================================================

class FormHandler {
    constructor() {
        this.init();
    }
    
    init() {
        // Handle all form submissions
        document.addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });
        
        // Enhanced form validation
        this.setupFormValidation();
        
        // Setup form accessibility
        this.setupFormAccessibility();
    }
    
    handleFormSubmit(e) {
        const form = e.target;
        if (!form.matches('form')) return;
        
        e.preventDefault();
        
        // Basic form validation
        if (!this.validateForm(form)) {
            return;
        }
        
        // Show loading state
        this.setFormLoading(form, true);
        
        // Simulate form submission (replace with actual submission logic)
        setTimeout(() => {
            this.setFormLoading(form, false);
            this.showFormSuccess(form);
        }, 1500);
    }
    
    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });
        
        // Email validation
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                this.showFieldError(field, 'Please enter a valid email address');
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    showFieldError(field, message) {
        this.clearFieldError(field);
        
        field.classList.add('error');
        field.setAttribute('aria-invalid', 'true');
        
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        errorElement.id = field.id + '-error';
        field.setAttribute('aria-describedby', errorElement.id);
        
        field.parentNode.appendChild(errorElement);
        
        // Announce error to screen readers
        if (window.mtsApp) {
            window.mtsApp.announce(message, 'assertive');
        }
    }
    
    clearFieldError(field) {
        field.classList.remove('error');
        field.removeAttribute('aria-invalid');
        field.removeAttribute('aria-describedby');
        
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }
    
    setFormLoading(form, loading) {
        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        
        if (loading) {
            form.classList.add('loading');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Sending...';
            }
        } else {
            form.classList.remove('loading');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Send Message';
            }
        }
    }
    
    showFormSuccess(form) {
        // Create success message
        const successMessage = document.createElement('div');
        successMessage.className = 'form-success';
        successMessage.innerHTML = `
            <h3>Thank you for your message!</h3>
            <p>We will respond to your inquiry as soon as possible. May your dharma studies be fruitful!</p>
        `;
        
        // Replace form with success message
        form.parentNode.replaceChild(successMessage, form);
        
        // Announce success
        if (window.mtsApp) {
            window.mtsApp.announce('Your message has been sent successfully');
        }
        
        // Focus success message
        successMessage.focus();
    }
    
    setupFormValidation() {
        // Real-time validation on blur
        document.addEventListener('blur', (e) => {
            if (e.target.matches('input, textarea, select')) {
                this.validateField(e.target);
            }
        }, true);
    }
    
    validateField(field) {
        if (field.hasAttribute('required') && !field.value.trim()) {
            this.showFieldError(field, 'This field is required');
        } else if (field.type === 'email' && field.value && !this.isValidEmail(field.value)) {
            this.showFieldError(field, 'Please enter a valid email address');
        } else {
            this.clearFieldError(field);
        }
    }
    
    setupFormAccessibility() {
        // Add required indicators
        document.querySelectorAll('input[required], textarea[required], select[required]').forEach(field => {
            const label = document.querySelector(`label[for="${field.id}"]`);
            if (label && !label.querySelector('.required-indicator')) {
                const indicator = document.createElement('span');
                indicator.className = 'required-indicator';
                indicator.textContent = ' *';
                indicator.setAttribute('aria-label', 'required');
                label.appendChild(indicator);
            }
        });
    }
}

// ===================================================================
// SEARCH HANDLER
// ===================================================================

class SearchHandler {
    constructor() {
        this.searchInputs = document.querySelectorAll('.search-input');
        this.searchButtons = document.querySelectorAll('.search-button');
        this.init();
    }
    
    init() {
        if (this.searchInputs.length === 0) return;
        
        this.setupEventListeners();
        this.setupSearchSuggestions();
    }
    
    setupEventListeners() {
        this.searchInputs.forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.performSearch(input);
                }
            });
            
            // Real-time search suggestions
            input.addEventListener('input', (e) => {
                this.debounce(() => {
                    this.showSuggestions(input);
                }, 300)();
            });
        });
        
        this.searchButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const input = button.parentNode.querySelector('.search-input');
                if (input) {
                    this.performSearch(input);
                }
            });
        });
    }
    
    performSearch(input) {
        const query = input.value.trim();
        if (!query) return;
        
        // Show loading state
        input.classList.add('searching');
        
        // Announce search
        if (window.mtsApp) {
            window.mtsApp.announce(`Searching for ${query}`);
        }
        
        // Simulate search (replace with actual search logic)
        setTimeout(() => {
            input.classList.remove('searching');
            
            // Mock search results
            const categories = ['Tibetan Terms', 'Translated Terms', 'Authors', 'Texts', 'Translations', 'Translators'];
            const mockResults = categories.map(cat => `• ${cat}\n`).join('');
            
            alert(`Searching for: "${query}"\n\nThis would search across:\n${mockResults}`);
            
            console.log('Search query:', query);
        }, 1000);
    }
    
    showSuggestions(input) {
        const query = input.value.trim();
        if (query.length < 2) {
            this.hideSuggestions(input);
            return;
        }
        
        // Mock suggestions (replace with actual suggestion logic)
        const mockSuggestions = [
            'Dharma', 'Buddha', 'Meditation', 'Enlightenment', 'Karma',
            'Sangha', 'Nirvana', 'Compassion', 'Wisdom', 'Mindfulness'
        ].filter(term => term.toLowerCase().includes(query.toLowerCase()));
        
        if (mockSuggestions.length > 0) {
            this.createSuggestionsList(input, mockSuggestions);
        } else {
            this.hideSuggestions(input);
        }
    }
    
    createSuggestionsList(input, suggestions) {
        // Remove existing suggestions
        this.hideSuggestions(input);
        
        const suggestionsList = document.createElement('ul');
        suggestionsList.className = 'search-suggestions';
        suggestionsList.setAttribute('role', 'listbox');
        
        suggestions.slice(0, 5).forEach((suggestion, index) => {
            const listItem = document.createElement('li');
            listItem.className = 'search-suggestion';
            listItem.textContent = suggestion;
            listItem.setAttribute('role', 'option');
            listItem.addEventListener('click', () => {
                input.value = suggestion;
                this.performSearch(input);
                this.hideSuggestions(input);
            });
            
            suggestionsList.appendChild(listItem);
        });
        
        input.parentNode.appendChild(suggestionsList);
    }
    
    hideSuggestions(input) {
        const existingSuggestions = input.parentNode.querySelector('.search-suggestions');
        if (existingSuggestions) {
            existingSuggestions.remove();
        }
    }
    
    setupSearchSuggestions() {
        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            this.searchInputs.forEach(input => {
                if (!input.parentNode.contains(e.target)) {
                    this.hideSuggestions(input);
                }
            });
        });
    }
    
    debounce(func, wait) {
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
}

// ===================================================================
// FILTER HANDLER
// ===================================================================

class FilterHandler {
    constructor() {
        this.filterForms = document.querySelectorAll('.filters-sidebar, .filter-container');
        this.init();
    }
    
    init() {
        if (this.filterForms.length === 0) return;
        
        this.setupEventListeners();
        this.setupMobileFilters();
    }
    
    setupEventListeners() {
        // Handle filter changes
        document.addEventListener('change', (e) => {
            if (e.target.matches('input[type="checkbox"], select, input[type="radio"]') && 
                e.target.closest('.filters-sidebar, .filter-container')) {
                this.debounce(() => {
                    this.applyFilters();
                }, 300)();
            }
        });
        
        // Clear filters button
        const clearButtons = document.querySelectorAll('.clear-filters');
        clearButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.clearAllFilters();
            });
        });
        
        // Sort dropdown
        const sortSelects = document.querySelectorAll('#sort-select');
        sortSelects.forEach(select => {
            select.addEventListener('change', () => {
                this.applySorting(select.value);
            });
        });
    }
    
    setupMobileFilters() {
        const mobileToggle = document.querySelector('.mobile-filter-toggle');
        const sidebar = document.querySelector('.filters-sidebar');
        
        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-open');
                const isOpen = sidebar.classList.contains('mobile-open');
                mobileToggle.setAttribute('aria-expanded', isOpen);
                
                if (isOpen) {
                    // Prevent body scroll
                    document.body.style.overflow = 'hidden';
                } else {
                    // Restore body scroll
                    document.body.style.overflow = '';
                }
            });
            
            // Close filters when clicking outside
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 1024 &&
                    !sidebar.contains(e.target) &&
                    !mobileToggle.contains(e.target) &&
                    sidebar.classList.contains('mobile-open')) {
                    sidebar.classList.remove('mobile-open');
                    mobileToggle.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                }
            });
        }
    }
    
    applyFilters() {
        const filters = this.getActiveFilters();
        console.log('Applying filters:', filters);
        
        // Show loading state
        this.setFilteringState(true);
        
        // Simulate filtering
        setTimeout(() => {
            this.setFilteringState(false);
            this.updateResultsCount(filters);
            
            // Announce results
            const count = this.calculateResultsCount(filters);
            if (window.mtsApp) {
                window.mtsApp.announce(`${count} results found`);
            }
        }, 500);
    }
    
    getActiveFilters() {
        const filters = {
            languages: [],
            types: [],
            authors: [],
            prices: [],
            search: ''
        };
        
        // Get checked checkboxes
        document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            const name = checkbox.name;
            if (filters[name]) {
                filters[name].push(checkbox.value);
            }
        });
        
        // Get search input
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            filters.search = searchInput.value.trim();
        }
        
        return filters;
    }
    
    calculateResultsCount(filters) {
        // Mock calculation based on filters
        let baseCount = 70;
        
        Object.entries(filters).forEach(([key, value]) => {
            if (Array.isArray(value) && value.length > 0) {
                baseCount -= value.length * 5;
            } else if (typeof value === 'string' && value) {
                baseCount -= 10;
            }
        });
        
        return Math.max(0, baseCount);
    }
    
    updateResultsCount(filters) {
        const resultsCountElement = document.querySelector('.results-count');
        if (resultsCountElement) {
            const count = this.calculateResultsCount(filters);
            resultsCountElement.textContent = count;
        }
    }
    
    clearAllFilters() {
        // Clear all checkboxes
        document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Clear search input
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.value = '';
        }
        
        // Reset sort
        const sortSelect = document.querySelector('#sort-select');
        if (sortSelect) {
            sortSelect.value = 'relevance';
        }
        
        // Apply filters
        this.applyFilters();
        
        // Announce clearing
        if (window.mtsApp) {
            window.mtsApp.announce('All filters cleared');
        }
    }
    
    applySorting(sortValue) {
        console.log('Sorting by:', sortValue);
        
        // Show loading state
        this.setFilteringState(true);
        
        // Simulate sorting
        setTimeout(() => {
            this.setFilteringState(false);
            
            if (window.mtsApp) {
                window.mtsApp.announce(`Results sorted by ${sortValue}`);
            }
        }, 300);
    }
    
    setFilteringState(filtering) {
        const resultsArea = document.querySelector('.products-grid, .catalog-main');
        if (resultsArea) {
            if (filtering) {
                resultsArea.classList.add('filtering');
                resultsArea.style.opacity = '0.6';
            } else {
                resultsArea.classList.remove('filtering');
                resultsArea.style.opacity = '';
            }
        }
    }
    
    debounce(func, wait) {
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
}

// ===================================================================
// UTILITY FUNCTIONS
// ===================================================================

// Global utilities
window.MTS = {
    // Smooth scroll to element
    scrollTo: (element, offset = 80) => {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (element) {
            const elementPosition = element.offsetTop - offset;
            window.scrollTo({
                top: elementPosition,
                behavior: 'smooth'
            });
        }
    },
    
    // Format numbers with localization
    formatNumber: (num) => {
        return num.toLocaleString();
    },
    
    // Debounce function
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Throttle function
    throttle: (func, limit) => {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};

// ===================================================================
// INITIALIZE APPLICATION
// ===================================================================

// Create global instance
window.mtsApp = new MTSWebsite();

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MTSWebsite;
}