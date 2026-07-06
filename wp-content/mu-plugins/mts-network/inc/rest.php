<?php
/**
 * REST mts/v1 — lectures publiques (données publiées du référentiel),
 * écritures gatées par la capability mts_use_atelier (propositions pending).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function () {

	register_rest_route( 'mts/v1', '/terms/search', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'mts_rest_terms_search',
		// Données publiées uniquement → lecture publique (remplace l'AJAX
		// search historique wp_ajax_nopriv_search_terms).
		'permission_callback' => '__return_true',
		'args'                => array(
			'q'     => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function ( $value ) {
					return is_string( $value ) && '' !== trim( $value );
				},
			),
			'limit' => array(
				'type'              => 'integer',
				'default'           => 20,
				'sanitize_callback' => 'absint',
			),
		),
	) );

	register_rest_route( 'mts/v1', '/terms/(?P<id>\d+)', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'mts_rest_term_get',
		'permission_callback' => '__return_true',
		'args'                => array(
			'id' => array(
				'required'          => true,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
		),
	) );

	register_rest_route( 'mts/v1', '/terms', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mts_rest_term_create',
		'permission_callback' => 'mts_rest_can_use_atelier',
		'args'                => array(
			'tibetan'        => array( 'required' => true, 'type' => 'string' ),
			'title'          => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'wylie'          => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'definition'     => array( 'type' => 'string' ),
			'context_source' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ),
		),
	) );

	register_rest_route( 'mts/v1', '/texts', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mts_rest_text_create',
		'permission_callback' => 'mts_rest_can_use_atelier',
		'args'                => array(
			'title'       => array( 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
			'source'      => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ),
			'author_name' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
		),
	) );
} );

/**
 * Les propositions centrales exigent la capability mts_use_atelier
 * (seedée sur editor/administrator des branches).
 */
function mts_rest_can_use_atelier() {
	if ( current_user_can( 'mts_use_atelier' ) ) {
		return true;
	}
	return new WP_Error(
		'mts_forbidden',
		__( 'You are not allowed to propose entries to the central referential.', 'mts-network' ),
		array( 'status' => rest_authorization_required_code() )
	);
}

function mts_rest_terms_search( WP_REST_Request $request ) {
	$limit = min( 50, max( 1, (int) $request['limit'] ) );
	return rest_ensure_response( mts_search_tibetan_terms( $request['q'], array( 'limit' => $limit ) ) );
}

function mts_rest_term_get( WP_REST_Request $request ) {
	$term = mts_get_tibetan_term( (int) $request['id'] );
	if ( ! $term || 'publish' !== $term['status'] ) {
		return new WP_Error( 'mts_not_found', __( 'Term not found.', 'mts-network' ), array( 'status' => 404 ) );
	}
	$term['translations'] = mts_get_term_translations( $term['id'] );
	return rest_ensure_response( $term );
}

function mts_rest_term_create( WP_REST_Request $request ) {
	$result = mts_create_pending_tibetan_term( array(
		'tibetan'        => (string) $request['tibetan'],
		'title'          => (string) $request['title'],
		'wylie'          => (string) $request['wylie'],
		'definition'     => (string) $request['definition'],
		'context_source' => (string) $request['context_source'],
	) );
	if ( is_wp_error( $result ) ) {
		$result->add_data( array( 'status' => 400 ) );
		return $result;
	}
	return rest_ensure_response( array_merge( $result, array(
		'status' => $result['existing'] ? 'existing' : 'pending',
	) ) );
}

function mts_rest_text_create( WP_REST_Request $request ) {
	$result = mts_create_pending_text( array(
		'title'       => (string) $request['title'],
		'source'      => (string) $request['source'],
		'author_name' => (string) $request['author_name'],
	) );
	if ( is_wp_error( $result ) ) {
		$result->add_data( array( 'status' => 400 ) );
		return $result;
	}
	return rest_ensure_response( array_merge( $result, array(
		'status' => $result['existing'] ? 'existing' : 'pending',
	) ) );
}
