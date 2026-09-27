<?php
/**
 * Accessible breadcrumbs with matching structured data.
 *
 * @package GreenFarm
 */

$greenfarm_crumbs = array(
    array(
        'label' => __('Home', 'greenfarm'),
        'url'   => home_url('/'),
    ),
);

if (is_post_type_archive('greenfarm_product')) {
    $greenfarm_crumbs[] = array(
        'label' => __('Products', 'greenfarm'),
        'url'   => '',
    );
} elseif (is_tax('product_category')) {
    $greenfarm_crumbs[] = array(
        'label' => __('Products', 'greenfarm'),
        'url'   => (string) get_post_type_archive_link('greenfarm_product'),
    );
    $greenfarm_crumbs[] = array(
        'label' => single_term_title('', false),
        'url'   => '',
    );
} elseif (is_post_type_archive('farm_story')) {
    $greenfarm_crumbs[] = array(
        'label' => __('Farm Stories', 'greenfarm'),
        'url'   => '',
    );
} elseif (is_home() || is_category() || is_singular('post')) {
    $greenfarm_crumbs[] = array(
        'label' => __('Blog', 'greenfarm'),
        'url'   => greenfarm_get_blog_url(),
    );
}

if (is_category()) {
    $greenfarm_crumbs[] = array(
        'label' => single_cat_title('', false),
        'url'   => '',
    );
} elseif (is_singular('post')) {
    $greenfarm_categories = get_the_category();
    if (! empty($greenfarm_categories)) {
        $greenfarm_primary_category = $greenfarm_categories[0];
        $greenfarm_crumbs[]         = array(
            'label' => $greenfarm_primary_category->name,
            'url'   => get_category_link($greenfarm_primary_category),
        );
    }
    $greenfarm_crumbs[] = array(
        'label' => get_the_title(),
        'url'   => '',
    );
} elseif (is_search()) {
    $greenfarm_crumbs[] = array(
        'label' => __('Search', 'greenfarm'),
        'url'   => '',
    );
}

$greenfarm_schema_items = array();
foreach ($greenfarm_crumbs as $greenfarm_index => $greenfarm_crumb) {
    $greenfarm_item = array(
        '@type'    => 'ListItem',
        'position' => $greenfarm_index + 1,
        'name'     => wp_strip_all_tags($greenfarm_crumb['label']),
    );
    if ('' !== $greenfarm_crumb['url']) {
        $greenfarm_item['item'] = $greenfarm_crumb['url'];
    }
    $greenfarm_schema_items[] = $greenfarm_item;
}
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'greenfarm'); ?>">
    <ol>
        <?php foreach ($greenfarm_crumbs as $greenfarm_crumb) : ?>
            <li>
                <?php if ('' !== $greenfarm_crumb['url']) : ?>
                    <a href="<?php echo esc_url($greenfarm_crumb['url']); ?>"><?php echo esc_html($greenfarm_crumb['label']); ?></a>
                <?php else : ?>
                    <span aria-current="page"><?php echo esc_html($greenfarm_crumb['label']); ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
<script type="application/ld+json"><?php echo wp_json_encode(array('@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $greenfarm_schema_items)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
