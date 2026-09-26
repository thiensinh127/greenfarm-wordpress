<?php
/**
 * Search results.
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main archive-view">
    <header class="archive-hero" data-reveal>
        <?php get_template_part('template-parts/breadcrumbs'); ?>
        <p class="eyebrow"><?php esc_html_e('Search GreenFarm', 'greenfarm'); ?></p>
        <h1>
            <?php
            printf(
                /* translators: %s: search query. */
                esc_html__('Search results for “%s”', 'greenfarm'),
                esc_html(get_search_query())
            );
            ?>
        </h1>
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
