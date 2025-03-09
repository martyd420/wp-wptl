<?php
/**
 * Plugin Name: WP Multilingual Translator
 * Plugin URI: https://example.com/wp-multilingual-translator
 * Description: Translate all types of posts including custom post types, menus, and content into multiple languages with editable translations from the WordPress admin.
 * Version: 1.0.0
 * Author: WordPress Developer
 * Author URI: https://example.com
 * Text Domain: wp-multilingual-translator
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPMT_VERSION', '1.0.0');
define('WPMT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPMT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPMT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once WPMT_PLUGIN_DIR . 'includes/class-wpmt-loader.php';

// Initialize the plugin
function wpmt_init() {
    $plugin = new WPMT_Loader();
    $plugin->run();
}
add_action('plugins_loaded', 'wpmt_init');

// Activation hook
register_activation_hook(__FILE__, 'wpmt_activate');
function wpmt_activate() {
    // Create necessary database tables
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Table for storing translations
    $table_translations = $wpdb->prefix . 'wpmt_translations';
    
    $sql = "CREATE TABLE $table_translations (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        object_id bigint(20) NOT NULL,
        object_type varchar(20) NOT NULL,
        language_code varchar(10) NOT NULL,
        original_language varchar(10) NOT NULL,
        translated_content longtext,
        translated_title text,
        translated_excerpt text,
        translated_slug varchar(200),
        status varchar(20) DEFAULT 'draft' NOT NULL,
        last_updated datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY object_id (object_id),
        KEY language_code (language_code),
        KEY object_type (object_type),
        UNIQUE KEY unique_translation (object_id,object_type,language_code)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Add default options
    $default_options = array(
        'enabled_languages' => array('en_US'),
        'default_language' => 'en_US',
        'display_language_switcher' => true,
        'translate_slugs' => true,
        'auto_translate' => false,
        'translation_service' => 'none'
    );
    
    add_option('wpmt_options', $default_options);
    
    // Create translation directory if it doesn't exist
    $upload_dir = wp_upload_dir();
    $translation_dir = $upload_dir['basedir'] . '/translations';
    
    if (!file_exists($translation_dir)) {
        wp_mkdir_p($translation_dir);
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wpmt_deactivate');
function wpmt_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'wpmt_uninstall');
function wpmt_uninstall() {
    // Remove plugin options
    delete_option('wpmt_options');
    
    // Remove database tables
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wpmt_translations");
    
    // Remove translation files
    $upload_dir = wp_upload_dir();
    $translation_dir = $upload_dir['basedir'] . '/translations';
    
    if (file_exists($translation_dir)) {
        // Remove translation directory and its contents
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();
        global $wp_filesystem;
        $wp_filesystem->rmdir($translation_dir, true);
    }
}
