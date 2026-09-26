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
        get_template_part('template-parts/home/products');
        get_template_part('template-parts/home/values-process');
        get_template_part('template-parts/home/stories');
        get_template_part('template-parts/home/latest-posts');
        get_template_part('template-parts/home/proof');
        get_template_part('template-parts/home/cta');
    }
    ?>
</main>
<?php
get_footer();
