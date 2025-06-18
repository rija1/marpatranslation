<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
<?php
$args = array(
    'post_type' => 'translated_term',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
);

$custom_query = new WP_Query($args);

if ($custom_query->have_posts()) : ?>
    <div class="mts-block-table translated-terms-grid">
        <div class="table-header">
            <div>Translated Term</div>
            <div>Original Tibetan</div>
            <div>Context & Usage</div>
            <div>Language</div>
            <div>View</div>
        </div>
        
        <?php while ($custom_query->have_posts()) : $custom_query->the_post(); 
            $pod = pods('translated_term', get_the_ID());
            
            // Get the related Tibetan term
            $tibetan_term = $pod ? $pod->field('tibetan_term') : null;
            $tibetan_title = '';
            $tibetan_url = '';
            if (!empty($tibetan_term)) {
                if (is_array($tibetan_term)) {
                    $tibetan_title = isset($tibetan_term['post_title']) ? $tibetan_term['post_title'] : '';
                    $tibetan_url = isset($tibetan_term['guid']) ? $tibetan_term['guid'] : get_permalink($tibetan_term['ID']);
                } else {
                    $tibetan_title = $tibetan_term->post_title ?? '';
                    $tibetan_url = get_permalink($tibetan_term->ID);
                }
            }
            
            // Get term content/excerpt for context
            $content_excerpt = '';
            $post_content = get_the_content();
            $post_excerpt = get_the_excerpt();
            
            if (!empty($post_excerpt)) {
                $content_excerpt = wp_trim_words($post_excerpt, 15, '...');
            } elseif (!empty($post_content)) {
                $content_excerpt = wp_trim_words(strip_tags($post_content), 15, '...');
            }
            
            // Detect or infer language from content
            $detected_language = '';
            $term_title = get_the_title();
            
            // Simple language detection based on character sets
            if (preg_match('/[\p{Tibetan}]/u', $term_title)) {
                $detected_language = 'Tibetan';
            } elseif (preg_match('/[\p{Han}]/u', $term_title)) {
                $detected_language = 'Chinese';
            } elseif (preg_match('/[\p{Hiragana}\p{Katakana}]/u', $term_title)) {
                $detected_language = 'Japanese';
            } elseif (preg_match('/[\p{Devanagari}]/u', $term_title)) {
                $detected_language = 'Sanskrit/Hindi';
            } else {
                $detected_language = 'English';
            }
        ?>
            <div class="table-row">
                <div class="term-info">
                    <div class="term-icon-container">
                        <div class="term-icon">
                            <span class="term-symbol">🔤</span>
                        </div>
                    </div>
                    <div class="term-details">
                        <div class="term-title"><?php echo esc_html(get_the_title()); ?></div>
                        <?php if (!empty($detected_language)) : ?>
                            <div class="term-language-hint">
                                <span class="language-indicator"><?php echo esc_html($detected_language); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="tibetan-source">
                    <?php if (!empty($tibetan_title) && !empty($tibetan_url)) : ?>
                        <a href="<?php echo esc_url($tibetan_url); ?>" class="tibetan-term-link">
                            <span class="tibetan-icon">🏔️</span>
                            <?php echo esc_html($tibetan_title); ?>
                        </a>
                    <?php elseif (!empty($tibetan_title)) : ?>
                        <span class="tibetan-term-static">
                            <span class="tibetan-icon">🏔️</span>
                            <?php echo esc_html($tibetan_title); ?>
                        </span>
                    <?php else : ?>
                        <span class="no-tibetan">No Tibetan term linked</span>
                    <?php endif; ?>
                </div>
                <div class="context-usage">
                    <?php if (!empty($content_excerpt)) : ?>
                        <div class="context-text"><?php echo esc_html($content_excerpt); ?></div>
                    <?php else : ?>
                        <span class="no-context">Click to see usage context</span>
                    <?php endif; ?>
                </div>
                <div class="language-cell">
                    <span class="language-badge <?php echo strtolower(str_replace(['/', ' '], ['_', '_'], $detected_language)); ?>">
                        <?php echo esc_html($detected_language); ?>
                    </span>
                </div>
                <div>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="view-button">Term Details</a>
                </div>
            </div>
        <?php endwhile; 
        wp_reset_postdata(); ?>
    </div>
<?php else : ?>
    <div class="no-terms-message">
        <div class="no-terms-icon">📚</div>
        <p>No translated terms found.</p>
        <p class="no-terms-subtitle">Terms will appear here as they are added to the knowledge base.</p>
    </div>
<?php endif; ?>
</div>

<style>
.translated-terms-grid {
    width: 100%;
}

.translated-terms-grid .table-header,
.translated-terms-grid .table-row {
    display: grid;
    grid-template-columns: 1.5fr 1.2fr 1.3fr 0.8fr 0.6fr; /* 5 columns optimized for terms */
    gap: 20px;
    align-items: center;
    padding: 16px 20px;
}

.translated-terms-grid .table-header {
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    font-size: 1rem;
}

.translated-terms-grid .table-row {
    border-bottom: 1px solid #dee2e6;
    min-height: 80px; /* Slightly shorter than image-based grids */
    transition: background 0.2s ease;
}

.translated-terms-grid .table-row:hover {
    background: #f8f9fa;
}

