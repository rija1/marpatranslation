<?php
/**
 * Seeds one-shot gatés par options réseau (pattern mts_FOO_v1, cf. §4 du
 * doc maître). Re-run : bump du suffixe en code, ou
 * `wp site option delete mts_FOO_v1`.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'mts_run_seeds', 20 );

function mts_run_seeds() {
	// Une seule exécution nécessaire, déclenchable depuis n'importe quel
	// site (admin ou WP-CLI) — chaque seed boucle sur les branches.
	mts_seed_languages();
	mts_seed_atelier_caps();
	mts_seed_branch_options();
}

/**
 * Langues par branche : slug = code langue, name = autonyme.
 */
function mts_seed_languages() {
	if ( get_site_option( 'mts_languages_v1' ) ) {
		return;
	}

	$languages = array(
		'en'      => 'English',
		'fr'      => 'Français',
		'zh-hant' => '繁體中文',
		'ne'      => 'नेपाली',
	);

	foreach ( mts_get_branch_sites() as $site ) {
		switch_to_blog( (int) $site->blog_id );
		// Le contexte switché n'a pas ré-exécuté init : enregistrer les
		// types pour que wp_insert_term accepte la taxonomie.
		mts_register_branch_types();
		foreach ( $languages as $slug => $name ) {
			if ( ! term_exists( $slug, 'mts_language' ) ) {
				wp_insert_term( $name, 'mts_language', array( 'slug' => $slug ) );
			}
		}
		restore_current_blog();
	}

	update_site_option( 'mts_languages_v1', current_time( 'mysql' ) );
}

/**
 * Capability mts_use_atelier sur editor/administrator des branches.
 */
function mts_seed_atelier_caps() {
	if ( get_site_option( 'mts_atelier_caps_v1' ) ) {
		return;
	}

	foreach ( mts_get_branch_sites() as $site ) {
		switch_to_blog( (int) $site->blog_id );
		foreach ( array( 'editor', 'administrator' ) as $role_name ) {
			$role = get_role( $role_name );
			if ( $role ) {
				$role->add_cap( 'mts_use_atelier' );
			}
		}
		restore_current_blog();
	}

	update_site_option( 'mts_atelier_caps_v1', current_time( 'mysql' ) );
}

/**
 * Options par défaut des branches : langue par défaut, pas d'indexation en
 * dev, réglages horaires copiés du hub.
 */
function mts_seed_branch_options() {
	if ( get_site_option( 'mts_branch_options_v1' ) ) {
		return;
	}

	$default_languages = array(
		'eu' => 'en',
		'hk' => 'zh-hant',
		'np' => 'ne',
	);

	$hub_timezone    = get_blog_option( MTS_MAIN_SITE_ID, 'timezone_string' );
	$hub_date_format = get_blog_option( MTS_MAIN_SITE_ID, 'date_format' );

	foreach ( mts_get_branch_sites() as $site ) {
		$branch = mts_branch_slug_from_site( $site );
		switch_to_blog( (int) $site->blog_id );

		update_option( 'mts_default_language', isset( $default_languages[ $branch ] ) ? $default_languages[ $branch ] : 'en' );
		update_option( 'blog_public', 0 );
		if ( $hub_timezone ) {
			update_option( 'timezone_string', $hub_timezone );
		}
		if ( $hub_date_format ) {
			update_option( 'date_format', $hub_date_format );
		}

		restore_current_blog();
	}

	update_site_option( 'mts_branch_options_v1', current_time( 'mysql' ) );
}
