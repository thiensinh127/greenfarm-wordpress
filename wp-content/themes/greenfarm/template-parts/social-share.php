<?php
/**
 * Progressive-enhancement social sharing controls.
 *
 * @package GreenFarm
 */

$greenfarm_share_url   = get_permalink();
$greenfarm_share_title = wp_strip_all_tags(get_the_title());
?>
<div class="social-share" data-share>
    <p class="social-share__title"><?php esc_html_e('Share this article', 'greenfarm'); ?></p>
    <ul>
        <li>
            <a href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($greenfarm_share_url)); ?>" rel="noopener noreferrer" target="_blank">
                <?php esc_html_e('Facebook', 'greenfarm'); ?>
                <span class="screen-reader-text"><?php esc_html_e('(opens in a new tab)', 'greenfarm'); ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo esc_url('https://twitter.com/intent/tweet?url=' . rawurlencode($greenfarm_share_url) . '&text=' . rawurlencode($greenfarm_share_title)); ?>" rel="noopener noreferrer" target="_blank">
                <?php esc_html_e('X', 'greenfarm'); ?>
                <span class="screen-reader-text"><?php esc_html_e('(opens in a new tab)', 'greenfarm'); ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo esc_url('mailto:?subject=' . rawurlencode($greenfarm_share_title) . '&body=' . rawurlencode($greenfarm_share_url)); ?>">
                <?php esc_html_e('Email', 'greenfarm'); ?>
            </a>
        </li>
    </ul>
    <button class="social-share__native" type="button" data-share-button data-url="<?php echo esc_url($greenfarm_share_url); ?>" data-title="<?php echo esc_attr($greenfarm_share_title); ?>">
        <?php esc_html_e('Share or copy link', 'greenfarm'); ?>
    </button>
    <p class="social-share__status" data-share-status aria-live="polite"></p>
</div>
