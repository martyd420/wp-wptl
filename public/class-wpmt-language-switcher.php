<?php
/**
 * Handles the language switcher functionality.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class WPMT_Language_Switcher {
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        
    }
    
    /**
     * Display the language switcher.
     *
     * @since    1.0.0
     */
    public function display_language_switcher() {
        // Check if language switcher is enabled
        if (!WPMT_Utils::get_option('display_language_switcher', true)) {
            return;
        }
        
        // Get enabled languages
        $enabled_languages = WPMT_Utils::get_enabled_languages();
        
        // If only one language is enabled, don't display the switcher
        if (count($enabled_languages) <= 1) {
            return;
        }
        
        // Get language switcher style
        $style = WPMT_Utils::get_option('language_switcher_style', 'dropdown');
        
        // Display the language switcher
        echo WPMT_Utils::get_language_switcher_html($style);
    }
    
    /**
     * Add language switcher widget to text widgets.
     *
     * @since    1.0.0
     * @param    string    $content    The widget content.
     * @return   string                The modified widget content.
     */
    public function language_switcher_widget($content) {
        // Replace [wpmt_language_switcher] shortcode with the language switcher
        if (strpos($content, '[wpmt_language_switcher]') !== false) {
            // Get language switcher style
            $style = WPMT_Utils::get_option('language_switcher_style', 'dropdown');
            
            // Get language switcher HTML
            $language_switcher = WPMT_Utils::get_language_switcher_html($style);
            
            // Replace shortcode with language switcher
            $content = str_replace('[wpmt_language_switcher]', $language_switcher, $content);
        }
        
        return $content;
    }
    
    /**
     * Register language switcher widget.
     *
     * @since    1.0.0
     */
    public function register_language_switcher_widget() {
        register_widget('WPMT_Language_Switcher_Widget');
    }
}

/**
 * Language Switcher Widget.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */
class WPMT_Language_Switcher_Widget extends WP_Widget {
    /**
     * Register widget with WordPress.
     *
     * @since    1.0.0
     */
    public function __construct() {
        parent::__construct(
            'wpmt_language_switcher_widget',
            __('Language Switcher', 'wp-multilingual-translator'),
            array(
                'description' => __('Displays a language switcher for your multilingual content.', 'wp-multilingual-translator'),
            )
        );
    }
    
    /**
     * Front-end display of widget.
     *
     * @since    1.0.0
     * @param    array    $args        Widget arguments.
     * @param    array    $instance    Saved values from database.
     */
    public function widget($args, $instance) {
        // Check if language switcher is enabled
        if (!WPMT_Utils::get_option('display_language_switcher', true)) {
            return;
        }
        
        // Get enabled languages
        $enabled_languages = WPMT_Utils::get_enabled_languages();
        
        // If only one language is enabled, don't display the switcher
        if (count($enabled_languages) <= 1) {
            return;
        }
        
        // Get widget title
        $title = !empty($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        
        // Get language switcher style
        $style = !empty($instance['style']) ? $instance['style'] : WPMT_Utils::get_option('language_switcher_style', 'dropdown');
        
        // Display the widget
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        // Display the language switcher
        echo WPMT_Utils::get_language_switcher_html($style);
        
        echo $args['after_widget'];
    }
    
    /**
     * Back-end widget form.
     *
     * @since    1.0.0
     * @param    array    $instance    Previously saved values from database.
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Language Switcher', 'wp-multilingual-translator');
        $style = !empty($instance['style']) ? $instance['style'] : WPMT_Utils::get_option('language_switcher_style', 'dropdown');
        
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'wp-multilingual-translator'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('style')); ?>"><?php esc_html_e('Style:', 'wp-multilingual-translator'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('style')); ?>" name="<?php echo esc_attr($this->get_field_name('style')); ?>">
                <option value="dropdown" <?php selected($style, 'dropdown'); ?>><?php esc_html_e('Dropdown', 'wp-multilingual-translator'); ?></option>
                <option value="list" <?php selected($style, 'list'); ?>><?php esc_html_e('List', 'wp-multilingual-translator'); ?></option>
                <option value="flags" <?php selected($style, 'flags'); ?>><?php esc_html_e('Flags', 'wp-multilingual-translator'); ?></option>
            </select>
        </p>
        <?php
    }
    
    /**
     * Sanitize widget form values as they are saved.
     *
     * @since    1.0.0
     * @param    array    $new_instance    Values just sent to be saved.
     * @param    array    $old_instance    Previously saved values from database.
     * @return   array                     Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['style'] = (!empty($new_instance['style'])) ? sanitize_text_field($new_instance['style']) : 'dropdown';
        
        return $instance;
    }
}
