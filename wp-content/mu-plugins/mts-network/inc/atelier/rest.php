<?php
/**
 * Atelier — REST mts/v1/atelier/* (branches, capability mts_use_atelier).
 * Actions candidats : spec atelier-terminologique.md §4.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function () {
	if ( is_main_site() ) {
		return;
	}

	register_rest_route( 'mts/v1', '/atelier/extract', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mts_rest_atelier_extract',
		'permission_callback' => 'mts_rest_can_use_atelier',
		'args'                => array(
			'source'         => array( 'required' => true, 'type' => 'string' ),
			'target'         => array( 'required' => true, 'type' => 'string' ),
			'translation_id' => array( 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ),
			'text_id'        => array( 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ),
			'target_lang'    => array( 'type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
		),
	) );

	register_rest_route( 'mts/v1', '/atelier/jobs/(?P<id>[a-f0-9\-]{36})', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'mts_rest_atelier_job',
		'permission_callback' => 'mts_rest_can_use_atelier',
	) );

	register_rest_route( 'mts/v1', '/atelier/candidates/(?P<id>\d+)/action', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'mts_rest_atelier_candidate_action',
		'permission_callback' => 'mts_rest_can_use_atelier',
		'args'                => array(
			'action'     => array( 'required' => true, 'type' => 'string', 'enum' => array( 'approve_link', 'create_term', 'reject', 'merge' ) ),
			'central_id' => array( 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ),
		),
	) );
} );

function mts_rest_atelier_extract( WP_REST_Request $request ) {
	if ( ! mts_atelier_is_configured() ) {
		return new WP_Error( 'mts_not_configured', __( 'The extraction engine is not configured (ANTHROPIC_API_KEY missing).', 'mts-network' ), array( 'status' => 409 ) );
	}
	$source = sanitize_textarea_field( (string) $request['source'] );
	$target = sanitize_textarea_field( (string) $request['target'] );
	if ( '' === trim( $source ) || '' === trim( $target ) ) {
		return new WP_Error( 'mts_empty_input', __( 'Source and translation are both required.', 'mts-network' ), array( 'status' => 400 ) );
	}
	$job_id = mts_atelier_create_job( $source, $target, array(
		'translation_id' => (int) $request['translation_id'],
		'text_id'        => (int) $request['text_id'],
		'target_lang'    => $request['target_lang'] ? $request['target_lang'] : (string) get_option( 'mts_default_language', 'en' ),
	) );
	return rest_ensure_response( array( 'job_id' => $job_id ) );
}

function mts_rest_atelier_job( WP_REST_Request $request ) {
	global $wpdb;
	$job = mts_atelier_get_job( $request['id'] );
	if ( ! $job ) {
		return new WP_Error( 'mts_not_found', __( 'Unknown job.', 'mts-network' ), array( 'status' => 404 ) );
	}
	unset( $job['chunks'] ); // payload lourd, inutile côté client.
	$job['candidates_rows'] = $wpdb->get_results( $wpdb->prepare(
		"SELECT id, tibetan, wylie, target, target_lang, context_source, context_target, confidence, source_anchor, central_match, match_type, status
		 FROM {$wpdb->prefix}mts_atelier_candidates WHERE job_id = %s ORDER BY confidence DESC, id ASC",
		$job['id']
	), ARRAY_A );
	return rest_ensure_response( $job );
}

function mts_rest_atelier_candidate_action( WP_REST_Request $request ) {
	global $wpdb;
	$table     = $wpdb->prefix . 'mts_atelier_candidates';
	$candidate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", (int) $request['id'] ), ARRAY_A );
	if ( ! $candidate ) {
		return new WP_Error( 'mts_not_found', __( 'Unknown candidate.', 'mts-network' ), array( 'status' => 404 ) );
	}
	if ( 'open' !== $candidate['status'] ) {
		return new WP_Error( 'mts_already_done', __( 'Candidate already processed.', 'mts-network' ), array( 'status' => 409 ) );
	}

	$action = (string) $request['action'];
	$job    = mts_atelier_get_job( $candidate['job_id'] );

	if ( 'reject' === $action ) {
		$wpdb->insert( $wpdb->prefix . 'mts_atelier_rejections', array(
			'tibetan'     => $candidate['tibetan'],
			'wylie'       => $candidate['wylie'],
			'target_norm' => mb_strtolower( trim( $candidate['target'] ) ),
			'target_lang' => $candidate['target_lang'],
			'rejected_by' => get_current_user_id(),
			'created_at'  => current_time( 'mysql' ),
		) );
		$wpdb->update( $table, array( 'status' => 'rejected' ), array( 'id' => $candidate['id'] ) );
		return rest_ensure_response( array( 'status' => 'rejected' ) );
	}

	// Résolution du terme central selon l'action.
	$pending_chain = false;
	if ( 'create_term' === $action ) {
		$created = mts_create_pending_tibetan_term( array(
			'tibetan'        => $candidate['tibetan'],
			'wylie'          => $candidate['wylie'],
			'title'          => $candidate['wylie'] ? $candidate['wylie'] : $candidate['tibetan'],
			'context_source' => $candidate['context_source'],
			'job_id'         => $candidate['job_id'],
		) );
		if ( is_wp_error( $created ) ) {
			return $created;
		}
		$central_id    = (int) $created['id'];
		$pending_chain = ! $created['existing'];
		$new_status    = 'proposed';
	} else { // approve_link | merge
		$central_id = ( 'merge' === $action ) ? (int) $request['central_id'] : (int) $candidate['central_match'];
		if ( ! $central_id ) {
			return new WP_Error( 'mts_no_central', __( 'No central term to link to (use create_term or merge with central_id).', 'mts-network' ), array( 'status' => 400 ) );
		}
		$new_status = 'linked';
	}

	// Terme local : réutilisé s'il existe (même central + même intitulé).
	$existing_terms = get_posts( array(
		'post_type'      => 'translated_term',
		'post_status'    => array( 'publish', 'pending' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'title'          => $candidate['target'],
		'meta_query'     => array( array( 'key' => 'central_tibetan_term_id', 'value' => $central_id ) ),
	) );
	if ( $existing_terms ) {
		$term_id = (int) $existing_terms[0];
	} else {
		$term_id = wp_insert_post( array(
			'post_type'   => 'translated_term',
			'post_status' => $pending_chain ? 'pending' : 'publish',
			'post_title'  => $candidate['target'],
		), true );
		if ( is_wp_error( $term_id ) ) {
			return $term_id;
		}
		update_post_meta( $term_id, 'central_tibetan_term_id', $central_id );
		wp_set_object_terms( $term_id, $candidate['target_lang'], 'mts_language' );
	}

	// Usage documenté, contexte capturé automatiquement.
	$usage_id = wp_insert_post( array(
		'post_type'   => 'term_usage',
		'post_status' => $pending_chain ? 'pending' : 'publish',
		'post_title'  => $candidate['target'],
	), true );
	if ( is_wp_error( $usage_id ) ) {
		return $usage_id;
	}
	update_post_meta( $usage_id, 'translated_term_id', $term_id );
	update_post_meta( $usage_id, 'translation_id', $job ? (int) $job['translation_id'] : 0 );
	update_post_meta( $usage_id, 'context_source', $candidate['context_source'] );
	update_post_meta( $usage_id, 'context_target', $candidate['context_target'] );
	update_post_meta( $usage_id, 'confidence', (float) $candidate['confidence'] );
	update_post_meta( $usage_id, 'created_via', 'atelier' );
	update_post_meta( $usage_id, 'source_anchor', (bool) $candidate['source_anchor'] );
	wp_update_post( array( 'ID' => $usage_id ) ); // titre.

	$wpdb->update( $table, array( 'status' => $new_status ), array( 'id' => $candidate['id'] ) );

	return rest_ensure_response( array(
		'status'             => $new_status,
		'central_id'         => $central_id,
		'translated_term_id' => $term_id,
		'term_usage_id'      => (int) $usage_id,
	) );
}
