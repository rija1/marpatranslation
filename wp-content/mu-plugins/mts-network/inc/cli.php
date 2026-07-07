<?php
/**
 * Commandes WP-CLI de migration Phase 3-4 (runbook
 * doc/plans/mts-network-migration-plan.md). Toujours lancer --dry-run d'abord.
 *
 *   wp mts migrate products    --to=eu [--dry-run]
 *   wp mts migrate terminology --to=eu [--dry-run]
 *   wp mts migrate articles    --to=eu [--dry-run]
 *   wp mts migrate retire      [--dry-run]   (originaux du hub → draft)
 *
 * D15 : le produit Woo est le porteur de la traduction sur les branches.
 * Cartes d'IDs conservées en options réseau (mts_map_products, mts_map_terms,
 * mts_map_usages, mts_map_articles) — commandes idempotentes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

class MTS_Migrate_Command {

	private function target_blog( $assoc ) {
		$branch  = isset( $assoc['to'] ) ? $assoc['to'] : 'eu';
		$blog_id = mts_get_branch_blog_id( $branch );
		if ( ! $blog_id ) {
			WP_CLI::error( "Branche inconnue : $branch" );
		}
		return $blog_id;
	}

	private function map_get( $name ) {
		$map = get_site_option( 'mts_map_' . $name, array() );
		return is_array( $map ) ? $map : array();
	}

	private function map_save( $name, $map ) {
		update_site_option( 'mts_map_' . $name, $map );
	}

	/**
	 * Metas à ne jamais copier telles quelles.
	 */
	private function meta_blacklist() {
		return array( '_edit_lock', '_edit_last', '_thumbnail_id', '_wp_old_slug', 'total_sales' );
	}

	private function gather_all_meta( $post_id ) {
		$out = array();
		foreach ( (array) get_post_meta( $post_id ) as $key => $values ) {
			if ( in_array( $key, $this->meta_blacklist(), true ) ) {
				continue;
			}
			$out[ $key ] = array_map( 'maybe_unserialize', $values );
		}
		return $out;
	}

	private function gather_thumb( $post_id ) {
		$attachment_id = get_post_thumbnail_id( $post_id );
		if ( ! $attachment_id ) {
			return null;
		}
		$file = get_attached_file( $attachment_id );
		return ( $file && file_exists( $file ) ) ? array( 'file' => $file, 'title' => get_the_title( $attachment_id ) ) : null;
	}

	/**
	 * IDs numériques d'une relation Pods stockée en meta (simple ou multiple).
	 */
	private function relation_ids_from_meta( $post_id, $key ) {
		$ids = array();
		foreach ( (array) get_post_meta( $post_id, $key, false ) as $value ) {
			foreach ( (array) maybe_unserialize( $value ) as $item ) {
				if ( is_numeric( $item ) ) {
					$ids[] = (int) $item;
				}
			}
		}
		return array_values( array_unique( array_filter( $ids ) ) );
	}

	/**
	 * slug mts_language depuis le nom d'un terme du hub (taxo legacy).
	 */
	private function language_slug_from_term_id( $term_id ) {
		$known = array( 'english' => 'en', 'french' => 'fr', 'français' => 'fr', 'norwegian' => 'no', 'norsk' => 'no' );
		$term  = get_term( (int) $term_id );
		if ( ! $term || is_wp_error( $term ) ) {
			return 'en';
		}
		$name = strtolower( $term->name );
		return isset( $known[ $name ] ) ? $known[ $name ] : sanitize_title( $name );
	}

	private function ensure_language_term( $slug ) {
		$names = array( 'en' => 'English', 'fr' => 'Français', 'no' => 'Norsk', 'zh-hant' => '繁體中文', 'ne' => 'नेपाली' );
		if ( ! term_exists( $slug, 'mts_language' ) ) {
			wp_insert_term( isset( $names[ $slug ] ) ? $names[ $slug ] : strtoupper( $slug ), 'mts_language', array( 'slug' => $slug ) );
		}
	}

	private function sideload_thumb( $thumb, $new_post_id ) {
		if ( ! $thumb ) {
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$tmp = wp_tempnam( basename( $thumb['file'] ) );
		if ( ! copy( $thumb['file'], $tmp ) ) {
			return;
		}
		$file_array    = array(
			'name'     => basename( $thumb['file'] ),
			'tmp_name' => $tmp,
		);
		$attachment_id = media_handle_sideload( $file_array, $new_post_id, $thumb['title'] );
		if ( ! is_wp_error( $attachment_id ) ) {
			set_post_thumbnail( $new_post_id, $attachment_id );
		} else {
			@unlink( $tmp );
		}
	}

	private function copy_post_fields( $post ) {
		return array(
			'post_title'   => $post->post_title,
			'post_name'    => $post->post_name,
			'post_content' => $post->post_content,
			'post_excerpt' => $post->post_excerpt,
			'post_status'  => 'publish',
			'post_date'    => $post->post_date,
		);
	}

	/**
	 * Produits (= traductions, D15) : hub → branche, avec catégories,
	 * langue, image, et pont central (texte source + traducteurs).
	 *
	 * ## OPTIONS
	 * [--to=<branch>] : défaut eu.  [--dry-run]
	 */
	public function products( $args, $assoc ) {
		$dry     = isset( $assoc['dry-run'] );
		$blog_id = $this->target_blog( $assoc );
		$map     = $this->map_get( 'products' );

		$items = array();
		foreach ( get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $post ) {
			$cats    = wp_get_post_terms( $post->ID, 'product_cat' );
			$items[] = array(
				'hub_id'      => $post->ID,
				'fields'      => $this->copy_post_fields( $post ),
				'meta'        => $this->gather_all_meta( $post->ID ),
				'cats'        => is_wp_error( $cats ) ? array() : array_map( function ( $t ) { return array( 'name' => $t->name, 'slug' => $t->slug ); }, $cats ),
				'lang_slug'   => $this->language_slug_from_term_id( (int) get_post_meta( $post->ID, 'language', true ) ),
				'thumb'       => $this->gather_thumb( $post->ID ),
				'text_id'     => (int) get_post_meta( $post->ID, 'source_text', true ),
				'translators' => $this->relation_ids_from_meta( $post->ID, 'translation_translators' ),
			);
		}

		WP_CLI::log( count( $items ) . ' produits publiés sur le hub.' );
		if ( $dry ) {
			foreach ( array_slice( $items, 0, 5 ) as $item ) {
				WP_CLI::log( sprintf( '  DRY %d "%s" lang=%s text=%d translators=[%s] déjà_migré=%s', $item['hub_id'], $item['fields']['post_title'], $item['lang_slug'], $item['text_id'], implode( ',', $item['translators'] ), isset( $map[ $item['hub_id'] ] ) ? 'oui' : 'non' ) );
			}
			WP_CLI::success( 'Dry-run produits terminé.' );
			return;
		}

		switch_to_blog( $blog_id );
		try {
			$done = 0;
			foreach ( $items as $item ) {
				if ( isset( $map[ $item['hub_id'] ] ) && get_post( $map[ $item['hub_id'] ] ) ) {
					continue;
				}
				$fields              = $item['fields'];
				$fields['post_type'] = 'product';
				$new_id              = wp_insert_post( $fields, true );
				if ( is_wp_error( $new_id ) ) {
					WP_CLI::warning( "Échec produit {$item['hub_id']} : " . $new_id->get_error_message() );
					continue;
				}
				foreach ( $item['meta'] as $key => $values ) {
					foreach ( $values as $value ) {
						add_post_meta( $new_id, $key, $value );
					}
				}
				update_post_meta( $new_id, 'central_text_id', $item['text_id'] );
				update_post_meta( $new_id, 'central_translator_ids', wp_json_encode( $item['translators'] ) );
				update_post_meta( $new_id, 'mts_hub_origin_id', $item['hub_id'] );
				foreach ( $item['cats'] as $cat ) {
					$existing = term_exists( $cat['slug'], 'product_cat' );
					if ( ! $existing ) {
						$existing = wp_insert_term( $cat['name'], 'product_cat', array( 'slug' => $cat['slug'] ) );
					}
					if ( ! is_wp_error( $existing ) ) {
						wp_set_object_terms( $new_id, (int) $existing['term_id'], 'product_cat', true );
					}
				}
				$this->ensure_language_term( $item['lang_slug'] );
				wp_set_object_terms( $new_id, $item['lang_slug'], 'mts_language' );
				$this->sideload_thumb( $item['thumb'], $new_id );
				$map[ $item['hub_id'] ] = (int) $new_id;
				$done++;
			}
		} finally {
			restore_current_blog();
		}
		$this->map_save( 'products', $map );
		WP_CLI::success( "$done produit(s) migré(s) vers le blog $blog_id." );
	}

	/**
	 * Terminologie : translated_term puis term_usage (citations → contextes).
	 *
	 * ## OPTIONS
	 * [--to=<branch>] [--dry-run]
	 */
	public function terminology( $args, $assoc ) {
		$dry         = isset( $assoc['dry-run'] );
		$blog_id     = $this->target_blog( $assoc );
		$term_map    = $this->map_get( 'terms' );
		$usage_map   = $this->map_get( 'usages' );
		$product_map = $this->map_get( 'products' );

		$terms = array();
		foreach ( get_posts( array( 'post_type' => 'translated_term', 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $post ) {
			$terms[] = array(
				'hub_id'     => $post->ID,
				'fields'     => $this->copy_post_fields( $post ),
				'tibetan_id' => (int) get_post_meta( $post->ID, 'tibetan_term', true ),
			);
		}
		$usages = array();
		foreach ( get_posts( array( 'post_type' => 'term_usage', 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $post ) {
			$usages[] = array(
				'hub_id'          => $post->ID,
				'fields'          => $this->copy_post_fields( $post ),
				'translated_term' => (int) get_post_meta( $post->ID, 'translated_term', true ),
				'translation'     => (int) get_post_meta( $post->ID, 'translations', true ),
				'context_source'  => (string) get_post_meta( $post->ID, 'term_quote_tib', true ),
				'context_target'  => (string) get_post_meta( $post->ID, 'term_quote_target_lang', true ),
				'quote_reference' => (string) get_post_meta( $post->ID, 'quote_reference', true ),
				'translator_note' => (string) get_post_meta( $post->ID, 'translator_note', true ),
			);
		}

		WP_CLI::log( count( $terms ) . ' translated_term, ' . count( $usages ) . ' term_usage publiés sur le hub.' );
		if ( $dry ) {
			foreach ( $terms as $t ) {
				WP_CLI::log( sprintf( '  DRY term %d "%s" → central_tibetan_term_id=%d', $t['hub_id'], $t['fields']['post_title'], $t['tibetan_id'] ) );
			}
			foreach ( $usages as $u ) {
				$product_target = isset( $product_map[ $u['translation'] ] ) ? $product_map[ $u['translation'] ] : 0;
				WP_CLI::log( sprintf( '  DRY usage %d : term %d→?, produit %d→%d, contexte src/cible %s/%s', $u['hub_id'], $u['translated_term'], $u['translation'], $product_target, $u['context_source'] ? 'oui' : 'non', $u['context_target'] ? 'oui' : 'non' ) );
			}
			WP_CLI::success( 'Dry-run terminologie terminé.' );
			return;
		}

		switch_to_blog( $blog_id );
		try {
			mts_register_branch_types();
			$done_t = 0;
			foreach ( $terms as $t ) {
				if ( isset( $term_map[ $t['hub_id'] ] ) && get_post( $term_map[ $t['hub_id'] ] ) ) {
					continue;
				}
				$fields              = $t['fields'];
				$fields['post_type'] = 'translated_term';
				$new_id              = wp_insert_post( $fields, true );
				if ( is_wp_error( $new_id ) ) {
					WP_CLI::warning( "Échec term {$t['hub_id']} : " . $new_id->get_error_message() );
					continue;
				}
				update_post_meta( $new_id, 'central_tibetan_term_id', $t['tibetan_id'] );
				update_post_meta( $new_id, 'mts_hub_origin_id', $t['hub_id'] );
				wp_set_object_terms( $new_id, (string) get_option( 'mts_default_language', 'en' ), 'mts_language' );
				$term_map[ $t['hub_id'] ] = (int) $new_id;
				$done_t++;
			}
			$done_u = 0;
			foreach ( $usages as $u ) {
				if ( isset( $usage_map[ $u['hub_id'] ] ) && get_post( $usage_map[ $u['hub_id'] ] ) ) {
					continue;
				}
				$fields              = $u['fields'];
				$fields['post_type'] = 'term_usage';
				$new_id              = wp_insert_post( $fields, true );
				if ( is_wp_error( $new_id ) ) {
					WP_CLI::warning( "Échec usage {$u['hub_id']} : " . $new_id->get_error_message() );
					continue;
				}
				update_post_meta( $new_id, 'translated_term_id', isset( $term_map[ $u['translated_term'] ] ) ? (int) $term_map[ $u['translated_term'] ] : 0 );
				update_post_meta( $new_id, 'translation_id', isset( $product_map[ $u['translation'] ] ) ? (int) $product_map[ $u['translation'] ] : 0 );
				update_post_meta( $new_id, 'context_source', $u['context_source'] );
				update_post_meta( $new_id, 'context_target', $u['context_target'] );
				update_post_meta( $new_id, 'quote_reference', $u['quote_reference'] );
				update_post_meta( $new_id, 'translator_note', $u['translator_note'] );
				update_post_meta( $new_id, 'created_via', 'migration' );
				update_post_meta( $new_id, 'confidence', 1 );
				update_post_meta( $new_id, 'mts_hub_origin_id', $u['hub_id'] );
				// Régénère le titre maintenant que les liens locaux existent.
				wp_update_post( array( 'ID' => $new_id ) );
				$usage_map[ $u['hub_id'] ] = (int) $new_id;
				$done_u++;
			}
		} finally {
			restore_current_blog();
		}
		$this->map_save( 'terms', $term_map );
		$this->map_save( 'usages', $usage_map );
		WP_CLI::success( "$done_t terme(s) + $done_u usage(s) migrés vers le blog $blog_id." );
	}

	/**
	 * Articles (posts publiés du hub) → branche.
	 *
	 * ## OPTIONS
	 * [--to=<branch>] [--dry-run]
	 */
	public function articles( $args, $assoc ) {
		$dry     = isset( $assoc['dry-run'] );
		$blog_id = $this->target_blog( $assoc );
		$map     = $this->map_get( 'articles' );

		$items = array();
		foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $post ) {
			$cats    = wp_get_post_terms( $post->ID, 'category' );
			$items[] = array(
				'hub_id' => $post->ID,
				'fields' => $this->copy_post_fields( $post ),
				'cats'   => is_wp_error( $cats ) ? array() : wp_list_pluck( $cats, 'name' ),
				'thumb'  => $this->gather_thumb( $post->ID ),
			);
		}

		WP_CLI::log( count( $items ) . ' article(s) publié(s) sur le hub.' );
		if ( $dry ) {
			foreach ( $items as $item ) {
				WP_CLI::log( sprintf( '  DRY %d "%s" cats=[%s] thumb=%s', $item['hub_id'], $item['fields']['post_title'], implode( ',', $item['cats'] ), $item['thumb'] ? 'oui' : 'non' ) );
			}
			WP_CLI::success( 'Dry-run articles terminé.' );
			return;
		}

		switch_to_blog( $blog_id );
		try {
			$done = 0;
			foreach ( $items as $item ) {
				if ( isset( $map[ $item['hub_id'] ] ) && get_post( $map[ $item['hub_id'] ] ) ) {
					continue;
				}
				$fields              = $item['fields'];
				$fields['post_type'] = 'post';
				$new_id              = wp_insert_post( $fields, true );
				if ( is_wp_error( $new_id ) ) {
					WP_CLI::warning( "Échec article {$item['hub_id']} : " . $new_id->get_error_message() );
					continue;
				}
				update_post_meta( $new_id, 'mts_hub_origin_id', $item['hub_id'] );
				foreach ( $item['cats'] as $cat_name ) {
					wp_set_object_terms( $new_id, $cat_name, 'category', true );
				}
				$this->sideload_thumb( $item['thumb'], $new_id );
				$map[ $item['hub_id'] ] = (int) $new_id;
				$done++;
			}
		} finally {
			restore_current_blog();
		}
		$this->map_save( 'articles', $map );
		WP_CLI::success( "$done article(s) migré(s) vers le blog $blog_id." );
	}

	/**
	 * Passe les originaux migrés du hub en draft (réversible).
	 *
	 * ## OPTIONS
	 * [--dry-run]
	 */
	public function retire( $args, $assoc ) {
		$dry = isset( $assoc['dry-run'] );
		$all = array();
		foreach ( array( 'products', 'terms', 'usages', 'articles' ) as $name ) {
			$all = array_merge( $all, array_keys( $this->map_get( $name ) ) );
		}
		WP_CLI::log( count( $all ) . ' original(aux) du hub concernés.' );
		if ( $dry ) {
			WP_CLI::success( 'Dry-run retire terminé (IDs hub : ' . implode( ',', $all ) . ')' );
			return;
		}
		$done = 0;
		foreach ( $all as $hub_id ) {
			$post = get_post( (int) $hub_id );
			if ( $post && 'publish' === $post->post_status ) {
				wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'draft' ) );
				$done++;
			}
		}
		WP_CLI::success( "$done original(aux) passés en draft sur le hub." );
	}
}

WP_CLI::add_command( 'mts migrate', 'MTS_Migrate_Command' );
