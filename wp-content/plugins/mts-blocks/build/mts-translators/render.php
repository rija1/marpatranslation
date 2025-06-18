<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
<?php
$args = array(
    'post_type' => 'translator',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
);

$custom_query = new WP_Query($args);

if ($custom_query->have_posts()) : ?>
    <div class="mts-block-table translators-grid">
        <div class="table-header">
            <div>Translator</div>
            <div>Alternative Name</div>
            <div>Translated Works</div>
            <div>View</div>
        </div>
        
        <?php while ($custom_query->have_posts()) : $custom_query->the_post(); 
            $pod = pods('translator', get_the_ID());
            
            // Get translator image
            $image = $pod ? $pod->field('image') : null;
            $image_url = '';
            if (!empty($image) && isset($image['guid'])) {
                $image_url = $image['guid'];
            }
            
            // Get alternative name
            $alt_name = $pod ? $pod->field('alt_name') : '';
            
            // Get translated texts
            $translated_texts = $pod ? $pod->field('translated_texts') : array();
            $text_count = is_array($translated_texts) ? count($translated_texts) : 0;
            
            // Create list of translated work titles (first 3)
            $text_titles = array();
            if (!empty($translated_texts)) {
                $count = 0;
                foreach ($translated_texts as $text) {
                    if (isset($text['post_title']) && $count < 3) {
                        $text_titles[] = $text['post_title'];
                        $count++;
                    }
                }
            }
            
            $texts_display = '';
            if (!empty($text_titles)) {
                $texts_display = implode(', ', $text_titles);
                if ($text_count > 3) {
                    $texts_display .= ' +' . ($text_count - 3) . ' more';
                }
            }
        ?>
            <div class="table-row">
                <div class="translator-info">
                    <?php if (!empty($image_url)) : ?>
                        <img src="<?php echo esc_url($image_url); ?>" 
                             alt="<?php echo esc_attr(get_the_title()); ?>" 
                             class="translator-avatar">
                    <?php else : ?>
                        <div class="translator-avatar-placeholder">
                            <span><?php echo esc_html(substr(get_the_title(), 0, 1)); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="translator-details">
                        <div class="translator-name"><?php echo esc_html(get_the_title()); ?></div>
                        <?php if (!empty($alt_name)) : ?>
                            <div class="translator-alt-small"><?php echo esc_html($alt_name); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="alt-name-column"><?php echo esc_html($alt_name); ?></div>
                <div class="translated-works">
                    <?php if ($text_count > 0) : ?>
                        <span class="work-count"><?php echo $text_count; ?> work<?php echo $text_count > 1 ? 's' : ''; ?></span>
                        <?php if (!empty($texts_display)) : ?>
                            <div class="work-titles"><?php echo esc_html($texts_display); ?></div>
                        <?php endif; ?>
                    <?php else : ?>
                        <span class="no-works">No works yet</span>
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
    <p>No translators found.</p>
<?php endif; ?>
</div>

<style>
.translators-grid {
    width: 100%;
}

.translators-grid .table-header,
.translators-grid .table-row {
    display: grid;
    grid-template-columns: 1fr 0.8fr 1.2fr 0.5fr; /* Wider first column */
    gap: 16px;
    align-items: center;
    padding: 12px 16px;
}

.translators-grid .table-header {
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.translators-grid .table-row {
    border-bottom: 1px solid #dee2e6;
}

.translator-info {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0; /* Allows text to wrap if needed */
}

.translator-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ddd;
    flex-shrink: 0; /* Prevents image from shrinking */
}

.translator-avatar-placeholder {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #e1e5e9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #666;
    font-size: 16px;
    border: 2px solid #ddd;
    flex-shrink: 0;
}

.translator-details {
    flex: 1;
    min-width: 0; /* Allows text to wrap */
}

.translator-name {
    font-weight: 600;
    color: #2c3e50;
    line-height: 1.3;
    word-wrap: break-word;
}

.translator-alt-small {
    font-size: 0.8em;
    color: #666;
    font-style: italic;
    margin-top: 2px;
    line-height: 1.2;
}

.alt-name-column {
    font-style: italic;
    color: #666;
    word-wrap: break-word;
}

.translated-works {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.work-count {
    font-weight: 600;
    color: #3498db;
    font-size: 0.9em;
}

.work-titles {
    font-size: 0.85em;
    color: #666;
    line-height: 1.3;
}

.no-works {
    color: #999;
    font-style: italic;
    font-size: 0.9em;
}

.view-button {
    background: #3498db;
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.9em;
    font-weight: 500;
    transition: background-color 0.2s;
}

.view-button:hover {
    background: #2980b9;
    color: white;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .translators-grid .table-header,
    .translators-grid .table-row {
        grid-template-columns: 1fr; /* Stack everything vertically on mobile */
        gap: 8px;
    }
    
    .translators-grid .table-header > div:not(:first-child),
    .translators-grid .table-row > div:not(:first-child) {
        padding-left: 20px;
        font-size: 0.9em;
    }
    
    .translator-info {
        gap: 10px;
    }
    
    .translator-avatar,
    .translator-avatar-placeholder {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }
    
    .translator-alt-small {
        display: none; /* Hide duplicate alt name on mobile */
    }
}

/* Alternative: Even wider first column */
.translators-grid.wide-names .table-header,
.translators-grid.wide-names .table-row {
    grid-template-columns: 1.3fr 0.7fr 1fr 0.5fr; /* Even wider first column */
}
</style>

<!-- Alternative using the Pods_Grid class -->
<?php
/*
require_once plugin_dir_path(__FILE__) . '../../inc/class-pods-grid.php';

$grid = new Pods_Grid('translator', [
    'title' => 'Translator',
    'alt_name' => 'Alternative Name',
    'translated_texts' => 'Translated Works',
    'view' => 'View'
]);
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
    <?php $grid->render(); ?>
</div>
*/
?>