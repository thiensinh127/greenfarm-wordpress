<?php
/**
 * Minimal integration test runner executed inside WordPress Playground.
 *
 * @package GreenFarm
 */

declare(strict_types=1);

require '/wordpress/wp-load.php';

$greenfarm_failures = 0;
$greenfarm_tests    = 0;

function greenfarm_test(string $name, callable $test): void
{
    global $greenfarm_failures, $greenfarm_tests;

    ++$greenfarm_tests;

    try {
        $test();
        echo "PASS: {$name}\n";
    } catch (Throwable $error) {
        ++$greenfarm_failures;
        echo "FAIL: {$name} — {$error->getMessage()}\n";
    }
}

function greenfarm_expect(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

$greenfarm_functions = dirname(__DIR__) . '/functions.php';

remove_theme_support('post-thumbnails');
remove_theme_support('html5');

if (file_exists($greenfarm_functions)) {
    require_once $greenfarm_functions;
    do_action('after_setup_theme');
}

greenfarm_test(
    'theme registers post thumbnails and HTML5 search forms',
    static function (): void {
        greenfarm_expect(current_theme_supports('post-thumbnails'), 'post-thumbnails support is missing');
        greenfarm_expect(current_theme_supports('html5', 'search-form'), 'HTML5 search-form support is missing');
    }
);

echo "\n{$greenfarm_tests} tests, {$greenfarm_failures} failures\n";
exit($greenfarm_failures > 0 ? 1 : 0);
