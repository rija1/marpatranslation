<?php
/**
 * TEMPORARY SCRIPT: Import Tibetan Terms
 * Run this once to import the list of Tibetan terms into the tibetan_term post type
 * 
 * Usage: Visit http://marpatranslation.local/import-tibetan-terms.php
 */

// Load WordPress
require_once('wp-load.php');

// Check if user has permissions
if (!current_user_can('manage_options')) {
    die('<h1>Access Denied</h1><p>You must be logged in as an administrator to run this import.</p>');
}

// List of Tibetan terms to import
$tibetan_terms = array(
    'ཆོས་མངོན་པ།',
    'གྲུབ་ཐོབ།',
    'བསྙེན་སྒྲུབ།',
    'རྙིང་མ།',
    'དངོས་གྲུབ།',
    'བྱང་ཆུབ་ཀྱི་སེམས།',
    'བར་དོ།',
    'བྱང་ཆུབ་སེམས་དཔའ།',
    'སངས་རྒྱས།',
    'སངས་རྒྱས་ཀྱི་གོ་འཕང་།',
    'ཞི་གནས།',
    'འཁོར་བ།',
    'བདུད།',
    'འདོད་ཁམས།',
    'གཟུགས་ཁམས།',
    'གཟུགས་མེད་ཁམས།',
    'ལྷས་བྱིན།',
    'གཟུངས།',
    'ཆོས།',
    'ཆོས་སྐུ།',
    'ཆོས་སྐྱོང་།',
    'འཇིག་རྟེན་ཆོས་བརྒྱད།',
    'སྤྲུལ་སྐུ།',
    'དབང་བསྐུར།',
    'ལོངས་སྤྱོད་རྫོགས་པའི་སྐུ།',
    'ཕུང་པོ་ལྔ།',
    'ཡེ་ཤེས་ལྔ།',
    'རྒྱུད་སྡེ་བཞི།',
    'གླིང་བཞི།',
    'སྒམ་པོ་པ།',
    'བསྐྱེད་རིམ།',
    'ཐེག་པ་ཆེན་པོ།',
    'ཕྱག་རྒྱ་ཆེན་པོ།',
    'ཉན་ཐོས།',
    'མཐོ་རིས།',
    'ལྷག་མཐོང་།',
    'སྐྱེས་རབས།',
    'བཀའ་གདམས་པ།',
    'བཀའ་བརྒྱུད།',
    'ཀར་མ་པ།',
    'བླ་མ།',
    'དགེ་བསྙེན།',
    'ཐེག་དམན།',
    'ཐར་པ།',
    'གཤིན་རྗེ།',
    'ངན་སོང་།',
    'མར་པ།',
    'ཏིང་ངེ་འཛིན།',
    'ཉོན་མོངས།',
    'བསོད་ནམས།',
    'མི་ལ་རས་པ།',
    'མུ་སྟེགས་པ།',
    'ཚུལ་ཁྲིམས།',
    'རི་རབ་ལྷུན་པོ།',
    'ཀླུ།',
    'ན་རོ་པ།',
    'གསར་མ།',
    'ཤེས་རབ་ཀྱི་ཕ་རོལ་ཏུ་ཕྱིན་པ།',
    'མན་ངག',
    'ཡི་དམ།',
    'རིག་པ།',
    'རྩ་བའི་བླ་མ།',
    'དམ་ཚིག',
    'དགེ་འདུན།',
    'གསང་སྔགས།',
    'ཡན་ལག་བདུན་པ།',
    'ཕ་རོལ་ཏུ་ཕྱིན་པ་དྲུག',
    'མཁའ་འགྲོ་མ།',
    'རང་སངས་རྒྱས།',
    'མཆོད་རྟེན།',
    'བདེ་བ་ཅན།',
    'མདོ་སྡེ།',
    'རྒྱུད།',
    'ཕྱོགས་བཅུ།',
    'བཅོམ་ལྡན་འདས།',
    'དཀོན་མཆོག་གསུམ།',
    'སྐུ་གསུམ།',
    'རྩ་གསུམ།',
    'འཁོར་གསུམ།',
    'སྡོམ་པ་གསུམ།',
    'དེ་བཞིན་གཤེགས་པ།',
    'ཏཻ་ལོ་པ།',
    'མྱ་ངན་ལས་འདས་པ།',
    'བསྟན་བཅོས།',
    'སྟོང་གསུམ་འཇིག་རྟེན་གྱི་ཁམས།',
    'ཚོགས་གཉིས།',
    'སྒྲིབ་པ་གཉིས།',
    'ཆོས་དབྱིངས།',
    'རྡོ་རྗེ་འཆང་།',
    'རྡོ་རྗེ་སེམས་དཔའ།',
    'རྡོ་རྗེ་ཕག་མོ།',
    'རྒྱལ་བ།',
    'འདུལ་བ།',
    'ལོག་པར་ལྟ་བ།'
);

