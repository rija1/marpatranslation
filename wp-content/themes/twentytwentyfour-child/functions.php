<?php

/**
 * =============================================================================
 * MTS KNOWLEDGE HUB - FUNCTIONS.PHP
 * Organized and cleaned functions for Marpa Translation Society website
 * =============================================================================
 */

/**
 * SECTION 1: BASIC THEME SETUP
 * =============================================================================
 */

/**
 * Debug helper function
 */
function pa($truc, $die = false, $vdump = false) {
    echo '<pre>';
    if ($vdump) {
        var_dump($truc);
    } else {
        print_r($truc);
    }
    echo '</pre>';
    if ($die) {
        die();
    }
}

/**
 * Enqueue theme styles
 */
function my_theme_enqueue_styles() {
    $parent_style = 'twentytwentyfour-style';
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array($parent_style),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

/**
 * Enqueue theme scripts
 */
function my_theme_scripts() {
    wp_enqueue_script('bsf', get_stylesheet_directory_uri() . '/js/bsf.js');
    wp_enqueue_script('chartjs', get_stylesheet_directory_uri() . '/js/chart.js');
}
add_action('wp_enqueue_scripts', 'my_theme_scripts');

/**
 * Add MTS color palette support for Gutenberg
 */
function mts_setup_theme_colors() {
    add_theme_support('editor-color-palette', array(
        array(
            'name'  => 'MTS Primary',
            'slug'  => 'primary',
            'color' => '#14375d',
        ),
        array(
            'name'  => 'MTS Burgundy', 
            'slug'  => 'burgundy',
            'color' => '#8b1538',
        ),
        array(
            'name'  => 'MTS Gold',
            'slug'  => 'gold', 
            'color' => '#d4af37',
        ),
        array(
            'name'  => 'MTS Parchment',
            'slug'  => 'parchment',
            'color' => '#f7f4f0',
        ),
        array(
            'name'  => 'MTS Text',
            'slug'  => 'text',
            'color' => '#2d3748',
        ),
        array(
            'name'  => 'MTS Light Text', 
            'slug'  => 'light-text',
            'color' => '#4a5568',
        ),
        array(
            'name'  => 'White 95%',
            'slug'  => 'white-95',
            'color' => 'rgba(255,255,255,0.95)',
        ),
        array(
            'name'  => 'White 10%',
            'slug'  => 'white-10',
            'color' => 'rgba(255,255,255,0.1)',
        ),
    ));
}
add_action('after_setup_theme', 'mts_setup_theme_colors');

/**
 * SECTION 2: WOOCOMMERCE CUSTOMIZATIONS
 * =============================================================================
 */

/**
 * Make products purchasable only if user has approved request
 */
add_filter('woocommerce_is_purchasable', 'mts_is_purchasable', 10, 2);
function mts_is_purchasable($is_purchasable, $product) {
    // If user is not logged in, not purchasable
    if (!is_user_logged_in()) {
        return false;
    }
    
    // Check if current user has approved request for this product
    $current_user_id = get_current_user_id();
    $product_id = $product->get_id();
    
    return customer_has_approved_request($current_user_id, $product_id);
}

/**
 * Check for approved book request
 */
function approved_book_request() {
    return false; // Placeholder for future functionality
}

/**
 * Placeholder function to prevent fatal errors
 */
if (!function_exists('mts_add_html_before_add_to_cart_form')) {
    function mts_add_html_before_add_to_cart_form() {
        // Placeholder - can be implemented later if needed
    }
}

/**
 * Add book request button
 */
function mts_add_book_request_button() {
    echo '<button type="submit" name="book-request" class="single_add_to_cart_button button alt wp-element-button">Request This Book</button>';
}

// TEST: Direct file write to see if functions.php is loading
file_put_contents('/Users/reedz/Local Sites/marpatranslation/logs/php/test.log', "FUNCTIONS.PHP LOADED AT: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

/**
 * TEMPORARY: Debug function to see which hooks fire for term_usage saves
 */
function debug_term_usage_hooks($post_id = null, $post = null, $update = null) {
    if ($post && $post->post_type === 'term_usage') {
        error_log("HOOK DEBUG: " . current_action() . " fired for term_usage {$post_id}, title: '{$post->post_title}'");
    }
}

// Hook into various save actions to see which ones fire
add_action('save_post', 'debug_term_usage_hooks', 10, 3);
add_action('wp_insert_post', 'debug_term_usage_hooks', 10, 3);
add_action('post_updated', 'debug_term_usage_hooks', 10, 3);

// Test Pods-specific hooks
add_action('pods_api_post_save_pod_item', function($pieces, $is_new_item, $id) {
    $post = get_post($id);
    if ($post && $post->post_type === 'term_usage') {
        error_log("PODS DEBUG: pods_api_post_save_pod_item fired for term_usage {$id}");
    }
}, 10, 3);

add_action('pods_api_post_save_pod_item_term_usage', function($pieces, $is_new_item, $id) {
    error_log("PODS DEBUG: pods_api_post_save_pod_item_term_usage fired for {$id}");
}, 10, 3);

// Try more hooks
add_action('edit_post', 'debug_term_usage_hooks', 10, 3);
add_action('post_updated_messages', function($messages) {
    error_log("HOOK DEBUG: post_updated_messages fired");
    return $messages;
});

// Try checking on admin_init if any new term_usage posts were created recently
add_action('admin_init', function() {
    static $checked = false;
    if (!$checked && is_admin()) {
        $checked = true;
        global $wpdb;
        $recent_posts = $wpdb->get_results("SELECT ID, post_title FROM wp_posts WHERE post_type = 'term_usage' AND post_date > DATE_SUB(NOW(), INTERVAL 1 HOUR) ORDER BY post_date DESC LIMIT 5");
        foreach ($recent_posts as $post) {
            error_log("RECENT TERM_USAGE: ID {$post->ID}, title: '{$post->post_title}'");
        }
    }
});

/**
 * WooCommerce Cart and Quantity Restrictions
 */

// Prevent quantity changes in cart
add_filter('woocommerce_is_sold_individually', 'mts_force_individual_sale', 10, 2);
function mts_force_individual_sale($return, $product) {
    return true; // Force all products to be sold individually (quantity = 1)
}

// Remove quantity selectors from cart
add_filter('woocommerce_cart_item_quantity', 'mts_remove_cart_quantity_selector', 10, 3);
function mts_remove_cart_quantity_selector($product_quantity, $cart_item_key, $cart_item) {
    return '<span class="quantity">1</span>';
}

// Validate cart items against approved requests
add_action('woocommerce_check_cart_items', 'mts_validate_cart_items');
function mts_validate_cart_items() {
    if (!is_user_logged_in()) {
        return;
    }
    
    $current_user_id = get_current_user_id();
    
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];
        
        // Check if user has approved request for this product
        if (!customer_has_approved_request($current_user_id, $product_id)) {
            WC()->cart->remove_cart_item($cart_item_key);
            wc_add_notice('A book was removed from your cart because your request is no longer approved. Please request the book again if needed.', 'error');
        }
        
        // Ensure quantity is always 1
        if ($cart_item['quantity'] > 1) {
            WC()->cart->set_quantity($cart_item_key, 1);
            wc_add_notice('Book quantities have been limited to 1 per approved request.', 'notice');
        }
    }
}

// Prevent adding to cart without approved request
add_filter('woocommerce_add_to_cart_validation', 'mts_validate_add_to_cart', 10, 3);
function mts_validate_add_to_cart($passed, $product_id, $quantity) {
    if (!is_user_logged_in()) {
        wc_add_notice('You must be logged in to add books to your cart.', 'error');
        return false;
    }
    
    $current_user_id = get_current_user_id();
    
    if (!customer_has_approved_request($current_user_id, $product_id)) {
        wc_add_notice('You can only add books to your cart that have been approved through a book request.', 'error');
        return false;
    }
    
    // Force quantity to 1
    if ($quantity > 1) {
        wc_add_notice('You can only order 1 copy of each book.', 'notice');
    }
    
    return $passed;
}

// Auto-add approved products to cart (improved version)
function mts_auto_add_approved_book_to_cart($customer_id, $product_id) {
    // Switch to the customer's session
    if (get_current_user_id() !== intval($customer_id)) {
        // We can't directly manipulate another user's cart in WordPress
        // Instead, we'll store a flag that the product should be added
        // when the customer next visits the site
        update_user_meta($customer_id, 'pending_approved_book_' . $product_id, time());
        return true;
    }
    
    // Add to current user's cart
    $added = WC()->cart->add_to_cart($product_id, 1);
    
    if ($added) {
        wc_add_notice('Your approved book has been added to your cart. You can now proceed to checkout.', 'success');
        return true;
    }
    
    return false;
}

