<?php
/**
 * Empty results state.
 *
 * @package GreenFarm
 */
?>
<section class="empty-state" aria-labelledby="greenfarm-empty-title">
    <h2 id="greenfarm-empty-title"><?php esc_html_e('No results', 'greenfarm'); ?></h2>
    <p><?php esc_html_e('Try another search or explore the latest farming guides.', 'greenfarm'); ?></p>
    <?php get_search_form(); ?>
</section>

