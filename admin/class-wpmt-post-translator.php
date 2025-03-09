<?php
/**
 * Handles post translation functionality.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class WPMT_Post_Translator {
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        
    }
    
    /**
     * Add translation meta boxes to post edit screens.
     *
     * @since    1.0.0
     */
    public function add_translation_meta_boxes() {
        // Get enabled languages
        $enabled_languages = WPMT_Utils::get_enabled_languages();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // If only one language is enabled, don't add meta boxes
        if (count($enabled_languages) <= 1) {
            return;
        }
        
        // Get post types to translate
        $post_types = get_post_types(array('public' => true), 'names');
        
        // Add meta box for each post type
        foreach ($post_types as $post_type) {
            add_meta_box(
                'wpmt_post_translations',
                __('Translations', 'wp-multilingual-translator'),
                array($this, 'render_translation_meta_box'),
                $post_type,
                'normal',
                'high'
            );
        }
    }
    
    /**
     * Render the translation meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_translation_meta_box($post) {
        // Get enabled languages
        $enabled_languages = WPMT_Utils::get_enabled_languages();
        $default_language = WPMT_Utils::get_option('default_language', 'en_US');
        
        // Add nonce for security
        wp_nonce_field('wpmt_save_post_translation', 'wpmt_post_translation_nonce');
        
        // Get current post language
        $post_language = get_post_meta($post->ID, '_wpmt_language', true);
        
        if (!$post_language) {
            $post_language = $default_language;
            update_post_meta($post->ID, '_wpmt_language', $post_language);
        }
        
        // Start output
        echo '<div class="wpmt-translations-wrapper">';
        
        // Language selector for current post
        echo '<div class="wpmt-post-language">';
        echo '<label for="wpmt_post_language">' . __('This post language:', 'wp-multilingual-translator') . '</label>';
        echo '<select name="wpmt_post_language" id="wpmt_post_language">';
        
        foreach ($enabled_languages as $locale => $language) {
            $selected = ($locale === $post_language) ? ' selected="selected"' : '';
            echo '<option value="' . esc_attr($locale) . '"' . $selected . '>' . esc_html($language['native_name']) . '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        
        // Translations section
        echo '<div class="wpmt-translations-list">';
        echo '<h4>' . __('Translations', 'wp-multilingual-translator') . '</h4>';
        
        // If this is a new post, show message
        if (!$post->ID || $post->post_status === 'auto-draft') {
            echo '<p>' . __('Please save this post first to add translations.', 'wp-multilingual-translator') . '</p>';
        } else {
            echo '<table class="wpmt-translations-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>' . __('Language', 'wp-multilingual-translator') . '</th>';
            echo '<th>' . __('Translation', 'wp-multilingual-translator') . '</th>';
            echo '<th>' . __('Status', 'wp-multilingual-translator') . '</th>';
            echo '<th>' . __('Actions', 'wp-multilingual-translator') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($enabled_languages as $locale => $language) {
                // Skip current post language
                if ($locale === $post_language) {
                    continue;
                }
                
                // Check if translation exists
                $translation = WPMT_Utils::get_translation($post->ID, 'post', $locale);
                $has_translation = !empty($translation);
                
                echo '<tr>';
                echo '<td>' . esc_html($language['native_name']) . '</td>';
                
                if ($has_translation) {
                    // Get translated post title
                    $translated_title = $translation['translated_title'];
                    
                    echo '<td>' . esc_html($translated_title) . '</td>';
                    echo '<td>' . esc_html(ucfirst($translation['status'])) . '</td>';
                    echo '<td>';
                    echo '<a href="#" class="button wpmt-edit-translation" data-language="' . esc_attr($locale) . '" data-post-id="' . esc_attr($post->ID) . '">' . __('Edit', 'wp-multilingual-translator') . '</a>';
                    echo ' <a href="#" class="button wpmt-delete-translation" data-language="' . esc_attr($locale) . '" data-post-id="' . esc_attr($post->ID) . '">' . __('Delete', 'wp-multilingual-translator') . '</a>';
                    echo '</td>';
                } else {
                    echo '<td>' . __('Not translated', 'wp-multilingual-translator') . '</td>';
                    echo '<td>-</td>';
                    echo '<td>';
                    echo '<a href="#" class="button wpmt-add-translation" data-language="' . esc_attr($locale) . '" data-post-id="' . esc_attr($post->ID) . '">' . __('Add Translation', 'wp-multilingual-translator') . '</a>';
                    echo '</td>';
                }
                
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
        }
        
        echo '</div>'; // .wpmt-translations-list
        
        // Translation editor (hidden by default)
        echo '<div class="wpmt-translation-editor" style="display: none;">';
        echo '<h4>' . __('Translation Editor', 'wp-multilingual-translator') . '</h4>';
        
        echo '<div class="wpmt-translation-form">';
        echo '<input type="hidden" name="wpmt_translation_language" id="wpmt_translation_language" value="">';
        
        echo '<div class="wpmt-form-field">';
        echo '<label for="wpmt_translated_title">' . __('Title', 'wp-multilingual-translator') . '</label>';
        echo '<input type="text" name="wpmt_translated_title" id="wpmt_translated_title" value="" class="widefat">';
        echo '</div>';
        
        echo '<div class="wpmt-form-field">';
        echo '<label for="wpmt_translated_content">' . __('Content', 'wp-multilingual-translator') . '</label>';
        wp_editor('', 'wpmt_translated_content', array(
            'media_buttons' => true,
            'textarea_name' => 'wpmt_translated_content',
            'textarea_rows' => 10,
            'editor_class' => 'wpmt-editor'
        ));
        echo '</div>';
        
        echo '<div class="wpmt-form-field">';
        echo '<label for="wpmt_translated_excerpt">' . __('Excerpt', 'wp-multilingual-translator') . '</label>';
        echo '<textarea name="wpmt_translated_excerpt" id="wpmt_translated_excerpt" rows="3" class="widefat"></textarea>';
        echo '</div>';
        
        echo '<div class="wpmt-form-field">';
        echo '<label for="wpmt_translated_slug">' . __('Slug', 'wp-multilingual-translator') . '</label>';
        echo '<input type="text" name="wpmt_translated_slug" id="wpmt_translated_slug" value="" class="widefat">';
        echo '</div>';
        
        echo '<div class="wpmt-form-field">';
        echo '<label for="wpmt_translation_status">' . __('Status', 'wp-multilingual-translator') . '</label>';
        echo '<select name="wpmt_translation_status" id="wpmt_translation_status">';
        echo '<option value="draft">' . __('Draft', 'wp-multilingual-translator') . '</option>';
        echo '<option value="published">' . __('Published', 'wp-multilingual-translator') . '</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div class="wpmt-form-actions">';
        echo '<button type="button" class="button button-primary wpmt-save-translation">' . __('Save Translation', 'wp-multilingual-translator') . '</button>';
        echo ' <button type="button" class="button wpmt-cancel-translation">' . __('Cancel', 'wp-multilingual-translator') . '</button>';
        echo '</div>';
        
        echo '</div>'; // .wpmt-translation-form
        echo '</div>'; // .wpmt-translation-editor
        
        echo '</div>'; // .wpmt-translations-wrapper
        
        // Add JavaScript for handling translation editor
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Add translation
                $('.wpmt-add-translation').on('click', function(e) {
                    e.preventDefault();
                    var language = $(this).data('language');
                    var postId = $(this).data('post-id');
                    
                    // Clear form
                    $('#wpmt_translated_title').val('');
                    $('#wpmt_translated_content').val('');
                    $('#wpmt_translated_excerpt').val('');
                    $('#wpmt_translated_slug').val('');
                    $('#wpmt_translation_status').val('draft');
                    
                    // Set language
                    $('#wpmt_translation_language').val(language);
                    
                    // Show editor
                    $('.wpmt-translations-list').hide();
                    $('.wpmt-translation-editor').show();
                });
                
                // Edit translation
                $('.wpmt-edit-translation').on('click', function(e) {
                    e.preventDefault();
                    var language = $(this).data('language');
                    var postId = $(this).data('post-id');
                    
                    // Set language
                    $('#wpmt_translation_language').val(language);
                    
                    // Load translation data via AJAX
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'wpmt_get_translation',
                            post_id: postId,
                            language: language,
                            nonce: wpmt_admin_vars.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                var translation = response.data;
                                
                                // Fill form
                                $('#wpmt_translated_title').val(translation.title);
                                
                                // Handle content editor
                                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('wpmt_translated_content')) {
                                    tinyMCE.get('wpmt_translated_content').setContent(translation.content);
                                } else {
                                    $('#wpmt_translated_content').val(translation.content);
                                }
                                
                                $('#wpmt_translated_excerpt').val(translation.excerpt);
                                $('#wpmt_translated_slug').val(translation.slug);
                                $('#wpmt_translation_status').val(translation.status);
                                
                                // Show editor
                                $('.wpmt-translations-list').hide();
                                $('.wpmt-translation-editor').show();
                            }
                        }
                    });
                });
                
                // Cancel translation
                $('.wpmt-cancel-translation').on('click', function(e) {
                    e.preventDefault();
                    
                    // Hide editor
                    $('.wpmt-translation-editor').hide();
                    $('.wpmt-translations-list').show();
                });
                
                // Save translation
                $('.wpmt-save-translation').on('click', function(e) {
                    e.preventDefault();
                    
                    var language = $('#wpmt_translation_language').val();
                    var postId = <?php echo intval($post->ID); ?>;
                    
                    // Get content from editor
                    var content = '';
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('wpmt_translated_content')) {
                        content = tinyMCE.get('wpmt_translated_content').getContent();
                    } else {
                        content = $('#wpmt_translated_content').val();
                    }
                    
                    // Save translation via AJAX
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'wpmt_save_translation',
                            post_id: postId,
                            language: language,
                            title: $('#wpmt_translated_title').val(),
                            content: content,
                            excerpt: $('#wpmt_translated_excerpt').val(),
                            slug: $('#wpmt_translated_slug').val(),
                            status: $('#wpmt_translation_status').val(),
                            nonce: wpmt_admin_vars.nonce
                        },
                        beforeSend: function() {
                            $('.wpmt-save-translation').text(wpmt_admin_vars.strings.saving);
                        },
                        success: function(response) {
                            if (response.success) {
                                // Reload page to show updated translations
                                location.reload();
                            } else {
                                alert(response.data.message || wpmt_admin_vars.strings.error);
                                $('.wpmt-save-translation').text(wpmt_admin_vars.strings.saved);
                            }
                        },
                        error: function() {
                            alert(wpmt_admin_vars.strings.error);
                            $('.wpmt-save-translation').text(wpmt_admin_vars.strings.saved);
                        }
                    });
                });
                
                // Delete translation
                $('.wpmt-delete-translation').on('click', function(e) {
                    e.preventDefault();
                    
                    if (!confirm(wpmt_admin_vars.strings.confirm_delete)) {
                        return;
                    }
                    
                    var language = $(this).data('language');
                    var postId = $(this).data('post-id');
                    
                    // Delete translation via AJAX
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'wpmt_delete_translation',
                            post_id: postId,
                            language: language,
                            nonce: wpmt_admin_vars.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                // Reload page to show updated translations
                                location.reload();
                            } else {
                                alert(response.data.message || wpmt_admin_vars.strings.error);
                            }
                        },
                        error: function() {
                            alert(wpmt_admin_vars.strings.error);
                        }
                    });
                });
            });
        </script>
        <?php
    }
    
    /**
     * Save post translation.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @param    WP_Post   $post       The post object.
     * @param    bool      $update     Whether this is an existing post being updated.
     */
    public function save_post_translation($post_id, $post, $update) {
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check if our nonce is set and verify it
        if (!isset($_POST['wpmt_post_translation_nonce']) || !wp_verify_nonce($_POST['wpmt_post_translation_nonce'], 'wpmt_save_post_translation')) {
            return;
        }
        
        // Save post language
        if (isset($_POST['wpmt_post_language'])) {
            $language = sanitize_text_field($_POST['wpmt_post_language']);
            update_post_meta($post_id, '_wpmt_language', $language);
        }
    }
    
    /**
     * AJAX handler for getting translation.
     *
     * @since    1.0.0
     */
    public function ajax_get_translation() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpmt_admin_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wp-multilingual-translator')));
        }
        
        // Check required parameters
        if (!isset($_POST['post_id']) || !isset($_POST['language'])) {
            wp_send_json_error(array('message' => __('Missing required parameters.', 'wp-multilingual-translator')));
        }
        
        $post_id = intval($_POST['post_id']);
        $language = sanitize_text_field($_POST['language']);
        
        // Get translation
        $translation = WPMT_Utils::get_translation($post_id, 'post', $language);
        
        if (!$translation) {
            wp_send_json_error(array('message' => __('Translation not found.', 'wp-multilingual-translator')));
        }
        
        // Prepare response
        $response = array(
            'title' => $translation['translated_title'],
            'content' => $translation['translated_content'],
            'excerpt' => $translation['translated_excerpt'],
            'slug' => $translation['translated_slug'],
            'status' => $translation['status']
        );
        
        wp_send_json_success($response);
    }
    
    /**
     * AJAX handler for saving translation.
     *
     * @since    1.0.0
     */
    public function ajax_save_translation() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpmt_admin_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wp-multilingual-translator')));
        }
        
        // Check required parameters
        if (!isset($_POST['post_id']) || !isset($_POST['language'])) {
            wp_send_json_error(array('message' => __('Missing required parameters.', 'wp-multilingual-translator')));
        }
        
        $post_id = intval($_POST['post_id']);
        $language = sanitize_text_field($_POST['language']);
        
        // Prepare translation data
        $translation_data = array(
            'translated_title' => isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '',
            'translated_content' => isset($_POST['content']) ? wp_kses_post($_POST['content']) : '',
            'translated_excerpt' => isset($_POST['excerpt']) ? sanitize_textarea_field($_POST['excerpt']) : '',
            'translated_slug' => isset($_POST['slug']) ? sanitize_title($_POST['slug']) : '',
            'status' => isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'draft'
        );
        
        // Save translation
        $result = WPMT_Utils::save_translation($post_id, 'post', $language, $translation_data);
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to save translation.', 'wp-multilingual-translator')));
        }
        
        wp_send_json_success(array('message' => __('Translation saved successfully.', 'wp-multilingual-translator')));
    }
    
    /**
     * AJAX handler for deleting translation.
     *
     * @since    1.0.0
     */
    public function ajax_delete_translation() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpmt_admin_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'wp-multilingual-translator')));
        }
        
        // Check required parameters
        if (!isset($_POST['post_id']) || !isset($_POST['language'])) {
            wp_send_json_error(array('message' => __('Missing required parameters.', 'wp-multilingual-translator')));
        }
        
        $post_id = intval($_POST['post_id']);
        $language = sanitize_text_field($_POST['language']);
        
        // Delete translation
        $result = WPMT_Utils::delete_translation($post_id, 'post', $language);
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to delete translation.', 'wp-multilingual-translator')));
        }
        
        wp_send_json_success(array('message' => __('Translation deleted successfully.', 'wp-multilingual-translator')));
    }
}
