<?php
/**
 * Farm Story archive.
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main archive-view story-archive">
    <header class="archive-hero" data-reveal>
        <?php get_template_part('template-parts/breadcrumbs'); ?>
        <p class="eyebrow"><?php esc_html_e('Notes from the field', 'greenfarm'); ?></p>
        <h1><?php esc_html_e('Farm Stories', 'greenfarm'); ?></h1>
        <p><?php esc_html_e('Meet the people, seasons, and everyday work behind GreenFarm.', 'greenfarm'); ?></p>
    </header>

    <?php if (have_posts()) : ?>
        <div class="story-grid" data-reveal>
            <?php while (have_posts()) : ?>
                <?php the_post(); ?>
                <?php get_template_part('template-parts/content', 'story-card'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(array('mid_size' => 1)); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
