<?php
/**
 * Simple product add to cart - Modified for Book Request System
 * MTS Knowledge Hub - Marpa Translation Society
 */

defined('ABSPATH') || exit;

global $product;

if (!$product->is_in_stock()) {
    return;
}

// Check if user is logged in
if (!is_user_logged_in()) {
    echo '<div class="woocommerce-info wc-nonpurchasable-message">';
    echo '<p><strong>Book Request Required</strong></p>';
    echo '<p>To request this book, please <a href="' . wp_login_url(get_permalink()) . '">login</a> or <a href="' . wp_registration_url() . '">register</a> for an account.</p>';
    echo '<p><em>Note: Books are provided free of charge. You will only pay for delivery costs.</em></p>';
    echo '</div>';
    return;
}

// Check if user already has an approved request for this product
$current_user_id = get_current_user_id();
$has_approved_request = customer_has_approved_request($current_user_id, $product->get_id());

if ($has_approved_request) {
    echo '<div class="woocommerce-message">';
    echo '<p><strong>Request Approved!</strong></p>';
    echo '<p>Your book request has been approved. This book should be available in your cart.</p>';
    echo '<p><a href="' . wc_get_cart_url() . '" class="button wc-forward">View Cart</a></p>';
    echo '</div>';
    return;
}

// Check if user has a pending request
$pending_request = get_posts(array(
    'post_type' => 'book_request',
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => 'customer_id',
            'value' => $current_user_id,
            'compare' => '='
        ),
        array(
            'key' => 'product_id',
            'value' => $product->get_id(),
            'compare' => '='
        ),
        array(
            'key' => 'request_status',
            'value' => 'new',
            'compare' => '='
        )
    ),
    'posts_per_page' => 1,
    'post_status' => 'publish'
));

if (!empty($pending_request)) {
    echo '<div class="woocommerce-info">';
    echo '<p><strong>Request Pending</strong></p>';
    echo '<p>You have already submitted a request for this book. We will review it and notify you by email.</p>';
    echo '</div>';
    return;
}

// Show stock info
echo wc_get_stock_html($product);

?>

<div class="book-request-section">
    <div class="book-request-info">
        <h4>Request This Book</h4>
        <p>This book is provided <strong>free of charge</strong> by the Marpa Translation Society. You will only pay for delivery costs.</p>
        <p>To ensure our books reach those who will make good use of them, please tell us why you would like to receive this book.</p>
    </div>

    <?php do_action('woocommerce_before_add_to_cart_form'); ?>

    <div class="book-request-form-container">
        <button type="button" id="request-book-btn" class="single_add_to_cart_button button alt wp-element-button" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
            Request This Book
        </button>
    </div>

    <?php do_action('woocommerce_after_add_to_cart_form'); ?>
</div>

<!-- Book Request Modal -->
<div id="book-request-modal" class="book-request-modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Request Book: <?php echo esc_html($product->get_name()); ?></h3>
            <button type="button" class="modal-close" id="close-modal-btn">
                <span>&times;</span>
            </button>
        </div>
        
        <div class="modal-body">
            <form id="book-request-form" method="post">
                <?php wp_nonce_field('book_request_nonce', 'book_request_nonce'); ?>
                <input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>">
                
                <div class="form-group">
                    <label for="request-reason">Why would you like to receive this book? *</label>
                    <textarea 
                        id="request-reason" 
                        name="reason" 
                        rows="6" 
                        required
                        placeholder="Please explain how you plan to use this book, your background with Buddhist studies, or why this particular text is important for your practice or research..."
                    ></textarea>
                    <div class="field-help">
                        <small>Help us understand how this book will benefit your studies or practice. This information helps us prioritize requests and ensure books reach dedicated practitioners and scholars.</small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="button secondary" id="cancel-request-btn">Cancel</button>
                    <button type="submit" class="button primary" id="submit-request-btn">Submit Request</button>
                </div>
            </form>
            
            <div id="request-response" class="request-response" style="display: none;"></div>
        </div>
    </div>
