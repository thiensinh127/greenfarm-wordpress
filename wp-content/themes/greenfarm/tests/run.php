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
 * Build the static Page query used by front-page template tests.
 */
function greenfarm_front_page_query(int $page_id): WP_Query
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
        greenfarm_expect(str_contains($html, '<h2 class="post-card__title">'), 'archive post cards must keep H2 headings');
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

greenfarm_test(
    '404 renders one recovery heading and noindex robots',
    static function (): void {
        $query             = new WP_Query(array('p' => 99999999));
        $query->is_404     = true;
        $query->is_single  = false;
        $query->is_singular = false;
        $html              = greenfarm_render_template('404.php', $query);
        $robots            = greenfarm_render_robots($query);

        greenfarm_expect(1 === substr_count($html, '<h1'), '404 must render exactly one H1');
        greenfarm_expect(str_contains($html, 'Page not found'), '404 recovery message is missing');
        greenfarm_expect(str_contains($robots, 'noindex'), '404 robots must include noindex');
    }
);

greenfarm_test(
    'Article JSON-LD cannot be terminated by filtered author text',
    static function (): void {
        $post_id = wp_insert_post(array('post_title' => 'Safe Structured Data', 'post_status' => 'publish'));
        $query = new WP_Query(array('p' => $post_id));
        $query->is_single = true;
        $query->is_singular = true;
        $query->is_home = false;
        $malicious_author = static fn (): string => '</script><script>alert(1)</script>';
        add_filter('the_author', $malicious_author);
        $html = greenfarm_render_template('single.php', $query);
        remove_filter('the_author', $malicious_author);

        greenfarm_expect(! str_contains($html, '</script><script>'), 'JSON-LD permits a closing script sequence');
    }
);

greenfarm_test(
    'Tag and empty archive contexts emit noindex robots',
    static function (): void {
        $tag = wp_insert_term('Composting', 'post_tag');
        $tag_id = is_wp_error($tag) ? (int) $tag->get_error_data('term_exists') : (int) $tag['term_id'];
        $post_id = wp_insert_post(array('post_title' => 'Compost Notes', 'post_status' => 'publish'));
        wp_set_post_tags($post_id, array($tag_id));
        $tag_query = new WP_Query(array('tag_id' => $tag_id));
        $tag_query->is_tag = true;
        $tag_query->is_archive = true;
        $tag_query->is_home = false;

        $empty_term = wp_insert_term('Empty Topic', 'category');
        $empty_id = is_wp_error($empty_term) ? (int) $empty_term->get_error_data('term_exists') : (int) $empty_term['term_id'];
        $empty_query = new WP_Query(array('cat' => $empty_id));
        $empty_query->is_category = true;
        $empty_query->is_archive = true;
        $empty_query->is_home = false;

        greenfarm_expect(str_contains(greenfarm_render_robots($tag_query), 'noindex'), 'Tag archive must be noindex');
        greenfarm_expect(str_contains(greenfarm_render_robots($empty_query), 'noindex'), 'empty archive must be noindex');
    }
);

greenfarm_test(
    'Search heading escapes a query exactly once',
    static function (): void {
        $query = new WP_Query(array('s' => 'soil & water'));
        $query->is_search = true;
        $query->is_home = false;
        $html = greenfarm_render_template('search.php', $query);

        greenfarm_expect(str_contains($html, 'soil &amp; water'), 'search query is not safely escaped');
        greenfarm_expect(! str_contains($html, 'soil &amp;amp; water'), 'search query is double-escaped');
    }
);

