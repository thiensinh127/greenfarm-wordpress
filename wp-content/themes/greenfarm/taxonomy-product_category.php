<?php
/**
 * Product Category archive.
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main archive-view product-category-archive">
    <header class="archive-hero" data-reveal>
        <?php get_template_part('template-parts/breadcrumbs'); ?>
        <p class="eyebrow"><?php esc_html_e('Browse the harvest', 'greenfarm'); ?></p>
        <h1><?php single_term_title(); ?></h1>
        <?php if (term_description()) : ?>
            <div class="archive-description"><?php echo wp_kses_post(term_description()); ?></div>
        <?php endif; ?>
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
