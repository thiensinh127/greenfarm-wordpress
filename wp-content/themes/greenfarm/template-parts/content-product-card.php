<?php
/**
 * Reusable Product card.
 *
 * @package GreenFarm
 */

$greenfarm_card_args = wp_parse_args(
    $args ?? array(),
    array(
        'heading_level' => 'h2',
        'card_class'    => 'product-card',
        'image_size'    => 'medium_large',
        'image_context' => 'archive',
    )
);

$greenfarm_heading_level = in_array($greenfarm_card_args['heading_level'], array('h2', 'h3'), true)
    ? $greenfarm_card_args['heading_level']
    : 'h2';
$greenfarm_card_class    = sanitize_html_class((string) $greenfarm_card_args['card_class'], 'product-card');
$greenfarm_image_size    = sanitize_key((string) $greenfarm_card_args['image_size']);
$greenfarm_image_size    = $greenfarm_image_size ?: 'medium_large';
$greenfarm_image_sizes   = array(
    'archive' => '(min-width: 75rem) 23rem, (min-width: 64rem) calc(33.333vw - 2rem), (min-width: 40rem) calc(50vw - 2rem), calc(100vw - 2rem)',
    'home'    => '(min-width: 75rem) 17rem, (min-width: 64rem) calc(25vw - 2rem), (min-width: 40rem) calc(50vw - 2rem), calc(100vw - 2rem)',
);
$greenfarm_image_context = isset($greenfarm_image_sizes[$greenfarm_card_args['image_context']])
    ? $greenfarm_card_args['image_context']
    : 'archive';
$greenfarm_availability  = (string) get_post_meta(get_the_ID(), 'greenfarm_availability', true);
$greenfarm_label         = function_exists('greenfarm_core_get_availability_label')
    ? greenfarm_core_get_availability_label($greenfarm_availability)
    : '';
?>
<article <?php post_class($greenfarm_card_class); ?> data-reveal-child>
    <?php if (has_post_thumbnail()) : ?>
        <a class="<?php echo esc_attr($greenfarm_card_class); ?>__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
            <?php the_post_thumbnail($greenfarm_image_size, array('sizes' => $greenfarm_image_sizes[$greenfarm_image_context])); ?>
        </a>
    <?php endif; ?>
    <div class="<?php echo esc_attr($greenfarm_card_class); ?>__body">
        <?php if ($greenfarm_label) : ?>
            <p class="<?php echo esc_attr($greenfarm_card_class); ?>__availability"><?php echo esc_html($greenfarm_label); ?></p>
        <?php endif; ?>
        <<?php echo $greenfarm_heading_level; ?>><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo $greenfarm_heading_level; ?>>
        <?php if (has_excerpt()) : ?>
            <p><?php echo esc_html(get_the_excerpt()); ?></p>
        <?php endif; ?>
    </div>
</article>
