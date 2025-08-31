<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
<?php
$args = array(
    'post_type' => 'text_author',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
);

$custom_query = new WP_Query($args);

if ($custom_query->have_posts()) : ?>
    <div class="mts-block-table authors-grid">
        <div class="table-header">
            <div>Author</div>
            <div>Texts</div>
            <div>Translations</div>
            <div>View</div>
        </div>
        
        <?php while ($custom_query->have_posts()) : $custom_query->the_post(); 
            $pod = pods('text_author', get_the_ID());
            
            // Get author image (check both image and picture fields)
            $image = $pod ? $pod->field('image') : null;
            if (empty($image)) {
                $image = $pod ? $pod->field('picture') : null;
            }
            $image_url = '';
            if (!empty($image) && isset($image['guid'])) {
                $image_url = $image['guid'];
            }
            
            // Get authored texts
            $authored_texts = $pod ? $pod->field('authored_texts') : array();
            $text_terms = array();
            
            if (!empty($authored_texts)) {
                foreach ($authored_texts as $text) {
                    if (isset($text['post_title'])) {
                        $text_terms[] = $text['post_title'];
                    }
                }
            }
            
            $texts_string = implode(', ', $text_terms);
            
            // Get translations
            $translations = $pod ? $pod->field('authored_texts_translations') : array();
            $translation_terms = array();
            $translation_count = 0;
            
            if (!empty($translations)) {
                foreach ($translations as $translation) {
                    if (isset($translation['post_title'])) {
                        $translation_count++;
                        if ($translation_count <= 3) { // Show only first 3
                            $translation_terms[] = $translation['post_title'];
                        }
                    }
                }
            }
            
            $translations_display = '';
            if (!empty($translation_terms)) {
                $translations_display = implode(', ', $translation_terms);
                if ($translation_count > 3) {
                    $translations_display .= ' +' . ($translation_count - 3) . ' more';
                }
            } else {
                $translations_display = 'No translations yet';
            }
        ?>
            <div class="table-row">
                <div class="author-info">
                    <div class="image-container">
                        <?php if (!empty($image_url)) : ?>
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 alt="<?php echo esc_attr(get_the_title()); ?>" 
                                 class="avatar">
                        <?php else : ?>
                            <div class="avatar-placeholder">
                                <span><?php echo esc_html(substr(get_the_title(), 0, 1)); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="details">
                        <div class="author-name"><?php echo esc_html(get_the_title()); ?></div>
                    </div>
                </div>
                <div class="texts-cell"><?php echo esc_html($texts_string); ?></div>
                <div class="translations-cell">
                    <?php if ($translation_count > 0) : ?>
                        <span class="translation-count"><?php echo $translation_count; ?> translation<?php echo $translation_count > 1 ? 's' : ''; ?></span>
                        <div class="translation-list"><?php echo esc_html($translations_display); ?></div>
                    <?php else : ?>
                        <span class="no-content">No translations yet</span>
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
    <div class="empty-state">
        <div class="icon">👤</div>
        <p>No authors found.</p>
        <p class="subtitle">Authors will appear here as they are added to the knowledge base.</p>
    </div>
<?php endif; ?>
</div>

<style>
/* Override avatar sizes to be smaller */
.authors-grid .avatar,
.authors-grid .avatar-placeholder {
    width: 60px !important;
    height: 60px !important;
    border-radius: 8px !important;
    border: 2px solid #ddd !important;
    font-size: 24px !important;
}

/* Fix author info layout - name next to avatar horizontally */
.authors-grid .author-info {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 16px !important;
}

.authors-grid .image-container {
    flex-shrink: 0 !important;
}

.authors-grid .details {
    flex: 1 !important;
    min-width: 0 !important;
}

/* Mobile: keep vertical layout */
@media (max-width: 768px) {
    .authors-grid .author-info {
        flex-direction: column !important;
        text-align: center !important;
        gap: 12px !important;
    }
}
</style>