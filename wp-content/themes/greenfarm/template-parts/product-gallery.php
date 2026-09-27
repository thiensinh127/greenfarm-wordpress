<?php
/**
 * Product gallery from native image attachments.
 *
 * @package GreenFarm
 */

$greenfarm_gallery_ids = get_post_meta(get_the_ID(), 'greenfarm_gallery_ids', true);
if (! is_array($greenfarm_gallery_ids)) {
    return;
}

$greenfarm_valid_gallery_ids = array();
foreach ($greenfarm_gallery_ids as $greenfarm_attachment_id) {
    $greenfarm_attachment_id = absint($greenfarm_attachment_id);
    if (
        $greenfarm_attachment_id < 1
        || in_array($greenfarm_attachment_id, $greenfarm_valid_gallery_ids, true)
        || 'attachment' !== get_post_type($greenfarm_attachment_id)
        || ! wp_attachment_is_image($greenfarm_attachment_id)
    ) {
        continue;
    }

    $greenfarm_valid_gallery_ids[] = $greenfarm_attachment_id;
}

if (! $greenfarm_valid_gallery_ids) {
    return;
}
?>
<section class="product-gallery" aria-labelledby="product-gallery-title" data-reveal>
    <h2 id="product-gallery-title"><?php esc_html_e('Gallery', 'greenfarm'); ?></h2>
    <div class="product-gallery__grid">
        <?php foreach ($greenfarm_valid_gallery_ids as $greenfarm_attachment_id) : ?>
            <figure class="product-gallery__item">
                <?php
                echo wp_get_attachment_image(
                    $greenfarm_attachment_id,
                    'large',
                    false,
                    array(
                        'loading' => 'lazy',
                        'sizes'   => '(min-width: 64rem) 50vw, 100vw',
                    )
                ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
                <?php $greenfarm_caption = wp_get_attachment_caption($greenfarm_attachment_id); ?>
                <?php if ($greenfarm_caption) : ?>
                    <figcaption><?php echo esc_html($greenfarm_caption); ?></figcaption>
                <?php endif; ?>
            </figure>
        <?php endforeach; ?>
    </div>
</section>