greenfarm_test(
    'front page uses native Page content for a coherent hero and introduction',
    static function (): void {
        $page_id = wp_insert_post(
            array(
                'post_type'    => 'page',
                'post_title'   => 'Food grown with care',
                'post_excerpt' => 'Seasonal produce from healthy soil.',
                'post_content' => '<p>Meet the people and practices behind every harvest.</p>',
                'post_status'  => 'publish',
            )
        );
        $html = greenfarm_render_template('front-page.php', greenfarm_front_page_query($page_id));

        greenfarm_expect(1 === substr_count($html, '<h1'), 'front page must render exactly one H1');
        greenfarm_expect(str_contains($html, 'Food grown with care'), 'Page title is missing from the hero');
        greenfarm_expect(str_contains($html, 'Seasonal produce from healthy soil.'), 'Page excerpt is missing from the hero');
        greenfarm_expect(str_contains($html, 'Meet the people and practices behind every harvest.'), 'Page content is missing from the introduction');
        greenfarm_expect(str_contains($html, home_url('/products/')), 'Products CTA is missing');
        greenfarm_expect(str_contains($html, home_url('/about/')), 'About CTA is missing');
        greenfarm_expect(! preg_match('/<img[^>]+src=(?:""|\'\')/i', $html), 'front page must not output an image with an empty source');
    }
);

greenfarm_test(
    'front page hero uses responsive high-priority attachment markup',
    static function (): void {
        $page_id = wp_insert_post(
            array(
                'post_type'   => 'page',
                'post_title'  => 'GreenFarm harvest',
                'post_status' => 'publish',
            )
        );
        $attachment_id = wp_insert_attachment(
            array(
                'post_title'     => 'Green fields at harvest',
                'post_mime_type' => 'image/jpeg',
                'post_status'    => 'inherit',
                'guid'           => home_url('/wp-content/uploads/2026/09/greenfarm-hero.jpg'),
            ),
            '2026/09/greenfarm-hero.jpg',
            $page_id
        );
        update_post_meta($attachment_id, '_wp_attached_file', '2026/09/greenfarm-hero.jpg');
        wp_update_attachment_metadata(
            $attachment_id,
            array(
                'width'  => 1600,
                'height' => 900,
                'file'   => '2026/09/greenfarm-hero.jpg',
                'sizes'  => array(
                    'medium' => array(
                        'file'      => 'greenfarm-hero-300x169.jpg',
                        'width'     => 300,
                        'height'    => 169,
                        'mime-type' => 'image/jpeg',
                    ),
                    'large' => array(
                        'file'      => 'greenfarm-hero-1024x576.jpg',
                        'width'     => 1024,
                        'height'    => 576,
                        'mime-type' => 'image/jpeg',
                    ),
                ),
            )
        );
        set_post_thumbnail($page_id, $attachment_id);

        $html = greenfarm_render_template('front-page.php', greenfarm_front_page_query($page_id));

        greenfarm_expect(str_contains($html, 'fetchpriority="high"'), 'hero image must receive high fetch priority');
        greenfarm_expect(str_contains($html, 'loading="eager"'), 'hero image must load eagerly');
        greenfarm_expect((bool) preg_match('/<img[^>]+width="1600"[^>]+height="900"/i', $html), 'hero image intrinsic dimensions are missing');
        greenfarm_expect(str_contains($html, 'srcset='), 'hero image responsive srcset is missing');
        greenfarm_expect(str_contains($html, 'sizes='), 'hero image responsive sizes are missing');
    }
);

greenfarm_test(
    'front page presents values, an ordered farm process, and factual proof',
    static function (): void {
        $page_id = wp_insert_post(
            array(
                'post_type'   => 'page',
                'post_title'  => 'GreenFarm',
                'post_status' => 'publish',
            )
        );
        $html = greenfarm_render_template('front-page.php', greenfarm_front_page_query($page_id));

        greenfarm_expect(3 === substr_count($html, 'home-value-card'), 'homepage must render exactly three value cards');
        greenfarm_expect((bool) preg_match('/<ol[^>]*>.*Grow.*Harvest.*Prepare.*Share.*<\/ol>/s', $html), 'farm process must be an ordered Grow, Harvest, Prepare, Share sequence');
        greenfarm_expect(str_contains($html, 'Seasonal harvests'), 'factual seasonal proof is missing');
        greenfarm_expect(str_contains($html, 'Clear growing information'), 'factual growing-information proof is missing');
        greenfarm_expect(str_contains($html, 'Practical farm education'), 'factual education proof is missing');
        greenfarm_expect(! str_contains($html, '"@type":"Review"'), 'homepage must not emit unverified Review schema');
        greenfarm_expect(1 === substr_count($html, '<h1'), 'values and process sections must not add another H1');
    }
);

