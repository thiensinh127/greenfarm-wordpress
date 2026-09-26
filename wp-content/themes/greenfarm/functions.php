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
 * Load global theme styles.
 */
function greenfarm_enqueue_assets(): void
{
    wp_enqueue_style(
        'greenfarm-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'greenfarm_enqueue_assets');

/**
 * Return the configured Posts page URL with a stable fallback.
 */
function greenfarm_get_blog_url(): string
{
    $posts_page_id = (int) get_option('page_for_posts');

    return $posts_page_id > 0 ? (string) get_permalink($posts_page_id) : home_url('/blog/');
}

