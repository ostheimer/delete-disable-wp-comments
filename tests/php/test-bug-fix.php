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
    public static bool  $fail_post_update      = false;
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
function wp_nonce_url($url, $action = -1, $name = '_wpnonce') { return $url . '&' . $name . '=nonce'; }
function wp_verify_nonce($nonce, $action = -1) { return $nonce === 'nonce'; }
function admin_url($path = '') { return 'http://example.test/wp-admin/' . $path; }
function esc_html__($s, $domain = null) { return $s; }
function esc_html_e($s, $domain = null) { echo $s; }
function esc_html($s) { return $s; }
function esc_url($s) { return $s; }
function esc_attr($s) { return $s; }
function checked($condition) { if ($condition) echo 'checked="checked"'; }
function disabled($condition) { if ($condition) echo 'disabled="disabled"'; }
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
    public string $comments = 'wp_comments';
    public string $last_error = '';
    public function prepare($sql, ...$args) { return vsprintf($sql, $args); }
    public function get_results($sql) {
        if (!empty($GLOBALS['db_read_error'])) { $this->last_error = 'test error'; return null; }
        $this->last_error = '';
        if (str_contains($sql, 'GROUP BY c.comment_type')) {
            $grouped = [];
            foreach ($GLOBALS['fixtures'] ?? [] as $comment) {
                $type = (string) $comment->comment_type;
                $post_type = (string) ($comment->ddwpc_post_type ?? 'post');
                $status = (string) $comment->comment_approved;
                $key = implode('|', [$type, $post_type, $status]);
                if (!isset($grouped[$key])) {
                    $grouped[$key] = (object) ['comment_type' => $type, 'ddwpc_post_type' => $post_type,
                        'comment_approved' => $status, 'ddwpc_total' => 0];
                }
                $grouped[$key]->ddwpc_total++;
            }
            return array_values($grouped);
        }
        preg_match('/comment_ID > (\d+).*LIMIT (\d+)/s', $sql, $m);
        return array_slice(array_values(array_filter($GLOBALS['fixtures'], fn($c) => $c->comment_ID > (int)$m[1])), 0, (int)$m[2]);
    }
    public string $posts = 'wp_posts';
    public function query(string $sql) {
        DDWPC_Test_State::$sql_query_count++;
        DDWPC_Test_State::$sql_queries[] = $sql;
        if (DDWPC_Test_State::$fail_post_update) return false;
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
function check_ajax_referer($action, $name = false, $die = true) { return $GLOBALS['valid_nonce'] ?? true; }
function current_user_can($cap) { return $GLOBALS['can_manage'] ?? true; }
function wp_send_json_success($data = null) { $GLOBALS['json_data'] = $data; throw new RuntimeException('json_success'); }
function wp_send_json_error($data = null) { $GLOBALS['json_data'] = $data; throw new RuntimeException('json_error'); }
function get_comments($args = []) {
    $rows = array_values(array_filter($GLOBALS['fixtures'] ?? [], fn($c) => in_array($c->comment_approved, ['0', '1'], true)));
    if (!empty($args['count'])) return count($rows);
    if (($args['fields'] ?? '') === 'ids') return array_map(fn($c) => $c->comment_ID, $rows);
    return array_slice($rows, $args['offset'] ?? 0, ($args['number'] ?? 0) ?: null);
}
function wp_cache_delete($key, $group = '') { return true; }
function wp_delete_comment($id, $force = false) {
    if (!$force || $id === ($GLOBALS['blocked_id'] ?? null)) return false;
    unset($GLOBALS['fixtures'][$id], $GLOBALS['commentmeta'][$id]);
    return true;
}
function wp_upload_dir() { return ['basedir' => sys_get_temp_dir(), 'baseurl' => 'http://example.test/uploads']; }
function wp_mkdir_p($d) { return true; }
function wp_delete_file($f) { return true; }
function trailingslashit($s) { return rtrim($s, '/') . '/'; }
function sanitize_text_field($s) { return $s; }
function sanitize_key($s) { return strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $s)); }
function wp_unslash($s) { return $s; }
function is_wp_error($value) { return $value instanceof WP_Error; }
class WP_Error {
    public function __construct(public string $code, public string $message, public array $data = []) {}
}
function nocache_headers() { /* no-op */ }

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
DDWPC_Test_State::$fail_post_update = true;
assert_eq(false, ddwpc_close_all_post_comments_in_db(), 'database failure is distinguishable from zero changed posts');
DDWPC_Test_State::$fail_post_update = false;

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
$admin_html = ob_get_clean();
$count_queries = array_filter(
    DDWPC_Test_State::$sql_queries,
    static fn(string $q): bool => str_contains($q, 'COUNT(*)')
);
assert_eq(0, count($count_queries),
    'no COUNT(*) query is fired when the disable-comments toggle is off');
assert_eq(true, str_contains($admin_html, 'admin-post.php?action=ddwpc_backup_comments'),
    'backup button points to protected admin-post download');
