<?php
/**
 * MTS Base — thème parent du réseau (portage mockup-1 « Thangka Heritage »).
 * Spec : doc/specs/theme-mts-base.md. Contenu piloté par les CPT du
 * mu-plugin mts-network (helpers mts_section_* et mts_get_*).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MTS_BASE_VERSION', '0.1.0' );

require get_template_directory() . '/inc/branding.php';
require get_template_directory() . '/inc/template-tags.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'mts-base' ),
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	// Fonts du mockup. TODO prod : self-host (RGPD) — consigné §14 doc maître.
	wp_enqueue_style(
		'mts-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=Spectral:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Montserrat:wght@300;400;500;600&display=swap',
		array(),
		null
	);
	wp_enqueue_style(
		'mts-base',
		get_template_directory_uri() . '/assets/css/base.css',
		array( 'mts-fonts' ),
		(string) filemtime( get_template_directory() . '/assets/css/base.css' )
	);
	if ( is_child_theme() ) {
		wp_enqueue_style(
			'mts-child',
			get_stylesheet_uri(),
			array( 'mts-base' ),
			(string) wp_get_theme()->get( 'Version' )
		);
	}
} );

/**
 * Parité mockup : la classe `active` sur le lien de nav courant
 * (le CSS cible .nav-links a.active).
 */
add_filter( 'nav_menu_link_attributes', function ( $atts, $item ) {
	if ( ! empty( $item->current ) || ! empty( $item->current_item_ancestor ) ) {
		$atts['class'] = isset( $atts['class'] ) ? $atts['class'] . ' active' : 'active';
	}
	return $atts;
}, 10, 2 );

add_filter( 'excerpt_length', function () {
	return 28;
}, 20 );
