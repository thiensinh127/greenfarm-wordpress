<?php
/**
 * GreenFarm Core integration tests executed in WordPress Playground.
 */

declare(strict_types=1);

require '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/template.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
require_once ABSPATH . 'wp-admin/includes/screen.php';

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

greenfarm_core_test(
    'Product metadata has a REST-visible product-only contract',
    static function (): void {
        greenfarm_core_expect(function_exists('greenfarm_core_register_product_meta'), 'Product meta registration function is missing');
        greenfarm_core_register_product_meta();

        $registered = get_registered_meta_keys('post', 'greenfarm_product');
        $strings    = array(
            'greenfarm_origin',
            'greenfarm_farming_method',
            'greenfarm_harvest_season',
            'greenfarm_storage_instructions',
            'greenfarm_availability',
        );
        foreach ($strings as $key) {
            greenfarm_core_expect(isset($registered[$key]), "{$key} is not registered");
            greenfarm_core_expect('string' === $registered[$key]['type'], "{$key} must be a string");
            greenfarm_core_expect(true === $registered[$key]['single'], "{$key} must be single-value");
            greenfarm_core_expect(true === $registered[$key]['show_in_rest'], "{$key} must be REST-visible");
            greenfarm_core_expect(is_callable($registered[$key]['sanitize_callback']), "{$key} sanitizer is missing");
        }

        greenfarm_core_expect(isset($registered['greenfarm_gallery_ids']), 'greenfarm_gallery_ids is not registered');
        $gallery = $registered['greenfarm_gallery_ids'];
        greenfarm_core_expect('array' === $gallery['type'], 'gallery meta must be an array');
        greenfarm_core_expect(true === $gallery['single'], 'gallery meta must be single-value');
        greenfarm_core_expect('array' === ($gallery['show_in_rest']['schema']['type'] ?? ''), 'gallery REST schema must be an array');
        greenfarm_core_expect('integer' === ($gallery['show_in_rest']['schema']['items']['type'] ?? ''), 'gallery REST items must be integers');

        foreach ($strings as $key) {
            greenfarm_core_expect(! isset(get_registered_meta_keys('post', 'post')[$key]), "{$key} leaked onto Posts");
        }
    }
);

greenfarm_core_test(
    'Product metadata sanitizers enforce availability and ordered positive gallery IDs',
    static function (): void {
        greenfarm_core_expect(function_exists('greenfarm_core_sanitize_availability'), 'availability sanitizer is missing');
        greenfarm_core_expect(function_exists('greenfarm_core_sanitize_gallery_ids'), 'gallery sanitizer is missing');

        foreach (array('', 'available', 'limited', 'seasonal', 'unavailable') as $value) {
            greenfarm_core_expect($value === greenfarm_core_sanitize_availability($value), "valid availability {$value} was rejected");
        }
        greenfarm_core_expect('' === greenfarm_core_sanitize_availability('preorder'), 'invalid availability must become empty');
        greenfarm_core_expect('' === greenfarm_core_sanitize_availability(array('available')), 'non-string availability must become empty');
        greenfarm_core_expect(
            array(3, 4, 9) === greenfarm_core_sanitize_gallery_ids(array('3', -1, '3', 'nope', 4, 0, 9)),
            'gallery array must retain ordered unique positive integers'
        );
        greenfarm_core_expect(
            array(8, 2) === greenfarm_core_sanitize_gallery_ids('8,0,2,8,-5'),
            'gallery CSV must retain ordered unique positive integers'
        );
    }
);

greenfarm_core_test(
    'Product meta boxes render native fields and the stored gallery contract',
    static function (): void {
        global $wp_meta_boxes;

        greenfarm_core_expect(function_exists('greenfarm_core_add_product_meta_boxes'), 'meta box registration function is missing');
        greenfarm_core_expect(function_exists('greenfarm_core_render_product_details_meta_box'), 'details renderer is missing');
        greenfarm_core_expect(function_exists('greenfarm_core_render_product_gallery_meta_box'), 'gallery renderer is missing');

        $post_id = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Meta Box Product', 'post_status' => 'draft'));
        update_post_meta($post_id, 'greenfarm_gallery_ids', array(13, 21));
        $post = get_post($post_id);

        set_current_screen('greenfarm_product');
        greenfarm_core_add_product_meta_boxes();
        greenfarm_core_expect(isset($wp_meta_boxes['greenfarm_product']['normal']['default']['greenfarm_core_product_details']), 'details meta box is missing');
        greenfarm_core_expect(isset($wp_meta_boxes['greenfarm_product']['side']['default']['greenfarm_core_product_gallery']), 'gallery meta box is missing');

        ob_start();
        greenfarm_core_render_product_details_meta_box($post);
        $details = (string) ob_get_clean();
        greenfarm_core_expect(str_contains($details, 'greenfarm_core_product_meta_nonce'), 'save nonce is missing');
        greenfarm_core_expect(str_contains($details, 'greenfarm_origin'), 'Origin field is missing');
        greenfarm_core_expect(str_contains($details, 'greenfarm_storage_instructions'), 'Storage field is missing');
        greenfarm_core_expect(str_contains($details, 'greenfarm_availability'), 'Availability field is missing');

        ob_start();
        greenfarm_core_render_product_gallery_meta_box($post);
        $gallery = (string) ob_get_clean();
        greenfarm_core_expect(str_contains($gallery, 'name="greenfarm_gallery_ids"'), 'gallery hidden field is missing');
        greenfarm_core_expect(str_contains($gallery, 'value="13,21"'), 'stored gallery order is missing');
    }
);

