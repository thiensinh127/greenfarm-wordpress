<?php
/**
 * Single Product.
 *
 * @package GreenFarm
 */

get_header();
?>
<main id="primary" class="site-main single-view product-view">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>
        <?php
        $greenfarm_product_facts = array_filter(
            array(
                __('Origin', 'greenfarm')         => trim((string) get_post_meta(get_the_ID(), 'greenfarm_origin', true)),
                __('Farming method', 'greenfarm') => trim((string) get_post_meta(get_the_ID(), 'greenfarm_farming_method', true)),
                __('Harvest season', 'greenfarm') => trim((string) get_post_meta(get_the_ID(), 'greenfarm_harvest_season', true)),
            )
        );
        $greenfarm_availability = function_exists('greenfarm_core_get_availability_label')
            ? greenfarm_core_get_availability_label((string) get_post_meta(get_the_ID(), 'greenfarm_availability', true))
            : '';
        $greenfarm_storage      = trim((string) get_post_meta(get_the_ID(), 'greenfarm_storage_instructions', true));
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('product-detail'); ?>>
            <header class="product-header" data-reveal>
                <?php get_template_part('template-parts/breadcrumbs'); ?>
                <?php if ($greenfarm_availability) : ?>
                    <p class="product-header__availability"><?php echo esc_html($greenfarm_availability); ?></p>
                <?php endif; ?>
                <h1><?php the_title(); ?></h1>
                <?php if (has_excerpt()) : ?>
                    <p class="product-header__summary"><?php echo esc_html(get_the_excerpt()); ?></p>
                <?php endif; ?>
                <?php $greenfarm_categories = get_the_terms(get_the_ID(), 'product_category'); ?>
                <?php if ($greenfarm_categories && ! is_wp_error($greenfarm_categories)) : ?>
                    <div class="product-header__categories">
                        <span><?php esc_html_e('Categories', 'greenfarm'); ?></span>
                        <?php echo wp_kses_post(get_the_term_list(get_the_ID(), 'product_category', '', ', ')); ?>
                    </div>
                <?php endif; ?>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <figure class="product-detail__featured-image" data-reveal>
                    <?php the_post_thumbnail('full', array('sizes' => '(min-width: 64rem) 70vw, 100vw')); ?>
                </figure>
            <?php endif; ?>

            <div class="product-detail__layout">
                <div class="product-detail__content entry-content" data-reveal>
                    <?php the_content(); ?>
                    <?php wp_link_pages(array('before' => '<nav class="page-links" aria-label="' . esc_attr__('Product pages', 'greenfarm') . '">', 'after' => '</nav>')); ?>
                </div>

                <?php if ($greenfarm_product_facts || $greenfarm_storage) : ?>
                    <aside class="product-detail__information" aria-label="<?php esc_attr_e('Product information', 'greenfarm'); ?>" data-reveal>
                        <?php if ($greenfarm_product_facts) : ?>
                            <dl class="product-facts">
                                <?php foreach ($greenfarm_product_facts as $greenfarm_label => $greenfarm_value) : ?>
                                    <div class="product-facts__item">
                                        <dt><?php echo esc_html($greenfarm_label); ?></dt>
                                        <dd><?php echo esc_html($greenfarm_value); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        <?php endif; ?>
                        <?php if ($greenfarm_storage) : ?>
                            <section class="product-storage" aria-labelledby="product-storage-title">
                                <h2 id="product-storage-title"><?php esc_html_e('Storage instructions', 'greenfarm'); ?></h2>
                                <p><?php echo nl2br(esc_html($greenfarm_storage)); ?></p>
                            </section>
                        <?php endif; ?>
                    </aside>
                <?php endif; ?>
            </div>

            <?php get_template_part('template-parts/product', 'gallery'); ?>
        </article>

        <?php
        the_post_navigation(
            array(
                'prev_text' => '<span>' . esc_html__('Previous product', 'greenfarm') . '</span><strong>%title</strong>',
                'next_text' => '<span>' . esc_html__('Next product', 'greenfarm') . '</span><strong>%title</strong>',
            )
        );
        ?>
    <?php endwhile; ?>
</main>
<?php
get_footer();
