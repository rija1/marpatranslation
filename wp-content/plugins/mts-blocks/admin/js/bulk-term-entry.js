/**
 * Bulk Term Usage Entry - JavaScript Interface
 * Handles term creation, validation, and AJAX operations with autocomplete
 */

(function($) {
    'use strict';
    
    let rowCounter = 1;
    let validationTimeout;
    
    $(document).ready(function() {
        initializeInterface();
        bindEvents();
        addInitialRow();
    });
    
    /**
     * Initialize the interface
     */
    function initializeInterface() {
        // Initialize text autocomplete
        $('#text-search').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: bulkTermAjax.ajax_url,
                    data: {
                        action: 'get_text_suggestions',
                        term: request.term
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            select: function(event, ui) {
                $('#selected-text-id').val(ui.item.id);
                $('.current-text-info .text-title').text(ui.item.label);
                $('.current-text-info').show();
                $('.text-search-container').hide();
                $('.bulk-entry-section').show();
                return false;
            },
            minLength: 2
        });
    }
    
    /**
     * Bind events
     */
    function bindEvents() {
        // Add row button
        $(document).on('click', '.add-row-btn', addNewRow);
        
        // Remove row button
        $(document).on('click', '.remove-row-btn', removeRow);
        
        // Change text button
        $(document).on('click', '.change-text-btn', function() {
            $('.current-text-info').hide();
            $('.text-search-container').show();
            $('.bulk-entry-section').hide();
            $('#text-search').val('').focus();
            $('#selected-text-id').val('');
        });
        
        // Save all button
        $(document).on('click', '.save-all-btn', saveAllEntries);
        
        
        // CSV import
        $(document).on('click', '.import-csv-btn', showCsvModal);
        $(document).on('click', '.close-modal', hideCsvModal);
        $(document).on('change', '#csv-file-input', handleCsvFile);
        $(document).on('click', '.import-csv-confirm', importCsvData);
        
        // Term autocomplete
        $(document).on('input', '.tibetan-input, .translated-input, .translations-input', handleTermAutocomplete);
        $(document).on('click', '.term-suggestion', selectTermSuggestion);
        $(document).on('focus', '.tibetan-input, .translated-input, .translations-input', function() {
            if ($(this).val().length >= 2) {
                handleTermAutocomplete.call(this);
            }
        });
        $(document).on('blur', '.tibetan-input, .translated-input, .translations-input', function() {
            setTimeout(() => {
                $(this).siblings('.term-suggestions').hide();
            }, 200);
        });
        
        // Multi-select for translations
        $(document).on('click', '.selected-item-remove', removeSelectedItem);
        
        // Term validation on change
        $(document).on('input', '.tibetan-input, .translated-input', function() {
            const $row = $(this).closest('tr');
            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => {
                validateRow($row);
            }, 500);
        });
        
        // Enter key to add new row
        $(document).on('keypress', '.usage-context-input', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                addNewRow();
            }
        });
    }
    
    /**
     * Add initial empty row
     */
    function addInitialRow() {
        addNewRow();
    }
    
    
    /**
     * Add new entry card
     */
    function addNewRow() {
        const cardHtml = `
            <div class="entry-card" data-row="${rowCounter}">
                <div class="card-header">
                    <span class="card-number">#${rowCounter}</span>
                    <button type="button" class="button remove-row-btn" title="Remove this entry">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
                
                <div class="card-content">
                    <!-- Row 1: Core Terms -->
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label required">Tibetan Term</label>
                            <div class="autocomplete-container">
                                <input type="text" class="tibetan-input regular-text" placeholder="Start typing Tibetan term..." />
                                <div class="term-suggestions" style="display:none;"></div>
                            </div>
                        </div>
                        
                        <div class="field-group">
                            <label class="field-label required">Translated Term</label>
                            <div class="autocomplete-container">
                                <input type="text" class="translated-input regular-text" placeholder="Start typing translated term..." />
                                <div class="term-suggestions" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Row 2: Related Translations -->
                    <div class="field-row">
                        <div class="field-group full-width">
                            <label class="field-label">Related Translation</label>
                            <div class="autocomplete-container">
                                <input type="text" class="translations-input regular-text" placeholder="Start typing translation name..." />
                                <input type="hidden" name="translations" class="translations-hidden-input" />
                                <div class="term-suggestions" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Row 3: Quotes -->
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Quote (Tibetan)</label>
                            <textarea class="quote-tib-input regular-text" rows="3" placeholder="Tibetan quote..."></textarea>
                        </div>
                        
                        <div class="field-group">
                            <label class="field-label">Quote (Target Language)</label>
                            <textarea class="quote-target-input regular-text" rows="3" placeholder="Quote in target language..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Row 4: Reference and Note -->
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Quote Reference</label>
                            <input type="text" class="quote-ref-input regular-text" placeholder="Quote reference..." />
                        </div>
                        
                        <div class="field-group">
                            <label class="field-label">Translator Note</label>
                            <textarea class="translator-note-input regular-text" rows="2" placeholder="Translator note..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#entry-cards-container').append(cardHtml);
        
        // Focus on the new card's first input
        $(`[data-row="${rowCounter}"] .tibetan-input`).focus();
        
        rowCounter++;
        updateRowNumbers();
    }
    
    /**
     * Remove a row
     */
    function removeRow() {
        const $row = $(this).closest('tr');
        
        // Don't remove if it's the only row
        if ($('#entry-table-body tr').length === 1) {
            alert('At least one row is required.');
            return;
        }
        
        $row.remove();
        updateRowNumbers();
    }
    
    /**
     * Update row numbers after adding/removing rows
     */
    function updateRowNumbers() {
        $('#entry-table-body tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
            $(this).attr('data-row', index + 1);
        });
    }
    
    /**
     * Handle term autocomplete input
     */
    function handleTermAutocomplete() {
        const $input = $(this);
        const searchTerm = $input.val();
        let postType;
        
        if ($input.hasClass('translated-input')) {
            postType = 'translated_term';
        } else if ($input.hasClass('translations-input')) {
            postType = 'translation';
        } else {
            postType = 'tibetan_term';
        }
        
        if (searchTerm.length < 2) {
            $input.siblings('.term-suggestions').hide();
            return;
        }
        
        // Debounce the search
        clearTimeout($input.data('searchTimeout'));
        const timeout = setTimeout(() => {
            loadTermSuggestions($input, postType, searchTerm);
        }, 300);
        $input.data('searchTimeout', timeout);
    }
    
    /**
     * Load term suggestions
     */
    function loadTermSuggestions($input, postType, searchTerm) {
        $.ajax({
            url: bulkTermAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'get_reference_options',
                nonce: bulkTermAjax.nonce,
                post_type: postType,
                search_term: searchTerm
            },
            success: function(response) {
                if (response.success) {
                    showTermSuggestionsForInput($input, response.data);
                }
            }
        });
    }
    
    /**
     * Show term suggestions
     */
    function showTermSuggestions() {
        const $input = $(this);
        const $suggestions = $input.siblings('.term-suggestions');
        
        if ($suggestions.children().length > 0) {
            $suggestions.show();
        } else if ($input.val().length >= 2) {
            $input.trigger('input');
        }
    }
    
    /**
     * Show term suggestions for specific input
     */
    function showTermSuggestionsForInput($input, suggestions) {
        const $suggestionsDiv = $input.siblings('.term-suggestions');
        
        let html = '';
        suggestions.forEach(suggestion => {
            html += `<div class="term-suggestion autocomplete-suggestion" data-id="${suggestion.id}" data-label="${suggestion.label}">
                ${suggestion.label}
            </div>`;
        });
        
        $suggestionsDiv.html(html);
        $suggestionsDiv.show();
    }
    
    /**
     * Select term suggestion
     */
    function selectTermSuggestion() {
        const $suggestion = $(this);
        const $container = $suggestion.closest('.autocomplete-container');
        const $input = $container.find('input[type="text"]');
        const selectedId = $suggestion.data('id');
        const selectedLabel = $suggestion.data('label');
        
        if ($input.hasClass('translations-input')) {
            // Multi-select for translations
            const $selectedItems = $container.find('.selected-items');
            const $hiddenInput = $container.find('.translations-hidden-input');
            
            // Check if already selected
            if ($selectedItems.find(`[data-id="${selectedId}"]`).length > 0) {
                $suggestion.closest('.term-suggestions').hide();
                return;
            }
            
            // Add selected item
            const selectedHtml = `<span class="selected-item" data-id="${selectedId}">
                ${selectedLabel}
                <span class="selected-item-remove" data-id="${selectedId}">&times;</span>
            </span>`;
            $selectedItems.append(selectedHtml);
            
            // Update hidden input
            const currentValues = $hiddenInput.val() ? $hiddenInput.val().split(',') : [];
            currentValues.push(selectedId);
            $hiddenInput.val(currentValues.join(','));
            
            // Clear input
            $input.val('');
        } else {
            // Single select for terms
            $input.val(selectedLabel);
            
            // Trigger validation for term fields
            const $row = $input.closest('tr');
            setTimeout(() => validateRow($row), 100);
        }
        
        $suggestion.closest('.term-suggestions').hide();
    }
    
    /**
     * Remove selected item (for multi-select)
     */
    function removeSelectedItem() {
        const $removeBtn = $(this);
        const $container = $removeBtn.closest('.autocomplete-container');
        const $hiddenInput = $container.find('.translations-hidden-input');
        const $selectedItem = $removeBtn.closest('.selected-item');
        const removedId = $removeBtn.data('id');
        
        // Remove from hidden input
        const currentValues = $hiddenInput.val() ? $hiddenInput.val().split(',') : [];
        const newValues = currentValues.filter(value => value != removedId);
        $hiddenInput.val(newValues.join(','));
        
        // Remove visual element
        $selectedItem.remove();
    }
    
    /**
     * Remove a row
     */
    function removeRow() {
        const $row = $(this).closest('tr');
        
        // Don't remove if it's the only row
        if ($('#entry-table-body tr').length === 1) {
            alert('At least one row is required.');
            return;
        }
        
        $row.remove();
        updateRowNumbers();
    }
    
    /**
     * Update row numbers after adding/removing rows
     */
    function updateRowNumbers() {
        $('#entry-table-body tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
            $(this).attr('data-row', index + 1);
        });
    }
    
    /**
     * Validate a row's terms
     */
    function validateRow($row) {
        const tibetanTerm = $row.find('.tibetan-input').val().trim();
        const translatedTerm = $row.find('.translated-input').val().trim();
        
        if (!tibetanTerm || !translatedTerm) {
            $row.find('.status-indicators span').removeClass('status-exists status-create').addClass('status-unknown');
            return;
        }
        
        // Show loading state
        $row.find('.status-indicators span').removeClass('status-exists status-create status-unknown').addClass('status-loading');
        
        $.ajax({
            url: bulkTermAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'validate_terms',
                nonce: bulkTermAjax.nonce,
                tibetan_term: tibetanTerm,
                translated_term: translatedTerm
            },
            success: function(response) {
                if (response.success) {
                    updateRowStatus($row, response.data);
                } else {
                    console.error('Validation failed:', response.data);
                }
            },
            error: function() {
                $row.find('.status-indicators span').removeClass('status-loading').addClass('status-error');
            }
        });
    }
    
    /**
     * Update row status indicators
     */
    function updateRowStatus($row, data) {
        const $tibetanStatus = $row.find('.tibetan-status');
        const $translatedStatus = $row.find('.translated-status');
        
        // Update Tibetan term status
        $tibetanStatus.removeClass('status-loading status-unknown status-error');
        if (data.tibetan.exists) {
            $tibetanStatus.addClass('status-exists').text('✓').attr('title', 'Tibetan term exists');
        } else {
            $tibetanStatus.addClass('status-create').text('+').attr('title', 'Will create Tibetan term');
        }
        
        // Update Translated term status
        $translatedStatus.removeClass('status-loading status-unknown status-error');
        if (data.translated.exists) {
            $translatedStatus.addClass('status-exists').text('✓').attr('title', 'Translated term exists');
        } else {
            $translatedStatus.addClass('status-create').text('+').attr('title', 'Will create Translated term');
        }
    }
    
    /**
     * Handle autocomplete input
     */
    function handleAutocompleteInput() {
        const $input = $(this);
        const searchTerm = $input.val();
        const pickObject = $input.data('pick-object');
        const pickVal = $input.data('pick-val');
        const postType = pickVal || pickObject;
        
        if (searchTerm.length < 2) {
            $input.siblings('.autocomplete-suggestions').hide();
            return;
        }
        
        // Debounce the search
        clearTimeout($input.data('searchTimeout'));
        const timeout = setTimeout(() => {
            loadAutocompleteSuggestions($input, postType, searchTerm);
        }, 300);
        $input.data('searchTimeout', timeout);
    }
    
    /**
     * Load autocomplete suggestions
     */
    function loadAutocompleteSuggestions($input, postType, searchTerm) {
        $.ajax({
            url: bulkTermAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'get_reference_options',
                nonce: bulkTermAjax.nonce,
                post_type: postType,
                search_term: searchTerm
            },
            success: function(response) {
                if (response.success) {
                    showAutocompleteSuggestionsForInput($input, response.data);
                }
            }
        });
    }
    
    /**
     * Show autocomplete suggestions
     */
    function showAutocompleteSuggestions() {
        const $input = $(this);
        const $suggestions = $input.siblings('.autocomplete-suggestions');
        
        if ($suggestions.children().length > 0) {
            $suggestions.show();
        } else if ($input.val().length >= 2) {
            // Trigger search if we have enough characters
            $input.trigger('input');
        }
    }
    
    /**
     * Show autocomplete suggestions for specific input
     */
    function showAutocompleteSuggestionsForInput($input, suggestions) {
        const $suggestionsDiv = $input.siblings('.autocomplete-suggestions');
        const isMultiple = $input.data('multiple') === 'true';
        const fieldName = $input.data('field-name');
        
        let html = '';
        suggestions.forEach(suggestion => {
            // Don't show already selected items in multi-select
            if (isMultiple) {
                const $selectedItems = $input.siblings('.selected-items');
                const alreadySelected = $selectedItems.find(`[data-id="${suggestion.id}"]`).length > 0;
                if (alreadySelected) return;
            }
            
            html += `<div class="autocomplete-suggestion" data-id="${suggestion.id}" data-label="${suggestion.label}" data-field="${fieldName}">
                ${suggestion.label}
            </div>`;
        });
        
        $suggestionsDiv.html(html);
        $suggestionsDiv.show();
    }
    
    /**
     * Select autocomplete suggestion
     */
    function selectAutocompleteSuggestion() {
        const $suggestion = $(this);
        const $container = $suggestion.closest('.autocomplete-container');
        const $input = $container.find('.pick-field-autocomplete-input');
        const $hiddenInput = $container.find('.autocomplete-hidden-input');
        const isMultiple = $input.data('multiple') === 'true';
        const fieldName = $suggestion.data('field');
        
        const selectedId = $suggestion.data('id');
        const selectedLabel = $suggestion.data('label');
        
        if (isMultiple) {
            // Add to selected items
            const $selectedItems = $container.find('.selected-items');
            const selectedHtml = `<span class="selected-item" data-id="${selectedId}">
                ${selectedLabel}
                <span class="selected-item-remove" data-id="${selectedId}">&times;</span>
            </span>`;
            $selectedItems.append(selectedHtml);
            
            // Update hidden input with comma-separated values
            const currentValues = $hiddenInput.val() ? $hiddenInput.val().split(',') : [];
            currentValues.push(selectedId);
            $hiddenInput.val(currentValues.join(','));
            
            // Clear the input
            $input.val('');
        } else {
            // Single select - set value and clear suggestions
            $input.val(selectedLabel);
            $hiddenInput.val(selectedId);
        }
        
        // Hide suggestions
        $container.find('.autocomplete-suggestions').hide();
    }
    
    /**
     * Remove selected item (for multi-select)
     */
    function removeSelectedItem() {
        const $removeBtn = $(this);
        const $container = $removeBtn.closest('.autocomplete-container');
        const $hiddenInput = $container.find('.autocomplete-hidden-input');
        const $selectedItem = $removeBtn.closest('.selected-item');
        const removedId = $removeBtn.data('id');
        
        // Remove from hidden input
        const currentValues = $hiddenInput.val() ? $hiddenInput.val().split(',') : [];
        const newValues = currentValues.filter(value => value != removedId);
        $hiddenInput.val(newValues.join(','));
        
        // Remove visual element
        $selectedItem.remove();
    }
    
    /**
     * Handle field changes for conditional logic
     */
    function handleFieldChange() {
        const $field = $(this);
        const fieldName = $field.attr('name');
        const $row = $field.closest('tr');
        
        // Handle conditional fields
        if (currentPodConfig && currentPodConfig.fields) {
            Object.keys(currentPodConfig.fields).forEach(condFieldName => {
                const condField = currentPodConfig.fields[condFieldName];
                if (condField.conditional && condField.conditional.field === fieldName) {
                    const $condField = $row.find(`[name="${condFieldName}"]`);
                    if ($field.val() === condField.conditional.value) {
                        $condField.closest('td').show();
                    } else {
                        $condField.closest('td').hide();
                        $condField.val('');
                    }
                }
            });
        }
    }
    
    /**
     * Validate a row's terms
     */
    function validateRow($row) {
        const tibetanTerm = $row.find('.tibetan-input').val().trim();
        const translatedTerm = $row.find('.translated-input').val().trim();
        
        if (!tibetanTerm || !translatedTerm) {
            $row.find('.status-indicators span').removeClass('status-exists status-create').addClass('status-unknown');
            return;
        }
        
        // Show loading state
        $row.find('.status-indicators span').removeClass('status-exists status-create status-unknown').addClass('status-loading');
        
        $.ajax({
            url: bulkTermAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'validate_terms',
                nonce: bulkTermAjax.nonce,
                tibetan_term: tibetanTerm,
                translated_term: translatedTerm
            },
            success: function(response) {
                if (response.success) {
                    updateRowStatus($row, response.data);
                } else {
                    console.error('Validation failed:', response.data);
                }
            },
            error: function() {
                $row.find('.status-indicators span').removeClass('status-loading').addClass('status-error');
            }
        });
    }
    
    /**
     * Update row status indicators
     */
    function updateRowStatus($row, data) {
        const $tibetanStatus = $row.find('.tibetan-status');
        const $translatedStatus = $row.find('.translated-status');
        
        // Update Tibetan term status
        $tibetanStatus.removeClass('status-loading status-unknown status-error');
        if (data.tibetan.exists) {
            $tibetanStatus.addClass('status-exists').text('✓').attr('title', 'Tibetan term exists');
        } else {
            $tibetanStatus.addClass('status-create').text('+').attr('title', 'Will create Tibetan term');
        }
        
        // Update Translated term status
        $translatedStatus.removeClass('status-loading status-unknown status-error');
        if (data.translated.exists) {
            $translatedStatus.addClass('status-exists').text('✓').attr('title', 'Translated term exists');
        } else {
            $translatedStatus.addClass('status-create').text('+').attr('title', 'Will create Translated term');
        }
    }
    
    /**
     * Save all entries
     */
    function saveAllEntries() {
        const textId = $('#selected-text-id').val();
        
        if (!textId) {
            alert('Please select a text first.');
            return;
        }
        
        const entries = collectEntries();
        
        if (entries.length === 0) {
            alert('Please add at least one complete entry.');
            return;
        }
        
        // Show progress
        showProgress();
        $('.save-all-btn').prop('disabled', true).text('Processing...');
        
        $.ajax({
            url: bulkTermAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'save_bulk_entries',
                nonce: bulkTermAjax.nonce,
                text_id: textId,
                entries: entries
            },
            success: function(response) {
                hideProgress();
                $('.save-all-btn').prop('disabled', false).text('Save All Entries');
                
                if (response.success) {
                    showResults(response.data);
                    clearAllRows();
                    addInitialRow();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                hideProgress();
                $('.save-all-btn').prop('disabled', false).text('Save All Entries');
                alert('Network error. Please try again.');
            }
        });
    }
    
    /**
     * Collect all entries from the table
     */
    function collectEntries() {
        const entries = [];
        
        $('#entry-cards-container .entry-card').each(function() {
            const $card = $(this);
            const tibetanTerm = $card.find('.tibetan-input').val().trim();
            const translatedTerm = $card.find('.translated-input').val().trim();
            const quoteTib = $card.find('.quote-tib-input').val().trim();
            const quoteTarget = $card.find('.quote-target-input').val().trim();
            const quoteRef = $card.find('.quote-ref-input').val().trim();
            const translatorNote = $card.find('.translator-note-input').val().trim();
            const translationsInput = $card.find('.translations-hidden-input').val();
            
            if (tibetanTerm && translatedTerm) {
                const entry = {
                    tibetan_term: tibetanTerm,
                    translated_term: translatedTerm,
                    term_quote_tib: quoteTib,
                    term_quote_target_lang: quoteTarget,
                    quote_reference: quoteRef,
                    translator_note: translatorNote
                };
                
                // Add translation if one is selected
                if (translationsInput && translationsInput.trim()) {
                    entry.translations = parseInt(translationsInput.trim());
                }
                
                entries.push(entry);
            }
        });
        
        return entries;
    }
    
    /**
     * Handle term autocomplete input
     */
    function handleTermAutocomplete() {
        const $input = $(this);
        const searchTerm = $input.val();
        let postType;
        
        if ($input.hasClass('tibetan-input')) {
            postType = 'tibetan_term';
        } else if ($input.hasClass('translated-input')) {
            postType = 'translated_term';
        } else if ($input.hasClass('translations-input')) {
            postType = 'translation';
        }
        
        if (searchTerm.length < 2 || !postType) {
            $input.siblings('.term-suggestions').hide();
            return;
        }
        
        // Debounce the search
        clearTimeout($input.data('search-timeout'));
        const timeout = setTimeout(() => {
            fetchTermSuggestions($input, postType, searchTerm);
        }, 300);
        $input.data('search-timeout', timeout);
    }
    
    /**
     * Fetch term suggestions via AJAX
     */
    function fetchTermSuggestions($input, postType, searchTerm) {
        $.ajax({
            url: bulkTermAjax.ajax_url,
            method: 'GET',
            data: {
                action: 'get_reference_options',
                post_type: postType,
                search_term: searchTerm
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    showTermSuggestions($input, response.data);
                } else {
                    $input.siblings('.term-suggestions').hide();
                }
            }
        });
    }
    
    /**
     * Show term suggestions
     */
    function showTermSuggestions($input, suggestions) {
        const $suggestions = $input.siblings('.term-suggestions');
        
        if (!suggestions || suggestions.length === 0) {
            $suggestions.hide();
            return;
        }
        
        let html = '';
        suggestions.forEach(function(item) {
            html += `<div class="term-suggestion" data-id="${item.id}">${item.label}</div>`;
        });
        
        $suggestions.html(html).show();
    }
    
    /**
     * Select a term suggestion
     */
    function selectTermSuggestion() {
        const $suggestion = $(this);
        const $input = $suggestion.closest('.autocomplete-container').find('input[type="text"]');
        const $hiddenInput = $suggestion.closest('.autocomplete-container').find('input[type="hidden"]');
        
        const suggestionId = $suggestion.data('id');
        const suggestionText = $suggestion.text();
        
        // Set the visible input value
        $input.val(suggestionText);
        
        // For single selection fields (translations), set the hidden input
        if ($input.hasClass('translations-input')) {
            $hiddenInput.val(suggestionId);
        }
        
        // Hide suggestions
        $suggestion.parent().hide();
    }
    
    /**
     * Clear all rows
     */
    function clearAllRows() {
        $('#entry-cards-container').empty();
        rowCounter = 1;
    }
    
    /**
     * Show progress indicator
     */
    function showProgress() {
        $('.progress-section').show();
        $('.progress-fill').css('width', '0%');
        
        // Animate progress bar
        $('.progress-fill').animate({ width: '100%' }, 2000);
    }
    
    /**
     * Hide progress indicator
     */
    function hideProgress() {
        $('.progress-section').hide();
    }
    
    /**
     * Show results summary
     */
    function showResults(data) {
        let html = '<div class="results-summary success">';
        html += '<h4>✅ Success!</h4>';
        html += `<p><strong>${data.processed}</strong> entries processed successfully:</p>`;
        html += '<ul>';
        html += `<li>Created <strong>${data.created_tibetan_terms || 0}</strong> new Tibetan terms</li>`;
        html += `<li>Created <strong>${data.created_translated_terms || 0}</strong> new Translated terms</li>`;
        html += `<li>Created <strong>${data.created_term_usages || 0}</strong> new Term Usage entries</li>`;
        html += '</ul>';
        
        if (data.errors && data.errors.length > 0) {
            html += '<h4>⚠️ Errors:</h4><ul>';
            data.errors.forEach(error => {
                html += `<li>${error}</li>`;
            });
            html += '</ul>';
        }
        
        html += '</div>';
        
        $('.results-content').html(html);
        $('.results-section').show();
        
        // Auto-hide after 10 seconds
        setTimeout(() => {
            $('.results-section').fadeOut();
        }, 10000);
    }
    
    /**
     * Show CSV import modal
     */
    function showCsvModal() {
        $('.csv-import-modal').show();
    }
    
    /**
     * Hide CSV import modal
     */
    function hideCsvModal() {
        $('.csv-import-modal').hide();
        $('#csv-file-input').val('');
        $('.csv-preview').hide();
        $('.import-csv-confirm').prop('disabled', true);
    }
    
    /**
     * Handle CSV file selection
     */
    function handleCsvFile(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const csvData = e.target.result;
            const parsedData = parseCSV(csvData);
            
            if (parsedData.length > 0) {
                showCsvPreview(parsedData);
                $('.import-csv-confirm').prop('disabled', false);
            }
        };
        reader.readAsText(file);
    }
    
    /**
     * Parse CSV data
     */
    function parseCSV(csvData) {
        const lines = csvData.split('\n');
        const result = [];
        
        for (let i = 1; i < lines.length; i++) { // Skip header
            const line = lines[i].trim();
            if (line) {
                const columns = line.split(',');
                if (columns.length >= 2) {
                    result.push({
                        tibetan_term: columns[0].replace(/"/g, '').trim(),
                        translated_term: columns[1].replace(/"/g, '').trim(),
                        example: columns[2] ? columns[2].replace(/"/g, '').trim() : '',
                        term_comment: columns[3] ? columns[3].replace(/"/g, '').trim() : ''
                    });
                }
            }
        }
        
        return result;
    }
    
    /**
     * Show CSV preview
     */
    function showCsvPreview(data) {
        let html = '<table class="wp-list-table widefat"><thead><tr>';
        html += '<th>Tibetan Term</th><th>Translated Term</th><th>Example</th><th>Comment</th>';
        html += '</tr></thead><tbody>';
        
        data.slice(0, 5).forEach(row => { // Show first 5 rows
            html += '<tr>';
            html += `<td>${row.tibetan_term}</td>`;
            html += `<td>${row.translated_term}</td>`;
            html += `<td>${row.example || ''}</td>`;
            html += `<td>${row.term_comment || ''}</td>`;
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        
        if (data.length > 5) {
            html += `<p><em>... and ${data.length - 5} more rows</em></p>`;
        }
        
        $('.csv-preview-content').html(html);
        $('.csv-preview').show();
        
        // Store data for import
        $('.import-csv-confirm').data('csvData', data);
    }
    
    /**
     * Import CSV data
     */
    function importCsvData() {
        const csvData = $('.import-csv-confirm').data('csvData');
        
        if (!csvData) return;
        
        // Clear existing rows
        clearAllRows();
        
        // Add rows from CSV
        csvData.forEach(row => {
            addNewRow();
            const $lastRow = $('#entry-table-body tr:last');
            $lastRow.find('.tibetan-input').val(row.tibetan_term);
            $lastRow.find('.translated-input').val(row.translated_term);
            $lastRow.find('.usage-context-input').val(row.usage_context);
            
            // Trigger validation
            setTimeout(() => validateRow($lastRow), 100);
        });
        
        hideCsvModal();
    }
    
})(jQuery);