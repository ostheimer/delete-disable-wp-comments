<?php
/**
 * Standalone smoke test for the v1.0.2 bug fix.
 *
 * Verifies that:
 *   - ddwpc_init() does NOT call wp_update_post() (the regression that triggered #1).
 *   - ddwpc_close_all_post_comments_in_db() executes a single safe SQL UPDATE.
 *   - ddwpc_apply_disable_comments_defaults() is idempotent.
 *
 * This test is intentionally framework-free: it stubs the small subset of
 * WordPress functions the plugin touches so it can be run without spinning up
 * a full wp-tests setup. Use it as the spec base for porting to PHPUnit /
 * wp-env when the project gains a proper test harness.
 *
 * Run:
 *   php tests/php/test-bug-fix.php
 *
 * Exits 0 on success, 1 on any failed assertion.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Tiny WordPress stub
// ---------------------------------------------------------------------------

if (!defined('ABSPATH'))   { define('ABSPATH', __DIR__ . '/'); }
if (!defined('WPINC'))     { define('WPINC', 'wp-includes'); }
if (!defined('HOUR_IN_SECONDS')) { define('HOUR_IN_SECONDS', 3600); }
if (!defined('DAY_IN_SECONDS'))  { define('DAY_IN_SECONDS', 86400); }
if (!defined('MINUTE_IN_SECONDS')) { define('MINUTE_IN_SECONDS', 60); }

/**
 * Bookkeeping for the stubbed environment so the assertions can inspect
 * what the plugin code did.
 */
final class DDWPC_Test_State {
    public static array $options       = ['ddwpc_disable_comments' => '1'];
    public static array $actions       = []; // hook => [ [callable, prio] ]
    public static array $filters       = [];
    public static array $option_writes = []; // option_name => write_count
    public static int   $wp_update_post_calls = 0;
    public static int   $sql_query_count       = 0;
    public static array $sql_queries           = [];
    public static int   $rows_to_update        = 25;
    public static int   $open_posts_count      = 0;
}

function add_action($hook, $cb, $prio = 10, $args = 1) {
    DDWPC_Test_State::$actions[$hook][] = [$cb, $prio];
}
function remove_action($hook, $cb, $prio = 10) { /* no-op */ }
function add_filter($hook, $cb, $prio = 10, $args = 1) {
    DDWPC_Test_State::$filters[$hook][] = [$cb, $prio];
}
function remove_filter($hook, $cb, $prio = 10) { /* no-op */ }
function do_action($hook, ...$args) {
    foreach (DDWPC_Test_State::$actions[$hook] ?? [] as [$cb, $prio]) {
        call_user_func_array($cb, $args);
    }
}
function get_option($name, $default = false) {
    return DDWPC_Test_State::$options[$name] ?? $default;
}
function update_option($name, $value, $autoload = null) {
    DDWPC_Test_State::$option_writes[$name] = (DDWPC_Test_State::$option_writes[$name] ?? 0) + 1;
    DDWPC_Test_State::$options[$name] = $value;
    return true;
}
function add_option($name, $value, $deprecated = '', $autoload = 'yes') {
    if (!array_key_exists($name, DDWPC_Test_State::$options)) {
        DDWPC_Test_State::$options[$name] = $value;
    }
    return true;
}
function delete_option($name) { unset(DDWPC_Test_State::$options[$name]); return true; }
function register_activation_hook($file, $cb) { /* no-op */ }
function register_deactivation_hook($file, $cb) { /* no-op */ }

function get_post_types($args = [], $output = 'names') { return ['post', 'page']; }
function post_type_supports($post_type, $feature) { return true; }
function remove_post_type_support($post_type, $feature) { /* no-op */ }
function unregister_widget($w) { /* no-op */ }
function plugin_dir_path($f) { return dirname($f) . '/'; }
function plugin_dir_url($f)  { return 'http://example.test/wp-content/plugins/delete-disable-comments/'; }
function wp_die($msg = '', $title = '', $args = []) { throw new RuntimeException("wp_die: $msg"); }
function is_comment_feed() { return false; }
function is_admin_bar_showing() { return false; }
function wp_dequeue_style($h) { /* no-op */ }
function wp_enqueue_style($h, $src, $deps = [], $ver = false) { /* no-op */ }
function wp_enqueue_script($h, $src, $deps = [], $ver = false, $in_footer = false) { /* no-op */ }
function wp_localize_script($h, $obj, $data) { /* no-op */ }
function wp_create_nonce($action) { return 'nonce'; }
function admin_url($path = '') { return 'http://example.test/wp-admin/' . $path; }
function esc_html__($s, $domain = null) { return $s; }
function esc_html_e($s, $domain = null) { echo $s; }
function esc_html($s) { return $s; }
function esc_attr($s) { return $s; }
function __($s, $domain = null) { return $s; }
function _e($s, $domain = null) { echo $s; }
function _n($single, $plural, $count, $domain = null) { return $count === 1 ? $single : $plural; }
function add_submenu_page() { /* no-op */ }
function remove_menu_page($slug) { /* no-op */ }
function wp_cache_set_last_changed($group) { /* no-op */ }
function wp_cache_set($key, $val, $group = '', $ttl = 0) { /* no-op */ }
function wp_cache_get($key, $group = '', $force = false, &$found = null) { return false; }

