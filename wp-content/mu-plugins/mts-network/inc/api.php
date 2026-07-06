<?php
/**
 * API helper cross-site — LE SEUL endroit du réseau autorisé à appeler
 * switch_to_blog() (convention §13 du doc maître). Lecture : tableaux
 * "flat" cachés (transients réseau). Écriture : créations `pending`
 * contrôlées sur le hub (les capabilities sont vérifiées par l'appelant,
 * couche REST ou admin).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sites de branche (tous sauf le hub), en objets WP_Site.
 */
function mts_get_branch_sites() {
	return get_sites( array(
		'site__not_in' => array( MTS_MAIN_SITE_ID ),
		'number'       => 100,
	) );
}

/**
 * Slug de branche = premier label du domaine (eu, hk, np).
 */
function mts_branch_slug_from_site( $site ) {
	$label = strtok( $site->domain, '.' );
	return $label ? $label : (string) $site->blog_id;
}

/**
 * blog_id d'une branche par slug ('eu' → 2), 0 si introuvable.
 */
function mts_get_branch_blog_id( $branch ) {
	foreach ( mts_get_branch_sites() as $site ) {
		if ( mts_branch_slug_from_site( $site ) === $branch ) {
			return (int) $site->blog_id;
		}
	}
	return 0;
}

/**
 * Exécute $callback dans le contexte du hub, avec restore garanti.
 */
function mts_with_hub( callable $callback ) {
	$switched = ( get_current_blog_id() !== MTS_MAIN_SITE_ID );
	if ( $switched ) {
		switch_to_blog( MTS_MAIN_SITE_ID );
	}
	try {
		return $callback();
	} finally {
		if ( $switched ) {
			restore_current_blog();
		}
	}
}

/**
 * Résout un post central par ID ou slug pour un post type donné.
 * À appeler dans le contexte du hub.
 */
function mts_resolve_central_post( $id_or_slug, $post_type ) {
	if ( is_numeric( $id_or_slug ) ) {
		$post = get_post( (int) $id_or_slug );
		// publish uniquement : ces lectures alimentent un cache réseau
		// partagé et des sorties publiques (audit Phase 2, constat critique).
		return ( $post && $post->post_type === $post_type && 'publish' === $post->post_status ) ? $post : null;
	}
	$found = get_posts( array(
		'post_type'      => $post_type,
		'name'           => sanitize_title( $id_or_slug ),
		'post_status'    => 'publish',
		'posts_per_page' => 1,
	) );
	return $found ? $found[0] : null;
}

// -------------------------------------------------------------------------
// Lectures centrales
// -------------------------------------------------------------------------

function mts_get_tibetan_term( $id_or_slug ) {
	$key    = mts_cache_key( 'tibetan_term', array( $id_or_slug ) );
	$cached = mts_cache_get( $key );
	if ( false !== $cached ) {
		return $cached;
	}
	$result = mts_with_hub( function () use ( $id_or_slug ) {
		$post = mts_resolve_central_post( $id_or_slug, 'tibetan_term' );
		return $post ? mts_extract_tibetan_term( $post ) : null;
	} );
	mts_cache_set( $key, $result );
	return $result;
}

/**
 * Recherche de termes tibétains : titre (LIKE via s) + meta tibetan_term.
 * Remplace l'AJAX search historique du thème (search_tibetan_terms).
 *
 * @return array[] Extraits légers : id, title, tibetan, wylie.
 */
