<?php
/**
 * GreenFarm content type registrations.
 *
 * @package GreenFarmCore
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register Products, Product Categories, and Farm Stories.
 */
function greenfarm_core_register_content_types(): void
{
    $shared_supports = array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author');

    register_taxonomy(
        'product_category',
        array('greenfarm_product'),
        array(
            'labels' => array(
                'name'          => __('Product Categories', 'greenfarm-core'),
                'singular_name' => __('Product Category', 'greenfarm-core'),
                'search_items'  => __('Search Product Categories', 'greenfarm-core'),
                'all_items'     => __('All Product Categories', 'greenfarm-core'),
                'edit_item'     => __('Edit Product Category', 'greenfarm-core'),
                'add_new_item'  => __('Add New Product Category', 'greenfarm-core'),
            ),
            'public'            => true,
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => array(
                'slug'         => 'products/category',
                'with_front'   => false,
                'hierarchical' => true,
            ),
        )
    );

    register_post_type(
        'greenfarm_product',
        array(
            'labels' => array(
                'name'          => __('Products', 'greenfarm-core'),
                'singular_name' => __('Product', 'greenfarm-core'),
                'add_new_item'  => __('Add New Product', 'greenfarm-core'),
                'edit_item'     => __('Edit Product', 'greenfarm-core'),
                'view_item'     => __('View Product', 'greenfarm-core'),
                'search_items'  => __('Search Products', 'greenfarm-core'),
                'not_found'     => __('No products found.', 'greenfarm-core'),
            ),
            'public'              => true,
            'show_in_rest'        => true,
            'exclude_from_search' => false,
            'has_archive'         => 'products',
            'rewrite'             => array(
                'slug'       => 'products',
                'with_front' => false,
            ),
            'menu_icon'           => 'dashicons-carrot',
            'supports'            => array_merge($shared_supports, array('custom-fields')),
        )
    );

    register_post_type(
        'farm_story',
        array(
            'labels' => array(
                'name'          => __('Farm Stories', 'greenfarm-core'),
                'singular_name' => __('Farm Story', 'greenfarm-core'),
                'add_new_item'  => __('Add New Farm Story', 'greenfarm-core'),
                'edit_item'     => __('Edit Farm Story', 'greenfarm-core'),
                'view_item'     => __('View Farm Story', 'greenfarm-core'),
                'search_items'  => __('Search Farm Stories', 'greenfarm-core'),
                'not_found'     => __('No farm stories found.', 'greenfarm-core'),
            ),
            'public'              => true,
            'show_in_rest'        => true,
            'exclude_from_search' => false,
            'has_archive'         => 'farm-stories',
            'rewrite'             => array(
                'slug'       => 'farm-stories',
                'with_front' => false,
            ),
            'menu_icon'           => 'dashicons-book-alt',
            'supports'            => $shared_supports,
        )
    );

}