// wp_update_post is the call we explicitly want to NEVER see during init.
function wp_update_post($post = [], $wp_error = false, $fire_after_hooks = true) {
    DDWPC_Test_State::$wp_update_post_calls++;
    return is_array($post) && isset($post['ID']) ? (int) $post['ID'] : 1;
}

// Minimal $wpdb stub.
final class DDWPC_Test_WPDB {
    public string $posts = 'wp_posts';
    public function query(string $sql) {
        DDWPC_Test_State::$sql_query_count++;
        DDWPC_Test_State::$sql_queries[] = $sql;
        return DDWPC_Test_State::$rows_to_update;
    }
    public function get_var(string $sql) {
        DDWPC_Test_State::$sql_query_count++;
        DDWPC_Test_State::$sql_queries[] = $sql;
        return DDWPC_Test_State::$open_posts_count;
    }
}
$GLOBALS['wpdb'] = new DDWPC_Test_WPDB();

// admin/admin-page.php and includes/functions.php declare a few things we don't care about.
// Stub the AJAX helpers so requiring the include file doesn't break.
function check_ajax_referer($action, $name = false, $die = true) { return true; }
function current_user_can($cap) { return true; }
function wp_send_json_success($data = null) { throw new RuntimeException('json_success'); }
function wp_send_json_error($data = null) { throw new RuntimeException('json_error'); }
function get_comments($args = []) { return []; }
function wp_delete_comment($id, $force = false) { return true; }
function wp_upload_dir() { return ['basedir' => sys_get_temp_dir(), 'baseurl' => 'http://example.test/uploads']; }
function wp_mkdir_p($d) { return true; }
function wp_delete_file($f) { return true; }
function trailingslashit($s) { return rtrim($s, '/') . '/'; }
function sanitize_text_field($s) { return $s; }
function wp_unslash($s) { return $s; }

// ---------------------------------------------------------------------------
// Load the plugin under test
// ---------------------------------------------------------------------------

require_once __DIR__ . '/../../wp-content/plugins/delete-disable-comments/delete-disable-comments.php';

// ---------------------------------------------------------------------------
// Assertions
// ---------------------------------------------------------------------------

$failures = [];

function assert_eq($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        $GLOBALS['failures'][] = sprintf(
            "FAIL: %s\n   expected: %s\n   actual:   %s",
            $message,
            var_export($expected, true),
            var_export($actual, true)
        );
    } else {
        echo "  ok  $message\n";
    }
}

echo "\n--- ddwpc_is_disable_comments_enabled() ---\n";
DDWPC_Test_State::$options['ddwpc_disable_comments'] = '1';
assert_eq(true, ddwpc_is_disable_comments_enabled(), "string '1' is treated as enabled");
DDWPC_Test_State::$options['ddwpc_disable_comments'] = 1;
assert_eq(true, ddwpc_is_disable_comments_enabled(), "int 1 is treated as enabled");
DDWPC_Test_State::$options['ddwpc_disable_comments'] = '0';
assert_eq(false, ddwpc_is_disable_comments_enabled(), "string '0' is treated as disabled");
DDWPC_Test_State::$options['ddwpc_disable_comments'] = false;
assert_eq(false, ddwpc_is_disable_comments_enabled(), "bool false is treated as disabled");

echo "\n--- ddwpc_init() must not call wp_update_post() (regression #1) ---\n";
DDWPC_Test_State::$options['ddwpc_disable_comments'] = '1';
DDWPC_Test_State::$wp_update_post_calls = 0;
ddwpc_init();
assert_eq(0, DDWPC_Test_State::$wp_update_post_calls,
    'ddwpc_init() must never trigger wp_update_post()');

echo "\n--- ddwpc_init() must not write any options on every request ---\n";
DDWPC_Test_State::$option_writes = [];
ddwpc_init();
assert_eq(0, DDWPC_Test_State::$option_writes['default_comment_status'] ?? 0,
    'ddwpc_init() must not write default_comment_status on every request');
assert_eq(0, DDWPC_Test_State::$option_writes['default_ping_status'] ?? 0,
    'ddwpc_init() must not write default_ping_status on every request');

echo "\n--- ddwpc_close_all_post_comments_in_db() runs a single safe SQL UPDATE ---\n";
DDWPC_Test_State::$sql_query_count = 0;
DDWPC_Test_State::$sql_queries     = [];
DDWPC_Test_State::$rows_to_update  = 7;
$rows = ddwpc_close_all_post_comments_in_db();
assert_eq(7, $rows, 'returns affected row count');
assert_eq(1, DDWPC_Test_State::$sql_query_count, 'runs exactly one SQL query');
$query = DDWPC_Test_State::$sql_queries[0] ?? '';
$is_update = (str_contains($query, 'UPDATE') && str_contains($query, "comment_status = 'closed'") && str_contains($query, "ping_status = 'closed'"));
assert_eq(true, $is_update, 'query is a single UPDATE that closes both statuses');
$is_idempotent = (str_contains($query, "<>") || str_contains($query, '!='));
assert_eq(true, $is_idempotent, "query has a WHERE clause that skips already-closed posts");

