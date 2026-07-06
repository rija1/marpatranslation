<?php
/**
 * Extraction "flat" des posts centraux — TOUJOURS appelée à l'intérieur
 * d'un switch_to_blog( MTS_MAIN_SITE_ID ) : permaliens, médias et champs
 * Pods sont résolus dans le contexte du hub, le résultat est un tableau
 * de scalaires (jamais de WP_Post cross-site).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lit un champ Pods si Pods est actif (hub), sinon post meta brut.
 */
function mts_field_raw( $post_id, $field ) {
	if ( function_exists( 'pods_field' ) ) {
		$value = pods_field( $field, $post_id );
		if ( null !== $value && false !== $value ) {
			return $value;
		}
	}
	return get_post_meta( $post_id, $field, true );
}

/**
 * Normalise une valeur de relation Pods en liste d'IDs.
 */
function mts_relation_ids( $value ) {
	$ids = array();
	foreach ( (array) $value as $item ) {
		if ( is_numeric( $item ) ) {
			$ids[] = (int) $item;
		} elseif ( is_array( $item ) && isset( $item['ID'] ) ) {
			$ids[] = (int) $item['ID'];
		} elseif ( $item instanceof WP_Post ) {
			$ids[] = (int) $item->ID;
		}
	}
	return array_values( array_unique( array_filter( $ids ) ) );
}

function mts_extract_tibetan_term( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	return array(
		'id'            => (int) $post->ID,
		'title'         => $post->post_title,
		'slug'          => $post->post_name,
		'status'        => $post->post_status,
		'tibetan'       => (string) mts_field_raw( $post->ID, 'tibetan_term' ),
		'wylie'         => (string) mts_field_raw( $post->ID, 'wylie' ),
		'sanskrit'      => (string) mts_field_raw( $post->ID, 'sanskrit' ),
		'definition'    => $post->post_content,
		'permalink_hub' => get_permalink( $post ),
	);
}

function mts_extract_text( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	$author_ids   = mts_relation_ids( mts_field_raw( $post->ID, 'text_author' ) );
	$author_names = array();
	foreach ( $author_ids as $author_id ) {
		$author = get_post( $author_id );
		if ( $author ) {
			$author_names[] = $author->post_title;
		}
	}
	return array(
		'id'            => (int) $post->ID,
		'title'         => $post->post_title,
		'slug'          => $post->post_name,
		'status'        => $post->post_status,
		'author_ids'    => $author_ids,
		'author_names'  => $author_names,
		'has_source'    => '' !== trim( $post->post_content ),
		'permalink_hub' => get_permalink( $post ),
	);
}

function mts_extract_translator( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	return array(
		'id'            => (int) $post->ID,
		'name'          => $post->post_title,
		'slug'          => $post->post_name,
		'status'        => $post->post_status,
		'bio_excerpt'   => wp_trim_words( wp_strip_all_tags( $post->post_content ), 40 ),
		'photo_url'     => (string) get_the_post_thumbnail_url( $post, 'medium' ),
		'permalink_hub' => get_permalink( $post ),
	);
}
