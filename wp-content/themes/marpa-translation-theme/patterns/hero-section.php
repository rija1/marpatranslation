<?php
/**
 * Title: Hero Section with Overlay
 * Slug: marpa-translation/hero-section
 * Description: A hero section with background image, overlay, and call-to-action buttons
 * Categories: featured, header
 */
?>

<!-- wp:group {"className":"ornamental-border"} -->
<div class="wp-block-group ornamental-border"></div>
<!-- /wp:group -->

<!-- wp:cover {"url":"","dimRatio":85,"overlayColor":"royal-blue","minHeight":60,"minHeightUnit":"vh","className":"hero-section","style":{"spacing":{"padding":{"top":"4rem","bottom":"4rem"}}}} -->
<div class="wp-block-cover hero-section" style="min-height:60vh;padding-top:4rem;padding-bottom:4rem">
    <span aria-hidden="true" class="wp-block-cover__background has-royal-blue-background-color has-background-dim-80 has-background-dim"></span>
    
    <div class="wp-block-cover__inner-container">
        <!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontStyle":"normal","fontWeight":"400","fontSize":"3rem"},"spacing":{"margin":{"bottom":"1.5rem"}},"color":{"text":"var(--wp--preset--color--white)"}},"fontFamily":"dm-serif-text"} -->
        <h1 class="wp-block-heading has-text-align-center has-white-color has-text-color has-dm-serif-text-font-family" style="margin-bottom:1.5rem;font-size:3rem;font-style:normal;font-weight:400">Preserving Buddhist Wisdom</h1>
        <!-- /wp:heading -->
        
        <!-- wp:paragraph {"align":"center","style":{"color":{"text":"var(--wp--preset--color--white)"},"typography":{"fontSize":"1.2rem"},"spacing":{"margin":{"bottom":"2rem"}}}} -->
        <p class="has-text-align-center has-white-color has-text-color" style="margin-bottom:2rem;font-size:1.2rem">Authentic translations of classical Buddhist texts for modern practitioners and scholars</p>
        <!-- /wp:paragraph -->
        
        <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
        <div class="wp-block-buttons">
            <!-- wp:button {"backgroundColor":"gold","textColor":"dark-gray","className":"is-style-fill"} -->
            <div class="wp-block-button is-style-fill"><a class="wp-block-button__link has-dark-gray-color has-gold-background-color has-text-color has-background wp-element-button">Explore Translations</a></div>
            <!-- /wp:button -->
            
            <!-- wp:button {"textColor":"white","className":"is-style-outline"} -->
            <div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-white-color has-text-color wp-element-button">Our Mission</a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->
    </div>
</div>
<!-- /wp:cover -->