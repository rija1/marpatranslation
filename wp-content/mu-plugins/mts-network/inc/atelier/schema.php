<?php
/**
 * Atelier — tables candidates + rejets (spec atelier-terminologique.md §6).
 * Créées par branche, gate réseau mts_atelier_schema_v1.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	if ( get_site_option( 'mts_atelier_schema_v1' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	global $wpdb;

	foreach ( mts_get_branch_sites() as $site ) {
		switch_to_blog( (int) $site->blog_id );
		$charset = $wpdb->get_charset_collate();
		dbDelta( "CREATE TABLE {$wpdb->prefix}mts_atelier_candidates (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			job_id VARCHAR(36) NOT NULL,
			tibetan TEXT NOT NULL,
			wylie VARCHAR(255) NOT NULL DEFAULT '',
			target VARCHAR(255) NOT NULL,
			target_lang VARCHAR(12) NOT NULL DEFAULT '',
			context_source TEXT,
			context_target TEXT,
			confidence DECIMAL(3,2) NOT NULL DEFAULT 0,
			source_anchor TINYINT(1) NOT NULL DEFAULT 0,
			central_match BIGINT UNSIGNED NULL,
			match_type VARCHAR(12) NULL,
			status VARCHAR(12) NOT NULL DEFAULT 'open',
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY job_id (job_id),
			KEY status (status),
			KEY wylie (wylie)
		) $charset;" );
		dbDelta( "CREATE TABLE {$wpdb->prefix}mts_atelier_rejections (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			wylie VARCHAR(255) NOT NULL DEFAULT '',
			tibetan VARCHAR(255) NOT NULL DEFAULT '',
			target_norm VARCHAR(255) NOT NULL,
			target_lang VARCHAR(12) NOT NULL DEFAULT '',
			rejected_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY pair (tibetan(120), target_norm(120), target_lang)
		) $charset;" );
		restore_current_blog();
	}

	update_site_option( 'mts_atelier_schema_v1', current_time( 'mysql' ) );
}, 30 );