// Check for pending approved books when user logs in or visits site
add_action('wp_login', 'mts_check_pending_approved_books', 10, 2);
add_action('template_redirect', 'mts_check_pending_approved_books_on_visit');

function mts_check_pending_approved_books($user_login, $user) {
    mts_process_pending_approved_books($user->ID);
}

function mts_check_pending_approved_books_on_visit() {
    if (is_user_logged_in() && !wp_doing_ajax() && !is_admin()) {
        $user_id = get_current_user_id();
        
        // Check only once per session
        if (!WC()->session->get('checked_pending_books')) {
            mts_process_pending_approved_books($user_id);
            WC()->session->set('checked_pending_books', true);
        }
    }
}

function mts_process_pending_approved_books($user_id) {
    $user_meta = get_user_meta($user_id);
    $pending_books = array();
    
    foreach ($user_meta as $key => $value) {
        if (strpos($key, 'pending_approved_book_') === 0) {
            $product_id = str_replace('pending_approved_book_', '', $key);
            $pending_books[$product_id] = $value[0];
        }
    }
    
    foreach ($pending_books as $product_id => $timestamp) {
        // Check if the approval is still valid
        if (customer_has_approved_request($user_id, $product_id)) {
            // Add to cart
            $added = WC()->cart->add_to_cart($product_id, 1);
            
            if ($added) {
                // Remove the pending flag
                delete_user_meta($user_id, 'pending_approved_book_' . $product_id);
                
                // Show success message
                $product = wc_get_product($product_id);
                if ($product) {
                    wc_add_notice(sprintf('Great! "%s" has been added to your cart as your request was approved.', $product->get_name()), 'success');
                }
            }
        } else {
            // Remove expired pending flag
            delete_user_meta($user_id, 'pending_approved_book_' . $product_id);
        }
    }
}

/**
 * SECTION 3: BOOK REQUEST SYSTEM
 * =============================================================================
 */

/**
 * Register book_request custom post type
 */
function register_book_request_post_type() {
    $labels = array(
        'name'                  => 'Book Requests',
        'singular_name'         => 'Book Request',
        'menu_name'             => 'Book Requests',
        'name_admin_bar'        => 'Book Request',
        'archives'              => 'Book Request Archives',
        'attributes'            => 'Book Request Attributes',
        'parent_item_colon'     => 'Parent Book Request:',
        'all_items'             => 'All Book Requests',
        'add_new_item'          => 'Add New Book Request',
        'add_new'               => 'Add New',
        'new_item'              => 'New Book Request',
        'edit_item'             => 'Edit Book Request',
        'update_item'           => 'Update Book Request',
        'view_item'             => 'View Book Request',
        'view_items'            => 'View Book Requests',
        'search_items'          => 'Search Book Requests',
        'not_found'             => 'Not found',
        'not_found_in_trash'    => 'Not found in Trash',
        'featured_image'        => 'Featured Image',
        'set_featured_image'    => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image'    => 'Use as featured image',
        'insert_into_item'      => 'Insert into book request',
        'uploaded_to_this_item' => 'Uploaded to this book request',
        'items_list'            => 'Book requests list',
        'items_list_navigation' => 'Book requests list navigation',
        'filter_items_list'     => 'Filter book requests list',
    );

    $args = array(
        'label'                 => 'Book Request',
        'description'           => 'Book requests from customers',
        'labels'                => $labels,
        'supports'              => array('title', 'editor'),
        'taxonomies'            => array(),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 58,
        'menu_icon'             => 'dashicons-book-alt',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'post',
        'capabilities'          => array(
            'create_posts' => false, // Remove "Add New" button from admin
        ),
        'map_meta_cap'          => true,
    );

    register_post_type('book_request', $args);
}
add_action('init', 'register_book_request_post_type', 0);

/**
 * Register custom fields for book_request post type using Pods
 */
function setup_book_request_pods_fields() {
    // Check if Pods is active
    if (!function_exists('pods_api')) {
        return;
    }

    $pods_api = pods_api();

    // Check if book_request pod already exists
    $pod = $pods_api->load_pod(array('name' => 'book_request'));
    
    if (!$pod) {
        // Create the Pod
        $pod_params = array(
            'name' => 'book_request',
            'label' => 'Book Request',
            'type' => 'post_type',
            'storage' => 'meta',
            'object' => 'book_request',
        );
        
        $pod_id = $pods_api->save_pod($pod_params);
    } else {
        $pod_id = $pod['id'];
    }

    // Define fields
    $fields = array(
        array(
            'name' => 'customer_id',
            'label' => 'Customer',
            'type' => 'pick',
            'pick_object' => 'user',
            'pick_format_type' => 'single',
            'pick_format_single' => 'autocomplete',
            'required' => '1',
        ),
        array(
            'name' => 'product_id',
            'label' => 'Product',
            'type' => 'pick',
            'pick_object' => 'post_type',
            'pick_val' => 'product',
            'pick_format_type' => 'single',
            'pick_format_single' => 'autocomplete',
            'required' => '1',
        ),
        array(
            'name' => 'request_reason',
            'label' => 'Reason for Request',
            'type' => 'wysiwyg',
            'required' => '1',
            'wysiwyg_allowed_html_tags' => 'strong,em,p,br,ul,ol,li',
        ),
        array(
            'name' => 'request_status',
            'label' => 'Status',
            'type' => 'pick',
            'pick_object' => 'custom-simple',
            'pick_custom' => "new|New Request\napproved|Approved\nrejected|Rejected",
            'default_value' => 'new',
            'required' => '1',
        ),
        array(
            'name' => 'admin_notes',
            'label' => 'Admin Notes',
            'type' => 'wysiwyg',
            'wysiwyg_allowed_html_tags' => 'strong,em,p,br,ul,ol,li',
        ),
        array(
            'name' => 'request_date',
            'label' => 'Request Date',
            'type' => 'datetime',
            'datetime_format' => 'mdy',
            'datetime_time_type' => '12',
        ),
        array(
            'name' => 'response_date',
            'label' => 'Response Date',
            'type' => 'datetime',
            'datetime_format' => 'mdy',
            'datetime_time_type' => '12',
        ),
    );

    // Add each field
    foreach ($fields as $field_data) {
        $field_data['pod_id'] = $pod_id;
        
        // Check if field already exists
        $existing_field = $pods_api->load_field(array('name' => $field_data['name'], 'pod' => 'book_request'));
        
        if (!$existing_field) {
            $pods_api->save_field($field_data);
        }
    }
}
add_action('init', 'setup_book_request_pods_fields', 20);

/**
 * AJAX handler for book request submissions
 */
add_action('wp_ajax_submit_book_request', 'handle_book_request_submission');
function handle_book_request_submission() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'book_request_nonce')) {
        wp_send_json_error('Security check failed');
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to submit a book request');
    }

    // Validate required fields
    $product_id = intval($_POST['product_id']);
    $reason = sanitize_textarea_field($_POST['reason']);

    if (!$product_id || empty($reason)) {
        wp_send_json_error('All fields are required');
    }

    // Check if product exists and is a WooCommerce product
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error('Invalid product');
    }

    $current_user_id = get_current_user_id();

    // Check if user already has a pending request for this product
    $existing_request = get_posts(array(
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
                'value' => $product_id,
                'compare' => '='
            ),
            array(
                'key' => 'request_status',
                'value' => array('new', 'approved'),
                'compare' => 'IN'
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ));

    if (!empty($existing_request)) {
        wp_send_json_error('You already have a pending or approved request for this book');
    }

    // Create the book request post
    $post_data = array(
        'post_title' => sprintf('Book Request: %s - %s', 
            $product->get_name(), 
            get_userdata($current_user_id)->display_name
        ),
        'post_type' => 'book_request',
        'post_status' => 'publish',
        'meta_input' => array(
            'customer_id' => $current_user_id,
            'product_id' => $product_id,
            'request_reason' => $reason,
            'request_status' => 'new',
            'request_date' => current_time('mysql'),
        )
    );

    $post_id = wp_insert_post($post_data);

    if ($post_id) {
        // Send email notification to admin
        $admin_email = get_option('admin_email');
        $subject = 'New Book Request Submitted';
        $message = sprintf(
            "A new book request has been submitted:\n\n" .
            "Product: %s\n" .
            "Customer: %s (%s)\n" .
            "Reason: %s\n\n" .
            "Review and respond: %s",
            $product->get_name(),
            get_userdata($current_user_id)->display_name,
            get_userdata($current_user_id)->user_email,
            $reason,
            admin_url('post.php?post=' . $post_id . '&action=edit')
        );
        
        wp_mail($admin_email, $subject, $message);
        
        wp_send_json_success('Your book request has been submitted successfully. You will be notified when it is reviewed.');
    } else {
        wp_send_json_error('Failed to submit request. Please try again.');
    }
}

