/**
 * Admin JavaScript for the WP Multilingual Translator plugin.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

(function($) {
    'use strict';

    /**
     * Initialize admin functionality.
     */
    function initAdmin() {
        // Initialize AJAX handlers for post translations
        initPostTranslations();
        
        // Initialize AJAX handlers for term translations
        initTermTranslations();
        
        // Initialize menu translations
        initMenuTranslations();
        
        // Initialize settings page
        initSettings();
    }

    /**
     * Initialize post translations functionality.
     */
    function initPostTranslations() {
        // Add translation button
        $(document).on('click', '.wpmt-add-translation', function(e) {
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
        
        // Edit translation button
        $(document).on('click', '.wpmt-edit-translation', function(e) {
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
        
        // Cancel translation button
        $(document).on('click', '.wpmt-cancel-translation', function(e) {
            e.preventDefault();
            
            // Hide editor
            $('.wpmt-translation-editor').hide();
            $('.wpmt-translations-list').show();
        });
        
        // Save translation button
        $(document).on('click', '.wpmt-save-translation', function(e) {
            e.preventDefault();
            
            var language = $('#wpmt_translation_language').val();
            var postId = $('#post_ID').val();
            
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
        
        // Delete translation button
        $(document).on('click', '.wpmt-delete-translation', function(e) {
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
    }

    /**
     * Initialize term translations functionality.
     */
    function initTermTranslations() {
        // Similar to post translations, but for terms
        // This would be implemented if term translation UI is added
    }

    /**
     * Initialize menu translations functionality.
     */
    function initMenuTranslations() {
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
    }

    /**
     * Initialize settings page functionality.
     */
    function initSettings() {
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
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initAdmin();
    });

})(jQuery);
