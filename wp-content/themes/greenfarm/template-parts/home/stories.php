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
                get_template_part(
                    'template-parts/content',
                    'story-card',
                    array(
                        'heading_level' => 'h3',
                        'card_class'    => 'home-story-card',
                        'image_size'    => 'large',
                    )
                );
            }
            ?>
        </div>
    </section>
    <?php
endif;
wp_reset_postdata();
