<?php
/**
 * Metas des CPT de branche (register_post_meta, exposées en REST).
 *
 * Le pont cross-site est TOUJOURS un entier post meta `central_*_id`
 * pointant un post du hub (décision D4) — jamais une relation Pods.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auth commune : peut éditer le post concerné.
 */
function mts_meta_auth_callback( $allowed, $meta_key, $object_id ) {
	return current_user_can( 'edit_post', $object_id );
}

add_action( 'init', function () {

	if ( is_main_site() ) {
		return;
	}

	$int_meta = array(
		'type'              => 'integer',
		'single'            => true,
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'auth_callback'     => 'mts_meta_auth_callback',
		'show_in_rest'      => true,
	);

	$string_meta = array(
		'type'              => 'string',
		'single'            => true,
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
		'auth_callback'     => 'mts_meta_auth_callback',
		'show_in_rest'      => true,
	);

	// translation --------------------------------------------------------
	register_post_meta( 'translation', 'central_text_id', $int_meta );
	register_post_meta( 'translation', 'central_translator_ids', array_merge( $string_meta, array(
		// Liste d'IDs centraux, stockée en JSON : "[12,34]".
		'sanitize_callback' => 'mts_sanitize_id_list_json',
	) ) );
	register_post_meta( 'translation', 'source_text_raw', array_merge( $string_meta, array(
		'sanitize_callback' => 'mts_sanitize_multiline',
	) ) );

	// translated_term ----------------------------------------------------
	register_post_meta( 'translated_term', 'central_tibetan_term_id', $int_meta );
	register_post_meta( 'translated_term', 'term_notes', array_merge( $string_meta, array(
		'sanitize_callback' => 'mts_sanitize_multiline',
	) ) );

	// term_usage ---------------------------------------------------------
	register_post_meta( 'term_usage', 'translated_term_id', $int_meta );
	register_post_meta( 'term_usage', 'translation_id', $int_meta );
	register_post_meta( 'term_usage', 'context_source', array_merge( $string_meta, array(
		'sanitize_callback' => 'mts_sanitize_multiline',
	) ) );
	register_post_meta( 'term_usage', 'context_target', array_merge( $string_meta, array(
		'sanitize_callback' => 'mts_sanitize_multiline',
	) ) );
	register_post_meta( 'term_usage', 'confidence', array(
		'type'              => 'number',
		'single'            => true,
		'default'           => 0,
		'sanitize_callback' => 'mts_sanitize_confidence',
		'auth_callback'     => 'mts_meta_auth_callback',
		'show_in_rest'      => true,
	) );
	register_post_meta( 'term_usage', 'created_via', $string_meta );
	register_post_meta( 'term_usage', 'source_anchor', array(
		'type'              => 'boolean',
		'single'            => true,
		'default'           => false,
		'sanitize_callback' => 'rest_sanitize_boolean',
		'auth_callback'     => 'mts_meta_auth_callback',
		'show_in_rest'      => true,
	) );
} );

/**
 * "[1,2,3]" — liste JSON d'entiers positifs, sinon chaîne vide.
 */
function mts_sanitize_id_list_json( $value ) {
	$ids = json_decode( (string) $value, true );
	if ( ! is_array( $ids ) ) {
		return '';
	}
	$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
	return empty( $ids ) ? '' : wp_json_encode( $ids );
}

/**
 * Texte multiligne sans balisage (contextes, sources tibétaines).
 */
function mts_sanitize_multiline( $value ) {
	return sanitize_textarea_field( (string) $value );
}

/**
 * Confiance bornée [0,1].
 */
function mts_sanitize_confidence( $value ) {
	return max( 0.0, min( 1.0, (float) $value ) );
}
