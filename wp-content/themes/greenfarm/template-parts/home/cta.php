<?php
/**
 * Homepage final call to action.
 *
 * @package GreenFarm
 */
?>
<section class="home-cta" aria-labelledby="home-cta-title" data-reveal>
    <div>
        <p class="home-eyebrow"><?php esc_html_e('Stay close to the season', 'greenfarm'); ?></p>
        <h2 id="home-cta-title"><?php esc_html_e('Get seasonal updates from GreenFarm', 'greenfarm'); ?></h2>
        <p><?php esc_html_e('Ask about current harvests, farm news, and future updates through our contact page.', 'greenfarm'); ?></p>
    </div>
    <a class="home-button home-button--primary" href="<?php echo esc_url(home_url('/contact/')); ?>">
        <?php esc_html_e('Contact GreenFarm', 'greenfarm'); ?>
    </a>
</section>