greenfarm_core_test(
    'valid Product save sanitizes details, removes omitted details, and preserves omitted gallery',
    static function (): void {
        greenfarm_core_expect(function_exists('greenfarm_core_save_product_meta'), 'Product save handler is missing');

        $admin_id = wp_create_user('greenfarm_admin', 'greenfarm-pass', 'admin@example.test');
        (new WP_User($admin_id))->set_role('administrator');
        wp_set_current_user($admin_id);

        $post_id = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Secure Product', 'post_status' => 'draft'));
        update_post_meta($post_id, 'greenfarm_harvest_season', 'Old season');
        update_post_meta($post_id, 'greenfarm_gallery_ids', array(7, 8));

        $_POST = array(
            'greenfarm_core_product_meta_nonce' => wp_create_nonce('greenfarm_core_save_product_meta'),
            'greenfarm_origin'                  => '  <b>Da Lat</b>  ',
            'greenfarm_farming_method'          => ' Regenerative ',
            'greenfarm_storage_instructions'    => "Keep cool\n<script>alert(1)</script>",
            'greenfarm_availability'            => 'limited',
        );
        greenfarm_core_save_product_meta($post_id);

        greenfarm_core_expect('Da Lat' === get_post_meta($post_id, 'greenfarm_origin', true), 'Origin was not sanitized');
        greenfarm_core_expect('Regenerative' === get_post_meta($post_id, 'greenfarm_farming_method', true), 'Farming method was not sanitized');
        greenfarm_core_expect('' === get_post_meta($post_id, 'greenfarm_harvest_season', true), 'omitted detail was not deleted');
        greenfarm_core_expect('Keep cool' === get_post_meta($post_id, 'greenfarm_storage_instructions', true), 'Storage instructions were not sanitized');
        greenfarm_core_expect('limited' === get_post_meta($post_id, 'greenfarm_availability', true), 'Availability was not saved');
        greenfarm_core_expect(array(7, 8) === get_post_meta($post_id, 'greenfarm_gallery_ids', true), 'omitted gallery must be preserved');

        $_POST = array();
        wp_set_current_user(0);
    }
);

greenfarm_core_test(
    'invalid nonce, subscriber, autosave, and revision contexts preserve Product metadata',
    static function (): void {
        greenfarm_core_expect(function_exists('greenfarm_core_save_product_meta'), 'Product save handler is missing');

        $admin_id = wp_create_user('greenfarm_security_admin', 'greenfarm-pass', 'security-admin@example.test');
        (new WP_User($admin_id))->set_role('administrator');
        wp_set_current_user($admin_id);
        $post_id = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Protected Product', 'post_status' => 'draft'));
        update_post_meta($post_id, 'greenfarm_origin', 'Protected origin');

        $_POST = array('greenfarm_core_product_meta_nonce' => 'invalid', 'greenfarm_origin' => 'Changed');
        greenfarm_core_save_product_meta($post_id);
        greenfarm_core_expect('Protected origin' === get_post_meta($post_id, 'greenfarm_origin', true), 'invalid nonce changed metadata');

        $subscriber_id = wp_create_user('greenfarm_subscriber', 'greenfarm-pass', 'subscriber@example.test');
        (new WP_User($subscriber_id))->set_role('subscriber');
        wp_set_current_user($subscriber_id);
        $_POST = array(
            'greenfarm_core_product_meta_nonce' => wp_create_nonce('greenfarm_core_save_product_meta'),
            'greenfarm_origin'                  => 'Changed',
        );
        greenfarm_core_save_product_meta($post_id);
        greenfarm_core_expect('Protected origin' === get_post_meta($post_id, 'greenfarm_origin', true), 'subscriber changed metadata');

        wp_set_current_user($admin_id);
        $_POST = array(
            'greenfarm_core_product_meta_nonce' => wp_create_nonce('greenfarm_core_save_product_meta'),
            'greenfarm_origin'                  => 'Changed',
        );
        $autosave_id = wp_insert_post(
            array(
                'post_type'   => 'revision',
                'post_status' => 'inherit',
                'post_parent' => $post_id,
                'post_name'   => $post_id . '-autosave-v1',
                'post_title'  => 'Autosave',
            )
        );
        greenfarm_core_save_product_meta($autosave_id);
        greenfarm_core_expect('Protected origin' === get_post_meta($post_id, 'greenfarm_origin', true), 'autosave changed parent metadata');

        $revision_id = wp_insert_post(
            array(
                'post_type'   => 'revision',
                'post_status' => 'inherit',
                'post_parent' => $post_id,
                'post_name'   => $post_id . '-revision-v1',
                'post_title'  => 'Revision',
            )
        );
        greenfarm_core_save_product_meta($revision_id);
        greenfarm_core_expect('Protected origin' === get_post_meta($post_id, 'greenfarm_origin', true), 'revision changed parent metadata');

        $_POST = array();
        wp_set_current_user(0);
    }
);

echo "\n{$greenfarm_core_tests} tests, {$greenfarm_core_failures} failures\n";
exit($greenfarm_core_failures > 0 ? 1 : 0);
