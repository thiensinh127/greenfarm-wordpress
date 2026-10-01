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
            <span class="site-brand__mark" aria-hidden="true">GF</span>
            <span class="site-brand__name"><?php bloginfo('name'); ?></span>
        </a>
        <button class="site-header__menu-toggle" type="button" aria-controls="primary-navigation" aria-expanded="false">
            <span class="site-header__menu-label"><?php esc_html_e('Menu', 'greenfarm'); ?></span>
            <span class="site-header__menu-icon" aria-hidden="true"></span>
        </button>
        <nav id="primary-navigation" class="primary-navigation" aria-label="<?php esc_attr_e('Primary navigation', 'greenfarm'); ?>">
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'fallback_cb'    => 'greenfarm_primary_menu_fallback',
                )
            );
            ?>
        </nav>
    </div>
</header>
