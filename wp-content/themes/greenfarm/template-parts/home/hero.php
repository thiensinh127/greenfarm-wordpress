<?php
/**
 * Homepage hero.
 *
 * @package GreenFarm
 */

$greenfarm_summary = trim((string) get_the_excerpt());
$greenfarm_image   = get_post_thumbnail_id();
?>
<section class="home-hero">
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
        <div class="home-actions">
            <a class="home-button home-button--primary" href="<?php echo esc_url(home_url('/products/')); ?>">
                <?php esc_html_e('Explore our products', 'greenfarm'); ?>
            </a>
        </div>
    </div>

    <?php if ($greenfarm_image) : ?>
        <div class="home-hero__media" data-reveal>
            <?php
            echo wp_get_attachment_image(
                $greenfarm_image,
                'full',
                false,
                array(
                    'class'         => 'home-hero__image',
                    'loading'       => 'eager',
                    'fetchpriority' => 'high',
                    'decoding'      => 'async',
                    'sizes'         => '(min-width: 64rem) 55vw, 100vw',
                )
            );
            ?>
        </div>
    <?php endif; ?>
</section>
