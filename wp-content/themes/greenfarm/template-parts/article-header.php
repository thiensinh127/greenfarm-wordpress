<?php
/**
 * Single article header and schema.
 *
 * @package GreenFarm
 */

$greenfarm_schema = array(
    '@context'         => 'https://schema.org',
    '@type'            => 'Article',
    'headline'         => wp_strip_all_tags(get_the_title()),
    'datePublished'    => get_the_date(DATE_W3C),
    'dateModified'     => get_the_modified_date(DATE_W3C),
    'mainEntityOfPage' => get_permalink(),
    'author'           => array(
        '@type' => 'Person',
        'name'  => wp_strip_all_tags(get_the_author()),
    ),
);

if (has_post_thumbnail()) {
    $greenfarm_schema['image'] = wp_get_attachment_image_url(get_post_thumbnail_id(), 'full');
}
?>
<header class="article-header" data-reveal>
    <?php get_template_part('template-parts/breadcrumbs'); ?>
    <?php $greenfarm_categories = get_the_category(); ?>
    <?php if (! empty($greenfarm_categories)) : ?>
        <a class="eyebrow" href="<?php echo esc_url(get_category_link($greenfarm_categories[0])); ?>">
            <?php echo esc_html($greenfarm_categories[0]->name); ?>
        </a>
    <?php endif; ?>
    <h1><?php the_title(); ?></h1>
    <?php if (has_excerpt()) : ?>
        <p class="article-header__dek"><?php echo esc_html(get_the_excerpt()); ?></p>
    <?php endif; ?>
    <?php get_template_part('template-parts/article', 'meta'); ?>
</header>
<script type="application/ld+json"><?php echo wp_json_encode($greenfarm_schema); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
