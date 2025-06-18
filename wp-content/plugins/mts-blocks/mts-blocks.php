<?php
/**
 * Plugin Name:       MTS Blocks
 * Description:       Custom blocks for MTS Knowledge Hub with consolidated styling
 * Version:           0.2.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            MTS Development Team
 * License:           GPL-2.0-or-later
 * Text Domain:       mts-blocks
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'inc/class-pods-grid.php';

class MTS_Blocks_Manager {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'register_blocks']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_shared_styles']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_shared_styles']);
    }
    
    /**
     * Enqueue shared styles for all MTS blocks
     */
    public function enqueue_shared_styles() {
        $shared_css_path = plugin_dir_path(__FILE__) . 'build/shared/mts-shared.css';
        $shared_css_url = plugin_dir_url(__FILE__) . 'build/shared/mts-shared.css';
        
        if (file_exists($shared_css_path)) {
            wp_enqueue_style(
                'mts-blocks-shared',
                $shared_css_url,
                [],
                filemtime($shared_css_path)
            );
        }
    }
    
    /**
     * Register all MTS blocks
     */
    public function register_blocks() {
        $blocks = [
            'mts-tibetan-terms',
            'mts-translators', 
            'mts-translated-terms',
            'mts-translations',
            'mts-authors',
            'mts-texts'
        ];
        
        foreach ($blocks as $block) {
            $this->register_single_block($block);
        }
        
        // Initialize Pods Grid for Tibetan Terms
        Pods_Grid::get_instance('tibetan_term', [
            'title' => 'Tibetan Term',
            'translated_terms' => 'Translations',
            'view' => 'View'
        ]);
    }
    
    /**
     * Register individual block
     */
    private function register_single_block($block_name) {
        $block_path = __DIR__ . '/build/' . $block_name;
        
        if (file_exists($block_path . '/block.json')) {
            register_block_type($block_path);
        }
    }
}

// Initialize the plugin
MTS_Blocks_Manager::get_instance();