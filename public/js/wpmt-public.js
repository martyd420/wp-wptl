/**
 * Public JavaScript for the WP Multilingual Translator plugin.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

(function($) {
    'use strict';

    /**
     * Initialize public functionality.
     */
    function initPublic() {
        // Initialize language switcher
        initLanguageSwitcher();
        
        // Initialize AJAX content loading
        initAjaxContent();
    }

    /**
     * Initialize language switcher functionality.
     */
    function initLanguageSwitcher() {
        // Handle dropdown language switcher
        $('.wpmt-language-select').on('change', function() {
            var url = $(this).val();
            if (url) {
                window.location.href = url;
            }
        });
        
        // Handle language switcher links
        $('.wpmt-language-list a, .wpmt-language-flags a').on('click', function(e) {
            // Allow normal link behavior
            // The URL is already set in the href attribute
        });
    }

    /**
     * Initialize AJAX content loading functionality.
     */
    function initAjaxContent() {
        // If AJAX content loading is enabled
        if (typeof wpmt_public_vars !== 'undefined' && wpmt_public_vars.ajax_content) {
            // Handle AJAX content loading for links
            $(document).on('click', 'a.wpmt-ajax-link', function(e) {
                e.preventDefault();
                
                var url = $(this).attr('href');
                var target = $(this).data('target') || '#content';
                
                // Show loading indicator
                $(target).addClass('wpmt-loading');
                
                // Load content via AJAX
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        // Extract content from response
                        var content = $(response).find(target).html();
                        
                        // Update content
                        $(target).html(content);
                        
                        // Update browser history
                        if (window.history && window.history.pushState) {
                            window.history.pushState({}, document.title, url);
                        }
                        
                        // Remove loading indicator
                        $(target).removeClass('wpmt-loading');
                        
                        // Trigger event for other scripts
                        $(document).trigger('wpmt-content-loaded', [target]);
                    },
                    error: function() {
                        // On error, redirect to the URL
                        window.location.href = url;
                    }
                });
            });
        }
    }

    /**
     * Add language parameter to URL.
     *
     * @param {string} url      The URL to modify.
     * @param {string} language The language code to add.
     * @return {string}         The modified URL.
     */
    function addLanguageToUrl(url, language) {
        if (!language) {
            language = wpmt_public_vars.current_language;
        }
        
        // If it's the default language, don't add the parameter
        if (language === wpmt_public_vars.default_language) {
            return url;
        }
        
        // Parse the URL
        var parser = document.createElement('a');
        parser.href = url;
        
        // Get query parameters
        var params = {};
        var query = parser.search.substring(1);
        var vars = query.split('&');
        
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            if (pair[0]) {
                params[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
            }
        }
        
        // Add language parameter
        params.lang = language;
        
        // Build query string
        var queryString = '';
        for (var key in params) {
            if (params.hasOwnProperty(key)) {
                if (queryString.length > 0) {
                    queryString += '&';
                }
                queryString += encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
            }
        }
        
        // Build URL
        var newUrl = parser.protocol + '//' + parser.host + parser.pathname;
        if (queryString.length > 0) {
            newUrl += '?' + queryString;
        }
        if (parser.hash) {
            newUrl += parser.hash;
        }
        
        return newUrl;
    }

    /**
     * Get current language.
     *
     * @return {string} The current language code.
     */
    function getCurrentLanguage() {
        return wpmt_public_vars.current_language;
    }

    /**
     * Get default language.
     *
     * @return {string} The default language code.
     */
    function getDefaultLanguage() {
        return wpmt_public_vars.default_language;
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initPublic();
    });

    // Expose public functions
    window.wpmt = {
        addLanguageToUrl: addLanguageToUrl,
        getCurrentLanguage: getCurrentLanguage,
        getDefaultLanguage: getDefaultLanguage
    };

})(jQuery);