greenfarm_test(
    'front page omits optional business sections when their models do not exist',
    static function (): void {
        $page_id = wp_insert_post(array('post_type' => 'page', 'post_title' => 'Model-free Home', 'post_status' => 'publish'));
        $html    = greenfarm_render_template('front-page.php', greenfarm_front_page_query($page_id));

        greenfarm_expect(! preg_match('/<h2[^>]*>\s*Product categories\s*<\/h2>/i', $html), 'missing taxonomy must not leave a Product categories heading');
        greenfarm_expect(! preg_match('/<h2[^>]*>\s*Featured products\s*<\/h2>/i', $html), 'missing Product model must not leave a Featured products heading');
        greenfarm_expect(! preg_match('/<h2[^>]*>\s*Farm stories\s*<\/h2>/i', $html), 'missing Farm Story model must not leave a Farm stories heading');
    }
);

greenfarm_test(
    'front page renders bounded published products, categories, and farm stories',
    static function (): void {
        register_post_type('greenfarm_product', array('public' => true, 'label' => 'Products', 'supports' => array('title', 'editor', 'excerpt', 'thumbnail')));
        register_post_type('farm_story', array('public' => true, 'label' => 'Farm Stories', 'supports' => array('title', 'editor', 'excerpt', 'thumbnail')));
        register_taxonomy('product_category', 'greenfarm_product', array('public' => true, 'label' => 'Product Categories'));

        $product_ids = array();
        $term_ids    = array();
        for ($index = 1; $index <= 5; ++$index) {
            $term = wp_insert_term(sprintf('Homepage Category %d', $index), 'product_category');
            $term_ids[] = is_wp_error($term) ? (int) $term->get_error_data('term_exists') : (int) $term['term_id'];
            $product_ids[] = wp_insert_post(
                array(
                    'post_type'    => 'greenfarm_product',
                    'post_title'   => sprintf('Homepage Product %d', $index),
                    'post_excerpt' => sprintf('Product %d from this season.', $index),
                    'post_status'  => 'publish',
                    'post_date'    => sprintf('2026-09-%02d 08:00:00', 10 + $index),
                )
            );
            wp_set_object_terms($product_ids[$index - 1], array($term_ids[$index - 1]), 'product_category');
        }
        update_post_meta($product_ids[4], 'availability', 'Available now');
        $draft_product = wp_insert_post(array('post_type' => 'greenfarm_product', 'post_title' => 'Homepage Draft Product', 'post_status' => 'draft'));

        $story_ids = array();
        for ($index = 1; $index <= 3; ++$index) {
            $story_ids[] = wp_insert_post(
                array(
                    'post_type'    => 'farm_story',
                    'post_title'   => sprintf('Homepage Story %d', $index),
                    'post_excerpt' => sprintf('Story %d from the field.', $index),
                    'post_status'  => 'publish',
                    'post_date'    => sprintf('2026-09-%02d 09:00:00', 20 + $index),
                )
            );
        }
        $draft_story = wp_insert_post(array('post_type' => 'farm_story', 'post_title' => 'Homepage Draft Story', 'post_status' => 'draft'));

        $page_id               = wp_insert_post(array('post_type' => 'page', 'post_title' => 'Business Home', 'post_status' => 'publish'));
        $post_before_proof     = 0;
        $capture_page_context  = static function () use (&$post_before_proof): void {
            $post_before_proof = get_the_ID();
        };
        add_action('get_template_part_template-parts/home/proof', $capture_page_context);
        $html = greenfarm_render_template('front-page.php', greenfarm_front_page_query($page_id));
        remove_action('get_template_part_template-parts/home/proof', $capture_page_context);

        greenfarm_expect(4 === substr_count($html, 'class="home-category-card"'), 'homepage must limit Product categories to four');
        greenfarm_expect(4 === substr_count($html, 'class="home-product-card"'), 'homepage must limit Products to four');
        greenfarm_expect(2 === substr_count($html, 'class="home-story-card"'), 'homepage must limit Farm Stories to two');
        greenfarm_expect(! str_contains($html, 'Homepage Product 1'), 'oldest Product must be outside the four-card limit');
        greenfarm_expect(! str_contains($html, 'Homepage Draft Product'), 'draft Product must not render');
        greenfarm_expect(! str_contains($html, 'Homepage Story 1'), 'oldest Farm Story must be outside the two-card limit');
        greenfarm_expect(! str_contains($html, 'Homepage Draft Story'), 'draft Farm Story must not render');
        greenfarm_expect(str_contains($html, get_permalink($product_ids[4])), 'Product canonical permalink is missing');
        greenfarm_expect(str_contains($html, get_permalink($story_ids[2])), 'Farm Story canonical permalink is missing');
        greenfarm_expect(str_contains($html, (string) get_term_link($term_ids[0], 'product_category')), 'Product category canonical link is missing');
        greenfarm_expect(str_contains($html, 'Available now'), 'Product availability metadata is missing');
        greenfarm_expect(1 === substr_count($html, '<h1'), 'optional sections must preserve the single Page H1');
        greenfarm_expect($page_id === $post_before_proof, 'secondary queries must restore the homepage Page context');

        wp_delete_post($draft_product, true);
        wp_delete_post($draft_story, true);
    }
);

