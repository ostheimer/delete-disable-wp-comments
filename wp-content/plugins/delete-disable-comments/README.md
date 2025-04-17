=== Delete & Disable Comments ===
Contributors: helpstring
Tags: comments, spam, delete, disable, backup
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.1
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin that helps to manage comments by deleting spam comments, removing all comments with backup, or disabling comments site-wide.

== Description ==

Delete & Disable Comments is a powerful tool for WordPress administrators to efficiently manage their site's comments. With this plugin, you can:

* Delete all spam comments with one click
* Delete all comments (with option to create a backup first)
* Disable comments site-wide
* Download a backup of your comments before deletion

The plugin is designed to be simple to use while providing robust functionality for comment management.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/delete-disable-comments` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Delete & Disable Comments screen to configure the plugin

== Frequently Asked Questions ==

= Can I recover deleted comments? =

Yes, if you create a backup before deletion. The plugin offers a backup option before performing any deletion operation.

= Will disabling comments affect existing comments? =

No, disabling comments only prevents new comments from being added. Existing comments remain visible unless you explicitly delete them.

== Screenshots ==

1. Main plugin interface
2. Comment backup and deletion confirmation
3. Site-wide comment disable option

== Changelog ==

= 1.0.1 =
* Renamed plugin prefixes from `ddc_` to `ddwpc_` across PHP and JS files.
* Removed manual `load_plugin_textdomain()` call (auto-loaded by WordPress).
* Removed direct core file loads (e.g. `require_once wp-load.php`).

= 1.0.0 =
* Initial release
* Added spam comment deletion
* Added all comments deletion with backup
* Added site-wide comment disable feature

== Upgrade Notice ==

= 1.0.1 =
* Changed all plugin function, constant, and script prefixes to `ddwpc_` and cleaned up core file loads and textdomain hooks.

= 1.0.0 =
Initial release of Delete & Disable Comments plugin. 