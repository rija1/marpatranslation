<?php

function pa($truc,$die,$vdump) {
    echo '<pre>';
    if($vdump) {
        var_dump($truc);
    } else {
        print_r($truc);
    }
    echo '</pre>';
    if($die) {
        die();
    }
}

function my_theme_enqueue_styles()
{
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

function my_theme_scripts()
{
    wp_enqueue_script('bsf', get_stylesheet_directory_uri() . '/js/bsf.js');
    wp_enqueue_script('chartjs', get_stylesheet_directory_uri() . '/js/chart.js');
}
add_action('wp_enqueue_scripts', 'my_theme_scripts');

add_filter( 'woocommerce_is_purchasable', 'mts_is_purchasable' );
function mts_is_purchasable( $is_purchasable ) {

return false;    
	global $product;

    // Check if a book an approved book request allows purchasing the product
    if($is_purchasable) {
        if (approved_book_request()) {
            $is_purchasable = true;
        } else {
            // Display "Request This Book" button
            add_action( 'woocommerce_before_add_to_cart_form', 'mts_add_book_request_button' ); 
            $is_purchasable = false;
        }
    }
	
	return $is_purchasable;
	
}

function approved_book_request() {
    // Check if a book an approved book request allows purchasing the product
    return false;
}

// add a piece of html at the woocommerce_before_add_to_cart_form hook

function mts_add_book_request_button() {
    echo '<button type="submit" name="book-request" class="single_add_to_cart_button button alt wp-element-button">Request This Book</button>';
    // A modal popup is displayed
    

}


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
    
    while($pods->fetch()) {
        $results[] = array(
            'label' => $pods->display('post_title'),
            'value' => $pods->display('ID')
        );
    }
    
    wp_send_json($results);
}

// Reedz debug
// Custom error handler to show stack trace
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
//

/**
 * Enhanced dynamic title generation for term_usage posts
 * This version works both in frontend display AND in Pods relationship fields
 * Format: [TRANSLATED_TERM] used by [TRANSLATOR(S)] in [TRANSLATION]
 */

// 1. Update the actual post_title in database when post is saved
add_action('save_post', 'update_term_usage_post_title', 20, 3);
add_action('pods_api_post_save_pod_item_translation', 'update_related_term_usage_titles', 20, 3);
add_action('pods_api_post_save_pod_item_translator', 'update_related_term_usage_titles_via_translator', 20, 3);

// 2. Keep existing filters for frontend display as backup
add_filter('the_title', 'generate_term_usage_title_display', 10, 2);
add_filter('get_the_title', 'generate_term_usage_title_display', 10, 2);

// 3. Hook into Pods relationship field display
add_filter('pods_field_pick_data', 'fix_term_usage_titles_in_relationship_fields', 10, 6);

/**
 * Update the actual post_title in the database when a term_usage post is saved
 */
function update_term_usage_post_title($post_id, $post, $update) {
    // Only process term_usage posts
    if ($post->post_type !== 'term_usage') {
        return;
    }
    
    // Avoid infinite loops
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }
    
    // Generate the custom title
    $custom_title = generate_term_usage_title_content($post_id);
    
    // Only update if the title is different and not empty
    if (!empty($custom_title) && $custom_title !== $post->post_title) {
        // Remove this hook temporarily to avoid infinite loop
        remove_action('save_post', 'update_term_usage_post_title', 20);
        
        // Update the post title in the database
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $custom_title
        ));
        
        // Re-add the hook
        add_action('save_post', 'update_term_usage_post_title', 20, 3);
    }
}

/**
 * Update term_usage titles when related translation is saved
 */
