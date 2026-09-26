<?php
/**
 * Site footer.
 *
 * @package GreenFarm
 */
?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <p class="site-footer__brand"><?php bloginfo('name'); ?></p>
        <p><?php esc_html_e('Fresh food, thoughtfully grown.', 'greenfarm'); ?></p>
        <?php
        wp_nav_menu(
            array(
                'theme_location' => 'footer',
                'container'      => 'nav',
                'container_aria_label' => __('Footer navigation', 'greenfarm'),
                'fallback_cb'    => false,
            )
        );
        ?>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