assert_eq(false, str_contains($admin_html, '/uploads/delete-disable-comments/'),
    'backup button does not expose a public uploads URL');

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
DDWPC_Test_State::$options['default_comment_status'] = 'open';
DDWPC_Test_State::$options['default_ping_status'] = 'open';
unset(DDWPC_Test_State::$options['ddwpc_previous_defaults']);
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
assert_eq('open', DDWPC_Test_State::$options['default_comment_status'] ?? null,
    'toggle OFF restores the previous comment default');
assert_eq('open', DDWPC_Test_State::$options['default_ping_status'] ?? null,
    'toggle OFF restores the previous ping default');

// ---------------------------------------------------------------------------
// Summary
// ---------------------------------------------------------------------------


// Lifecycle regression #7: emulate a fresh request before re-registering filters.
foreach (['0', '1'] as $state) {
    DDWPC_Test_State::$options = ['ddwpc_disable_comments' => $state, 'ddwpc_other' => 'keep'];
    $before = DDWPC_Test_State::$options;
    ddwpc_deactivate();
    assert_eq($before, DDWPC_Test_State::$options, "deactivation preserves options ($state)");
    ddwpc_activate();
    assert_eq($state, get_option('ddwpc_disable_comments'), "reactivation preserves toggle ($state)");
    DDWPC_Test_State::$filters = [];
    ddwpc_init();
    assert_eq($state === '1', isset(DDWPC_Test_State::$filters['comments_open']), "runtime filters restored ($state)");
}

function seed_comments($count) {
    $GLOBALS['fixtures'] = $GLOBALS['commentmeta'] = [];
    for ($id = 1; $id <= $count; $id++) {
        $comment = (object) array_fill_keys(ddwpc_get_comment_backup_headers(), '');
        $comment->comment_ID = $id;
        $comment->comment_approved = ['1', '0', 'spam', 'trash', 'custom'][$id % 5];
        $comment->ddwpc_post_type = 'post';
        $GLOBALS['fixtures'][$id] = $comment;
        $GLOBALS['commentmeta'][$id] = ['test' => 'value'];
    }
}
function run_cleanup(array $scopes = ['public'], bool $spam_only = false): array {
    $_POST['scopes'] = $scopes;
    $_POST['cursor'] = '0';
    $total = 0;
    do {
        try {
            if ($spam_only) ddwpc_delete_spam_comments();
            else ddwpc_delete_all_comments();
        } catch (RuntimeException $e) {
            if ($e->getMessage() !== 'json_success') throw $e;
        }
        $data = $GLOBALS['json_data'];
        $total += $data['deleted'];
        $_POST['cursor'] = (string) $data['cursor'];
    } while ($data['more']);
    unset($_POST['scopes'], $_POST['cursor']);
    return ['deleted' => $total, 'remaining' => $data['remaining']];
}
foreach ([0, 5, 1003] as $count) {
    seed_comments($count);
    if (function_exists('ddwpc_write_comment_backup')) {
        $stream = fopen('php://temp', 'w+');
        assert_eq($count, ddwpc_write_comment_backup($stream), "CSV count ($count)");
        rewind($stream);
        fgetcsv($stream, 0, ',', '"', '');
        $actual = [];
        $row_count = 0;
        while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            $actual[(int)$row[0]] = $row[10];
            $row_count++;
        }
        assert_eq($count, $row_count, "each CSV ID appears exactly once ($count)");
        assert_eq(array_map(fn($c) => $c->comment_approved, $GLOBALS['fixtures']), $actual, "CSV IDs and statuses ($count)");
        fclose($stream);
    } else {
        assert_eq(true, false, 'status-independent CSV writer exists');
    }
    $result = run_cleanup();
    assert_eq([], $GLOBALS['fixtures'], "all statuses removed ($count)");
    assert_eq([], $GLOBALS['commentmeta'], "metadata removed through WordPress API ($count)");
    assert_eq($count, $result['deleted'], "actual deletion count ($count)");
}

seed_comments(5);
$GLOBALS['fixtures'][2]->comment_type = 'note';
$GLOBALS['fixtures'][3]->ddwpc_post_type = 'product';
$result = run_cleanup();
assert_eq(true, isset($GLOBALS['fixtures'][2]), 'default cleanup preserves editor Notes');
assert_eq(true, isset($GLOBALS['fixtures'][3]), 'default cleanup preserves product reviews');
assert_eq(3, $result['deleted'], 'default cleanup deletes only ordinary comments');
assert_eq(0, $result['remaining'], 'no selected comments remain');

seed_comments(230);
$GLOBALS['fixtures'][8]->comment_type = 'note';
$GLOBALS['fixtures'][9]->ddwpc_post_type = 'product';
$GLOBALS['fixtures'][10]->comment_type = 'custom-type';
$result = run_cleanup(['public', 'reviews']);
assert_eq(228, $result['deleted'], 'batched selection deletes public comments and reviews');
assert_eq([8, 10], array_keys($GLOBALS['fixtures']), 'Notes and other custom types remain');

