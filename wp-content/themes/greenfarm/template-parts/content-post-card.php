<?php
/**
 * Post card used by native archives and search results.
 *
 * @package GreenFarm
 */

$greenfarm_heading_level = isset($args['heading_level']) && in_array($args['heading_level'], array('h2', 'h3'), true)
    ? $args['heading_level']
    : 'h2';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
    <?php if (has_post_thumbnail()) : ?>
        <a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
            <?php the_post_thumbnail('large', array('class' => 'post-card__image')); ?>
        </a>
    <?php endif; ?>
    <div class="post-card__body">
        <?php $greenfarm_categories = get_the_category(); ?>
        <?php if (! empty($greenfarm_categories)) : ?>
            <a class="post-card__category" href="<?php echo esc_url(get_category_link($greenfarm_categories[0])); ?>">
                <?php echo esc_html($greenfarm_categories[0]->name); ?>
            </a>
        <?php endif; ?>
        <<?php echo esc_attr($greenfarm_heading_level); ?> class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo esc_attr($greenfarm_heading_level); ?>>
        <div class="post-card__excerpt"><?php the_excerpt(); ?></div>
        <div class="post-card__meta">
            <span><?php echo esc_html(get_the_author()); ?></span>
            <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time>
        </div>
    </div>
</article>
