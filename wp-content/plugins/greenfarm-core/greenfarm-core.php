<?php
/**
 * Plugin Name: GreenFarm Core
 * Description: Durable content models for GreenFarm products and farm stories.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 8.2
 * Text Domain: greenfarm-core
 */

if (! defined('ABSPATH')) {
    exit;
}

define('GREENFARM_CORE_VERSION', '1.0.0');
define('GREENFARM_CORE_FILE', __FILE__);

require_once __DIR__ . '/includes/content-types.php';
require_once __DIR__ . '/includes/product-meta.php';

add_action('init', 'greenfarm_core_register_content_types');
add_action('init', 'greenfarm_core_register_product_meta');
add_action('add_meta_boxes', 'greenfarm_core_add_product_meta_boxes');
add_action('add_meta_boxes_greenfarm_product', 'greenfarm_core_remove_default_product_custom_fields_box', 100);
add_action('save_post_greenfarm_product', 'greenfarm_core_save_product_meta');
add_action('admin_enqueue_scripts', 'greenfarm_core_enqueue_product_admin_assets');

/**
 * Register content before refreshing permalinks on activation.
 */
function greenfarm_core_activate(): void
{
    greenfarm_core_register_content_types();
    flush_rewrite_rules();
}

/**
 * Remove stored rewrite rules on deactivation.
 */
function greenfarm_core_deactivate(): void
{
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'greenfarm_core_activate');
register_deactivation_hook(__FILE__, 'greenfarm_core_deactivate');
