<?php
/**
 * Template Name: GreenFarm Contact
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main core-page core-page--contact">
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
                    'eyebrow'  => __('Liên hệ', 'greenfarm'),
                    'page_kind' => 'contact',
                )
            );
            ?>
            <?php if ('' !== trim((string) get_post_field('post_content', get_the_ID()))) : ?>
                <div class="core-page__content" data-reveal>
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        </article>
        <?php
    }
    ?>
</main>
<?php
get_footer();
