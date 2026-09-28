<?php
/**
 * Single Farm Story.
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main single-view story-view">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>
        <?php
        $greenfarm_published  = (int) get_post_time('U', true);
        $greenfarm_modified   = (int) get_post_modified_time('U', true);
        $greenfarm_is_updated = $greenfarm_modified > ($greenfarm_published + DAY_IN_SECONDS);
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('farm-story'); ?>>
            <header class="story-header" data-reveal>
                <?php get_template_part('template-parts/breadcrumbs'); ?>
                <p class="eyebrow"><?php esc_html_e('From the farm', 'greenfarm'); ?></p>
                <h1><?php the_title(); ?></h1>
                <?php if (has_excerpt()) : ?>
                    <p class="story-header__summary"><?php echo esc_html(get_the_excerpt()); ?></p>
                <?php endif; ?>
                <div class="story-meta">
                    <span class="story-meta__author">
                        <?php esc_html_e('By', 'greenfarm'); ?>
                        <a href="<?php echo esc_url(get_author_posts_url((int) get_the_author_meta('ID'))); ?>" rel="author"><?php echo esc_html(get_the_author()); ?></a>
                    </span>
                    <time class="story-meta__published" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time>
                    <?php if ($greenfarm_is_updated) : ?>
                        <time class="story-meta__updated" datetime="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>">
                            <?php
                            printf(
                                /* translators: %s: last updated date. */
                                esc_html__('Updated %s', 'greenfarm'),
                                esc_html(get_the_modified_date())
                            );
                            ?>
                        </time>
                    <?php endif; ?>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <figure class="story__featured-image" data-reveal>
                    <?php the_post_thumbnail('full', array('sizes' => '(min-width: 75rem) 69rem, (min-width: 40rem) 92vw, calc(100vw - 2rem)')); ?>
                </figure>
            <?php endif; ?>

            <div class="story__content entry-content" data-reveal>
                <?php the_content(); ?>
                <?php wp_link_pages(array('before' => '<nav class="page-links" aria-label="' . esc_attr__('Story pages', 'greenfarm') . '">', 'after' => '</nav>')); ?>
            </div>
        </article>

        <?php
        the_post_navigation(
            array(
                'prev_text' => '<span>' . esc_html__('Previous story', 'greenfarm') . '</span><strong>%title</strong>',
                'next_text' => '<span>' . esc_html__('Next story', 'greenfarm') . '</span><strong>%title</strong>',
            )
        );
        ?>
    <?php endwhile; ?>
</main>
<?php
get_footer();
