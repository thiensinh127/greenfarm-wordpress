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
                    <?php $greenfarm_category_url = get_term_link($greenfarm_category); ?>
                    <?php if (! is_wp_error($greenfarm_category_url)) : ?>
                        <a class="home-category-card" href="<?php echo esc_url($greenfarm_category_url); ?>" data-reveal-child>
                            <span><?php echo esc_html($greenfarm_category->name); ?></span>
                            <span aria-hidden="true">→</span>
                        </a>
                    <?php endif; ?>
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
            <div class="home-section__heading">
                <p class="home-eyebrow"><?php esc_html_e('In season', 'greenfarm'); ?></p>
                <h2 id="home-products-title"><?php esc_html_e('Featured products', 'greenfarm'); ?></h2>
            </div>
            <div class="home-products__grid">
                <?php
                while ($greenfarm_products->have_posts()) {
                    $greenfarm_products->the_post();
                    $greenfarm_availability = trim((string) get_post_meta(get_the_ID(), 'availability', true));
                    ?>
                    <article class="home-product-card" data-reveal-child>
                        <?php if (has_post_thumbnail()) : ?>
                            <a class="home-product-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                                <?php the_post_thumbnail('medium_large', array('sizes' => '(min-width: 64rem) 25vw, (min-width: 40rem) 50vw, 100vw')); ?>
                            </a>
                        <?php endif; ?>
                        <div class="home-product-card__body">
                            <?php if ($greenfarm_availability) : ?>
                                <p class="home-product-card__availability"><?php echo esc_html($greenfarm_availability); ?></p>
                            <?php endif; ?>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <?php if (has_excerpt()) : ?>
                                <p><?php echo esc_html(get_the_excerpt()); ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php
                }
                ?>
            </div>
        </section>
        <?php
    endif;
    wp_reset_postdata();
}

