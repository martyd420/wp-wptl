<?php
/**
 * Utility functions for the plugin.
 *
 * This class provides helper functions that will be used throughout the plugin.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class WPMT_Utils {
    /**
     * Get plugin options.
     *
     * @since    1.0.0
     * @return   array    The plugin options.
     */
    public static function get_options() {
        $default_options = array(
            'enabled_languages' => array('en_US'),
            'default_language' => 'en_US',
            'display_language_switcher' => true,
            'translate_slugs' => true,
            'auto_translate' => false,
            'translation_service' => 'none'
        );
        
        $options = get_option('wpmt_options', $default_options);
        
        return wp_parse_args($options, $default_options);
    }
    
    /**
     * Get a specific plugin option.
     *
     * @since    1.0.0
     * @param    string    $option     The option name.
     * @param    mixed     $default    The default value if the option doesn't exist.
     * @return   mixed                 The option value.
     */
    public static function get_option($option, $default = false) {
        $options = self::get_options();
        
        return isset($options[$option]) ? $options[$option] : $default;
    }
    
    /**
     * Update plugin options.
     *
     * @since    1.0.0
     * @param    array    $new_options    The new options to update.
     * @return   bool                     Whether the options were updated.
     */
    public static function update_options($new_options) {
        $options = self::get_options();
        $options = wp_parse_args($new_options, $options);
        
        return update_option('wpmt_options', $options);
    }
    
    /**
     * Get available languages.
     *
     * @since    1.0.0
     * @return   array    The available languages.
     */
    public static function get_available_languages() {
        // Get WordPress available languages
        $wp_languages = get_available_languages();
        
        // Add English (US) if it's not already in the list
        if (!in_array('en_US', $wp_languages)) {
            $wp_languages[] = 'en_US';
        }
        
        // Get language information
        $languages = array();
        
        foreach ($wp_languages as $locale) {
            $languages[$locale] = self::get_language_details($locale);
        }
        
        return $languages;
    }
    
    /**
     * Get enabled languages.
     *
     * @since    1.0.0
     * @return   array    The enabled languages.
     */
    public static function get_enabled_languages() {
        $enabled_locales = self::get_option('enabled_languages', array('en_US'));
        $available_languages = self::get_available_languages();
        
        $enabled_languages = array();
        
        foreach ($enabled_locales as $locale) {
            if (isset($available_languages[$locale])) {
                $enabled_languages[$locale] = $available_languages[$locale];
            }
        }
        
        return $enabled_languages;
    }
    
    /**
     * Get language details.
     *
     * @since    1.0.0
     * @param    string    $locale    The language locale.
     * @return   array                The language details.
     */
    public static function get_language_details($locale) {
        // Get language information from WordPress translations
        require_once(ABSPATH . 'wp-admin/includes/translation-install.php');
        $translations = wp_get_available_translations();
        
        // Default language details
        $language_details = array(
            'locale' => $locale,
            'name' => $locale,
            'native_name' => $locale,
            'lang_code' => substr($locale, 0, 2),
            'flag' => substr($locale, 0, 2),
        );
        
        // If it's English (US)
        if ($locale === 'en_US') {
            $language_details['name'] = 'English (United States)';
            $language_details['native_name'] = 'English (United States)';
            $language_details['lang_code'] = 'en';
            $language_details['flag'] = 'us';
        } 
        // If it's in the WordPress translations
        elseif (isset($translations[$locale])) {
            $language_details['name'] = $translations[$locale]['english_name'];
            $language_details['native_name'] = $translations[$locale]['native_name'];
            
            // Extract language code from locale (e.g., 'en' from 'en_US')
            $language_details['lang_code'] = substr($locale, 0, 2);
            
            // Extract country code for flag (e.g., 'us' from 'en_US')
            $country_code = strtolower(substr($locale, -2));
            $language_details['flag'] = $country_code;
        }
        
        return $language_details;
    }
    
    /**
     * Get current language.
     *
     * @since    1.0.0
     * @return   string    The current language locale.
     */
    public static function get_current_language() {
        $default_language = self::get_option('default_language', 'en_US');
        
        // Check if language is set in the URL
        if (isset($_GET['lang'])) {
            $lang = sanitize_text_field($_GET['lang']);
            $enabled_languages = self::get_enabled_languages();
            
            if (isset($enabled_languages[$lang])) {
                return $lang;
            }
        }
        
        // Check if language is set in the cookie
        if (isset($_COOKIE['wpmt_language'])) {
            $lang = sanitize_text_field($_COOKIE['wpmt_language']);
            $enabled_languages = self::get_enabled_languages();
            
            if (isset($enabled_languages[$lang])) {
                return $lang;
            }
        }
        
        return $default_language;
    }
    
    /**
     * Set current language.
     *
     * @since    1.0.0
     * @param    string    $locale    The language locale.
     */
    public static function set_current_language($locale) {
        $enabled_languages = self::get_enabled_languages();
        
        if (isset($enabled_languages[$locale])) {
            setcookie('wpmt_language', $locale, time() + (86400 * 30), '/'); // 30 days
        }
    }
    
    /**
     * Get translation for an object.
     *
     * @since    1.0.0
     * @param    int       $object_id       The object ID.
     * @param    string    $object_type     The object type.
     * @param    string    $language_code   The language code.
     * @return   array|false                The translation or false if not found.
     */
    public static function get_translation($object_id, $object_type, $language_code) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wpmt_translations';
        
        $translation = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE object_id = %d AND object_type = %s AND language_code = %s",
                $object_id,
                $object_type,
                $language_code
            ),
            ARRAY_A
        );
        
        return $translation;
    }
    
    /**
     * Save translation for an object.
     *
     * @since    1.0.0
     * @param    int       $object_id           The object ID.
     * @param    string    $object_type         The object type.
     * @param    string    $language_code       The language code.
     * @param    array     $translation_data    The translation data.
     * @return   int|false                      The number of rows affected or false on error.
     */
    public static function save_translation($object_id, $object_type, $language_code, $translation_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wpmt_translations';
        $default_language = self::get_option('default_language', 'en_US');
        
        // Check if translation already exists
        $existing_translation = self::get_translation($object_id, $object_type, $language_code);
        
        // Prepare data
        $data = array(
            'object_id' => $object_id,
            'object_type' => $object_type,
            'language_code' => $language_code,
            'original_language' => $default_language,
            'last_updated' => current_time('mysql')
        );
        
        // Merge with translation data
        $data = array_merge($data, $translation_data);
        
        // If translation exists, update it
        if ($existing_translation) {
            return $wpdb->update(
                $table_name,
                $data,
                array(
                    'id' => $existing_translation['id']
                )
            );
        } 
        // Otherwise, insert new translation
        else {
            return $wpdb->insert($table_name, $data);
        }
    }
    
    /**
     * Delete translation for an object.
     *
     * @since    1.0.0
     * @param    int       $object_id       The object ID.
     * @param    string    $object_type     The object type.
     * @param    string    $language_code   The language code (optional, if not provided, all translations for the object will be deleted).
     * @return   int|false                  The number of rows affected or false on error.
     */
    public static function delete_translation($object_id, $object_type, $language_code = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wpmt_translations';
        
        $where = array(
            'object_id' => $object_id,
            'object_type' => $object_type
        );
        
        if ($language_code) {
            $where['language_code'] = $language_code;
        }
        
        return $wpdb->delete($table_name, $where);
    }
    
    /**
     * Get language switcher HTML.
     *
     * @since    1.0.0
     * @param    string    $style    The style of the language switcher (dropdown, list, flags).
     * @return   string              The language switcher HTML.
     */
    public static function get_language_switcher_html($style = 'dropdown') {
        $enabled_languages = self::get_enabled_languages();
        $current_language = self::get_current_language();
        
        if (count($enabled_languages) <= 1) {
            return '';
        }
        
        $output = '<div class="wpmt-language-switcher wpmt-style-' . esc_attr($style) . '">';
        
        switch ($style) {
            case 'dropdown':
                $output .= '<select class="wpmt-language-select" onchange="window.location.href=this.value;">';
                
                foreach ($enabled_languages as $locale => $language) {
                    $selected = ($locale === $current_language) ? ' selected="selected"' : '';
                    $url = add_query_arg('lang', $locale);
                    
                    $output .= '<option value="' . esc_url($url) . '"' . $selected . '>';
                    $output .= esc_html($language['native_name']);
                    $output .= '</option>';
                }
                
                $output .= '</select>';
                break;
                
            case 'list':
                $output .= '<ul class="wpmt-language-list">';
                
                foreach ($enabled_languages as $locale => $language) {
                    $active = ($locale === $current_language) ? ' class="wpmt-active"' : '';
                    $url = add_query_arg('lang', $locale);
                    
                    $output .= '<li' . $active . '>';
                    $output .= '<a href="' . esc_url($url) . '">';
                    $output .= esc_html($language['native_name']);
                    $output .= '</a>';
                    $output .= '</li>';
                }
                
                $output .= '</ul>';
                break;
                
            case 'flags':
                $output .= '<ul class="wpmt-language-flags">';
                
                foreach ($enabled_languages as $locale => $language) {
                    $active = ($locale === $current_language) ? ' class="wpmt-active"' : '';
                    $url = add_query_arg('lang', $locale);
                    $flag = $language['flag'];
                    
                    $output .= '<li' . $active . '>';
                    $output .= '<a href="' . esc_url($url) . '" title="' . esc_attr($language['native_name']) . '">';
                    $output .= '<img src="' . esc_url(WPMT_PLUGIN_URL . 'public/images/flags/' . $flag . '.png') . '" alt="' . esc_attr($language['native_name']) . '">';
                    $output .= '</a>';
                    $output .= '</li>';
                }
                
                $output .= '</ul>';
                break;
        }
        
        $output .= '</div>';
        
        return $output;
    }
}