function mts_search_tibetan_terms( $q, $args = array() ) {
	$args = wp_parse_args( $args, array(
		'limit'  => 20,
		'offset' => 0,
	) );
	$q    = trim( (string) $q );
	if ( '' === $q ) {
		return array();
	}

	$key    = mts_cache_key( 'term_search', array( $q, $args ) );
	$cached = mts_cache_get( $key );
	if ( false !== $cached ) {
		return $cached;
	}

	$result = mts_with_hub( function () use ( $q, $args ) {
		$by_title = get_posts( array(
			'post_type'      => 'tibetan_term',
			'post_status'    => 'publish',
			's'              => $q,
			'posts_per_page' => (int) $args['limit'],
			'offset'         => (int) $args['offset'],
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );
		$by_meta = get_posts( array(
			'post_type'      => 'tibetan_term',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $args['limit'],
			'meta_query'     => array(
				array(
					'key'     => 'tibetan_term',
					'value'   => $q,
					'compare' => 'LIKE',
				),
			),
		) );

		$out  = array();
		$seen = array();
		foreach ( array_merge( $by_title, $by_meta ) as $post ) {
			if ( isset( $seen[ $post->ID ] ) ) {
				continue;
			}
			$seen[ $post->ID ] = true;
			$extract           = mts_extract_tibetan_term( $post );
			$out[]             = array(
				'id'      => $extract['id'],
				'title'   => $extract['title'],
				'tibetan' => $extract['tibetan'],
				'wylie'   => $extract['wylie'],
			);
			if ( count( $out ) >= (int) $args['limit'] ) {
				break;
			}
		}
		return $out;
	} );

	mts_cache_set( $key, $result );
	return $result;
}

function mts_get_text( $id_or_slug ) {
	$key    = mts_cache_key( 'text', array( $id_or_slug ) );
	$cached = mts_cache_get( $key );
	if ( false !== $cached ) {
		return $cached;
	}
	$result = mts_with_hub( function () use ( $id_or_slug ) {
		$post = mts_resolve_central_post( $id_or_slug, 'text' );
		return $post ? mts_extract_text( $post ) : null;
	} );
	mts_cache_set( $key, $result );
	return $result;
}

function mts_get_texts( $args = array() ) {
	$args   = wp_parse_args( $args, array( 'limit' => -1 ) );
	$key    = mts_cache_key( 'texts', array( $args ) );
	$cached = mts_cache_get( $key );
	if ( false !== $cached ) {
		return $cached;
	}
	$result = mts_with_hub( function () use ( $args ) {
		$posts = get_posts( array(
			'post_type'      => 'text',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $args['limit'],
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );
		return array_values( array_filter( array_map( 'mts_extract_text', $posts ) ) );
	} );
	mts_cache_set( $key, $result );
	return $result;
}

/**
 * Contenu source tibétain d'un text (post_content du hub), caché par texte.
 */
function mts_get_text_source( $id ) {
	$key    = mts_cache_key( 'text_source', array( (int) $id ) );
	$cached = mts_cache_get( $key );
	if ( false !== $cached ) {
		return $cached;
	}
	$result = mts_with_hub( function () use ( $id ) {
		$post = get_post( (int) $id );
		return ( $post && 'text' === $post->post_type ) ? $post->post_content : '';
	} );
	mts_cache_set( $key, $result );
	return $result;
}

function mts_get_translator( $id ) {
	$key    = mts_cache_key( 'translator', array( (int) $id ) );
	$cached = mts_cache_get( $key );
	if ( false !== $cached ) {
		return $cached;
	}
	$result = mts_with_hub( function () use ( $id ) {
		$post = get_post( (int) $id );
		return ( $post && 'translator' === $post->post_type ) ? mts_extract_translator( $post ) : null;
	} );
	mts_cache_set( $key, $result );
	return $result;
}

function mts_get_translators( $args = array() ) {
	$args   = wp_parse_args( $args, array( 'limit' => -1 ) );
	$key    = mts_cache_key( 'translators', array( $args ) );
	$cached = mts_cache_get( $key );
	if ( false !== $cached ) {
		return $cached;
	}
	$result = mts_with_hub( function () use ( $args ) {
		$posts = get_posts( array(
			'post_type'      => 'translator',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $args['limit'],
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );
		return array_values( array_filter( array_map( 'mts_extract_translator', $posts ) ) );
	} );
	mts_cache_set( $key, $result );
	return $result;
}

// -------------------------------------------------------------------------
// Agrégation réseau
// -------------------------------------------------------------------------

/**
 * Équivalents d'un terme tibétain dans toutes les branches.
 *
 * @return array ex. [ 'eu' => [ [ 'translated_term_id', 'term', 'lang', 'usages_count' ], … ], 'hk' => [], … ]
 */
function mts_get_term_translations( $tibetan_id ) {
	$tibetan_id = (int) $tibetan_id;
	$key        = mts_cache_key( 'term_translations', array( $tibetan_id ) );
	$cached     = mts_cache_get( $key );
	if ( false !== $cached ) {
		return $cached;
	}

	$result = array();
	foreach ( mts_get_branch_sites() as $site ) {
		$branch            = mts_branch_slug_from_site( $site );
		$result[ $branch ] = array();

		switch_to_blog( (int) $site->blog_id );
		try {
			$terms = get_posts( array(
				'post_type'      => 'translated_term',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'meta_query'     => array(
					array(
						'key'   => 'central_tibetan_term_id',
						'value' => $tibetan_id,
					),
				),
			) );

			foreach ( $terms as $term_post ) {
				$langs  = wp_get_post_terms( $term_post->ID, 'mts_language', array( 'fields' => 'slugs' ) );
				$usages = get_posts( array(
					'post_type'      => 'term_usage',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'   => 'translated_term_id',
							'value' => $term_post->ID,
						),
					),
				) );

				$result[ $branch ][] = array(
					'translated_term_id' => (int) $term_post->ID,
					'term'               => $term_post->post_title,
					'lang'               => ( ! is_wp_error( $langs ) && $langs ) ? $langs[0] : '',
					'usages_count'       => count( $usages ),
					'blog_id'            => (int) $site->blog_id,
				);
			}
		} finally {
			restore_current_blog();
		}
	}

	mts_cache_set( $key, $result );
	return $result;
}

/**
 * URL du produit d'une branche pour une référence livre commune (mts_book_ref).
 */
function mts_get_book_shop_url( $book_ref, $branch ) {
	$book_ref = sanitize_text_field( (string) $book_ref );
	$blog_id  = mts_get_branch_blog_id( $branch );
	if ( ! $blog_id || '' === $book_ref ) {
		return '';
	}

	$key    = mts_cache_key( 'book_shop_url', array( $book_ref, $branch ) );
	$cached = mts_cache_get( $key );
	if ( false !== $cached ) {
		return $cached;
	}

	switch_to_blog( $blog_id );
	try {
		$products = get_posts( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => 'mts_book_ref',
					'value' => $book_ref,
				),
			),
		) );
		$url = $products ? get_permalink( $products[0] ) : '';
	} finally {
		restore_current_blog();
	}

	mts_cache_set( $key, $url );
	return $url;
}

