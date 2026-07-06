<?php
/**
 * MTS Referentiel — noindex forcé, pas d'assets front.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'wp_robots', function ( $robots ) {
	$robots['noindex']  = true;
	$robots['nofollow'] = true;
	return $robots;
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'mts-referentiel', get_stylesheet_uri(), array(), '0.1.0' );
} );
