=== Delete & Disable Comments ===
Contributors: helpstring
Tags: comments, spam, disable comments, delete comments, backup
Requires at least: 5.0
Tested up to: 7.0
Stable tag: 1.0.6
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean up spam, delete all comments with CSV backup, or disable comments site-wide from one admin screen.

== Description ==

**Delete & Disable Comments** gives WordPress administrators a focused screen for comment cleanup and comment shutdown tasks. Open it under **Tools → Delete & Disable Comments**.

Many older sites collect spam comments, unused discussion threads, or imported comment data that no longer belongs on the site. This plugin keeps those maintenance actions in one place without asking you to edit the database manually.

= Why use it? =

* Delete all comments marked as spam after confirmation.
* Delete all comments after confirmation.
* Download a CSV backup before deleting all comments.
* Disable comments site-wide with a single toggle.
* Close comments and pings on existing posts when disable mode is active.
* Use a standard WordPress admin screen available only to administrators.

= Common use cases =

* Remove accumulated spam comments from a site.
* Prepare a site that no longer accepts discussion.
* Clean comments before a redesign, migration, or client handover.
* Download a CSV copy of comment data before permanent deletion.
* Close open comment status on existing posts without triggering `save_post` hooks.

= What Disable Comments does =

The **Disable Comments** toggle changes the site's comment behavior. When enabled, it sets WordPress defaults for new content to closed, prevents new comment and ping submissions, hides front-end comment output, removes comment-related UI, blocks comment REST endpoints, unregisters comment-related blocks, and removes the recent comments widget.

It does **not** delete existing comments. It also does not run a scheduled cleanup. If existing posts still have open comment or ping status, the admin screen shows a maintenance notice with a **Close all comments now** button. That button performs one direct SQL update to close those fields and avoids per-post `save_post` hooks, which is useful for sites using WPML, Yoast SEO, Polylang, or other plugins that listen to post updates.

= Translations =

The plugin ships with gettext translation files for broad EU language support, including German and additional EU locales. WordPress loads the matching `.mo` file automatically based on the site language. Text domain: `delete-disable-comments`.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/delete-disable-comments`, or install the ZIP via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Open **Tools → Delete & Disable Comments** to delete spam comments, download a CSV backup, delete all comments, or disable comments site-wide.

== Frequently Asked Questions ==

= Can I back up comments before deleting them? =

Yes. Use **Download Backup** before **Delete All Comments**. The backup is generated on demand and streamed as a protected administrator download.

= Can the plugin restore deleted comments from a CSV backup? =

No. The CSV backup is for safekeeping or manual import workflows. The plugin does not include a restore tool.

= Does disabling comments delete existing comments? =

No. The disable toggle prevents and hides comment functionality, but existing comment records stay in the database until you explicitly delete them.

= What does “Close all comments now” do? =

When disable mode is active, some existing posts may still have `comment_status` or `ping_status` set to open. The button closes those fields in one SQL update.

= Is the close action safe with WPML, Yoast SEO, or Polylang? =

The close action does not call `wp_update_post()` for every post and does not trigger `save_post` hooks. That keeps it suitable for sites using WPML, Yoast SEO, Polylang, and other plugins that react to post saves.

= Who can use the plugin screen? =

Only users with the `manage_options` capability, usually administrators. AJAX actions are protected with WordPress nonces and capability checks.

= Does it support WordPress multisite? =

No. This plugin is designed for single-site WordPress installations.

= Does it run scheduled cleanup jobs? =

No. Cleanup actions run only when an administrator clicks the relevant button.

= Where are CSV backup files stored? =

Backups are streamed through a protected administrator download. The plugin no longer leaves CSV files in the public uploads directory.

== Screenshots ==

1. Main panel with three sections: spam, delete all plus backup, disable toggle
2. Spam delete confirmation dialog with Yes and No
3. Delete all warning plus backup reminder
4. Disable toggle ON plus yellow maintenance notice plus Close all comments now button

== Changelog ==

= 1.0.6 =
* Changed CSV backups to stream through an authenticated administrator download instead of writing public files under `wp-content/uploads/`.
* Removed stale backup object caching so downloads reflect the current comments table.

= 1.0.5 =
* Added complete EU locale translation files and compiled `.mo` files for the admin UI.
* Kept complete German translations (`de_AT`, `de_DE`) for admin UI, AJAX messages, and maintenance notices.
* Updated the translation template (`.pot`) and fixed the hardcoded “Error loading status” JavaScript message.

= 1.0.4 =
* Plugin Check compliance: `Tested up to` 7.0, sanitized toggle AJAX input, documented intentional bulk SQL queries.
* Version bump for WordPress.org submission readiness.

= 1.0.3 =
* Fixed the disable-comments toggle so it no longer runs a synchronous bulk update on `wp_posts` during AJAX.
* Bulk-closing existing posts is now performed only through **Close all comments now** or on plugin activation when disable mode is already enabled.
* Hardened toggle AJAX input handling and admin JavaScript error recovery.
* Removed Cypress E2E tests from the repository; PHP smoke tests remain under `tests/php/`.

= 1.0.2 =
* Fixed `ddwpc_init()` so it no longer iterates over every post on every page request.
* Bulk close-comments now runs only when the administrator clicks **Close all comments now**.
* Bulk close operation uses a single `$wpdb` update without triggering `save_post`, `transition_post_status`, or `wp_after_insert_post`.
* Settings screen shows the count of posts with open comments or pings and provides a one-click close button.
* Added helpers: `ddwpc_is_disable_comments_enabled()`, `ddwpc_apply_disable_comments_defaults()`, `ddwpc_close_all_post_comments_in_db()`, and `ddwpc_count_posts_with_open_comments()`.

= 1.0.1 =
* Renamed plugin prefixes from `ddc_` to `ddwpc_` across PHP and JavaScript.
* Removed the manual `load_plugin_textdomain()` call because WordPress 4.6+ auto-loads plugin translations.
* Removed direct core file loads.
* Moved CSV backups under `wp-content/uploads/delete-disable-comments/`.

= 1.0.0 =
* Initial release with spam deletion, delete-all with backup, and site-wide comment disable.

== Upgrade Notice ==

= 1.0.6 =
Privacy hardening for CSV backups. Backup files are now streamed to administrators and are not left in public uploads.

= 1.0.5 =
Translation release with broad EU locale support for the admin interface.

= 1.0.4 =
Maintenance release for Plugin Check and WordPress 7.0 compatibility metadata.

= 1.0.3 =
Recommended if the disable-comments toggle stayed on “Updating...” or large sites took too long while toggling comments off.

= 1.0.2 =
Recommended for sites using WPML, Yoast SEO, Polylang, or any plugin that hooks into post saves.

= 1.0.1 =
Prefix rename to `ddwpc_`, safer paths, and backup files moved to the uploads directory.

= 1.0.0 =
Initial release.
