<?php
/**
 * Cache réseau des helpers cross-site.
 *
 * Transients réseau versionnés : l'invalidation incrémente un compteur
 * réseau (mts_cache_ver), ce qui rend obsolètes toutes les clés d'un coup.
 * Brutal mais suffisant au volume MTS (TTL 12 h en garde-fou).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const MTS_CACHE_TTL = 12 * HOUR_IN_SECONDS;

function mts_cache_ver() {
	return (int) get_site_option( 'mts_cache_ver', 1 );
}

function mts_bump_cache_ver() {
	update_site_option( 'mts_cache_ver', mts_cache_ver() + 1 );
}

function mts_cache_key( $fn, $args ) {
	return 'mts_' . $fn . '_' . md5( wp_json_encode( $args ) ) . '_v' . mts_cache_ver();
}

function mts_cache_get( $key ) {
	return get_site_transient( $key );
}

function mts_cache_set( $key, $value ) {
	set_site_transient( $key, $value, MTS_CACHE_TTL );
}

/**
 * Invalidation : toute écriture sur un type participant aux lectures
 * cross-site (centraux du hub, production des branches, produits pour
 * mts_get_book_shop_url) invalide le cache réseau.
 */
function mts_cache_invalidating_types() {
	return array(
		'tibetan_term',
		'text',
		'text_author',
		'translator',
		'translation',
		'translated_term',
		'term_usage',
		'product',
	);
}

foreach ( mts_cache_invalidating_types() as $mts_type ) {
	add_action( 'save_post_' . $mts_type, 'mts_bump_cache_ver' );
}
unset( $mts_type );

add_action( 'deleted_post', function ( $post_id, $post ) {
	if ( $post && in_array( $post->post_type, mts_cache_invalidating_types(), true ) ) {
		mts_bump_cache_ver();
	}
}, 10, 2 );

add_action( 'trashed_post', function ( $post_id ) {
	$post = get_post( $post_id );
	if ( $post && in_array( $post->post_type, mts_cache_invalidating_types(), true ) ) {
		mts_bump_cache_ver();
	}
} );
