<?php
/**
 * Branding par site (pattern Drolung) : theme_mods surchargables via le
 * Customizer, défauts communs. Le nom de site WP (blogname) reste libre.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mts_get_brand_name() {
	return (string) get_theme_mod( 'mts_brand_name', __( 'Marpa Translation Society', 'mts-base' ) );
}

function mts_get_brand_tag() {
	return (string) get_theme_mod( 'mts_brand_tag', __( 'Kagyu Buddhist Translations', 'mts-base' ) );
}

function mts_get_logo_url() {
	$custom = get_theme_mod( 'mts_brand_logo', '' );
	return $custom ? (string) $custom : get_template_directory_uri() . '/assets/images/mts-logo-full.svg';
}

function mts_get_footer_tagline() {
	return (string) get_theme_mod( 'mts_footer_tagline', __( 'Preserving the wisdom of the Kagyu lineage through authentic, practitioner-led translation.', 'mts-base' ) );
}

/**
 * URL et libellé du bouton don — surchargeables par branche (filtres).
 */
function mts_get_donate_url() {
	return (string) apply_filters( 'mts_donate_url', '#' );
}

function mts_get_donate_label() {
	return (string) apply_filters( 'mts_donate_label', __( 'Support Us', 'mts-base' ) );
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'mts_branding', array(
		'title'    => __( 'MTS Branding', 'mts-base' ),
		'priority' => 30,
	) );
	$fields = array(
		'mts_brand_name'     => __( 'Brand name', 'mts-base' ),
		'mts_brand_tag'      => __( 'Brand tagline (under the name)', 'mts-base' ),
		'mts_brand_logo'     => __( 'Logo URL (empty = theme default)', 'mts-base' ),
		'mts_footer_tagline' => __( 'Footer tagline', 'mts-base' ),
	);
	foreach ( $fields as $key => $label ) {
		$wp_customize->add_setting( $key, array( 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $key, array(
			'label'   => $label,
			'section' => 'mts_branding',
			'type'    => 'text',
		) );
	}
} );
