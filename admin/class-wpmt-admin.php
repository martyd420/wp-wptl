<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class WPMT_Admin {
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        
    }
    
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'wpmt-admin',
            WPMT_PLUGIN_URL . 'admin/css/wpmt-admin.css',
            array(),
            WPMT_VERSION,
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'wpmt-admin',
            WPMT_PLUGIN_URL . 'admin/js/wpmt-admin.js',
            array('jquery'),
            WPMT_VERSION,
            false
        );
        
        // Localize the script with data for JavaScript
        wp_localize_script(
            'wpmt-admin',
            'wpmt_admin_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wpmt_admin_nonce'),
                'strings' => array(
                    'confirm_delete' => __('Are you sure you want to delete this translation?', 'wp-multilingual-translator'),
                    'saving' => __('Saving...', 'wp-multilingual-translator'),
                    'saved' => __('Saved!', 'wp-multilingual-translator'),
                    'error' => __('Error!', 'wp-multilingual-translator')
                )
            )
        );
    }
    
    /**
     * Add plugin admin menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Main menu
        add_menu_page(
            __('WP Multilingual Translator', 'wp-multilingual-translator'),
            __('Translations', 'wp-multilingual-translator'),
            'manage_options',
            'wpmt-settings',
            array($this, 'display_plugin_admin_page'),
            'dashicons-translation',
            100
        );
        
        // Settings submenu
        add_submenu_page(
            'wpmt-settings',
            __('Settings', 'wp-multilingual-translator'),
            __('Settings', 'wp-multilingual-translator'),
            'manage_options',
            'wpmt-settings',
            array($this, 'display_plugin_admin_page')
        );
        
        // Languages submenu
        add_submenu_page(
            'wpmt-settings',
            __('Languages', 'wp-multilingual-translator'),
            __('Languages', 'wp-multilingual-translator'),
            'manage_options',
            'wpmt-languages',
            array($this, 'display_languages_page')
        );
        
        // Translation status submenu
        add_submenu_page(
            'wpmt-settings',
            __('Translation Status', 'wp-multilingual-translator'),
            __('Translation Status', 'wp-multilingual-translator'),
            'manage_options',
            'wpmt-status',
            array($this, 'display_status_page')
        );
    }
    
    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once WPMT_PLUGIN_DIR . 'admin/partials/wpmt-admin-display.php';
    }
    
    /**
     * Render the languages page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_languages_page() {
        include_once WPMT_PLUGIN_DIR . 'admin/partials/wpmt-languages-display.php';
    }
    
    /**
     * Render the translation status page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_status_page() {
        include_once WPMT_PLUGIN_DIR . 'admin/partials/wpmt-status-display.php';
    }
}
