<?php
/**
 * Marpa Translation Society Block Theme functions and definitions
 *
 * @package Marpa_Translation
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
 * GENERIC POD TITLE GENERATOR ENGINE
 * ============================================================================
 */

function mts_register_title_generator($post_type, callable $callback) {
    $hook_function_name = 'mts_title_generator_' . $post_type;
    
    // Create a named function dynamically
    $GLOBALS[$hook_function_name] = function ($post_id, $post, $update) use ($post_type, $callback, $hook_function_name) {
        if ($post->post_type !== $post_type) {
            return;
        }

        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        $new_title = call_user_func($callback, $post_id, $post);

        if (
            empty($new_title) ||
            $new_title === $post->post_title ||
            $new_title === 'Auto Draft'
        ) {
            return;
        }

        // Remove this specific hook temporarily
        remove_action('save_post', $GLOBALS[$hook_function_name], 999);

        wp_update_post([
            'ID'         => $post_id,
            'post_title' => $new_title,
        ]);

        // Re-add this specific hook
        add_action('save_post', $GLOBALS[$hook_function_name], 999, 3);
    };
    
    add_action('save_post', $GLOBALS[$hook_function_name], 999, 3);
}

function mts_generate_term_usage_title_cb($post_id, $post) {

    $translated_term_id = get_post_meta($post_id, 'translated_term', true);
    $translation_id     = get_post_meta($post_id, 'translations', true);

    $title_parts = [];

    // Translated term
    if ($translated_term_id) {
        $term_title = get_the_title($translated_term_id);
        if ($term_title && $term_title !== 'Auto Draft') {
            $title_parts[] = $term_title;
        }
    }

    // Translators
    if ($translation_id && function_exists('pods')) {
        $pods_obj = pods('product', $translation_id);
        if ($pods_obj && $pods_obj->exists()) {
            $translators = $pods_obj->field('translation_translators');

            if (is_array($translators)) {
                $names = [];

                foreach ($translators as $translator) {
                    if (is_array($translator) && isset($translator['post_title'])) {
                        $names[] = $translator['post_title'];
                    } else {
                        $t = get_the_title($translator);
                        if ($t && $t !== 'Auto Draft') {
                            $names[] = $t;
                        }
                    }
                }

                if ($names) {
                    $title_parts[] = 'used by ' . implode(' and ', $names);
                }
            }
        }
    }

    // Translation
    if ($translation_id) {
        $translation_title = get_the_title($translation_id);
        if ($translation_title && $translation_title !== 'Auto Draft') {
            $title_parts[] = 'in ' . $translation_title;
        }
    }

    return $title_parts ? implode(' ', $title_parts) : 'Term Usage Entry';
}

mts_register_title_generator('term_usage', 'mts_generate_term_usage_title_cb');

function mts_generate_glossary_entry_title_cb($post_id, $post) {

    // IMPORTANT: false → get all repeatable values
    $terms = get_post_meta($post_id, 'glossary_term', false);

    if (empty($terms)) {
        return 'Glossary Entry';
    }

    // Clean values
    $terms = array_filter(array_map('trim', $terms));

    if (!$terms) {
        return 'Glossary Entry';
    }

    return implode(', ', $terms);
}

mts_register_title_generator('glossary_entry', 'mts_generate_glossary_entry_title_cb');

/**
 * Genre title generator
 */
function mts_generate_genre_title_cb($post_id, $post) {
    $genre_name = get_post_meta($post_id, 'genre_name', true);
    return !empty($genre_name) ? trim($genre_name) : 'Genre';
}

mts_register_title_generator('genre', 'mts_generate_genre_title_cb');

/**
 * Language title generator
 */
function mts_generate_language_title_cb($post_id, $post) {
    $language_name = get_post_meta($post_id, 'language_name', true);
    return !empty($language_name) ? trim($language_name) : 'Language';
}

mts_register_title_generator('language', 'mts_generate_language_title_cb');

/**
 * Philosophical School title generator
 */
function mts_generate_philosophical_school_title_cb($post_id, $post) {
    $school_name = get_post_meta($post_id, 'philosophical_school_name', true);
    return !empty($school_name) ? trim($school_name) : 'Philosophical School';
}

mts_register_title_generator('philosophical_school', 'mts_generate_philosophical_school_title_cb');