/**
 * Handle book request approval
 */
function approve_book_request($post_id) {
    $customer_id = get_post_meta($post_id, 'customer_id', true);
    $product_id = get_post_meta($post_id, 'product_id', true);
    
    if (!$customer_id || !$product_id) {
        return false;
    }

    // Update request_status
    update_post_meta($post_id, 'request_status', 'approved');
    update_post_meta($post_id, 'response_date', current_time('mysql'));
    
    // Add product to customer's cart
    $customer = new WC_Customer($customer_id);
    
    // We need to temporarily make the product purchasable to add it to cart
    add_filter('woocommerce_is_purchasable', function($is_purchasable, $product) use ($product_id) {
        if ($product->get_id() == $product_id) {
            return true;
        }
        return $is_purchasable;
    }, 10, 2);
    
    // Add to cart for this specific customer
    WC()->cart->empty_cart();
    $cart_item_key = WC()->cart->add_to_cart($product_id, 1);
    
    if ($cart_item_key) {
        // Store the approved request ID with the cart item for later validation
        WC()->cart->cart_contents[$cart_item_key]['book_request_id'] = $post_id;
        WC()->cart->set_session();
        
        // Send email notification to customer
        $customer_email = get_userdata($customer_id)->user_email;
        $product = wc_get_product($product_id);
        
        $subject = 'Book Request Approved';
        $message = sprintf(
            "Great news! Your book request has been approved.\n\n" .
            "Book: %s\n\n" .
            "The book has been added to your cart. You can now proceed to checkout to arrange delivery.\n\n" .
            "Visit your cart: %s",
            $product->get_name(),
            wc_get_cart_url()
        );
        
        wp_mail($customer_email, $subject, $message);
        
        return true;
    }
    
    return false;
}

/**
 * Check if customer has approved request for product
 */
function customer_has_approved_request($customer_id, $product_id) {
    $approved_request = get_posts(array(
        'post_type' => 'book_request',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'customer_id',
                'value' => $customer_id,
                'compare' => '='
            ),
            array(
                'key' => 'product_id',
                'value' => $product_id,
                'compare' => '='
            ),
            array(
                'key' => 'request_status',
                'value' => 'approved',
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ));

    return !empty($approved_request);
}

/**
 * Admin interface customizations for book_request post type
 */

// Add custom admin columns for book_request
add_filter('manage_book_request_posts_columns', 'mts_add_book_request_admin_columns');
add_action('manage_book_request_posts_custom_column', 'mts_display_book_request_admin_columns', 10, 2);
add_filter('manage_edit-book_request_sortable_columns', 'mts_make_book_request_columns_sortable');

function mts_add_book_request_admin_columns($columns) {
    return array(
        'cb' => '<input type="checkbox" />',
        'customer' => 'Customer',
        'product' => 'Book',
        'reason' => 'Reason',
        'request_status' => 'Status',
        'request_date' => 'Request Date',
        'actions' => 'Actions'
    );
}

function mts_display_book_request_admin_columns($column, $post_id) {
    switch ($column) {
        case 'customer':
            $customer_id = get_post_meta($post_id, 'customer_id', true);
            if ($customer_id) {
                $user = get_userdata($customer_id);
                if ($user) {
                    echo '<strong>' . esc_html($user->display_name) . '</strong><br>';
                    echo '<a href="mailto:' . esc_attr($user->user_email) . '">' . esc_html($user->user_email) . '</a>';
                }
            } else {
                echo '<span style="color: #999;">Unknown Customer</span>';
            }
            break;

        case 'product':
            $product_id = get_post_meta($post_id, 'product_id', true);
            if ($product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    $edit_link = get_edit_post_link($product_id);
                    if ($edit_link) {
                        echo '<a href="' . esc_url($edit_link) . '" target="_blank"><strong>' . esc_html($product->get_name()) . '</strong></a>';
                    } else {
                        echo '<strong>' . esc_html($product->get_name()) . '</strong>';
                    }
                }
            } else {
                echo '<span style="color: #999;">Unknown Product</span>';
            }
            break;

        case 'reason':
            $reason = get_post_meta($post_id, 'request_reason', true);
            if ($reason) {
                $truncated = wp_trim_words(strip_tags($reason), 15, '...');
                echo '<span title="' . esc_attr(strip_tags($reason)) . '">' . esc_html($truncated) . '</span>';
            } else {
                echo '<span style="color: #999;">No reason provided</span>';
            }
            break;

        case 'request_status':
            $request_status = get_post_meta($post_id, 'request_status', true);
            $status_colors = array(
                'new' => '#e74c3c',
                'approved' => '#27ae60',
                'rejected' => '#e67e22'
            );
            $status_labels = array(
                'new' => 'New Request',
                'approved' => 'Approved',
                'rejected' => 'Rejected'
            );
            
            $color = isset($status_colors[$request_status]) ? $status_colors[$request_status] : '#999';
            $label = isset($status_labels[$request_status]) ? $status_labels[$request_status] : ucfirst($request_status);
            
            echo '<span style="background: ' . esc_attr($color) . '; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">';
            echo esc_html($label);
            echo '</span>';
            break;

        case 'request_date':
            $date = get_post_meta($post_id, 'request_date', true);
            if ($date) {
                $formatted_date = date('M j, Y', strtotime($date));
                $time = date('g:i A', strtotime($date));
                echo '<strong>' . esc_html($formatted_date) . '</strong><br>';
                echo '<small style="color: #666;">' . esc_html($time) . '</small>';
            } else {
                echo esc_html(get_the_date('M j, Y', $post_id));
            }
            break;

        case 'actions':
            $request_status = get_post_meta($post_id, 'request_status', true);
            $nonce = wp_create_nonce('book_request_action_' . $post_id);
            
            echo '<div class="book-request-actions">';
            
            if ($request_status === 'new') {
                echo '<a href="#" class="button button-small button-approve" data-request-id="' . esc_attr($post_id) . '" data-nonce="' . esc_attr($nonce) . '" style="background: #27ae60; color: white; margin-right: 5px;">Approve</a>';
                echo '<a href="#" class="button button-small button-reject" data-request-id="' . esc_attr($post_id) . '" data-nonce="' . esc_attr($nonce) . '" style="background: #e74c3c; color: white;">Reject</a>';
            } else if ($request_status === 'approved') {
                echo '<span style="color: #27ae60; font-weight: bold;">✓ Approved</span>';
            } else if ($request_status === 'rejected') {
                echo '<span style="color: #e74c3c; font-weight: bold;">✗ Rejected</span>';
            }
            
            echo '</div>';
            break;
    }
}

function mts_make_book_request_columns_sortable($columns) {
    $columns['request_status'] = 'request_status';
    $columns['request_date'] = 'request_date';
    return $columns;
}

// Add admin filters for book requests
add_action('restrict_manage_posts', 'mts_add_book_request_admin_filters');
function mts_add_book_request_admin_filters() {
    global $typenow;
    
    if ($typenow === 'book_request') {
        // Status filter
        echo '<select name="filter_book_request_status" id="filter_book_request_status">';
        echo '<option value="">All Statuses</option>';
        
        $statuses = array(
            'new' => 'New Requests',
            'approved' => 'Approved',
            'rejected' => 'Rejected'
        );
        
        $selected_status = isset($_GET['filter_book_request_status']) ? $_GET['filter_book_request_status'] : '';
        
        foreach ($statuses as $value => $label) {
            $selected = selected($selected_status, $value, false);
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }
}

// Handle admin filter queries
add_filter('parse_query', 'mts_book_request_admin_filter_query');
function mts_book_request_admin_filter_query($query) {
    global $pagenow, $typenow;
    
    if ($pagenow !== 'edit.php' || $typenow !== 'book_request') {
        return;
    }
    
    if (!empty($_GET['filter_book_request_status'])) {
        $query->set('meta_key', 'request_status');
        $query->set('meta_value', sanitize_text_field($_GET['filter_book_request_status']));
    }
}

// Handle admin column sorting
add_action('pre_get_posts', 'mts_book_request_admin_columns_orderby');
function mts_book_request_admin_columns_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('request_status' === $orderby) {
        $query->set('meta_key', 'request_status');
        $query->set('orderby', 'meta_value');
    }
    
    if ('request_date' === $orderby) {
        $query->set('meta_key', 'request_date');
        $query->set('orderby', 'meta_value');
    }
}

