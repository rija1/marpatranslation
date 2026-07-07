<?php
/**
 * CPT locaux des branches + taxonomie mts_language.
 *
 * Les CPT centraux (tibetan_term, text, text_author, translator) restent
 * enregistrés par Pods sur le site 1 uniquement (décision D8) — rien à faire ici.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Taxonomie de langues — enregistrée sur TOUS les sites : l'enregistrement
 * est global à la requête, et les lectures cross-site (wp_get_post_terms
 * dans mts_get_term_translations, wp_insert_term dans les seeds) exigent
 * taxonomy_exists() dans le contexte switché. Sans CPT rattaché sur le
 * hub, elle n'y produit aucune UI.
 */
function mts_register_language_taxonomy() {
	register_taxonomy(
		'mts_language',
		array( 'translation', 'translated_term' ),
		array(
			'labels'            => array(
				'name'          => __( 'Languages', 'mts-network' ),
				'singular_name' => __( 'Language', 'mts-network' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'language' ),
		)
	);
}

/**
 * Enregistre les CPT de branche.
 *
 * Appelée sur init pour les branches, ET manuellement par les seeds à
 * l'intérieur d'un switch_to_blog().
 */
function mts_register_branch_types() {

	mts_register_language_taxonomy();

	register_post_type(
		'translation',
		array(
			'labels'       => array(
				'name'          => __( 'Translations', 'mts-network' ),
				'singular_name' => __( 'Translation', 'mts-network' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'menu_icon'    => 'dashicons-translation',
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'translations' ),
			'taxonomies'   => array( 'mts_language' ),
		)
	);

	register_post_type(
		'translated_term',
		array(
			'labels'       => array(
				'name'          => __( 'Translated Terms', 'mts-network' ),
				'singular_name' => __( 'Translated Term', 'mts-network' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'menu_icon'    => 'dashicons-editor-spellcheck',
			'supports'     => array( 'title' ),
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'glossary-term' ),
			'taxonomies'   => array( 'mts_language' ),
		)
	);

	register_post_type(
		'term_usage',
		array(
			'labels'       => array(
				'name'          => __( 'Term Usages', 'mts-network' ),
				'singular_name' => __( 'Term Usage', 'mts-network' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'menu_icon'    => 'dashicons-editor-quote',
			'supports'     => array( 'title' ),
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'term-usage' ),
		)
	);
}

add_action( 'init', function () {
	if ( is_main_site() ) {
		mts_register_language_taxonomy();
	} else {
		mts_register_branch_types();
		// D15 : les produits Woo portent les traductions → taguables par langue
		// (filtres du catalogue). No-op tant que Woo n'est pas actif.
		if ( post_type_exists( 'product' ) ) {
			register_taxonomy_for_object_type( 'mts_language', 'product' );
		}
	}
}, 15 );
