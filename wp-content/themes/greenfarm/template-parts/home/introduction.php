<?php
/**
 * Homepage farm introduction.
 *
 * @package GreenFarm
 */
?>
<section class="home-section home-introduction" aria-labelledby="home-introduction-title" data-reveal>
    <div class="home-section__heading">
        <p class="home-eyebrow"><?php esc_html_e('Our farm', 'greenfarm'); ?></p>
        <h2 id="home-introduction-title"><?php esc_html_e('Good food begins with how it is grown', 'greenfarm'); ?></h2>
    </div>
    <div class="home-introduction__body">
        <div class="home-introduction__content">
            <?php the_content(); ?>
        </div>
        <ul class="home-introduction__points" aria-label="<?php esc_attr_e('GreenFarm commitments', 'greenfarm'); ?>">
            <li><?php esc_html_e('Healthy soil', 'greenfarm'); ?></li>
            <li><?php esc_html_e('Seasonal harvests', 'greenfarm'); ?></li>
            <li><?php esc_html_e('Transparent growing', 'greenfarm'); ?></li>
        </ul>
        <a class="home-text-link" href="<?php echo esc_url(home_url('/about/')); ?>">
            <?php esc_html_e('Learn about our farm', 'greenfarm'); ?>
        </a>
    </div>
</section>
