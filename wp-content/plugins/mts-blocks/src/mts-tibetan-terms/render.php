<?php
    /**
     * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
     */
    ?>
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