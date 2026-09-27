<?php
/**
 * GreenFarm Core integration tests executed in WordPress Playground.
 */

declare(strict_types=1);

require '/wordpress/wp-load.php';

$greenfarm_core_failures = 0;
$greenfarm_core_tests    = 0;

function greenfarm_core_test(string $name, callable $test): void
{
    global $greenfarm_core_failures, $greenfarm_core_tests;

    ++$greenfarm_core_tests;

    try {
        $test();
        echo "PASS: {$name}\n";
    } catch (Throwable $error) {
        ++$greenfarm_core_failures;
        echo "FAIL: {$name} — {$error->getMessage()}\n";
    }
}

function greenfarm_core_expect(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

$greenfarm_core_plugin = dirname(__DIR__) . '/greenfarm-core.php';

greenfarm_core_test(
    'plugin bootstrap exists and exposes the registration API',
    static function () use ($greenfarm_core_plugin): void {
        greenfarm_core_expect(file_exists($greenfarm_core_plugin), 'greenfarm-core.php is missing');
        require_once $greenfarm_core_plugin;
        greenfarm_core_expect(function_exists('greenfarm_core_register_content_types'), 'registration function is missing');
    }
);

if (file_exists($greenfarm_core_plugin)) {
    require_once $greenfarm_core_plugin;
}
if (function_exists('greenfarm_core_register_content_types')) {
    greenfarm_core_register_content_types();
}

greenfarm_core_test(
    'Product is a public searchable REST content type with stable routes and supports',
    static function (): void {
        $product = get_post_type_object('greenfarm_product');
        greenfarm_core_expect($product instanceof WP_Post_Type, 'greenfarm_product is not registered');
        greenfarm_core_expect(true === $product->public, 'Product must be public');
        greenfarm_core_expect(true === $product->show_in_rest, 'Product must be visible in REST');
        greenfarm_core_expect(false === $product->exclude_from_search, 'Product must be searchable');
        greenfarm_core_expect('products' === $product->has_archive, 'Product archive slug must be products');
        greenfarm_core_expect('products' === ($product->rewrite['slug'] ?? ''), 'Product single rewrite slug must be products');

        $expected = array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author');
        foreach ($expected as $support) {
            greenfarm_core_expect(post_type_supports('greenfarm_product', $support), "Product support {$support} is missing");
        }
        foreach (array('comments', 'trackbacks', 'page-attributes', 'custom-fields') as $unsupported) {
            greenfarm_core_expect(! post_type_supports('greenfarm_product', $unsupported), "Product must not enable {$unsupported}");
        }
    }
);

greenfarm_core_test(
    'Farm Story is a public searchable REST content type with stable routes and supports',
    static function (): void {
        $story = get_post_type_object('farm_story');
        greenfarm_core_expect($story instanceof WP_Post_Type, 'farm_story is not registered');
        greenfarm_core_expect(true === $story->public, 'Farm Story must be public');
        greenfarm_core_expect(true === $story->show_in_rest, 'Farm Story must be visible in REST');
        greenfarm_core_expect(false === $story->exclude_from_search, 'Farm Story must be searchable');
        greenfarm_core_expect('farm-stories' === $story->has_archive, 'Farm Story archive slug must be farm-stories');
        greenfarm_core_expect('farm-stories' === ($story->rewrite['slug'] ?? ''), 'Farm Story single rewrite slug must be farm-stories');

        foreach (array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author') as $support) {
            greenfarm_core_expect(post_type_supports('farm_story', $support), "Farm Story support {$support} is missing");
        }
    }
);

greenfarm_core_test(
    'Product Category is hierarchical, public, REST-visible, and Product-only',
    static function (): void {
        $taxonomy = get_taxonomy('product_category');
        greenfarm_core_expect($taxonomy instanceof WP_Taxonomy, 'product_category is not registered');
        greenfarm_core_expect(true === $taxonomy->public, 'Product Category must be public');
        greenfarm_core_expect(true === $taxonomy->hierarchical, 'Product Category must be hierarchical');
        greenfarm_core_expect(true === $taxonomy->show_in_rest, 'Product Category must be visible in REST');
        greenfarm_core_expect(true === $taxonomy->show_admin_column, 'Product Category admin column is missing');
        greenfarm_core_expect('products/category' === ($taxonomy->rewrite['slug'] ?? ''), 'Product Category rewrite slug is incorrect');
        greenfarm_core_expect(array('greenfarm_product') === array_values($taxonomy->object_type), 'Product Category must attach only to Products');
    }
);

greenfarm_core_test(
    'activation hooks exist and normal registration does not flush rewrites',
    static function () use ($greenfarm_core_plugin): void {
        greenfarm_core_expect(function_exists('greenfarm_core_activate'), 'activation callback is missing');
        greenfarm_core_expect(function_exists('greenfarm_core_deactivate'), 'deactivation callback is missing');

        $plugin_basename = plugin_basename($greenfarm_core_plugin);
        greenfarm_core_expect(false !== has_action('activate_' . $plugin_basename, 'greenfarm_core_activate'), 'activation hook is not registered');
        greenfarm_core_expect(false !== has_action('deactivate_' . $plugin_basename, 'greenfarm_core_deactivate'), 'deactivation hook is not registered');

        $rewrite_updates = 0;
        $track_updates   = static function ($value) use (&$rewrite_updates) {
            ++$rewrite_updates;
            return $value;
        };
        add_filter('pre_update_option_rewrite_rules', $track_updates);
        greenfarm_core_register_content_types();
        remove_filter('pre_update_option_rewrite_rules', $track_updates);

        greenfarm_core_expect(0 === $rewrite_updates, 'normal content registration must not flush rewrite rules');
    }
);

echo "\n{$greenfarm_core_tests} tests, {$greenfarm_core_failures} failures\n";
exit($greenfarm_core_failures > 0 ? 1 : 0);
