<?php
/**
 * Provide a admin area view for the translation status page
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

// Get enabled languages
$enabled_languages = WPMT_Utils::get_enabled_languages();
$default_language = WPMT_Utils::get_option('default_language', 'en_US');

// Get post types
$post_types = get_post_types(array('public' => true), 'objects');

// Get current filter values
$current_post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'all';
$current_language = isset($_GET['language']) ? sanitize_text_field($_GET['language']) : '';
$current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

// Get translation status data
global $wpdb;
$table_name = $wpdb->prefix . 'wpmt_translations';

// Build query
$query = "SELECT p.ID, p.post_title, p.post_type, p.post_status, t.language_code, t.status AS translation_status
          FROM {$wpdb->posts} p
          LEFT JOIN $table_name t ON p.ID = t.object_id AND t.object_type = 'post'";

$where_clauses = array();
$where_clauses[] = "p.post_status IN ('publish', 'draft', 'pending')";

if ($current_post_type !== 'all') {
    $where_clauses[] = $wpdb->prepare("p.post_type = %s", $current_post_type);
}

if ($current_language !== '') {
    $where_clauses[] = $wpdb->prepare("t.language_code = %s", $current_language);
}

if ($current_status === 'translated') {
    $where_clauses[] = "t.id IS NOT NULL";
} elseif ($current_status === 'not_translated') {
    $where_clauses[] = "t.id IS NULL";
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

$query .= " ORDER BY p.post_title ASC";

$results = $wpdb->get_results($query);

// Group results by post
$posts = array();
foreach ($results as $result) {
    if (!isset($posts[$result->ID])) {
        $posts[$result->ID] = array(
            'ID' => $result->ID,
            'title' => $result->post_title,
            'type' => $result->post_type,
            'status' => $result->post_status,
            'translations' => array()
        );
    }
    
    if ($result->language_code) {
        $posts[$result->ID]['translations'][$result->language_code] = $result->translation_status;
    }
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="wpmt-admin-content">
        <h2><?php _e('Translation Status', 'wp-multilingual-translator'); ?></h2>
        <p><?php _e('View the translation status of your content.', 'wp-multilingual-translator'); ?></p>
        
        <div class="wpmt-status-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="wpmt-status">
                
                <select name="post_type">
                    <option value="all" <?php selected($current_post_type, 'all'); ?>><?php _e('All Post Types', 'wp-multilingual-translator'); ?></option>
                    <?php foreach ($post_types as $post_type) : ?>
                        <option value="<?php echo esc_attr($post_type->name); ?>" <?php selected($current_post_type, $post_type->name); ?>>
                            <?php echo esc_html($post_type->labels->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="language">
                    <option value="" <?php selected($current_language, ''); ?>><?php _e('All Languages', 'wp-multilingual-translator'); ?></option>
                    <?php foreach ($enabled_languages as $locale => $language) : ?>
                        <?php if ($locale !== $default_language) : ?>
                            <option value="<?php echo esc_attr($locale); ?>" <?php selected($current_language, $locale); ?>>
                                <?php echo esc_html($language['name']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                
                <select name="status">
                    <option value="all" <?php selected($current_status, 'all'); ?>><?php _e('All Statuses', 'wp-multilingual-translator'); ?></option>
                    <option value="translated" <?php selected($current_status, 'translated'); ?>><?php _e('Translated', 'wp-multilingual-translator'); ?></option>
                    <option value="not_translated" <?php selected($current_status, 'not_translated'); ?>><?php _e('Not Translated', 'wp-multilingual-translator'); ?></option>
                </select>
                
                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'wp-multilingual-translator'); ?>">
            </form>
        </div>
        
        <div class="wpmt-status-table">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="column-title"><?php _e('Title', 'wp-multilingual-translator'); ?></th>
                        <th class="column-type"><?php _e('Type', 'wp-multilingual-translator'); ?></th>
                        <th class="column-status"><?php _e('Status', 'wp-multilingual-translator'); ?></th>
                        <?php foreach ($enabled_languages as $locale => $language) : ?>
                            <?php if ($locale !== $default_language) : ?>
                                <th class="column-language">
                                    <?php if (file_exists(WPMT_PLUGIN_DIR . 'public/images/flags/' . $language['flag'] . '.png')) : ?>
                                        <img src="<?php echo esc_url(WPMT_PLUGIN_URL . 'public/images/flags/' . $language['flag'] . '.png'); ?>" alt="<?php echo esc_attr($language['name']); ?>" width="16" height="11">
                                    <?php endif; ?>
                                    <?php echo esc_html($language['name']); ?>
                                </th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <th class="column-actions"><?php _e('Actions', 'wp-multilingual-translator'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)) : ?>
                        <tr>
                            <td colspan="<?php echo 4 + count($enabled_languages) - 1; ?>"><?php _e('No content found.', 'wp-multilingual-translator'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($posts as $post) : ?>
                            <tr>
                                <td class="column-title">
                                    <?php echo esc_html($post['title']); ?>
                                </td>
                                <td class="column-type">
                                    <?php echo esc_html(isset($post_types[$post['type']]) ? $post_types[$post['type']]->labels->singular_name : $post['type']); ?>
                                </td>
                                <td class="column-status">
                                    <?php echo esc_html(ucfirst($post['status'])); ?>
                                </td>
                                <?php foreach ($enabled_languages as $locale => $language) : ?>
                                    <?php if ($locale !== $default_language) : ?>
                                        <td class="column-language">
                                            <?php if (isset($post['translations'][$locale])) : ?>
                                                <span class="translation-status translation-status-<?php echo esc_attr($post['translations'][$locale]); ?>">
                                                    <?php echo esc_html(ucfirst($post['translations'][$locale])); ?>
                                                </span>
                                            <?php else : ?>
                                                <span class="translation-status translation-status-missing">
                                                    <?php _e('Missing', 'wp-multilingual-translator'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <td class="column-actions">
                                    <a href="<?php echo esc_url(get_edit_post_link($post['ID'])); ?>" class="button button-small">
                                        <?php _e('Edit', 'wp-multilingual-translator'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="wpmt-admin-sidebar">
        <div class="wpmt-admin-box">
            <h3><?php _e('Translation Status', 'wp-multilingual-translator'); ?></h3>
            <p><?php _e('This page shows the translation status of your content.', 'wp-multilingual-translator'); ?></p>
            <p><?php _e('Use the filters to narrow down the results.', 'wp-multilingual-translator'); ?></p>
        </div>
        
        <div class="wpmt-admin-box">
            <h3><?php _e('Legend', 'wp-multilingual-translator'); ?></h3>
            <ul class="wpmt-status-legend">
                <li>
                    <span class="translation-status translation-status-published"><?php _e('Published', 'wp-multilingual-translator'); ?></span>
                    <?php _e('Translation is published and visible on the frontend.', 'wp-multilingual-translator'); ?>
                </li>
                <li>
                    <span class="translation-status translation-status-draft"><?php _e('Draft', 'wp-multilingual-translator'); ?></span>
                    <?php _e('Translation is saved as a draft and not visible on the frontend.', 'wp-multilingual-translator'); ?>
                </li>
                <li>
                    <span class="translation-status translation-status-missing"><?php _e('Missing', 'wp-multilingual-translator'); ?></span>
                    <?php _e('No translation exists for this content.', 'wp-multilingual-translator'); ?>
                </li>
            </ul>
        </div>
        
        <div class="wpmt-admin-box">
            <h3><?php _e('Languages', 'wp-multilingual-translator'); ?></h3>
            <p><?php _e('Manage the languages available for translation.', 'wp-multilingual-translator'); ?></p>
            <p><a href="<?php echo admin_url('admin.php?page=wpmt-languages'); ?>" class="button"><?php _e('Manage Languages', 'wp-multilingual-translator'); ?></a></p>
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
    
    .wpmt-status-filters {
        margin-bottom: 20px;
        padding: 10px;
        background: #f9f9f9;
        border: 1px solid #ccd0d4;
    }
    
    .wpmt-status-filters select {
        margin-right: 10px;
    }
    
    .wpmt-status-table {
        margin-top: 20px;
        margin-bottom: 20px;
    }
    
    .column-title {
        width: 25%;
    }
    
    .column-type,
    .column-status {
        width: 10%;
    }
    
    .column-language {
        width: 15%;
    }
    
    .column-actions {
        width: 10%;
        text-align: center;
    }
    
    .translation-status {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .translation-status-published {
        background-color: #dff0d8;
        color: #3c763d;
    }
    
    .translation-status-draft {
        background-color: #fcf8e3;
        color: #8a6d3b;
    }
    
    .translation-status-missing {
        background-color: #f2dede;
        color: #a94442;
    }
    
    .wpmt-status-legend {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .wpmt-status-legend li {
        margin-bottom: 10px;
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
