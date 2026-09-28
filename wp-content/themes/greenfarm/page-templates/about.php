<?php
/**
 * Template Name: GreenFarm About
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main core-page core-page--about">
    <?php
    while (have_posts()) {
        the_post();
        get_template_part('template-parts/breadcrumbs');
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('core-page__article'); ?>>
            <?php
            get_template_part(
                'template-parts/core-page/hero',
                null,
                array(
                    'eyebrow'  => __('Về GreenFarm', 'greenfarm'),
                    'page_kind' => 'about',
                )
            );
            ?>
            <div class="core-page__content" data-reveal>
                <?php the_content(); ?>
            </div>
            <?php get_template_part('template-parts/core-page/about-cta'); ?>
        </article>
        <?php
    }
    ?>
</main>
<?php
get_footer();
