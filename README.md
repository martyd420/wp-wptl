# WP Multilingual Translator

A WordPress plugin for translating all types of posts, pages, custom post types, menus, and content into multiple languages.

## Features

- Translate all types of posts including custom post types
- Translate navigation menus
- Translate categories, tags, and custom taxonomies
- Language switcher with multiple display options (dropdown, list, flags)
- Support for all languages available in WordPress
- Edit translations directly from the WordPress admin interface
- Translation status overview
- No duplicate content - all translations are stored in a separate database table
- SEO friendly URLs with language parameter
- Compatible with all themes and plugins

## Installation

1. Upload the `wp-multilingual-translator` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Translations' > 'Settings' to configure the plugin
4. Select your default language and enable the languages you want to use for translation

## Usage

### Setting Up Languages

1. Go to 'Translations' > 'Languages'
2. Select your default language (the language your content is primarily written in)
3. Enable the languages you want to use for translation
4. Click 'Save Changes'

### Translating Posts and Pages

1. Edit a post or page
2. Scroll down to the 'Translations' meta box
3. Click 'Add Translation' for the language you want to translate to
4. Enter the translated title, content, excerpt, and slug
5. Select the translation status (Draft or Published)
6. Click 'Save Translation'

### Translating Menus

1. Go to 'Appearance' > 'Menus'
2. Edit a menu item
3. Check 'Show translations'
4. Enter the translated title, title attribute, and description for each language
5. Click 'Save Menu'

### Adding a Language Switcher

You can add a language switcher to your website in several ways:

1. **Widget**: Go to 'Appearance' > 'Widgets' and add the 'Language Switcher' widget to a widget area
2. **Shortcode**: Use the `[wpmt_language_switcher]` shortcode in your posts, pages, or text widgets
3. **PHP Function**: Add `<?php echo WPMT_Utils::get_language_switcher_html(); ?>` to your theme files

### Customizing the Language Switcher

1. Go to 'Translations' > 'Settings'
2. Under 'Display Settings', select the language switcher style (dropdown, list, or flags)
3. Save your changes

## Advanced Settings

### Translation Services

The plugin supports automatic translation using external translation services:

1. Go to 'Translations' > 'Settings'
2. Under 'Advanced Settings', select a translation service
3. Enter your API key for the selected service
4. Enable 'Automatically translate content' if you want to use automatic translation

### Translation Status

You can view the translation status of all your content:

1. Go to 'Translations' > 'Translation Status'
2. Use the filters to narrow down the results
3. Click 'Edit' to edit a post or page and its translations

## FAQ

### Can I translate custom post types?

Yes, the plugin supports translation of all post types, including custom post types.

### Can I translate menus?

Yes, you can translate menu items, including the title, title attribute, and description.

### Can I translate categories and tags?

Yes, you can translate all taxonomies, including categories, tags, and custom taxonomies.

### Can I translate widgets?

Yes, you can translate widget titles and content.

### Can I translate theme strings?

Yes, you can translate theme strings using the plugin's string translation feature.

### Can I use the plugin with any theme?

Yes, the plugin is compatible with all WordPress themes.

### Can I use the plugin with other plugins?

Yes, the plugin is designed to be compatible with other WordPress plugins.

## Support

If you have any questions or issues, please contact us at support@example.com.

## License

This plugin is licensed under the GPL v2 or later.
