=== Delete & Disable Comments – Bulk Delete Spam & All Comments ===
Contributors: helpstring
Tags: delete comments, disable comments, delete all comments, remove comments, delete spam comments
Requires at least: 5.0
Tested up to: 7.1
Stable tag: 1.1.1
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bulk delete spam or all comments in safe, resumable batches. Export to CSV first or disable comments site-wide. Free, no upsells.

== Description ==

Delete all comments, bulk delete spam, or disable comments on your whole WordPress site – safely, even when there are tens of thousands of them. Everything happens on one screen under **Tools → Delete & Disable Comments**.

= Why site owners choose it =

* **Works on large sites and cheap hosting.** Comments are deleted in small batches with a live counter, so a big cleanup does not time out or run out of memory.
* **Protects what you want to keep.** WooCommerce product reviews, block editor Notes and comment types added by other plugins stay untouched unless you select them.
* **Exact counts first.** See how many comments of each type and how much spam your site has before anything is deleted.
* **Free CSV export.** Download all comments as a spreadsheet before you delete. Formula-like values are neutralized, so the file opens safely in Excel, Numbers or LibreOffice.
* **Disable comments site-wide with one switch.** Comment forms, existing comments, comment menus, widgets, feeds and blocks disappear. New public comments and pings are refused, including through the REST API.
* **Safe with WPML, Polylang and Yoast SEO.** Closing comments on existing posts does not trigger post updates.
* **100% free.** No Pro version, no ads, no account, no tracking.

= Proven on real sites =

One user removed 32,509 unwanted comments from a client site with a PHP memory limit of only 128 MB, after the standard WordPress comment screens had failed with server errors.

= Typical uses =

* Clean up years of accumulated spam.
* Remove all comments before a redesign, migration or client handover.
* Turn a blog into a website without discussion.
* Delete old comments, pingbacks and trackbacks while keeping product reviews.

= Try it before you install =

Use the **Live Preview** button on this page to open a temporary demo site in your browser. It comes with sample comments, spam, pingbacks and editor Notes, so you can try every button safely. Nothing is installed on your own site.

= Before you delete =

Deletion is permanent. The CSV export is a readable copy of the comment rows, **not a restorable backup**: it omits comment metadata and contains personal data such as names and email addresses, so keep it private. For a full safety net, make a database backup with your host first.

The default selection covers ordinary comments, pingbacks and trackbacks on non-product content, regardless of status. Product reviews, editor Notes and other custom comment types are only deleted when you select them. **Delete Spam Comments** only removes ordinary spam.

= What the disable switch does =

When comments are disabled, new content defaults to closed comments and pings, the comment form and existing comments are hidden on the front end, comment menus, widgets, feeds and blocks are removed, and product reviews are closed. Existing comments stay in the database until you delete them. Block editor Notes keep working.

Some existing posts may still be marked as open. The screen then offers **Close all comments now**, which permanently closes them in one database update. Switching comments back on restores your previous Discussion defaults, but posts closed this way stay closed. Sites disabled with a version before 1.1.0 did not record those defaults.

= Need help with a complex cleanup? =

For a step-by-step guide or professional help with large cleanups and migrations, see [Ostheimer's Delete & Disable Comments guide](https://www.ostheimer.at/leistungen/wordpress-plugins/delete-disable-comments).

= Translations =

The admin interface is available in English and German. WordPress loads the matching language file automatically based on the site language. Text domain: `delete-disable-comments`.

== Installation ==

1. In WordPress, go to **Plugins → Add New**, search for **Delete & Disable Comments**, then install and activate it. Alternatively, upload the ZIP via **Plugins → Add New → Upload Plugin**.
2. Open **Tools → Delete & Disable Comments**.
3. Review the counts, export a CSV if you want a copy, then delete spam, delete selected comments, or disable comments site-wide.

== Frequently Asked Questions ==

= How do I delete all comments in WordPress? =

Open **Tools → Delete & Disable Comments**. Keep **Public comments and pingbacks** selected, add other types if you want them removed as well, click **Delete Selected Comments** and confirm. The plugin deletes in batches and shows a running count until the selected comments are gone.

= How do I delete only spam comments? =

Click **Delete Spam Comments** and confirm. Only ordinary comments marked as spam are removed. Approved and pending comments, product reviews and editor Notes stay.

= How do I disable comments on my whole site? =

Switch on the **Disable Comments** toggle. Comment forms and existing comments disappear from the front end, and new comments and pings are refused. If the screen then shows **Close all comments now**, you can also close the comment status on existing posts.

= Can it handle tens of thousands of comments on shared hosting? =

Yes. Each request deletes a batch of 100 comments, so memory use and request time stay small. If you close the browser tab during a cleanup, start it again; it continues with the comments that are left.

= Will it delete WooCommerce product reviews? =

Only if you select **Product reviews**. The default cleanup and the spam button keep them. Disabling comments site-wide does close product reviews for new submissions.

= Is the CSV export a backup? =

No. **Export all comments as CSV** streams the comment table to an authenticated administrator. It omits comment metadata and cannot be restored by this plugin. Make a full database backup before permanent deletion.

= Can the plugin restore deleted comments from CSV? =

No. The CSV is for inspection or manual import workflows. Use your database backup to restore deleted comments and their metadata.

= Why can some exported values start with an apostrophe? =

Comment fields that look like spreadsheet formulas are prefixed with an apostrophe in the CSV. This keeps untrusted comment text visible while preventing common spreadsheet applications from evaluating it as a formula. Remove that protective prefix only in a trusted import workflow.

= Does disabling comments delete existing comments or editor Notes? =

No. The toggle prevents and hides public comment functionality, but existing records stay in the database until you explicitly delete them. Editor Notes stay available in the block editor through the comment REST route. Product reviews are closed by the site-wide toggle.

= Should I switch comments back on before deactivating the plugin? =

Yes, if you want comments again. Switching the toggle off restores your earlier Discussion defaults. If you deactivate the plugin while comments are disabled, the hiding and blocking stop, but new content keeps the closed Discussion defaults until you change them under **Settings → Discussion**. Your toggle setting is kept for a later reactivation.

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

CSV files are streamed through a protected administrator download. The plugin does not leave them in the public uploads directory.

== Screenshots ==

1. One screen to delete spam, delete selected or all comments, export them to CSV and disable comments site-wide
2. Bulk delete thousands of comments in safe batches with a live counter
3. Confirmation before deleting spam comments
4. Confirmation with the exact number of comments that will be deleted
5. Disable comments site-wide, with a one-click option to close comments on existing posts

== Changelog ==

= 1.1.1 =
* Clearer plugin name, description, FAQ and screenshots for the WordPress.org plugin directory.
* No functional changes.

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

= 1.1.1 =
Listing and naming update only. No functional changes.

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
