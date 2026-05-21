=== Delete & Disable Comments ===
Contributors: helpstring
Tags: comments, spam, delete, disable, backup
Requires at least: 5.0
Tested up to: 7.0
Stable tag: 1.0.5
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean spam, wipe all comments with CSV backup, or disable comments site-wide — fast, safe, and built for busy WordPress admins.

== Description ==

**Delete & Disable Comments** gives site administrators a focused control panel under **Tools → Delete & Disable Comments** to regain control over comment clutter without touching the database manually.

Whether you are fighting years of Akismet spam, migrating a site that no longer needs discussions, or shutting down comments after an abuse wave — this plugin keeps the workflow simple, reversible where it matters, and compatible with popular plugins like WPML, Yoast SEO, and Polylang.

= Why administrators choose this plugin =

* **One-click spam cleanup** — Remove every comment WordPress has marked as spam.
* **Safe mass deletion** — Delete all comments only after an explicit confirmation, with an optional CSV backup download first.
* **True site-wide disable** — Turn off new comments, hide comment UI on the front end, block REST comment endpoints, and remove Gutenberg comment blocks.
* **Large-site friendly** — Bulk operations use efficient SQL updates without firing `save_post` on every post (no timeouts, no plugin conflicts).
* **Maintenance tools** — See how many posts still allow comments and close them in one safe action when needed.
* **Native WordPress admin UI** — Familiar cards, buttons, and notices; no bloated dashboard widgets.

= Perfect for =

* Blogs and business sites buried in spam comments
* Brochure sites, portfolios, and landing pages that should never accept comments
* Agencies cleaning up client sites before handover
* Anyone who wants a backup before deleting comment data permanently

= What happens when you disable comments? =

The plugin closes comment and ping status for existing content, prevents new submissions, removes comment forms and widgets from the theme, unregisters comment-related blocks, and replaces the comments template with a blank file — while leaving your posts and pages untouched.

= Translations =

English (source strings in code) and German (`de_AT`, `de_DE`) ship with the plugin. WordPress loads the matching `.mo` file automatically based on the site locale. Text domain: `delete-disable-comments`.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/delete-disable-comments`, or install the ZIP via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Open **Tools → Delete & Disable Comments** to delete spam, remove all comments (with optional backup), or toggle comments site-wide.

== Frequently Asked Questions ==

= Can I recover deleted comments? =

Only if you downloaded a CSV backup before deletion. The plugin prompts you to back up before removing all comments. Spam deletion and “delete all” are permanent once confirmed.

= Does disabling comments delete existing comments? =

No. Disabling comments prevents new submissions and hides comment UI on the front end. Existing comment records stay in the database until you delete them explicitly.

= Will this break WPML, Yoast, or other plugins? =

Version 1.0.2+ avoids calling `wp_update_post()` on every request. Bulk “close comments” uses a single hook-free SQL update so `save_post` listeners are not triggered unexpectedly.

= Why is there a “Close all comments now” button? =

Some posts may still have `comment_status = open` in the database even after toggling disable. The maintenance button closes them in one efficient update — useful after imports or legacy data.

= Where are CSV backups stored? =

Backups are written to `wp-content/uploads/delete-disable-comments/` and also offered as a direct browser download before deletion.

= Who can use this plugin? =

Only users with the `manage_options` capability (typically administrators). All actions are protected with nonces.

= Does it work on multisite? =

No. This plugin is designed for single-site WordPress installations.

== Screenshots ==

1. Main control panel under Tools → Delete & Disable Comments
2. Confirmation dialog before deleting spam comments
3. Delete all comments with backup reminder and download option
4. Disable-comments toggle with maintenance notice for open posts

== Changelog ==

= 1.0.5 =
* Complete German translations (`de_AT`, `de_DE`) for all admin UI, AJAX messages, and maintenance notices.
* Updated translation template (`.pot`); fixed hardcoded “Error loading status” in admin JavaScript.

= 1.0.4 =
* Plugin Check compliance: `Tested up to` 7.0, sanitized toggle AJAX input, documented intentional bulk SQL queries.
* Version bump (patch release) for WordPress.org submission readiness.

= 1.0.3 =
* **Fix:** “Disable comments site-wide” toggle no longer runs a synchronous bulk UPDATE on `wp_posts` during AJAX (prevents “Updating…” hang and table locks on large sites).
* Bulk-closing existing posts is now only performed via **Close all comments now** (or on plugin activation), not when flipping the toggle.
* Hardened toggle AJAX input handling and admin JavaScript error recovery.
* Removed Cypress E2E tests from the repository; PHP smoke tests remain under `tests/php/`.

= 1.0.2 =
* **Critical fix:** `ddwpc_init()` no longer iterates over every post on every page request. The previous implementation called `wp_update_post()` for every post, causing severe performance issues and fatal errors with plugins that hook `save_post` (notably WPML). See [GitHub Issue #1](https://github.com/ostheimer/delete-disable-wp-comments/issues/1).
* Bulk close-comments now runs only when the operator toggles disable or clicks **Close all comments now**.
* Bulk operation uses a single safe `$wpdb` UPDATE without triggering `save_post`, `transition_post_status`, or `wp_after_insert_post`.
* Settings screen shows count of posts with open comments/pings and a one-click close button.
* New helpers: `ddwpc_is_disable_comments_enabled()`, `ddwpc_apply_disable_comments_defaults()`, `ddwpc_close_all_post_comments_in_db()`, `ddwpc_count_posts_with_open_comments()`.

= 1.0.1 =
* Renamed plugin prefixes from `ddc_` to `ddwpc_` across PHP and JS.
* Removed manual `load_plugin_textdomain()` call (auto-loaded by WordPress 4.6+).
* Removed direct core file loads (e.g. `require_once wp-load.php`).
* CSV backups stored under `wp-content/uploads/delete-disable-comments`.

= 1.0.0 =
* Initial release: spam deletion, delete all with backup, site-wide comment disable.

== Upgrade Notice ==

= 1.0.5 =
Translation release: full German locale support for the admin interface. No functional changes.

= 1.0.4 =
Maintenance release for Plugin Check and WordPress 7.0 compatibility header. No functional changes.

= 1.0.3 =
Recommended if the disable-comments toggle stayed on “Updating…” or the site felt slow when toggling comments off.

= 1.0.2 =
Critical fix for sites using WPML or any plugin hooking `save_post` while “Disable comments site-wide” was enabled. Upgrade strongly recommended.

= 1.0.1 =
Prefix rename to `ddwpc_`, safer paths, backups moved to the uploads directory.

= 1.0.0 =
Initial release.
