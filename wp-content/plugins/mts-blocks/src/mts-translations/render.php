<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
<?php
$args = array(
    'post_type' => 'translation',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
);

$custom_query = new WP_Query($args);

if ($custom_query->have_posts()) : ?>
    <div class="mts-block-table">
        <div class="table-header">
            <div>Translation</div>
            <div>Source Text</div>
            <div>Language</div>
            <div>Status</div>
            <div>View</div>
        </div>
        
        <?php while ($custom_query->have_posts()) : $custom_query->the_post(); 
            $pod = pods('translation', get_the_ID());
            
            // Get source text
            $source_text = $pod ? $pod->field('translation_source_text') : null;
            $source_title = '';
            if (!empty($source_text) && isset($source_text['post_title'])) {
                $source_title = $source_text['post_title'];
            }
            
            // Get language
            $language = $pod ? $pod->field('translation_language') : null;
            $language_name = '';
            if (!empty($language) && isset($language['post_title'])) {
                $language_name = $language['post_title'];
            }
            
            // Get status
            $status = $pod ? $pod->field('translation_status') : null;
            $status_label = '';
            if (!empty($status) && isset($status['_label'])) {
                $status_label = $status['_label'];
            }
        ?>
            <div class="table-row">
                <div><?php echo esc_html(get_the_title()); ?></div>
                <div><?php echo esc_html($source_title); ?></div>
                <div><?php echo esc_html($language_name); ?></div>
                <div>
                    <?php if (!empty($status_label)) : ?>
                        <span class="status-badge status-<?php echo esc_attr($status['meta_value'] ?? ''); ?>">
                            <?php echo esc_html($status_label); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="view-button">View</a>
                </div>
            </div>
        <?php endwhile; 
        wp_reset_postdata(); ?>
    </div>
<?php else : ?>
    <p>No translations found.</p>
<?php endif; ?>
</div>

<!-- Alternative using the Pods_Grid class -->
<?php
/*
require_once plugin_dir_path(__FILE__) . '../../inc/class-pods-grid.php';

$grid = new Pods_Grid('translation', [
    'title' => 'Translation',
    'translation_source_text' => 'Source Text',
    'translation_language' => 'Language', 
    'translation_status' => 'Status',
    'view' => 'View'
]);
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
    <?php $grid->render(); ?>
</div>
*/
?>