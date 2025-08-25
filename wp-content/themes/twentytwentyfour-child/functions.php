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
 * SECTION 2: WOOCOMMERCE CUSTOMIZATIONS
 * =============================================================================
 */

/**
 * Make products not purchasable by default
 */
add_filter('woocommerce_is_purchasable', 'mts_is_purchasable');
function mts_is_purchasable($is_purchasable) {
    return false; // Always return false to disable purchasing
    
    // Note: Original logic commented out but preserved
    /*
    global $product;
    if ($is_purchasable) {
        if (approved_book_request()) {
            $is_purchasable = true;
        } else {
            add_action('woocommerce_before_add_to_cart_form', 'mts_add_book_request_button'); 
            $is_purchasable = false;
        }
    }
    return $is_purchasable;
    */
}

/**
 * Check for approved book request
 */
function approved_book_request() {
    return false; // Placeholder for future functionality
}

/**
 * Add book request button
 */
function mts_add_book_request_button() {
    echo '<button type="submit" name="book-request" class="single_add_to_cart_button button alt wp-element-button">Request This Book</button>';
}

/**
 * SECTION 3: AJAX SEARCH FUNCTIONALITY
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
add_action('save_post', 'update_term_usage_post_title', 20, 3);
add_action('pods_api_post_save_pod_item_translation', 'update_related_term_usage_titles', 20, 3);
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
    
    $custom_title = generate_term_usage_title_content($post_id);
    
    if (!empty($custom_title) && $custom_title !== $post->post_title) {
        remove_action('save_post', 'update_term_usage_post_title', 20);
        
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $custom_title
        ));
        
        add_action('save_post', 'update_term_usage_post_title', 20, 3);
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

// Translation post type columns
add_filter('manage_translation_posts_columns', 'mts_add_translation_admin_columns');
add_action('manage_translation_posts_custom_column', 'mts_display_translation_admin_columns', 10, 2);

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
    
    if ($pagenow !== 'edit.php' || !in_array($typenow, ['text', 'translation', 'term_usage', 'translated_term'])) {
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
    
    if ($typenow === 'translation') {
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