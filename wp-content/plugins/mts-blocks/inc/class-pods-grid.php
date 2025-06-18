<?php
// inc/class-pods-grid.php
class Pods_Grid
{
    private static $instance = null;
    private $pod_type;
    private $columns;
    private $per_page;

    public function __construct($pod_type, $columns, $per_page = 10)
    {
        $this->pod_type = $pod_type;
        $this->columns = $columns;
        $this->per_page = $per_page;

        // Only add AJAX handlers once
        if (!has_action('wp_ajax_filter_pods_grid', array($this, 'ajax_filter'))) {
            add_action('wp_ajax_filter_pods_grid', array($this, 'ajax_filter'));
            add_action('wp_ajax_nopriv_filter_pods_grid', array($this, 'ajax_filter'));
        }
    }

    public static function get_instance($pod_type, $columns, $per_page = 10)
    {
        if (null === self::$instance) {
            self::$instance = new self($pod_type, $columns, $per_page);
        }
        return self::$instance;
    }

    public function ajax_filter()
    {
        $paged = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        
        // Get pod_type from AJAX request, fall back to instance pod_type
        $requested_pod_type = isset($_GET['pod_type']) ? sanitize_text_field($_GET['pod_type']) : $this->pod_type;
        
        // Get columns for the requested pod type
        $columns = $this->get_pod_columns($requested_pod_type);

        $args = array(
            'post_type' => $requested_pod_type, // Use the requested pod type
            'posts_per_page' => $this->per_page,
            'paged' => $paged,
            'orderby' => 'title',
            'order' => 'ASC',
            's' => $search
        );

        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $pod = pods($requested_pod_type, get_the_ID());
                ?>
                <div class="table-row">
                    <?php foreach (array_keys($columns) as $column) : ?>
                        <div><?php echo $this->get_column_value($column, $pod); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php
            }
            
            // Add pagination after rows
            if ($query->max_num_pages > 1) {
                echo '<div class="pagination">';
                echo paginate_links(array(
                    'total' => $query->max_num_pages,
                    'current' => $paged,
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;'
                ));
                echo '</div>';
            }
        } else {
            echo '<div class="table-row"><div>No items found.</div></div>';
        }
        
        wp_reset_postdata();
        wp_die();
    }

    // Add this method to handle different pod types
    private function get_pod_columns($pod_type) {
        switch ($pod_type) {
            case 'tibetan_term':
                return [
                    'title' => 'Term',
                    'translated_terms' => 'Translations',
                    'view' => 'View'
                ];
            case 'translator':
                return [
                    'image' => 'Photo',
                    'title' => 'Translator',
                    'translated_texts' => 'Translations',
                    'view' => 'View'
                ];
            case 'text_author':
                return [
                    'image' => 'Photo',
                    'title' => 'Author',
                    'authored_texts' => 'Authored Texts',
                    'view' => 'View'
                ];
            case 'text':
                return [
                    'image' => 'Image',
                    'title' => 'Text',
                    'text_translations' => 'Translations',
                    'text_textauthor' => 'Author',
                    'view' => 'View'
                ];
            case 'translation':
                return [
                    'image' => 'Image',
                    'title' => 'Translation',
                    'translation_translators' => 'Translators',
                    'translation_source_text' => 'Source Text',
                    'view' => 'View'
                ];
            default:
                return [
                    'title' => 'Title',
                    'view' => 'View'
                ];
        }
    }

    public function render()
    {
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        $args = [
            'post_type' => $this->pod_type,
            'posts_per_page' => $this->per_page,
            'paged' => $paged,
            'orderby' => 'title',
            'order' => 'ASC'
        ];

        $query = new WP_Query($args);
        include plugin_dir_path(__FILE__) . 'pods-grid-template.php';
    }

    public function get_column_value($column, $pod)
    {
        switch ($column) {
            case 'translated_terms':
                return $this->get_relationship_multivalues($pod, $column);
            case 'translated_texts':
                return $this->get_relationship_links($pod, $column);
            case 'text_translations':
                return $this->get_relationship_multivalues($pod, $column);
            case 'text_textauthor':
                return $this->get_relationship_singlevalue($pod, $column);
            case 'translation':
                return $this->get_relationship_links($pod, $column);
            case 'authored_texts':
                return $this->get_relationship_links($pod, $column);
            case 'translation_translators':
                return $this->get_relationship_links($pod, $column);
            case 'translation_source_text':
                return $this->get_relationship_singlevalue($pod, $column);
            case 'image':
                return $this->get_image_field($pod, $column);
            case 'view':
                return sprintf(
                    '<a href="%s" class="view-button">View</a>',
                    esc_url(get_permalink())
                );
            default:
                $value = $pod->field($column);

                if (is_array($value)) {
                    return implode(', ', array_map('esc_html', $value));
                }

                return esc_html($value);
        }
    }

    private function get_image_field($pod, $column)
    {
        $image = $pod->field($column);

        if (empty($image)) {
            return '';
        }

        // Handle different image field formats
        if (is_array($image)) {
            // Multiple images - take the first one
            $image = reset($image);
        }

        // Get image data
        if (is_object($image)) {
            $image_id = $image->ID ?? null;
            $image_url = $image->guid ?? null;
        } elseif (is_array($image)) {
            $image_id = $image['ID'] ?? null;
            $image_url = $image['guid'] ?? null;
        } else {
            // Assume it's an attachment ID
            $image_id = $image;
            $image_url = wp_get_attachment_url($image_id);
        }

        if (!$image_url && $image_id) {
            $image_url = wp_get_attachment_url($image_id);
        }

        if (!$image_url) {
            return '';
        }

        // Get thumbnail version for display
        $thumbnail_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : $image_url;

        return sprintf(
            '<img src="%s" alt="%s" class="translator-photo" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">',
            esc_url($thumbnail_url),
            esc_attr('Photo')
        );
    }

    private function get_relationship_links($pod, $column)
    {
        $value = $pod->field($column);

        if (empty($value)) {
            return '';
        }

        // Ensure we have an array to work with
        if (!is_array($value)) {
            $value = [$value];
        }

        $links = [];

        foreach ($value as $item) {
            // Handle different formats that Pods might return
            if (is_object($item)) {
                $id = $item->ID ?? $item->id ?? null;
                $title = $item->post_title ?? $item->name ?? "Item #{$id}";
            } elseif (is_array($item)) {
                $id = $item['ID'] ?? $item['id'] ?? null;
                $title = $item['post_title'] ?? $item['name'] ?? "Item #{$id}";
            } else {
                // If it's just an ID
                $id = $item;
                $title = get_the_title($id) ?: "Item #{$id}";
            }

            if ($id) {
                $url = get_permalink($id);
                $links[] = sprintf(
                    '<a href="%s" class="relationship-link">%s</a>',
                    esc_url($url),
                    esc_html($title)
                );
            }
        }

        return implode(', ', $links);
    }

    private function get_relationship_singlevalue($pod, $column)
    {
        $val = '';
        $related_val = $pod->field($column);

        if (!empty($related_val) && isset($related_val['post_title'])) {
            $val = $related_val['post_title'];
        }

        return esc_html($val);
    }

    private function get_relationship_multivalues($pod, $column)
    {
        $vals = [];
        $related_vals = $pod->field($column);

        if (!empty($related_vals) && is_array($related_vals)) {
            foreach ($related_vals as $val) {
                if (isset($val['post_title'])) {
                    $vals[] = $val['post_title'];
                }
            }
        }

        return esc_html(implode(', ', $vals));
    }
}