echo '<!DOCTYPE html>
<html>
<head>
    <title>Import Tibetan Terms</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .term { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa; }
        .success { border-left-color: #46b450; }
        .error { border-left-color: #dc3232; }
        .tibetan { font-size: 18px; font-family: "Noto Serif Tibetan", serif; }
    </style>
</head>
<body>';

echo '<h1>Tibetan Terms Import</h1>';
echo '<p>Importing ' . count($tibetan_terms) . ' Tibetan terms into the tibetan_term post type...</p>';

$imported_count = 0;
$skipped_count = 0;
$errors = array();

foreach ($tibetan_terms as $index => $term) {
    $term = trim($term);
    
    if (empty($term)) {
        continue;
    }
    
    echo '<div class="term">';
    echo '<strong>' . ($index + 1) . '.</strong> <span class="tibetan">' . esc_html($term) . '</span><br>';
    
    // Check if term already exists
    $existing = get_posts(array(
        'post_type' => 'tibetan_term',
        'meta_query' => array(
            array(
                'key' => 'tibetan_term',
                'value' => $term,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'any'
    ));
    
    if (!empty($existing)) {
        echo '<span style="color: #e67e22;">Already exists (ID: ' . $existing[0]->ID . ')</span>';
        $skipped_count++;
    } else {
        // Create new tibetan_term post
        $post_data = array(
            'post_type' => 'tibetan_term',
            'post_status' => 'publish',
            'meta_input' => array(
                'tibetan_term' => $term
            )
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            echo '<span style="color: #46b450;">✓ Created successfully (ID: ' . $post_id . ')</span>';
            $imported_count++;
        } else {
            $error_message = is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error';
            echo '<span style="color: #dc3232;">✗ Failed: ' . esc_html($error_message) . '</span>';
            $errors[] = "Term #{$index}: {$term} - {$error_message}";
        }
    }
    
    echo '</div>';
    
    // Flush output to show progress
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
}

echo '<hr>';
echo '<h2>Import Summary</h2>';
echo '<p><strong>Total terms:</strong> ' . count($tibetan_terms) . '</p>';
echo '<p><strong>Successfully imported:</strong> ' . $imported_count . '</p>';
echo '<p><strong>Skipped (already exist):</strong> ' . $skipped_count . '</p>';
echo '<p><strong>Errors:</strong> ' . count($errors) . '</p>';

if (!empty($errors)) {
    echo '<h3>Error Details:</h3>';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>' . esc_html($error) . '</li>';
    }
    echo '</ul>';
}

echo '<hr>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ul>';
echo '<li>Review the imported terms in your WordPress admin</li>';
echo '<li>Delete this import script file for security: <code>import-tibetan-terms.php</code></li>';
echo '<li>The title generation system will automatically set proper titles for these posts</li>';
echo '</ul>';

echo '<p><a href="' . admin_url('edit.php?post_type=tibetan_term') . '">← View Tibetan Terms in Admin</a></p>';

echo '</body></html>';
?>