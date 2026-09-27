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

    $path = dirname(__DIR__) . '/' . $template;
    if (! file_exists($path)) {
        throw new RuntimeException(sprintf('Missing template: %s', $template));
    }

    $previous_post     = $post ?? null;
    $previous_query    = $wp_query;
    $previous_wp_query = $wp_the_query;
    $wp_query          = $query;
    $wp_the_query      = $query;

    ob_start();
    require $path;
    $html = (string) ob_get_clean();

    $post         = $previous_post;
    $wp_query     = $previous_query;
    $wp_the_query = $previous_wp_query;
    wp_reset_postdata();

    return $html;
}

/**
 * Capture robots output for the supplied main query.
 */
function greenfarm_content_render_robots(WP_Query $query): string
{
    global $wp_query, $wp_the_query;

    $previous_query    = $wp_query;
    $previous_wp_query = $wp_the_query;
    $wp_query          = $query;
    $wp_the_query      = $query;

    ob_start();
    wp_robots();
    $robots = (string) ob_get_clean();

    $wp_query     = $previous_query;
    $wp_the_query = $previous_wp_query;

    return $robots;
}

/**
 * Read breadcrumb names from the rendered JSON-LD.
 *
 * @return array<int, string>
 */
function greenfarm_content_breadcrumb_names(string $html): array
{
    if (! preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches)) {
        return array();
    }

    $schema = json_decode($matches[1], true);
    if (! is_array($schema) || 'BreadcrumbList' !== ($schema['@type'] ?? '')) {
        return array();
    }

    return array_column($schema['itemListElement'] ?? array(), 'name');
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
        $draft_product_id = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Integrated Draft Product', 'post_status' => 'draft'));

        $story_ids = array();
        for ($index = 1; $index <= 3; ++$index) {
            $story_ids[] = wp_insert_post(
                array(
                    'post_type'    => 'farm_story',
                    'post_title'   => sprintf('Integrated Story %d', $index),
                    'post_excerpt' => sprintf('Integrated Story excerpt %d.', $index),
                    'post_status'  => 'publish',
                    'post_date'    => sprintf('2026-09-%02d 09:00:00', 19 + $index),
                )
            );
        }
        $draft_story_id = wp_insert_post(array('post_type' => 'farm_story', 'post_title' => 'Integrated Draft Story', 'post_status' => 'draft'));

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

        foreach (array_merge($products, $story_ids, array($draft_product_id, $draft_story_id, $page_id)) as $post_id) {
            wp_delete_post($post_id, true);
        }
    }
);

greenfarm_content_test(
    'Product archive uses the main Loop, native pagination, breadcrumbs, and indexable robots',
    static function (): void {
        $product_ids = array();
        for ($index = 1; $index <= 3; ++$index) {
            $product_ids[] = wp_insert_post(
                array(
                    'post_type'   => 'greenfarm_product',
                    'post_title'  => sprintf('Archive Product %d', $index),
                    'post_status' => 'publish',
                    'post_date'   => sprintf('2026-08-%02d 08:00:00', $index),
                )
            );
        }
        $draft_id = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Archive Draft Product', 'post_status' => 'draft'));

        $query                       = new WP_Query(array('post_type' => 'greenfarm_product', 'post_status' => 'publish', 'posts_per_page' => 2, 'paged' => 1));
        $query->is_archive           = true;
        $query->is_post_type_archive = true;
        $query->queried_object       = get_post_type_object('greenfarm_product');
        $robots                     = greenfarm_content_render_robots($query);
        $html                       = greenfarm_content_render_template('archive-greenfarm_product.php', $query);

        greenfarm_content_expect(1 === substr_count($html, '<h1'), 'Product archive must have one H1');
        greenfarm_content_expect(str_contains($html, '<h1>Products</h1>'), 'Product archive heading is not contextual');
        greenfarm_content_expect(2 === preg_match_all('/<article[^>]*\bproduct-card\b[^>]*>.*?<h2\b/s', $html), 'Product archive must render two H2 cards from the main Loop');
        greenfarm_content_expect(str_contains($html, get_permalink($product_ids[2])), 'newest Product canonical link is missing');
        greenfarm_content_expect(! str_contains($html, 'Archive Draft Product'), 'draft Product leaked into its archive');
        greenfarm_content_expect(str_contains($html, 'navigation pagination'), 'Product archive must render native pagination');
        greenfarm_content_expect(array('Home', 'Products') === greenfarm_content_breadcrumb_names($html), 'Product breadcrumb JSON-LD does not match its hierarchy');
        greenfarm_content_expect(str_contains($html, '>Home</a>') && str_contains($html, '>Products</span>'), 'Product visible breadcrumbs do not match their JSON-LD');
        greenfarm_content_expect(! str_contains($robots, 'noindex'), 'populated Product archive must remain indexable');

        foreach (array_merge($product_ids, array($draft_id)) as $post_id) {
            wp_delete_post($post_id, true);
        }
    }
);

