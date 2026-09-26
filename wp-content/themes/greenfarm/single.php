<?php
/**
 * Single blog article.
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main single-view">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('article'); ?>>
            <?php get_template_part('template-parts/article', 'header'); ?>

            <?php if (has_post_thumbnail()) : ?>
                <figure class="article__featured-image" data-reveal>
                    <?php the_post_thumbnail('full'); ?>
                </figure>
            <?php endif; ?>

            <div class="article__layout">
                <div class="article__content entry-content" data-reveal>
                    <?php the_content(); ?>
                    <?php
                    wp_link_pages(
                        array(
                            'before' => '<nav class="page-links" aria-label="' . esc_attr__('Article pages', 'greenfarm') . '">',
                            'after'  => '</nav>',
                        )
                    );
                    ?>
                </div>

                <aside class="article__aside" aria-label="<?php esc_attr_e('Article actions', 'greenfarm'); ?>">
                    <?php get_template_part('template-parts/social', 'share'); ?>
                </aside>
            </div>

            <footer class="article__footer" data-reveal>
                <?php if (has_category()) : ?>
                    <div class="article-taxonomy">
                        <span><?php esc_html_e('Filed under', 'greenfarm'); ?></span>
                        <?php the_category(', '); ?>
                    </div>
                <?php endif; ?>
                <?php if (has_tag()) : ?>
                    <div class="article-tags"><?php the_tags('', ' '); ?></div>
                <?php endif; ?>
            </footer>
        </article>

        <?php
        the_post_navigation(
            array(
                'prev_text' => '<span>' . esc_html__('Previous article', 'greenfarm') . '</span><strong>%title</strong>',
                'next_text' => '<span>' . esc_html__('Next article', 'greenfarm') . '</span><strong>%title</strong>',
            )
        );
        ?>
    <?php endwhile; ?>
</main>
<?php
get_footer();

