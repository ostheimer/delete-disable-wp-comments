<?php
/**
 * Check and copy language files to WordPress languages directory
 */

// Check and create language files if they don't exist
function check_and_create_language_files() {
    $languages = array(
        'de_DE', 'de_AT', 'de_CH', 'de',
        'en_US', 'en_GB', 'en'
    );

    $lang_dir = plugin_dir_path(dirname(__FILE__)) . 'languages';
    
    // Use WP_Filesystem
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once(ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }
    
    // Create languages directory if it doesn't exist
    if (!$wp_filesystem->exists($lang_dir)) {
        $wp_filesystem->mkdir($lang_dir, FS_CHMOD_DIR);
    }

    foreach ($languages as $locale) {
        create_mo_file($locale);
    }
}

function create_mo_file($locale) {
    $domain = 'delete-disable-comments';
    $mo_file = plugin_dir_path(dirname(__FILE__)) . 'languages/' . $domain . '-' . $locale . '.mo';
    $po_file = plugin_dir_path(dirname(__FILE__)) . 'languages/' . $domain . '-' . $locale . '.po';

    // Use WP_Filesystem
    global $wp_filesystem;
    if (empty($wp_filesystem)) {
        require_once(ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }

    // Check if files already exist
    if (!$wp_filesystem->exists($mo_file) || !$wp_filesystem->exists($po_file)) {
        // Create empty PO file with basic headers
        $po_content = 'msgid ""
msgstr ""
"Project-Id-Version: Delete & Disable Comments\n"
"POT-Creation-Date: ' . gmdate('Y-m-d H:i:sO') . '\n"
"PO-Revision-Date: ' . gmdate('Y-m-d H:i:sO') . '\n"
"Last-Translator: \n"
"Language-Team: \n"
"Language: ' . $locale . '\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"';

        // Write PO file
        $wp_filesystem->put_contents($po_file, $po_content);

        // Create empty MO file
        $wp_filesystem->put_contents($mo_file, '');
    }
}

// Run the check on plugin activation
add_action('activate_plugin', 'check_and_create_language_files'); 