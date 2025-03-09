<?php
/**
 * The main loader class for the plugin.
 *
 * This class initializes all the components of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class WPMT_Loader {
    /**
     * The array of actions registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // Core functionality
        require_once WPMT_PLUGIN_DIR . 'includes/class-wpmt-i18n.php';
        require_once WPMT_PLUGIN_DIR . 'includes/class-wpmt-utils.php';
        
        // Admin functionality
        require_once WPMT_PLUGIN_DIR . 'admin/class-wpmt-admin.php';
        require_once WPMT_PLUGIN_DIR . 'admin/class-wpmt-post-translator.php';
        require_once WPMT_PLUGIN_DIR . 'admin/class-wpmt-menu-translator.php';
        require_once WPMT_PLUGIN_DIR . 'admin/class-wpmt-settings.php';
        
        // Public functionality
        require_once WPMT_PLUGIN_DIR . 'public/class-wpmt-public.php';
        require_once WPMT_PLUGIN_DIR . 'public/class-wpmt-language-switcher.php';
        require_once WPMT_PLUGIN_DIR . 'public/class-wpmt-content-filter.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new WPMT_Admin();
        $post_translator = new WPMT_Post_Translator();
        $menu_translator = new WPMT_Menu_Translator();
        $settings = new WPMT_Settings();
        
        // Admin hooks
        $this->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Post translation hooks
        $this->add_action('add_meta_boxes', $post_translator, 'add_translation_meta_boxes');
        $this->add_action('save_post', $post_translator, 'save_post_translation', 10, 3);
        
        // Menu translation hooks
        $this->add_action('admin_head-nav-menus.php', $menu_translator, 'add_menu_translation_fields');
        $this->add_action('wp_update_nav_menu', $menu_translator, 'save_menu_translations', 10, 2);
        
        // Settings hooks
        $this->add_action('admin_init', $settings, 'register_settings');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new WPMT_Public();
        $language_switcher = new WPMT_Language_Switcher();
        $content_filter = new WPMT_Content_Filter();
        
        // Public hooks
        $this->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Language switcher hooks
        $this->add_action('wp_footer', $language_switcher, 'display_language_switcher');
        $this->add_filter('widget_text', $language_switcher, 'language_switcher_widget', 99);
        
        // Content filter hooks
        $this->add_filter('the_title', $content_filter, 'translate_title', 10, 2);
        $this->add_filter('the_content', $content_filter, 'translate_content');
        $this->add_filter('get_the_excerpt', $content_filter, 'translate_excerpt', 10, 2);
        $this->add_filter('wp_nav_menu_objects', $content_filter, 'translate_menu_items', 10);
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             The name of the WordPress action that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the action is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             The name of the WordPress filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @since    1.0.0
     * @access   private
     * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
     * @param    string               $hook             The name of the WordPress filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         The priority at which the function should be fired.
     * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
     * @return   array                                  The collection of actions and filters registered with WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Register the filters and actions with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        // Load plugin text domain
        $plugin_i18n = new WPMT_i18n();
        $plugin_i18n->load_plugin_textdomain();
        
        // Register all actions
        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        // Register all filters
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }
}
