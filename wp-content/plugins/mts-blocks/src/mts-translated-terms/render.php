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
            <div>Used in</div>
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
            
            // Get term usages directly from the translated_term (correct relationship)
            $term_usages = $pod ? $pod->field('term_usages') : null;
            $used_in_translations = array();
            $detected_language = '';
            
            if (!empty($term_usages)) {
                // If it's not an array, make it one
                if (!is_array($term_usages)) {
                    $term_usages = array($term_usages);
                }
                
                foreach ($term_usages as $usage) {
                    // Get the translations for this usage
                    $usage_pod = null;
                    if (is_object($usage)) {
                        $usage_pod = pods('term_usage', $usage->ID);
                    } elseif (is_array($usage) && isset($usage['ID'])) {
                        $usage_pod = pods('term_usage', $usage['ID']);
                    }
                    
                    if ($usage_pod) {
                        $translations = $usage_pod->field('translations');
                        if (!empty($translations)) {
                            if (!is_array($translations)) {
                                $translations = array($translations);
                            }
                            
                            foreach ($translations as $translation) {
                                if (is_object($translation)) {
                                    $used_in_translations[] = $translation->post_title;
                                    
                                    // Try to get language from the translation if not set yet
                                    if (empty($detected_language)) {
                                        $trans_pod = pods('translation', $translation->ID);
                                        $trans_language = $trans_pod ? $trans_pod->field('translation_language') : null;
                                        if (!empty($trans_language)) {
                                            if (is_array($trans_language)) {
                                                $detected_language = isset($trans_language['post_title']) ? $trans_language['post_title'] : '';
                                            } else {
                                                $detected_language = $trans_language->post_title ?? '';
                                            }
                                        }
                                    }
                                } elseif (is_array($translation) && isset($translation['post_title'])) {
                                    $used_in_translations[] = $translation['post_title'];
                                    
                                    // Try to get language from the translation if not set yet
                                    if (empty($detected_language) && isset($translation['ID'])) {
                                        $trans_pod = pods('translation', $translation['ID']);
                                        $trans_language = $trans_pod ? $trans_pod->field('translation_language') : null;
                                        if (!empty($trans_language)) {
                                            if (is_array($trans_language)) {
                                                $detected_language = isset($trans_language['post_title']) ? $trans_language['post_title'] : '';
                                            } else {
                                                $detected_language = $trans_language->post_title ?? '';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Remove duplicates and format the translations list
            $used_in_translations = array_unique($used_in_translations);
            $used_in_text = !empty($used_in_translations) ? implode(', ', $used_in_translations) : '';
            
            // Fallback language detection if not found via translations
            if (empty($detected_language)) {
                $term_title = get_the_title();
                // Check if there's a language field directly on translated_term
                $term_language = $pod ? $pod->field('language') : null;
                if (!empty($term_language)) {
                    if (is_array($term_language)) {
                        $detected_language = isset($term_language['post_title']) ? $term_language['post_title'] : '';
                    } else {
                        $detected_language = $term_language->post_title ?? '';
                    }
                }
                
                // Final fallback to character-based detection
                if (empty($detected_language)) {
                    if (preg_match('/[\p{Tibetan}]/u', $term_title)) {
                        $detected_language = 'Tibetan';
                    } elseif (preg_match('/[\p{Han}]/u', $term_title)) {
                        $detected_language = 'Chinese';
                    } elseif (preg_match('/[\p{Hiragana}\p{Katakana}]/u', $term_title)) {
                        $detected_language = 'Japanese';
                    } elseif (preg_match('/[\p{Devanagari}]/u', $term_title)) {
                        $detected_language = 'Sanskrit/Hindi';
                    } elseif (preg_match('/[àâäéèêëïîôöùûüÿç]/i', $term_title)) {
                        $detected_language = 'French';
                    } elseif (preg_match('/[äöüß]/i', $term_title)) {
                        $detected_language = 'German';
                    } elseif (preg_match('/[ñáéíóú]/i', $term_title)) {
                        $detected_language = 'Spanish';
                    } elseif (preg_match('/[àèìòù]/i', $term_title)) {
                        $detected_language = 'Italian';
                    } else {
                        $detected_language = 'English';
                    }
                }
            }
        ?>
            <div class="table-row">
                <div class="term-info">
                    <div class="term-details">
                        <div class="term-title"><?php echo esc_html(get_the_title()); ?></div>
                    </div>
                </div>
                <div class="tibetan-source">
                    <?php if (!empty($tibetan_title) && !empty($tibetan_url)) : ?>
                        <a href="<?php echo esc_url($tibetan_url); ?>" class="tibetan-term-link">
                            <?php echo esc_html($tibetan_title); ?>
                        </a>
                    <?php elseif (!empty($tibetan_title)) : ?>
                        <span class="tibetan-term-static">
                            <?php echo esc_html($tibetan_title); ?>
                        </span>
                    <?php else : ?>
                        <span class="no-tibetan">No Tibetan term linked</span>
                    <?php endif; ?>
                </div>
                <div class="used-in">
                    <?php if (!empty($used_in_text)) : ?>
                        <div class="used-in-text"><?php echo esc_html($used_in_text); ?></div>
                    <?php else : ?>
                        <span class="no-usage">Not used in any translations yet</span>
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

/* Removed term icon container and styling - no more purple icon */

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

/* Removed language hint styling since it's no longer used in first column */

/* Enhanced Tibetan source styling - larger and no mountain icon */
.tibetan-source {
    font-size: 1.3rem; /* Increased further */
    line-height: 1.4;
}

.tibetan-term-link {
    color: #2c3e50; /* Changed from purple to neutral dark */
    text-decoration: none;
    font-weight: 600; /* Increased weight */
    font-size: 1.3rem; /* Increased size */
    transition: color 0.2s ease;
}

.tibetan-term-link:hover {
    color: #34495e; /* Darker neutral on hover */
    text-decoration: underline;
}

.tibetan-term-static {
    color: #2c3e50; /* Changed from gray to match link color */
    font-weight: 600; /* Increased weight */
    font-size: 1.3rem; /* Increased size */
}

.no-tibetan {
    color: #999;
    font-style: italic;
    font-size: 0.85rem;
}

/* Used in styling - simple plain text */
.used-in {
    font-size: 0.9rem;
    line-height: 1.4;
}

.used-in-text {
    color: #555;
    font-weight: 500;
}

.no-usage {
    color: #999;
    font-style: italic;
    font-size: 0.85rem;
}

/* Language badge styling with additional language colors */
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

/* Language-specific colors - expanded */
.language-badge.english { background: #3498db; }
.language-badge.tibetan { background: #e74c3c; }
.language-badge.chinese { background: #f39c12; }
.language-badge.japanese { background: #9b59b6; }
.language-badge.sanskrit_hindi { background: #e67e22; }
.language-badge.french { background: #2ecc71; }
.language-badge.german { background: #34495e; }
.language-badge.spanish { background: #e74c3c; }
.language-badge.italian { background: #27ae60; }
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
        flex-direction: row;
        text-align: left;
        gap: 12px;
    }
    
    .term-title {
        font-size: 1.2rem;
        text-align: left;
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
    .used-in:before { content: "Used in: "; }
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
.translated-terms-grid .table-row:hover .tibetan-term-link {
    color: #1a252f; /* Darker neutral on row hover */
}
</style>