echo "\n--- ddwpc_apply_disable_comments_defaults() is idempotent ---\n";
DDWPC_Test_State::$options['default_comment_status'] = 'closed';
DDWPC_Test_State::$options['default_ping_status']    = 'closed';
DDWPC_Test_State::$option_writes = [];
ddwpc_apply_disable_comments_defaults(true);
assert_eq(0, DDWPC_Test_State::$option_writes['default_comment_status'] ?? 0,
    'no write when already closed');
assert_eq(0, DDWPC_Test_State::$option_writes['default_ping_status'] ?? 0,
    'no write when already closed');

DDWPC_Test_State::$options['default_comment_status'] = 'open';
DDWPC_Test_State::$options['default_ping_status']    = 'open';
DDWPC_Test_State::$option_writes = [];
ddwpc_apply_disable_comments_defaults(true);
assert_eq(1, DDWPC_Test_State::$option_writes['default_comment_status'] ?? 0,
    'one write when comment_status is open');
assert_eq(1, DDWPC_Test_State::$option_writes['default_ping_status'] ?? 0,
    'one write when ping_status is open');

echo "\n--- ddwpc_count_posts_with_open_comments() returns the wpdb result ---\n";
DDWPC_Test_State::$open_posts_count = 12;
assert_eq(12, ddwpc_count_posts_with_open_comments(), 'returns wpdb->get_var() result');

echo "\n--- admin page does not run the COUNT query when toggle is off (review feedback) ---\n";
DDWPC_Test_State::$options['ddwpc_disable_comments'] = '0';
DDWPC_Test_State::$sql_query_count = 0;
DDWPC_Test_State::$sql_queries     = [];
require_once __DIR__ . '/../../wp-content/plugins/delete-disable-comments/admin/admin-page.php';
ob_start();
ddwpc_admin_page();
ob_end_clean();
$count_queries = array_filter(
    DDWPC_Test_State::$sql_queries,
    static fn(string $q): bool => str_contains($q, 'COUNT(*)')
);
assert_eq(0, count($count_queries),
    'no COUNT(*) query is fired when the disable-comments toggle is off');

DDWPC_Test_State::$options['ddwpc_disable_comments'] = '1';
DDWPC_Test_State::$sql_query_count = 0;
DDWPC_Test_State::$sql_queries     = [];
DDWPC_Test_State::$open_posts_count = 0;
ob_start();
ddwpc_admin_page();
ob_end_clean();
$count_queries = array_filter(
    DDWPC_Test_State::$sql_queries,
    static fn(string $q): bool => str_contains($q, 'COUNT(*)')
);
assert_eq(1, count($count_queries),
    'exactly one COUNT(*) query is fired when the disable-comments toggle is on');

echo "\n--- ddwpc_toggle_comments() returns quickly without bulk SQL UPDATE ---\n";
DDWPC_Test_State::$options['ddwpc_disable_comments'] = '0';
DDWPC_Test_State::$sql_query_count = 0;
DDWPC_Test_State::$sql_queries     = [];
$_POST['disabled'] = 'true';
$caught_success = false;
try {
    ddwpc_toggle_comments();
} catch (RuntimeException $e) {
    if ($e->getMessage() === 'json_success') {
        $caught_success = true;
    }
}
unset($_POST['disabled']);
assert_eq(true, $caught_success, 'toggle ON responds with JSON success');
assert_eq(0, DDWPC_Test_State::$sql_query_count, 'toggle ON does not run bulk SQL UPDATE');
assert_eq(true, ddwpc_is_disable_comments_enabled(), 'toggle ON persists the disable option');
assert_eq('closed', DDWPC_Test_State::$options['default_comment_status'] ?? null,
    'toggle ON applies default_comment_status');

DDWPC_Test_State::$options['ddwpc_disable_comments'] = '1';
DDWPC_Test_State::$sql_query_count = 0;
$_POST['disabled'] = 'false';
$caught_success = false;
try {
    ddwpc_toggle_comments();
} catch (RuntimeException $e) {
    if ($e->getMessage() === 'json_success') {
        $caught_success = true;
    }
}
unset($_POST['disabled']);
assert_eq(true, $caught_success, 'toggle OFF responds with JSON success');
assert_eq(0, DDWPC_Test_State::$sql_query_count, 'toggle OFF does not run bulk SQL UPDATE');
assert_eq(false, ddwpc_is_disable_comments_enabled(), 'toggle OFF clears the disable option');

// ---------------------------------------------------------------------------
// Summary
// ---------------------------------------------------------------------------

echo "\n--- Summary ---\n";
if (count($failures) === 0) {
    echo "All assertions passed.\n";
    exit(0);
} else {
    echo count($failures) . " failed assertion(s):\n";
    foreach ($failures as $f) { echo "$f\n"; }
    exit(1);
}
