/**
 * Custom JavaScript Functions for Marpa Translation Theme
 * 
 * Add your custom JavaScript functions here.
 * This file is enqueued in the theme and available on all pages.
 */

(function($) {
    'use strict';

    // Document ready
    $(document).ready(function() {
        // Initialize custom functions when DOM is ready
        initCustomFunctions();
    });

    /**
     * Initialize custom functions
     */
    function initCustomFunctions() {
        // Add your initialization code here
        console.log('Marpa Translation custom scripts loaded');
        
        // Initialize glossary autocomplete
        initGlossaryAutocomplete();
        
        // Handle term anchor on page load
        handleTermAnchor();
    }

    // Add your custom functions below this line
    
    /**
     * Glossary Autocomplete Search
     */
    function initGlossaryAutocomplete() {
        const searchInput = $('#glossary-autocomplete');
        if (!searchInput.length) return;
        
        let searchTimeout;
        let resultsContainer = $('<div class="glossary-autocomplete-results"></div>');
        searchInput.after(resultsContainer);
        
        searchInput.on('input', function() {
            const query = $(this).val().trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                resultsContainer.hide().empty();
                // Clear filter if search is empty
                if (query.length === 0) {
                    clearFilter();
                }
                return;
            }
            
            // Only show autocomplete dropdown, no live filtering
            searchTimeout = setTimeout(function() {
                performGlossarySearch(query, resultsContainer);
            }, 300);
        });
        
        // Hide results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.glossary-search').length) {
                resultsContainer.hide();
            }
        });
        
        // Hide results on escape key
        searchInput.on('keydown', function(e) {
            if (e.keyCode === 27) { // Escape key
                resultsContainer.hide();
            }
        });
    }
    
    /**
     * Perform AJAX search for glossary terms
     */
    function performGlossarySearch(query, resultsContainer) {
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'GET',
            data: {
                action: 'glossary_autocomplete',
                q: query
            },
            beforeSend: function() {
                resultsContainer.html('<div class="autocomplete-loading">Searching...</div>').show();
            },
            success: function(response) {
                if (response && response.length > 0) {
                    let html = '<ul class="autocomplete-list">';
                    response.forEach(function(term) {
                        html += '<li class="autocomplete-item" data-term="' + $('<div>').text(term).html() + '">' + term + '</li>';
                    });
                    html += '</ul>';
                    resultsContainer.html(html).show();
                } else {
                    resultsContainer.html('<div class="autocomplete-no-results">No terms found</div>').show();
                }
            },
            error: function() {
                resultsContainer.html('<div class="autocomplete-error">Search error occurred</div>').show();
            }
        });
    }
    
    /**
     * Handle autocomplete item selection
     */
    $(document).on('click', '.autocomplete-item', function() {
        const term = $(this).data('term');
        const searchInput = $('#glossary-autocomplete');
        const resultsContainer = $('.glossary-autocomplete-results');
        
        searchInput.val(term);
        resultsContainer.hide();
        
        // Filter to show only the selected term
        filterGlossaryEntries(term);
    });
    
    /**
     * Filter glossary entries to show only matching term
     */
    function filterGlossaryEntries(searchTerm) {
        const glossaryEntries = $('.glossary-entry-item, .glossary-entry');
        const pagination = $('.pods-pagination');
        let matchFound = false;
        let exactMatch = null;
        
        // First pass: look for exact match in multiple possible selectors
        glossaryEntries.each(function() {
            const $entry = $(this);
            let termText = '';
            
            // Try different selectors for the term text
            const $termElement = $entry.find('.term-name, .glossary-term, h3, h4').first();
            if ($termElement.length) {
                termText = $termElement.text().trim();
            } else {
                // Fallback: look for any heading or strong text
                const $fallback = $entry.find('h1, h2, h3, h4, h5, h6, strong').first();
                if ($fallback.length) {
                    termText = $fallback.text().trim();
                }
            }
            
            if (termText && termText.toLowerCase() === searchTerm.toLowerCase()) {
                exactMatch = $entry;
                return false; // Break loop
            }
        });
        
        // If exact match found, show only that
        if (exactMatch) {
            glossaryEntries.hide();
            exactMatch.show().addClass('highlighted');
            pagination.hide();
            matchFound = true;
            
            // Scroll to the entry
            $('html, body').animate({
                scrollTop: exactMatch.offset().top - 100
            }, 300);
            
            // Add clear filter button
            addClearFilterButton();
        } else {
            // No exact match, look for partial matches
            glossaryEntries.each(function() {
                const $entry = $(this);
                let termText = '';
                
                // Try different selectors for the term text
                const $termElement = $entry.find('.term-name, .glossary-term, h3, h4').first();
                if ($termElement.length) {
                    termText = $termElement.text().trim();
                } else {
                    // Fallback: look for any heading or strong text
                    const $fallback = $entry.find('h1, h2, h3, h4, h5, h6, strong').first();
                    if ($fallback.length) {
                        termText = $fallback.text().trim();
                    }
                }
                
                if (termText && termText.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1) {
                    $entry.show().addClass('highlighted');
                    matchFound = true;
                } else {
                    $entry.hide();
                }
            });
            
            if (matchFound) {
                pagination.hide();
                addClearFilterButton();
                
                // Scroll to first visible entry
                const firstVisible = $('.glossary-entry-item:visible:first, .glossary-entry:visible:first');
                if (firstVisible.length) {
                    $('html, body').animate({
                        scrollTop: firstVisible.offset().top - 100
                    }, 300);
                }
            } else {
                // No matches found, load the term from database
                loadSpecificTerm(searchTerm);
            }
        }
    }
    
    /**
     * Load a specific term from database
     */
    function loadSpecificTerm(searchTerm) {
        showSearchMessage('Loading term...', 'loading');
        
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'GET',
            data: {
                action: 'load_glossary_term',
                term: searchTerm
            },
            success: function(response) {
                $('.glossary-search-message').remove();
                
                if (response.success && response.data.html) {
                    // Hide all current entries and pagination
                    $('.glossary-entry').hide();
                    $('.pods-pagination').hide();
                    
                    // Insert the loaded term
                    $('.entry-content').append(response.data.html);
                    
                    // Highlight and scroll to new entry
                    const newEntry = $('.glossary-entry').last();
                    newEntry.addClass('highlighted');
                    
                    $('html, body').animate({
                        scrollTop: newEntry.offset().top - 100
                    }, 300);
                    
                    addClearFilterButton();
                } else {
                    showSearchMessage('Term not found in glossary', 'error');
                }
            },
            error: function() {
                $('.glossary-search-message').remove();
                showSearchMessage('Error loading term', 'error');
            }
        });
    }
    
    /**
     * Add clear filter button
     */
    function addClearFilterButton() {
        if ($('.clear-filter-btn').length) return; // Already exists
        
        const clearBtn = $('<button class="clear-filter-btn">Show All Terms</button>');
        $('.glossary-search').after(clearBtn);
        
        clearBtn.on('click', function() {
            clearFilter();
        });
    }
    
    /**
     * Clear the filter and show all entries
     */
    function clearFilter() {
        $('.glossary-entry-item, .glossary-entry').show().removeClass('highlighted');
        $('.pods-pagination').show();
        $('.clear-filter-btn').remove();
        $('.glossary-search-message').remove();
        $('#glossary-autocomplete').val('');
        
        // Remove any dynamically loaded entries
        $('.glossary-entry.loaded-term, .glossary-entry-item.loaded-term').remove();
        
        // Scroll back to top
        $('html, body').animate({
            scrollTop: $('.glossary-search').offset().top - 100
        }, 300);
    }
    
    /**
     * Show search message to user
     */
    function showSearchMessage(message, type) {
        $('.glossary-search-message').remove(); // Remove any existing messages
        
        const messageHtml = '<div class="glossary-search-message ' + type + '">' + message + '</div>';
        const $message = $(messageHtml);
        
        $('.glossary-search').append($message);
        
        // Auto-remove after 3 seconds for non-loading messages
        if (type !== 'loading') {
            setTimeout(function() {
                $message.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    }
    
    /**
     * Handle term anchor on page load (for direct links to terms)
     */
    function handleTermAnchor() {
        const hash = window.location.hash;
        if (hash && hash.startsWith('#term-')) {
            const termName = decodeURIComponent(hash.replace('#term-', '')).replace(/-/g, ' ');
            if (termName) {
                setTimeout(function() {
                    filterGlossaryEntries(termName);
                }, 500);
            }
        }
    }

})(jQuery);