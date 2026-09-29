<?php
/**
 * Blog posts archive.
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main archive-view journal-archive--editorial">
    <header class="archive-hero" data-reveal>
        <?php get_template_part('template-parts/breadcrumbs'); ?>
        <p class="eyebrow"><?php esc_html_e('Field notes and practical guides', 'greenfarm'); ?></p>
        <h1><?php esc_html_e('The GreenFarm Journal', 'greenfarm'); ?></h1>
        <p><?php esc_html_e('Useful ideas for growing, choosing, storing, and enjoying fresh food.', 'greenfarm'); ?></p>
    </header>

    <?php if (have_posts()) : ?>
        <div class="post-grid" data-reveal>
            <?php while (have_posts()) : ?>
                <?php the_post(); ?>
                <?php get_template_part('template-parts/content', 'post-card'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 1)); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
