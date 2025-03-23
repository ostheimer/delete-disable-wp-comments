<?php
/**
 * Check and copy language files to WordPress languages directory
 */

function delete_disable_comments_check_languages() {
    // Get WordPress languages directory
    $wp_lang_dir = WP_LANG_DIR . '/plugins';
    if (!file_exists($wp_lang_dir)) {
        @mkdir($wp_lang_dir, 0755, true);
    }

    // Get plugin languages directory
    $plugin_lang_dir = plugin_dir_path(dirname(__FILE__)) . 'languages';

    // List of supported locales
    $locales = array(
        'de_DE',    // Deutsch (Sie)
        'de_AT',    // Deutsch (Österreich)
        'de_CH',    // Deutsch (Schweiz)
        'de',       // Deutsch (Standard)
        'en_US',    // Englisch (USA)
        'en_GB',    // Englisch (GB)
        'en'        // Englisch (Standard)
    );

    foreach ($locales as $locale) {
        $mo_source = $plugin_lang_dir . '/delete-disable-comments-' . $locale . '.mo';
        $mo_target = $wp_lang_dir . '/delete-disable-comments-' . $locale . '.mo';
        
        if (file_exists($mo_source)) {
            error_log("Found MO file for {$locale} in plugin directory");
            if (!file_exists($mo_target) || filemtime($mo_source) > filemtime($mo_target)) {
                if (@copy($mo_source, $mo_target)) {
                    error_log("Copied MO file for {$locale} to WordPress languages directory");
                } else {
                    error_log("Failed to copy MO file for {$locale}");
                }
            }
        } else {
            error_log("MO file for {$locale} not found in plugin directory");
        }
    }
}

// Run the check when the plugin is activated or updated
add_action('plugins_loaded', 'delete_disable_comments_check_languages', 5); 