/**
 * Sanskrit Term title generator
 */
function mts_generate_sanskrit_term_title_cb($post_id, $post) {
    $sanskrit_term = get_post_meta($post_id, 'sanskrit_term', true);
    return !empty($sanskrit_term) ? trim($sanskrit_term) : 'Sanskrit Term';
}

mts_register_title_generator('sanskrit_term', 'mts_generate_sanskrit_term_title_cb');

/**
 * Text Author title generator
 */
function mts_generate_text_author_title_cb($post_id, $post) {
    $author_name = get_post_meta($post_id, 'text_author_name', true);
    return !empty($author_name) ? trim($author_name) : 'Text Author';
}

mts_register_title_generator('text_author', 'mts_generate_text_author_title_cb');

/**
 * Text title generator
 */
function mts_generate_text_title_cb($post_id, $post) {
    $text_title = get_post_meta($post_id, 'text_full_title', true);
    return !empty($text_title) ? trim($text_title) : 'Text';
}

mts_register_title_generator('text', 'mts_generate_text_title_cb');

/**
 * Tibetan Term title generator
 */
function mts_generate_tibetan_term_title_cb($post_id, $post) {
    $tibetan_term = get_post_meta($post_id, 'tibetan_term', true);
    return !empty($tibetan_term) ? trim($tibetan_term) : 'Tibetan Term';
}

mts_register_title_generator('tibetan_term', 'mts_generate_tibetan_term_title_cb');


// Glossary Autocomplete
add_action( 'wp_ajax_glossary_autocomplete', 'glossary_autocomplete' );
add_action( 'wp_ajax_nopriv_glossary_autocomplete', 'glossary_autocomplete' );

