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
wp_clean_themes_cache();
switch_theme('greenfarm');

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

/**
 * Render a theme template against a real WordPress query.
 *
 * @param string   $template Relative theme template path.
 * @param WP_Query $query    Query exposed to the template Loop.
 */
function greenfarm_render_template(string $template, WP_Query $query): string
{
    global $post, $wp_query, $wp_the_query;

    $previous_query     = $wp_query;
    $previous_wp_query  = $wp_the_query;
    $previous_post      = $post ?? null;
    $wp_query           = $query;
    $wp_the_query       = $query;

    ob_start();
    $path = dirname(__DIR__) . '/' . $template;
    if (file_exists($path)) {
        require $path;
    }
    $html = (string) ob_get_clean();

    $wp_query     = $previous_query;
    $wp_the_query = $previous_wp_query;
    $post         = $previous_post;
    wp_reset_postdata();

    return $html;
}

/**
 * Capture WordPress robots output for a specific query context.
 */
function greenfarm_render_robots(WP_Query $query): string
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
 * Render related posts for a real current Post.
 */
function greenfarm_render_related_posts(int $post_id): string
{
    global $post, $wp_query, $wp_the_query;

    $previous_post     = $post ?? null;
    $previous_query    = $wp_query;
    $previous_wp_query = $wp_the_query;
    $query             = new WP_Query(array('p' => $post_id));
    $query->is_single  = true;
    $query->is_singular = true;
    $wp_query          = $query;
    $wp_the_query      = $query;
    $query->the_post();

    ob_start();
    $path = dirname(__DIR__) . '/template-parts/related-posts.php';
    if (file_exists($path)) {
        require $path;
    }
    $html = (string) ob_get_clean();

    $post         = $previous_post;
    $wp_query     = $previous_query;
    $wp_the_query = $previous_wp_query;
    wp_reset_postdata();

    return $html;
}

greenfarm_test(
    'blog archive card links the post title and has one page heading',
    static function (): void {
        $post_id = wp_insert_post(
            array(
                'post_title'   => 'Keeping Herbs Fresh',
                'post_content' => 'A practical herb storage guide.',
                'post_excerpt' => 'Keep herbs fresh for longer.',
                'post_status'  => 'publish',
            )
        );
        $query            = new WP_Query(array('p' => $post_id));
        $query->is_home   = true;
        $query->is_single = false;
        $html             = greenfarm_render_template('home.php', $query);

        greenfarm_expect(
            1 === substr_count($html, '<h1'),
            'blog archive must render exactly one H1; got ' . substr_count($html, '<h1') . ' in ' . substr(strip_tags($html), 0, 180)
        );
        greenfarm_expect(str_contains($html, 'Keeping Herbs Fresh'), 'post title is missing');
        greenfarm_expect(str_contains($html, get_permalink($post_id)), 'post permalink is missing');
    }
);

greenfarm_test(
    'empty search renders one heading, an accessible empty state, and noindex robots',
    static function (): void {
        $query              = new WP_Query(array('s' => 'greenfarm-no-result-token'));
        $query->is_search   = true;
        $query->is_home     = false;
        $query->is_archive  = false;
        $html               = greenfarm_render_template('search.php', $query);

        greenfarm_expect(
            1 === substr_count($html, '<h1'),
            'search must render exactly one H1; got ' . substr_count($html, '<h1') . ' in ' . substr(strip_tags($html), 0, 180)
        );
        greenfarm_expect(str_contains($html, 'No results'), 'search empty state is missing in ' . substr(strip_tags($html), 0, 240));

        $robots = greenfarm_render_robots($query);
        greenfarm_expect(str_contains($robots, 'noindex'), 'search robots must include noindex');
    }
);

greenfarm_test(
    'category archive uses its term as the sole heading and breadcrumb context',
    static function (): void {
        $category_id = wp_insert_term('Food Storage', 'category');
        $category_id = is_wp_error($category_id) ? 0 : (int) $category_id['term_id'];
        $post_id     = wp_insert_post(
            array(
                'post_title'  => 'Store Greens Well',
                'post_status' => 'publish',
                'post_category' => array($category_id),
            )
        );
        $query                  = new WP_Query(array('cat' => $category_id, 'p' => $post_id));
        $query->is_category     = true;
        $query->is_archive      = true;
        $query->is_home         = false;
        $query->queried_object  = get_category($category_id);
        $query->queried_object_id = $category_id;
        $html                   = greenfarm_render_template('category.php', $query);

        greenfarm_expect(1 === substr_count($html, '<h1'), 'category archive must render exactly one H1');
        greenfarm_expect(str_contains($html, 'Food Storage'), 'category name is missing');
        greenfarm_expect(str_contains($html, 'BreadcrumbList'), 'breadcrumb structured data is missing');
    }
);

