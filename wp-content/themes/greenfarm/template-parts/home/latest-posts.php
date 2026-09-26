<?php
/**
 * Homepage latest educational articles.
 *
 * @package GreenFarm
 */

$greenfarm_latest_posts = new WP_Query(
    array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 3,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    )
);

if ($greenfarm_latest_posts->have_posts()) :
    ?>
    <section class="home-section home-latest" aria-labelledby="home-latest-title" data-reveal>
        <div class="home-section__heading home-section__heading--with-link">
            <div>
                <p class="home-eyebrow"><?php esc_html_e('Learn with us', 'greenfarm'); ?></p>
                <h2 id="home-latest-title"><?php esc_html_e('Latest educational articles', 'greenfarm'); ?></h2>
            </div>
            <a class="home-text-link" href="<?php echo esc_url(greenfarm_get_blog_url()); ?>">
                <?php esc_html_e('Visit the Journal', 'greenfarm'); ?>
            </a>
        </div>
        <div class="home-latest__grid">
            <?php
            while ($greenfarm_latest_posts->have_posts()) {
                $greenfarm_latest_posts->the_post();
                get_template_part(
                    'template-parts/content',
                    'post-card',
                    array('heading_level' => 'h3')
                );
            }
            ?>
        </div>
    </section>
    <?php
endif;
wp_reset_postdata();
