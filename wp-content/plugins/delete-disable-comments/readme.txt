=== Delete & Disable Comments ===
Contributors: helpstring
Tags: comments, spam, disable comments, delete comments, cleanup
Requires at least: 5.0
Tested up to: 7.1
Stable tag: 1.1.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Review comment counts, clean up selected types in resumable batches, export CSV, or disable public comments from one admin screen.

== Description ==

**Delete & Disable Comments** gives WordPress administrators a focused screen for comment cleanup and comment shutdown tasks. Open it under **Tools → Delete & Disable Comments**.

Many older sites collect spam comments, unused discussion threads, or imported comment data that no longer belongs on the site. This plugin keeps those maintenance actions in one place without asking you to edit the database manually.

= Why use it? =

* See counts before deleting and select the comment types to remove.
* Clean ordinary spam or selected comments in small, resumable requests.
* Keep WordPress editor Notes, product reviews and other custom comment types by default.
* Export all comment rows to CSV for inspection or a manual import workflow. The CSV is **not a restorable database backup** and omits comment metadata.
* Open CSV exports in spreadsheet applications without executing formula-like comment values.
* Disable public comments site-wide with a toggle while keeping editor Notes available through REST.
* Restore previously recorded Discussion defaults when switching the toggle off. Existing posts closed by the separate maintenance action stay closed.
* Use a standard WordPress admin screen available only to administrators.

= Common use cases =

* Remove accumulated spam comments from a site.
* Prepare a site that no longer accepts discussion.
* Clean comments before a redesign, migration, or client handover.
* Export a CSV copy of comment data before permanent deletion, and make a database backup with your host.
* Close open comment status on existing posts without triggering `save_post` hooks.

= What Disable Comments does =

The **Disable Comments** toggle changes the site's comment behavior. When enabled, it sets WordPress defaults for new content to closed, prevents new public comment and ping submissions, hides front-end comment output, removes comment-related UI, unregisters comment-related blocks, and removes the recent comments widget. Product reviews are also closed. The comment REST route remains available for internal editor Notes.

It does **not** delete existing comments. It also does not run a scheduled cleanup. If existing posts still have open comment or ping status, the admin screen shows a maintenance notice with a **Close all comments now** button. This separate, confirmed action permanently changes those fields and avoids per-post `save_post` hooks. Switching the toggle off does not reopen those posts. On sites disabled by an older version, the earlier Discussion defaults were not recorded and cannot be restored automatically.

= Before deleting =

The default selection covers ordinary comments, pingbacks and trackbacks on non-product content, regardless of status. Product reviews, editor Notes and other custom types require explicit selection. The spam button only deletes ordinary public spam. The CSV export includes all comment types and personal data; keep it private. For a recoverable copy, make a full database backup with your host before deleting. Deletion is permanent.

For a step-by-step guide or help with complex cleanups and migration work, see [Ostheimer's Delete & Disable Comments guide](https://www.ostheimer.at/leistungen/wordpress-plugins/delete-disable-comments).

= Translations =

The current admin interface is translated into German. Other included locale files may use English for newer messages until their translations are updated. WordPress loads the matching `.mo` file based on the site language. Text domain: `delete-disable-comments`.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/delete-disable-comments`, or install the ZIP via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Open **Tools → Delete & Disable Comments** to review counts, export CSV, delete selected comments, or disable comments site-wide.

== Frequently Asked Questions ==

= Is the CSV export a backup? =

No. **Export all comments as CSV** streams the comment table to an authenticated administrator. It omits comment metadata and cannot be restored by this plugin. Make a full database backup before permanent deletion.

= Can the plugin restore deleted comments from CSV? =

No. The CSV is for inspection or manual import workflows. Use your database backup to restore deleted comments and their metadata.

= Why can some exported values start with an apostrophe? =

Comment fields that look like spreadsheet formulas are prefixed with an apostrophe in the CSV. This keeps untrusted comment text visible while preventing common spreadsheet applications from evaluating it as a formula. Remove that protective prefix only in a trusted import workflow.

= Does disabling comments delete existing comments or editor Notes? =

No. The toggle prevents and hides public comment functionality, but existing records stay in the database until you explicitly delete them. Editor Notes stay available in the block editor. Product reviews are closed by the site-wide toggle.

= What does “Close all comments now” do? =

When disable mode is active, some existing posts may still have `comment_status` or `ping_status` set to open. The button permanently closes those fields in one SQL update. Switching the toggle off does not reopen them.

= Is the close action safe with WPML, Yoast SEO, or Polylang? =

The close action does not call `wp_update_post()` for every post and does not trigger `save_post` hooks. That keeps it suitable for sites using WPML, Yoast SEO, Polylang, and other plugins that react to post saves.

= Who can use the plugin screen? =

Only users with the `manage_options` capability, usually administrators. AJAX actions are protected with WordPress nonces and capability checks.

= Does it support WordPress multisite? =

No. This plugin is designed for single-site WordPress installations.

= Does it run scheduled cleanup jobs? =

No. Cleanup actions run only when an administrator clicks the relevant button.

= Where are CSV exports stored? =

CSV files are streamed through a protected administrator download. The plugin no longer leaves them in the public uploads directory.

== Screenshots ==

1. Main panel with cleanup counts, type selection, CSV export and disable toggle
2. Spam deletion confirmation dialog
3. Selected comment deletion confirmation with affected count
4. Disable toggle and permanent close-posts maintenance notice

== Changelog ==

= 1.1.0 =
* Protect editor Notes, product reviews and custom comment types from default cleanup.
* Delete selected types in resumable batches with exact progress and failure reporting.
* Keep editor Notes available through REST while public comments are disabled.
* Restore saved Discussion defaults when re-enabling comments; clarify permanent post-status changes and legacy settings.
* Rename the CSV download as an export and clarify that it is not a restorable backup.
* Show cleanup counts and add contextual help for complex migrations.

= 1.0.7 =
* Export and delete comments with every status, including spam, trash, and custom statuses.
* Verify complete deletion and report the number of comments actually removed.
* Preserve the site-wide disable setting across plugin deactivation and reactivation.
* Neutralize formula-like values in CSV backups before they reach spreadsheet applications.
* Verify compatibility with WordPress 7.1 and add Plugin Check to continuous integration.

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

= 1.1.0 =
Cleanup now protects Notes and reviews by default. Review the selected types before deleting. CSV exports are not restorable backups.

= 1.0.7 =
Complete comment cleanup and safer CSV backups. Recommended for sites that delete spam or open backups in spreadsheet applications.

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