greenfarm_test(
    'single post renders one title heading inside a semantic article',
    static function (): void {
        $post_id = wp_insert_post(
            array(
                'post_title'   => 'How GreenFarm Builds Healthy Soil',
                'post_content' => '<h2>Start with compost</h2><p>Healthy soil supports healthy crops.</p>',
                'post_status'  => 'publish',
            )
        );
        $query               = new WP_Query(array('p' => $post_id));
        $query->is_single    = true;
        $query->is_singular  = true;
        $query->is_home      = false;
        $html                = greenfarm_render_template('single.php', $query);

        greenfarm_expect(1 === substr_count($html, '<h1'), 'single post must render exactly one H1');
        greenfarm_expect(str_contains($html, '<article'), 'semantic article element is missing');
        greenfarm_expect(str_contains($html, 'How GreenFarm Builds Healthy Soil'), 'single post title is missing');
        greenfarm_expect(str_contains($html, '<h2>Start with compost</h2>'), 'native post content is missing');
        greenfarm_expect(str_contains($html, '"@type":"Article"'), 'Article structured data is missing');
        greenfarm_expect(str_contains($html, 'article-meta__author'), 'article author is missing');
        greenfarm_expect(str_contains($html, 'data-share'), 'share controls are missing');
    }
);

greenfarm_test(
    'single post hides updated date when it matches publication date',
    static function (): void {
        $post_id = wp_insert_post(
            array(
                'post_title'        => 'Seasonal Harvest Notes',
                'post_content'      => 'Fresh from the field.',
                'post_status'       => 'publish',
                'post_date'         => '2026-09-01 08:00:00',
                'post_date_gmt'     => '2026-09-01 08:00:00',
                'post_modified'     => '2026-09-01 08:00:00',
                'post_modified_gmt' => '2026-09-01 08:00:00',
            )
        );
        $query              = new WP_Query(array('p' => $post_id));
        $query->is_single   = true;
        $query->is_singular = true;
        $query->is_home     = false;
        $html               = greenfarm_render_template('single.php', $query);

        greenfarm_expect(str_contains($html, 'article-meta__published'), 'published date is missing');
        greenfarm_expect(! str_contains($html, 'article-meta__updated'), 'unchanged post must not show an updated date');
    }
);

greenfarm_test(
    'single post shows an updated date after a material revision',
    static function (): void {
        $post_id = wp_insert_post(
            array(
                'post_title'    => 'Revised Harvest Guide',
                'post_content'  => 'Revised guidance.',
                'post_status'   => 'publish',
                'post_date'     => '2025-01-01 08:00:00',
                'post_date_gmt' => '2025-01-01 08:00:00',
            )
        );
        global $wpdb;
        $wpdb->update(
            $wpdb->posts,
            array(
                'post_modified'     => '2025-01-05 08:00:00',
                'post_modified_gmt' => '2025-01-05 08:00:00',
            ),
            array('ID' => $post_id)
        );
        clean_post_cache($post_id);
        $query               = new WP_Query(array('p' => $post_id));
        $query->is_single    = true;
        $query->is_singular  = true;
        $query->is_home      = false;
        $html                = greenfarm_render_template('single.php', $query);

        greenfarm_expect(str_contains($html, 'article-meta__updated'), 'materially revised post must show updated date');
    }
);

greenfarm_test(
    'related posts exclude the current post and require a shared category',
    static function (): void {
        $term = wp_insert_term('Soil Health', 'category');
        $category_id = is_wp_error($term) ? (int) $term->get_error_data('term_exists') : (int) $term['term_id'];
        $current_id = wp_insert_post(array('post_title' => 'Healthy Soil Guide', 'post_status' => 'publish', 'post_category' => array($category_id)));
        $related_id = wp_insert_post(array('post_title' => 'Compost Basics', 'post_status' => 'publish', 'post_category' => array($category_id)));
        wp_insert_post(array('post_title' => 'Unrelated Orchard Notes', 'post_status' => 'publish'));
        $html = greenfarm_render_related_posts($current_id);

        greenfarm_expect(str_contains($html, 'Compost Basics'), 'shared-category article is missing');
        greenfarm_expect(! str_contains($html, 'Healthy Soil Guide'), 'current article must be excluded');
        greenfarm_expect(! str_contains($html, 'Unrelated Orchard Notes'), 'unrelated article must be excluded');
        greenfarm_expect(str_contains($html, get_permalink($related_id)), 'related article permalink is missing');
    }
);

greenfarm_test(
    'uncategorized post omits the related posts section',
    static function (): void {
        $post_id = wp_insert_post(array('post_title' => 'A Quiet Field Note', 'post_status' => 'publish'));
        wp_set_object_terms($post_id, array(), 'category');
        $html = greenfarm_render_related_posts($post_id);

        greenfarm_expect('' === trim($html), 'uncategorized post must not render related posts');
    }
);

greenfarm_test(
    'share enhancement is enqueued only for a single post request',
    static function (): void {
        global $wp_query, $wp_the_query;

        $post_id = wp_insert_post(array('post_title' => 'Shareable Farm Guide', 'post_status' => 'publish'));
        $query = new WP_Query(array('p' => $post_id));
        $query->is_single = true;
        $query->is_singular = true;
        $wp_query = $query;
        $wp_the_query = $query;
        wp_dequeue_script('greenfarm-share');
        greenfarm_enqueue_assets();

        greenfarm_expect(wp_script_is('greenfarm-share', 'enqueued'), 'single post must enqueue share enhancement');
    }
);

echo "\n{$greenfarm_tests} tests, {$greenfarm_failures} failures\n";
exit($greenfarm_failures > 0 ? 1 : 0);
