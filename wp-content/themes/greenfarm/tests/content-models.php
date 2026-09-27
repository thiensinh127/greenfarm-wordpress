<?php
/**
 * GreenFarm Core + theme integration tests.
 *
 * @package GreenFarm
 */

declare(strict_types=1);

require '/wordpress/wp-load.php';

$greenfarm_content_failures = 0;
$greenfarm_content_tests    = 0;

function greenfarm_content_test(string $name, callable $test): void
{
    global $greenfarm_content_failures, $greenfarm_content_tests;

    ++$greenfarm_content_tests;

    try {
        $test();
        echo "PASS: {$name}\n";
    } catch (Throwable $error) {
        ++$greenfarm_content_failures;
        echo "FAIL: {$name} — {$error->getMessage()}\n";
    }
}

function greenfarm_content_expect(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

wp_clean_themes_cache();
switch_theme('greenfarm');
require_once dirname(__DIR__) . '/functions.php';
do_action('after_setup_theme');

require_once '/wordpress/wp-content/plugins/greenfarm-core/greenfarm-core.php';
greenfarm_core_register_content_types();
greenfarm_core_register_product_meta();

/**
 * Render a shared card against a real current post.
 *
 * @param array<string, mixed> $args Template arguments.
 */
function greenfarm_content_render_card(string $name, int $post_id, array $args = array()): string
{
    global $post;

    $previous_post = $post ?? null;
    $post          = get_post($post_id);
    setup_postdata($post);

    ob_start();
    get_template_part('template-parts/content', $name, $args);
    $html = (string) ob_get_clean();

    $post = $previous_post;
    wp_reset_postdata();

    return $html;
}

/**
 * Render a theme template against a real main query.
 */
function greenfarm_content_render_template(string $template, WP_Query $query): string
{
    global $post, $wp_query, $wp_the_query;

    $previous_post     = $post ?? null;
    $previous_query    = $wp_query;
    $previous_wp_query = $wp_the_query;
    $wp_query          = $query;
    $wp_the_query      = $query;

    ob_start();
    require dirname(__DIR__) . '/' . $template;
    $html = (string) ob_get_clean();

    $post         = $previous_post;
    $wp_query     = $previous_query;
    $wp_the_query = $previous_wp_query;
    wp_reset_postdata();

    return $html;
}

function greenfarm_content_front_page_query(int $page_id): WP_Query
{
    $query                    = new WP_Query(array('page_id' => $page_id));
    $query->is_page           = true;
    $query->is_singular       = true;
    $query->is_home           = false;
    $query->is_front_page     = true;
    $query->queried_object    = get_post($page_id);
    $query->queried_object_id = $page_id;

    return $query;
}

greenfarm_content_test(
    'shared Product card uses canonical metadata and requested semantic arguments',
    static function (): void {
        $product_id = wp_insert_post(
            array(
                'post_type'    => 'greenfarm_product',
                'post_title'   => 'Purple Carrots',
                'post_excerpt' => 'Sweet roots from healthy soil.',
                'post_status'  => 'publish',
                'post_date'    => '2025-01-01 08:00:00',
            )
        );
        update_post_meta($product_id, 'greenfarm_availability', 'limited');

        $html = greenfarm_content_render_card(
            'product-card',
            $product_id,
            array(
                'heading_level' => 'h3',
                'card_class'    => 'test-product-card',
                'image_size'    => 'medium',
            )
        );

        greenfarm_content_expect(str_contains($html, '<h3'), 'requested H3 is missing');
        greenfarm_content_expect(str_contains($html, 'test-product-card'), 'requested card class is missing');
        greenfarm_content_expect(str_contains($html, 'Limited availability'), 'allowlisted availability label is missing');
        greenfarm_content_expect(str_contains($html, 'Sweet roots from healthy soil.'), 'Product excerpt is missing');
        greenfarm_content_expect(str_contains($html, get_permalink($product_id)), 'Product canonical link is missing');
        greenfarm_content_expect(! str_contains($html, '<img'), 'no-image Product must not output an image');

        wp_delete_post($product_id, true);
    }
);

greenfarm_content_test(
    'shared cards omit invalid Product availability and keep Story content independent',
    static function (): void {
        $product_id = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Invalid Availability Product', 'post_status' => 'publish'));
        $invalid_meta = static fn ($value, $object_id, $meta_key) => $object_id === $product_id && 'greenfarm_availability' === $meta_key ? array('preorder') : $value;
        add_filter('get_post_metadata', $invalid_meta, 10, 3);
        $product_html = greenfarm_content_render_card('product-card', $product_id);
        remove_filter('get_post_metadata', $invalid_meta, 10);

        $story_id = wp_insert_post(
            array(
                'post_type'    => 'farm_story',
                'post_title'   => 'Planting Before Sunrise',
                'post_excerpt' => 'A morning with the field team.',
                'post_status'  => 'publish',
                'post_date'    => '2025-01-02 08:00:00',
            )
        );
        $story_html = greenfarm_content_render_card('story-card', $story_id, array('heading_level' => 'h3', 'card_class' => 'test-story-card'));

        greenfarm_content_expect(! str_contains($product_html, 'preorder'), 'invalid availability value leaked into Product card');
        greenfarm_content_expect(! str_contains($product_html, '__availability'), 'invalid availability left an empty badge');
        greenfarm_content_expect(str_contains($story_html, '<h3'), 'Story requested H3 is missing');
        greenfarm_content_expect(str_contains($story_html, 'test-story-card'), 'Story requested class is missing');
        greenfarm_content_expect(str_contains($story_html, 'A morning with the field team.'), 'Story excerpt is missing');
        greenfarm_content_expect(str_contains($story_html, get_permalink($story_id)), 'Story canonical link is missing');
        greenfarm_content_expect(! str_contains($story_html, 'availability'), 'Story card must not render Product metadata');

        wp_delete_post($product_id, true);
        wp_delete_post($story_id, true);
    }
);

greenfarm_content_test(
    'Homepage reuses bounded H3 Product and Story cards and restores its Page context',
    static function (): void {
        $products = array();
        for ($index = 1; $index <= 5; ++$index) {
            $products[] = wp_insert_post(
                array(
                    'post_type'    => 'greenfarm_product',
                    'post_title'   => sprintf('Integrated Product %d', $index),
                    'post_excerpt' => sprintf('Integrated Product excerpt %d.', $index),
                    'post_status'  => 'publish',
                    'post_date'    => sprintf('2026-09-%02d 08:00:00', 19 + $index),
                )
            );
        }
        update_post_meta($products[4], 'greenfarm_availability', 'available');
        wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Integrated Draft Product', 'post_status' => 'draft'));

        for ($index = 1; $index <= 3; ++$index) {
            wp_insert_post(
                array(
                    'post_type'    => 'farm_story',
                    'post_title'   => sprintf('Integrated Story %d', $index),
                    'post_excerpt' => sprintf('Integrated Story excerpt %d.', $index),
                    'post_status'  => 'publish',
                    'post_date'    => sprintf('2026-09-%02d 09:00:00', 19 + $index),
                )
            );
        }
        wp_insert_post(array('post_type' => 'farm_story', 'post_title' => 'Integrated Draft Story', 'post_status' => 'draft'));

        $page_id           = wp_insert_post(array('post_type' => 'page', 'post_title' => 'Integrated Homepage', 'post_status' => 'publish'));
        $post_before_proof = 0;
        $capture_context   = static function () use (&$post_before_proof): void {
            $post_before_proof = get_the_ID();
        };
        add_action('get_template_part_template-parts/home/proof', $capture_context);
        $html = greenfarm_content_render_template('front-page.php', greenfarm_content_front_page_query($page_id));
        remove_action('get_template_part_template-parts/home/proof', $capture_context);

        greenfarm_content_expect(4 === preg_match_all('/<article[^>]+class="[^"]*\bhome-product-card\b[^"]*"/i', $html), 'Homepage must limit Products to four shared cards');
        greenfarm_content_expect(2 === preg_match_all('/<article[^>]+class="[^"]*\bhome-story-card\b[^"]*"/i', $html), 'Homepage must limit Stories to two shared cards');
        greenfarm_content_expect(4 === preg_match_all('/<article[^>]*\bhome-product-card\b[^>]*>.*?<h3\b/s', $html), 'Homepage Product cards must use H3 headings');
        greenfarm_content_expect(2 === preg_match_all('/<article[^>]*\bhome-story-card\b[^>]*>.*?<h3\b/s', $html), 'Homepage Story cards must use H3 headings');
        greenfarm_content_expect(str_contains($html, 'Available now'), 'Homepage must use canonical availability label');
        greenfarm_content_expect(! str_contains($html, 'Integrated Product 1'), 'oldest Product must be outside the Homepage limit');
        greenfarm_content_expect(! str_contains($html, 'Integrated Story 1'), 'oldest Story must be outside the Homepage limit');
        greenfarm_content_expect(! str_contains($html, 'Integrated Draft'), 'draft business content must not render');
        greenfarm_content_expect($page_id === $post_before_proof, 'Homepage Page context was not restored');
    }
);

echo "\n{$greenfarm_content_tests} tests, {$greenfarm_content_failures} failures\n";
exit($greenfarm_content_failures > 0 ? 1 : 0);