// AJAX handlers for approve/reject actions
add_action('wp_ajax_approve_book_request', 'handle_approve_book_request');
add_action('wp_ajax_reject_book_request', 'handle_reject_book_request');

function handle_approve_book_request() {
    $post_id = intval($_POST['post_id']);
    $nonce = sanitize_text_field($_POST['nonce']);
    
    if (!wp_verify_nonce($nonce, 'book_request_action_' . $post_id)) {
        wp_send_json_error('Security check failed');
    }
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    // Update request_status and response date
    update_post_meta($post_id, 'request_status', 'approved');
    update_post_meta($post_id, 'response_date', current_time('mysql'));
    
    // Get request details
    $customer_id = get_post_meta($post_id, 'customer_id', true);
    $product_id = get_post_meta($post_id, 'product_id', true);
    
    if ($customer_id && $product_id) {
        $customer = get_userdata($customer_id);
        $product = wc_get_product($product_id);
        
        // Auto-add book to customer's cart (when they next visit)
        mts_auto_add_approved_book_to_cart($customer_id, $product_id);
        
        // Send approval email
        if ($customer && $product) {
            $subject = 'Book Request Approved - ' . $product->get_name();
            $message = sprintf(
                "Dear %s,\n\n" .
                "Great news! Your book request has been approved.\n\n" .
                "Book: %s\n\n" .
                "The next time you visit our website, this book will automatically be added to your cart. " .
                "You can then proceed with checkout to arrange delivery. " .
                "Please note that while the book is provided free of charge, delivery fees will apply.\n\n" .
                "Visit the book page: %s\n" .
                "Or go directly to your cart: %s\n\n" .
                "Thank you for your interest in Buddhist teachings.\n\n" .
                "Best regards,\n" .
                "Marpa Translation Society",
                $customer->display_name,
                $product->get_name(),
                $product->get_permalink(),
                wc_get_cart_url()
            );
            
            wp_mail($customer->user_email, $subject, $message);
        }
    }
    
    wp_send_json_success('Book request approved successfully');
}

function handle_reject_book_request() {
    $post_id = intval($_POST['post_id']);
    $nonce = sanitize_text_field($_POST['nonce']);
    $reason = sanitize_textarea_field($_POST['reason'] ?? '');
    
    if (!wp_verify_nonce($nonce, 'book_request_action_' . $post_id)) {
        wp_send_json_error('Security check failed');
    }
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    // Update request_status and response date
    update_post_meta($post_id, 'request_status', 'rejected');
    update_post_meta($post_id, 'response_date', current_time('mysql'));
    
    if ($reason) {
        $current_notes = get_post_meta($post_id, 'admin_notes', true);
        $new_notes = $current_notes ? $current_notes . "\n\nRejection reason: " . $reason : "Rejection reason: " . $reason;
        update_post_meta($post_id, 'admin_notes', $new_notes);
    }
    
    // Get request details for email
    $customer_id = get_post_meta($post_id, 'customer_id', true);
    $product_id = get_post_meta($post_id, 'product_id', true);
    
    if ($customer_id && $product_id) {
        $customer = get_userdata($customer_id);
        $product = wc_get_product($product_id);
        
        // Send rejection email
        if ($customer && $product) {
            $subject = 'Book Request Update - ' . $product->get_name();
            $message = sprintf(
                "Dear %s,\n\n" .
                "Thank you for your interest in: %s\n\n" .
                "After reviewing your request, we are unable to approve it at this time.\n\n" .
                "%s\n\n" .
                "If you have any questions, please feel free to contact us.\n\n" .
                "Best regards,\n" .
                "Marpa Translation Society",
                $customer->display_name,
                $product->get_name(),
                $reason ? "Reason: " . $reason : "Please feel free to contact us if you have questions about this decision."
            );
            
            wp_mail($customer->user_email, $subject, $message);
        }
    }
    
    wp_send_json_success('Book request rejected');
}

// Add admin styles and scripts for book requests
add_action('admin_head', 'mts_book_request_admin_styles');
function mts_book_request_admin_styles() {
    global $pagenow, $typenow;
    
    if ($pagenow === 'edit.php' && $typenow === 'book_request') {
        echo '<style>
        .wp-list-table .column-cb { width: 2.2em; }
        .wp-list-table .column-customer { width: 20%; }
        .wp-list-table .column-product { width: 25%; }
        .wp-list-table .column-reason { width: 30%; }
        .wp-list-table .column-request_status { width: 10%; }
        .wp-list-table .column-request_date { width: 12%; }
        .wp-list-table .column-actions { width: 15%; }
        
        .book-request-actions .button {
            font-size: 11px;
            padding: 4px 8px;
            line-height: 1.2;
        }
        </style>';
        
        echo '<script>
        jQuery(document).ready(function($) {
            $(".button-approve").on("click", function(e) {
                e.preventDefault();
                var requestId = $(this).data("request-id");
                var nonce = $(this).data("nonce");
                var button = $(this);
                
                if (confirm("Are you sure you want to approve this book request?")) {
                    button.text("Approving...").prop("disabled", true);
                    
                    $.post(ajaxurl, {
                        action: "approve_book_request",
                        post_id: requestId,
                        nonce: nonce
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert("Error: " + response.data);
                            button.text("Approve").prop("disabled", false);
                        }
                    });
                }
            });
            
            $(".button-reject").on("click", function(e) {
                e.preventDefault();
                var requestId = $(this).data("request-id");
                var nonce = $(this).data("nonce");
                var button = $(this);
                
                var reason = prompt("Please provide a reason for rejection (optional):");
                if (reason !== null) {
                    button.text("Rejecting...").prop("disabled", true);
                    
                    $.post(ajaxurl, {
                        action: "reject_book_request",
                        post_id: requestId,
                        nonce: nonce,
                        reason: reason
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert("Error: " + response.data);
                            button.text("Reject").prop("disabled", false);
                        }
                    });
                }
            });
        });
        </script>';
    }
}

/**
 * SECTION 4: AJAX SEARCH FUNCTIONALITY
 * =============================================================================
 */

/**
 * AJAX search for Tibetan terms
 */
add_action('wp_ajax_search_terms', 'search_tibetan_terms');
add_action('wp_ajax_nopriv_search_terms', 'search_tibetan_terms');

function search_tibetan_terms() {
    $search = sanitize_text_field($_GET['search']);
    $pods = pods('tibetan_term');
    $params = array(
        'where' => "t.post_title LIKE '%{$search}%'",
        'limit' => 10
    );
    
    $pods->find($params);
    $results = array();
    
    while ($pods->fetch()) {
        $results[] = array(
            'label' => $pods->display('post_title'),
            'value' => $pods->display('ID')
        );
    }
    
    wp_send_json($results);
}

/**
 * SECTION 4: DEBUG AND ERROR HANDLING
 * =============================================================================
 */

/**
 * Custom error handler for debugging array to string conversions
 */
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    if (strpos($errstr, 'Array to string conversion') !== false) {
        echo "<pre>";
        echo "Error: $errstr\n";
        echo "File: $errfile\n";
        echo "Line: $errline\n\n";
        echo "Stack trace:\n";
        debug_print_backtrace();
        echo "</pre>";
    }
    return false; // Let PHP handle the error normally too
}
set_error_handler('custom_error_handler');

/**
 * SECTION 5: DYNAMIC TERM USAGE TITLES
 * =============================================================================
 */

/**
 * Enhanced dynamic title generation for term_usage posts
 * Format: [TRANSLATED_TERM] used by [TRANSLATOR(S)] in [TRANSLATION]
 */