function update_related_term_usage_titles($pieces, $is_new_item, $id) {
    // Find all term_usage posts that reference this translation
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
    // This is more complex - we need to find translations that use this translator,
    // then find term_usage posts that use those translations
    
    // Find translations that use this translator
    $translations_with_translator = get_posts(array(
        'post_type' => 'translation',
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
    // Get field values
    $translated_term = get_field_title('translated_term', $post_id);
    $translator = get_translator_from_translation($post_id);
    $translation = get_field_title('translations', $post_id);
    
    // Build title (handles multiple translators automatically)
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
    // Get the post object
    $post = get_post($post_id);
    
    // Only process term_usage posts
    if (!$post || $post->post_type !== 'term_usage') {
        return $title;
    }
    
    // Avoid infinite loops - don't generate custom titles in admin edit screens
    if (is_admin() && !wp_doing_ajax()) {
        global $pagenow;
        if (in_array($pagenow, ['post.php', 'post-new.php'])) {
            return $title;
        }
    }
    
    // If the title looks like it's already been generated, use it
    if (!empty($title) && $title !== 'Auto Draft' && !preg_match('/^term_usage_\d+$/', $title)) {
        return $title;
    }
    
    // Generate custom title
    return generate_term_usage_title_content($post_id);
}

/**
 * Fix term_usage titles in Pods relationship fields
 */
function fix_term_usage_titles_in_relationship_fields($data, $name, $options, $pod, $id, $obj) {
    // Only apply to relationship fields that might contain term_usage posts
    if (!isset($options['pick_object']) || $options['pick_object'] !== 'post-type') {
        return $data;
    }
    
    if (!isset($options['pick_val']) || $options['pick_val'] !== 'term_usage') {
        return $data;
    }
    
    // Process each item in the data array
    if (is_array($data)) {
        foreach ($data as $key => $item) {
            if (is_array($item) && isset($item['post_title']) && isset($item['ID'])) {
                // Check if this looks like a default title that needs to be replaced
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
 * (Keep your existing function)
 */
function get_translator_from_translation($post_id) {
    $translations_data = pods_field('translations', $post_id);
    
    if (empty($translations_data)) {
        return '';
    }
    
    // Get the translation ID
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
    
    // Use Pods object method (the working one!)
    $pods_obj = pods('translation', $translation_id);
    if (!$pods_obj) {
        return '';
    }
    
    $translators_data = $pods_obj->field('translation_translators');
    
    if (empty($translators_data)) {
        return '';
    }
    
    $translator_names = array();
    
    // Handle the array format we know works
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
    
    // Return properly formatted list of translators
    if (empty($translator_names)) {
        return '';
    } else if (count($translator_names) == 1) {
        return $translator_names[0];
    } else if (count($translator_names) == 2) {
        return $translator_names[0] . ' and ' . $translator_names[1];
    } else {
        // 3 or more translators: "John, Mary, and Jane"
        $last_translator = array_pop($translator_names);
        return implode(', ', $translator_names) . ', and ' . $last_translator;
    }
}

/**
 * Helper function to get field title - add this if it doesn't exist
 */
if (!function_exists('get_field_title')) {
    function get_field_title($field_name, $post_id) {
        $field_data = get_field($field_name, $post_id);
        
        if (empty($field_data)) {
            return '';
        }
        
        // Handle different field formats
        if (is_array($field_data)) {
            if (isset($field_data['post_title'])) {
                return $field_data['post_title'];
            } else if (isset($field_data[0]) && is_array($field_data[0]) && isset($field_data[0]['post_title'])) {
                return $field_data[0]['post_title'];
            }
        } else if (is_object($field_data) && isset($field_data->post_title)) {
            return $field_data->post_title;
        }
        
        return '';
    }
}

/**
 * Helper function to check if title is clean - add this if it doesn't exist
 */
if (!function_exists('is_clean_title')) {
    function is_clean_title($title) {
        return !empty($title) && $title !== 'Auto Draft' && !preg_match('/^(term_usage|translation|translator)_\d+$/', $title);
    }
}

/**
 * Helper function to get translator name - add this if it doesn't exist
 */
if (!function_exists('get_translator_name')) {
    function get_translator_name($translator_id) {
        $translator_post = get_post($translator_id);
        if ($translator_post && is_clean_title($translator_post->post_title)) {
            return $translator_post->post_title;
        }
        return '';
    }
}

/**
 * Optional: Bulk update existing term_usage posts
 * Run this once to fix existing posts
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

// Uncomment the line below and visit /wp-admin/admin.php?page=bulk-update-term-usage to run bulk update
// add_action('admin_menu', function() { add_submenu_page('tools.php', 'Bulk Update Term Usage', 'Bulk Update Term Usage', 'manage_options', 'bulk-update-term-usage', 'bulk_update_term_usage_titles'); });

/**
 * Get translator name (tries translator_name field first, then post_title)
 */
function get_translator_name($translator_id) {
    // Try translator_name field first
    $translator_name = pods_field('translator_name', $translator_id);
    if (!empty($translator_name) && !is_array($translator_name) && is_clean_title($translator_name)) {
        return $translator_name;
    }
    
    // Fallback to post title
    $title = get_the_title($translator_id);
    return is_clean_title($title) ? $title : '';
}

/**
 * Helper function to extract clean titles from Pods fields
 */
function get_field_title($field_name, $post_id) {
    $field_data = pods_field($field_name, $post_id);
    
    if (empty($field_data)) {
        return '';
    }
    
    // Handle arrays (most common case)
    if (is_array($field_data)) {
        // Single post as array
        if (isset($field_data['post_title'])) {
            return is_clean_title($field_data['post_title']) ? $field_data['post_title'] : '';
        }
        
        // Try getting title by ID
        if (isset($field_data['ID'])) {
            $title = get_the_title($field_data['ID']);
            return is_clean_title($title) ? $title : '';
        }
        
        // Multiple posts - take first one
        if (isset($field_data[0]) && is_array($field_data[0]) && isset($field_data[0]['post_title'])) {
            return is_clean_title($field_data[0]['post_title']) ? $field_data[0]['post_title'] : '';
        }
    }
    
    // Handle objects
    if (is_object($field_data) && isset($field_data->post_title)) {
        return is_clean_title($field_data->post_title) ? $field_data->post_title : '';
    }
    
    return '';
}

/**
 * Helper function to check if a title is valid
 */
function is_clean_title($title) {
    if (empty($title) || !is_string($title)) return false;
    if (in_array($title, array('Auto Draft', 'Hello world!'))) return false;
    if (strpos($title, 'Term Usage') !== false) return false;
    if (strlen($title) > 200) return false;
    return true;
}

/**
 * Add this to your functions.php file
 * Custom shortcode to get translator information for templates
 */

// Shortcode to get translators from a translation ID
add_shortcode('show_translators', 'show_translators_shortcode');

function show_translators_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts);
    
    $translation_id = intval($atts['id']);
    if (!$translation_id) {
        return '';
    }
    
    // Use the working Pods object method (same as our dynamic title system)
    $pods_obj = pods('translation', $translation_id);
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
    
    // Format with proper grammar
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
 * Add this to your functions.php file
 * Custom shortcode to get translator information for templates
 */

// Shortcode to get translators from a translation ID
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
    
    // Use the working Pods object method
    $pods_obj = pods('translation', $translation_id);
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
                
                // Get translator name
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
                
                // Get translator link
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
    
    // Format output based on request
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

// Shortcode to get translation language
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
 * Customize Term Usage admin columns
 * Add this to your functions.php file
 */

// Define custom columns for term_usage admin
add_filter('manage_term_usage_posts_columns', 'custom_term_usage_columns');

function custom_term_usage_columns($columns) {
    // Remove default title column
    unset($columns['title']);
    
    // Add our custom columns
    $new_columns = array();
    $new_columns['cb'] = $columns['cb']; // Keep checkbox
    $new_columns['translated_term'] = 'Translated Term';
    $new_columns['translation'] = 'Translation';
    $new_columns['translators'] = 'Translator(s)';
    $new_columns['date'] = $columns['date']; // Keep date
    
    return $new_columns;
}

// Populate the custom columns with data
add_action('manage_term_usage_posts_custom_column', 'custom_term_usage_column_content', 10, 2);

function custom_term_usage_column_content($column, $post_id) {
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
                    // Handle single translation as array
                    if (isset($translations['post_title'])) {
                        $title = $translations['post_title'];
                        $link = isset($translations['permalink']) ? $translations['permalink'] : get_permalink($translations['ID']);
                        echo '<a href="' . esc_url($link) . '">' . esc_html($title) . '</a>';
                    } 
                    // Handle multiple translations
                    else if (isset($translations[0])) {
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
            // Get translators from the linked translation(s)
            $translations = pods_field('translations', $post_id);
            $translator_names = array();
            
            if (!empty($translations)) {
                $translation_ids = array();
                
                // Extract translation IDs
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
                
                // Get translators for each translation
                foreach ($translation_ids as $translation_id) {
                    $pods_obj = pods('translation', $translation_id);
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

// Make columns sortable (optional)
add_filter('manage_edit-term_usage_sortable_columns', 'term_usage_sortable_columns');

function term_usage_sortable_columns($columns) {
    $columns['translated_term'] = 'translated_term';
    $columns['translation'] = 'translation';
    return $columns;
}

// Handle sorting for custom columns (optional)
add_action('pre_get_posts', 'term_usage_column_orderby');

function term_usage_column_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ('term_usage' !== $query->get('post_type')) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('translated_term' === $orderby) {
        $query->set('meta_key', 'translated_term');
        $query->set('orderby', 'meta_value');
    }
    
    if ('translation' === $orderby) {
        $query->set('meta_key', 'translations');
        $query->set('orderby', 'meta_value');
    }
}

// Optional: Add quick edit support for the term_usage fields
add_action('quick_edit_custom_box', 'term_usage_quick_edit_fields', 10, 2);

function term_usage_quick_edit_fields($column_name, $post_type) {
    if ($post_type !== 'term_usage') {
        return;
    }
    
    switch ($column_name) {
        case 'translated_term':
            echo '<fieldset class="inline-edit-col-left">';
            echo '<div class="inline-edit-col">';
            echo '<label><span class="title">Translated Term</span>';
            echo '<em>Edit in full editor for relationship fields</em>';
            echo '</label>';
            echo '</div>';
            echo '</fieldset>';
            break;
    }
}

// Optional: Customize column widths
add_action('admin_head', 'term_usage_admin_css');

function term_usage_admin_css() {
    global $post_type;
    if ($post_type === 'term_usage') {
        echo '<style>
        .wp-list-table .column-translated_term { width: 25%; }
        .wp-list-table .column-translation { width: 35%; }
        .wp-list-table .column-translators { width: 25%; }
        .wp-list-table .column-date { width: 15%; }
        .wp-list-table .column-cb { width: 2.2em; }
        </style>';
    }
}





function reorder_admin_menu() {
    global $menu;
    
    // Find the MailPoet menu item
    $mailpoet_key = null;
    $mailpoet_item = null;
    
    foreach ($menu as $key => $item) {
        if (isset($item[2]) && $item[2] === 'mailpoet-homepage') {
            $mailpoet_key = $key;
            $mailpoet_item = $item;
            break;
        }
    }
    
    // Find the Vehicles menu item
    $vehicles_key = null;
    foreach ($menu as $key => $item) {
        if (isset($item[2]) && $item[2] === 'edit.php?post_type=vehicle') {
            $vehicles_key = $key;
            break;
        }
    }
    
    // If both items are found, reorder them
    if ($mailpoet_key !== null && $vehicles_key !== null && $mailpoet_item !== null) {
        // Remove MailPoet from its current position
        unset($menu[$mailpoet_key]);
        
        // Find a position after Vehicles (add small increment to ensure it comes after)
        $new_position = $vehicles_key + 0.1;
        
        // Insert MailPoet at the new position
        $menu[$new_position] = $mailpoet_item;
        
        // Re-sort the menu by keys
        ksort($menu);
    }
}
add_action('admin_menu', 'reorder_admin_menu', 999);