// -------------------------------------------------------------------------
// Écritures contrôlées (créations `pending` sur le hub)
// -------------------------------------------------------------------------

/**
 * Propose un tibetan_term central (post_status pending + provenance).
 * Si un terme publié porte déjà exactement la même écriture tibétaine,
 * retourne son ID avec 'existing' => true au lieu de créer un doublon.
 *
 * @param array $data { tibetan (requis), title, wylie, definition, context_source, job_id }
 * @return array|WP_Error { id, existing }
 */
function mts_create_pending_tibetan_term( $data ) {
	$tibetan = mts_normalize_tibetan( isset( $data['tibetan'] ) ? $data['tibetan'] : '' );
	if ( '' === $tibetan ) {
		return new WP_Error( 'mts_missing_tibetan', __( 'Tibetan script is required.', 'mts-network' ) );
	}

	$origin_blog = get_current_blog_id();
	$user_id     = get_current_user_id();

	return mts_with_hub( function () use ( $data, $tibetan, $origin_blog, $user_id ) {

		$existing = get_posts( array(
			'post_type'      => 'tibetan_term',
			'post_status'    => array( 'publish', 'pending' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => 'tibetan_term',
					'value' => $tibetan,
				),
			),
		) );
		if ( $existing ) {
			return array(
				'id'       => (int) $existing[0],
				'existing' => true,
			);
		}

		$title = ! empty( $data['title'] ) ? sanitize_text_field( $data['title'] )
			: ( ! empty( $data['wylie'] ) ? sanitize_text_field( $data['wylie'] ) : $tibetan );

		$post_id = wp_insert_post( array(
			'post_type'    => 'tibetan_term',
			'post_status'  => 'pending',
			'post_title'   => $title,
			'post_content' => ! empty( $data['definition'] ) ? wp_kses_post( $data['definition'] ) : '',
		), true );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		update_post_meta( $post_id, 'tibetan_term', $tibetan );
		if ( ! empty( $data['wylie'] ) ) {
			update_post_meta( $post_id, 'wylie', sanitize_text_field( $data['wylie'] ) );
		}
		if ( ! empty( $data['context_source'] ) ) {
			update_post_meta( $post_id, 'context_source', sanitize_textarea_field( $data['context_source'] ) );
		}
		if ( ! empty( $data['job_id'] ) ) {
			update_post_meta( $post_id, 'atelier_job_id', sanitize_text_field( $data['job_id'] ) );
		}
		update_post_meta( $post_id, 'proposed_from_blog_id', (int) $origin_blog );
		update_post_meta( $post_id, 'proposed_by_user', (int) $user_id );

		return array(
			'id'       => (int) $post_id,
			'existing' => false,
		);
	} );
}

