<?php
/**
 * GreenFarm theme functions.
 *
 * @package GreenFarm
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register native theme capabilities.
 */
function greenfarm_setup(): void
{
    load_theme_textdomain('greenfarm', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support(
        'html5',
        array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script')
    );

    register_nav_menus(
        array(
            'primary' => __('Primary navigation', 'greenfarm'),
            'footer'  => __('Footer navigation', 'greenfarm'),
        )
    );
}
add_action('after_setup_theme', 'greenfarm_setup');

/**
 * Expose the native excerpt field for editor-managed Pages.
 */
function greenfarm_enable_page_excerpt(): void
{
    add_post_type_support('page', 'excerpt');
}
add_action('init', 'greenfarm_enable_page_excerpt');

/**
 * Load global theme styles.
 */
function greenfarm_enqueue_assets(): void
{
    $version = wp_get_theme()->get('Version');

    wp_enqueue_style(
        'greenfarm-style',
        get_stylesheet_uri(),
        array(),
        $version
    );

    if (greenfarm_is_blog_view()) {
        wp_enqueue_style(
            'greenfarm-blog',
            get_template_directory_uri() . '/assets/css/blog.css',
            array('greenfarm-style'),
            $version
        );
    }

    if (is_front_page()) {
        wp_enqueue_style(
            'greenfarm-home',
            get_template_directory_uri() . '/assets/css/home.css',
            array('greenfarm-style'),
            $version
        );
    }

    if (greenfarm_is_content_model_view()) {
        wp_enqueue_style(
            'greenfarm-content-models',
            get_template_directory_uri() . '/assets/css/content-models.css',
            array('greenfarm-style'),
            $version
        );
    }

    if (greenfarm_is_core_page_view()) {
        wp_enqueue_style(
            'greenfarm-core-pages',
            get_template_directory_uri() . '/assets/css/core-pages.css',
            array('greenfarm-style'),
            $version
        );
    }

    if (greenfarm_is_blog_view() || greenfarm_is_content_model_view() || greenfarm_is_core_page_view() || is_front_page()) {
        wp_enqueue_script(
            'greenfarm-motion',
            get_template_directory_uri() . '/assets/js/motion.js',
            array(),
            $version,
            array(
                'strategy'  => 'defer',
                'in_footer' => true,
            )
        );
    }

    if (is_singular('post')) {
        wp_enqueue_script(
            'greenfarm-share',
            get_template_directory_uri() . '/assets/js/share.js',
            array(),
            $version,
            array(
                'strategy'  => 'defer',
                'in_footer' => true,
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'greenfarm_enqueue_assets');

/**
 * Determine whether the current request uses the editorial blog system.
 */
function greenfarm_is_blog_view(): bool
{
    return is_home() || is_category() || is_tag() || is_search() || is_singular('post');
}

/**
 * Determine whether the current request uses a Product or Farm Story view.
 */
function greenfarm_is_content_model_view(): bool
{
    return is_post_type_archive(array('greenfarm_product', 'farm_story'))
        || is_singular(array('greenfarm_product', 'farm_story'))
        || is_tax('product_category');
}

/**
 * Determine whether the current Page uses a GreenFarm core Page template.
 */
function greenfarm_is_core_page_view(): bool
{
    return is_page_template(array('page-templates/about.php', 'page-templates/contact.php'));
}

/**
 * Return the configured Posts page URL with a stable fallback.
 */
function greenfarm_get_blog_url(): string
{
    $posts_page_id = (int) get_option('page_for_posts');

    return $posts_page_id > 0 ? (string) get_permalink($posts_page_id) : home_url('/blog/');
}

/**
 * Render essential public routes when no Primary navigation is assigned.
 *
 * @param array<string, mixed> $args WordPress menu arguments.
 */
function greenfarm_primary_menu_fallback(array $args = array()): void
{
    $items = array(
        array('label' => __('Products', 'greenfarm'), 'url' => home_url('/products/')),
        array('label' => __('Farm Stories', 'greenfarm'), 'url' => home_url('/farm-stories/')),
        array('label' => __('About GreenFarm', 'greenfarm'), 'url' => home_url('/about/')),
        array('label' => __('Contact', 'greenfarm'), 'url' => home_url('/contact/')),
        array('label' => __('Journal', 'greenfarm'), 'url' => greenfarm_get_blog_url()),
    );

    echo '<ul class="menu">';
    foreach ($items as $item) {
        printf('<li><a href="%1$s">%2$s</a></li>', esc_url($item['url']), esc_html($item['label']));
    }
    echo '</ul>';
}

/**
 * Keep utility and thin archive URLs out of search indexes.
 *
 * @param array<string, bool> $robots Current robots directives.
 * @return array<string, bool>
 */
function greenfarm_archive_robots(array $robots): array
{
    $is_thin_archive = is_archive() && ! have_posts();

    if (is_404() || is_tag() || is_author() || is_date() || is_attachment() || $is_thin_archive) {
        $robots['noindex'] = true;
        $robots['follow']  = true;
    }

    return $robots;
}
add_filter('wp_robots', 'greenfarm_archive_robots');
