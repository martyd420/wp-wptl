<?php
/**
 * Handles content translation filtering.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class WPMT_Content_Filter {
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        
    }
    
    /**
     * Translate post title.
     *
     * @since    1.0.0
     * @param    string    $title     The post title.
     * @param    int       $post_id   The post ID.
     * @return   string               The translated post title.
     */
    public function translate_title($title, $post_id = 0) {
        // If no post ID is provided, return the original title
        if (!$post_id) {
            return $title;
        }
        
        // Get current language
        $current_language = WPMT_Utils::get_current_language();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If we're in the default language, return the original title
        if ($current_language === $default_language) {
            return $title;
        }
        
        // Get translation for this post
        $translation = WPMT_Utils::get_translation($post_id, 'post', $current_language);
        
        // If translation exists and has a title, return the translated title
        if ($translation && !empty($translation['translated_title'])) {
            return $translation['translated_title'];
        }
        
        return $title;
    }
    
    /**
     * Translate post content.
     *
     * @since    1.0.0
     * @param    string    $content    The post content.
     * @return   string                The translated post content.
     */
    public function translate_content($content) {
        // Get current post ID
        $post_id = get_the_ID();
        
        // If no post ID is available, return the original content
        if (!$post_id) {
            return $content;
        }
        
        // Get current language
        $current_language = WPMT_Utils::get_current_language();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If we're in the default language, return the original content
        if ($current_language === $default_language) {
            return $content;
        }
        
        // Get translation for this post
        $translation = WPMT_Utils::get_translation($post_id, 'post', $current_language);
        
        // If translation exists and has content, return the translated content
        if ($translation && !empty($translation['translated_content'])) {
            return $translation['translated_content'];
        }
        
        return $content;
    }
    
    /**
     * Translate post excerpt.
     *
     * @since    1.0.0
     * @param    string    $excerpt    The post excerpt.
     * @param    WP_Post   $post       The post object.
     * @return   string                The translated post excerpt.
     */
    public function translate_excerpt($excerpt, $post = null) {
        // If no post is provided, try to get the current post
        if (!$post) {
            $post = get_post();
        }
        
        // If no post is available, return the original excerpt
        if (!$post) {
            return $excerpt;
        }
        
        // Get current language
        $current_language = WPMT_Utils::get_current_language();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If we're in the default language, return the original excerpt
        if ($current_language === $default_language) {
            return $excerpt;
        }
        
        // Get translation for this post
        $translation = WPMT_Utils::get_translation($post->ID, 'post', $current_language);
        
        // If translation exists and has an excerpt, return the translated excerpt
        if ($translation && !empty($translation['translated_excerpt'])) {
            return $translation['translated_excerpt'];
        }
        
        return $excerpt;
    }
    
    /**
     * Translate menu items.
     *
     * @since    1.0.0
     * @param    array    $menu_items    The menu items.
     * @return   array                   The translated menu items.
     */
    public function translate_menu_items($menu_items) {
        // Get current language
        $current_language = WPMT_Utils::get_current_language();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If we're in the default language, return the original menu items
        if ($current_language === $default_language) {
            return $menu_items;
        }
        
        // Loop through menu items and translate them
        foreach ($menu_items as $menu_item) {
            // Get translation for this menu item
            $translation = WPMT_Utils::get_translation($menu_item->ID, 'menu_item', $current_language);
            
            // If translation exists, update the menu item
            if ($translation) {
                // Translate title
                if (!empty($translation['translated_title'])) {
                    $menu_item->title = $translation['translated_title'];
                }
                
                // Translate attribute title
                if (!empty($translation['translated_attr_title'])) {
                    $menu_item->attr_title = $translation['translated_attr_title'];
                }
                
                // Translate description
                if (!empty($translation['translated_description'])) {
                    $menu_item->description = $translation['translated_description'];
                }
                
                // Add language parameter to URL if it's a custom link
                if ($menu_item->type === 'custom' && !empty($menu_item->url)) {
                    $menu_item->url = WPMT_Public::add_language_to_url($menu_item->url);
                }
            }
        }
        
        return $menu_items;
    }
    
    /**
     * Translate term name.
     *
     * @since    1.0.0
     * @param    string    $name       The term name.
     * @param    object    $term       The term object.
     * @return   string                The translated term name.
     */
    public function translate_term_name($name, $term) {
        // Get current language
        $current_language = WPMT_Utils::get_current_language();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If we're in the default language, return the original name
        if ($current_language === $default_language) {
            return $name;
        }
        
        // Get translation for this term
        $translation = WPMT_Utils::get_translation($term->term_id, 'term', $current_language);
        
        // If translation exists and has a name, return the translated name
        if ($translation && !empty($translation['translated_title'])) {
            return $translation['translated_title'];
        }
        
        return $name;
    }
    
    /**
     * Translate term description.
     *
     * @since    1.0.0
     * @param    string    $description    The term description.
     * @param    object    $term           The term object.
     * @return   string                    The translated term description.
     */
    public function translate_term_description($description, $term) {
        // Get current language
        $current_language = WPMT_Utils::get_current_language();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If we're in the default language, return the original description
        if ($current_language === $default_language) {
            return $description;
        }
        
        // Get translation for this term
        $translation = WPMT_Utils::get_translation($term->term_id, 'term', $current_language);
        
        // If translation exists and has a description, return the translated description
        if ($translation && !empty($translation['translated_content'])) {
            return $translation['translated_content'];
        }
        
        return $description;
    }
    
    /**
     * Translate widget title.
     *
     * @since    1.0.0
     * @param    string    $title      The widget title.
     * @param    array     $instance   The widget instance.
     * @param    string    $id_base    The widget ID base.
     * @return   string                The translated widget title.
     */
    public function translate_widget_title($title, $instance, $id_base) {
        // If title is empty, return it
        if (empty($title)) {
            return $title;
        }
        
        // Get current language
        $current_language = WPMT_Utils::get_current_language();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If we're in the default language, return the original title
        if ($current_language === $default_language) {
            return $title;
        }
        
        // Get widget ID
        $widget_id = 'widget_' . $id_base;
        
        // Get translation for this widget
        $translation = WPMT_Utils::get_translation($widget_id, 'widget', $current_language);
        
        // If translation exists and has a title for this instance, return the translated title
        if ($translation && isset($translation['titles']) && isset($translation['titles'][$instance['_widget_number']])) {
            return $translation['titles'][$instance['_widget_number']];
        }
        
        return $title;
    }
    
    /**
     * Translate text.
     *
     * @since    1.0.0
     * @param    string    $text       The text to translate.
     * @param    string    $domain     The text domain.
     * @return   string                The translated text.
     */
    public function translate_text($text, $domain = 'default') {
        // If text is empty, return it
        if (empty($text)) {
            return $text;
        }
        
        // Get current language
        $current_language = WPMT_Utils::get_current_language();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If we're in the default language, return the original text
        if ($current_language === $default_language) {
            return $text;
        }
        
        // Get translation for this text
        $translation = WPMT_Utils::get_translation(md5($text), 'string', $current_language);
        
        // If translation exists, return the translated text
        if ($translation && !empty($translation['translated_content'])) {
            return $translation['translated_content'];
        }
        
        return $text;
    }
}
