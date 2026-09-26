<?php
/**
 * Site header.
 *
 * @package GreenFarm
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'greenfarm'); ?></a>
<header class="site-header">
    <div class="site-header__inner">
        <a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
            <span aria-hidden="true">◌</span>
            <span><?php bloginfo('name'); ?></span>
        </a>
        <nav class="primary-navigation" aria-label="<?php esc_attr_e('Primary navigation', 'greenfarm'); ?>">
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'fallback_cb'    => 'wp_page_menu',
                )
            );
            ?>
        </nav>
    </div>
</header>

