<?php
/**
 * About Page continuation actions.
 *
 * @package GreenFarm
 */
?>
<section class="core-page-cta" aria-labelledby="core-page-cta-title" data-reveal>
    <div data-reveal-child>
        <p class="core-page-eyebrow"><?php esc_html_e('Tiếp tục khám phá', 'greenfarm'); ?></p>
        <h2 id="core-page-cta-title"><?php esc_html_e('Hiểu thêm về GreenFarm', 'greenfarm'); ?></h2>
        <p><?php esc_html_e('Khám phá nông sản theo mùa hoặc liên hệ trực tiếp với GreenFarm.', 'greenfarm'); ?></p>
    </div>
    <div class="core-page-actions" data-reveal-child>
        <a class="core-page-button core-page-button--primary" href="<?php echo esc_url(home_url('/products/')); ?>">
            <?php esc_html_e('Khám phá sản phẩm', 'greenfarm'); ?>
        </a>
        <a class="core-page-button core-page-button--secondary" href="<?php echo esc_url(home_url('/contact/')); ?>">
            <?php esc_html_e('Liên hệ GreenFarm', 'greenfarm'); ?>
        </a>
    </div>
</section>
