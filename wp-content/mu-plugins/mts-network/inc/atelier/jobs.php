<?php
/**
 * Atelier — jobs d'extraction asynchrones. Action Scheduler (livré avec
 * Woo, actif sur les branches) avec repli WP-Cron. Les chunks d'un job
 * s'enchaînent séquentiellement dans une même action tant que < 50 s
 * (préservation du cache prompt, TTL 5 min — spec llm-extraction §5).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mts_atelier_job_key( $job_id ) {
	return 'mts_atelier_job_' . sanitize_key( $job_id );
}

function mts_atelier_get_job( $job_id ) {
	return get_option( mts_atelier_job_key( $job_id ), null );
}

function mts_atelier_save_job( $job ) {
	update_option( mts_atelier_job_key( $job['id'] ), $job, false );
}

/**
 * Crée un job + planifie son exécution. Retourne l'ID de job.
 */
function mts_atelier_create_job( $source, $target, $args = array() ) {
	$job_id = wp_generate_uuid4();
	$chunks = mts_atelier_chunk_pair( $source, $target );
	$job    = array(
		'id'             => $job_id,
		'status'         => 'queued',
		'chunks_total'   => count( $chunks ),
		'chunks_done'    => 0,
		'candidates'     => 0,
		'errors'         => array(),
		'usage'          => array( 'input_tokens' => 0, 'output_tokens' => 0, 'cache_read_input_tokens' => 0 ),
		'chunks'         => $chunks,
		'target_lang'    => isset( $args['target_lang'] ) ? $args['target_lang'] : (string) get_option( 'mts_default_language', 'en' ),
		'translation_id' => isset( $args['translation_id'] ) ? (int) $args['translation_id'] : 0,
		'text_id'        => isset( $args['text_id'] ) ? (int) $args['text_id'] : 0,
		'user_id'        => get_current_user_id(),
		'created_at'     => current_time( 'mysql' ),
	);
	mts_atelier_save_job( $job );

	if ( function_exists( 'as_enqueue_async_action' ) ) {
		as_enqueue_async_action( 'mts_atelier_run_job', array( $job_id ), 'mts-atelier' );
	} else {
		wp_schedule_single_event( time() + 1, 'mts_atelier_run_job', array( $job_id ) );
	}
	return $job_id;
}

add_action( 'mts_atelier_run_job', 'mts_atelier_run_job' );

function mts_atelier_run_job( $job_id ) {
	$job = mts_atelier_get_job( $job_id );
	if ( ! $job || in_array( $job['status'], array( 'done', 'failed' ), true ) ) {
		return;
	}
	$job['status'] = 'running';
	mts_atelier_save_job( $job );

	$glossary = mts_atelier_glossary_block();
	$start    = time();

	while ( $job['chunks_done'] < $job['chunks_total'] ) {
		$index  = (int) $job['chunks_done'];
		$chunk  = $job['chunks'][ $index ];
		$result = mts_atelier_extract_chunk( $chunk, $glossary, $job['target_lang'] );

		if ( is_wp_error( $result ) ) {
			if ( 'mts_api_retryable' === $result->get_error_code() && count( $job['errors'] ) < 3 ) {
				$job['errors'][] = $result->get_error_message();
				mts_atelier_save_job( $job );
				sleep( 2 );
				continue;
			}
			$job['status']   = 'failed';
			$job['errors'][] = $result->get_error_code() . ': ' . $result->get_error_message();
			mts_atelier_save_job( $job );
			return;
		}

		$job['candidates'] += mts_atelier_ingest_pairs( $job_id, $result['pairs'], $chunk['source'], $job['target_lang'] );
		foreach ( array( 'input_tokens', 'output_tokens', 'cache_read_input_tokens' ) as $key ) {
			if ( isset( $result['usage'][ $key ] ) ) {
				$job['usage'][ $key ] += (int) $result['usage'][ $key ];
			}
		}
		$job['chunks_done']++;
		mts_atelier_save_job( $job );

		// Ré-enqueue au-delà de ~50 s pour rester sous les timeouts.
		if ( time() - $start > 50 && $job['chunks_done'] < $job['chunks_total'] ) {
			if ( function_exists( 'as_enqueue_async_action' ) ) {
				as_enqueue_async_action( 'mts_atelier_run_job', array( $job_id ), 'mts-atelier' );
			} else {
				wp_schedule_single_event( time() + 1, 'mts_atelier_run_job', array( $job_id ) );
			}
			return;
		}
	}

	$job['status'] = 'done';
	mts_atelier_save_job( $job );
}

/**
 * Réconciliation quotidienne : publie les term_usage pending dont le terme
 * central proposé a été publié sur le hub (spec atelier §4).
 */
add_action( 'init', function () {
	if ( ! is_main_site() && ! wp_next_scheduled( 'mts_atelier_reconcile' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'mts_atelier_reconcile' );
	}
}, 40 );

add_action( 'mts_atelier_reconcile', function () {
	$pending = get_posts( array( 'post_type' => 'term_usage', 'post_status' => 'pending', 'posts_per_page' => 100, 'meta_query' => array( array( 'key' => 'created_via', 'value' => 'atelier' ) ) ) );
	foreach ( $pending as $usage ) {
		$term_id    = (int) get_post_meta( $usage->ID, 'translated_term_id', true );
		$central_id = (int) get_post_meta( $term_id, 'central_tibetan_term_id', true );
		if ( ! $central_id ) {
			continue;
		}
		$central = mts_get_tibetan_term( $central_id ); // publish only (resolver).
		if ( $central ) {
			wp_update_post( array( 'ID' => $usage->ID, 'post_status' => 'publish' ) );
			if ( 'pending' === get_post_status( $term_id ) ) {
				wp_update_post( array( 'ID' => $term_id, 'post_status' => 'publish' ) );
			}
		}
	}
} );
