<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

// Get custom fields
$tibetan_title = get_post_meta( $product->get_id(), 'tibetan_title', true );
$original_author = get_post_meta( $product->get_id(), 'original_author', true );
$translator = get_post_meta( $product->get_id(), 'translator', true );
$publication_date = get_post_meta( $product->get_id(), 'publication_date', true );
$publication_status = get_post_meta( $product->get_id(), 'publication_status', true );
if (empty($publication_status)) {
    $publication_status = 'published'; // default
}

// Get taxonomies
$traditions = wp_get_post_terms( $product->get_id(), 'product_tradition' );
$text_types = wp_get_post_terms( $product->get_id(), 'product_text_type' );
$topics = wp_get_post_terms( $product->get_id(), 'product_topic' );

// Get tradition name
$tradition_name = '';
if ( !empty( $traditions ) && !is_wp_error( $traditions ) ) {
    $tradition_name = $traditions[0]->name;
}

// Get product tags for display
$product_tags = wp_get_post_terms( $product->get_id(), 'product_tag' );
?>

<article <?php wc_product_class( 'translation-item', $product ); ?>>
    <div class="translation-item-header">
        <div>
            <h3>
                <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
                    <?php echo esc_html( $product->get_name() ); ?>
                </a>
            </h3>
        </div>
        <span class="status-badge <?php echo esc_attr( $publication_status ); ?>">
            <?php 
            switch ($publication_status) {
                case 'published':
                    echo 'Published';
                    break;
                case 'in-progress':
                    echo 'In Progress';
                    break;
                case 'forthcoming':
                    echo 'Forthcoming';
                    break;
                default:
                    echo 'Published';
            }
            ?>
        </span>
    </div>

    <?php if ( !empty( $tibetan_title ) ) : ?>
        <div class="tibetan-title"><?php echo esc_html( $tibetan_title ); ?></div>
    <?php endif; ?>

    <div class="translation-meta">
        <?php if ( !empty( $original_author ) ) : ?>
            <div class="meta-item">
                <strong>Author:</strong> <?php echo esc_html( $original_author ); ?>
            </div>
        <?php endif; ?>
        
        <?php if ( !empty( $translator ) ) : ?>
            <div class="meta-item">
                <strong>Translator:</strong> <?php echo esc_html( $translator ); ?>
            </div>
        <?php endif; ?>
        
        <?php if ( !empty( $publication_date ) ) : ?>
            <div class="meta-item">
                <strong>Published:</strong> <?php echo esc_html( $publication_date ); ?>
            </div>
        <?php endif; ?>
        
        <?php if ( !empty( $tradition_name ) ) : ?>
            <div class="meta-item">
                <strong>Tradition:</strong> <?php echo esc_html( $tradition_name ); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php
    $description = $product->get_short_description();
    if ( empty( $description ) ) {
        $description = $product->get_description();
    }
    if ( !empty( $description ) ) :
    ?>
        <div class="translation-description">
            <?php echo wp_trim_words( wp_strip_all_tags( $description ), 50, '...' ); ?>
        </div>
    <?php endif; ?>

    <?php if ( !empty( $product_tags ) || !empty( $topics ) ) : ?>
        <div class="translation-tags">
            <?php 
            // Display product tags
            if ( !empty( $product_tags ) && !is_wp_error( $product_tags ) ) {
                foreach ( $product_tags as $tag ) {
                    echo '<span class="tag">' . esc_html( $tag->name ) . '</span>';
                }
            }
            
            // Display topic tags
            if ( !empty( $topics ) && !is_wp_error( $topics ) ) {
                foreach ( $topics as $topic ) {
                    echo '<span class="tag">' . esc_html( $topic->name ) . '</span>';
                }
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="translation-actions">
        <?php if ( $publication_status === 'published' ) : ?>
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="action-link">
                📖 Read Online
            </a>
            <a href="#" class="action-link download-pdf" data-product-id="<?php echo $product->get_id(); ?>">
                ⬇️ Download PDF
            </a>
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="action-link">
                🛒 Request Print Copy
            </a>
            <a href="#" class="action-link view-terms" data-product-id="<?php echo $product->get_id(); ?>">
                🔤 View Terms
            </a>
        <?php elseif ( $publication_status === 'in-progress' ) : ?>
            <a href="#" class="action-link view-progress" data-product-id="<?php echo $product->get_id(); ?>">
                📋 View Progress
            </a>
            <a href="#" class="action-link get-notified" data-product-id="<?php echo $product->get_id(); ?>">
                🔔 Get Notified
            </a>
        <?php elseif ( $publication_status === 'forthcoming' ) : ?>
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="action-link">
                📋 Publication Details
            </a>
            <a href="#" class="action-link get-notified" data-product-id="<?php echo $product->get_id(); ?>">
                🔔 Get Notified
            </a>
        <?php endif; ?>
    </div>

    <?php
    /**
     * Hook: woocommerce_after_shop_loop_item.
     */
    do_action( 'woocommerce_after_shop_loop_item' );
    ?>
</article>