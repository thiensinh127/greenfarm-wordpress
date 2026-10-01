<?php
/**
 * Optional homepage product discovery sections.
 *
 * @package GreenFarm
 */

if (taxonomy_exists('product_category')) {
    $greenfarm_categories = get_terms(
        array(
            'taxonomy'   => 'product_category',
            'hide_empty' => true,
            'number'     => 4,
        )
    );

    if (! is_wp_error($greenfarm_categories) && $greenfarm_categories) :
        ?>
        <section class="home-section home-categories" aria-labelledby="home-categories-title" data-reveal>
            <div class="home-section__heading">
                <p class="home-eyebrow"><?php esc_html_e('Explore the harvest', 'greenfarm'); ?></p>
                <h2 id="home-categories-title"><?php esc_html_e('Product categories', 'greenfarm'); ?></h2>
            </div>
            <div class="home-categories__grid">
                <?php foreach ($greenfarm_categories as $greenfarm_category) : ?>
                    <?php
                    $greenfarm_category_url      = get_term_link($greenfarm_category);
                    $greenfarm_category_products = new WP_Query(
                        array(
                            'post_type'           => 'greenfarm_product',
                            'post_status'         => 'publish',
                            'posts_per_page'      => 1,
                            'ignore_sticky_posts' => true,
                            'no_found_rows'       => true,
                            'tax_query'           => array(
                                array(
                                    'taxonomy' => 'product_category',
                                    'field'    => 'term_id',
                                    'terms'    => $greenfarm_category->term_id,
                                ),
                            ),
                        )
                    );
                    $greenfarm_category_image_id = $greenfarm_category_products->have_posts()
                        ? get_post_thumbnail_id($greenfarm_category_products->posts[0]->ID)
                        : 0;
                    ?>
                    <?php if (! is_wp_error($greenfarm_category_url)) : ?>
                        <a class="home-category-card" href="<?php echo esc_url($greenfarm_category_url); ?>" data-reveal-child>
                            <?php if ($greenfarm_category_image_id) : ?>
                                <span class="home-category-card__media" aria-hidden="true">
                                    <?php echo wp_get_attachment_image($greenfarm_category_image_id, 'medium_large', false, array('alt' => '', 'sizes' => '(min-width: 64rem) 25vw, (min-width: 40rem) 50vw, 100vw')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </span>
                            <?php endif; ?>
                            <span class="home-category-card__content">
                                <span><?php echo esc_html($greenfarm_category->name); ?></span>
                                <span aria-hidden="true">→</span>
                            </span>
                        </a>
                    <?php endif; ?>
                    <?php wp_reset_postdata(); ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    endif;
}

if (post_type_exists('greenfarm_product')) {
    $greenfarm_products = new WP_Query(
        array(
            'post_type'           => 'greenfarm_product',
            'post_status'         => 'publish',
            'posts_per_page'      => 4,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        )
    );

    if ($greenfarm_products->have_posts()) :
        ?>
        <section class="home-section home-products" aria-labelledby="home-products-title" data-reveal>
            <div class="home-section__heading home-section__heading--with-link">
                <div>
                    <p class="home-eyebrow"><?php esc_html_e('In season', 'greenfarm'); ?></p>
                    <h2 id="home-products-title"><?php esc_html_e('Featured products', 'greenfarm'); ?></h2>
                </div>
                <?php $greenfarm_products_url = get_post_type_archive_link('greenfarm_product'); ?>
                <?php if ($greenfarm_products_url) : ?>
                    <a class="home-text-link" href="<?php echo esc_url($greenfarm_products_url); ?>"><?php esc_html_e('View all products', 'greenfarm'); ?></a>
                <?php endif; ?>
            </div>
            <div class="home-products__grid">
                <?php
                while ($greenfarm_products->have_posts()) {
                    $greenfarm_products->the_post();
                    get_template_part(
                        'template-parts/content',
                        'product-card',
                        array(
                            'heading_level' => 'h3',
                            'card_class'    => 'home-product-card',
                            'image_size'    => 'medium_large',
                            'image_context' => 'home',
                        )
                    );
                }
                ?>
            </div>
        </section>
        <?php
    endif;
    wp_reset_postdata();
}
