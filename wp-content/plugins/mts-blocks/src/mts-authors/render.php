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
                    <div class="author-image-container">
                        <?php if (!empty($image_url)) : ?>
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 alt="<?php echo esc_attr(get_the_title()); ?>" 
                                 class="author-avatar">
                        <?php else : ?>
                            <div class="author-avatar-placeholder">
                                <span><?php echo esc_html(substr(get_the_title(), 0, 1)); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="author-details">
                        <div class="author-name"><?php echo esc_html(get_the_title()); ?></div>
                    </div>
                </div>
                <div class="texts-cell"><?php echo esc_html($texts_string); ?></div>
                <div class="translations-cell">
                    <?php if ($translation_count > 0) : ?>
                        <span class="translation-count"><?php echo $translation_count; ?> translation<?php echo $translation_count > 1 ? 's' : ''; ?></span>
                        <div class="translation-list"><?php echo esc_html($translations_display); ?></div>
                    <?php else : ?>
                        <span class="no-translations">No translations yet</span>
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
    <p>No authors found.</p>
<?php endif; ?>
</div>

<style>
.authors-grid {
    width: 100%;
}

.authors-grid .table-header,
.authors-grid .table-row {
    display: grid;
    grid-template-columns: 1.5fr 1.2fr 1.2fr 0.5fr; /* Wider first column for image + name */
    gap: 20px;
    align-items: center;
    padding: 16px 20px;
}

.authors-grid .table-header {
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    font-size: 1rem;
}

.authors-grid .table-row {
    border-bottom: 1px solid #dee2e6;
    min-height: 120px; /* Ensure enough height for larger images */
}

.author-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.author-image-container {
    flex-shrink: 0;
}

.author-avatar {
    width: 90px;
    height: 90px;
    border-radius: 12px; /* Slightly rounded corners instead of full circle */
    object-fit: cover;
    border: 3px solid #ddd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.author-avatar-placeholder {
    width: 90px;
    height: 90px;
    border-radius: 12px;
    background: linear-gradient(135deg, #e1e5e9, #c8d3dd);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #666;
    font-size: 32px;
    border: 3px solid #ddd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.author-details {
    flex: 1;
    min-width: 0; /* Allows text to wrap if needed */
}

.author-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
    line-height: 1.3;
    word-wrap: break-word;
}

.texts-cell {
    font-size: 0.9em;
    color: #555;
    line-height: 1.4;
}

.translations-cell {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.translation-count {
    font-weight: 600;
    color: #3498db;
    font-size: 0.95em;
}

.translation-list {
    font-size: 0.85em;
    color: #666;
    line-height: 1.4;
}

.no-translations {
    color: #999;
    font-style: italic;
    font-size: 0.9em;
}

.view-button {
    background: #3498db;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9em;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-block;
}

.view-button:hover {
    background: #2980b9;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .authors-grid .table-header,
    .authors-grid .table-row {
        grid-template-columns: 1fr; /* Stack everything vertically on mobile */
        gap: 12px;
    }
    
    .authors-grid .table-row {
        min-height: auto;
        padding: 20px;
        text-align: center;
    }
    
    .author-info {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
    
    .author-avatar,
    .author-avatar-placeholder {
        width: 100px;
        height: 100px;
        font-size: 36px;
    }
    
    .author-name {
        font-size: 1.2rem;
    }
    
    .authors-grid .table-row > div:not(:first-child) {
        padding: 8px 0;
        border-top: 1px solid #eee;
        margin-top: 8px;
    }
}

/* Alternative: Even larger images */
.authors-grid.xl-images .author-avatar,
.authors-grid.xl-images .author-avatar-placeholder {
    width: 120px;
    height: 120px;
    font-size: 40px;
}

.authors-grid.xl-images .table-row {
    min-height: 140px;
}
</style>