// Update post_title in database when post is saved
add_action('save_post', 'update_term_usage_post_title', 999, 3);
add_action('pods_api_post_save_pod_item_product', 'update_related_term_usage_titles', 20, 3);
add_action('pods_api_post_save_pod_item_translator', 'update_related_term_usage_titles_via_translator', 20, 3);

// Keep existing filters for frontend display as backup
add_filter('the_title', 'generate_term_usage_title_display', 10, 2);
add_filter('get_the_title', 'generate_term_usage_title_display', 10, 2);

// Hook into Pods relationship field display
add_filter('pods_field_pick_data', 'fix_term_usage_titles_in_relationship_fields', 10, 6);

/**
 * Update the actual post_title in the database when a term_usage post is saved
 */
function update_term_usage_post_title($post_id, $post, $update) {
    if ($post->post_type !== 'term_usage') {
        return;
    }
    
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }
    
    file_put_contents('/Users/reedz/Local Sites/marpatranslation/logs/php/test.log', "SAVE_POST: Fired for term_usage {$post_id}, title: '{$post->post_title}'\n", FILE_APPEND);
    
    $custom_title = generate_term_usage_title_content($post_id);
    file_put_contents('/Users/reedz/Local Sites/marpatranslation/logs/php/test.log', "SAVE_POST: Generated title: '{$custom_title}'\n", FILE_APPEND);
    
    if (!empty($custom_title) && $custom_title !== $post->post_title) {
        remove_action('save_post', 'update_term_usage_post_title', 999);
        
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $custom_title
        ));
        
        file_put_contents('/Users/reedz/Local Sites/marpatranslation/logs/php/test.log', "SAVE_POST: Updated title to: '{$custom_title}'\n", FILE_APPEND);
        
        add_action('save_post', 'update_term_usage_post_title', 999, 3);
    }
}

/**
 * Update term_usage titles when related translation is saved
 */
function update_related_term_usage_titles($pieces, $is_new_item, $id) {
    $term_usage_posts = get_posts(array(
        'post_type' => 'term_usage',
        'meta_query' => array(
            array(
                'key' => 'translations',
                'value' => '"' . $id . '"',
                'compare' => 'LIKE'
            )
        ),
        'posts_per_page' => -1
    ));
    
    foreach ($term_usage_posts as $term_usage_post) {
        update_term_usage_post_title($term_usage_post->ID, $term_usage_post, true);
    }
}

/**
 * Update term_usage titles when related translator is saved
 */
function update_related_term_usage_titles_via_translator($pieces, $is_new_item, $id) {
    $translations_with_translator = get_posts(array(
        'post_type' => 'product',
        'meta_query' => array(
            array(
                'key' => 'translation_translators',
                'value' => '"' . $id . '"',
                'compare' => 'LIKE'
            )
        ),
        'posts_per_page' => -1
    ));
    
    foreach ($translations_with_translator as $translation) {
        update_related_term_usage_titles($pieces, $is_new_item, $translation->ID);
    }
}

/**
 * Generate the custom title content (core logic)
 */
function generate_term_usage_title_content($post_id) {
    $translated_term = get_field_title('translated_term', $post_id);
    $translator = get_translator_from_translation($post_id);
    $translation = get_field_title('translations', $post_id);
    
    $title_parts = array();
    if (!empty($translated_term)) $title_parts[] = $translated_term;
    if (!empty($translator)) $title_parts[] = 'used by ' . $translator;
    if (!empty($translation)) $title_parts[] = 'in ' . $translation;
    
    return !empty($title_parts) ? implode(' ', $title_parts) : 'Term Usage Entry';
}

/**
 * Display filter (backup for frontend display)
 */
function generate_term_usage_title_display($title, $post_id = null) {
    $post = get_post($post_id);
    
    if (!$post || $post->post_type !== 'term_usage') {
        return $title;
    }
    
    if (is_admin() && !wp_doing_ajax()) {
        global $pagenow;
        if (in_array($pagenow, ['post.php', 'post-new.php'])) {
            return $title;
        }
    }
    
    if (!empty($title) && $title !== 'Auto Draft' && !preg_match('/^term_usage_\d+$/', $title)) {
        return $title;
    }
    
    return generate_term_usage_title_content($post_id);
}

/**
 * Fix term_usage titles in Pods relationship fields
 */
function fix_term_usage_titles_in_relationship_fields($data, $name, $options, $pod, $id, $obj) {
    if (!isset($options['pick_object']) || $options['pick_object'] !== 'post-type') {
        return $data;
    }
    
    if (!isset($options['pick_val']) || $options['pick_val'] !== 'term_usage') {
        return $data;
    }
    
    if (is_array($data)) {
        foreach ($data as $key => $item) {
            if (is_array($item) && isset($item['post_title']) && isset($item['ID'])) {
                if (in_array($item['post_title'], ['Auto Draft', '']) || preg_match('/^term_usage_\d+$/', $item['post_title'])) {
                    $custom_title = generate_term_usage_title_content($item['ID']);
                    if (!empty($custom_title)) {
                        $data[$key]['post_title'] = $custom_title;
                    }
                }
            }
        }
    }
    
    return $data;
}

/**
 * Get translator name(s) from the linked translation
 */
function get_translator_from_translation($post_id) {
    $translations_data = pods_field('translations', $post_id);
    
    if (empty($translations_data)) {
        return '';
    }
    
    $translation_id = null;
    if (is_array($translations_data)) {
        if (isset($translations_data['ID'])) {
            $translation_id = $translations_data['ID'];
        } else if (isset($translations_data[0]) && is_array($translations_data[0]) && isset($translations_data[0]['ID'])) {
            $translation_id = $translations_data[0]['ID'];
        }
    } else if (is_object($translations_data) && isset($translations_data->ID)) {
        $translation_id = $translations_data->ID;
    }
    
    if (!$translation_id) {
        return '';
    }
    
    $pods_obj = pods('product', $translation_id);
    if (!$pods_obj) {
        return '';
    }
    
    $translators_data = $pods_obj->field('translation_translators');
    
    if (empty($translators_data)) {
        return '';
    }
    
    $translator_names = array();
    
    if (is_array($translators_data)) {
        foreach ($translators_data as $translator) {
            if (is_array($translator) && isset($translator['post_title'])) {
                $name = $translator['post_title'];
                if (is_clean_title($name)) {
                    $translator_names[] = $name;
                }
            } else if (is_array($translator) && isset($translator['ID'])) {
                $name = get_translator_name($translator['ID']);
                if (!empty($name)) {
                    $translator_names[] = $name;
                }
            } else if (is_object($translator) && isset($translator->post_title)) {
                $name = $translator->post_title;
                if (is_clean_title($name)) {
                    $translator_names[] = $name;
                }
            }
        }
    }
    
    if (empty($translator_names)) {
        return '';
    } else if (count($translator_names) == 1) {
        return $translator_names[0];
    } else if (count($translator_names) == 2) {
        return $translator_names[0] . ' and ' . $translator_names[1];
    } else {
        $last_translator = array_pop($translator_names);
        return implode(', ', $translator_names) . ', and ' . $last_translator;
    }
}

/**
 * SECTION 6: HELPER FUNCTIONS
 * =============================================================================
 */

/**
 * Helper function to get field title
 */
function get_field_title($field_name, $post_id) {
    $field_data = pods_field($field_name, $post_id);
    
    if (empty($field_data)) {
        return '';
    }
    
    if (is_array($field_data)) {
        if (isset($field_data['post_title'])) {
            return is_clean_title($field_data['post_title']) ? $field_data['post_title'] : '';
        }
        
        if (isset($field_data['ID'])) {
            $title = get_the_title($field_data['ID']);
            return is_clean_title($title) ? $title : '';
        }
        
        if (isset($field_data[0]) && is_array($field_data[0]) && isset($field_data[0]['post_title'])) {
            return is_clean_title($field_data[0]['post_title']) ? $field_data[0]['post_title'] : '';
        }
    }
    
    if (is_object($field_data) && isset($field_data->post_title)) {
        return is_clean_title($field_data->post_title) ? $field_data->post_title : '';
    }
    
    return '';
}

/**
 * Helper function to check if title is clean
 */
function is_clean_title($title) {
    if (empty($title) || !is_string($title)) return false;
    if (in_array($title, array('Auto Draft', 'Hello world!'))) return false;
    if (strpos($title, 'Term Usage') !== false) return false;
    if (strlen($title) > 200) return false;
    return true;
}

