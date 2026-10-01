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
 * Capture conditional public assets for a main-query context.
 *
 * @return array<string, bool>
 */
function greenfarm_content_assets(WP_Query $query): array
{
    global $wp_query, $wp_the_query;

    $previous_query    = $wp_query;
    $previous_wp_query = $wp_the_query;
    $wp_query          = $query;
    $wp_the_query      = $query;

    foreach (array('greenfarm-content-models', 'greenfarm-home') as $handle) {
        wp_dequeue_style($handle);
    }
    wp_dequeue_script('greenfarm-motion');
    greenfarm_enqueue_assets();

    $assets = array(
        'content_css' => wp_style_is('greenfarm-content-models', 'enqueued'),
        'home_css'    => wp_style_is('greenfarm-home', 'enqueued'),
        'motion'      => wp_script_is('greenfarm-motion', 'enqueued'),
    );

    $wp_query     = $previous_query;
    $wp_the_query = $previous_wp_query;

    return $assets;
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

/**
 * Build a single-business-content query.
 */
function greenfarm_content_single_query(int $post_id, string $post_type): WP_Query
{
    $query                    = new WP_Query(array('post_type' => $post_type, 'p' => $post_id));
    $query->is_single         = true;
    $query->is_singular       = true;
    $query->is_archive        = false;
    $query->queried_object    = get_post($post_id);
    $query->queried_object_id = $post_id;

    return $query;
}

/**
 * Create an image attachment fixture with responsive metadata.
 */
function greenfarm_content_image(string $name, string $caption = ''): int
{
    $file = '2026/09/' . sanitize_file_name($name) . '.jpg';
    $id   = wp_insert_attachment(
        array(
            'post_title'     => $name,
            'post_excerpt'   => $caption,
            'post_status'    => 'inherit',
            'post_mime_type' => 'image/jpeg',
            'guid'           => home_url('/wp-content/uploads/' . $file),
        ),
        $file
    );
    update_post_meta($id, '_wp_attached_file', $file);
    wp_update_attachment_metadata(
        $id,
        array(
            'width'  => 1200,
            'height' => 800,
            'file'   => $file,
            'sizes'  => array(
                'medium' => array(
                    'file'      => sanitize_file_name($name) . '-300x200.jpg',
                    'width'     => 300,
                    'height'    => 200,
                    'mime-type' => 'image/jpeg',
                ),
                'large' => array(
                    'file'      => sanitize_file_name($name) . '-1024x683.jpg',
                    'width'     => 1024,
                    'height'    => 683,
                    'mime-type' => 'image/jpeg',
                ),
            ),
        )
    );

    return $id;
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
        update_post_meta($product_id, 'greenfarm_origin', 'North Field');
        update_post_meta($product_id, 'greenfarm_harvest_season', 'Early autumn');

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
        greenfarm_content_expect(str_contains($html, 'North Field') && str_contains($html, 'Early autumn'), 'Product origin and harvest season are missing');
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
    'shared cards select responsive image sizes for their actual layout context',
    static function (): void {
        $image_id   = greenfarm_content_image('card-context-image');
        $product_id = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Sized Product', 'post_status' => 'publish'));
        $story_id   = wp_insert_post(array('post_type' => 'farm_story', 'post_title' => 'Sized Story', 'post_status' => 'publish'));
        set_post_thumbnail($product_id, $image_id);
        set_post_thumbnail($story_id, $image_id);

        $product_archive = greenfarm_content_render_card('product-card', $product_id);
        $product_home    = greenfarm_content_render_card('product-card', $product_id, array('image_context' => 'home'));
        $story_archive   = greenfarm_content_render_card('story-card', $story_id);
        $story_featured  = greenfarm_content_render_card('story-card', $story_id, array('image_context' => 'home-featured'));
        $story_secondary = greenfarm_content_render_card('story-card', $story_id, array('image_context' => 'home-secondary'));

        greenfarm_content_expect(str_contains($product_archive, '(min-width: 75rem) 23rem'), 'Product archive card sizes are not container-capped');
        greenfarm_content_expect(str_contains($product_home, '(min-width: 75rem) 17rem'), 'Homepage Product card sizes do not match four columns');
        greenfarm_content_expect(str_contains($story_archive, '(min-width: 75rem) 34rem'), 'Story archive card sizes do not match two columns');
        greenfarm_content_expect(str_contains($story_featured, '(min-width: 75rem) 46rem'), 'featured Homepage Story sizes do not match the wide column');
        greenfarm_content_expect(str_contains($story_secondary, '(min-width: 75rem) 22rem'), 'secondary Homepage Story sizes do not match the narrow column');

        wp_delete_post($product_id, true);
        wp_delete_post($story_id, true);
        wp_delete_attachment($image_id, true);
    }
);

greenfarm_content_test(
    'business-content CSS gives primary text links effective 44px targets',
    static function (): void {
        $css = (string) file_get_contents(dirname(__DIR__) . '/assets/css/content-models.css');

        greenfarm_content_expect(
            (bool) preg_match('/\.breadcrumbs a,[\s\S]*?\.product-header__categories a\s*\{[^}]*display:\s*inline-flex;[^}]*min-height:\s*2\.75rem;/s', $css),
            'breadcrumb and Product Category links need an effective 44px target'
        );
        greenfarm_content_expect(
            (bool) preg_match('/\.breadcrumbs ol\s*\{[^}]*align-items:\s*center;[\s\S]*?\.breadcrumbs li\s*\{[^}]*align-items:\s*center;[^}]*display:\s*inline-flex;/s', $css),
            'breadcrumb links and current-page text must share one vertical alignment'
        );
        greenfarm_content_expect(
            (bool) preg_match('/\.product-card h2 a,[\s\S]*?\.story-card h2 a\s*\{[^}]*display:\s*inline-flex;[^}]*min-height:\s*2\.75rem;/s', $css),
            'card title links need an effective 44px target'
        );
        greenfarm_content_expect(
            (bool) preg_match('/\.archive-hero,[\s\S]*?\.story-header\s*\{[^}]*padding:\s*clamp\(2rem,\s*5vw,\s*4\.5rem\)\s+1rem;/s', $css),
            'archive breadcrumb hero must use compact spacing on large screens'
        );
    }
);

greenfarm_content_test(
    'image media uses rounded borders, keyboard-safe hover polish, and reduced motion fallbacks',
    static function (): void {
        $content_css = (string) file_get_contents(dirname(__DIR__) . '/assets/css/content-models.css');
        $blog_css    = (string) file_get_contents(dirname(__DIR__) . '/assets/css/blog.css');
        $home_css    = (string) file_get_contents(dirname(__DIR__) . '/assets/css/home.css');
        $core_css    = (string) file_get_contents(dirname(__DIR__) . '/assets/css/core-pages.css');

        greenfarm_content_expect(str_contains($content_css, '.product-card:focus-within .product-card__media'), 'Product media needs a keyboard-visible hover state');
        greenfarm_content_expect(str_contains($blog_css, '.post-card:focus-within .post-card__media'), 'Journal media needs a keyboard-visible hover state');
        greenfarm_content_expect(str_contains($home_css, '.home-hero__media') && str_contains($home_css, 'border-radius: var(--gf-radius);'), 'Homepage hero needs a modern rounded image frame');
        greenfarm_content_expect(
            (bool) preg_match('/\.core-page-hero__media\s*\{[^}]*border-radius:\s*var\(--gf-radius\);/s', $core_css),
            'Core page hero needs the shared 20px media radius'
        );
        greenfarm_content_expect(! str_contains($home_css, '.home-hero__media:hover') && ! str_contains($core_css, '.core-page-hero__media:hover'), 'Standalone hero images must not animate without a keyboard equivalent');
        greenfarm_content_expect(! str_contains($content_css, '.product-gallery__item:hover img'), 'Standalone gallery images must not animate without a keyboard equivalent');
        greenfarm_content_expect(
            (bool) preg_match('/\.product-gallery__item img\s*\{[^}]*border:\s*1px solid rgba\(168, 214, 68, 0\.38\);[^}]*border-radius:\s*calc\(var\(--gf-radius\) - 0\.375rem\);/s', $content_css),
            'Product gallery needs the bordered 14px image treatment'
        );
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
        $post_before_cta = 0;
        $capture_context = static function () use (&$post_before_cta): void {
            $post_before_cta = get_the_ID();
        };
        add_action('get_template_part_template-parts/home/cta', $capture_context);
        $html = greenfarm_content_render_template('front-page.php', greenfarm_content_front_page_query($page_id));
        remove_action('get_template_part_template-parts/home/cta', $capture_context);

        greenfarm_content_expect(4 === preg_match_all('/<article[^>]+class="[^"]*\bhome-product-card\b[^"]*"/i', $html), 'Homepage must limit Products to four shared cards');
        greenfarm_content_expect(2 === preg_match_all('/<article[^>]+class="[^"]*\bhome-story-card\b[^"]*"/i', $html), 'Homepage must limit Stories to two shared cards');
        greenfarm_content_expect(4 === preg_match_all('/<article[^>]*\bhome-product-card\b[^>]*>.*?<h3\b/s', $html), 'Homepage Product cards must use H3 headings');
        greenfarm_content_expect(2 === preg_match_all('/<article[^>]*\bhome-story-card\b[^>]*>.*?<h3\b/s', $html), 'Homepage Story cards must use H3 headings');
        greenfarm_content_expect(str_contains($html, 'Available now'), 'Homepage must use canonical availability label');
        greenfarm_content_expect(! str_contains($html, 'Integrated Product 1'), 'oldest Product must be outside the Homepage limit');
        greenfarm_content_expect(! str_contains($html, 'Integrated Story 1'), 'oldest Story must be outside the Homepage limit');
        greenfarm_content_expect(! str_contains($html, 'Integrated Draft'), 'draft business content must not render');
        greenfarm_content_expect($page_id === $post_before_cta, 'Homepage Page context was not restored');

        foreach (array_merge($products, $story_ids, array($draft_product_id, $draft_story_id, $page_id)) as $post_id) {
            wp_delete_post($post_id, true);
        }
    }
);

greenfarm_content_test(
    'content-model styles and motion load only on their public routes',
    static function (): void {
        $product_id = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Asset Product', 'post_status' => 'publish'));
        $story_id   = wp_insert_post(array('post_type' => 'farm_story', 'post_title' => 'Asset Story', 'post_status' => 'publish'));
        $page_id    = wp_insert_post(array('post_type' => 'page', 'post_title' => 'Asset Page', 'post_status' => 'publish'));
        $term       = wp_insert_term('Asset Category', 'product_category');
        greenfarm_content_expect(! is_wp_error($term), 'asset Product Category fixture could not be created');
        $term_id = (int) $term['term_id'];

        $product_archive                       = new WP_Query(array('post_type' => 'greenfarm_product'));
        $product_archive->is_archive           = true;
        $product_archive->is_post_type_archive = true;
        $product_archive->queried_object       = get_post_type_object('greenfarm_product');

        $taxonomy_query                       = new WP_Query(array('post_type' => 'greenfarm_product', 'tax_query' => array(array('taxonomy' => 'product_category', 'field' => 'term_id', 'terms' => $term_id))));
        $taxonomy_query->is_archive           = true;
        $taxonomy_query->is_tax               = true;
        $taxonomy_query->is_post_type_archive = false;
        $taxonomy_query->queried_object       = get_term($term_id, 'product_category');
        $taxonomy_query->queried_object_id    = $term_id;

        foreach (array($product_archive, greenfarm_content_single_query($product_id, 'greenfarm_product'), greenfarm_content_single_query($story_id, 'farm_story'), $taxonomy_query) as $query) {
            $assets = greenfarm_content_assets($query);
            greenfarm_content_expect($assets['content_css'], 'content-model stylesheet is missing on a business-content route');
            greenfarm_content_expect($assets['motion'], 'shared motion is missing on a business-content route');
        }

        $page_query                    = new WP_Query(array('page_id' => $page_id));
        $page_query->is_page           = true;
        $page_query->is_singular       = true;
        $page_query->is_front_page     = false;
        $page_query->queried_object    = get_post($page_id);
        $page_query->queried_object_id = $page_id;
        $page_assets                   = greenfarm_content_assets($page_query);
        greenfarm_content_expect(! $page_assets['content_css'] && ! $page_assets['motion'], 'unrelated Page must not load content-model assets');

        $previous_show_on_front = get_option('show_on_front');
        $previous_page_on_front = get_option('page_on_front');
        update_option('show_on_front', 'page');
        update_option('page_on_front', $page_id);
        $home_assets = greenfarm_content_assets(greenfarm_content_front_page_query($page_id));
        update_option('show_on_front', $previous_show_on_front);
        update_option('page_on_front', $previous_page_on_front);
        greenfarm_content_expect($home_assets['home_css'] && $home_assets['motion'], 'Homepage must retain its own stylesheet and shared motion');
        greenfarm_content_expect(! $home_assets['content_css'], 'Homepage shared cards must not require the content-model stylesheet');

        foreach (array($product_id, $story_id, $page_id) as $post_id) {
            wp_delete_post($post_id, true);
        }
        wp_delete_term($term_id, 'product_category');
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
        greenfarm_content_expect(str_contains($html, 'product-archive--editorial'), 'Product archive must expose its editorial layout hook');
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
        greenfarm_content_expect(str_contains($html, 'story-archive--editorial'), 'Farm Story archive must expose its editorial layout hook');
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
    'editorial archive CSS gives Stories a lead card hierarchy',
    static function (): void {
        $css = (string) file_get_contents(dirname(__DIR__) . '/assets/css/content-models.css');

        greenfarm_content_expect(
            (bool) preg_match('/\\.story-archive--editorial \\.story-card:first-child\\s*\\{[^}]*grid-column:\\s*1\\s*\\/\\s*-1;/s', $css),
            'Farm Story archive must promote its first card across the grid'
        );
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

greenfarm_content_test(
    'Product single renders safe optional facts, descriptions, media, and Product-only navigation',
    static function (): void {
        global $wpdb;

        $term = wp_insert_term('Leafy Greens', 'product_category');
        greenfarm_content_expect(! is_wp_error($term), 'Product Category fixture could not be created');
        $term_id = (int) $term['term_id'];

        $previous_id = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Earlier Kale', 'post_status' => 'publish', 'post_date' => '2025-03-01 08:00:00'));
        $product_id  = wp_insert_post(
            array(
                'post_type'    => 'greenfarm_product',
                'post_title'   => 'Tuscan Kale',
                'post_excerpt' => 'A tender, mineral-rich harvest.',
                'post_content' => '<h2>About this harvest</h2><p>Picked in the cool morning.</p>',
                'post_status'  => 'publish',
                'post_date'    => '2025-03-02 08:00:00',
            )
        );
        $next_id     = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Later Spinach', 'post_status' => 'publish', 'post_date' => '2025-03-03 08:00:00'));
        $story_id    = wp_insert_post(array('post_type' => 'farm_story', 'post_title' => 'Interleaved Story', 'post_status' => 'publish', 'post_date' => '2025-03-02 12:00:00'));
        wp_set_object_terms($product_id, array($term_id), 'product_category');

        update_post_meta($product_id, 'greenfarm_origin', 'North Field');
        update_post_meta($product_id, 'greenfarm_harvest_season', 'Autumn');
        update_post_meta($product_id, 'greenfarm_availability', 'limited');

        $featured_id = greenfarm_content_image('product-featured');
        $gallery_one = greenfarm_content_image('gallery-one', 'Rows ready for harvest');
        $gallery_two = greenfarm_content_image('gallery-two');
        $deleted_id  = greenfarm_content_image('gallery-deleted');
        wp_delete_attachment($deleted_id, true);
        $document_id = wp_insert_attachment(
            array(
                'post_title'     => 'Field notes PDF',
                'post_status'    => 'inherit',
                'post_mime_type' => 'application/pdf',
                'guid'           => home_url('/wp-content/uploads/2026/09/field-notes.pdf'),
            ),
            '2026/09/field-notes.pdf'
        );
        set_post_thumbnail($product_id, $featured_id);

        update_post_meta($product_id, 'greenfarm_storage_instructions', 'placeholder');
        $wpdb->update(
            $wpdb->postmeta,
            array('meta_value' => 'Keep cool <script>alert(1)</script>'),
            array('post_id' => $product_id, 'meta_key' => 'greenfarm_storage_instructions')
        );
        update_post_meta($product_id, 'greenfarm_gallery_ids', array($gallery_one));
        $wpdb->update(
            $wpdb->postmeta,
            array('meta_value' => maybe_serialize(array($gallery_two, -5, $gallery_one, $gallery_two, $deleted_id, $document_id))),
            array('post_id' => $product_id, 'meta_key' => 'greenfarm_gallery_ids')
        );
        clean_post_cache($product_id);

        $html = greenfarm_content_render_template('single-greenfarm_product.php', greenfarm_content_single_query($product_id, 'greenfarm_product'));

        greenfarm_content_expect(1 === substr_count($html, '<h1'), 'Product single must have one H1');
        greenfarm_content_expect(str_contains($html, 'A tender, mineral-rich harvest.'), 'Product short description is missing');
        greenfarm_content_expect(str_contains($html, '<h2>About this harvest</h2>'), 'Product full description is missing');
        greenfarm_content_expect(str_contains($html, 'product-featured'), 'Product featured image is missing');
        greenfarm_content_expect(str_contains($html, '(min-width: 75rem) 69rem'), 'Product featured image sizes do not match its container');
        greenfarm_content_expect(str_contains($html, '<dl class="product-facts"'), 'Product facts must use a description list');
        greenfarm_content_expect(str_contains($html, 'North Field') && str_contains($html, 'Autumn'), 'populated Product facts are missing');
        greenfarm_content_expect(! str_contains($html, 'Farming method'), 'empty Product fact must be omitted');
        greenfarm_content_expect(str_contains($html, 'Limited availability'), 'allowlisted Product availability label is missing');
        greenfarm_content_expect(str_contains($html, home_url('/contact/')), 'Product single must offer a low-pressure Contact path');
        greenfarm_content_expect(! str_contains($html, '<script>alert(1)</script>'), 'storage instructions were not escaped');
        greenfarm_content_expect(str_contains($html, 'Keep cool &lt;script&gt;alert(1)&lt;/script&gt;'), 'escaped storage instructions are missing');
        greenfarm_content_expect(str_contains($html, get_term_link($term_id, 'product_category')), 'Product Category canonical link is missing');
        greenfarm_content_expect(str_contains($html, 'Earlier Kale') && str_contains($html, 'Later Spinach'), 'Product adjacent navigation is incomplete');
        greenfarm_content_expect(! str_contains($html, 'Interleaved Story'), 'Product navigation leaked another post type');

        greenfarm_content_expect(2 === preg_match_all('/<figure class="product-gallery__item"/', $html), 'gallery must render only two unique valid images');
        greenfarm_content_expect(strpos($html, 'gallery-two') < strpos($html, 'gallery-one'), 'gallery image order was not preserved');
        greenfarm_content_expect(str_contains($html, 'loading="lazy"'), 'gallery images must load lazily');
        greenfarm_content_expect((bool) preg_match('/product-gallery__item.*?<img[^>]+width="[1-9][0-9]*"[^>]+height="[1-9][0-9]*"/s', $html), 'gallery intrinsic image dimensions are missing');
        greenfarm_content_expect(str_contains($html, 'srcset='), 'gallery responsive srcset is missing');
        greenfarm_content_expect(str_contains($html, 'sizes='), 'gallery responsive sizes are missing');
        greenfarm_content_expect(str_contains($html, '(min-width: 75rem) 22rem'), 'gallery sizes do not match its three-column layout');
        greenfarm_content_expect(str_contains($html, 'Rows ready for harvest'), 'gallery caption is missing');
        greenfarm_content_expect(! str_contains($html, 'field-notes.pdf'), 'non-image attachment leaked into gallery');

        $expected_crumbs = array('Home', 'Products', 'Leafy Greens', 'Tuscan Kale');
        greenfarm_content_expect($expected_crumbs === greenfarm_content_breadcrumb_names($html), 'Product single breadcrumb JSON-LD is incomplete');
        greenfarm_content_expect(str_contains($html, '>Leafy Greens</a>') && str_contains($html, '>Tuscan Kale</span>'), 'Product visible breadcrumbs do not match JSON-LD');

        foreach (array($previous_id, $product_id, $next_id, $story_id, $featured_id, $gallery_one, $gallery_two, $document_id) as $id) {
            wp_delete_post($id, true);
        }
        wp_delete_term($term_id, 'product_category');
    }
);

greenfarm_content_test(
    'Farm Story single is an independent editorial experience with Story-only navigation',
    static function (): void {
        global $wpdb;

        $author_id = wp_create_user('greenfarm_story_author', wp_generate_password(), 'story@example.com');
        wp_update_user(array('ID' => $author_id, 'display_name' => 'Maya Green'));
        $previous_id = wp_insert_post(array('post_type' => 'farm_story', 'post_title' => 'Before the Rain', 'post_status' => 'publish', 'post_date' => '2025-04-01 08:00:00'));
        $story_id    = wp_insert_post(
            array(
                'post_type'    => 'farm_story',
                'post_title'   => 'A Morning in the Orchard',
                'post_excerpt' => 'The first harvest of spring.',
                'post_content' => '<h2>At first light</h2><p>The team walks each orchard row.</p>',
                'post_status'  => 'publish',
                'post_author'  => $author_id,
                'post_date'    => '2025-04-02 08:00:00',
            )
        );
        $next_id     = wp_insert_post(array('post_type' => 'farm_story', 'post_title' => 'After the Harvest', 'post_status' => 'publish', 'post_date' => '2025-04-03 08:00:00'));
        $product_id  = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Interleaved Product', 'post_status' => 'publish', 'post_date' => '2025-04-02 12:00:00'));
        $featured_id = greenfarm_content_image('story-featured');
        set_post_thumbnail($story_id, $featured_id);
        $wpdb->update(
            $wpdb->posts,
            array('post_modified' => '2025-04-05 08:00:00', 'post_modified_gmt' => '2025-04-05 08:00:00'),
            array('ID' => $story_id)
        );
        clean_post_cache($story_id);

        $html = greenfarm_content_render_template('single-farm_story.php', greenfarm_content_single_query($story_id, 'farm_story'));

        greenfarm_content_expect(1 === substr_count($html, '<h1'), 'Farm Story single must have one H1');
        greenfarm_content_expect((bool) preg_match('/<article[^>]+class="[^"]*\bfarm-story\b/', $html), 'Farm Story needs semantic article markup');
        greenfarm_content_expect(str_contains($html, 'Maya Green'), 'Farm Story author is missing');
        greenfarm_content_expect(str_contains($html, 'story-meta__published'), 'Farm Story published date is missing');
        greenfarm_content_expect(str_contains($html, 'story-meta__updated'), 'materially changed Farm Story needs an updated date');
        greenfarm_content_expect(str_contains($html, 'story-featured'), 'Farm Story featured image is missing');
        greenfarm_content_expect(str_contains($html, '(min-width: 75rem) 69rem'), 'Farm Story featured image sizes do not match its container');
        greenfarm_content_expect(str_contains($html, '<h2>At first light</h2>'), 'Farm Story content is missing');
        greenfarm_content_expect(str_contains($html, 'Before the Rain') && str_contains($html, 'After the Harvest'), 'Farm Story adjacent navigation is incomplete');
        greenfarm_content_expect(! str_contains($html, 'Interleaved Product'), 'Farm Story navigation leaked another post type');
        greenfarm_content_expect(! str_contains($html, 'article-taxonomy') && ! str_contains($html, 'article-tags'), 'Blog taxonomy UI leaked into Farm Story');
        greenfarm_content_expect(! str_contains($html, 'related-posts') && ! str_contains($html, 'data-share'), 'Blog related/share UI leaked into Farm Story');
        greenfarm_content_expect(! str_contains($html, '"@type":"Article"') && ! str_contains($html, '"@type":"Product"') && ! str_contains($html, '"@type":"Review"'), 'unsupported structured data leaked into Farm Story');

        $expected_crumbs = array('Home', 'Farm Stories', 'A Morning in the Orchard');
        greenfarm_content_expect($expected_crumbs === greenfarm_content_breadcrumb_names($html), 'Farm Story breadcrumb JSON-LD is incomplete');
        greenfarm_content_expect(str_contains($html, '>Farm Stories</a>') && str_contains($html, '>A Morning in the Orchard</span>'), 'Farm Story visible breadcrumbs do not match JSON-LD');

        foreach (array($previous_id, $story_id, $next_id, $product_id, $featured_id) as $id) {
            wp_delete_post($id, true);
        }
        if (function_exists('wp_delete_user')) {
            wp_delete_user($author_id);
        }
    }
);

echo "\n{$greenfarm_content_tests} tests, {$greenfarm_content_failures} failures\n";
exit($greenfarm_content_failures > 0 ? 1 : 0);
