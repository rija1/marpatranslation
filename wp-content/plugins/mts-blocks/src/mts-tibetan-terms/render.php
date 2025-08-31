<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
<?php
$args = array(
    'post_type' => 'tibetan_term',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
);

$custom_query = new WP_Query($args);

if ($custom_query->have_posts()) : ?>
    <div class="mts-block-table tibetan-terms-grid">
        <div class="table-header">
            <div>Tibetan Term</div>
            <div>Translations</div>
            <div>View</div>
        </div>
        
        <?php while ($custom_query->have_posts()) : $custom_query->the_post(); 
            $pod = pods('tibetan_term', get_the_ID());
            
            // Get translated terms with links
            $translated_terms = $pod ? $pod->field('translated_terms') : array();
            $translation_links = array();
            
            if (!empty($translated_terms)) {
                foreach ($translated_terms as $translated_term) {
                    $term_title = '';
                    $term_url = '';
                    
                    if (is_array($translated_term)) {
                        $term_title = isset($translated_term['post_title']) ? $translated_term['post_title'] : '';
                        $term_url = isset($translated_term['guid']) ? $translated_term['guid'] : get_permalink($translated_term['ID']);
                    } elseif (is_object($translated_term)) {
                        $term_title = $translated_term->post_title ?? '';
                        $term_url = get_permalink($translated_term->ID);
                    }
                    
                    if (!empty($term_title) && !empty($term_url)) {
                        $translation_links[] = '<a href="' . esc_url($term_url) . '" class="translation-link">' . esc_html($term_title) . '</a>';
                    }
                }
            }
        ?>
            <div class="table-row">
                <div class="tibetan-term-info">
                    <div class="tibetan-term-details">
                        <div class="tibetan-term-title"><?php echo esc_html(get_the_title()); ?></div>
                    </div>
                </div>
                <div class="translations-cell">
                    <?php if (!empty($translation_links)) : ?>
                        <div class="translation-links">
                            <?php echo implode('', $translation_links); ?>
                        </div>
                    <?php else : ?>
                        <span class="no-translations">No translations yet</span>
                    <?php endif; ?>
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
        <p>No Tibetan terms found.</p>
        <p class="no-terms-subtitle">Terms will appear here as they are added to the knowledge base.</p>
    </div>
<?php endif; ?>
</div>

<style>
.tibetan-terms-grid {
    width: 100%;
    max-width: 1200px;
    margin: 2rem auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.tibetan-terms-grid .table-header,
.tibetan-terms-grid .table-row {
    display: grid;
    grid-template-columns: 1.5fr 1.2fr 0.6fr; /* 3 columns optimized for terms */
    gap: 20px;
    align-items: center;
    padding: 16px 20px;
}

.tibetan-terms-grid .table-header {
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    font-size: 1rem;
}

.tibetan-terms-grid .table-row {
    border-bottom: 1px solid #dee2e6;
    min-height: 80px;
    transition: background 0.2s ease;
}

.tibetan-terms-grid .table-row:hover {
    background: #f8f9fa;
}

.tibetan-term-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.tibetan-term-details {
    flex: 1;
    min-width: 0;
}

.tibetan-term-title {
    font-weight: 600; /* Bold like other Tibetan text */
    color: #2c3e50; /* Neutral dark color like other grids */
    font-size: 1.3rem; /* Large size matching other grids */
    line-height: 1.3;
    word-wrap: break-word;
}

/* Translations styling - stacked links */
.translations-cell {
    font-size: 0.9rem;
    line-height: 1.4;
}

.translation-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.translation-link {
    color: #8e44ad;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
    display: block;
}

.translation-link:hover {
    color: #732d91;
    text-decoration: underline;
}

.no-translations {
    color: #999;
    font-style: italic;
    font-size: 0.85rem;
}

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
    .tibetan-terms-grid .table-header,
    .tibetan-terms-grid .table-row {
        grid-template-columns: 1fr; /* Stack everything vertically on mobile */
        gap: 12px;
    }
    
    .tibetan-terms-grid .table-row {
        min-height: auto;
        padding: 20px;
        text-align: left;
    }
    
    .tibetan-term-info {
        flex-direction: row;
        text-align: left;
        gap: 12px;
    }
    
    .tibetan-term-title {
        font-size: 1.1rem; /* Slightly smaller on mobile but still prominent */
        text-align: left;
    }
    
    .tibetan-terms-grid .table-row > div:not(:first-child) {
        padding: 8px 0;
        border-top: 1px solid #eee;
        margin-top: 8px;
    }
    
    .tibetan-terms-grid .table-row > div:not(:first-child):before {
        content: attr(data-label);
        font-weight: 600;
        display: block;
        margin-bottom: 4px;
        color: #666;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    
    /* Add labels for mobile */
    .translations-cell:before { content: "Translations: "; }
    
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
    .tibetan-terms-grid .table-header,
    .tibetan-terms-grid .table-row {
        grid-template-columns: 1.3fr 1fr 0.6fr;
        gap: 15px;
        padding: 14px 18px;
    }
    
    .tibetan-term-title {
        font-size: 1.2rem;
    }
    
    .view-button {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
}

/* Focus and accessibility */
.translation-link:focus,
.view-button:focus {
    outline: 2px solid #3498db;
    outline-offset: 2px;
    border-radius: 4px;
}

/* Loading state support */
.tibetan-terms-grid.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Print styles */
@media print {
    .view-button {
        display: none;
    }
    
    .tibetan-terms-grid .table-row {
        break-inside: avoid;
    }
}

/* Extra visual enhancements */
.tibetan-terms-grid .table-row:hover .translation-link {
    color: #5e2d79;
}
</style>