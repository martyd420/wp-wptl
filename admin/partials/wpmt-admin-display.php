<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="wpmt-admin-content">
        <form method="post" action="options.php">
            <?php
            // Output security fields for the registered setting
            settings_fields('wpmt_settings');
            
            // Output setting sections and their fields
            do_settings_sections('wpmt_settings');
            
            // Output save settings button
            submit_button(__('Save Settings', 'wp-multilingual-translator'));
            ?>
        </form>
    </div>
    
    <div class="wpmt-admin-sidebar">
        <div class="wpmt-admin-box">
            <h3><?php _e('Plugin Information', 'wp-multilingual-translator'); ?></h3>
            <p><?php _e('WP Multilingual Translator allows you to translate all types of posts, pages, custom post types, menus, and other content into multiple languages.', 'wp-multilingual-translator'); ?></p>
            <p><?php _e('Version', 'wp-multilingual-translator'); ?>: <?php echo WPMT_VERSION; ?></p>
        </div>
        
        <div class="wpmt-admin-box">
            <h3><?php _e('Quick Links', 'wp-multilingual-translator'); ?></h3>
            <ul>
                <li><a href="<?php echo admin_url('admin.php?page=wpmt-languages'); ?>"><?php _e('Languages', 'wp-multilingual-translator'); ?></a></li>
                <li><a href="<?php echo admin_url('admin.php?page=wpmt-status'); ?>"><?php _e('Translation Status', 'wp-multilingual-translator'); ?></a></li>
            </ul>
        </div>
        
        <div class="wpmt-admin-box">
            <h3><?php _e('Need Help?', 'wp-multilingual-translator'); ?></h3>
            <p><?php _e('Check out the documentation for help with setting up and using the plugin.', 'wp-multilingual-translator'); ?></p>
            <p><a href="https://example.com/documentation" target="_blank" class="button"><?php _e('Documentation', 'wp-multilingual-translator'); ?></a></p>
        </div>
    </div>
</div>

<style>
    .wpmt-admin-content {
        float: left;
        width: 65%;
        margin-right: 5%;
    }
    
    .wpmt-admin-sidebar {
        float: left;
        width: 30%;
    }
    
    .wpmt-admin-box {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        margin-bottom: 20px;
        padding: 15px;
    }
    
    .wpmt-admin-box h3 {
        margin-top: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    .wpmt-enabled-languages {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 10px;
        background: #f9f9f9;
    }
    
    .wpmt-enabled-languages label {
        display: block;
        margin-bottom: 5px;
    }
    
    @media screen and (max-width: 782px) {
        .wpmt-admin-content,
        .wpmt-admin-sidebar {
            float: none;
            width: 100%;
            margin-right: 0;
        }
        
        .wpmt-admin-content {
            margin-bottom: 30px;
        }
    }
</style>
