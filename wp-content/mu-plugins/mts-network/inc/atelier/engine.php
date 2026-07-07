<?php
/**
 * Atelier — moteur d'extraction (spec llm-extraction.md).
 *
 * Client HTTP direct de l'API Claude (fallback documenté de la spec §1 ;
 * migration vers le SDK PHP officiel possible sans toucher au contrat).
 * Modèle : MTS_LLM_MODEL (défaut claude-opus-4-8). Clé : ANTHROPIC_API_KEY
 * (wp-config.php, jamais en base). Mode mock : filtre
 * `mts_atelier_mock_pairs` (tests E2E sans clé ni coût).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mts_atelier_is_configured() {
	return ( defined( 'ANTHROPIC_API_KEY' ) && '' !== ANTHROPIC_API_KEY ) || has_filter( 'mts_atelier_mock_pairs' );
}

/**
 * Découpe grossière en paires de chunks (~$size caractères source),
 * frontières sur shad (།) côté tibétain, proportionnel côté cible (spec §2).
 */
function mts_atelier_chunk_pair( $source, $target, $size = 2400 ) {
	$source = trim( (string) $source );
	$target = trim( (string) $target );
	if ( mb_strlen( $source ) <= $size ) {
		return array( array( 'source' => $source, 'target' => $target ) );
	}
	$parts  = preg_split( '/(?<=།)/u', $source );
	$chunks = array();
	$buffer = '';
	foreach ( $parts as $part ) {
		$buffer .= $part;
		if ( mb_strlen( $buffer ) >= $size ) {
			$chunks[] = $buffer;
			$buffer   = '';
		}
	}
	if ( '' !== trim( $buffer ) ) {
		$chunks[] = $buffer;
	}
	$out          = array();
	$total_source = max( 1, mb_strlen( $source ) );
	$cursor       = 0;
	foreach ( $chunks as $chunk ) {
		$ratio  = mb_strlen( $chunk ) / $total_source;
		$length = (int) ceil( $ratio * mb_strlen( $target ) );
		$out[]  = array(
			'source' => $chunk,
			'target' => mb_substr( $target, $cursor, $length ),
		);
		$cursor += $length;
	}
	return $out;
}

/**
 * Extrait du glossaire de branche injecté (préfixe stable → prompt caching).
 */
function mts_atelier_glossary_block() {
	$lines = array();
	foreach ( get_posts( array( 'post_type' => 'translated_term', 'post_status' => 'publish', 'posts_per_page' => 200 ) ) as $term ) {
		$central_id = (int) get_post_meta( $term->ID, 'central_tibetan_term_id', true );
		$central    = $central_id ? mts_get_tibetan_term( $central_id ) : null;
		if ( $central ) {
			$lines[] = ( $central['wylie'] ? $central['wylie'] . ' | ' : '' ) . $central['tibetan'] . ' => ' . $term->post_title;
		}
	}
	return "KNOWN GLOSSARY (existing MTS conventions):\n" . implode( "\n", $lines );
}

function mts_atelier_system_instructions( $target_lang ) {
	return 'You are an expert terminologist in Tibetan Buddhist translation. You are given a Tibetan passage and its translation into ' . $target_lang . '. Extract ONLY terminological correspondences actually present in both passages: technical Dharma terms, proper names, philosophical terms. Ignore grammatical words and non-terminological common vocabulary. Never invent a correspondence: the Tibetan term must appear in the source passage AND its rendering in the target passage. Normalize Tibetan to Unicode NFC without trailing tsheg; provide the Wylie transliteration. For each pair, give the source sentence and target sentence containing the terms, and a confidence: 1.0 = certain and unambiguous in this context; 0.7 = probable; 0.4 = possible but ambiguous. If the provided glossary already contains the Tibetan term, focus on how THIS text renders it (even if different from the glossary).';
}

