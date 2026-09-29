<?php
/**
 * Shared hero for GreenFarm core Pages.
 *
 * @package GreenFarm
 */

$greenfarm_hero_args = wp_parse_args(
    $args ?? array(),
    array(
        'eyebrow'   => '',
        'page_kind' => '',
    )
);
$greenfarm_excerpt  = has_excerpt() ? trim((string) get_the_excerpt()) : '';
$greenfarm_image_id = get_post_thumbnail_id();
$greenfarm_has_image = $greenfarm_image_id > 0 && wp_attachment_is_image($greenfarm_image_id);
?>
<section class="core-page-hero core-page-hero--<?php echo esc_attr(sanitize_html_class($greenfarm_hero_args['page_kind'])); ?>" aria-labelledby="core-page-title" data-reveal>
    <div class="core-page-hero__content" data-reveal-child>
        <?php if ('' !== $greenfarm_hero_args['eyebrow']) : ?>
            <p class="core-page-eyebrow"><?php echo esc_html($greenfarm_hero_args['eyebrow']); ?></p>
        <?php endif; ?>
        <h1 id="core-page-title"><?php the_title(); ?></h1>
        <?php if ('' !== $greenfarm_excerpt) : ?>
            <p class="core-page-hero__summary"><?php echo esc_html($greenfarm_excerpt); ?></p>
        <?php endif; ?>
    </div>

    <?php if ($greenfarm_has_image) : ?>
        <div class="core-page-hero__media" data-reveal-child>
            <?php
            echo wp_get_attachment_image(
                $greenfarm_image_id,
                'full',
                false,
                array(
                    'class'         => 'core-page-hero__image',
                    'loading'       => 'eager',
                    'fetchpriority' => 'high',
                    'decoding'      => 'async',
                    'sizes'         => '(min-width: 64rem) 50vw, 100vw',
                )
            );
            ?>
        </div>
    <?php endif; ?>
</section>