/**
 * Get translator name (tries translator_name field first, then post_title)
 */
function get_translator_name($translator_id) {
    $translator_name = pods_field('translator_name', $translator_id);
    if (!empty($translator_name) && !is_array($translator_name) && is_clean_title($translator_name)) {
        return $translator_name;
    }
    
    $title = get_the_title($translator_id);
    return is_clean_title($title) ? $title : '';
}

/**
 * SECTION 7: SHORTCODES
 * =============================================================================
 */

/**
 * Shortcode to show translators
 */
add_shortcode('show_translators', 'show_translators_shortcode');
function show_translators_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts);
    
    $translation_id = intval($atts['id']);
    if (!$translation_id) {
        return '';
    }
    
    $pods_obj = pods('product', $translation_id);
    if (!$pods_obj) {
        return '';
    }
    
    $translators_data = $pods_obj->field('translation_translators');
    if (empty($translators_data)) {
        return '';
    }
    
    $translator_names = array();
    
    if (is_array($translators_data)) {
        foreach ($translators_data as $translator) {
            if (is_array($translator) && isset($translator['post_title'])) {
                $name = $translator['post_title'];
                if ($name && $name !== 'Auto Draft' && $name !== 'Hello world!') {
                    $translator_names[] = $name;
                }
            } else if (is_array($translator) && isset($translator['ID'])) {
                $name = get_translator_name($translator['ID']);
                if (!empty($name)) {
                    $translator_names[] = $name;
                }
            } else if (is_object($translator) && isset($translator->post_title)) {
                $name = $translator->post_title;
                if ($name && $name !== 'Auto Draft' && $name !== 'Hello world!') {
                    $translator_names[] = $name;
                }
            }
        }
    }
    
    if (empty($translator_names)) {
        return '';
    }
    
    if (count($translator_names) == 1) {
        return $translator_names[0];
    } else if (count($translator_names) == 2) {
        return $translator_names[0] . ' and ' . $translator_names[1];
    } else {
        $last_translator = array_pop($translator_names);
        return implode(', ', $translator_names) . ', and ' . $last_translator;
    }
}

/**
 * Shortcode to get translators with formatting options
 */
add_shortcode('get_translators', 'get_translators_shortcode');
function get_translators_shortcode($atts) {
    $atts = shortcode_atts(array(
        'translation_id' => '',
        'format' => 'names', // 'names', 'links', or 'full'
    ), $atts);
    
    $translation_id = intval($atts['translation_id']);
    if (!$translation_id) {
        return '';
    }
    
    $pods_obj = pods('product', $translation_id);
    if (!$pods_obj) {
        return '';
    }
    
    $translators_data = $pods_obj->field('translation_translators');
    if (empty($translators_data)) {
        return '';
    }
    
    $translator_names = array();
    $translator_links = array();
    
    if (is_array($translators_data)) {
        foreach ($translators_data as $translator) {
            if (is_array($translator)) {
                $name = '';
                $link = '';
                
                if (isset($translator['post_title'])) {
                    $name = $translator['post_title'];
                } else if (isset($translator['ID'])) {
                    $translator_name_field = pods_field('translator_name', $translator['ID']);
                    if (!empty($translator_name_field) && !is_array($translator_name_field)) {
                        $name = $translator_name_field;
                    } else {
                        $name = get_the_title($translator['ID']);
                    }
                }
                
                if (isset($translator['ID'])) {
                    $link = get_permalink($translator['ID']);
                }
                
                if (!empty($name) && $name !== 'Auto Draft' && $name !== 'Hello world!') {
                    $translator_names[] = $name;
                    if (!empty($link)) {
                        $translator_links[] = '<a href="' . esc_url($link) . '" class="translator-name">' . esc_html($name) . '</a>';
                    } else {
                        $translator_links[] = '<span class="translator-name">' . esc_html($name) . '</span>';
                    }
                }
            }
        }
    }
    
    if (empty($translator_names)) {
        return '';
    }
    
    switch ($atts['format']) {
        case 'links':
            return implode(', ', $translator_links);
        case 'full':
            return '<span class="translators-label">Translated by:</span> ' . implode(', ', $translator_links);
        case 'names':
        default:
            return implode(', ', $translator_names);
    }
}

/**
 * Shortcode to get translation language
 */
add_shortcode('get_translation_language', 'get_translation_language_shortcode');
function get_translation_language_shortcode($atts) {
    $atts = shortcode_atts(array(
        'translation_id' => '',
    ), $atts);
    
    $translation_id = intval($atts['translation_id']);
    if (!$translation_id) {
        return '';
    }
    
    $language_data = pods_field('translation_language', $translation_id);
    if (empty($language_data)) {
        return '';
    }
    
    if (is_array($language_data) && isset($language_data['post_title'])) {
        return '<span class="usage-language">' . esc_html($language_data['post_title']) . '</span>';
    } else if (is_object($language_data) && isset($language_data->post_title)) {
        return '<span class="usage-language">' . esc_html($language_data->post_title) . '</span>';
    }
    
    return '';
}

/**
 * SECTION 8: ADMIN CUSTOMIZATIONS
 * =============================================================================
 */

/**
 * Reorder admin menu to put MailPoet after Vehicles
 */
function reorder_admin_menu() {
    global $menu;
    
    $mailpoet_key = null;
    $mailpoet_item = null;
    
    foreach ($menu as $key => $item) {
        if (isset($item[2]) && $item[2] === 'mailpoet-homepage') {
            $mailpoet_key = $key;
            $mailpoet_item = $item;
            break;
        }
    }
    
    $vehicles_key = null;
    foreach ($menu as $key => $item) {
        if (isset($item[2]) && $item[2] === 'edit.php?post_type=vehicle') {
            $vehicles_key = $key;
            break;
        }
    }
    
    if ($mailpoet_key !== null && $vehicles_key !== null && $mailpoet_item !== null) {
        unset($menu[$mailpoet_key]);
        $new_position = $vehicles_key + 0.1;
        $menu[$new_position] = $mailpoet_item;
        ksort($menu);
    }
}
add_action('admin_menu', 'reorder_admin_menu', 999);

/**
 * SECTION 9: ADMIN COLUMNS CUSTOMIZATION
 * =============================================================================
 */

/**
 * Customize admin columns for Text, Translation, and Term Usage post types
 */

// Text post type columns
add_filter('manage_text_posts_columns', 'mts_add_text_admin_columns');
add_action('manage_text_posts_custom_column', 'mts_display_text_admin_columns', 10, 2);
add_filter('manage_edit-text_sortable_columns', 'mts_make_text_columns_sortable');

// Product post type columns for translations 
add_filter('manage_product_posts_columns', 'mts_add_translation_admin_columns');
add_action('manage_product_posts_custom_column', 'mts_display_translation_admin_columns', 10, 2);

// Term Usage post type columns
add_filter('manage_term_usage_posts_columns', 'mts_add_term_usage_admin_columns');
add_action('manage_term_usage_posts_custom_column', 'mts_display_term_usage_admin_columns', 10, 2);
add_filter('manage_edit-term_usage_sortable_columns', 'mts_make_term_usage_columns_sortable');

// Translated Term post type columns
add_filter('manage_translated_term_posts_columns', 'mts_add_translated_term_admin_columns');
add_action('manage_translated_term_posts_custom_column', 'mts_display_translated_term_admin_columns', 10, 2);

// Filters and sorting
add_action('restrict_manage_posts', 'mts_add_admin_filters');
add_filter('parse_query', 'mts_admin_filter_query');
add_action('pre_get_posts', 'mts_admin_columns_orderby');

// Column styling
add_action('admin_head', 'mts_admin_column_styles');

/**
 * Text post type admin columns
 */
function mts_add_text_admin_columns($columns) {
    // Define exactly which columns we want and in what order
    return [
        'cb' => '<input type="checkbox" />',
        'title' => 'Title',
        'text_type' => 'Type',
        'text_author' => 'Author',
        'date' => 'Date'
    ];
}