.term-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.term-icon-container {
    flex-shrink: 0;
}

/* Term icon styling - simpler than image avatars but consistent */
.term-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #ddd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    flex-shrink: 0;
}

.term-symbol {
    font-size: 24px;
    filter: brightness(0) invert(1); /* Makes emoji white */
}

.term-details {
    flex: 1;
    min-width: 0;
}

.term-title {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
    line-height: 1.3;
    word-wrap: break-word;
    margin-bottom: 4px;
}

.term-language-hint {
    margin-top: 2px;
}

.language-indicator {
    background: #e9ecef;
    color: #495057;
    padding: 0.15rem 0.4rem;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 500;
    text-transform: uppercase;
}

/* Tibetan source styling with clickable link */
.tibetan-source {
    font-size: 0.95rem;
    line-height: 1.4;
}

.tibetan-term-link {
    color: #8e44ad;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.tibetan-term-link:hover {
    color: #732d91;
    text-decoration: underline;
}

.tibetan-term-static {
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
}

.tibetan-icon {
    font-size: 0.9rem;
}

.no-tibetan {
    color: #999;
    font-style: italic;
    font-size: 0.85rem;
}

/* Context and usage styling */
.context-usage {
    font-size: 0.9rem;
    line-height: 1.4;
}

.context-text {
    color: #555;
    background: #f8f9fa;
    padding: 0.5rem;
    border-radius: 6px;
    border-left: 3px solid #3498db;
    font-style: italic;
}

.no-context {
    color: #999;
    font-style: italic;
    font-size: 0.85rem;
}

/* Language badge styling with different colors */
.language-cell {
    text-align: center;
}

.language-badge {
    padding: 0.3rem 0.7rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
    display: inline-block;
    min-width: 60px;
    text-align: center;
    text-transform: capitalize;
}

/* Language-specific colors */
.language-badge.english { background: #3498db; }
.language-badge.tibetan { background: #e74c3c; }
.language-badge.chinese { background: #f39c12; }
.language-badge.japanese { background: #9b59b6; }
.language-badge.sanskrit_hindi { background: #e67e22; }
.language-badge.default { background: #95a5a6; }

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

/* No terms message styling */
.no-terms-message {
    text-align: center;
    padding: 4rem 2rem;
    background: #f8f9fa;
    border-radius: 12px;
    border: 2px dashed #dee2e6;
}

.no-terms-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.6;
}

.no-terms-message p {
    color: #6c757d;
    font-size: 1.1rem;
    margin: 0.5rem 0;
}

.no-terms-subtitle {
    font-size: 0.9rem !important;
    opacity: 0.8;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .translated-terms-grid .table-header,
    .translated-terms-grid .table-row {
        grid-template-columns: 1fr; /* Stack everything vertically on mobile */
        gap: 12px;
    }
    
    .translated-terms-grid .table-row {
        min-height: auto;
        padding: 20px;
        text-align: left;
    }
    
    .term-info {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
    
    .term-icon {
        width: 80px;
        height: 80px;
        align-self: center;
    }
    
    .term-symbol {
        font-size: 32px;
    }
    
    .term-title {
        font-size: 1.2rem;
        text-align: center;
    }
    
    .translated-terms-grid .table-row > div:not(:first-child) {
        padding: 8px 0;
        border-top: 1px solid #eee;
        margin-top: 8px;
        position: relative;
    }
    
    /* Add labels for mobile */
    .translated-terms-grid .table-row > div:not(:first-child):before {
        content: attr(data-label);
        font-weight: 600;
        display: block;
        margin-bottom: 4px;
        color: #666;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    
    .tibetan-source:before { content: "Original Tibetan: "; }
    .context-usage:before { content: "Context & Usage: "; }
    .language-cell:before { content: "Language: "; }
    
    .view-button {
        width: 100%;
        margin-top: 8px;
    }
    
    .no-terms-message {
        padding: 2rem 1rem;
    }
    
    .no-terms-icon {
        font-size: 3rem;
    }
}

/* Tablet responsiveness */
@media (max-width: 1024px) and (min-width: 769px) {
    .translated-terms-grid .table-header,
    .translated-terms-grid .table-row {
        grid-template-columns: 1.3fr 1fr 1.1fr 0.7fr 0.6fr;
        gap: 15px;
        padding: 14px 18px;
    }
    
    .term-icon {
        width: 50px;
        height: 50px;
    }
    
    .term-symbol {
        font-size: 20px;
    }
    
    .term-title {
        font-size: 1rem;
    }
    
    .view-button {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
}

/* Print styles */
@media print {
    .view-button {
        display: none;
    }
    
    .translated-terms-grid .table-row {
        break-inside: avoid;
    }
    
    .term-icon {
        background: #f0f0f0 !important;
        color: #333 !important;
    }
}

/* Focus and accessibility */
.tibetan-term-link:focus,
.view-button:focus {
    outline: 2px solid #3498db;
    outline-offset: 2px;
    border-radius: 4px;
}

/* Loading state support */
.translated-terms-grid.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Search/filter compatibility */
.translated-terms-grid[data-filtered="true"] .table-row {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Extra visual enhancements */
.translated-terms-grid .table-row:hover .term-icon {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

.translated-terms-grid .table-row:hover .tibetan-term-link {
    color: #5e2d79;
}
</style>