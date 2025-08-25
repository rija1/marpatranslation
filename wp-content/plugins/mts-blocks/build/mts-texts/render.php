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
            <div>Full Title</div>
            <div>Author</div>
            <div>Translations</div>
            <div>View</div>
        </div>
        
        <?php while ($custom_query->have_posts()) : $custom_query->the_post(); 
            $pod = pods('text', get_the_ID());
            
            // Get full title
            $full_title = $pod ? $pod->field('text_full_title') : '';
            
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
            
            // Get translations - for now just count them
            $translations = $pod ? $pod->field('text_translations') : array();
            $translation_count = 0;
            if (!empty($translations)) {
                $translation_count = is_array($translations) ? count($translations) : 1;
            }
        ?>
            <div class="table-row">
                <div class="text-info">
                    <div class="text-details">
                        <div class="text-title"><?php echo esc_html(get_the_title()); ?></div>
                        <div class="text-meta">
                            <span class="text-type-badge">Original Text</span>
                        </div>
                    </div>
                </div>
                <div class="full-title">
                    <?php if (!empty($full_title) && $full_title !== get_the_title()) : ?>
                        <span class="full-title-text">
                            <?php echo esc_html($full_title); ?>
                        </span>
                    <?php else : ?>
                        <span class="no-full-title">Same as title</span>
                    <?php endif; ?>
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
                        <span class="no-author">No author assigned</span>
                    <?php endif; ?>
                </div>
                <div class="translations-cell">
                    <?php if ($translation_count > 0) : ?>
                        <div class="translation-count">
                            <span class="count-badge"><?php echo $translation_count; ?></span>
                            <span class="count-label"><?php echo $translation_count === 1 ? 'translation' : 'translations'; ?></span>
                        </div>
                    <?php else : ?>
                        <span class="no-translations">No translations yet</span>
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
    <div class="no-texts-message">
        <div class="no-texts-icon">📜</div>
        <p>No texts found.</p>
        <p class="no-texts-subtitle">Texts will appear here as they are added to the collection.</p>
    </div>
<?php endif; ?>
</div>

<style>
.texts-grid {
    width: 100%;
}

.texts-grid .table-header,
.texts-grid .table-row {
    display: grid;
    grid-template-columns: 1.5fr 1.2fr 1fr 0.8fr 0.6fr; /* 5 columns optimized for content */
    gap: 20px;
    align-items: center;
    padding: 16px 20px;
}

.texts-grid .table-header {
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    font-size: 1rem;
}

.texts-grid .table-row {
    border-bottom: 1px solid #dee2e6;
    min-height: 80px;
    transition: background 0.2s ease;
}

.texts-grid .table-row:hover {
    background: #f8f9fa;
}

.text-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.text-details {
    flex: 1;
    min-width: 0;
}

.text-title {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
    line-height: 1.3;
    word-wrap: break-word;
    margin-bottom: 4px;
}

.text-meta {
    margin-top: 4px;
}

.text-type-badge {
    background: #e74c3c;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Full title styling - matching Tibetan text from previous grids */
.full-title {
    font-size: 1.3rem; /* Same size as Tibetan text */
    line-height: 1.4;
}

.full-title-text {
    color: #2c3e50; /* Neutral dark color like Tibetan text */
    font-weight: 600; /* Bold like Tibetan text */
    font-size: 1.3rem;
    line-height: 1.4;
}

.no-full-title {
    color: #999;
    font-style: italic;
    font-size: 0.85rem;
}

/* Author styling */
.author-cell {
    font-size: 0.9rem;
    line-height: 1.4;
}

.author-link {
    color: #8e44ad;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.author-link:hover {
    color: #732d91;
    text-decoration: underline;
}

.author-static {
    color: #6c757d;
    font-weight: 500;
}

.no-author {
    color: #999;
    font-style: italic;
    font-size: 0.85rem;
}

/* Translations styling */
.translations-cell {
    font-size: 0.9rem;
    line-height: 1.4;
    text-align: center;
}

.translation-count {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.count-badge {
    background: #27ae60;
    color: white;
    padding: 0.3rem 0.6rem;
    border-radius: 50%;
    font-weight: 600;
    font-size: 0.8rem;
    min-width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.count-label {
    color: #555;
    font-size: 0.8rem;
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

/* No texts message styling */
.no-texts-message {
    text-align: center;
    padding: 4rem 2rem;
    background: #f8f9fa;
    border-radius: 12px;
    border: 2px dashed #dee2e6;
}

.no-texts-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.6;
}

.no-texts-message p {
    color: #6c757d;
    font-size: 1.1rem;
    margin: 0.5rem 0;
}

.no-texts-subtitle {
    font-size: 0.9rem !important;
    opacity: 0.8;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .texts-grid .table-header,
    .texts-grid .table-row {
        grid-template-columns: 1fr; /* Stack everything vertically on mobile */
        gap: 12px;
    }
    
    .texts-grid .table-row {
        min-height: auto;
        padding: 20px;
        text-align: left;
    }
    
    .text-info {
        flex-direction: row; /* Keep horizontal since no image */
        text-align: left;
        gap: 12px;
    }
    
    .text-title {
        font-size: 1.2rem;
        text-align: left;
    }
    
    .texts-grid .table-row > div:not(:first-child) {
        padding: 8px 0;
        border-top: 1px solid #eee;
        margin-top: 8px;
    }
    
    .texts-grid .table-row > div:not(:first-child):before {
        content: attr(data-label);
        font-weight: 600;
        display: block;
        margin-bottom: 4px;
        color: #666;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    
    /* Add labels for mobile */
    .full-title:before { content: "Full Title: "; }
    .author-cell:before { content: "Author: "; }
    .translations-cell:before { content: "Translations: "; }
    
    .view-button {
        width: 100%;
        margin-top: 8px;
    }
    
    .no-texts-message {
        padding: 2rem 1rem;
    }
    
    .no-texts-icon {
        font-size: 3rem;
    }
}

/* Tablet responsiveness */
@media (max-width: 1024px) and (min-width: 769px) {
    .texts-grid .table-header,
    .texts-grid .table-row {
        grid-template-columns: 1.3fr 1fr 0.8fr 0.7fr 0.6fr;
        gap: 15px;
        padding: 14px 18px;
    }
    
    .text-title {
        font-size: 1rem;
    }
    
    .view-button {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
}

/* Focus and accessibility */
.author-link:focus,
.view-button:focus {
    outline: 2px solid #3498db;
    outline-offset: 2px;
    border-radius: 4px;
}

/* Loading state support */
.texts-grid.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Print styles */
@media print {
    .view-button {
        display: none;
    }
    
    .texts-grid .table-row {
        break-inside: avoid;
    }
}

/* Extra visual enhancements */
.texts-grid .table-row:hover .author-link {
    color: #5e2d79;
}
</style>