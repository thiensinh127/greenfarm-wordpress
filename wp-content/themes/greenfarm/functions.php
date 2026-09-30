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
        'greenfarm-fonts',
        'https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Manrope:wght@400;500;600;700&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'greenfarm-style',
        get_stylesheet_uri(),
        array('greenfarm-fonts'),
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

        wp_enqueue_script(
            'greenfarm-hero-carousel',
            get_template_directory_uri() . '/assets/js/hero-carousel.js',
            array(),
            $version,
            array('strategy' => 'defer', 'in_footer' => true)
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

/** @return array<int, int> */
function greenfarm_get_hero_slider_ids(int $page_id): array
{
    $value = get_post_meta($page_id, '_greenfarm_hero_slider_ids', true);
    $ids   = is_array($value) ? $value : explode(',', (string) $value);

    return array_values(array_filter(array_unique(array_map('absint', $ids)), 'wp_attachment_is_image'));
}

function greenfarm_add_hero_slider_meta_box(): void
{
    add_meta_box('greenfarm-hero-slider', __('Hero carousel images', 'greenfarm'), 'greenfarm_render_hero_slider_meta_box', 'page', 'side');
}
add_action('add_meta_boxes_page', 'greenfarm_add_hero_slider_meta_box');

/** @param WP_Post $post Current Page. */
function greenfarm_render_hero_slider_meta_box(WP_Post $post): void
{
    $ids = greenfarm_get_hero_slider_ids((int) $post->ID);
    wp_nonce_field('greenfarm_save_hero_slider', 'greenfarm_hero_slider_nonce');
    echo '<p>' . esc_html__('Choose images for the homepage hero. Drag to change their order.', 'greenfarm') . '</p>';
    echo '<ul id="greenfarm-hero-slider-list">';
    foreach ($ids as $id) {
        printf('<li data-id="%1$d">%2$s <button type="button" class="button-link-delete" aria-label="%3$s">%4$s</button></li>', $id, wp_get_attachment_image($id, 'thumbnail'), esc_attr__('Remove image', 'greenfarm'), esc_html__('Remove', 'greenfarm'));
    }
    echo '</ul><input id="greenfarm-hero-slider-ids" name="greenfarm_hero_slider_ids" type="hidden" value="' . esc_attr(implode(',', $ids)) . '">';
    echo '<button id="greenfarm-hero-slider-add" type="button" class="button">' . esc_html__('Add images', 'greenfarm') . '</button>';
}

function greenfarm_save_hero_slider(int $post_id): void
{
    if (! isset($_POST['greenfarm_hero_slider_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['greenfarm_hero_slider_nonce'])), 'greenfarm_save_hero_slider') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ! current_user_can('edit_post', $post_id)) {
        return;
    }

    $raw_ids = isset($_POST['greenfarm_hero_slider_ids']) ? (string) wp_unslash($_POST['greenfarm_hero_slider_ids']) : '';
    $ids     = array_values(array_filter(array_unique(array_map('absint', explode(',', $raw_ids))), 'wp_attachment_is_image'));

    if ($ids) {
        update_post_meta($post_id, '_greenfarm_hero_slider_ids', $ids);
    } else {
        delete_post_meta($post_id, '_greenfarm_hero_slider_ids');
    }
}
add_action('save_post_page', 'greenfarm_save_hero_slider');

/** @param string $hook Admin page hook. */
function greenfarm_enqueue_hero_slider_admin_assets(string $hook): void
{
    $screen = get_current_screen();
    if (! in_array($hook, array('post.php', 'post-new.php'), true) || ! $screen || 'page' !== $screen->post_type) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('greenfarm-hero-slider-admin', get_template_directory_uri() . '/assets/js/hero-slider-admin.js', array('jquery', 'jquery-ui-sortable'), wp_get_theme()->get('Version'), true);
}
add_action('admin_enqueue_scripts', 'greenfarm_enqueue_hero_slider_admin_assets');

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
        array('label' => __('Journal', 'greenfarm'), 'url' => greenfarm_get_blog_url()),
        array('label' => __('About GreenFarm', 'greenfarm'), 'url' => home_url('/about/')),
        array('label' => __('Contact', 'greenfarm'), 'url' => home_url('/contact/')),
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
