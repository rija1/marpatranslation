<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
<?php
$args = array(
    'post_type' => 'text',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
);

$custom_query = new WP_Query($args);

if ($custom_query->have_posts()) : ?>
    <div class="mts-block-table texts-grid">
        <div class="table-header">
            <div>Text</div>
            <div>Author</div>
            <div>Translations</div>
            <div>View</div>
        </div>
        
        <?php while ($custom_query->have_posts()) : $custom_query->the_post(); 
            $pod = pods('text', get_the_ID());
            
            // Get author
            $author = $pod ? $pod->field('text_textauthor') : null;
            $author_name = '';
            $author_url = '';
            if (!empty($author)) {
                if (is_array($author)) {
                    $author_name = isset($author['post_title']) ? $author['post_title'] : '';
                    $author_url = isset($author['guid']) ? $author['guid'] : get_permalink($author['ID']);
                } else {
                    $author_name = $author->post_title ?? '';
                    $author_url = get_permalink($author->ID);
                }
            }
            
            // Get translations with links - more robust handling
            $translations = $pod ? $pod->field('translations') : null;
            $translation_links = array();
            
            if (!empty($translations)) {
                // Handle single translation (not array)
                if (!is_array($translations)) {
                    $translations = array($translations);
                }
                
                foreach ($translations as $translation) {
                    $translation_title = '';
                    $translation_url = '';
                    
                    // Handle array format
                    if (is_array($translation)) {
                        $translation_title = isset($translation['post_title']) ? $translation['post_title'] : '';
                        $translation_id = isset($translation['ID']) ? $translation['ID'] : '';
                        $translation_url = !empty($translation_id) ? get_permalink($translation_id) : '';
                    } 
                    // Handle object format
                    elseif (is_object($translation)) {
                        $translation_title = isset($translation->post_title) ? $translation->post_title : '';
                        $translation_url = isset($translation->ID) ? get_permalink($translation->ID) : '';
                    }
                    // Handle simple ID
                    elseif (is_numeric($translation)) {
                        $translation_post = get_post($translation);
                        if ($translation_post) {
                            $translation_title = $translation_post->post_title;
                            $translation_url = get_permalink($translation);
                        }
                    }
                    // Handle string ID
                    elseif (is_string($translation) && is_numeric($translation)) {
                        $translation_id = intval($translation);
                        $translation_post = get_post($translation_id);
                        if ($translation_post) {
                            $translation_title = $translation_post->post_title;
                            $translation_url = get_permalink($translation_id);
                        }
                    }
                    
                    // Add link if we have both title and URL
                    if (!empty($translation_title) && !empty($translation_url)) {
                        $translation_links[] = '<a href="' . esc_url($translation_url) . '" class="translation-link">' . esc_html($translation_title) . '</a>';
                    }
                }
            }
        ?>
            <div class="table-row">
                <div class="text-info">
                    <div class="details">
                        <div class="text-title"><?php echo esc_html(get_the_title()); ?></div>
                    </div>
                </div>
                <div class="author-cell">
                    <?php if (!empty($author_name) && !empty($author_url)) : ?>
                        <a href="<?php echo esc_url($author_url); ?>" class="author-link">
                            <?php echo esc_html($author_name); ?>
                        </a>
                    <?php elseif (!empty($author_name)) : ?>
                        <span class="author-static">
                            <?php echo esc_html($author_name); ?>
                        </span>
                    <?php else : ?>
                        <span class="no-content">No author assigned</span>
                    <?php endif; ?>
                </div>
                <div class="translations-cell">
                    <?php if (!empty($translation_links)) : ?>
                        <div class="translation-links">
                            <?php echo implode('', $translation_links); ?>
                        </div>
                    <?php else : ?>
                        <span class="no-content">No translations yet</span>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="view-button">Text Details</a>
                </div>
            </div>
        <?php endwhile; 
        wp_reset_postdata(); ?>
    </div>

<?php else : ?>
    <div class="empty-state">
        <div class="icon">📜</div>
        <p>No texts found.</p>
        <p class="subtitle">Texts will appear here as they are added to the collection.</p>
    </div>
<?php endif; ?>
</div>