function mts_display_text_admin_columns($column, $post_id) {
    if ($column === 'text_type') {
        $text_type = pods_field('text_type', $post_id);
        if ($text_type) {
            echo esc_html($text_type);
            
            if ($text_type === 'Individual chapter') {
                $chapter_number = pods_field('chapter_number', $post_id);
                $chapter_of = pods_field('chapter_of', $post_id);
                
                if ($chapter_number || $chapter_of) {
                    echo '<br><small style="color: #666;">';
                    if ($chapter_number) {
                        echo "Ch. {$chapter_number}";
                    }
                    if ($chapter_of) {
                        $parent_title = is_array($chapter_of) ? $chapter_of['post_title'] : get_the_title($chapter_of);
                        if ($parent_title) {
                            echo ($chapter_number ? ' of ' : '') . esc_html($parent_title);
                        }
                    }
                    echo '</small>';
                }
            }
        } else {
            echo '<span style="color: #999;">—</span>';
        }
    }
    
    if ($column === 'text_author') {
        $author = pods_field('text_textauthor', $post_id);
        if ($author) {
            $author_title = is_array($author) ? $author['post_title'] : get_the_title($author);
            $author_id = is_array($author) ? $author['ID'] : $author;
            
            if ($author_title && $author_id) {
                $edit_link = get_edit_post_link($author_id);
                if ($edit_link) {
                    echo '<a href="' . esc_url($edit_link) . '">' . esc_html($author_title) . '</a>';
                } else {
                    echo esc_html($author_title);
                }
            }
        } else {
            echo '<span style="color: #999;">—</span>';
        }
    }
}

function mts_make_text_columns_sortable($columns) {
    $columns['text_type'] = 'text_type';
    $columns['text_author'] = 'text_author';
    return $columns;
}

/**
 * Translation post type admin columns
 */
function mts_add_translation_admin_columns($columns) {
    // Define exactly which columns we want and in what order
    return [
        'cb' => '<input type="checkbox" />',
        'title' => 'Title',
        'translators' => 'Translators',
        'date' => 'Date'
    ];
}

function mts_display_translation_admin_columns($column, $post_id) {
    if ($column === 'translators') {
        $translators = pods_field('translation_translators', $post_id);
        
        if (empty($translators)) {
            echo '<span style="color: #999;">—</span>';
            return;
        }
        
        $translator_names = [];
        
        // Ensure we have an array to work with
        if (!is_array($translators)) {
            echo '<span style="color: #999;">—</span>';
            return;
        }
        
        foreach ($translators as $translator) {
            $name = '';
            $translator_id = null;
            
            // Get the translator ID first
            if (is_array($translator) && isset($translator['ID'])) {
                $translator_id = $translator['ID'];
            } else if (is_numeric($translator)) {
                $translator_id = $translator;
            } else if (is_object($translator) && isset($translator->ID)) {
                $translator_id = $translator->ID;
            }
            
            // Now get ONLY the translator name using the ID
            if ($translator_id) {
                // Try the translator_name field first (your custom field)
                $translator_name_field = pods_field('translator_name', $translator_id);
                if (!empty($translator_name_field) && is_string($translator_name_field)) {
                    $name = trim($translator_name_field);
                } else {
                    // Fallback to the post title of the translator post
                    $translator_post = get_post($translator_id);
                    if ($translator_post && $translator_post->post_type === 'translator') {
                        $name = trim($translator_post->post_title);
                    }
                }
            }
            
            // Validate the name and add it
            if (!empty($name) && 
                $name !== 'Hello world!' && 
                $name !== 'Auto Draft' && 
                !preg_match('/^translator_\d+$/', $name)) {
                $translator_names[] = esc_html($name);
            }
        }
        
        // Remove duplicates and display
        $translator_names = array_unique($translator_names);
        
        if (!empty($translator_names)) {
            echo implode(', ', $translator_names);
        } else {
            echo '<span style="color: #999;">—</span>';
        }
    }
}

/**
 * Term Usage post type admin columns
 */
function mts_add_term_usage_admin_columns($columns) {
    // Define exactly which columns we want and in what order
    return [
        'cb' => '<input type="checkbox" />',
        'translated_term' => 'Translated Term',
        'translation' => 'Translation',
        'translators' => 'Translator(s)',
        'date' => 'Date'
    ];
}

