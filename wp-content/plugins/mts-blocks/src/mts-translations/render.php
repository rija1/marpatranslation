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
    <div class="mts-block-table translations-grid">
        <div class="table-header">
            <div>Translation</div>
            <div>Original Text</div>
            <div>Translators</div>
            <div>Status</div>
            <div>View</div>
        </div>
        
        <?php while ($custom_query->have_posts()) : $custom_query->the_post(); 
            $pod = pods('translation', get_the_ID());
            
            // Get translation image
            $image = $pod ? $pod->field('image') : null;
            $image_url = '';
            if (!empty($image) && isset($image['guid'])) {
                $image_url = $image['guid'];
            }
            
            // Get source text with link
            $source_text = $pod ? $pod->field('translation_source_text') : null;
            $source_title = '';
            $source_url = '';
            if (!empty($source_text)) {
                if (is_array($source_text)) {
                    $source_title = isset($source_text['post_title']) ? $source_text['post_title'] : '';
                    $source_url = isset($source_text['guid']) ? $source_text['guid'] : get_permalink($source_text['ID']);
                } else {
                    $source_title = $source_text->post_title ?? '';
                    $source_url = get_permalink($source_text->ID);
                }
            }
            
            // Get translators with links
            $translators = $pod ? $pod->field('translation_translators') : array();
            $translator_links = array();
            if (!empty($translators)) {
                foreach ($translators as $translator) {
                    if (isset($translator['post_title'])) {
                        $translator_url = isset($translator['guid']) ? $translator['guid'] : get_permalink($translator['ID']);
                        $translator_links[] = '<a href="' . esc_url($translator_url) . '" class="translator-link">' . esc_html($translator['post_title']) . '</a>';
                    }
                }
            }
            $translators_display = !empty($translator_links) ? implode(', ', $translator_links) : 'No translators assigned';
            
            // Get language
            $language = $pod ? $pod->field('translation_language') : null;
            $language_name = '';
            if (!empty($language)) {
                $language_name = is_array($language) ? ($language['post_title'] ?? '') : ($language->post_title ?? '');
            }
            
            // Get status with proper labels
            $status_value = $pod ? $pod->field('translation_status') : '';
            $status_labels = array(
                '0' => 'Not Started',
                '1' => 'In Progress', 
                '2' => 'Editing',
                '3' => 'Reviewing',
                '4' => 'Waiting for Publication',
                '5' => 'Published'
            );
            $status_label = isset($status_labels[$status_value]) ? $status_labels[$status_value] : 'Unknown';
        ?>
            <div class="table-row">
                <div class="translation-info">
                    <div class="translation-image-container">
                        <?php if (!empty($image_url)) : ?>
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 alt="<?php echo esc_attr(get_the_title()); ?>" 
                                 class="translation-avatar">
                        <?php else : ?>
                            <div class="translation-avatar-placeholder">
                                <span><?php echo esc_html(substr(get_the_title(), 0, 1)); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="translation-details">
                        <div class="translation-title"><?php echo esc_html(get_the_title()); ?></div>
                        <?php if (!empty($language_name)) : ?>
                            <div class="translation-language-small">
                                <span class="language-badge"><?php echo esc_html($language_name); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="source-text">
                    <?php if (!empty($source_title) && !empty($source_url)) : ?>
                        <a href="<?php echo esc_url($source_url); ?>" class="source-text-link">
                            <?php echo esc_html($source_title); ?>
                        </a>
                    <?php elseif (!empty($source_title)) : ?>
                        <?php echo esc_html($source_title); ?>
                    <?php else : ?>
                        <span class="no-source">No source text</span>
                    <?php endif; ?>
                </div>
                <div class="translators-cell">
                    <?php echo $translators_display; ?>
                </div>
                <div class="status-cell">
                    <span class="translation-status status-<?php echo esc_attr($status_value); ?>">
                        <?php echo esc_html($status_label); ?>
                    </span>
                </div>
                <div>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="view-button">Translation Details</a>
                </div>
            </div>
        <?php endwhile; 
        wp_reset_postdata(); ?>
    </div>
<?php else : ?>
    <p>No translations found.</p>
<?php endif; ?>
</div>

<style>
.translations-grid {
    width: 100%;
}

.translations-grid .table-header,
.translations-grid .table-row {
    display: grid;
    grid-template-columns: 1.5fr 1.2fr 1fr 0.8fr 0.6fr; /* 5 columns optimized for content */
    gap: 20px;
    align-items: center;
    padding: 16px 20px;
}

.translations-grid .table-header {
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    font-size: 1rem;
}

.translations-grid .table-row {
    border-bottom: 1px solid #dee2e6;
    min-height: 120px; /* Ensure enough height for larger images */
}

