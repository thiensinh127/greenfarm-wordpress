<?php
/**
 * Not found template.
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main error-view">
    <section class="error-hero" aria-labelledby="error-title">
        <div class="error-hero__content">
            <p class="error-hero__eyebrow"><?php esc_html_e('A wrong turn in the field', 'greenfarm'); ?></p>
            <p class="error-hero__code" aria-hidden="true">404</p>
            <h1 id="error-title"><?php esc_html_e('Page not found', 'greenfarm'); ?></h1>
            <p><?php esc_html_e('The page may have moved, or the link may be out of season. Find your way back or search the site.', 'greenfarm'); ?></p>
            <div class="error-hero__actions">
                <a class="error-hero__button error-hero__button--primary" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php esc_html_e('Return home', 'greenfarm'); ?>
                </a>
                <a class="error-hero__button error-hero__button--secondary" href="<?php echo esc_url(greenfarm_get_blog_url()); ?>">
                    <?php esc_html_e('Explore the Journal', 'greenfarm'); ?>
                </a>
            </div>
        </div>
        <div class="error-hero__search">
            <p><?php esc_html_e('Search GreenFarm', 'greenfarm'); ?></p>
            <?php get_search_form(); ?>
        </div>
    </section>
</main>
<?php
get_footer();
