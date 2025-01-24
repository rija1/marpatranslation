<?php

function pa($truc,$die,$vdump) {
    echo '<pre>';
    if($vdump) {
        var_dump($truc);
    } else {
        print_r($truc);
    }
    echo '</pre>';
    if($die) {
        die();
    }
}

function my_theme_enqueue_styles()
{
    $parent_style = 'twentytwentyfour-style';
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array($parent_style),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

function my_theme_scripts()
{
    wp_enqueue_script('bsf', get_stylesheet_directory_uri() . '/js/bsf.js');
    wp_enqueue_script('chartjs', get_stylesheet_directory_uri() . '/js/chart.js');
}
add_action('wp_enqueue_scripts', 'my_theme_scripts');

add_filter( 'woocommerce_is_purchasable', 'mts_is_purchasable' );
function mts_is_purchasable( $is_purchasable ) {

return false;    
	global $product;

    // Check if a book an approved book request allows purchasing the product
    if($is_purchasable) {
        if (approved_book_request()) {
            $is_purchasable = true;
        } else {
            // Display "Request This Book" button
            add_action( 'woocommerce_before_add_to_cart_form', 'mts_add_book_request_button' ); 
            $is_purchasable = false;
        }
    }
	
	return $is_purchasable;
	
}

function approved_book_request() {
    // Check if a book an approved book request allows purchasing the product
    return false;
}

// add a piece of html at the woocommerce_before_add_to_cart_form hook

function mts_add_book_request_button() {
    echo '<button type="submit" name="book-request" class="single_add_to_cart_button button alt wp-element-button">Request This Book</button>';
    // A modal popup is displayed
    

}