function mts_atelier_output_schema() {
	return array(
		'type'                 => 'object',
		'properties'           => array(
			'pairs' => array(
				'type'  => 'array',
				'items' => array(
					'type'                 => 'object',
					'properties'           => array(
						'tibetan'        => array( 'type' => 'string' ),
						'wylie'          => array( 'type' => 'string' ),
						'target'         => array( 'type' => 'string' ),
						'target_lang'    => array( 'type' => 'string' ),
						'context_source' => array( 'type' => 'string' ),
						'context_target' => array( 'type' => 'string' ),
						'confidence'     => array( 'type' => 'number' ),
						'notes'          => array( 'type' => 'string' ),
					),
					'required'             => array( 'tibetan', 'wylie', 'target', 'target_lang', 'context_source', 'context_target', 'confidence', 'notes' ),
					'additionalProperties' => false,
				),
			),
		),
		'required'             => array( 'pairs' ),
		'additionalProperties' => false,
	);
}

/**
 * Appelle l'API pour UN chunk. Retourne array{pairs:array,usage:array}|WP_Error.
 */
function mts_atelier_extract_chunk( $chunk, $glossary_block, $target_lang ) {
	// Mode mock (tests E2E sans clé) : le filtre fournit les paires.
	$mock = apply_filters( 'mts_atelier_mock_pairs', null, $chunk, $target_lang );
	if ( null !== $mock ) {
		return array( 'pairs' => (array) $mock, 'usage' => array( 'mock' => true ) );
	}

	if ( ! defined( 'ANTHROPIC_API_KEY' ) || '' === ANTHROPIC_API_KEY ) {
		return new WP_Error( 'mts_no_api_key', __( 'ANTHROPIC_API_KEY is not configured in wp-config.php.', 'mts-network' ) );
	}

	$body = array(
		'model'         => MTS_LLM_MODEL,
		'max_tokens'    => 4096,
		'system'        => array(
			array( 'type' => 'text', 'text' => mts_atelier_system_instructions( $target_lang ) ),
			array(
				'type'          => 'text',
				'text'          => $glossary_block,
				// Préfixe stable → cache (~0,1× dès le 2e chunk, spec §5).
				'cache_control' => array( 'type' => 'ephemeral' ),
			),
		),
		'messages'      => array(
			array(
				'role'    => 'user',
				'content' => "TIBETAN SOURCE:\n" . $chunk['source'] . "\n\nTRANSLATION (" . $target_lang . "):\n" . $chunk['target'],
			),
		),
		'output_config' => array(
			'format' => array(
				'type'   => 'json_schema',
				'schema' => mts_atelier_output_schema(),
			),
		),
	);

	$response = wp_remote_post( 'https://api.anthropic.com/v1/messages', array(
		'timeout' => 120,
		'headers' => array(
			'x-api-key'         => ANTHROPIC_API_KEY,
			'anthropic-version' => '2023-06-01',
			'content-type'      => 'application/json',
		),
		'body'    => wp_json_encode( $body ),
	) );

	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( 429 === $code || $code >= 500 ) {
		return new WP_Error( 'mts_api_retryable', 'HTTP ' . $code );
	}
	if ( 200 !== $code ) {
		return new WP_Error( 'mts_api_error', 'HTTP ' . $code . ' — ' . ( isset( $data['error']['message'] ) ? $data['error']['message'] : '' ) );
	}
	if ( isset( $data['stop_reason'] ) && 'refusal' === $data['stop_reason'] ) {
		return new WP_Error( 'mts_api_refusal', 'refusal' );
	}
	$text = '';
	foreach ( (array) $data['content'] as $block ) {
		if ( isset( $block['type'] ) && 'text' === $block['type'] ) {
			$text .= $block['text'];
		}
	}
	$parsed = json_decode( $text, true );
	if ( ! is_array( $parsed ) || ! isset( $parsed['pairs'] ) ) {
		return new WP_Error( 'mts_api_badjson', __( 'Unparseable extraction output.', 'mts-network' ) );
	}
	return array(
		'pairs' => (array) $parsed['pairs'],
		'usage' => isset( $data['usage'] ) ? $data['usage'] : array(),
	);
}

