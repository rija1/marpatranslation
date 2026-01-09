/**
 * Catalog functionality for Marpa Translation Society
 * 
 * Handles filtering, search, and AJAX interactions for the translation catalog
 */

jQuery(document).ready(function($) {
    'use strict';

    // Initialize catalog functionality
    const Catalog = {
        
        // Properties
        $filtersForm: null,
        $searchInput: null,
        $searchBtn: null,
        $applyBtn: null,
        $clearBtn: null,
        $catalogMain: null,
        $translationList: null,
        isLoading: false,

        // Initialize
        init: function() {
            this.bindElements();
            this.bindEvents();
            this.initUrlParams();
        },

        // Bind DOM elements
        bindElements: function() {
            this.$searchInput = $('#catalog-search');
            this.$searchBtn = $('#catalog-search-btn');
            this.$applyBtn = $('#apply-filters');
            this.$clearBtn = $('#clear-filters');
            this.$catalogMain = $('.catalog-main');
            this.$translationList = $('.translation-list');
        },

        // Bind events
        bindEvents: function() {
            // Search functionality
            this.$searchBtn.on('click', this.performSearch.bind(this));
            this.$searchInput.on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    Catalog.performSearch();
                }
            });

            // Filter functionality
            this.$applyBtn.on('click', this.applyFilters.bind(this));
            this.$clearBtn.on('click', this.clearFilters.bind(this));

            // Auto-apply filters on checkbox change
            $('.filters-sidebar input[type="checkbox"], .filters-sidebar select').on('change', function() {
                // Add a small delay to allow for multiple rapid changes
                clearTimeout(Catalog.filterTimeout);
                Catalog.filterTimeout = setTimeout(function() {
                    Catalog.applyFilters();
                }, 500);
            });

            // Action links
            $(document).on('click', '.download-pdf', this.handleDownload.bind(this));
            $(document).on('click', '.view-terms', this.handleViewTerms.bind(this));
            $(document).on('click', '.get-notified', this.handleNotification.bind(this));
            $(document).on('click', '.view-progress', this.handleProgress.bind(this));
        },

        // Initialize from URL parameters
        initUrlParams: function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            // Set search value
            const searchTerm = urlParams.get('catalog_search');
            if (searchTerm) {
                this.$searchInput.val(searchTerm);
            }

            // Set filter values
            const status = urlParams.getAll('product_status[]');
            const tradition = urlParams.getAll('product_tradition[]');
            const textType = urlParams.getAll('product_text_type[]');
            const topic = urlParams.getAll('product_topic[]');
            const languagePair = urlParams.get('language_pair');

            // Check appropriate checkboxes
            status.forEach(function(value) {
                $('input[name="product_status[]"][value="' + value + '"]').prop('checked', true);
            });

            tradition.forEach(function(value) {
                $('input[name="product_tradition[]"][value="' + value + '"]').prop('checked', true);
            });

            textType.forEach(function(value) {
                $('input[name="product_text_type[]"][value="' + value + '"]').prop('checked', true);
            });

            topic.forEach(function(value) {
                $('input[name="product_topic[]"][value="' + value + '"]').prop('checked', true);
            });

            if (languagePair) {
                $('select[name="language_pair"]').val(languagePair);
            }
        },

        // Perform search
        performSearch: function() {
            const searchTerm = this.$searchInput.val().trim();
            this.updateUrl({ catalog_search: searchTerm });
            this.loadProducts();
        },

        // Apply filters
        applyFilters: function() {
            if (this.isLoading) return;

            const filters = this.getFilterData();
            this.updateUrl(filters);
            this.loadProducts();
        },

        // Clear all filters
        clearFilters: function() {
            // Clear checkboxes
            $('.filters-sidebar input[type="checkbox"]').prop('checked', false);
            
            // Reset select
            $('.filters-sidebar select').val('');
            
            // Clear search
            this.$searchInput.val('');
            
            // Update URL
            this.updateUrl({});
            
            // Reload products
            this.loadProducts();
        },

        // Get filter data from form
        getFilterData: function() {
            const data = {};

            // Get checked status filters
            const status = [];
            $('input[name="product_status[]"]:checked').each(function() {
                status.push($(this).val());
            });
            if (status.length) data.product_status = status;

            // Get checked tradition filters
            const tradition = [];
            $('input[name="product_tradition[]"]:checked').each(function() {
                tradition.push($(this).val());
            });
            if (tradition.length) data.product_tradition = tradition;

            // Get checked text type filters
            const textType = [];
            $('input[name="product_text_type[]"]:checked').each(function() {
                textType.push($(this).val());
            });
            if (textType.length) data.product_text_type = textType;

            // Get checked topic filters
            const topic = [];
            $('input[name="product_topic[]"]:checked').each(function() {
                topic.push($(this).val());
            });
            if (topic.length) data.product_topic = topic;

            // Get language pair
            const languagePair = $('select[name="language_pair"]').val();
            if (languagePair) data.language_pair = languagePair;

            // Get search term
            const searchTerm = this.$searchInput.val().trim();
            if (searchTerm) data.catalog_search = searchTerm;

            return data;
        },

        // Update URL with new parameters
        updateUrl: function(params) {
            const url = new URL(window.location);
            
            // Clear existing catalog parameters
            url.searchParams.delete('catalog_search');
            url.searchParams.delete('product_status[]');
            url.searchParams.delete('product_tradition[]');
            url.searchParams.delete('product_text_type[]');
            url.searchParams.delete('product_topic[]');
            url.searchParams.delete('language_pair');

            // Add new parameters
            Object.keys(params).forEach(key => {
                if (Array.isArray(params[key])) {
                    params[key].forEach(value => {
                        url.searchParams.append(key + '[]', value);
                    });
                } else if (params[key]) {
                    url.searchParams.set(key, params[key]);
                }
            });

            // Update browser history
            window.history.replaceState({}, '', url);
        },

        // Load products via AJAX
        loadProducts: function() {
            if (this.isLoading) return;

            this.isLoading = true;
            this.showLoading();

            const filterData = this.getFilterData();
            
            // Prepare AJAX data
            const ajaxData = {
                action: 'filter_products',
                nonce: catalog_ajax.nonce,
                status: filterData.product_status || [],
                tradition: filterData.product_tradition || [],
                text_type: filterData.product_text_type || [],
                topic: filterData.product_topic || [],
                language_pair: filterData.language_pair || '',
                search: filterData.catalog_search || ''
            };

            $.ajax({
                url: catalog_ajax.ajax_url,
                type: 'POST',
                data: ajaxData,
                success: this.handleLoadSuccess.bind(this),
                error: this.handleLoadError.bind(this),
                complete: this.handleLoadComplete.bind(this)
            });
        },

        // Handle successful product load
        handleLoadSuccess: function(response) {
            if (response.success && response.data) {
                this.$translationList.html(response.data.html);
                this.updateResultsCount(response.data.found_posts);
            } else {
                this.showError('Failed to load translations.');
            }
        },

        // Handle load error
        handleLoadError: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            this.showError('An error occurred while loading translations.');
        },

        // Handle load complete
        handleLoadComplete: function() {
            this.isLoading = false;
            this.hideLoading();
        },

        // Show loading indicator
        showLoading: function() {
            this.$catalogMain.addClass('loading');
            this.$translationList.css('opacity', '0.5');
        },

        // Hide loading indicator
        hideLoading: function() {
            this.$catalogMain.removeClass('loading');
            this.$translationList.css('opacity', '1');
        },

        // Update results count
        updateResultsCount: function(total) {
            $('.catalog-count strong').last().text(total);
        },

        // Show error message
        showError: function(message) {
            const errorHtml = '<div class="catalog-error"><p>' + message + '</p></div>';
            this.$translationList.html(errorHtml);
        },

        // Handle download action
        handleDownload: function(e) {
            e.preventDefault();
            const productId = $(e.currentTarget).data('product-id');
            
            // In a real implementation, this would trigger a download
            // For now, show a placeholder message
            alert('Download functionality would be implemented here for product ' + productId);
        },

        // Handle view terms action
        handleViewTerms: function(e) {
            e.preventDefault();
            const productId = $(e.currentTarget).data('product-id');
            
            // In a real implementation, this would open a terms modal or page
            alert('Terms view functionality would be implemented here for product ' + productId);
        },

        // Handle notification signup
        handleNotification: function(e) {
            e.preventDefault();
            const productId = $(e.currentTarget).data('product-id');
            
            // In a real implementation, this would handle email signup
            alert('Notification signup functionality would be implemented here for product ' + productId);
        },

        // Handle progress view
        handleProgress: function(e) {
            e.preventDefault();
            const productId = $(e.currentTarget).data('product-id');
            
            // In a real implementation, this would show progress details
            alert('Progress view functionality would be implemented here for product ' + productId);
        }
    };

    // Initialize catalog when DOM is ready
    Catalog.init();

    // Export for external use
    window.MTS_Catalog = Catalog;
});