<?php
/**
 * Related posts selected by shared Categories.
 *
 * @package GreenFarm
 */

$greenfarm_current_id = get_the_ID();
$greenfarm_category_ids = wp_get_post_categories($greenfarm_current_id);

if (empty($greenfarm_category_ids)) {
    return;
}

$greenfarm_related = new WP_Query(
    array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 3,
        'post__not_in'        => array($greenfarm_current_id),
        'category__in'        => $greenfarm_category_ids,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    )
);

if (! $greenfarm_related->have_posts()) {
    return;
}
?>
<section class="related-posts" aria-labelledby="related-posts-title" data-reveal>
    <header class="section-heading">
        <p class="eyebrow"><?php esc_html_e('Keep learning', 'greenfarm'); ?></p>
        <h2 id="related-posts-title"><?php esc_html_e('Related articles', 'greenfarm'); ?></h2>
    </header>
    <div class="related-posts__grid">
        <?php while ($greenfarm_related->have_posts()) : ?>
            <?php $greenfarm_related->the_post(); ?>
            <article <?php post_class('related-card'); ?>>
                <?php if (has_post_thumbnail()) : ?>
                    <a class="related-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                        <?php the_post_thumbnail('medium_large'); ?>
                    </a>
                <?php endif; ?>
                <div class="related-card__body">
                    <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time>
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <?php the_excerpt(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</section>
<?php
wp_reset_postdata();