greenfarm_content_test(
    'Product Category archive escapes its description and exposes the full breadcrumb hierarchy',
    static function (): void {
        $term = wp_insert_term(
            'Root Vegetables',
            'product_category',
            array('description' => '<strong>Earth grown</strong><script>alert(1)</script>')
        );
        greenfarm_content_expect(! is_wp_error($term), 'Product Category fixture could not be created');
        $term_id = (int) $term['term_id'];

        $product_id = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Category Carrot', 'post_status' => 'publish'));
        $draft_id   = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Category Draft Product', 'post_status' => 'draft'));
        wp_set_object_terms($product_id, array($term_id), 'product_category');
        wp_set_object_terms($draft_id, array($term_id), 'product_category');

        $query                  = new WP_Query(
            array(
                'post_type'      => 'greenfarm_product',
                'post_status'    => 'publish',
                'posts_per_page' => 10,
                'tax_query'      => array(
                    array('taxonomy' => 'product_category', 'field' => 'term_id', 'terms' => $term_id),
                ),
            )
        );
        $query->is_archive            = true;
        $query->is_tax                = true;
        $query->is_post_type_archive  = false;
        $query->queried_object        = get_term($term_id, 'product_category');
        $query->queried_object_id = $term_id;
        $robots                = greenfarm_content_render_robots($query);
        $html                  = greenfarm_content_render_template('taxonomy-product_category.php', $query);

        greenfarm_content_expect(1 === substr_count($html, '<h1'), 'Product Category archive must have one H1');
        greenfarm_content_expect(str_contains($html, '<h1>Root Vegetables</h1>'), 'Product Category H1 is missing');
        greenfarm_content_expect(str_contains($html, '<strong>Earth grown</strong>'), 'allowed Product Category description markup is missing');
        greenfarm_content_expect(! str_contains($html, '<script>'), 'unsafe Product Category description markup was not escaped');
        greenfarm_content_expect(1 === preg_match_all('/<article[^>]*\bproduct-card\b[^>]*>.*?<h2\b/s', $html), 'Product Category must render published H2 Product cards');
        greenfarm_content_expect(! str_contains($html, 'Category Draft Product'), 'draft Product leaked into its category archive');
        greenfarm_content_expect(array('Home', 'Products', 'Root Vegetables') === greenfarm_content_breadcrumb_names($html), 'Product Category breadcrumb JSON-LD is incomplete');
        greenfarm_content_expect(str_contains($html, '>Products</a>') && str_contains($html, '>Root Vegetables</span>'), 'Product Category visible breadcrumbs are incomplete');
        greenfarm_content_expect(! str_contains($robots, 'noindex'), 'populated Product Category archive must remain indexable');

        wp_delete_post($product_id, true);
        wp_delete_post($draft_id, true);
        wp_delete_term($term_id, 'product_category');
    }
);

