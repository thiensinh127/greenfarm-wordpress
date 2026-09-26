<?php
/**
 * Optional homepage farm stories.
 *
 * @package GreenFarm
 */

if (! post_type_exists('farm_story')) {
    return;
}

$greenfarm_stories = new WP_Query(
    array(
        'post_type'           => 'farm_story',
        'post_status'         => 'publish',
        'posts_per_page'      => 2,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    )
);

if ($greenfarm_stories->have_posts()) :
    ?>
    <section class="home-section home-stories" aria-labelledby="home-stories-title" data-reveal>
        <div class="home-section__heading">
            <p class="home-eyebrow"><?php esc_html_e('Notes from the field', 'greenfarm'); ?></p>
            <h2 id="home-stories-title"><?php esc_html_e('Farm stories', 'greenfarm'); ?></h2>
        </div>
        <div class="home-stories__grid">
            <?php
            while ($greenfarm_stories->have_posts()) {
                $greenfarm_stories->the_post();
                ?>
                <article class="home-story-card" data-reveal-child>
                    <?php if (has_post_thumbnail()) : ?>
                        <a class="home-story-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                            <?php the_post_thumbnail('large', array('sizes' => '(min-width: 64rem) 50vw, 100vw')); ?>
                        </a>
                    <?php endif; ?>
                    <div class="home-story-card__body">
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <?php if (has_excerpt()) : ?>
                            <p><?php echo esc_html(get_the_excerpt()); ?></p>
                        <?php endif; ?>
                    </div>
                </article>
                <?php
            }
            ?>
        </div>
    </section>
    <?php
endif;
wp_reset_postdata();