function glossary_autocomplete() {
    global $wpdb;

    $q = sanitize_text_field( $_GET['q'] ?? '' );

    if ( strlen( $q ) < 2 ) {
        wp_send_json( [] );
    }

    $results = $wpdb->get_col( $wpdb->prepare( "
        SELECT pm.meta_value
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = 'glossary_term'
        AND pm.meta_value LIKE %s
        AND p.post_type = 'glossary_entry'
        AND p.post_status = 'publish'
        LIMIT 10
    ", '%' . $wpdb->esc_like( $q ) . '%' ) );

    wp_send_json( array_values( array_unique( $results ) ) );
}

// Find glossary term page AJAX handler
add_action( 'wp_ajax_find_glossary_term_page', 'find_glossary_term_page' );
add_action( 'wp_ajax_nopriv_find_glossary_term_page', 'find_glossary_term_page' );

function find_glossary_term_page() {
    global $wpdb;

    $term = sanitize_text_field( $_GET['term'] ?? '' );

    if ( empty( $term ) ) {
        wp_send_json_error( 'No term provided' );
    }

    // Get the glossary page ID (assuming it's the current page)
    $page_id = get_queried_object_id();
    
    // Find the glossary entry with this term
    $post_id = $wpdb->get_var( $wpdb->prepare( "
        SELECT p.ID
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = 'glossary_term'
        AND pm.meta_value LIKE %s
        AND p.post_type = 'glossary_entry'
        AND p.post_status = 'publish'
        LIMIT 1
    ", '%' . $wpdb->esc_like( $term ) . '%' ) );

    if ( !$post_id ) {
        wp_send_json_error( 'Term not found' );
    }

    // For now, return the current page URL since all terms show on the glossary page
    // In future, you could implement logic to determine which page number the term appears on
    $page_url = get_permalink( $page_id );
    
    wp_send_json_success( array(
        'page_url' => $page_url,
        'post_id' => $post_id
    ) );
}

// Load specific glossary term AJAX handler
add_action( 'wp_ajax_load_glossary_term', 'load_glossary_term' );
add_action( 'wp_ajax_nopriv_load_glossary_term', 'load_glossary_term' );

function load_glossary_term() {
    global $wpdb;

    $term = sanitize_text_field( $_GET['term'] ?? '' );

    if ( empty( $term ) ) {
        wp_send_json_error( 'No term provided' );
    }

    // Find the glossary entry with this term
    $post_id = $wpdb->get_var( $wpdb->prepare( "
        SELECT p.ID
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = 'glossary_term'
        AND pm.meta_value LIKE %s
        AND p.post_type = 'glossary_entry'
        AND p.post_status = 'publish'
        LIMIT 1
    ", '%' . $wpdb->esc_like( $term ) . '%' ) );

    if ( !$post_id ) {
        wp_send_json_error( 'Term not found' );
    }

    // Get the post data
    $post = get_post( $post_id );
    $glossary_term = get_post_meta( $post_id, 'glossary_term', true );
    $definition = get_post_meta( $post_id, 'definitiion', true ); // Note: keeping the typo as in field name
    $sanskrit_terms = get_post_meta( $post_id, 'sanskrit_term', true );
    $tibetan_terms = get_post_meta( $post_id, 'tibetan_term', true );

    // Build HTML output matching your existing structure
    ob_start();
    ?>
    <article class="glossary-entry loaded-term">
        <h3 class="glossary-term"><?php echo esc_html( $glossary_term ); ?></h3>
        
        <?php if ( !empty( $sanskrit_terms ) || !empty( $tibetan_terms ) ): ?>
        <ul class="glossary-languages">
            <?php if ( !empty( $tibetan_terms ) ): ?>
            <li class="tibetan">
                <strong>Tibetan:</strong>
                <?php echo esc_html( is_array( $tibetan_terms ) ? $tibetan_terms[0]['tibetan_term'] ?? '' : $tibetan_terms ); ?>
            </li>
            <?php endif; ?>
            
            <?php if ( !empty( $sanskrit_terms ) ): ?>
            <li class="sanskrit">
                <strong>Sanskrit:</strong>
                <?php echo esc_html( is_array( $sanskrit_terms ) ? $sanskrit_terms[0]['sanskrit_term'] ?? '' : $sanskrit_terms ); ?>
            </li>
            <?php endif; ?>
        </ul>
        <?php endif; ?>
        
        <?php if ( !empty( $definition ) ): ?>
        <div class="glossary-definition">
            <p><?php echo wp_kses_post( $definition ); ?></p>
        </div>
        <?php endif; ?>
    </article>
    <?php
    $html = ob_get_clean();

    wp_send_json_success( array(
        'html' => $html,
        'post_id' => $post_id
    ) );
}

// Get available letters AJAX handler
add_action( 'wp_ajax_get_glossary_letters', 'get_glossary_letters' );
add_action( 'wp_ajax_nopriv_get_glossary_letters', 'get_glossary_letters' );

function get_glossary_letters() {
    global $wpdb;

    $letters = $wpdb->get_col( "
        SELECT DISTINCT UPPER(LEFT(pm.meta_value, 1)) as first_letter
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = 'glossary_term'
        AND p.post_type = 'glossary_entry'
        AND p.post_status = 'publish'
        ORDER BY first_letter
    " );

    wp_send_json_success( array_values( array_unique( $letters ) ) );
}

// Get glossary terms by letter AJAX handler
add_action( 'wp_ajax_get_glossary_terms_by_letter', 'get_glossary_terms_by_letter' );
add_action( 'wp_ajax_nopriv_get_glossary_terms_by_letter', 'get_glossary_terms_by_letter' );

function get_glossary_terms_by_letter() {
    global $wpdb;

    $letter = sanitize_text_field( $_GET['letter'] ?? '' );

    if ( empty( $letter ) || strlen( $letter ) !== 1 ) {
        wp_send_json_error( 'Invalid letter provided' );
    }

    // Find all glossary entries starting with the specified letter
    $posts = $wpdb->get_results( $wpdb->prepare( "
        SELECT p.ID, p.post_title, pm.meta_value as glossary_term
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = 'glossary_term'
        AND UPPER(LEFT(pm.meta_value, 1)) = %s
        AND p.post_type = 'glossary_entry'
        AND p.post_status = 'publish'
        ORDER BY pm.meta_value
    ", strtoupper( $letter ) ) );

    if ( empty( $posts ) ) {
        wp_send_json_success( array() );
    }

    $html_entries = array();
    
    foreach ( $posts as $post ) {
        $post_id = $post->ID;
        $glossary_term = get_post_meta( $post_id, 'glossary_term', true );
        $definition = get_post_meta( $post_id, 'definitiion', true ); // Note: keeping the typo as in field name
        $sanskrit_terms = get_post_meta( $post_id, 'sanskrit_term', true );
        $tibetan_terms = get_post_meta( $post_id, 'tibetan_term', true );

        // Build HTML output matching your existing structure
        ob_start();
        ?>
        <article class="glossary-entry loaded-term">
            <h3 class="glossary-term"><?php echo esc_html( $glossary_term ); ?></h3>
            
            <?php if ( !empty( $sanskrit_terms ) || !empty( $tibetan_terms ) ): ?>
            <ul class="glossary-languages">
                <?php if ( !empty( $tibetan_terms ) ): ?>
                <li class="tibetan">
                    <strong>Tibetan:</strong>
                    <?php echo esc_html( is_array( $tibetan_terms ) ? $tibetan_terms[0]['tibetan_term'] ?? '' : $tibetan_terms ); ?>
                </li>
                <?php endif; ?>
                
                <?php if ( !empty( $sanskrit_terms ) ): ?>
                <li class="sanskrit">
                    <strong>Sanskrit:</strong>
                    <?php echo esc_html( is_array( $sanskrit_terms ) ? $sanskrit_terms[0]['sanskrit_term'] ?? '' : $sanskrit_terms ); ?>
                </li>
                <?php endif; ?>
            </ul>
            <?php endif; ?>
            
            <?php if ( !empty( $definition ) ): ?>
            <div class="glossary-definition">
                <p><?php echo wp_kses_post( $definition ); ?></p>
            </div>
            <?php endif; ?>
        </article>
        <?php
        $html_entries[] = ob_get_clean();
    }

    wp_send_json_success( $html_entries );
}





/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @since 1.0.0
 */
function marpa_translation_setup() {
    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title.
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support( 'post-thumbnails' );

    // Add support for Block Styles.
    add_theme_support( 'wp-block-styles' );

    // Add support for full and wide align images.
    add_theme_support( 'align-wide' );

    // Add support for editor styles.
    add_theme_support( 'editor-styles' );

    // Enqueue editor styles.
    add_editor_style( 'style.css' );

    // Add support for responsive embedded content.
    add_theme_support( 'responsive-embeds' );

    // Add support for custom line height controls.
    add_theme_support( 'custom-line-height' );

    // Add support for custom units.
    add_theme_support( 'custom-units' );

    // Remove core block patterns.
    remove_theme_support( 'core-block-patterns' );

    // Add WooCommerce support
    add_theme_support( 'woocommerce' );
    
    // Add WooCommerce gallery features
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'marpa_translation_setup' );

/**
 * Enqueue scripts and styles.
 *
 * @since 1.0.0
 */
function marpa_translation_scripts() {
    // Enqueue theme stylesheet with high priority.
    wp_enqueue_style( 
        'marpa-translation-style', 
        get_stylesheet_uri(), 
        array(), 
        filemtime( get_template_directory() . '/style.css' ) ?: wp_get_theme()->get( 'Version' )
    );

    // Check if compiled CSS exists, fallback to SCSS if needed
    $compiled_css_path = get_template_directory() . '/style.css';
    $scss_css_path = get_template_directory() . '/style.scss';
    
    // Use compiled CSS if available, otherwise fallback to direct SCSS
    if ( file_exists( $compiled_css_path ) && filemtime( $compiled_css_path ) >= filemtime( $scss_css_path ) ) {
        // Compiled CSS is up to date
        $css_version = filemtime( $compiled_css_path );
    } else {
        // SCSS is newer or CSS doesn't exist
        $css_version = wp_get_theme()->get( 'Version' );
    }

    // Enqueue Google Fonts.
    wp_enqueue_style( 
        'marpa-translation-fonts', 
        'https://fonts.googleapis.com/css2?family=DM+Serif+Text:ital@0;1&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Lato:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap',
        array(),
        null
    );

    // Enqueue custom JavaScript
    wp_enqueue_script(
        'marpa-translation-custom',
        get_template_directory_uri() . '/assets/js/custom.js',
        array('jquery'),
        filemtime( get_template_directory() . '/assets/js/custom.js' ) ?: wp_get_theme()->get( 'Version' ),
        true // Load in footer
    );

    // Enqueue glossary search CSS
    wp_enqueue_style(
        'marpa-translation-glossary-search',
        get_template_directory_uri() . '/assets/css/glossary-search.css',
        array(),
        filemtime( get_template_directory() . '/assets/css/glossary-search.css' ) ?: wp_get_theme()->get( 'Version' )
    );

}
add_action( 'wp_enqueue_scripts', 'marpa_translation_scripts' );


/**
 * Register block patterns.
 *
 * @since 1.0.0
 */
function marpa_translation_register_block_patterns() {
    if ( function_exists( 'register_block_pattern_category' ) ) {
        register_block_pattern_category(
            'marpa-translation',
            array(
                'label'       => __( 'Marpa Translation', 'marpa-translation' ),
                'description' => __( 'Patterns for the Marpa Translation Society website.', 'marpa-translation' ),
            )
        );
    }
}
add_action( 'init', 'marpa_translation_register_block_patterns' );

/**
 * Add custom block styles.
 *
 * @since 1.0.0
 */
function marpa_translation_register_block_styles() {
    if ( function_exists( 'register_block_style' ) ) {
        // Register button styles
        register_block_style(
            'core/button',
            array(
                'name'  => 'rounded-gradient',
                'label' => __( 'Rounded Gradient', 'marpa-translation' ),
                'style_handle' => 'marpa-translation-block-styles',
            )
        );

        register_block_style(
            'core/button',
            array(
                'name'  => 'outline-rounded',
                'label' => __( 'Outline Rounded', 'marpa-translation' ),
                'style_handle' => 'marpa-translation-block-styles',
            )
        );

        // Register group styles for cards
        register_block_style(
            'core/group',
            array(
                'name'  => 'feature-card',
                'label' => __( 'Feature Card', 'marpa-translation' ),
                'style_handle' => 'marpa-translation-block-styles',
            )
        );

        register_block_style(
            'core/group',
            array(
                'name'  => 'ornamental-border',
                'label' => __( 'Ornamental Border', 'marpa-translation' ),
                'style_handle' => 'marpa-translation-block-styles',
            )
        );
    }
}
add_action( 'init', 'marpa_translation_register_block_styles' );

/**
 * Enqueue block styles.
 *
 * @since 1.0.0
 */
function marpa_translation_enqueue_block_styles() {
    wp_enqueue_block_style(
        'core/button',
        array(
            'handle' => 'marpa-translation-button-styles',
            'src'    => get_theme_file_uri( 'assets/css/button-styles.css' ),
            'path'   => get_theme_file_path( 'assets/css/button-styles.css' ),
        )
    );
}
add_action( 'init', 'marpa_translation_enqueue_block_styles' );

/**
 * Customize theme support for various features.
 *
 * @since 1.0.0
 */
function marpa_translation_customize_theme_support() {
    // Add support for custom spacing.
    add_theme_support( 'custom-spacing' );
    
    // Add support for link color.
    add_theme_support( 'link-color' );
    
    // Add support for border controls.
    add_theme_support( 'border' );
}
add_action( 'after_setup_theme', 'marpa_translation_customize_theme_support' );

/**
 * Filter the excerpt length.
 *
 * @param int $length Excerpt length.
 * @return int Modified excerpt length.
 * @since 1.0.0
 */
function marpa_translation_excerpt_length( $length ) {
    if ( is_admin() ) {
        return $length;
    }
    return 30;
}
add_filter( 'excerpt_length', 'marpa_translation_excerpt_length' );

/**
 * Filter the excerpt "read more" string.
 *
 * @param string $more "Read more" excerpt string.
 * @return string Modified "read more" excerpt string.
 * @since 1.0.0
 */
function marpa_translation_excerpt_more( $more ) {
    if ( is_admin() ) {
        return $more;
    }
    return '&hellip; <a class="read-more-link" href="' . esc_url( get_permalink() ) . '">' . __( 'Continue reading', 'marpa-translation' ) . '</a>';
}
add_filter( 'excerpt_more', 'marpa_translation_excerpt_more' );

/**
 * Add preconnect for Google Fonts.
 *
 * @since 1.0.0
 */
function marpa_translation_resource_hints( $urls, $relation_type ) {
    if ( wp_style_is( 'marpa-translation-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
        $urls[] = array(
            'href' => 'https://fonts.gstatic.com',
            'crossorigin',
        );
    }
    return $urls;
}
add_filter( 'wp_resource_hints', 'marpa_translation_resource_hints', 10, 2 );

/**
 * Add custom classes to navigation menu items.
 *
 * @since 1.0.0
 */
function marpa_translation_nav_menu_css_class( $classes, $item, $args ) {
    if ( isset( $args->theme_location ) && 'primary' === $args->theme_location ) {
        $classes[] = 'nav-item';
    }
    return $classes;
}
add_filter( 'nav_menu_css_class', 'marpa_translation_nav_menu_css_class', 10, 3 );

/**
 * Enqueue scripts for enhanced interactivity.
 *
 * @since 1.0.0
 */
function marpa_translation_enqueue_frontend_scripts() {
    if ( ! is_admin() ) {
        wp_enqueue_script( 
            'marpa-translation-frontend', 
            get_template_directory_uri() . '/assets/js/frontend.js', 
            array(), 
            wp_get_theme()->get( 'Version' ), 
            true 
        );
    }
}
add_action( 'wp_enqueue_scripts', 'marpa_translation_enqueue_frontend_scripts' );

/**
 * Customize block editor settings.
 *
 * @since 1.0.0
 */
function marpa_translation_block_editor_settings() {
    // Add custom editor color palette
    add_theme_support( 'editor-color-palette', array(
        array(
            'name'  => __( 'Royal Blue', 'marpa-translation' ),
            'slug'  => 'royal-blue',
            'color' => '#14375d',
        ),
        array(
            'name'  => __( 'Burgundy', 'marpa-translation' ),
            'slug'  => 'burgundy',
            'color' => '#8b1538',
        ),
        array(
            'name'  => __( 'Gold', 'marpa-translation' ),
            'slug'  => 'gold',
            'color' => '#d4af37',
        ),
        array(
            'name'  => __( 'Cream', 'marpa-translation' ),
            'slug'  => 'cream',
            'color' => '#f5f1e8',
        ),
    ) );
}
add_action( 'after_setup_theme', 'marpa_translation_block_editor_settings' );

/**
 * Register custom taxonomies for WooCommerce products.
 *
 * @since 1.0.0
 */
function marpa_translation_register_product_taxonomies() {
    // Buddhist Tradition Taxonomy
    register_taxonomy(
        'product_tradition',
        'product',
        array(
            'label' => __( 'Buddhist Traditions', 'marpa-translation' ),
            'labels' => array(
                'name' => __( 'Buddhist Traditions', 'marpa-translation' ),
                'singular_name' => __( 'Buddhist Tradition', 'marpa-translation' ),
                'menu_name' => __( 'Traditions', 'marpa-translation' ),
            ),
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_rest' => true,
        )
    );

    // Text Type Taxonomy
    register_taxonomy(
        'product_text_type',
        'product',
        array(
            'label' => __( 'Text Types', 'marpa-translation' ),
            'labels' => array(
                'name' => __( 'Text Types', 'marpa-translation' ),
                'singular_name' => __( 'Text Type', 'marpa-translation' ),
                'menu_name' => __( 'Text Types', 'marpa-translation' ),
            ),
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_rest' => true,
        )
    );

    // Topic Taxonomy
    register_taxonomy(
        'product_topic',
        'product',
        array(
            'label' => __( 'Topics', 'marpa-translation' ),
            'labels' => array(
                'name' => __( 'Topics', 'marpa-translation' ),
                'singular_name' => __( 'Topic', 'marpa-translation' ),
                'menu_name' => __( 'Topics', 'marpa-translation' ),
            ),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_rest' => true,
        )
    );
}
add_action( 'init', 'marpa_translation_register_product_taxonomies' );

/**
 * Enqueue catalog-specific scripts and styles.
 *
 * @since 1.0.0
 */
function marpa_translation_catalog_scripts() {
    if ( is_shop() || is_product_category() || is_product_tag() ) {
        wp_enqueue_script(
            'marpa-translation-catalog',
            get_template_directory_uri() . '/assets/js/catalog.js',
            array( 'jquery' ),
            wp_get_theme()->get( 'Version' ),
            true
        );

        wp_localize_script(
            'marpa-translation-catalog',
            'catalog_ajax',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'catalog_filter_nonce' ),
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'marpa_translation_catalog_scripts' );

/**
 * AJAX handler for catalog filtering.
 *
 * @since 1.0.0
 */
function marpa_translation_filter_products() {
    check_ajax_referer( 'catalog_filter_nonce', 'nonce' );

    // Get filter parameters
    $status = isset( $_POST['status'] ) ? array_map( 'sanitize_text_field', $_POST['status'] ) : array();
    $tradition = isset( $_POST['tradition'] ) ? array_map( 'sanitize_text_field', $_POST['tradition'] ) : array();
    $text_type = isset( $_POST['text_type'] ) ? array_map( 'sanitize_text_field', $_POST['text_type'] ) : array();
    $topic = isset( $_POST['topic'] ) ? array_map( 'sanitize_text_field', $_POST['topic'] ) : array();
    $language_pair = isset( $_POST['language_pair'] ) ? sanitize_text_field( $_POST['language_pair'] ) : '';
    $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';

    // Build query args
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 10,
        'paged' => isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1,
    );

    // Meta query for status and other custom fields
    $meta_query = array();
    
    if ( !empty( $status ) ) {
        $meta_query[] = array(
            'key' => 'publication_status',
            'value' => $status,
            'compare' => 'IN',
        );
    }

    if ( !empty( $language_pair ) ) {
        $meta_query[] = array(
            'key' => 'language_pair',
            'value' => $language_pair,
            'compare' => '=',
        );
    }

    if ( !empty( $meta_query ) ) {
        $args['meta_query'] = $meta_query;
    }

    // Tax query for taxonomies
    $tax_query = array();

    if ( !empty( $tradition ) ) {
        $tax_query[] = array(
            'taxonomy' => 'product_tradition',
            'field' => 'slug',
            'terms' => $tradition,
        );
    }

    if ( !empty( $text_type ) ) {
        $tax_query[] = array(
            'taxonomy' => 'product_text_type',
            'field' => 'slug',
            'terms' => $text_type,
        );
    }

    if ( !empty( $topic ) ) {
        $tax_query[] = array(
            'taxonomy' => 'product_topic',
            'field' => 'slug',
            'terms' => $topic,
        );
    }

    if ( !empty( $tax_query ) ) {
        $args['tax_query'] = $tax_query;
    }

    // Search query
    if ( !empty( $search ) ) {
        $args['s'] = $search;
    }

    $products = new WP_Query( $args );

    ob_start();
    
    if ( $products->have_posts() ) {
        while ( $products->have_posts() ) {
            $products->the_post();
            wc_get_template_part( 'content', 'product' );
        }
    }

    $html = ob_get_clean();

    wp_send_json_success( array(
        'html' => $html,
        'found_posts' => $products->found_posts,
        'max_pages' => $products->max_num_pages,
    ) );
}
add_action( 'wp_ajax_filter_products', 'marpa_translation_filter_products' );
add_action( 'wp_ajax_nopriv_filter_products', 'marpa_translation_filter_products' );

/**
 * BOOK REQUEST SYSTEM
 * ============================================================================
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
    
    // Add product to customer's cart immediately
    if ($customer_id && $product_id) {
        // Switch to the customer's context to add to their cart
        $original_user = wp_get_current_user();
        wp_set_current_user($customer_id);
        
        // Initialize WooCommerce session for this customer
        if (class_exists('WC_Session_Handler')) {
            WC()->session = new WC_Session_Handler();
            WC()->session->init();
        }
        
        // Check if product is already in customer's cart
        $cart_contents = WC()->cart->get_cart_contents();
        $already_in_cart = false;
        
        foreach ($cart_contents as $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                $already_in_cart = true;
                break;
            }
        }
        
        if (!$already_in_cart && wc_get_product($product_id)) {
            // Add to customer's cart
            WC()->cart->add_to_cart($product_id, 1);
            
            // Mark as added to cart
            update_post_meta($post_id, '_added_to_cart', current_time('mysql'));
        }
        
        // Restore original user context
        wp_set_current_user($original_user->ID);
    }
    
    if ($customer_id && $product_id) {
        $customer = get_userdata($customer_id);
        $product = wc_get_product($product_id);
        
        // Send approval email
        if ($customer && $product) {
            $subject = 'Book Request Approved - ' . $product->get_name();
            $message = sprintf(
                "Dear %s,\n\n" .
                "Great news! Your book request has been approved.\n\n" .
                "Book: %s\n\n" .
                "This book has been added to your cart and is ready for checkout. " .
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
 * Remove missing asset enqueues to fix 404 errors
 */
function remove_missing_assets() {
    // Remove any problematic asset enqueues
    wp_dequeue_style('button-styles');
    wp_dequeue_script('frontend');
}
add_action('wp_enqueue_scripts', 'remove_missing_assets', 999);

/**
 * Add video overlay CSS and JavaScript
 */
function fix_video_overlay_script() {
    ?>
    <style>
    /* Video overlay styles */
    figure.wp-block-video {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    figure.wp-block-video video {
        width: 100%;
        height: auto;
        display: block;
    }
    
    .video-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: opacity 0.3s ease;
        pointer-events: all;
    }
    
    .video-overlay.hidden {
        opacity: 0;
        pointer-events: none;
    }
    
    .play-button {
        transition: transform 0.2s ease;
    }
    
    .play-button:hover {
        transform: scale(1.1);
    }
    
    /* Ensure video controls are visible when video is playing */
    figure.wp-block-video video:not([controls="false"]) {
        position: relative;
        z-index: 2;
    }
    
    figure.wp-block-video video[controls] {
        pointer-events: all;
    }
    
    /* Disable hover effects when video is playing */
    figure.wp-block-video.video-playing {
        transform: none !important;
        transition: none !important;
    }
    
    figure.wp-block-video.video-playing:hover {
        transform: none !important;
    }
    </style>
    <script>
    function playVideo(button) {
        // Find the video element within the same figure
        const figure = button.closest('figure.wp-block-video');
        const video = figure ? figure.querySelector('video') : null;
        const overlay = button.closest('.video-overlay');
        
        if (!video) {
            console.error('Video element not found');
            return;
        }
        
        if (video.paused) {
            video.play();
            if (overlay) {
                overlay.style.opacity = '0';
                overlay.style.pointerEvents = 'none';
            }
            video.controls = true;
            video.style.pointerEvents = 'all';
            figure.classList.add('video-playing');
        } else {
            video.pause();
            if (overlay) {
                overlay.style.opacity = '1';
                overlay.style.pointerEvents = 'all';
            }
            figure.classList.remove('video-playing');
        }
    }

    // Auto-hide overlay when video starts playing
    document.addEventListener('DOMContentLoaded', function() {
        const videos = document.querySelectorAll('figure.wp-block-video video');
        videos.forEach(video => {
            const figure = video.closest('figure.wp-block-video');
            const overlay = figure ? figure.querySelector('.video-overlay') : null;
            
            if (!overlay) return;
            
            video.addEventListener('play', function() {
                if (overlay) {
                    overlay.style.opacity = '0';
                    overlay.style.pointerEvents = 'none';
                }
                this.controls = true;
                this.style.pointerEvents = 'all';
                figure.classList.add('video-playing');
            });
            
            video.addEventListener('pause', function() {
                if (overlay) {
                    if (this.currentTime > 0) {
                        overlay.style.opacity = '0';
                        overlay.style.pointerEvents = 'none';
                        figure.classList.add('video-playing');
                    } else {
                        overlay.style.opacity = '1';
                        overlay.style.pointerEvents = 'all';
                        this.controls = false;
                        figure.classList.remove('video-playing');
                    }
                }
            });
            
            video.addEventListener('ended', function() {
                if (overlay) {
                    overlay.style.opacity = '1';
                    overlay.style.pointerEvents = 'all';
                }
                this.controls = false;
                figure.classList.remove('video-playing');
            });
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'fix_video_overlay_script');

/**
 * Add Tibetan font support.
 *
 * @since 1.0.0
 */
function marpa_translation_add_tibetan_fonts() {
    ?>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Noto+Serif+Tibetan:wght@400;500;700&display=swap');
    </style>
    <?php
}
add_action( 'wp_head', 'marpa_translation_add_tibetan_fonts' );

/**
 * Force load our shop template.
 *
 * @since 1.0.0
 */
function marpa_translation_force_shop_template( $template ) {
    if ( is_shop() || is_product_category() || is_product_tag() ) {
        $custom_template = get_template_directory() . '/woocommerce/archive-product.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'marpa_translation_force_shop_template', 99 );



