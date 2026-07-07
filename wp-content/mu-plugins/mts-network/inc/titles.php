<?php
/**
 * Titres dynamiques des term_usage de BRANCHE.
 *
 * Format historique du site (functions.php du thème, conservé sur le hub
 * jusqu'à la Phase 3) : "[TRANSLATED_TERM] used by [TRANSLATOR(S)] in
 * [TRANSLATION]", fallback "Term Usage Entry". Ici, les traducteurs sont
 * résolus via le référentiel central (meta central_translator_ids de la
 * translation locale).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'save_post_term_usage', 'mts_update_term_usage_title', 999, 2 );

function mts_update_term_usage_title( $post_id, $post ) {
	if ( is_main_site() ) {
		return; // Le hub garde sa logique historique jusqu'à la Phase 3.
	}
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$title = mts_generate_term_usage_title( $post_id );

	if ( '' !== $title && $title !== $post->post_title ) {
		remove_action( 'save_post_term_usage', 'mts_update_term_usage_title', 999 );
		wp_update_post( array(
			'ID'         => $post_id,
			'post_title' => $title,
			'post_name'  => sanitize_title( $title ),
		) );
		add_action( 'save_post_term_usage', 'mts_update_term_usage_title', 999, 2 );
	}
}

function mts_generate_term_usage_title( $post_id ) {
	$parts = array();

	$term_id = (int) get_post_meta( $post_id, 'translated_term_id', true );
	if ( $term_id ) {
		$term = get_post( $term_id );
		if ( $term && 'translated_term' === $term->post_type ) {
			$parts[] = $term->post_title;
		}
	}

	$translation_id    = (int) get_post_meta( $post_id, 'translation_id', true );
	$translation_title = '';
	$translator_names  = array();

	if ( $translation_id ) {
		$translation = get_post( $translation_id );
		// D15 : sur les branches, le porteur de la traduction est le produit
		// Woo (modèle historique conservé) ; le CPT translation reste accepté.
		if ( $translation && in_array( $translation->post_type, array( 'translation', 'product' ), true ) ) {
			$translation_title = $translation->post_title;

			$central_ids = json_decode( (string) get_post_meta( $translation_id, 'central_translator_ids', true ), true );
			foreach ( (array) $central_ids as $central_id ) {
				$translator = mts_get_translator( (int) $central_id );
				if ( $translator ) {
					$translator_names[] = $translator['name'];
				}
			}
		}
	}

	if ( $translator_names ) {
		$parts[] = 'used by ' . implode( ', ', $translator_names );
	}
	if ( '' !== $translation_title ) {
		$parts[] = 'in ' . $translation_title;
	}

	return $parts ? implode( ' ', $parts ) : __( 'Term Usage Entry', 'mts-network' );
}