function mts_display_term_usage_admin_columns($column, $post_id) {
    switch ($column) {
        case 'translated_term':
            $translated_term = pods_field('translated_term', $post_id);
            if (!empty($translated_term)) {
                if (is_array($translated_term) && isset($translated_term['post_title'])) {
                    $title = $translated_term['post_title'];
                    $link = isset($translated_term['permalink']) ? $translated_term['permalink'] : get_permalink($translated_term['ID']);
                    echo '<a href="' . esc_url($link) . '">' . esc_html($title) . '</a>';
                } else if (is_object($translated_term) && isset($translated_term->post_title)) {
                    echo '<a href="' . esc_url($translated_term->permalink) . '">' . esc_html($translated_term->post_title) . '</a>';
                } else {
                    echo '<em>No translated term</em>';
                }
            } else {
                echo '<em>No translated term</em>';
            }
            break;
            
        case 'translation':
            $translations = pods_field('translations', $post_id);
            if (!empty($translations)) {
                if (is_array($translations)) {
                    if (isset($translations['post_title'])) {
                        $title = $translations['post_title'];
                        $link = isset($translations['permalink']) ? $translations['permalink'] : get_permalink($translations['ID']);
                        echo '<a href="' . esc_url($link) . '">' . esc_html($title) . '</a>';
                    } else if (isset($translations[0])) {
                        $translation_links = array();
                        foreach ($translations as $translation) {
                            if (is_array($translation) && isset($translation['post_title'])) {
                                $title = $translation['post_title'];
                                $link = isset($translation['permalink']) ? $translation['permalink'] : get_permalink($translation['ID']);
                                $translation_links[] = '<a href="' . esc_url($link) . '">' . esc_html($title) . '</a>';
                            }
                        }
                        echo implode(', ', $translation_links);
                    }
                } else if (is_object($translations) && isset($translations->post_title)) {
                    echo '<a href="' . esc_url($translations->permalink) . '">' . esc_html($translations->post_title) . '</a>';
                } else {
                    echo '<em>No translation</em>';
                }
            } else {
                echo '<em>No translation</em>';
            }
            break;
            
        case 'translators':
            $translations = pods_field('translations', $post_id);
            $translator_names = array();
            
            if (!empty($translations)) {
                $translation_ids = array();
                
                if (is_array($translations)) {
                    if (isset($translations['ID'])) {
                        $translation_ids[] = $translations['ID'];
                    } else if (isset($translations[0])) {
                        foreach ($translations as $translation) {
                            if (is_array($translation) && isset($translation['ID'])) {
                                $translation_ids[] = $translation['ID'];
                            }
                        }
                    }
                } else if (is_object($translations) && isset($translations->ID)) {
                    $translation_ids[] = $translations->ID;
                }
                
                foreach ($translation_ids as $translation_id) {
                    $pods_obj = pods('product', $translation_id);
                    if ($pods_obj) {
                        $translators_data = $pods_obj->field('translation_translators');
                        if (!empty($translators_data) && is_array($translators_data)) {
                            foreach ($translators_data as $translator) {
                                if (is_array($translator) && isset($translator['post_title'])) {
                                    $name = $translator['post_title'];
                                    $link = isset($translator['permalink']) ? $translator['permalink'] : get_permalink($translator['ID']);
                                    if (!empty($name) && $name !== 'Auto Draft') {
                                        $translator_names[] = '<a href="' . esc_url($link) . '">' . esc_html($name) . '</a>';
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            if (!empty($translator_names)) {
                echo implode(', ', array_unique($translator_names));
            } else {
                echo '<em>No translators</em>';
            }
            break;
    }
}

function mts_make_term_usage_columns_sortable($columns) {
    $columns['translated_term'] = 'translated_term';
    $columns['translation'] = 'translation';
    return $columns;
}

/**
 * Translated Term post type admin columns
 */
function mts_add_translated_term_admin_columns($columns) {
    // Define exactly which columns we want and in what order
    return [
        'cb' => '<input type="checkbox" />',
        'title' => 'Title',
        'tibetan_term' => 'Tibetan Term',
        'used_in' => 'Used in',
        'date' => 'Date'
    ];
}

function mts_display_translated_term_admin_columns($column, $post_id) {
    switch ($column) {
        case 'tibetan_term':
            $tibetan_term = pods_field('tibetan_term', $post_id);
            if (!empty($tibetan_term)) {
                if (is_array($tibetan_term) && isset($tibetan_term['post_title'])) {
                    $title = $tibetan_term['post_title'];
                    $link = isset($tibetan_term['permalink']) ? $tibetan_term['permalink'] : get_permalink($tibetan_term['ID']);
                    echo '<a href="' . esc_url($link) . '">' . esc_html($title) . '</a>';
                } else if (is_object($tibetan_term) && isset($tibetan_term->post_title)) {
                    echo '<a href="' . esc_url($tibetan_term->permalink) . '">' . esc_html($tibetan_term->post_title) . '</a>';
                }
            } else {
                echo '<span style="color: #999;">—</span>';
            }
            break;
            
        case 'used_in':
            // Get term usages for this translated term
            $term_usages = pods_field('term_usages', $post_id);
            $translation_titles = array();
            
            if (!empty($term_usages) && is_array($term_usages)) {
                foreach ($term_usages as $term_usage) {
                    $term_usage_id = null;
                    
                    // Get the term usage ID
                    if (is_array($term_usage) && isset($term_usage['ID'])) {
                        $term_usage_id = $term_usage['ID'];
                    } else if (is_numeric($term_usage)) {
                        $term_usage_id = $term_usage;
                    } else if (is_object($term_usage) && isset($term_usage->ID)) {
                        $term_usage_id = $term_usage->ID;
                    }
                    
                    if ($term_usage_id) {
                        // Get translations from this term usage using Pods object
                        $term_usage_pod = pods('term_usage', $term_usage_id);
                        if ($term_usage_pod) {
                            $translations = $term_usage_pod->field('translations');
                            
                            if (!empty($translations)) {
                                // Handle single translation
                                if (is_array($translations) && isset($translations['post_title'])) {
                                    $title = $translations['post_title'];
                                    $link = isset($translations['permalink']) ? $translations['permalink'] : get_permalink($translations['ID']);
                                    if (!empty($title) && $title !== 'Auto Draft') {
                                        $translation_titles[] = '<a href="' . esc_url($link) . '">' . esc_html($title) . '</a>';
                                    }
                                }
                                // Handle array of translations
                                else if (is_array($translations)) {
                                    foreach ($translations as $translation) {
                                        if (is_array($translation) && isset($translation['post_title'])) {
                                            $title = $translation['post_title'];
                                            $link = isset($translation['permalink']) ? $translation['permalink'] : get_permalink($translation['ID']);
                                            if (!empty($title) && $title !== 'Auto Draft') {
                                                $translation_titles[] = '<a href="' . esc_url($link) . '">' . esc_html($title) . '</a>';
                                            }
                                        }
                                    }
                                }
                                // Handle object format
                                else if (is_object($translations) && isset($translations->post_title)) {
                                    $title = $translations->post_title;
                                    if (!empty($title) && $title !== 'Auto Draft') {
                                        $translation_titles[] = '<a href="' . esc_url($translations->permalink) . '">' . esc_html($title) . '</a>';
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Remove duplicates and display
            $translation_titles = array_unique($translation_titles);
            
            if (!empty($translation_titles)) {
                echo implode(', ', $translation_titles);
            } else {
                echo '<span style="color: #999;">Not used</span>';
            }
            break;
    }
}

/**
 * Admin filters
 */
function mts_add_admin_filters() {
    global $typenow;
    
    if ($typenow === 'text') {
        // Text Type Filter
        echo '<select name="filter_text_type" id="filter_text_type">';
        echo '<option value="">All Types</option>';
        
        $text_types = [
            'Treatise root text',
            'Treatise commentary', 
            'Practice manual',
            'Liturgy',
            'Individual chapter'
        ];
        
        $selected_type = isset($_GET['filter_text_type']) ? $_GET['filter_text_type'] : '';
        
        foreach ($text_types as $type) {
            $selected = selected($selected_type, $type, false);
            echo '<option value="' . esc_attr($type) . '" ' . $selected . '>' . esc_html($type) . '</option>';
        }
        echo '</select>';
        
        // Author Filter
        $authors = get_posts([
            'post_type' => 'text_author',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'publish'
        ]);
        
        if (!empty($authors)) {
            echo '<select name="filter_text_author" id="filter_text_author">';
            echo '<option value="">All Authors</option>';
            
            $selected_author = isset($_GET['filter_text_author']) ? $_GET['filter_text_author'] : '';
            
            foreach ($authors as $author) {
                $selected = selected($selected_author, $author->ID, false);
                echo '<option value="' . esc_attr($author->ID) . '" ' . $selected . '>' . esc_html($author->post_title) . '</option>';
            }
            echo '</select>';
        }
    }
}

/**
 * Handle admin filtering
 */
function mts_admin_filter_query($query) {
    global $pagenow, $typenow;
    
    if ($pagenow !== 'edit.php') {
        return;
    }
    
    $meta_query = [];
    
    if ($typenow === 'text') {
        if (!empty($_GET['filter_text_type'])) {
            $meta_query[] = [
                'key' => 'text_type',
                'value' => sanitize_text_field($_GET['filter_text_type']),
                'compare' => '='
            ];
        }
        
        if (!empty($_GET['filter_text_author'])) {
            $meta_query[] = [
                'key' => 'text_textauthor',
                'value' => intval($_GET['filter_text_author']),
                'compare' => '='
            ];
        }
    }
    
    if (!empty($meta_query)) {
        $query->set('meta_query', $meta_query);
    }
}

/**
 * Handle admin column sorting
 */
function mts_admin_columns_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    // Text post type sorting
    if ('text_type' === $orderby) {
        $query->set('meta_key', 'text_type');
        $query->set('orderby', 'meta_value');
    }
    
    if ('text_author' === $orderby) {
        $query->set('meta_key', 'text_textauthor');
        $query->set('orderby', 'meta_value');
    }
    
    // Term Usage post type sorting
    if ('translated_term' === $orderby) {
        $query->set('meta_key', 'translated_term');
        $query->set('orderby', 'meta_value');
    }
    
    if ('translation' === $orderby) {
        $query->set('meta_key', 'translations');
        $query->set('orderby', 'meta_value');
    }
}

/**
 * Admin column styles
 */
function mts_admin_column_styles() {
    global $pagenow, $typenow;
    
    if ($pagenow !== 'edit.php' || !in_array($typenow, ['text', 'product', 'term_usage', 'translated_term'])) {
        return;
    }
    
    echo '<style>';
    
    if ($typenow === 'text') {
        echo '
        .wp-list-table .column-cb { width: 2.2em; }
        .wp-list-table .column-title { width: 40%; }
        .wp-list-table .column-text_type { width: 20%; }
        .wp-list-table .column-text_author { width: 25%; }
        .wp-list-table .column-date { width: 12%; }';
    }
    
    if ($typenow === 'product') {
        echo '
        .wp-list-table .column-cb { width: 2.2em; }
        .wp-list-table .column-title { width: 50%; }
        .wp-list-table .column-translators { width: 30%; }
        .wp-list-table .column-date { width: 15%; }';
    }
    
    if ($typenow === 'term_usage') {
        echo '
        .wp-list-table .column-cb { width: 2.2em; }
        .wp-list-table .column-translated_term { width: 25%; }
        .wp-list-table .column-translation { width: 30%; }
        .wp-list-table .column-translators { width: 25%; }
        .wp-list-table .column-date { width: 15%; }';
    }
    
    if ($typenow === 'translated_term') {
        echo '
        .wp-list-table .column-cb { width: 2.2em; }
        .wp-list-table .column-title { width: 25%; }
        .wp-list-table .column-tibetan_term { width: 25%; }
        .wp-list-table .column-used_in { width: 35%; }
        .wp-list-table .column-date { width: 12%; }';
    }
    
    echo '</style>';
}

/**
 * SECTION 10: BULK OPERATIONS (OPTIONAL)
 * =============================================================================
 */

/**
 * Bulk update existing term_usage posts
 * Uncomment the add_action line below and visit /wp-admin/admin.php?page=bulk-update-term-usage
 */
function bulk_update_term_usage_titles() {
    $term_usage_posts = get_posts(array(
        'post_type' => 'term_usage',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    foreach ($term_usage_posts as $post) {
        update_term_usage_post_title($post->ID, $post, true);
    }
    
    wp_die('Bulk update completed for ' . count($term_usage_posts) . ' term_usage posts.');
}

// Uncomment to add bulk update tool
// add_action('admin_menu', function() { add_submenu_page('tools.php', 'Bulk Update Term Usage', 'Bulk Update Term Usage', 'manage_options', 'bulk-update-term-usage', 'bulk_update_term_usage_titles'); });