/**
 * Post-traitement déterministe d'un lot de paires (spec llm-extraction §4)
 * et insertion en table candidats. Retourne le nombre d'insérées.
 */
function mts_atelier_ingest_pairs( $job_id, $pairs, $chunk_source, $target_lang ) {
	global $wpdb;
	$table    = $wpdb->prefix . 'mts_atelier_candidates';
	$inserted = 0;

	foreach ( (array) $pairs as $pair ) {
		$tibetan = mts_normalize_tibetan( isset( $pair['tibetan'] ) ? $pair['tibetan'] : '' );
		$target  = sanitize_text_field( isset( $pair['target'] ) ? $pair['target'] : '' );
		if ( '' === $tibetan || '' === $target ) {
			continue;
		}
		$wylie       = sanitize_text_field( isset( $pair['wylie'] ) ? $pair['wylie'] : '' );
		$confidence  = max( 0, min( 1, (float) ( isset( $pair['confidence'] ) ? $pair['confidence'] : 0 ) ) );
		// Ancrage : essai avec puis sans shad final (le terme apparaît souvent
		// suivi d'une particule — ལམ་དུ — sans son shad de citation).
		$anchor = ( false !== mb_strpos( $chunk_source, $tibetan ) )
			|| ( false !== mb_strpos( $chunk_source, rtrim( $tibetan, '།' ) ) );
		$target_norm = mb_strtolower( trim( $target ) );

		// Filtre des rejets mémorisés + écarté si ni ancré ni confiant.
		$rejected = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}mts_atelier_rejections WHERE tibetan = %s AND target_norm = %s AND target_lang = %s",
			$tibetan, $target_norm, $target_lang
		) );
		if ( $rejected || ( ! $anchor && $confidence < 0.5 ) ) {
			continue;
		}

		// Badge : match exact Unicode → wylie exact → aucun (fuzzy en v2).
		$central_match = 0;
		$match_type    = 'none';
		$found         = mts_with_hub( function () use ( $tibetan, $wylie ) {
			$by_tib = get_posts( array( 'post_type' => 'tibetan_term', 'post_status' => array( 'publish', 'pending' ), 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => 'tibetan_term', 'value' => $tibetan ) ) ) );
			if ( $by_tib ) {
				return array( (int) $by_tib[0], 'exact' );
			}
			if ( '' !== $wylie ) {
				$by_wylie = get_posts( array( 'post_type' => 'tibetan_term', 'post_status' => array( 'publish', 'pending' ), 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => 'wylie', 'value' => $wylie ) ) ) );
				if ( $by_wylie ) {
					return array( (int) $by_wylie[0], 'wylie' );
				}
			}
			return array( 0, 'none' );
		} );
		list( $central_match, $match_type ) = $found;

		// Dédoublonnage intra-job (même tibétain+cible : garder la meilleure).
		$dupe = $wpdb->get_row( $wpdb->prepare(
			"SELECT id, confidence FROM $table WHERE job_id = %s AND tibetan = %s AND LOWER(target) = %s",
			$job_id, $tibetan, $target_norm
		) );
		if ( $dupe ) {
			if ( $confidence > (float) $dupe->confidence ) {
				$wpdb->update( $table, array( 'confidence' => $confidence ), array( 'id' => $dupe->id ) );
			}
			continue;
		}

		$wpdb->insert( $table, array(
			'job_id'         => $job_id,
			'tibetan'        => $tibetan,
			'wylie'          => $wylie,
			'target'         => $target,
			'target_lang'    => $target_lang,
			'context_source' => sanitize_textarea_field( isset( $pair['context_source'] ) ? $pair['context_source'] : '' ),
			'context_target' => sanitize_textarea_field( isset( $pair['context_target'] ) ? $pair['context_target'] : '' ),
			'confidence'     => $confidence,
			'source_anchor'  => $anchor ? 1 : 0,
			'central_match'  => $central_match ? $central_match : null,
			'match_type'     => $match_type,
			'status'         => 'open',
			'created_at'     => current_time( 'mysql' ),
		) );
		$inserted++;
	}
	return $inserted;
}
