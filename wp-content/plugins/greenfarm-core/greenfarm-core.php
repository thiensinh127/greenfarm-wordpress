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

require_once __DIR__ . '/includes/content-types.php';

add_action('init', 'greenfarm_core_register_content_types');

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
