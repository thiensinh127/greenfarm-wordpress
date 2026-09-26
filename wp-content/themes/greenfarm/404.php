<?php
/**
 * Not found template.
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main error-view">
    <section class="empty-state" data-reveal>
        <p class="eyebrow"><?php esc_html_e('404 error', 'greenfarm'); ?></p>
        <h1><?php esc_html_e('Page not found', 'greenfarm'); ?></h1>
        <p><?php esc_html_e('This page may have moved. Search GreenFarm or return to the latest farming guides.', 'greenfarm'); ?></p>
        <?php get_search_form(); ?>
        <p><a href="<?php echo esc_url(greenfarm_get_blog_url()); ?>"><?php esc_html_e('Explore the GreenFarm Journal', 'greenfarm'); ?></a></p>
    </section>
</main>
<?php
get_footer();