seed_comments(12);
$GLOBALS['fixtures'][2]->ddwpc_post_type = 'product';
$result = run_cleanup(['public'], true);
assert_eq(2, $result['deleted'], 'spam cleanup deletes only public spam');
assert_eq(true, isset($GLOBALS['fixtures'][2]), 'spam product review is preserved');
assert_eq(0, $result['remaining'], 'no public spam remains');

DDWPC_Test_State::$options['ddwpc_disable_comments'] = '1';
$note = ['comment_type' => 'note'];
assert_eq($note, ddwpc_block_public_rest_comment($note, null), 'editor Notes remain writable through REST');
assert_eq(true, is_wp_error(ddwpc_block_public_rest_comment(['comment_type' => 'comment'], null)),
    'public REST comment creation is blocked');
DDWPC_Test_State::$options['ddwpc_disable_comments'] = '0';
assert_eq(['comment_type' => 'comment'], ddwpc_block_public_rest_comment(['comment_type' => 'comment'], null),
    'REST comments work again after the toggle is off');

$formula_comment = (object) array_fill_keys(ddwpc_get_comment_backup_headers(), 'safe');
$formula_comment->comment_ID = '1';
$formula_comment->comment_post_ID = '1';
$formula_comment->comment_author = '=1+1';
$formula_comment->comment_author_email = '+cmd@example.test';
$formula_comment->comment_author_url = '-HYPERLINK("https://example.test")';
$formula_comment->comment_author_IP = '@SUM(1+1)';
$formula_comment->comment_content = "\t=1+1";
$formula_comment->comment_agent = "\r@SUM(1+1)";
$formula_comment->comment_type = " \n+1+1";
$formula_row = ddwpc_format_comment_for_backup($formula_comment);
foreach ([2, 3, 4, 5, 8, 11, 12] as $column) {
    assert_eq("'", substr($formula_row[$column], 0, 1), "formula-like CSV column $column is neutralized");
}
assert_eq('safe', ddwpc_neutralize_csv_formula('safe'), 'ordinary CSV value is unchanged');
assert_eq("'=1+1", ddwpc_neutralize_csv_formula('=1+1'), 'equals-prefixed CSV value is neutralized');
assert_eq("'  @SUM(1+1)", ddwpc_neutralize_csv_formula('  @SUM(1+1)'), 'formula after spaces is neutralized');
assert_eq("'\nplain text", ddwpc_neutralize_csv_formula("\nplain text"), 'line-break-prefixed CSV value is neutralized');

seed_comments(5);
$GLOBALS['blocked_id'] = 3;
try { ddwpc_delete_all_comments(); } catch (RuntimeException $e) {
    assert_eq('json_error', $e->getMessage(), 'failed deletion never reports full success');
}
assert_eq(2, $GLOBALS['json_data']['deleted'] ?? null, 'partial deletion count is accurate');
unset($GLOBALS['blocked_id']);


foreach (['valid_nonce', 'can_manage'] as $guard) {
    seed_comments(5);
    $GLOBALS[$guard] = false;
    try { ddwpc_delete_all_comments(); } catch (RuntimeException $e) {
        assert_eq('json_error', $e->getMessage(), "delete blocked by $guard");
    }
    assert_eq(5, count($GLOBALS['fixtures']), "unauthorized delete leaves data intact ($guard)");
    unset($GLOBALS[$guard]);
}
foreach (['nonce', 'permission'] as $guard) {
    $_GET['nonce'] = $guard === 'nonce' ? 'invalid' : 'nonce';
    $GLOBALS['can_manage'] = $guard !== 'permission';
    try { ddwpc_backup_comments(); } catch (RuntimeException $e) {
        assert_eq(true, str_starts_with($e->getMessage(), 'wp_die:'), "backup blocked by $guard");
    }
}
unset($_GET['nonce'], $GLOBALS['can_manage']);
$GLOBALS['db_read_error'] = true;
try { ddwpc_delete_all_comments(); } catch (RuntimeException $e) {
    assert_eq('json_error', $e->getMessage(), 'database failure cannot report deletion success');
}
$stream = fopen('php://temp', 'w+');
try {
    ddwpc_write_comment_backup($stream);
    assert_eq(true, false, 'backup must fail on database error');
} catch (RuntimeException $e) {
    assert_eq('Could not read comments.', $e->getMessage(), 'backup rejects failed database reads');
}
fclose($stream);
unset($GLOBALS['db_read_error']);
echo "\n--- Summary ---\n";
if (count($failures) === 0) {
    echo "All assertions passed.\n";
    exit(0);
} else {
    echo count($failures) . " failed assertion(s):\n";
    foreach ($failures as $f) { echo "$f\n"; }
    exit(1);
}
