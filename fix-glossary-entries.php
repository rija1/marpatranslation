<?php
/**
 * TEMPORARY SCRIPT: Fix Glossary Entries
 * Run this once to fix field mappings for imported glossary entries
 * 
 * Usage: Visit http://marpatranslation.local/fix-glossary-entries.php
 */

// Load WordPress
require_once('wp-load.php');

// Check if user has permissions
if (!current_user_can('manage_options')) {
    die('<h1>Access Denied</h1><p>You must be logged in as an administrator to run this fix.</p>');
}

echo '<!DOCTYPE html>
<html>
<head>
    <title>Fix Glossary Entries</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .entry { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa; }
        .success { border-left-color: #46b450; }
        .error { border-left-color: #dc3232; }
    </style>
</head>
<body>';

echo '<h1>Fix Glossary Entries Field Mappings</h1>';

// Get all glossary entries
$glossary_entries = get_posts(array(
    'post_type' => 'glossary_entry',
    'posts_per_page' => -1,
    'post_status' => 'publish'
));

echo '<p>Found ' . count($glossary_entries) . ' glossary entries to fix...</p>';

$fixed_count = 0;
$error_count = 0;

foreach ($glossary_entries as $entry) {
    echo '<div class="entry">';
    echo '<strong>Entry ID ' . $entry->ID . '</strong><br>';
    
    try {
        // Get existing metadata
        $english_term = get_post_meta($entry->ID, 'english_term', true);
        $definition = get_post_meta($entry->ID, 'definitiion', true);
        $sanskrit_id = get_post_meta($entry->ID, 'sanskrit_term', true);
        $tibetan_id = get_post_meta($entry->ID, 'tibetan_term', true);
        
        echo 'English Term: ' . esc_html($english_term) . '<br>';
        
        // Fix 1: Set glossary_term field to the English term
        if (!empty($english_term)) {
            update_post_meta($entry->ID, 'glossary_term', $english_term);
            echo '✓ Set glossary_term field<br>';
        }
        
        // Fix 2: Create proper Pods relationships for Sanskrit term
        if (!empty($sanskrit_id) && is_numeric($sanskrit_id)) {
            // Remove old meta
            delete_post_meta($entry->ID, 'sanskrit_term');
            
            // Add to wp_podsrel table (field_id 840 = sanskrit_term field)
            global $wpdb;
            $wpdb->replace(
                $wpdb->prefix . 'podsrel',
                array(
                    'field_id' => 840,  // sanskrit_term field ID
                    'item_id' => $entry->ID,
                    'related_item_id' => $sanskrit_id,
                    'weight' => 1
                ),
                array('%d', '%d', '%d', '%d')
            );
            echo '✓ Fixed Sanskrit term relationship (ID: ' . $sanskrit_id . ')<br>';
        }
        
        // Fix 3: Create proper Pods relationships for Tibetan term
        if (!empty($tibetan_id) && is_numeric($tibetan_id)) {
            // Remove old meta
            delete_post_meta($entry->ID, 'tibetan_term');
            
            // Add to wp_podsrel table (field_id 839 = tibetan_term field)
            $wpdb->replace(
                $wpdb->prefix . 'podsrel',
                array(
                    'field_id' => 839,  // tibetan_term field ID
                    'item_id' => $entry->ID,
                    'related_item_id' => $tibetan_id,
                    'weight' => 1
                ),
                array('%d', '%d', '%d', '%d')
            );
            echo '✓ Fixed Tibetan term relationship (ID: ' . $tibetan_id . ')<br>';
        }
        
        echo '<span style="color: #46b450;">✓ Fixed successfully</span>';
        $fixed_count++;
        
    } catch (Exception $e) {
        echo '<span style="color: #dc3232;">✗ Error: ' . esc_html($e->getMessage()) . '</span>';
        $error_count++;
    }
    
    echo '</div>';
    
    // Flush output
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
}

echo '<hr>';
echo '<h2>Fix Summary</h2>';
echo '<p><strong>Total entries:</strong> ' . count($glossary_entries) . '</p>';
echo '<p><strong>Successfully fixed:</strong> ' . $fixed_count . '</p>';
echo '<p><strong>Errors:</strong> ' . $error_count . '</p>';

echo '<hr>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ul>';
echo '<li>Check glossary entries in WordPress admin to verify relationships</li>';
echo '<li>Delete this fix script: <code>fix-glossary-entries.php</code></li>';
echo '<li>Delete the import script: <code>import-glossary-entries.php</code></li>';
echo '</ul>';

echo '<p><a href="' . admin_url('edit.php?post_type=glossary_entry') . '">← View Glossary Entries in Admin</a></p>';

echo '</body></html>';
?>