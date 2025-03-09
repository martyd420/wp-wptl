<?php
/**
 * Handles plugin settings functionality.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class WPMT_Settings {
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        
    }
    
    /**
     * Register plugin settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register settings
        register_setting(
            'wpmt_settings',
            'wpmt_options',
            array($this, 'sanitize_settings')
        );
        
        // General settings section
        add_settings_section(
            'wpmt_general_settings',
            __('General Settings', 'wp-multilingual-translator'),
            array($this, 'general_settings_section_callback'),
            'wpmt_settings'
        );
        
        // Default language setting
        add_settings_field(
            'wpmt_default_language',
            __('Default Language', 'wp-multilingual-translator'),
            array($this, 'default_language_callback'),
            'wpmt_settings',
            'wpmt_general_settings'
        );
        
        // Enabled languages setting
        add_settings_field(
            'wpmt_enabled_languages',
            __('Enabled Languages', 'wp-multilingual-translator'),
            array($this, 'enabled_languages_callback'),
            'wpmt_settings',
            'wpmt_general_settings'
        );
        
        // Display settings section
        add_settings_section(
            'wpmt_display_settings',
            __('Display Settings', 'wp-multilingual-translator'),
            array($this, 'display_settings_section_callback'),
            'wpmt_settings'
        );
        
        // Language switcher setting
        add_settings_field(
            'wpmt_display_language_switcher',
            __('Language Switcher', 'wp-multilingual-translator'),
            array($this, 'display_language_switcher_callback'),
            'wpmt_settings',
            'wpmt_display_settings'
        );
        
        // Language switcher style setting
        add_settings_field(
            'wpmt_language_switcher_style',
            __('Language Switcher Style', 'wp-multilingual-translator'),
            array($this, 'language_switcher_style_callback'),
            'wpmt_settings',
            'wpmt_display_settings'
        );
        
        // Translate slugs setting
        add_settings_field(
            'wpmt_translate_slugs',
            __('Translate Slugs', 'wp-multilingual-translator'),
            array($this, 'translate_slugs_callback'),
            'wpmt_settings',
            'wpmt_display_settings'
        );
        
        // Advanced settings section
        add_settings_section(
            'wpmt_advanced_settings',
            __('Advanced Settings', 'wp-multilingual-translator'),
            array($this, 'advanced_settings_section_callback'),
            'wpmt_settings'
        );
        
        // Auto translate setting
        add_settings_field(
            'wpmt_auto_translate',
            __('Auto Translate', 'wp-multilingual-translator'),
            array($this, 'auto_translate_callback'),
            'wpmt_settings',
            'wpmt_advanced_settings'
        );
        
        // Translation service setting
        add_settings_field(
            'wpmt_translation_service',
            __('Translation Service', 'wp-multilingual-translator'),
            array($this, 'translation_service_callback'),
            'wpmt_settings',
            'wpmt_advanced_settings'
        );
    }
    
    /**
     * Sanitize settings.
     *
     * @since    1.0.0
     * @param    array    $input    The settings input.
     * @return   array              The sanitized settings.
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();
        
        // Default language
        if (isset($input['default_language'])) {
            $sanitized_input['default_language'] = sanitize_text_field($input['default_language']);
        }
        
        // Enabled languages
        if (isset($input['enabled_languages']) && is_array($input['enabled_languages'])) {
            $sanitized_input['enabled_languages'] = array_map('sanitize_text_field', $input['enabled_languages']);
        }
        
        // Display language switcher
        if (isset($input['display_language_switcher'])) {
            $sanitized_input['display_language_switcher'] = (bool) $input['display_language_switcher'];
        }
        
        // Language switcher style
        if (isset($input['language_switcher_style'])) {
            $sanitized_input['language_switcher_style'] = sanitize_text_field($input['language_switcher_style']);
        }
        
        // Translate slugs
        if (isset($input['translate_slugs'])) {
            $sanitized_input['translate_slugs'] = (bool) $input['translate_slugs'];
        }
        
        // Auto translate
        if (isset($input['auto_translate'])) {
            $sanitized_input['auto_translate'] = (bool) $input['auto_translate'];
        }
        
        // Translation service
        if (isset($input['translation_service'])) {
            $sanitized_input['translation_service'] = sanitize_text_field($input['translation_service']);
        }
        
        return $sanitized_input;
    }
    
    /**
     * General settings section callback.
     *
     * @since    1.0.0
     */
    public function general_settings_section_callback() {
        echo '<p>' . __('Configure the general settings for the multilingual translator.', 'wp-multilingual-translator') . '</p>';
    }
    
    /**
     * Default language callback.
     *
     * @since    1.0.0
     */
    public function default_language_callback() {
        $options = WPMT_Utils::get_options();
        $default_language = isset($options['default_language']) ? $options['default_language'] : 'en_US';
        
        // Get available languages
        $available_languages = WPMT_Utils::get_available_languages();
        
        echo '<select name="wpmt_options[default_language]" id="wpmt_default_language">';
        
        foreach ($available_languages as $locale => $language) {
            $selected = ($locale === $default_language) ? ' selected="selected"' : '';
            echo '<option value="' . esc_attr($locale) . '"' . $selected . '>' . esc_html($language['name']) . ' (' . esc_html($language['native_name']) . ')</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . __('Select the default language for your website.', 'wp-multilingual-translator') . '</p>';
    }
    
    /**
     * Enabled languages callback.
     *
     * @since    1.0.0
     */
    public function enabled_languages_callback() {
        $options = WPMT_Utils::get_options();
        $enabled_languages = isset($options['enabled_languages']) ? $options['enabled_languages'] : array('en_US');
        
        // Get available languages
        $available_languages = WPMT_Utils::get_available_languages();
        
        echo '<div class="wpmt-enabled-languages">';
        
        foreach ($available_languages as $locale => $language) {
            $checked = in_array($locale, $enabled_languages) ? ' checked="checked"' : '';
            
            echo '<label>';
            echo '<input type="checkbox" name="wpmt_options[enabled_languages][]" value="' . esc_attr($locale) . '"' . $checked . '>';
            echo ' ' . esc_html($language['name']) . ' (' . esc_html($language['native_name']) . ')';
            echo '</label><br>';
        }
        
        echo '</div>';
        echo '<p class="description">' . __('Select the languages you want to enable for translation.', 'wp-multilingual-translator') . '</p>';
    }
    
    /**
     * Display settings section callback.
     *
     * @since    1.0.0
     */
    public function display_settings_section_callback() {
        echo '<p>' . __('Configure how translations are displayed on your website.', 'wp-multilingual-translator') . '</p>';
    }
    
    /**
     * Display language switcher callback.
     *
     * @since    1.0.0
     */
    public function display_language_switcher_callback() {
        $options = WPMT_Utils::get_options();
        $display_language_switcher = isset($options['display_language_switcher']) ? $options['display_language_switcher'] : true;
        
        echo '<label>';
        echo '<input type="checkbox" name="wpmt_options[display_language_switcher]" value="1"' . checked($display_language_switcher, true, false) . '>';
        echo ' ' . __('Display language switcher on the frontend', 'wp-multilingual-translator');
        echo '</label>';
        echo '<p class="description">' . __('If enabled, a language switcher will be displayed on your website.', 'wp-multilingual-translator') . '</p>';
    }
    
    /**
     * Language switcher style callback.
     *
     * @since    1.0.0
     */
    public function language_switcher_style_callback() {
        $options = WPMT_Utils::get_options();
        $language_switcher_style = isset($options['language_switcher_style']) ? $options['language_switcher_style'] : 'dropdown';
        
        $styles = array(
            'dropdown' => __('Dropdown', 'wp-multilingual-translator'),
            'list' => __('List', 'wp-multilingual-translator'),
            'flags' => __('Flags', 'wp-multilingual-translator')
        );
        
        echo '<select name="wpmt_options[language_switcher_style]" id="wpmt_language_switcher_style">';
        
        foreach ($styles as $value => $label) {
            $selected = ($value === $language_switcher_style) ? ' selected="selected"' : '';
            echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . __('Select the style of the language switcher.', 'wp-multilingual-translator') . '</p>';
    }
    
    /**
     * Translate slugs callback.
     *
     * @since    1.0.0
     */
    public function translate_slugs_callback() {
        $options = WPMT_Utils::get_options();
        $translate_slugs = isset($options['translate_slugs']) ? $options['translate_slugs'] : true;
        
        echo '<label>';
        echo '<input type="checkbox" name="wpmt_options[translate_slugs]" value="1"' . checked($translate_slugs, true, false) . '>';
        echo ' ' . __('Translate post and term slugs', 'wp-multilingual-translator');
        echo '</label>';
        echo '<p class="description">' . __('If enabled, post and term slugs will be translated.', 'wp-multilingual-translator') . '</p>';
    }
    
    /**
     * Advanced settings section callback.
     *
     * @since    1.0.0
     */
    public function advanced_settings_section_callback() {
        echo '<p>' . __('Configure advanced settings for the multilingual translator.', 'wp-multilingual-translator') . '</p>';
    }
    
    /**
     * Auto translate callback.
     *
     * @since    1.0.0
     */
    public function auto_translate_callback() {
        $options = WPMT_Utils::get_options();
        $auto_translate = isset($options['auto_translate']) ? $options['auto_translate'] : false;
        
        echo '<label>';
        echo '<input type="checkbox" name="wpmt_options[auto_translate]" value="1"' . checked($auto_translate, true, false) . '>';
        echo ' ' . __('Automatically translate content', 'wp-multilingual-translator');
        echo '</label>';
        echo '<p class="description">' . __('If enabled, content will be automatically translated when created or updated.', 'wp-multilingual-translator') . '</p>';
    }
    
    /**
     * Translation service callback.
     *
     * @since    1.0.0
     */
    public function translation_service_callback() {
        $options = WPMT_Utils::get_options();
        $translation_service = isset($options['translation_service']) ? $options['translation_service'] : 'none';
        
        $services = array(
            'none' => __('None', 'wp-multilingual-translator'),
            'google' => __('Google Translate API', 'wp-multilingual-translator'),
            'deepl' => __('DeepL API', 'wp-multilingual-translator')
        );
        
        echo '<select name="wpmt_options[translation_service]" id="wpmt_translation_service">';
        
        foreach ($services as $value => $label) {
            $selected = ($value === $translation_service) ? ' selected="selected"' : '';
            echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">' . __('Select the translation service to use for automatic translation.', 'wp-multilingual-translator') . '</p>';
        
        // API key field
        echo '<div id="wpmt_api_key_field" style="margin-top: 10px; display: none;">';
        echo '<label for="wpmt_api_key">' . __('API Key', 'wp-multilingual-translator') . '</label><br>';
        echo '<input type="text" name="wpmt_options[api_key]" id="wpmt_api_key" value="' . esc_attr(isset($options['api_key']) ? $options['api_key'] : '') . '" class="regular-text">';
        echo '<p class="description">' . __('Enter your API key for the selected translation service.', 'wp-multilingual-translator') . '</p>';
        echo '</div>';
        
        // JavaScript to show/hide API key field
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Show/hide API key field based on selected translation service
                function toggleApiKeyField() {
                    var service = $('#wpmt_translation_service').val();
                    
                    if (service === 'none') {
                        $('#wpmt_api_key_field').hide();
                    } else {
                        $('#wpmt_api_key_field').show();
                    }
                }
                
                // Initial state
                toggleApiKeyField();
                
                // On change
                $('#wpmt_translation_service').on('change', toggleApiKeyField);
            });
        </script>
        <?php
    }
}
