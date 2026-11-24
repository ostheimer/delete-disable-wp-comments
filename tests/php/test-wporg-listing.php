<?php
/**
 * Checks the WordPress.org listing inputs that the plugin directory parses.
 *
 * WordPress.org reads readme.txt, the plugin header, the screenshot assets and
 * assets/blueprints/blueprint.json (Live Preview). Mistakes there do not break
 * the plugin itself, but they break or degrade the public listing, so they are
 * guarded here:
 *   - the readme name matches the plugin header (Plugin Check warns otherwise),
 *   - the short description fits the 150 character limit and has no markup,
 *   - there are 1 to 5 unique tags (only five are used),
 *   - every version field agrees,
 *   - each screenshot asset has exactly one caption,
 *   - the Live Preview blueprint is valid and seeds demo comments.
 *
 * Run:
 *   php tests/php/test-wporg-listing.php
 *
 * Exits 0 on success, 1 on any failed assertion.
 */

declare(strict_types=1);

$root        = dirname(__DIR__, 2);
$plugin_dir  = $root . '/wp-content/plugins/delete-disable-comments';
$readme      = (string) file_get_contents($plugin_dir . '/readme.txt');
$plugin_file = (string) file_get_contents($plugin_dir . '/delete-disable-comments.php');
$assets_dir  = $root . '/wordpress-org/assets';
$failures    = array();

function listing_check(bool $condition, string $label, string $detail = ''): void {
    global $failures;
    if ($condition) {
        echo "  ok  $label\n";
        return;
    }
    $failures[] = "FAIL $label" . ('' !== $detail ? " ($detail)" : '');
    echo "  FAIL $label" . ('' !== $detail ? " ($detail)" : '') . "\n";
}

function plugin_header(string $source, string $field): string {
    if (preg_match('/^[ \t\/*#@]*' . preg_quote($field, '/') . ':\s*(.+)$/mi', $source, $m)) {
        return trim($m[1]);
    }
    return '';
}

function readme_header(string $readme, string $field): string {
    if (preg_match('/^' . preg_quote($field, '/') . ':\s*(.+)$/mi', $readme, $m)) {
        return trim($m[1]);
    }
    return '';
}

echo "--- Readme and plugin header ---\n";

$readme_name = preg_match('/^===\s*(.+?)\s*===\s*$/m', $readme, $m) ? $m[1] : '';
$header_name = plugin_header($plugin_file, 'Plugin Name');
listing_check('' !== $readme_name, 'readme declares a plugin name');
listing_check($readme_name === $header_name, 'readme name matches plugin header', "readme \"$readme_name\", header \"$header_name\"");

// The short description is the first text line after the header block.
$before_description = (string) strstr($readme, '== Description ==', true);
$header_end         = strpos($before_description, "\n\n");
$short_description  = false === $header_end ? '' : trim((string) strtok(substr($before_description, $header_end), "\n"));
listing_check('' !== $short_description, 'readme has a short description');
listing_check(mb_strlen($short_description) <= 150, 'short description fits 150 characters', mb_strlen($short_description) . ' characters');
listing_check(!preg_match('/[<>*`\[\]]/', $short_description), 'short description has no markup');

$tags = array_values(array_filter(array_map('trim', explode(',', readme_header($readme, 'Tags')))));
listing_check(count($tags) >= 1 && count($tags) <= 5, 'readme has one to five tags', count($tags) . ' tags');
listing_check(count($tags) === count(array_unique(array_map('strtolower', $tags))), 'tags are unique');

$stable_tag = readme_header($readme, 'Stable tag');
$version    = plugin_header($plugin_file, 'Version');
$constant   = preg_match("/define\(\s*'DDWPC_VERSION'\s*,\s*'([^']+)'\s*\)/", $plugin_file, $m) ? $m[1] : '';
listing_check('' !== $stable_tag && $stable_tag === $version, 'stable tag matches plugin version', "$stable_tag / $version");
listing_check($constant === $version, 'DDWPC_VERSION matches plugin version', "$constant / $version");
listing_check(1 === preg_match('/^= ' . preg_quote($version, '/') . ' =$/m', $readme), 'changelog has an entry for the current version');

echo "--- Screenshots ---\n";

$screenshot_section = preg_match('/== Screenshots ==\s*(.*?)(?:\n== |\z)/s', $readme, $m) ? $m[1] : '';
preg_match_all('/^(\d+)\.\s+\S/m', $screenshot_section, $m);
$captions = array_map('intval', $m[1]);
$files    = array();
foreach ((array) glob($assets_dir . '/screenshot-*.png') as $file) {
    if (preg_match('/screenshot-(\d+)\.png$/', (string) $file, $fm)) {
        $files[] = (int) $fm[1];
    }
}
sort($files);
listing_check(count($files) > 0, 'screenshot assets exist');
listing_check($captions === range(1, count($captions)), 'screenshot captions are numbered 1..n', implode(',', $captions));
listing_check($captions === $files, 'each screenshot asset has one caption', 'captions ' . implode(',', $captions) . ' / files ' . implode(',', $files));

echo "--- Live Preview blueprint ---\n";

$blueprint_file = $assets_dir . '/blueprints/blueprint.json';
listing_check(is_file($blueprint_file), 'blueprint exists at assets/blueprints/blueprint.json');
$blueprint = is_file($blueprint_file) ? json_decode((string) file_get_contents($blueprint_file), true) : null;
listing_check(is_array($blueprint), 'blueprint is valid JSON', json_last_error_msg());
$blueprint = is_array($blueprint) ? $blueprint : array();
$steps     = isset($blueprint['steps']) && is_array($blueprint['steps']) ? $blueprint['steps'] : array();

listing_check(
    ($blueprint['landingPage'] ?? '') === '/wp-admin/tools.php?page=delete-disable-comments',
    'preview opens the plugin screen'
);

$installs_plugin = false;
$logs_in         = !empty($blueprint['login']);
$seed_code       = '';
foreach ($steps as $step) {
    $name = $step['step'] ?? '';
    if ('installPlugin' === $name
        && ($step['pluginData']['resource'] ?? '') === 'wordpress.org/plugins'
        && ($step['pluginData']['slug'] ?? '') === 'delete-disable-comments'
        && !empty($step['options']['activate'])) {
        $installs_plugin = true;
    }
    if ('login' === $name) {
        $logs_in = true;
    }
    if ('runPHP' === $name) {
        $seed_code .= (string) ($step['code'] ?? '');
    }
}
listing_check($installs_plugin, 'blueprint installs and activates the plugin from WordPress.org');
listing_check($logs_in, 'blueprint logs the visitor in');
listing_check(false !== strpos($seed_code, 'wp_insert_comment'), 'blueprint seeds demo comments');
listing_check(false !== strpos($seed_code, "'spam'"), 'blueprint seeds spam comments');

if ('' !== $seed_code) {
    $tmp = tempnam(sys_get_temp_dir(), 'ddwpc-blueprint-');
    file_put_contents((string) $tmp, $seed_code);
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg((string) $tmp) . ' 2>&1', $lint_output, $lint_status);
    unlink((string) $tmp);
    listing_check(0 === $lint_status, 'blueprint PHP code has valid syntax', implode(' ', $lint_output));
}

echo "\n--- Summary ---\n";
if (0 === count($failures)) {
    echo "All listing assertions passed.\n";
    exit(0);
}
echo count($failures) . " failed assertion(s):\n";
foreach ($failures as $failure) {
    echo "$failure\n";
}
exit(1);
