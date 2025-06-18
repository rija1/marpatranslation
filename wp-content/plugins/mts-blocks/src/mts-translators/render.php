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
            
            // Create list of translated work titles (first 3) with links
            $text_links = array();
            if (!empty($translated_texts)) {
                $count = 0;
                foreach ($translated_texts as $text) {
                    if (isset($text['post_title']) && $count < 3) {
                        // Create clickable link for each work
                        $work_url = isset($text['guid']) ? $text['guid'] : get_permalink($text['ID']);
                        $text_links[] = '<a href="' . esc_url($work_url) . '" class="work-link">' . esc_html($text['post_title']) . '</a>';
                        $count++;
                    }
                }
            }
            
            $texts_display = '';
            if (!empty($text_links)) {
                $texts_display = implode(', ', $text_links);
                if ($text_count > 3) {
                    $texts_display .= ' +' . ($text_count - 3) . ' more';
                }
            }
        ?>
            <div class="table-row">
                <div class="translator-info">
                    <div class="translator-image-container">
                        <?php if (!empty($image_url)) : ?>
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 alt="<?php echo esc_attr(get_the_title()); ?>" 
                                 class="translator-avatar">
                        <?php else : ?>
                            <div class="translator-avatar-placeholder">
                                <span><?php echo esc_html(substr(get_the_title(), 0, 1)); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="translator-details">
                        <div class="translator-name"><?php echo esc_html(get_the_title()); ?></div>
                        <?php if (!empty($alt_name)) : ?>
                            <div class="translator-alt-name"><?php echo esc_html($alt_name); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="translated-works">
                    <?php if ($text_count > 0) : ?>
                        <span class="work-count"><?php echo $text_count; ?> work<?php echo $text_count > 1 ? 's' : ''; ?></span>
                        <?php if (!empty($texts_display)) : ?>
                            <div class="work-titles"><?php echo $texts_display; ?></div>
                        <?php endif; ?>
                    <?php else : ?>
                        <span class="no-works">No works yet</span>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="view-button">View Profile</a>
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
    grid-template-columns: 1.5fr 1.2fr 0.5fr; /* Removed alternative name column - now 3 columns like authors */
    gap: 20px; /* Increased gap like authors */
    align-items: center;
    padding: 16px 20px; /* Increased padding like authors */
}

.translators-grid .table-header {
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    font-size: 1rem; /* Increased font size like authors */
}

.translators-grid .table-row {
    border-bottom: 1px solid #dee2e6;
    min-height: 120px; /* Ensure enough height for larger images like authors */
}

.translator-info {
    display: flex;
    align-items: center;
    gap: 16px; /* Increased gap like authors */
}

.translator-image-container {
    flex-shrink: 0;
}

/* Updated to match authors style exactly */
.translator-avatar {
    width: 90px; /* Increased from 45px to match authors */
    height: 90px;
    border-radius: 12px; /* Changed from 50% (circle) to 12px (rounded corners) like authors */
    object-fit: cover;
    border: 3px solid #ddd; /* Increased from 2px to match authors */
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); /* Added shadow like authors */
    flex-shrink: 0;
}

.translator-avatar-placeholder {
    width: 90px; /* Increased from 45px to match authors */
    height: 90px;
    border-radius: 12px; /* Changed from 50% to match authors */
    background: linear-gradient(135deg, #e1e5e9, #c8d3dd); /* Added gradient like authors */
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #666;
    font-size: 32px; /* Increased from 16px to match authors */
    border: 3px solid #ddd; /* Increased from 2px to match authors */
    box-shadow: 0 2px 8px rgba(0,0,0,0.1); /* Added shadow like authors */
    flex-shrink: 0;
}

.translator-details {
    flex: 1;
    min-width: 0; /* Allows text to wrap */
}

.translator-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem; /* Increased to match authors */
    line-height: 1.3;
    word-wrap: break-word;
}

.translator-alt-name {
    font-size: 0.85em; /* Slightly larger than before */
    color: #666;
    font-style: italic;
    margin-top: 2px;
    line-height: 1.2;
}

.translated-works {
    display: flex;
    flex-direction: column;
    gap: 6px; /* Increased gap like authors */
}

.work-count {
    font-weight: 600;
    color: #3498db;
    font-size: 0.95em; /* Increased to match authors */
}

.work-titles {
    font-size: 0.85em;
    color: #666;
    line-height: 1.4; /* Increased line height like authors */
}

/* Style for clickable work links */
.work-link {
    color: #3498db;
    text-decoration: none;
    transition: color 0.2s ease;
}

.work-link:hover {
    color: #2980b9;
    text-decoration: underline;
}

.no-works {
    color: #999;
    font-style: italic;
    font-size: 0.9em;
}

/* Updated button style to match authors */
.view-button {
    background: #3498db;
    color: white;
    padding: 8px 16px; /* Increased padding like authors */
    border-radius: 6px; /* Increased border radius like authors */
    text-decoration: none;
    font-size: 0.9em;
    font-weight: 500;
    transition: all 0.2s ease; /* Enhanced transition like authors */
    display: inline-block;
}

.view-button:hover {
    background: #2980b9;
    color: white;
    transform: translateY(-1px); /* Added transform like authors */
    box-shadow: 0 2px 4px rgba(0,0,0,0.2); /* Added shadow like authors */
}

/* Mobile responsiveness - updated to match authors */
@media (max-width: 768px) {
    .translators-grid .table-header,
    .translators-grid .table-row {
        grid-template-columns: 1fr; /* Stack everything vertically on mobile */
        gap: 12px;
    }
    
    .translators-grid .table-row {
        min-height: auto;
        padding: 20px;
        text-align: center;
    }
    
    .translator-info {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
    
    .translator-avatar,
    .translator-avatar-placeholder {
        width: 100px; /* Larger on mobile like authors */
        height: 100px;
        font-size: 36px;
    }
    
    .translator-name {
        font-size: 1.2rem;
    }
    
    .translators-grid .table-row > div:not(:first-child) {
        padding: 8px 0;
        border-top: 1px solid #eee;
        margin-top: 8px;
    }
}

/* Alternative: Even larger images like authors */
.translators-grid.xl-images .translator-avatar,
.translators-grid.xl-images .translator-avatar-placeholder {
    width: 120px;
    height: 120px;
    font-size: 40px;
}

.translators-grid.xl-images .table-row {
    min-height: 140px;
}
</style>