/**
 * Propose un text central (pending + provenance).
 *
 * @param array $data { title (requis), source, author_name, job_id }
 * @return array|WP_Error { id, existing }
 */
function mts_create_pending_text( $data ) {
	$title = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
	if ( '' === $title ) {
		return new WP_Error( 'mts_missing_title', __( 'Text title is required.', 'mts-network' ) );
	}

	$origin_blog = get_current_blog_id();
	$user_id     = get_current_user_id();

	return mts_with_hub( function () use ( $data, $title, $origin_blog, $user_id ) {

		$existing = get_posts( array(
			'post_type'      => 'text',
			'post_status'    => array( 'publish', 'pending' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'title'          => $title,
		) );
		if ( $existing ) {
			return array(
				'id'       => (int) $existing[0],
				'existing' => true,
			);
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'text',
			'post_status'  => 'pending',
			'post_title'   => $title,
			'post_content' => ! empty( $data['source'] ) ? sanitize_textarea_field( $data['source'] ) : '',
		), true );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		if ( ! empty( $data['author_name'] ) ) {
			update_post_meta( $post_id, 'proposed_author_name', sanitize_text_field( $data['author_name'] ) );
		}
		if ( ! empty( $data['job_id'] ) ) {
			update_post_meta( $post_id, 'atelier_job_id', sanitize_text_field( $data['job_id'] ) );
		}
		update_post_meta( $post_id, 'proposed_from_blog_id', (int) $origin_blog );
		update_post_meta( $post_id, 'proposed_by_user', (int) $user_id );

		return array(
			'id'       => (int) $post_id,
			'existing' => false,
		);
	} );
}

/**
 * Normalisation tibétaine : Unicode NFC + trim + tsheg final retiré
 * (règle §5 de la spec Atelier — appliquée dès l'ingestion).
 */
function mts_normalize_tibetan( $value ) {
	// Entrée potentiellement REST : balises/octets invalides retirés avant
	// normalisation (Unicode intact, y compris tibétain).
	$value = sanitize_text_field( (string) $value );
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}
	if ( class_exists( 'Normalizer' ) ) {
		$normalized = Normalizer::normalize( $value, Normalizer::FORM_C );
		if ( false !== $normalized ) {
			$value = $normalized;
		}
	}
	// Tsheg (U+0F0B) et espaces en fin de chaîne.
	return rtrim( $value, "\u{0F0B} \t\n\r" );
}
