<?php
/**
 * Provide a admin area view for the languages page
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Multilingual_Translator
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Get available and enabled languages
$available_languages = WPMT_Utils::get_available_languages();
$enabled_languages = WPMT_Utils::get_enabled_languages();
$default_language = WPMT_Utils::get_option('default_language', 'en_US');

// Handle form submission
if (isset($_POST['wpmt_update_languages']) && isset($_POST['wpmt_languages_nonce']) && wp_verify_nonce($_POST['wpmt_languages_nonce'], 'wpmt_update_languages')) {
    $new_enabled_languages = isset($_POST['wpmt_enabled_languages']) ? (array) $_POST['wpmt_enabled_languages'] : array();
    $new_default_language = isset($_POST['wpmt_default_language']) ? sanitize_text_field($_POST['wpmt_default_language']) : 'en_US';
    
    // Make sure default language is in enabled languages
    if (!in_array($new_default_language, $new_enabled_languages)) {
        $new_enabled_languages[] = $new_default_language;
    }
    
    // Update options
    WPMT_Utils::update_options(array(
        'enabled_languages' => $new_enabled_languages,
        'default_language' => $new_default_language
    ));
    
    // Show success message
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Languages updated successfully.', 'wp-multilingual-translator') . '</p></div>';
    
    // Refresh language data
    $enabled_languages = WPMT_Utils::get_enabled_languages();
    $default_language = WPMT_Utils::get_option('default_language', 'en_US');
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="wpmt-admin-content">
        <form method="post" action="">
            <?php wp_nonce_field('wpmt_update_languages', 'wpmt_languages_nonce'); ?>
            <input type="hidden" name="wpmt_update_languages" value="1">
            
            <h2><?php _e('Default Language', 'wp-multilingual-translator'); ?></h2>
            <p><?php _e('Select the default language for your website. This is the language that your content is primarily written in.', 'wp-multilingual-translator'); ?></p>
            
            <select name="wpmt_default_language" id="wpmt_default_language">
                <?php foreach ($available_languages as $locale => $language) : ?>
                    <option value="<?php echo esc_attr($locale); ?>" <?php selected($locale, $default_language); ?>>
                        <?php echo esc_html($language['name'] . ' (' . $language['native_name'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <h2><?php _e('Enabled Languages', 'wp-multilingual-translator'); ?></h2>
            <p><?php _e('Select the languages you want to enable for translation. The default language will always be enabled.', 'wp-multilingual-translator'); ?></p>
            
            <div class="wpmt-languages-list">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th class="column-enabled"><?php _e('Enabled', 'wp-multilingual-translator'); ?></th>
                            <th class="column-flag"><?php _e('Flag', 'wp-multilingual-translator'); ?></th>
                            <th class="column-name"><?php _e('Language', 'wp-multilingual-translator'); ?></th>
                            <th class="column-code"><?php _e('Code', 'wp-multilingual-translator'); ?></th>
                            <th class="column-default"><?php _e('Default', 'wp-multilingual-translator'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($available_languages as $locale => $language) : ?>
                            <tr>
                                <td class="column-enabled">
                                    <input type="checkbox" name="wpmt_enabled_languages[]" value="<?php echo esc_attr($locale); ?>" <?php checked(isset($enabled_languages[$locale])); ?> <?php disabled($locale, $default_language); ?>>
                                </td>
                                <td class="column-flag">
                                    <?php if (file_exists(WPMT_PLUGIN_DIR . 'public/images/flags/' . $language['flag'] . '.png')) : ?>
                                        <img src="<?php echo esc_url(WPMT_PLUGIN_URL . 'public/images/flags/' . $language['flag'] . '.png'); ?>" alt="<?php echo esc_attr($language['name']); ?>" width="24" height="16">
                                    <?php else : ?>
                                        <span class="dashicons dashicons-flag"></span>
                                    <?php endif; ?>
                                </td>
                                <td class="column-name">
                                    <?php echo esc_html($language['name']); ?>
                                    <div class="row-actions">
                                        <span class="native"><?php echo esc_html($language['native_name']); ?></span>
                                    </div>
                                </td>
                                <td class="column-code"><?php echo esc_html($locale); ?></td>
                                <td class="column-default">
                                    <?php if ($locale === $default_language) : ?>
                                        <span class="dashicons dashicons-yes"></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php submit_button(__('Save Changes', 'wp-multilingual-translator')); ?>
        </form>
    </div>
    
    <div class="wpmt-admin-sidebar">
        <div class="wpmt-admin-box">
            <h3><?php _e('Language Management', 'wp-multilingual-translator'); ?></h3>
            <p><?php _e('Enable the languages you want to use for translation. The default language will always be enabled.', 'wp-multilingual-translator'); ?></p>
            <p><?php _e('You can add more languages to WordPress by installing language packs.', 'wp-multilingual-translator'); ?></p>
            <p><a href="<?php echo admin_url('options-general.php'); ?>" class="button"><?php _e('WordPress Settings', 'wp-multilingual-translator'); ?></a></p>
        </div>
        
        <div class="wpmt-admin-box">
            <h3><?php _e('Translation Status', 'wp-multilingual-translator'); ?></h3>
            <p><?php _e('Check the translation status of your content.', 'wp-multilingual-translator'); ?></p>
            <p><a href="<?php echo admin_url('admin.php?page=wpmt-status'); ?>" class="button"><?php _e('View Translation Status', 'wp-multilingual-translator'); ?></a></p>
        </div>
    </div>
</div>

<style>
    .wpmt-admin-content {
        float: left;
        width: 65%;
        margin-right: 5%;
    }
    
    .wpmt-admin-sidebar {
        float: left;
        width: 30%;
    }
    
    .wpmt-admin-box {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        margin-bottom: 20px;
        padding: 15px;
    }
    
    .wpmt-admin-box h3 {
        margin-top: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    .wpmt-languages-list {
        margin-top: 20px;
        margin-bottom: 20px;
    }
    
    .column-enabled,
    .column-flag,
    .column-default {
        width: 10%;
        text-align: center;
    }
    
    .column-code {
        width: 15%;
    }
    
    .column-name {
        width: 55%;
    }
    
    @media screen and (max-width: 782px) {
        .wpmt-admin-content,
        .wpmt-admin-sidebar {
            float: none;
            width: 100%;
            margin-right: 0;
        }
        
        .wpmt-admin-content {
            margin-bottom: 30px;
        }
    }
</style>
