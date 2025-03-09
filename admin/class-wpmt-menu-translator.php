<?php
/**
 * Handles menu translation functionality.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class WPMT_Menu_Translator {
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        
    }
    
    /**
     * Add menu translation fields to the menu edit screen.
     *
     * @since    1.0.0
     */
    public function add_menu_translation_fields() {
        // Get enabled languages
        $enabled_languages = WPMT_Utils::get_enabled_languages();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If only one language is enabled, don't add translation fields
        if (count($enabled_languages) <= 1) {
            return;
        }
        
        // Add translation fields to menu items
        add_filter('wp_setup_nav_menu_item', array($this, 'setup_nav_menu_item'));
        
        // Add custom fields to menu items
        add_action('wp_nav_menu_item_custom_fields', array($this, 'menu_item_translation_fields'), 10, 4);
        
        // Add JavaScript to handle menu translations
        add_action('admin_footer', array($this, 'menu_translation_javascript'));
    }
    
    /**
     * Setup nav menu item with translation data.
     *
     * @since    1.0.0
     * @param    object    $menu_item    The menu item object.
     * @return   object                  The modified menu item object.
     */
    public function setup_nav_menu_item($menu_item) {
        // Get current language
        $current_language = WPMT_Utils::get_current_language();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If we're in the admin, or if we're in the default language, return the original menu item
        if (is_admin() || $current_language === $default_language) {
            return $menu_item;
        }
        
        // Get translation for this menu item
        $translation = WPMT_Utils::get_translation($menu_item->ID, 'menu_item', $current_language);
        
        // If translation exists, update the menu item title and link text
        if ($translation) {
            if (!empty($translation['translated_title'])) {
                $menu_item->title = $translation['translated_title'];
            }
            
            if (!empty($translation['translated_attr_title'])) {
                $menu_item->attr_title = $translation['translated_attr_title'];
            }
            
            if (!empty($translation['translated_description'])) {
                $menu_item->description = $translation['translated_description'];
            }
        }
        
        return $menu_item;
    }
    
    /**
     * Add translation fields to menu items.
     *
     * @since    1.0.0
     * @param    int       $item_id          Menu item ID.
     * @param    object    $item             Menu item data object.
     * @param    int       $depth            Depth of menu item.
     * @param    array     $args             Menu item args.
     */
    public function menu_item_translation_fields($item_id, $item, $depth, $args) {
        // Get enabled languages
        $enabled_languages = WPMT_Utils::get_enabled_languages();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If only one language is enabled, don't add translation fields
        if (count($enabled_languages) <= 1) {
            return;
        }
        
        // Add nonce for security
        wp_nonce_field('wpmt_save_menu_translation_' . $item_id, 'wpmt_menu_translation_nonce_' . $item_id);
        
        // Start output
        echo '<div class="wpmt-menu-translations">';
        echo '<p class="description description-wide">';
        echo '<label for="wpmt-menu-translations-toggle-' . esc_attr($item_id) . '">';
        echo '<input type="checkbox" id="wpmt-menu-translations-toggle-' . esc_attr($item_id) . '" class="wpmt-menu-translations-toggle">';
        echo ' ' . __('Show translations', 'wp-multilingual-translator');
        echo '</label>';
        echo '</p>';
        
        echo '<div class="wpmt-menu-translations-fields" style="display: none;">';
        
        foreach ($enabled_languages as $locale => $language) {
            // Skip default language
            if ($locale === $default_language) {
                continue;
            }
            
            // Get translation for this menu item
            $translation = WPMT_Utils::get_translation($item_id, 'menu_item', $locale);
            
            // Get translated values
            $translated_title = !empty($translation['translated_title']) ? $translation['translated_title'] : '';
            $translated_attr_title = !empty($translation['translated_attr_title']) ? $translation['translated_attr_title'] : '';
            $translated_description = !empty($translation['translated_description']) ? $translation['translated_description'] : '';
            
            // Language heading
            echo '<h4>' . esc_html($language['native_name']) . '</h4>';
            
            // Title field
            echo '<p class="description description-wide">';
            echo '<label for="wpmt-menu-item-title-' . esc_attr($item_id) . '-' . esc_attr($locale) . '">';
            echo __('Title', 'wp-multilingual-translator') . '<br>';
            echo '<input type="text" id="wpmt-menu-item-title-' . esc_attr($item_id) . '-' . esc_attr($locale) . '" class="widefat" name="wpmt-menu-item-title[' . esc_attr($item_id) . '][' . esc_attr($locale) . ']" value="' . esc_attr($translated_title) . '">';
            echo '</label>';
            echo '</p>';
            
            // Title attribute field
            echo '<p class="description description-wide">';
            echo '<label for="wpmt-menu-item-attr-title-' . esc_attr($item_id) . '-' . esc_attr($locale) . '">';
            echo __('Title Attribute', 'wp-multilingual-translator') . '<br>';
            echo '<input type="text" id="wpmt-menu-item-attr-title-' . esc_attr($item_id) . '-' . esc_attr($locale) . '" class="widefat" name="wpmt-menu-item-attr-title[' . esc_attr($item_id) . '][' . esc_attr($locale) . ']" value="' . esc_attr($translated_attr_title) . '">';
            echo '</label>';
            echo '</p>';
            
            // Description field
            echo '<p class="description description-wide">';
            echo '<label for="wpmt-menu-item-description-' . esc_attr($item_id) . '-' . esc_attr($locale) . '">';
            echo __('Description', 'wp-multilingual-translator') . '<br>';
            echo '<textarea id="wpmt-menu-item-description-' . esc_attr($item_id) . '-' . esc_attr($locale) . '" class="widefat" name="wpmt-menu-item-description[' . esc_attr($item_id) . '][' . esc_attr($locale) . ']" rows="3">' . esc_textarea($translated_description) . '</textarea>';
            echo '</label>';
            echo '</p>';
        }
        
        echo '</div>'; // .wpmt-menu-translations-fields
        echo '</div>'; // .wpmt-menu-translations
    }
    
    /**
     * Add JavaScript to handle menu translations.
     *
     * @since    1.0.0
     */
    public function menu_translation_javascript() {
        // Only add on nav-menus.php page
        $screen = get_current_screen();
        
        if (!$screen || $screen->base !== 'nav-menus') {
            return;
        }
        
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Toggle translation fields
                $(document).on('click', '.wpmt-menu-translations-toggle', function() {
                    var fields = $(this).closest('.wpmt-menu-translations').find('.wpmt-menu-translations-fields');
                    
                    if ($(this).is(':checked')) {
                        fields.slideDown();
                    } else {
                        fields.slideUp();
                    }
                });
                
                // Initialize toggles
                $('.wpmt-menu-translations-toggle').each(function() {
                    var fields = $(this).closest('.wpmt-menu-translations').find('.wpmt-menu-translations-fields');
                    
                    if ($(this).is(':checked')) {
                        fields.show();
                    } else {
                        fields.hide();
                    }
                });
            });
        </script>
        <?php
    }
    
    /**
     * Save menu translations.
     *
     * @since    1.0.0
     * @param    int       $menu_id         Menu ID.
     * @param    int       $menu_item_db_id Menu item ID.
     */
    public function save_menu_translations($menu_id, $menu_item_db_id) {
        // Get enabled languages
        $enabled_languages = WPMT_Utils::get_enabled_languages();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If only one language is enabled, don't save translations
        if (count($enabled_languages) <= 1) {
            return;
        }
        
        // Check if our nonce is set and verify it
        if (!isset($_POST['wpmt_menu_translation_nonce_' . $menu_item_db_id]) || !wp_verify_nonce($_POST['wpmt_menu_translation_nonce_' . $menu_item_db_id], 'wpmt_save_menu_translation_' . $menu_item_db_id)) {
            return;
        }
        
        // Save translations for each language
        foreach ($enabled_languages as $locale => $language) {
            // Skip default language
            if ($locale === $default_language) {
                continue;
            }
            
            // Get translation data from POST
            $translated_title = isset($_POST['wpmt-menu-item-title'][$menu_item_db_id][$locale]) ? sanitize_text_field($_POST['wpmt-menu-item-title'][$menu_item_db_id][$locale]) : '';
            $translated_attr_title = isset($_POST['wpmt-menu-item-attr-title'][$menu_item_db_id][$locale]) ? sanitize_text_field($_POST['wpmt-menu-item-attr-title'][$menu_item_db_id][$locale]) : '';
            $translated_description = isset($_POST['wpmt-menu-item-description'][$menu_item_db_id][$locale]) ? sanitize_textarea_field($_POST['wpmt-menu-item-description'][$menu_item_db_id][$locale]) : '';
            
            // Prepare translation data
            $translation_data = array(
                'translated_title' => $translated_title,
                'translated_attr_title' => $translated_attr_title,
                'translated_description' => $translated_description,
                'status' => 'published'
            );
            
            // Save translation only if at least one field is not empty
            if (!empty($translated_title) || !empty($translated_attr_title) || !empty($translated_description)) {
                WPMT_Utils::save_translation($menu_item_db_id, 'menu_item', $locale, $translation_data);
            } else {
                // If all fields are empty, delete the translation
                WPMT_Utils::delete_translation($menu_item_db_id, 'menu_item', $locale);
            }
        }
    }
}
