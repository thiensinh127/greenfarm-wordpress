<?php
/**
 * Homepage farm introduction.
 *
 * @package GreenFarm
 */
?>
<section class="home-section home-introduction" aria-labelledby="home-introduction-title" data-reveal>
    <div class="home-introduction__story">
        <div class="home-section__heading">
            <p class="home-eyebrow"><?php esc_html_e('Our farm', 'greenfarm'); ?></p>
            <h2 id="home-introduction-title"><?php esc_html_e('A farm rooted in care', 'greenfarm'); ?></h2>
        </div>
        <div class="home-introduction__content">
            <?php the_content(); ?>
        </div>
        <a class="home-button home-button--primary" href="<?php echo esc_url(home_url('/about/')); ?>">
            <?php esc_html_e('Learn about our farm', 'greenfarm'); ?>
        </a>
    </div>
    <div class="home-introduction__support">
        <?php if (has_post_thumbnail()) : ?>
            <div class="home-introduction__media" data-reveal-child>
                <?php the_post_thumbnail('large', array('sizes' => '(min-width: 64rem) 42vw, 100vw')); ?>
            </div>
        <?php endif; ?>
        <ul class="home-introduction__points" aria-label="<?php esc_attr_e('GreenFarm commitments', 'greenfarm'); ?>">
            <li><?php esc_html_e('Seasonal growing', 'greenfarm'); ?></li>
            <li><?php esc_html_e('Responsible methods', 'greenfarm'); ?></li>
            <li><?php esc_html_e('Transparent farm stories', 'greenfarm'); ?></li>
        </ul>
    </div>
</section>
