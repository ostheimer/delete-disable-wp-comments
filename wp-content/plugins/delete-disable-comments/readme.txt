=== Delete & Disable Comments ===
Contributors: helpstring
Tags: comments, spam, delete, disable, backup
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.0.2
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

= 1.0.2 =
* **Critical bug fix**: `ddwpc_init()` no longer iterates over every post on every page request. The previous implementation called `wp_update_post()` for every post in the database on every uncached request, which was both a severe performance regression *and* triggered fatal errors in third-party plugins that hook into `save_post` (notably WPML).
* The bulk close-comments operation now runs only when the operator explicitly toggles "Disable comments site-wide" or clicks the new **Close all comments now** maintenance button on the settings screen.
* The bulk operation now uses a single safe `$wpdb` UPDATE statement so it does not trigger `save_post`, `transition_post_status`, or `wp_after_insert_post` hooks. This makes it compatible with WPML, Yoast SEO, Avada/Fusion Builder, and similar plugins.
* The settings screen now shows how many posts in the database still have open comments/pings, with a one-click button to close them.
* Activation hook now sets `default_comment_status` and `default_ping_status` only once and only when the operator's setting requires it (no more option writes on every request).
* New helper functions `ddwpc_is_disable_comments_enabled()`, `ddwpc_apply_disable_comments_defaults()`, `ddwpc_close_all_post_comments_in_db()`, and `ddwpc_count_posts_with_open_comments()`.
* See: <https://github.com/ostheimer/delete-disable-wp-comments/issues/1>

= 1.0.1 =
* Renamed plugin prefixes from `ddc_` to `ddwpc_` across PHP and JS files.
* Removed manual `load_plugin_textdomain()` call (auto-loaded by WordPress).
* Removed direct core file loads (e.g. `require_once wp-load.php`).
* Updated backup directory path: CSV backups are now stored under `wp-content/uploads/delete-disable-comments` using `WP_CONTENT_DIR`, ensuring they are visible on the host.


= 1.0.0 =
* Initial release
* Added spam comment deletion
* Added all comments deletion with backup
* Added site-wide comment disable feature

== Upgrade Notice ==

= 1.0.2 =
Critical fix. Resolves a fatal error / WSOD that could appear with WPML and any plugin that hooks into save_post when the "Disable comments site-wide" toggle was on. Strongly recommended upgrade for anyone using the disable-comments feature.

= 1.0.1 =
* Changed all plugin function, constant, and script prefixes to `ddwpc_` and cleaned up core file loads and textdomain hooks.
* Changed backup location to `uploads/delete-disable-comments` so backup files appear correctly via host mount.

= 1.0.0 =
Initial release of Delete & Disable Comments plugin. 