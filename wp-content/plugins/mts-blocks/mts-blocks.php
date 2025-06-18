<?php

/**
 * Plugin Name:       MTS Blocks
 * Description:       Example block scaffolded with Create Block tool.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mts-blocks
 */

if (! defined('ABSPATH')) {
    exit;
}
require_once plugin_dir_path(__FILE__) . 'inc/class-pods-grid.php';

add_action('init', function() {
    Pods_Grid::get_instance('tibetan_term', [
        'title' => 'Tibetan Term',
        'translated_terms' => 'Translations',
        'view' => 'View'
    ]);
});

// MTS TIBETAN TERMS BLOCK - INIT
function create_block_mts_tibetan_terms_block_init()
{
    register_block_type(__DIR__ . '/build/mts-tibetan-terms');
}
add_action('init', 'create_block_mts_tibetan_terms_block_init');

// MTS TRANSLATORS BLOCK - INIT
function create_block_mts_translators_block_init()
{
    register_block_type(__DIR__ . '/build/mts-translators');
}
add_action('init', 'create_block_mts_translators_block_init');

// MTS TRANSLATED TERMS BLOCK - INIT
function create_block_mts_translated_terms_block_init()
{
    register_block_type(__DIR__ . '/build/mts-translated-terms');
}
add_action('init', 'create_block_mts_translated_terms_block_init');

// MTS TRANSLATIONS BLOCK - INIT
function create_block_mts_translations_block_init()
{
    register_block_type(__DIR__ . '/build/mts-translations');
}
add_action('init', 'create_block_mts_translations_block_init');

// MTS AUTHORS BLOCK - INIT
function create_block_mts_authors_block_init()
{
    register_block_type(__DIR__ . '/build/mts-authors');
}
add_action('init', 'create_block_mts_authors_block_init');

// MTS TEXT BLOCK - INIT
function create_block_mts_texts_block_init()
{
    register_block_type(__DIR__ . '/build/mts-texts');
}
add_action('init', 'create_block_mts_texts_block_init');

