<?php
/**
 * Plugin Name: MTS Network
 * Description: Socle réseau MTS — CPT de branches, taxonomie de langues, helpers cross-site, REST mts/v1, seeds. Spec : doc/specs/mts-network-plugin.md.
 * Version: 0.1.0
 * Author: Marpa Translation Society
 * Network: true
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MTS_NETWORK_VERSION', '0.1.0' );

if ( ! defined( 'MTS_MAIN_SITE_ID' ) ) {
	define( 'MTS_MAIN_SITE_ID', 1 );
}

// Modèle LLM par défaut de l'Atelier (surchageable dans wp-config.php). Utilisé en Phase 6.
if ( ! defined( 'MTS_LLM_MODEL' ) ) {
	define( 'MTS_LLM_MODEL', 'claude-opus-4-8' );
}

$mts_network_inc = __DIR__ . '/mts-network/inc/';

require $mts_network_inc . 'post-types.php';
require $mts_network_inc . 'fields.php';
require $mts_network_inc . 'cache.php';
require $mts_network_inc . 'extract.php';
require $mts_network_inc . 'api.php';
require $mts_network_inc . 'rest.php';
require $mts_network_inc . 'titles.php';
require $mts_network_inc . 'content-types.php';
require $mts_network_inc . 'seeds.php';
require $mts_network_inc . 'cli.php';