greenfarm_content_test(
    'Farm Story archive uses H2 cards, pagination, breadcrumbs, and published content only',
    static function (): void {
        $story_ids = array();
        for ($index = 1; $index <= 3; ++$index) {
            $story_ids[] = wp_insert_post(
                array(
                    'post_type'   => 'farm_story',
                    'post_title'  => sprintf('Archive Story %d', $index),
                    'post_status' => 'publish',
                    'post_date'   => sprintf('2026-07-%02d 08:00:00', $index),
                )
            );
        }
        $draft_id = wp_insert_post(array('post_type' => 'farm_story', 'post_title' => 'Archive Draft Story', 'post_status' => 'draft'));

        $query                       = new WP_Query(array('post_type' => 'farm_story', 'post_status' => 'publish', 'posts_per_page' => 2, 'paged' => 1));
        $query->is_archive           = true;
        $query->is_post_type_archive = true;
        $query->queried_object       = get_post_type_object('farm_story');
        $robots                     = greenfarm_content_render_robots($query);
        $html                       = greenfarm_content_render_template('archive-farm_story.php', $query);

        greenfarm_content_expect(1 === substr_count($html, '<h1'), 'Farm Story archive must have one H1');
        greenfarm_content_expect(str_contains($html, '<h1>Farm Stories</h1>'), 'Farm Story archive heading is not contextual');
        greenfarm_content_expect(2 === preg_match_all('/<article[^>]*\bstory-card\b[^>]*>.*?<h2\b/s', $html), 'Farm Story archive must render two H2 cards');
        greenfarm_content_expect(str_contains($html, get_permalink($story_ids[2])), 'newest Farm Story canonical link is missing');
        greenfarm_content_expect(! str_contains($html, 'Archive Draft Story'), 'draft Farm Story leaked into its archive');
        greenfarm_content_expect(str_contains($html, 'navigation pagination'), 'Farm Story archive must render native pagination');
        greenfarm_content_expect(array('Home', 'Farm Stories') === greenfarm_content_breadcrumb_names($html), 'Farm Story breadcrumb JSON-LD does not match its hierarchy');
        greenfarm_content_expect(str_contains($html, '>Home</a>') && str_contains($html, '>Farm Stories</span>'), 'Farm Story visible breadcrumbs do not match their JSON-LD');
        greenfarm_content_expect(! str_contains($robots, 'noindex'), 'populated Farm Story archive must remain indexable');

        foreach (array_merge($story_ids, array($draft_id)) as $post_id) {
            wp_delete_post($post_id, true);
        }
    }
);

greenfarm_content_test(
    'empty business archives render accessible recovery content and noindex follow robots',
    static function (): void {
        $contexts = array(
            array('archive-greenfarm_product.php', 'greenfarm_product', false),
            array('archive-farm_story.php', 'farm_story', false),
        );

        $empty_term = wp_insert_term('Empty Harvest', 'product_category');
        greenfarm_content_expect(! is_wp_error($empty_term), 'empty Product Category fixture could not be created');
        $contexts[] = array('taxonomy-product_category.php', 'greenfarm_product', (int) $empty_term['term_id']);

        foreach ($contexts as [$template, $post_type, $term_id]) {
            $args = array('post_type' => $post_type, 'post__in' => array(0));
            if ($term_id) {
                $args['tax_query'] = array(array('taxonomy' => 'product_category', 'field' => 'term_id', 'terms' => $term_id));
            }
            $query             = new WP_Query($args);
            $query->is_archive = true;
            if ($term_id) {
                $query->is_tax               = true;
                $query->is_post_type_archive = false;
                $query->queried_object       = get_term($term_id, 'product_category');
                $query->queried_object_id    = $term_id;
            } else {
                $query->is_post_type_archive = true;
                $query->queried_object       = get_post_type_object($post_type);
            }

            $robots = greenfarm_content_render_robots($query);
            $html   = greenfarm_content_render_template($template, $query);

            greenfarm_content_expect(str_contains($html, 'class="empty-state"'), sprintf('%s must render the accessible empty state', $template));
            greenfarm_content_expect(str_contains($html, 'aria-labelledby="greenfarm-empty-title"'), sprintf('%s empty state needs an accessible name', $template));
            greenfarm_content_expect(str_contains($robots, 'noindex') && str_contains($robots, 'follow'), sprintf('%s empty archive must emit noindex,follow', $template));
        }

        wp_delete_term((int) $empty_term['term_id'], 'product_category');
    }
);

echo "\n{$greenfarm_content_tests} tests, {$greenfarm_content_failures} failures\n";
exit($greenfarm_content_failures > 0 ? 1 : 0);
