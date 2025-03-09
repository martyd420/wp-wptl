<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class WPMT_Public {
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        
    }
    
    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'wpmt-public',
            WPMT_PLUGIN_URL . 'public/css/wpmt-public.css',
            array(),
            WPMT_VERSION,
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'wpmt-public',
            WPMT_PLUGIN_URL . 'public/js/wpmt-public.js',
            array('jquery'),
            WPMT_VERSION,
            false
        );
        
        // Localize the script with data for JavaScript
        wp_localize_script(
            'wpmt-public',
            'wpmt_public_vars',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'current_language' => WPMT_Utils::get_current_language(),
                'default_language' => WPMT_Utils::get_option('default_language', 'en_US')
            )
        );
    }
    
    /**
     * Set language based on URL parameter.
     *
     * @since    1.0.0
     */
    public function set_language() {
        // Check if language is set in the URL
        if (isset($_GET['lang'])) {
            $lang = sanitize_text_field($_GET['lang']);
            $enabled_languages = WPMT_Utils::get_enabled_languages();
            
            if (isset($enabled_languages[$lang])) {
                WPMT_Utils::set_current_language($lang);
            }
        }
    }
    
    /**
     * Add language parameter to URLs.
     *
     * @since    1.0.0
     * @param    string    $url       The URL to modify.
     * @param    string    $language  The language code to add.
     * @return   string               The modified URL.
     */
    public static function add_language_to_url($url, $language = null) {
        if ($language === null) {
            $language = WPMT_Utils::get_current_language();
        }
        
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If it's the default language, don't add the parameter
        if ($language === $default_language) {
            return $url;
        }
        
        // Parse the URL
        $parsed_url = parse_url($url);
        
        // Build the query string
        $query = array();
        
        if (isset($parsed_url['query'])) {
            parse_str($parsed_url['query'], $query);
        }
        
        $query['lang'] = $language;
        
        // Rebuild the URL
        $new_url = '';
        
        if (isset($parsed_url['scheme'])) {
            $new_url .= $parsed_url['scheme'] . '://';
        }
        
        if (isset($parsed_url['host'])) {
            $new_url .= $parsed_url['host'];
        }
        
        if (isset($parsed_url['port'])) {
            $new_url .= ':' . $parsed_url['port'];
        }
        
        if (isset($parsed_url['path'])) {
            $new_url .= $parsed_url['path'];
        }
        
        if (!empty($query)) {
            $new_url .= '?' . http_build_query($query);
        }
        
        if (isset($parsed_url['fragment'])) {
            $new_url .= '#' . $parsed_url['fragment'];
        }
        
        return $new_url;
    }
    
    /**
     * Filter permalinks to add language parameter.
     *
     * @since    1.0.0
     * @param    string    $permalink    The post's permalink.
     * @param    WP_Post   $post         The post in question.
     * @return   string                  The filtered permalink.
     */
    public function filter_permalinks($permalink, $post) {
        $current_language = WPMT_Utils::get_current_language();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If it's the default language, don't modify the permalink
        if ($current_language === $default_language) {
            return $permalink;
        }
        
        // Check if post has a translation for the current language
        $translation = WPMT_Utils::get_translation($post->ID, 'post', $current_language);
        
        // If translation exists and has a slug, use it
        if ($translation && !empty($translation['translated_slug']) && WPMT_Utils::get_option('translate_slugs', true)) {
            // Replace the post slug in the permalink
            $post_name = $post->post_name;
            $translated_slug = $translation['translated_slug'];
            
            $permalink = str_replace('/' . $post_name . '/', '/' . $translated_slug . '/', $permalink);
        }
        
        // Add language parameter to the permalink
        return self::add_language_to_url($permalink);
    }
    
    /**
     * Filter term links to add language parameter.
     *
     * @since    1.0.0
     * @param    string    $termlink    Term link URL.
     * @param    WP_Term   $term        Term object.
     * @return   string                 The filtered term link.
     */
    public function filter_term_links($termlink, $term) {
        $current_language = WPMT_Utils::get_current_language();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If it's the default language, don't modify the term link
        if ($current_language === $default_language) {
            return $termlink;
        }
        
        // Check if term has a translation for the current language
        $translation = WPMT_Utils::get_translation($term->term_id, 'term', $current_language);
        
        // If translation exists and has a slug, use it
        if ($translation && !empty($translation['translated_slug']) && WPMT_Utils::get_option('translate_slugs', true)) {
            // Replace the term slug in the term link
            $term_slug = $term->slug;
            $translated_slug = $translation['translated_slug'];
            
            $termlink = str_replace('/' . $term_slug . '/', '/' . $translated_slug . '/', $termlink);
        }
        
        // Add language parameter to the term link
        return self::add_language_to_url($termlink);
    }
}