.translation-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.translation-image-container {
    flex-shrink: 0;
}

/* Translation image styling - matches authors/translators */
.translation-avatar {
    width: 90px;
    height: 90px;
    border-radius: 12px; /* Rounded corners like authors */
    object-fit: cover;
    border: 3px solid #ddd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    flex-shrink: 0;
}

.translation-avatar-placeholder {
    width: 90px;
    height: 90px;
    border-radius: 12px;
    background: linear-gradient(135deg, #e1e5e9, #c8d3dd); /* Gradient like authors */
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #666;
    font-size: 32px;
    border: 3px solid #ddd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    flex-shrink: 0;
}

.translation-details {
    flex: 1;
    min-width: 0;
}

.translation-title {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
    line-height: 1.3;
    word-wrap: break-word;
}

.translation-language-small {
    margin-top: 4px;
}

.language-badge {
    background: #6c757d;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Source text styling with clickable link */
.source-text {
    font-size: 0.9rem;
    line-height: 1.4;
}

.source-text-link {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.source-text-link:hover {
    color: #2980b9;
    text-decoration: underline;
}

.no-source {
    color: #999;
    font-style: italic;
    font-size: 0.85rem;
}

/* Translators styling with clickable links */
.translators-cell {
    font-size: 0.9rem;
    line-height: 1.4;
}

.translator-link {
    color: #8e44ad;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.translator-link:hover {
    color: #732d91;
    text-decoration: underline;
}

/* Status styling with color coding */
.status-cell {
    text-align: center;
}

.translation-status {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
    display: inline-block;
    min-width: 80px;
    text-align: center;
}

/* Status color coding based on workflow */
.translation-status.status-0 { background: #95a5a6; } /* Not Started - Gray */
.translation-status.status-1 { background: #f39c12; } /* In Progress - Orange */
.translation-status.status-2 { background: #e67e22; } /* Editing - Dark Orange */
.translation-status.status-3 { background: #9b59b6; } /* Reviewing - Purple */
.translation-status.status-4 { background: #3498db; } /* Waiting for Publication - Blue */
.translation-status.status-5 { background: #27ae60; } /* Published - Green */

/* Enhanced button styling */
.view-button {
    background: #3498db;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-block;
    text-align: center;
}

.view-button:hover {
    background: #2980b9;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .translations-grid .table-header,
    .translations-grid .table-row {
        grid-template-columns: 1fr; /* Stack everything vertically on mobile */
        gap: 12px;
    }
    
    .translations-grid .table-row {
        min-height: auto;
        padding: 20px;
        text-align: left;
    }
    
    .translation-info {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
    
    .translation-avatar,
    .translation-avatar-placeholder {
        width: 100px;
        height: 100px;
        font-size: 36px;
        align-self: center;
    }
    
    .translation-title {
        font-size: 1.2rem;
        text-align: center;
    }
    
    .translations-grid .table-row > div:not(:first-child) {
        padding: 8px 0;
        border-top: 1px solid #eee;
        margin-top: 8px;
    }
    
    .translations-grid .table-row > div:before {
        content: attr(data-label);
        font-weight: 600;
        display: block;
        margin-bottom: 4px;
        color: #666;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    
    /* Add labels for mobile */
    .source-text:before { content: "Original Text: "; }
    .translators-cell:before { content: "Translators: "; }
    .status-cell:before { content: "Status: "; }
    
    .view-button {
        width: 100%;
        margin-top: 8px;
    }
}

/* Tablet responsiveness */
@media (max-width: 1024px) and (min-width: 769px) {
    .translations-grid .table-header,
    .translations-grid .table-row {
        grid-template-columns: 1.3fr 1fr 0.8fr 0.7fr 0.6fr;
        gap: 15px;
        padding: 14px 18px;
    }
    
    .translation-avatar,
    .translation-avatar-placeholder {
        width: 70px;
        height: 70px;
        font-size: 24px;
    }
    
    .translation-title {
        font-size: 1rem;
    }
    
    .view-button {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
}

/* Extra large images option */
.translations-grid.xl-images .translation-avatar,
.translations-grid.xl-images .translation-avatar-placeholder {
    width: 120px;
    height: 120px;
    font-size: 40px;
}

.translations-grid.xl-images .table-row {
    min-height: 140px;
}

/* Hover effects for entire row */
.translations-grid .table-row:hover {
    background: #f8f9fa;
    transition: background 0.2s ease;
}

/* Loading state support */
.translations-grid.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Print styles */
@media print {
    .view-button {
        display: none;
    }
    
    .translations-grid .table-row {
        break-inside: avoid;
    }
}
</style>