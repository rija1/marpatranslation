<?php
/**
 * Bulk Term Usage Entry - WordPress Admin Page
 * Allows translators to efficiently create multiple term usage entries
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MTS_Bulk_Term_Entry {
    
    private $pod_fields_map;
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_validate_terms', array($this, 'ajax_validate_terms'));
        add_action('wp_ajax_save_bulk_entries', array($this, 'ajax_save_bulk_entries'));
        add_action('wp_ajax_get_text_suggestions', array($this, 'ajax_get_text_suggestions'));
        add_action('wp_ajax_get_pod_fields', array($this, 'ajax_get_pod_fields'));
        add_action('wp_ajax_get_reference_options', array($this, 'ajax_get_reference_options'));
        
        $this->init_pod_fields_map();
    }
    
    /**
     * Initialize comprehensive field mapping for all pod types
     */
    private function init_pod_fields_map() {
        $this->pod_fields_map = array(
            'book_request' => array(
                'label' => 'Book Requests',
                'supports_title' => true,
                'supports_editor' => true,
                'fields' => array(
                    'customer' => array('type' => 'pick', 'pick_object' => 'user', 'required' => true),
                    'product' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'product', 'required' => true),
                    'reason' => array('type' => 'paragraph', 'required' => true),
                    'request_status' => array('type' => 'pick', 'pick_object' => 'custom-simple', 'required' => true, 'options' => array('0' => 'New', '1' => 'Approved', '2' => 'Rejected', '3' => 'Complete')),
                    'created_date' => array('type' => 'datetime', 'required' => true)
                )
            ),
            'genre' => array(
                'label' => 'Genres',
                'supports_title' => true,
                'supports_editor' => true,
                'fields' => array()
            ),
            'language' => array(
                'label' => 'Languages',
                'supports_title' => false,
                'supports_editor' => false,
                'fields' => array(
                    'language_name' => array('type' => 'text', 'required' => false)
                )
            ),
            'philosophical_school' => array(
                'label' => 'Philosophical Schools',
                'supports_title' => true,
                'supports_editor' => true,
                'fields' => array()
            ),
            'sanskrit_term' => array(
                'label' => 'Sanskrit Terms',
                'supports_title' => true,
                'supports_editor' => true,
                'fields' => array(
                    'origin' => array('type' => 'wysiwyg', 'required' => false),
                    'vehicles' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'vehicle', 'pick_format_type' => 'multi', 'required' => false),
                    'tibetan_terms' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'tibetan_term', 'pick_format_type' => 'multi', 'required' => false)
                )
            ),
            'term_usage' => array(
                'label' => 'Term Usages',
                'supports_title' => false,
                'supports_editor' => false,
                'fields' => array(
                    'translated_term' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'translated_term', 'required' => true),
                    'translations' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'translation', 'pick_format_type' => 'multi', 'required' => false),
                    'term_comment' => array('type' => 'paragraph', 'required' => false),
                    'example' => array('type' => 'wysiwyg', 'required' => false)
                )
            ),
            'text_author' => array(
                'label' => 'Text Authors',
                'supports_title' => true,
                'supports_editor' => true,
                'fields' => array(
                    'authored_texts' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'text', 'pick_format_type' => 'multi', 'required' => false),
                    'picture' => array('type' => 'file', 'file_type' => 'images', 'required' => false),
                    'image' => array('type' => 'file', 'file_type' => 'images', 'required' => false),
                    'authored_texts_translations' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'translation', 'pick_format_type' => 'multi', 'required' => false)
                )
            ),
            'text' => array(
                'label' => 'Texts',
                'supports_title' => true,
                'supports_editor' => true,
                'fields' => array(
                    'text_full_title' => array('type' => 'text', 'required' => false),
                    'image' => array('type' => 'file', 'file_type' => 'images', 'required' => false),
                    'text_textauthor' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'text_author', 'required' => true),
                    'translations' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'translation', 'pick_format_type' => 'multi', 'required' => false, 'read_only' => true),
                    'text_type' => array('type' => 'pick', 'pick_object' => 'custom-simple', 'required' => true, 'options' => array('Treatise root text', 'Treatise commentary', 'Practice manual', 'Liturgy', 'Individual chapter')),
                    'chapter_of' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'text', 'required' => false, 'conditional' => array('field' => 'text_type', 'value' => 'Individual chapter')),
                    'chapter_number' => array('type' => 'number', 'required' => false, 'conditional' => array('field' => 'text_type', 'value' => 'Individual chapter')),
                    'chapter_name' => array('type' => 'text', 'required' => false, 'conditional' => array('field' => 'text_type', 'value' => 'Individual chapter'))
                )
            ),
            'tibetan_term' => array(
                'label' => 'Tibetan Terms',
                'supports_title' => true,
                'supports_editor' => true,
                'fields' => array(
                    'translated_terms' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'translated_term', 'pick_format_type' => 'multi', 'required' => false, 'read_only' => true),
                    'sanskrit_terms' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'sanskrit_term', 'pick_format_type' => 'multi', 'required' => false),
                    'explanation' => array('type' => 'wysiwyg', 'required' => false, 'label' => 'Origin')
                )
            ),
            'translated_term' => array(
                'label' => 'Translated Terms',
                'supports_title' => true,
                'supports_editor' => true,
                'fields' => array(
                    'tibetan_term' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'tibetan_term', 'required' => true),
                    'glossary_definition' => array('type' => 'wysiwyg', 'required' => false),
                    'term_language' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'language', 'pick_format_type' => 'multi', 'required' => true),
                    'term_comment' => array('type' => 'text', 'required' => false),
                    'vehicles' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'vehicle', 'pick_format_type' => 'multi', 'required' => false),
                    'philosophical_schools' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'philosophical_school', 'pick_format_type' => 'multi', 'required' => false),
                    'term_usages' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'term_usage', 'pick_format_type' => 'multi', 'required' => false, 'read_only' => true)
                )
            ),
            'translation' => array(
                'label' => 'Translations',
                'supports_title' => true,
                'supports_editor' => true,
                'fields' => array(
                    'mts_translation' => array('type' => 'boolean', 'required' => false),
                    'translation_text_author' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'text_author', 'required' => true),
                    'translation_source_text' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'text', 'required' => false),
                    'image' => array('type' => 'file', 'file_type' => 'images', 'required' => false),
                    'translation_translators' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'translator', 'pick_format_type' => 'multi', 'required' => false),
                    'translation_language' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'language', 'required' => false),
                    'translation_status' => array('type' => 'pick', 'pick_object' => 'custom-simple', 'required' => false, 'options' => array('0' => 'Not Started', '1' => 'Translation in Progress', '2' => 'Editing', '3' => 'Reviewing', '4' => 'Waiting for Publication', '5' => 'Published'))
                )
            ),
            'translator' => array(
                'label' => 'Translators',
                'supports_title' => false,
                'supports_editor' => false,
                'fields' => array(
                    'mts_translator' => array('type' => 'boolean', 'required' => false),
                    'translator_name' => array('type' => 'text', 'required' => false),
                    'image' => array('type' => 'file', 'file_type' => 'images', 'required' => false),
                    'alt_name' => array('type' => 'text', 'required' => false),
                    'bio' => array('type' => 'paragraph', 'required' => false),
                    'translated_texts' => array('type' => 'pick', 'pick_object' => 'post_type', 'pick_val' => 'translation', 'pick_format_type' => 'multi', 'required' => false, 'read_only' => true)
                )
            ),
            'vehicle' => array(
                'label' => 'Vehicles',
                'supports_title' => true,
                'supports_editor' => true,
                'fields' => array()
            )
        );
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_menu_page(
            'Bulk Entry Management',
            'Bulk Entries',
            'manage_options',
            'bulk-entry-management',
            array($this, 'admin_page'),
            'dashicons-editor-table',
            30
        );
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_bulk-entry-management') {
            return;
        }
        
        wp_enqueue_script(
            'bulk-term-entry',
            plugins_url('js/bulk-term-entry.js', __FILE__),
            array('jquery', 'jquery-ui-autocomplete'),
            '1.0.0',
            true
        );
        
        wp_enqueue_style(
            'bulk-term-entry',
            plugins_url('css/bulk-term-entry.css', __FILE__),
            array(),
            '1.0.0'
        );
        
        // Localize script for AJAX
        wp_localize_script('bulk-term-entry', 'bulkTermAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bulk_term_nonce'),
            'loading_text' => __('Processing...', 'mts-blocks'),
            'success_text' => __('Entries saved successfully!', 'mts-blocks'),
            'error_text' => __('Error occurred. Please try again.', 'mts-blocks')
        ));
    }
    
    /**
     * Render admin page
     */
    public function admin_page() {
        // Get current text if specified
        $current_text_id = isset($_GET['text_id']) ? intval($_GET['text_id']) : 0;
        $current_text = null;
        
        if ($current_text_id) {
            $current_text = get_post($current_text_id);
        }
        ?>
        
        <div class="wrap bulk-term-entry">
            <h1><?php _e('Bulk Term Usage Entry', 'mts-blocks'); ?></h1>
            <p class="description"><?php _e('Create multiple term usage entries efficiently. Add Tibetan and Translated terms as needed, then link them in usage contexts.', 'mts-blocks'); ?></p>
            
            <!-- Text Selection -->
            <div class="text-selection-section">
                <h2><?php _e('Select Text', 'mts-blocks'); ?></h2>
                <div class="text-selector">
                    <?php if ($current_text): ?>
                        <div class="current-text-info">
                            <strong><?php _e('Current Text:', 'mts-blocks'); ?></strong>
                            <span class="text-title"><?php echo esc_html($current_text->post_title); ?></span>
                            <button type="button" class="button change-text-btn"><?php _e('Change Text', 'mts-blocks'); ?></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-search-container" <?php echo $current_text ? 'style="display:none;"' : ''; ?>>
                        <label for="text-search"><?php _e('Search and select a text:', 'mts-blocks'); ?></label>
                        <input type="text" id="text-search" class="regular-text" placeholder="<?php _e('Start typing to search texts...', 'mts-blocks'); ?>">
                        <input type="hidden" id="selected-text-id" value="<?php echo $current_text_id; ?>">
                    </div>
                </div>
            </div>
            
            <!-- Bulk Entry Interface -->
            <div class="bulk-entry-section" <?php echo !$current_text ? 'style="display:none;"' : ''; ?>>
                <div class="section-header">
                    <h2><?php _e('Term Usage Entries', 'mts-blocks'); ?></h2>
                    <div class="header-controls">
                        <button type="button" class="button add-row-btn">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php _e('Add Row', 'mts-blocks'); ?>
                        </button>
                        <button type="button" class="button import-csv-btn">
                            <span class="dashicons dashicons-upload"></span>
                            <?php _e('Import CSV', 'mts-blocks'); ?>
                        </button>
                        <button type="button" class="button button-primary save-all-btn">
                            <span class="dashicons dashicons-saved"></span>
                            <?php _e('Save All Entries', 'mts-blocks'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Usage Instructions -->
                <div class="field-instructions">
                    <p><?php _e('Enter Tibetan and Translated terms to create Term Usage entries with quotes, references, and translator notes. Terms will be created automatically if they don\'t exist.', 'mts-blocks'); ?></p>
                </div>
                
                <!-- Entry Cards Container -->
                <div class="entry-cards-container" id="entry-cards-container">
                    <!-- Cards will be added dynamically -->
                </div>
                
                <!-- Progress Section -->
                <div class="progress-section" style="display:none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="progress-text">Processing entries...</div>
                </div>
                
                <!-- Results Section -->
                <div class="results-section" style="display:none;">
                    <h3><?php _e('Results Summary', 'mts-blocks'); ?></h3>
                    <div class="results-content"></div>
                </div>
            </div>
            
            <!-- CSV Import Modal -->
            <div class="csv-import-modal" style="display:none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><?php _e('Import CSV File', 'mts-blocks'); ?></h3>
                        <button type="button" class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p><?php _e('Upload a CSV file with columns: Tibetan Term, Translated Term, Usage Context', 'mts-blocks'); ?></p>
                        <input type="file" id="csv-file-input" accept=".csv" />
                        <div class="csv-preview" style="display:none;">
                            <h4><?php _e('Preview:', 'mts-blocks'); ?></h4>
                            <div class="csv-preview-content"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button button-secondary close-modal"><?php _e('Cancel', 'mts-blocks'); ?></button>
                        <button type="button" class="button button-primary import-csv-confirm" disabled><?php _e('Import', 'mts-blocks'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
    }
    
    /**
     * AJAX: Validate terms and check if they exist
     */
    public function ajax_validate_terms() {
        check_ajax_referer('bulk_term_nonce', 'nonce');
        
        $tibetan_term = sanitize_text_field($_POST['tibetan_term']);
        $translated_term = sanitize_text_field($_POST['translated_term']);
        
        $response = array();
        
        // Check if Tibetan term exists
        $tibetan_exists = $this->check_tibetan_term_exists($tibetan_term);
        $response['tibetan'] = array(
            'exists' => $tibetan_exists !== false,
            'id' => $tibetan_exists,
            'action' => $tibetan_exists ? 'exists' : 'create'
        );
        
        // Check if Translated term exists
        $translated_exists = $this->check_translated_term_exists($translated_term, $tibetan_term);
        $response['translated'] = array(
            'exists' => $translated_exists !== false,
            'id' => $translated_exists,
            'action' => $translated_exists ? 'exists' : 'create'
        );
        
        wp_send_json_success($response);
    }
    
    /**
     * AJAX: Save bulk entries
     */
    public function ajax_save_bulk_entries() {
        check_ajax_referer('bulk_term_nonce', 'nonce');
        
        $text_id = intval($_POST['text_id']);
        $entries = $_POST['entries'];
        
        if (!$text_id || empty($entries)) {
            wp_send_json_error('Invalid data provided.');
        }
        
        $results = array(
            'created_tibetan_terms' => 0,
            'created_translated_terms' => 0,
            'created_term_usages' => 0,
            'errors' => array(),
            'processed' => 0
        );
        
        foreach ($entries as $index => $entry) {
            try {
                $this->process_single_entry($entry, $text_id, $results);
                $results['processed']++;
            } catch (Exception $e) {
                $results['errors'][] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX: Get text suggestions for autocomplete
     */
    public function ajax_get_text_suggestions() {
        $term = sanitize_text_field($_GET['term']);
        
        $texts = get_posts(array(
            'post_type' => 'text',
            'post_status' => 'publish',
            's' => $term,
            'posts_per_page' => 10
        ));
        
        $suggestions = array();
        foreach ($texts as $text) {
            $suggestions[] = array(
                'id' => $text->ID,
                'label' => $text->post_title,
                'value' => $text->post_title
            );
        }
        
        wp_send_json($suggestions);
    }
    
    /**
     * AJAX: Get pod fields configuration
     */
    public function ajax_get_pod_fields() {
        $pod_type = sanitize_text_field($_POST['pod_type']);
        
        if (!isset($this->pod_fields_map[$pod_type])) {
            wp_send_json_error('Invalid pod type');
        }
        
        $config = $this->pod_fields_map[$pod_type];
        
        wp_send_json_success($config);
    }
    
    /**
     * AJAX: Get reference options for pick fields
     */
    public function ajax_get_reference_options() {
        $post_type = sanitize_text_field($_POST['post_type']);
        $search_term = sanitize_text_field($_POST['search_term']);
        
        $query_args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => 20
        );
        
        if (!empty($search_term)) {
            $query_args['s'] = $search_term;
        }
        
        if ($post_type === 'user') {
            $users = get_users(array(
                'search' => $search_term ? '*' . $search_term . '*' : '',
                'number' => 20
            ));
            
            $options = array();
            foreach ($users as $user) {
                $options[] = array(
                    'id' => $user->ID,
                    'label' => $user->display_name . ' (' . $user->user_login . ')',
                    'value' => $user->ID
                );
            }
            
            wp_send_json_success($options);
            return;
        }
        
        $posts = get_posts($query_args);
        
        $options = array();
        foreach ($posts as $post) {
            $options[] = array(
                'id' => $post->ID,
                'label' => $post->post_title,
                'value' => $post->ID
            );
        }
        
        wp_send_json_success($options);
    }
    
    /**
     * Process a single pod entry
     */
    private function process_pod_entry($entry, $pod_type, &$results) {
        $pod_config = $this->pod_fields_map[$pod_type];
        
        $post_title = '';
        $post_content = '';
        
        // Handle title and content if supported
        if ($pod_config['supports_title']) {
            $post_title = sanitize_text_field($entry['post_title'] ?? '');
            if (empty($post_title)) {
                throw new Exception("Title is required for this content type.");
            }
        } else {
            // Generate title from field data or use a default
            $post_title = $this->generate_post_title($entry, $pod_type);
        }
        
        if ($pod_config['supports_editor']) {
            $post_content = wp_kses_post($entry['post_content'] ?? '');
        }
        
        // Create the post
        $post_data = array(
            'post_type' => $pod_type,
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            throw new Exception("Failed to create entry: " . $post_id->get_error_message());
        }
        
        // Process pod fields
        $pod = pods($pod_type, $post_id);
        if (!$pod) {
            throw new Exception("Failed to initialize Pod for field updates.");
        }
        
        $field_data = array();
        foreach ($pod_config['fields'] as $field_name => $field_config) {
            if (isset($entry[$field_name]) && !empty($entry[$field_name]) && !isset($field_config['read_only'])) {
                $field_value = $this->process_field_value($entry[$field_name], $field_config);
                if ($field_value !== null) {
                    $field_data[$field_name] = $field_value;
                }
            }
        }
        
        if (!empty($field_data)) {
            $pod->save($field_data);
        }
        
        $results['created_entries']++;
        return $post_id;
    }
    
    /**
     * Generate post title for pods that don't support title
     */
    private function generate_post_title($entry, $pod_type) {
        $pod_config = $this->pod_fields_map[$pod_type];
        
        // Try to use the first meaningful field as title
        foreach ($pod_config['fields'] as $field_name => $field_config) {
            if (isset($entry[$field_name]) && !empty($entry[$field_name])) {
                if ($field_config['type'] === 'text' || $field_config['type'] === 'pick') {
                    $value = $entry[$field_name];
                    if (is_array($value) && isset($value[0])) {
                        $value = $value[0];
                    }
                    return sanitize_text_field($value);
                }
            }
        }
        
        // Fallback to generic title with timestamp
        return $pod_config['label'] . ' Entry - ' . date('Y-m-d H:i:s');
    }
    
    /**
     * Process field value based on field configuration
     */
    private function process_field_value($value, $field_config) {
        switch ($field_config['type']) {
            case 'text':
                return sanitize_text_field($value);
                
            case 'paragraph':
                return sanitize_textarea_field($value);
                
            case 'wysiwyg':
                return wp_kses_post($value);
                
            case 'number':
                return is_numeric($value) ? (int) $value : null;
                
            case 'boolean':
                return $value ? '1' : '0';
                
            case 'datetime':
                // Expect format: YYYY-MM-DD HH:MM:SS or similar
                return sanitize_text_field($value);
                
            case 'pick':
                if ($field_config['pick_object'] === 'custom-simple') {
                    return sanitize_text_field($value);
                } else {
                    // Handle relationships - expect ID or array of IDs
                    if (is_array($value)) {
                        return array_map('intval', $value);
                    } else {
                        return intval($value);
                    }
                }
                
            case 'file':
                // Handle file uploads - expect attachment IDs
                if (is_array($value)) {
                    return array_map('intval', $value);
                } else {
                    return intval($value);
                }
                
            default:
                return sanitize_text_field($value);
        }
    }
    
    /**
     * Check if Tibetan term exists
     */
    private function check_tibetan_term_exists($term) {
        $posts = get_posts(array(
            'post_type' => 'tibetan_term',
            'post_status' => 'publish',
            'title' => $term,
            'posts_per_page' => 1
        ));
        
        return !empty($posts) ? $posts[0]->ID : false;
    }
    
    /**
     * Check if Translated term exists
     */
    private function check_translated_term_exists($term, $tibetan_term) {
        $posts = get_posts(array(
            'post_type' => 'translated_term',
            'post_status' => 'publish',
            'title' => $term,
            'posts_per_page' => 1
        ));
        
        return !empty($posts) ? $posts[0]->ID : false;
    }
    
    /**
     * Process a single entry
     */
    private function process_single_entry($entry, $text_id, &$results) {
        $tibetan_term = sanitize_text_field($entry['tibetan_term']);
        $translated_term = sanitize_text_field($entry['translated_term']);
        $term_quote_tib = wp_kses_post($entry['term_quote_tib'] ?? '');
        $term_quote_target_lang = sanitize_textarea_field($entry['term_quote_target_lang'] ?? '');
        $quote_reference = sanitize_text_field($entry['quote_reference'] ?? '');
        $translator_note = sanitize_textarea_field($entry['translator_note'] ?? '');
        $translations = $entry['translations'] ?? '';
        
        if (empty($tibetan_term) || empty($translated_term)) {
            throw new Exception("Tibetan and Translated terms are required.");
        }
        
        // 1. Create or get Tibetan term
        $tibetan_term_id = $this->create_or_get_tibetan_term($tibetan_term, $results);
        
        // 2. Create or get Translated term
        $translated_term_id = $this->create_or_get_translated_term($translated_term, $tibetan_term_id, $results);
        
        // 3. Create Term Usage
        $term_usage_id = $this->create_term_usage($translated_term_id, $term_quote_tib, $term_quote_target_lang, $quote_reference, $translator_note, $translations, $results);
        
        return $term_usage_id;
    }
    
    /**
     * Create or get Tibetan term
     */
    private function create_or_get_tibetan_term($term_title, &$results) {
        $existing_id = $this->check_tibetan_term_exists($term_title);
        
        if ($existing_id) {
            return $existing_id;
        }
        
        // Create new Tibetan term
        $post_id = wp_insert_post(array(
            'post_type' => 'tibetan_term',
            'post_title' => $term_title,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ));
        
        if (is_wp_error($post_id)) {
            throw new Exception("Failed to create Tibetan term: " . $post_id->get_error_message());
        }
        
        $results['created_tibetan_terms']++;
        return $post_id;
    }
    
    /**
     * Create or get Translated term
     */
    private function create_or_get_translated_term($term_title, $tibetan_term_id, &$results) {
        $existing_id = $this->check_translated_term_exists($term_title, '');
        
        if ($existing_id) {
            return $existing_id;
        }
        
        // Create new Translated term
        $post_id = wp_insert_post(array(
            'post_type' => 'translated_term',
            'post_title' => $term_title,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ));
        
        if (is_wp_error($post_id)) {
            throw new Exception("Failed to create Translated term: " . $post_id->get_error_message());
        }
        
        // Link to Tibetan term using Pods
        $pod = pods('translated_term', $post_id);
        if ($pod) {
            $pod->save('tibetan_term', $tibetan_term_id);
        }
        
        $results['created_translated_terms']++;
        return $post_id;
    }
    
    /**
     * Create Term Usage
     */
    private function create_term_usage($translated_term_id, $term_quote_tib, $term_quote_target_lang, $quote_reference, $translator_note, $translations, &$results) {
        // Get translated term title for the term usage title
        $translated_term_title = get_the_title($translated_term_id);
        
        // Create Term Usage post
        $post_id = wp_insert_post(array(
            'post_type' => 'term_usage',
            'post_title' => 'Term Usage - ' . $translated_term_title,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ));
        
        if (is_wp_error($post_id)) {
            throw new Exception("Failed to create Term Usage: " . $post_id->get_error_message());
        }
        
        // Link using Pods
        $pod = pods('term_usage', $post_id);
        if ($pod) {
            $pod_data = array(
                'translated_term' => $translated_term_id
            );
            
            // Add term quote (Tibetan) if provided
            if (!empty($term_quote_tib)) {
                $pod_data['term_quote_tib'] = $term_quote_tib;
            }
            
            // Add term quote (target language) if provided
            if (!empty($term_quote_target_lang)) {
                $pod_data['term_quote_target_lang'] = $term_quote_target_lang;
            }
            
            // Add quote reference if provided
            if (!empty($quote_reference)) {
                $pod_data['quote_reference'] = $quote_reference;
            }
            
            // Add translator note if provided
            if (!empty($translator_note)) {
                $pod_data['translator_note'] = $translator_note;
            }
            
            // Add translation if provided
            if (!empty($translations)) {
                $translation_id = intval($translations);
                $pod_data['translations'] = $translation_id;
            }
            
            $pod->save($pod_data);
        }
        
        $results['created_term_usages']++;
        return $post_id;
    }
}

// Initialize the bulk term entry system
new MTS_Bulk_Term_Entry();