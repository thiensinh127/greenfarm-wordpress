<?php
/**
 * Static front page.
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main home-page">
    <?php
    while (have_posts()) {
        the_post();
        get_template_part('template-parts/home/hero');
        get_template_part('template-parts/home/introduction');
    }
    ?>
</main>
<?php
get_footer();

