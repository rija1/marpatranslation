    <!-- <?php
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
    <div class="mts-block-table">
        <div class="table-header">
            <div>Tibetan Term</div>
            <div>Translations</div>
            <div>View</div>
        </div>
        
        <?php while ($custom_query->have_posts()) : $custom_query->the_post(); 
            $pod = pods('tibetan_term', get_the_ID());
            $translations = $pod ? $pod->field('translated_terms') : array();
            $translation_terms = array();
            
            if (!empty($translations)) {
                foreach ($translations as $translation) {
                    if (isset($translation['post_title'])) {
                        $translation_terms[] = $translation['post_title'];
                    }
                }
            }
            
            $translation_string = implode(', ', $translation_terms);
        ?>
            <div class="table-row">
                <div><?php echo esc_html(get_the_title()); ?></div>
                <div><?php echo esc_html($translation_string); ?></div>
                <div>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="view-button">View</a>
                </div>
            </div>
        <?php endwhile; 
        wp_reset_postdata(); ?>
    </div>
<?php else : ?>
    <p>No terms found.</p>
<?php endif; ?>
    </div> -->

<?php
    require_once plugin_dir_path(__FILE__) . '../../inc/class-pods-grid.php';
 
 $grid = new Pods_Grid('tibetan_term', [
    'title' => 'Tibetan Term',
    'translated_terms' => 'Translations',
    'view' => 'View'
 ]);
 ?>
 
 <div <?php echo get_block_wrapper_attributes(); ?>>
    <?php $grid->render(); ?>
 </div>