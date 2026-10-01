<?php
/**
 * Homepage hero.
 *
 * @package GreenFarm
 */

$greenfarm_summary = trim((string) get_the_excerpt());
$greenfarm_images  = greenfarm_get_hero_slider_ids((int) get_the_ID());

if (! $greenfarm_images && get_post_thumbnail_id()) {
    $greenfarm_images = array((int) get_post_thumbnail_id());
}
?>
<section class="home-hero<?php echo $greenfarm_images ? ' home-hero--with-image' : ''; ?>">
    <div class="home-hero__content" data-reveal>
        <p class="home-eyebrow"><?php esc_html_e('Rooted in better farming', 'greenfarm'); ?></p>
        <h1><?php the_title(); ?></h1>
        <p class="home-hero__summary">
            <?php
            echo esc_html(
                $greenfarm_summary ?: __('Fresh food, thoughtfully grown from healthy soil.', 'greenfarm')
            );
            ?>
        </p>
        <div class="home-actions home-hero__actions">
            <a class="home-button home-button--primary" href="<?php echo esc_url(home_url('/farm-stories/')); ?>">
                <?php esc_html_e('Read the latest from the fields', 'greenfarm'); ?>
            </a>
            <a class="home-button home-button--secondary" href="<?php echo esc_url(home_url('/products/')); ?>">
                <?php esc_html_e('Explore products', 'greenfarm'); ?>
            </a>
        </div>
    </div>

    <?php if ($greenfarm_images) : ?>
        <div class="home-hero__media" data-reveal>
            <div class="home-hero__carousel" data-hero-carousel aria-roledescription="carousel" aria-label="<?php esc_attr_e('Homepage hero images', 'greenfarm'); ?>">
                <?php foreach ($greenfarm_images as $greenfarm_index => $greenfarm_image) : ?>
                    <div class="home-hero__slide<?php echo 0 === $greenfarm_index ? ' is-active' : ''; ?>" aria-hidden="<?php echo 0 === $greenfarm_index ? 'false' : 'true'; ?>">
                        <?php echo wp_get_attachment_image($greenfarm_image, 'full', false, array('class' => 'home-hero__image', 'loading' => 0 === $greenfarm_index ? 'eager' : 'lazy', 'fetchpriority' => 0 === $greenfarm_index ? 'high' : 'auto', 'decoding' => 'async', 'sizes' => '(min-width: 64rem) 55vw, 100vw')); ?>
                    </div>
                <?php endforeach; ?>
                <?php if (count($greenfarm_images) > 1) : ?>
                    <div class="home-hero__carousel-controls" role="group" aria-label="<?php esc_attr_e('Choose hero image', 'greenfarm'); ?>">
                        <button class="home-hero__carousel-toggle" type="button" aria-label="<?php esc_attr_e('Pause carousel', 'greenfarm'); ?>" aria-pressed="false">
                            <span class="screen-reader-text"><?php esc_html_e('Pause carousel', 'greenfarm'); ?></span>
                        </button>
                        <?php foreach ($greenfarm_images as $greenfarm_index => $greenfarm_image) : ?>
                            <button class="home-hero__carousel-control" type="button" data-index="<?php echo esc_attr((string) $greenfarm_index); ?>" aria-current="<?php echo 0 === $greenfarm_index ? 'true' : 'false'; ?>"><span class="screen-reader-text"><?php echo esc_html(sprintf(__('Show hero image %d', 'greenfarm'), $greenfarm_index + 1)); ?></span></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
