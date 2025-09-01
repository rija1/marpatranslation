# Book Request System Frontend Implementation Plan

## 1. Plan Overview

This plan outlines the complete frontend implementation strategy for a book request system overlay that will appear when users click "Request This Book" on WooCommerce product pages. The solution integrates seamlessly with the existing twentytwentyfour-child theme and MTS design system, using modern accessibility standards and responsive design.

**Key Implementation Strategy:**
- Modal overlay system with backdrop blur and professional MTS branding
- AJAX form submission with comprehensive error handling  
- Seamless integration with existing WooCommerce templates
- Full responsive design matching MTS grid aesthetic
- Professional user experience flow with loading states
- Integration with existing Gutenberg blocks and theme patterns

## 2. Current Architecture Analysis

### Theme Structure
- **Active Theme:** `twentytwentyfour-child` 
- **Color System:** Custom MTS palette with primary (#14375d), burgundy (#8b1538), gold (#d4af37)
- **Typography:** Cardo heading font, Inter body font, custom font sizes
- **Layout:** CSS Grid-based responsive design with 1280px wide max content

### Existing Patterns
- **MTS Blocks Plugin:** Uses comprehensive grid system with consistent styling (`/wp-content/plugins/mts-blocks/src/shared/mts-grids-shared.scss`)
- **AJAX Implementation:** Current AJAX search for Tibetan terms (`functions.php` line 153-175)
- **WooCommerce Override:** Custom simple product template at `/wp-content/themes/twentytwentyfour-child/woocommerce/single-product/add-to-cart/simple.php`
- **Design System:** Button styling, form elements, and modal patterns follow established grid aesthetic

### JavaScript Architecture
- **Current Scripts:** `bsf.js`, `chart.js`, ScrollTrigger (minimal existing JS)
- **Block Patterns:** MTS blocks use simple view.js files for frontend interactions
- **AJAX Pattern:** WordPress standard with `wp_ajax_` hooks and nonce verification

## 3. Step-by-Step Implementation Instructions

### Step 1: Enhance WooCommerce Template Override
**File: `/wp-content/themes/twentytwentyfour-child/woocommerce/single-product/add-to-cart/simple.php`**

Replace the current implementation with a comprehensive book request button system:

1. Remove the existing conditional logic (lines 14-16)
2. Add the book request button with proper data attributes
3. Include modal container div
4. Add loading states and accessibility attributes

### Step 2: Create Modal Overlay System
**File: `/wp-content/themes/twentytwentyfour-child/js/book-request-modal.js`** (NEW FILE)

Implement a modern modal system with:
- Backdrop blur and MTS color theming
- Keyboard navigation (ESC key, tab trapping)
- Touch-friendly mobile interactions
- ARIA compliance for screen readers
- Loading states and success/error feedback

### Step 3: Enhanced AJAX Handler  
**File: `/wp-content/themes/twentytwentyfour-child/functions.php`** (MODIFY EXISTING)

Add to the existing AJAX section (after line 175):
- Book request form submission handler
- Data validation and sanitization
- Email integration with WordPress mail system
- Admin notification system
- User confirmation system

### Step 4: Professional Modal Styling
**File: `/wp-content/themes/twentytwentyfour-child/css/book-request-modal.scss`** (NEW FILE)

Create comprehensive styling that matches MTS design system:
- Modal overlay with backdrop-filter blur
- Form styling consistent with theme inputs
- Button styling matching MTS blocks
- Professional animations and transitions
- Mobile-first responsive design

### Step 5: Integration with Theme Enqueue System
**File: `/wp-content/themes/twentytwentyfour-child/functions.php`** (MODIFY EXISTING)

Enhance the existing enqueue function (lines 49-53) to conditionally load book request assets only on WooCommerce product pages.

## 4. Detailed Code Implementation

### 4.1 Enhanced WooCommerce Template
**File: `wp-content/themes/twentytwentyfour-child/woocommerce/single-product/add-to-cart/simple.php`**

```php
<?php
/**
 * Simple product add to cart - Enhanced for Book Requests
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
    return;
}

echo wc_get_stock_html( $product );

if ( $product->is_in_stock() ) : ?>
    
    <?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>
    
    <div class="book-request-container">
        <div class="book-request-info">
            <p class="book-availability-notice">
                <strong>Book Request System:</strong> This publication is available by request. 
                Submit your information to receive access details.
            </p>
        </div>
        
        <button 
            type="button" 
            class="book-request-button single_add_to_cart_button button alt wp-element-button"
            data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
            data-product-title="<?php echo esc_attr( $product->get_title() ); ?>"
            data-product-price="<?php echo esc_attr( $product->get_price() ); ?>"
            aria-label="Request access to <?php echo esc_attr( $product->get_title() ); ?>"
        >
            <span class="button-text">Request This Book</span>
            <span class="button-loading" style="display: none;">
                <span class="spinner"></span> Processing...
            </span>
        </button>
    </div>
    
    <!-- Book Request Modal -->
    <div id="book-request-modal" class="book-request-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-hidden="true">
        <div class="modal-backdrop"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h2 id="modal-title">Request Book Access</h2>
                <button type="button" class="modal-close" aria-label="Close modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="book-info">
                    <h3 class="book-title"></h3>
                    <p class="book-description">Complete the form below to request access to this publication.</p>
                </div>
                
                <form id="book-request-form" class="book-request-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="requester-name">Full Name *</label>
                            <input type="text" id="requester-name" name="requester_name" required 
                                   aria-describedby="name-help" class="form-control">
                            <small id="name-help" class="form-help">Your full legal name</small>
                        </div>
                        <div class="form-group">
                            <label for="requester-email">Email Address *</label>
                            <input type="email" id="requester-email" name="requester_email" required
                                   aria-describedby="email-help" class="form-control">
                            <small id="email-help" class="form-help">We'll send access information here</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="institution">Institution/Organization</label>
                        <input type="text" id="institution" name="institution" class="form-control"
                               aria-describedby="institution-help">
                        <small id="institution-help" class="form-help">Academic institution, monastery, or organization</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="research-purpose">Purpose of Request *</label>
                        <select id="research-purpose" name="research_purpose" required class="form-control"
                                aria-describedby="purpose-help">
                            <option value="">Select purpose...</option>
                            <option value="academic_research">Academic Research</option>
                            <option value="translation_work">Translation Work</option>
                            <option value="dharma_study">Dharma Study</option>
                            <option value="comparative_study">Comparative Study</option>
                            <option value="other">Other</option>
                        </select>
                        <small id="purpose-help" class="form-help">Help us understand your intended use</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="request-details">Additional Details</label>
                        <textarea id="request-details" name="request_details" rows="4" class="form-control"
                                  aria-describedby="details-help" 
                                  placeholder="Please provide any additional context about your research or study..."></textarea>
                        <small id="details-help" class="form-help">Optional: Specific research focus, timeline, etc.</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="submit-text">Submit Request</span>
                            <span class="submit-loading" style="display: none;">
                                <span class="spinner"></span> Submitting...
                            </span>
                        </button>
                    </div>
                    
                    <!-- Hidden fields -->
                    <input type="hidden" name="product_id" value="">
                    <input type="hidden" name="product_title" value="">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('book_request_nonce'); ?>">
                </form>
            </div>
        </div>
    </div>
    
    <?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>
```

### 4.2 JavaScript Modal Implementation
**File: `wp-content/themes/twentytwentyfour-child/js/book-request-modal.js`** (NEW FILE)

```javascript
/**
 * Book Request Modal System
 * Integrated with MTS design system and WordPress AJAX
 */

class BookRequestModal {
    constructor() {
        this.modal = null;
        this.form = null;
        this.isOpen = false;
        this.lastFocusedElement = null;
        
        this.init();
    }
    
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.bindEvents());
        } else {
            this.bindEvents();
        }
    }
    
    bindEvents() {
        this.modal = document.getElementById('book-request-modal');
        this.form = document.getElementById('book-request-form');
        
        if (!this.modal || !this.form) return;
        
        // Bind request button clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.book-request-button')) {
                e.preventDefault();
                this.openModal(e.target.closest('.book-request-button'));
            }
        });
        
        // Bind modal close events
        this.modal.querySelector('.modal-close').addEventListener('click', () => this.closeModal());
        this.modal.querySelector('.modal-cancel').addEventListener('click', () => this.closeModal());
        this.modal.querySelector('.modal-backdrop').addEventListener('click', () => this.closeModal());
        
        // Bind form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Bind keyboard events
        document.addEventListener('keydown', (e) => this.handleKeydown(e));
        
        // Bind form validation
        this.bindFormValidation();
    }
    
    openModal(button) {
        const productId = button.dataset.productId;
        const productTitle = button.dataset.productTitle;
        
        // Update modal content
        this.modal.querySelector('.book-title').textContent = productTitle;
        this.modal.querySelector('input[name="product_id"]').value = productId;
        this.modal.querySelector('input[name="product_title"]').value = productTitle;
        
        // Store current focus
        this.lastFocusedElement = button;
        
        // Show modal
        this.modal.classList.add('show');
        this.modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        
        // Focus first form field
        setTimeout(() => {
            const firstInput = this.form.querySelector('input, select, textarea');
            if (firstInput) firstInput.focus();
        }, 150);
        
        this.isOpen = true;
        
        // Trap focus within modal
        this.trapFocus();
    }
    
    closeModal() {
        if (!this.isOpen) return;
        
        this.modal.classList.remove('show');
        this.modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        
        // Restore focus
        if (this.lastFocusedElement) {
            this.lastFocusedElement.focus();
        }
        
        // Reset form
        this.form.reset();
        this.clearErrors();
        
        this.isOpen = false;
    }
    
    handleSubmit(e) {
        e.preventDefault();
        
        if (!this.validateForm()) {
            return;
        }
        
        this.setSubmitting(true);
        
        // Prepare form data
        const formData = new FormData(this.form);
        formData.append('action', 'submit_book_request');
        
        // Submit via AJAX
        fetch(bookRequestAjax.ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            this.setSubmitting(false);
            
            if (data.success) {
                this.showSuccess(data.data.message);
            } else {
                this.showError(data.data.message || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            this.setSubmitting(false);
            this.showError('Network error. Please check your connection and try again.');
            console.error('Book request error:', error);
        });
    }
    
    validateForm() {
        this.clearErrors();
        let isValid = true;
        
        // Required fields validation
        const requiredFields = this.form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'This field is required');
                isValid = false;
            }
        });
        
        // Email validation
        const emailField = this.form.querySelector('input[type="email"]');
        if (emailField.value && !this.isValidEmail(emailField.value)) {
            this.showFieldError(emailField, 'Please enter a valid email address');
            isValid = false;
        }
        
        return isValid;
    }
    
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    showFieldError(field, message) {
        const group = field.closest('.form-group');
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        errorElement.setAttribute('role', 'alert');
        
        group.appendChild(errorElement);
        field.setAttribute('aria-invalid', 'true');
        field.classList.add('error');
    }
    
    clearErrors() {
        const errors = this.form.querySelectorAll('.field-error');
        errors.forEach(error => error.remove());
        
        const errorFields = this.form.querySelectorAll('.error');
        errorFields.forEach(field => {
            field.classList.remove('error');
            field.removeAttribute('aria-invalid');
        });
    }
    
    setSubmitting(isSubmitting) {
        const submitButton = this.form.querySelector('button[type="submit"]');
        const submitText = submitButton.querySelector('.submit-text');
        const submitLoading = submitButton.querySelector('.submit-loading');
        
        if (isSubmitting) {
            submitButton.disabled = true;
            submitText.style.display = 'none';
            submitLoading.style.display = 'inline-flex';
        } else {
            submitButton.disabled = false;
            submitText.style.display = 'inline';
            submitLoading.style.display = 'none';
        }
    }
    
    showSuccess(message) {
        const modalBody = this.modal.querySelector('.modal-body');
        modalBody.innerHTML = `
            <div class="success-message">
                <div class="success-icon">✓</div>
                <h3>Request Submitted Successfully</h3>
                <p>${message}</p>
                <button type="button" class="btn btn-primary modal-close-success">Close</button>
            </div>
        `;
        
        modalBody.querySelector('.modal-close-success').addEventListener('click', () => {
            this.closeModal();
        });
        
        // Auto-close after 5 seconds
        setTimeout(() => {
            if (this.isOpen) this.closeModal();
        }, 5000);
    }
    
    showError(message) {
        const existingAlert = this.form.querySelector('.form-alert');
        if (existingAlert) existingAlert.remove();
        
        const alert = document.createElement('div');
        alert.className = 'form-alert form-alert-error';
        alert.setAttribute('role', 'alert');
        alert.innerHTML = `
            <strong>Error:</strong> ${message}
            <button type="button" class="alert-close" aria-label="Close alert">&times;</button>
        `;
        
        this.form.insertBefore(alert, this.form.firstChild);
        
        alert.querySelector('.alert-close').addEventListener('click', () => {
            alert.remove();
        });
        
        // Auto-remove after 8 seconds
        setTimeout(() => {
            if (alert.parentNode) alert.remove();
        }, 8000);
    }
    
    handleKeydown(e) {
        if (!this.isOpen) return;
        
        if (e.key === 'Escape') {
            this.closeModal();
        }
    }
    
    trapFocus() {
        const focusableElements = this.modal.querySelectorAll(
            'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];
        
        this.modal.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey && document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                } else if (!e.shiftKey && document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        });
    }
    
    bindFormValidation() {
        // Real-time validation for better UX
        const inputs = this.form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                if (input.hasAttribute('required') && !input.value.trim()) {
                    this.showFieldError(input, 'This field is required');
                }
            });
            
            input.addEventListener('input', () => {
                const error = input.closest('.form-group').querySelector('.field-error');
                if (error && input.value.trim()) {
                    error.remove();
                    input.classList.remove('error');
                    input.removeAttribute('aria-invalid');
                }
            });
        });
    }
}

// Initialize when DOM is ready
new BookRequestModal();
```

### 4.3 AJAX Handler in PHP
**Add to `/wp-content/themes/twentytwentyfour-child/functions.php` after line 175:**

```php
/**
 * SECTION 3.5: BOOK REQUEST AJAX HANDLER
 * =============================================================================
 */

/**
 * Handle book request form submissions
 */
add_action('wp_ajax_submit_book_request', 'handle_book_request_submission');
add_action('wp_ajax_nopriv_submit_book_request', 'handle_book_request_submission');

function handle_book_request_submission() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'book_request_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'));
        return;
    }
    
    // Sanitize and validate form data
    $requester_name = sanitize_text_field($_POST['requester_name'] ?? '');
    $requester_email = sanitize_email($_POST['requester_email'] ?? '');
    $institution = sanitize_text_field($_POST['institution'] ?? '');
    $research_purpose = sanitize_text_field($_POST['research_purpose'] ?? '');
    $request_details = sanitize_textarea_field($_POST['request_details'] ?? '');
    $product_id = intval($_POST['product_id'] ?? 0);
    $product_title = sanitize_text_field($_POST['product_title'] ?? '');
    
    // Validate required fields
    $errors = array();
    
    if (empty($requester_name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($requester_email) || !is_email($requester_email)) {
        $errors[] = 'Valid email address is required';
    }
    
    if (empty($research_purpose)) {
        $errors[] = 'Purpose of request is required';
    }
    
    if (!empty($errors)) {
        wp_send_json_error(array('message' => 'Please correct the following: ' . implode(', ', $errors)));
        return;
    }
    
    // Get product information
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(array('message' => 'Invalid product specified.'));
        return;
    }
    
    // Save request to database
    $request_data = array(
        'requester_name' => $requester_name,
        'requester_email' => $requester_email,
        'institution' => $institution,
        'research_purpose' => $research_purpose,
        'request_details' => $request_details,
        'product_id' => $product_id,
        'product_title' => $product_title,
        'product_url' => get_permalink($product_id),
        'request_date' => current_time('mysql'),
        'request_status' => 'pending',
        'user_ip' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
        'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '')
    );
    
    $saved = save_book_request($request_data);
    
    if (!$saved) {
        wp_send_json_error(array('message' => 'Unable to save your request. Please try again.'));
        return;
    }
    
    // Send confirmation email to requester
    $requester_email_sent = send_book_request_confirmation($request_data);
    
    // Send notification to site admin
    $admin_email_sent = send_book_request_notification($request_data);
    
    // Prepare success response
    $response_message = 'Your book request has been submitted successfully! ';
    
    if ($requester_email_sent) {
        $response_message .= 'A confirmation has been sent to your email address. ';
    }
    
    $response_message .= 'We will review your request and respond within 2-3 business days.';
    
    wp_send_json_success(array(
        'message' => $response_message,
        'request_id' => $saved
    ));
}

/**
 * Save book request to database
 */
function save_book_request($request_data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'book_requests';
    
    // Create table if it doesn't exist
    create_book_requests_table();
    
    $inserted = $wpdb->insert(
        $table_name,
        $request_data,
        array(
            '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s'
        )
    );
    
    if ($inserted === false) {
        error_log('Book request database error: ' . $wpdb->last_error);
        return false;
    }
    
    return $wpdb->insert_id;
}

/**
 * Create book requests table
 */
function create_book_requests_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'book_requests';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        requester_name varchar(255) NOT NULL,
        requester_email varchar(255) NOT NULL,
        institution varchar(255),
        research_purpose varchar(100) NOT NULL,
        request_details text,
        product_id int(11) NOT NULL,
        product_title varchar(500) NOT NULL,
        product_url varchar(500),
        request_date datetime NOT NULL,
        request_status varchar(50) DEFAULT 'pending',
        user_ip varchar(45),
        user_agent text,
        PRIMARY KEY (id),
        KEY requester_email (requester_email),
        KEY product_id (product_id),
        KEY request_status (request_status),
        KEY request_date (request_date)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Send confirmation email to requester
 */
function send_book_request_confirmation($request_data) {
    $to = $request_data['requester_email'];
    $subject = 'Book Request Confirmation - ' . get_bloginfo('name');
    
    $message = sprintf(
        "Dear %s,\n\n" .
        "Thank you for your book request. We have received your request for:\n\n" .
        "Book: %s\n" .
        "Purpose: %s\n" .
        "Institution: %s\n\n" .
        "Request Details:\n%s\n\n" .
        "We will review your request and respond within 2-3 business days.\n\n" .
        "If you have any questions, please contact us at %s.\n\n" .
        "Best regards,\n" .
        "The %s Team",
        $request_data['requester_name'],
        $request_data['product_title'],
        ucwords(str_replace('_', ' ', $request_data['research_purpose'])),
        $request_data['institution'] ?: 'Not specified',
        $request_data['request_details'] ?: 'None provided',
        get_option('admin_email'),
        get_bloginfo('name')
    );
    
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
    );
    
    return wp_mail($to, $subject, $message, $headers);
}

/**
 * Send notification email to admin
 */
function send_book_request_notification($request_data) {
    $admin_email = get_option('admin_email');
    $subject = 'New Book Request - ' . $request_data['product_title'];
    
    $message = sprintf(
        "A new book request has been submitted:\n\n" .
        "Requester: %s (%s)\n" .
        "Institution: %s\n" .
        "Purpose: %s\n" .
        "Book: %s\n" .
        "Product URL: %s\n\n" .
        "Request Details:\n%s\n\n" .
        "Date: %s\n" .
        "IP Address: %s\n\n" .
        "Please review and respond to this request.",
        $request_data['requester_name'],
        $request_data['requester_email'],
        $request_data['institution'] ?: 'Not specified',
        ucwords(str_replace('_', ' ', $request_data['research_purpose'])),
        $request_data['product_title'],
        $request_data['product_url'],
        $request_data['request_details'] ?: 'None provided',
        $request_data['request_date'],
        $request_data['user_ip']
    );
    
    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    return wp_mail($admin_email, $subject, $message, $headers);
}
```

### 4.4 Professional Modal Styling
**File: `wp-content/themes/twentytwentyfour-child/css/book-request-modal.scss`** (NEW FILE)

```scss
/**
 * Book Request Modal - MTS Design System Integration
 * Professional styling matching the existing grid aesthetic
 */

// Import MTS color variables from theme
$mts-primary: #14375d;
$mts-burgundy: #8b1538;
$mts-gold: #d4af37;
$mts-parchment: #f7f4f0;
$mts-text: #2d3748;
$mts-light-text: #4a5568;

// Grid system colors (matching MTS blocks)
$grid-border: #e2e8f0;
$grid-bg-header: #f8f9fa;
$grid-text-dark: #2c3e50;
$grid-blue: #3498db;
$grid-blue-hover: #2980b9;
$grid-shadow: rgba(0, 0, 0, 0.1);

// =============================================================================
// BOOK REQUEST CONTAINER (Product Page)
// =============================================================================

.book-request-container {
    margin: 2rem 0;
    padding: 1.5rem;
    background: white;
    border: 1px solid $grid-border;
    border-radius: 8px;
    box-shadow: 0 2px 4px $grid-shadow;
    
    .book-availability-notice {
        background: $grid-bg-header;
        padding: 1rem 1.25rem;
        border-radius: 6px;
        border-left: 4px solid $mts-primary;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        line-height: 1.5;
        
        strong {
            color: $mts-primary;
            font-weight: 600;
        }
    }
}

.book-request-button {
    background: linear-gradient(135deg, $mts-primary 0%, lighten($mts-primary, 10%) 100%);
    border: none;
    color: white;
    padding: 1rem 2rem;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    min-width: 200px;
    text-transform: none;
    letter-spacing: 0.025em;
    
    &:hover {
        background: linear-gradient(135deg, darken($mts-primary, 5%) 0%, $mts-primary 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba($mts-primary, 0.3);
        color: white;
    }
    
    &:focus {
        outline: 2px solid $mts-gold;
        outline-offset: 2px;
    }
    
    &:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
    }
}

// =============================================================================
// MODAL SYSTEM
// =============================================================================

.book-request-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    
    &.show {
        opacity: 1;
        visibility: visible;
        
        .modal-container {
            transform: scale(1);
            opacity: 1;
        }
    }
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.modal-container {
    position: relative;
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    max-width: 600px;
    width: 90vw;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.9);
    opacity: 0;
    transition: all 0.3s ease;
    
    // Smooth scrolling
    scroll-behavior: smooth;
    
    // Custom scrollbar
    &::-webkit-scrollbar {
        width: 6px;
    }
    
    &::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    &::-webkit-scrollbar-thumb {
        background: lighten($mts-primary, 20%);
        border-radius: 3px;
        
        &:hover {
            background: $mts-primary;
        }
    }
}

// Modal Header
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem 2rem;
    border-bottom: 2px solid $grid-border;
    background: $grid-bg-header;
    border-radius: 12px 12px 0 0;
    
    h2 {
        margin: 0;
        color: $grid-text-dark;
        font-size: 1.5rem;
        font-weight: 600;
        font-family: var(--wp--preset--font-family--heading);
    }
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: $mts-light-text;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    line-height: 1;
    transition: all 0.2s ease;
    
    &:hover {
        background: rgba($mts-primary, 0.1);
        color: $mts-primary;
    }
    
    &:focus {
        outline: 2px solid $mts-gold;
        outline-offset: 2px;
    }
}

// Modal Body
.modal-body {
    padding: 2rem;
}

.book-info {
    margin-bottom: 2rem;
    text-align: center;
    
    .book-title {
        color: $mts-primary;
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
        font-family: var(--wp--preset--font-family--heading);
    }
    
    .book-description {
        color: $mts-light-text;
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.5;
    }
}

// =============================================================================
// FORM STYLING
// =============================================================================

.book-request-form {
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        
        @media (max-width: 600px) {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
    
    .form-group {
        margin-bottom: 1.5rem;
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: $grid-text-dark;
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid $grid-border;
            border-radius: 6px;
            font-size: 1rem;
            line-height: 1.5;
            transition: all 0.2s ease;
            background: white;
            font-family: var(--wp--preset--font-family--body);
            
            &:focus {
                outline: none;
                border-color: $grid-blue;
                box-shadow: 0 0 0 3px rgba($grid-blue, 0.1);
            }
            
            &.error {
                border-color: #e74c3c;
                box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
            }
            
            &::placeholder {
                color: lighten($mts-light-text, 10%);
                opacity: 0.8;
            }
        }
        
        .form-help {
            display: block;
            margin-top: 0.375rem;
            color: $mts-light-text;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        
        .field-error {
            display: block;
            margin-top: 0.5rem;
            color: #e74c3c;
            font-size: 0.85rem;
            font-weight: 500;
        }
    }
    
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }
    
    select.form-control {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3E%3Cpath fill='%23666' d='m2 0-2 2h4zm0 5 2-2h-4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 8px 10px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
    }
}

// Form Actions
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid $grid-border;
    
    @media (max-width: 600px) {
        flex-direction: column;
        align-items: stretch;
    }
}

.btn {
    padding: 0.75rem 1.5rem;
    border: 2px solid transparent;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
    font-family: var(--wp--preset--font-family--body);
    
    &:focus {
        outline: 2px solid $mts-gold;
        outline-offset: 2px;
    }
    
    &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: currentColor;
        animation: spin 1s ease-in-out infinite;
    }
}

.btn-primary {
    background: $mts-primary;
    color: white;
    border-color: $mts-primary;
    
    &:hover:not(:disabled) {
        background: darken($mts-primary, 8%);
        border-color: darken($mts-primary, 8%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba($mts-primary, 0.3);
    }
    
    .spinner {
        border-top-color: white;
    }
}

.btn-secondary {
    background: transparent;
    color: $mts-light-text;
    border-color: $grid-border;
    
    &:hover:not(:disabled) {
        background: $grid-bg-header;
        border-color: lighten($mts-primary, 30%);
        color: $mts-primary;
    }
}

// =============================================================================
// ALERTS AND MESSAGES
// =============================================================================

.form-alert {
    padding: 1rem 1.25rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    font-size: 0.95rem;
    line-height: 1.5;
    
    .alert-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        opacity: 0.7;
        transition: opacity 0.2s ease;
        
        &:hover {
            opacity: 1;
        }
    }
}

.form-alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
    
    .alert-close {
        color: #b91c1c;
    }
}

// Success Message Styling
.success-message {
    text-align: center;
    padding: 2rem;
    
    .success-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 4rem;
        height: 4rem;
        background: #10b981;
        color: white;
        border-radius: 50%;
        font-size: 2rem;
        font-weight: bold;
        margin: 0 auto 1.5rem auto;
        animation: successBounce 0.6s ease-out;
    }
    
    h3 {
        color: $grid-text-dark;
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0 0 1rem 0;
        font-family: var(--wp--preset--font-family--heading);
    }
    
    p {
        color: $mts-light-text;
        font-size: 1rem;
        line-height: 1.6;
        margin: 0 0 2rem 0;
    }
}

// =============================================================================
// ANIMATIONS
// =============================================================================

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes successBounce {
    0%, 20%, 53%, 80%, 100% {
        animation-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
        transform: translate3d(0, 0, 0);
    }
    40%, 43% {
        animation-timing-function: cubic-bezier(0.755, 0.050, 0.855, 0.060);
        transform: translate3d(0, -8px, 0);
    }
    70% {
        animation-timing-function: cubic-bezier(0.755, 0.050, 0.855, 0.060);
        transform: translate3d(0, -4px, 0);
    }
    90% {
        transform: translate3d(0, -2px, 0);
    }
}

// =============================================================================
// RESPONSIVE DESIGN
// =============================================================================

@media (max-width: 768px) {
    .modal-container {
        width: 95vw;
        max-height: 95vh;
        border-radius: 8px;
    }
    
    .modal-header {
        padding: 1rem 1.5rem;
        
        h2 {
            font-size: 1.25rem;
        }
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .book-request-container {
        margin: 1rem 0;
        padding: 1rem;
        
        .book-availability-notice {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
    }
    
    .book-request-button {
        width: 100%;
        padding: 0.875rem 1.5rem;
        font-size: 0.95rem;
    }
    
    .success-message {
        padding: 1.5rem;
        
        .success-icon {
            width: 3rem;
            height: 3rem;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        h3 {
            font-size: 1.25rem;
        }
    }
}

// =============================================================================
// ACCESSIBILITY & FOCUS STATES
// =============================================================================

@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

@media (prefers-contrast: high) {
    .book-request-modal {
        .modal-container {
            border: 2px solid $grid-text-dark;
        }
        
        .form-control:focus {
            border-color: $grid-text-dark;
            box-shadow: 0 0 0 3px rgba($grid-text-dark, 0.3);
        }
        
        .btn:focus {
            outline: 3px solid $grid-text-dark;
            outline-offset: 2px;
        }
    }
}

// =============================================================================
// BODY CLASS MODIFICATIONS
// =============================================================================

body.modal-open {
    overflow: hidden;
    padding-right: 0; // Prevent content shift
    
    // Prevent background scrolling on mobile
    position: fixed;
    width: 100%;
    
    @media (min-width: 769px) {
        position: static;
    }
}

// Print styles
@media print {
    .book-request-modal {
        display: none !important;
    }
    
    .book-request-container {
        page-break-inside: avoid;
    }
}
```

### 4.5 Enhanced Theme Script Enqueue
**Modify `/wp-content/themes/twentytwentyfour-child/functions.php` lines 49-53:**

```php
/**
 * Enqueue theme scripts and styles
 */
function my_theme_scripts() {
    // Existing scripts
    wp_enqueue_script('bsf', get_stylesheet_directory_uri() . '/js/bsf.js');
    wp_enqueue_script('chartjs', get_stylesheet_directory_uri() . '/js/chart.js');
    
    // Book request system - only on WooCommerce product pages
    if (is_product()) {
        // Enqueue modal JavaScript
        wp_enqueue_script(
            'book-request-modal',
            get_stylesheet_directory_uri() . '/js/book-request-modal.js',
            array('jquery'),
            wp_get_theme()->get('Version'),
            true
        );
        
        // Enqueue modal CSS
        wp_enqueue_style(
            'book-request-modal',
            get_stylesheet_directory_uri() . '/css/book-request-modal.css',
            array(),
            wp_get_theme()->get('Version')
        );
        
        // Localize script for AJAX
        wp_localize_script(
            'book-request-modal',
            'bookRequestAjax',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('book_request_nonce'),
                'strings' => array(
                    'processing' => __('Processing...', 'twentytwentyfour'),
                    'error_generic' => __('An error occurred. Please try again.', 'twentytwentyfour'),
                    'error_network' => __('Network error. Please check your connection.', 'twentytwentyfour'),
                    'success_title' => __('Request Submitted!', 'twentytwentyfour'),
                    'required_field' => __('This field is required', 'twentytwentyfour'),
                    'invalid_email' => __('Please enter a valid email address', 'twentytwentyfour')
                )
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'my_theme_scripts');
```

## 5. Styling & Performance Notes

### 5.1 Design System Integration
- **Color Palette:** Uses existing MTS colors (`$mts-primary: #14375d`, `$mts-burgundy: #8b1538`, `$mts-gold: #d4af37`)
- **Typography:** Matches theme fonts (Cardo for headings, Inter for body text)
- **Grid System:** Consistent with MTS blocks grid aesthetic using CSS Grid and Flexbox
- **Animations:** Smooth, professional transitions with reduced-motion support

### 5.2 Accessibility Features  
- **ARIA Labels:** Complete semantic markup with proper roles and labels
- **Keyboard Navigation:** Full keyboard support with focus trapping in modal
- **Screen Reader Support:** Announcements for form errors and success states
- **Color Contrast:** High contrast mode support with enhanced focus states
- **Focus Management:** Proper focus restoration when modal closes

### 5.3 Responsive Design
- **Mobile-First:** Progressive enhancement from mobile (320px) to desktop (1280px+)
- **Touch-Friendly:** Large touch targets (minimum 44px) on mobile devices
- **Viewport Optimization:** Modal scales appropriately across all device sizes
- **Form Layout:** Stacked form layout on mobile, side-by-side on larger screens

### 5.4 Performance Optimization
- **Conditional Loading:** Assets only load on WooCommerce product pages
- **Modern CSS:** Uses CSS Grid, Flexbox, and modern properties for efficiency
- **Minimal JavaScript:** Vanilla JS implementation without heavy frameworks
- **Optimized AJAX:** Proper nonce verification and error handling
- **Database Optimization:** Indexed database table for efficient queries

### 5.5 Security Considerations
- **Nonce Verification:** All AJAX requests include WordPress nonce validation
- **Input Sanitization:** All form inputs properly sanitized using WordPress functions
- **Rate Limiting:** Consider implementing rate limiting for form submissions
- **Data Validation:** Both frontend and backend validation for security
- **SQL Injection Prevention:** Uses WordPress wpdb prepared statements

## 6. User Experience Flow

### 6.1 Product Page Experience
1. User views WooCommerce product page
2. Sees professional "Book Request System" notice
3. Large, prominent "Request This Book" button with MTS branding
4. Button includes loading state when clicked

### 6.2 Modal Interaction
1. Modal opens with backdrop blur and professional animation  
2. Pre-populated with book title and product information
3. Clean, accessible form with real-time validation
4. Clear field labels and helpful instructions
5. Professional error handling with specific messages

### 6.3 Form Submission
1. Comprehensive validation before submission
2. Loading states with spinner animation
3. Success message with confirmation details
4. Email confirmations to both user and admin
5. Proper error recovery with actionable messages

### 6.4 Post-Submission
1. Success screen with clear next steps
2. Auto-close functionality after 5 seconds
3. Email confirmation with request details
4. Admin notification for request processing
5. Database storage for request tracking

## 7. Integration Notes

### 7.1 WooCommerce Integration
- Replaces default add-to-cart functionality seamlessly
- Maintains WooCommerce action hooks for plugin compatibility
- Uses WooCommerce product data for form pre-population
- Compatible with WooCommerce themes and plugins

### 7.2 WordPress Integration  
- Uses WordPress AJAX handling (`wp_ajax_` hooks)
- Integrates with WordPress mail system (`wp_mail()`)
- Uses WordPress nonce system for security
- Compatible with WordPress multisite installations

### 7.3 Theme Integration
- Matches existing MTS color palette and typography
- Uses same grid system as MTS blocks
- Integrates with theme's responsive breakpoints  
- Follows theme's button and form styling patterns

### 7.4 Plugin Compatibility
- Compatible with caching plugins (AJAX requests bypass cache)
- Works with security plugins using standard WordPress hooks
- Compatible with form plugins and anti-spam solutions
- Extensible for integration with CRM systems

## 8. Future Enhancements

### 8.1 Admin Dashboard
- Consider adding WordPress admin interface for managing requests
- Request status tracking and communication system
- Analytics for request patterns and approval rates
- Bulk processing tools for administrators

### 8.2 User Account Integration
- Integration with WordPress user accounts for returning users
- Request history for logged-in users
- Save draft functionality for complex requests
- Preferred communication settings

### 8.3 Advanced Features
- File upload capability for supporting documents
- Request categorization and tagging system
- Automated approval workflow for certain criteria
- Integration with external systems (CRM, fulfillment)

This comprehensive implementation plan provides a professional, accessible, and user-friendly book request system that seamlessly integrates with the existing MTS design system and WordPress/WooCommerce infrastructure.