</div>

<style>
/* Book Request Section Styles */
.book-request-section {
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid var(--wp--preset--color--primary, #14375d);
}

.book-request-info h4 {
    color: var(--wp--preset--color--primary, #14375d);
    margin-bottom: 10px;
    font-size: 1.2em;
}

.book-request-info p {
    margin-bottom: 10px;
    line-height: 1.6;
}

.book-request-form-container {
    margin-top: 20px;
}

/* Modal Styles */
.book-request-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 90%;
    max-height: 90%;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e5e5e5;
    background: var(--wp--preset--color--primary, #14375d);
    color: white;
    border-radius: 12px 12px 0 0;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.3em;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    color: white;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.2s;
}

.modal-close:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.modal-body {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--wp--preset--color--primary, #14375d);
}

.form-group textarea {
    width: 100%;
    border: 2px solid #e5e5e5;
    border-radius: 6px;
    padding: 12px;
    font-family: inherit;
    font-size: 14px;
    line-height: 1.5;
    resize: vertical;
    transition: border-color 0.2s;
}

.form-group textarea:focus {
    outline: none;
    border-color: var(--wp--preset--color--primary, #14375d);
}

.field-help {
    margin-top: 8px;
}

.field-help small {
    color: #666;
    font-size: 13px;
    line-height: 1.4;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 25px;
}

.form-actions .button {
    padding: 12px 24px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.button.secondary {
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    color: #495057;
}

.button.secondary:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.button.primary {
    background: var(--wp--preset--color--primary, #14375d);
    border: 2px solid var(--wp--preset--color--primary, #14375d);
    color: white;
}

.button.primary:hover {
    background: var(--wp--preset--color--burgundy, #8b1538);
    border-color: var(--wp--preset--color--burgundy, #8b1538);
}

.request-response {
    padding: 15px;
    border-radius: 6px;
    margin-top: 20px;
}

.request-response.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.request-response.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 20px;
    }
    
    .modal-header,
    .modal-body {
        padding: 15px 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .button {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('book-request-modal');
    const requestBtn = document.getElementById('request-book-btn');
    const closeBtn = document.getElementById('close-modal-btn');
    const cancelBtn = document.getElementById('cancel-request-btn');
    const backdrop = modal?.querySelector('.modal-backdrop');
    const form = document.getElementById('book-request-form');
    const responseDiv = document.getElementById('request-response');
    
    // Open modal
    requestBtn?.addEventListener('click', function() {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Focus first field
        const firstField = modal.querySelector('#request-reason');
        if (firstField) {
            setTimeout(() => firstField.focus(), 100);
        }
    });
    
    // Close modal functions
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        form.reset();
        responseDiv.style.display = 'none';
        responseDiv.className = 'request-response';
        responseDiv.innerHTML = '';
    }
    
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    backdrop?.addEventListener('click', closeModal);
    
    // ESC key close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
    });
    
    // Form submission
    form?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submit-request-btn');
        const originalText = submitBtn.textContent;
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
        
        const formData = new FormData(form);
        formData.append('action', 'submit_book_request');
        formData.append('nonce', document.querySelector('[name="book_request_nonce"]').value);
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            responseDiv.style.display = 'block';
            
            if (data.success) {
                responseDiv.className = 'request-response success';
                responseDiv.innerHTML = '<strong>Success!</strong><br>' + data.data;
                
                // Hide form, show success message
                form.style.display = 'none';
                
                // Auto-close after 3 seconds
                setTimeout(() => {
                    closeModal();
                    // Refresh page to show updated status
                    window.location.reload();
                }, 3000);
                
            } else {
                responseDiv.className = 'request-response error';
                responseDiv.innerHTML = '<strong>Error:</strong><br>' + data.data;
            }
        })
        .catch(error => {
            responseDiv.style.display = 'block';
            responseDiv.className = 'request-response error';
            responseDiv.innerHTML = '<strong>Error:</strong><br>Something went wrong. Please try again.';
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });
});
</script>