greenfarm_test(
    'front page renders three latest articles and a contact CTA without leaking query context',
    static function (): void {
        foreach (get_posts(array('post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1)) as $existing_post) {
            wp_delete_post($existing_post->ID, true);
        }

        $article_ids = array();
        for ($index = 1; $index <= 4; ++$index) {
            $article_ids[] = wp_insert_post(
                array(
                    'post_title'   => sprintf('Homepage Article %d', $index),
                    'post_excerpt' => sprintf('Educational article %d.', $index),
                    'post_status'  => 'publish',
                    'post_date'    => sprintf('2026-09-%02d 10:00:00', 19 + $index),
                )
            );
        }
        wp_insert_post(array('post_title' => 'Homepage Draft Article', 'post_status' => 'draft'));

        $blog_page_id = wp_insert_post(array('post_type' => 'page', 'post_title' => 'Learn', 'post_status' => 'publish'));
        update_option('page_for_posts', $blog_page_id);

        $page_id           = wp_insert_post(array('post_type' => 'page', 'post_title' => 'Editorial Home', 'post_status' => 'publish'));
        $post_before_proof = 0;
        $capture_context   = static function () use (&$post_before_proof): void {
            $post_before_proof = get_the_ID();
        };
        add_action('get_template_part_template-parts/home/proof', $capture_context);
        $html = greenfarm_render_template('front-page.php', greenfarm_front_page_query($page_id));
        remove_action('get_template_part_template-parts/home/proof', $capture_context);

        greenfarm_expect(3 === preg_match_all('/<article[^>]+class="[^"]*\bpost-card\b[^"]*"/i', $html), 'homepage must render exactly three latest-article cards');
        greenfarm_expect(3 === substr_count($html, '<h3 class="post-card__title">'), 'homepage article cards must use H3 headings');
        greenfarm_expect(str_contains($html, 'Homepage Article 4'), 'newest article is missing');
        greenfarm_expect(str_contains($html, 'Homepage Article 2'), 'third latest article is missing');
        greenfarm_expect(! str_contains($html, 'Homepage Article 1'), 'fourth article must be outside the three-card limit');
        greenfarm_expect(! str_contains($html, 'Homepage Draft Article'), 'draft article must not render');
        greenfarm_expect(str_contains($html, get_permalink($article_ids[3])), 'article canonical link is missing');
        greenfarm_expect(str_contains($html, get_permalink($blog_page_id)), 'configured Blog link is missing');
        greenfarm_expect(str_contains($html, home_url('/contact/')), 'final Contact CTA is missing');
        greenfarm_expect(! str_contains($html, '<form'), 'homepage must not render a fake newsletter form');
        greenfarm_expect($page_id === $post_before_proof, 'Latest Articles query must restore the homepage Page context');
    }
);

echo "\n{$greenfarm_tests} tests, {$greenfarm_failures} failures\n";
exit($greenfarm_failures > 0 ? 1 : 0);
