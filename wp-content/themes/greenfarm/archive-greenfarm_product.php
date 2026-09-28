<?php
/**
 * Product archive.
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main archive-view product-archive">
    <header class="archive-hero" data-reveal>
        <?php get_template_part('template-parts/breadcrumbs'); ?>
        <p class="eyebrow"><?php esc_html_e('Explore the harvest', 'greenfarm'); ?></p>
        <h1><?php esc_html_e('Products', 'greenfarm'); ?></h1>
        <p><?php esc_html_e('Discover seasonal food grown with care for the soil and the people it feeds.', 'greenfarm'); ?></p>
    </header>

    <?php if (have_posts()) : ?>
        <div class="product-grid" data-reveal>
            <?php while (have_posts()) : ?>
                <?php the_post(); ?>
                <?php get_template_part('template-parts/content', 'product-card'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 1)); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
