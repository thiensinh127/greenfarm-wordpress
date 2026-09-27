<?php
/**
 * Native Product metadata and editor fields.
 *
 * @package GreenFarmCore
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Return the controlled Product availability vocabulary.
 *
 * @return array<string, string>
 */
function greenfarm_core_get_availability_options(): array
{
    return array(
        'available'   => __('Available now', 'greenfarm-core'),
        'limited'     => __('Limited availability', 'greenfarm-core'),
        'seasonal'    => __('Seasonal', 'greenfarm-core'),
        'unavailable' => __('Currently unavailable', 'greenfarm-core'),
    );
}

/**
 * Get a public label for a valid availability value.
 */
function greenfarm_core_get_availability_label(string $value): string
{
    $options = greenfarm_core_get_availability_options();

    return $options[$value] ?? '';
}

/**
 * Restrict availability to the controlled vocabulary or empty.
 *
 * @param mixed $value Submitted value.
 */
function greenfarm_core_sanitize_availability($value): string
{
    if (! is_string($value)) {
        return '';
    }

    $value = sanitize_key($value);

    return isset(greenfarm_core_get_availability_options()[$value]) ? $value : '';
}

/**
 * Normalize gallery input to ordered unique positive attachment IDs.
 *
 * @param mixed $value Submitted value.
 * @return array<int>
 */
function greenfarm_core_sanitize_gallery_ids($value): array
{
    if (is_string($value)) {
        $value = explode(',', $value);
    }

    if (! is_array($value)) {
        return array();
    }

    $ids = array();
    foreach ($value as $candidate) {
        if (! is_scalar($candidate) || ! is_numeric($candidate)) {
            continue;
        }

        $id = (int) $candidate;
        if ($id > 0 && ! in_array($id, $ids, true)) {
            $ids[] = $id;
        }
    }

    return $ids;
}

/**
 * Permit registered Product meta only to users who can edit the Product.
 */
function greenfarm_core_auth_product_meta(bool $allowed, string $meta_key, int $post_id): bool
{
    return current_user_can('edit_post', $post_id);
}

/**
 * Register the structured Product metadata contract.
 */
function greenfarm_core_register_product_meta(): void
{
    $shared = array(
        'object_subtype' => 'greenfarm_product',
        'single'         => true,
        'show_in_rest'   => true,
        'auth_callback'  => 'greenfarm_core_auth_product_meta',
    );

    foreach (array('greenfarm_origin', 'greenfarm_farming_method', 'greenfarm_harvest_season') as $key) {
        register_post_meta(
            'greenfarm_product',
            $key,
            array_merge(
                $shared,
                array(
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                )
            )
        );
    }

    register_post_meta(
        'greenfarm_product',
        'greenfarm_storage_instructions',
        array_merge(
            $shared,
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
            )
        )
    );

    register_post_meta(
        'greenfarm_product',
        'greenfarm_availability',
        array_merge(
            $shared,
            array(
                'type'              => 'string',
                'sanitize_callback' => 'greenfarm_core_sanitize_availability',
            )
        )
    );

    register_post_meta(
        'greenfarm_product',
        'greenfarm_gallery_ids',
        array(
            'object_subtype'    => 'greenfarm_product',
            'type'              => 'array',
            'single'            => true,
            'sanitize_callback' => 'greenfarm_core_sanitize_gallery_ids',
            'auth_callback'     => 'greenfarm_core_auth_product_meta',
            'show_in_rest'      => array(
                'schema' => array(
                    'type'  => 'array',
                    'items' => array('type' => 'integer'),
                ),
            ),
        )
    );
}

/**
 * Register Product-only native meta boxes.
 */
function greenfarm_core_add_product_meta_boxes(): void
{
    add_meta_box(
        'greenfarm_core_product_details',
        __('Product details', 'greenfarm-core'),
        'greenfarm_core_render_product_details_meta_box',
        'greenfarm_product',
        'normal',
        'default'
    );
    add_meta_box(
        'greenfarm_core_product_gallery',
        __('Product gallery', 'greenfarm-core'),
        'greenfarm_core_render_product_gallery_meta_box',
        'greenfarm_product',
        'side',
        'default'
    );
}

/**
 * Render structured Product detail controls.
 */
function greenfarm_core_render_product_details_meta_box(WP_Post $post): void
{
    wp_nonce_field('greenfarm_core_save_product_meta', 'greenfarm_core_product_meta_nonce');

    $fields = array(
        'greenfarm_origin'                  => __('Origin', 'greenfarm-core'),
        'greenfarm_farming_method'          => __('Farming method', 'greenfarm-core'),
        'greenfarm_harvest_season'          => __('Harvest season', 'greenfarm-core'),
        'greenfarm_storage_instructions'    => __('Storage instructions', 'greenfarm-core'),
    );

    foreach ($fields as $key => $label) {
        $value = (string) get_post_meta($post->ID, $key, true);
        ?>
        <p>
            <label for="<?php echo esc_attr($key); ?>"><strong><?php echo esc_html($label); ?></strong></label><br>
            <?php if ('greenfarm_storage_instructions' === $key) : ?>
                <textarea class="widefat" rows="4" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>"><?php echo esc_textarea($value); ?></textarea>
            <?php else : ?>
                <input class="widefat" type="text" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
            <?php endif; ?>
        </p>
        <?php
    }

    $availability = (string) get_post_meta($post->ID, 'greenfarm_availability', true);
    ?>
    <p>
        <label for="greenfarm_availability"><strong><?php esc_html_e('Availability', 'greenfarm-core'); ?></strong></label><br>
        <select class="widefat" id="greenfarm_availability" name="greenfarm_availability">
            <option value=""><?php esc_html_e('Not specified', 'greenfarm-core'); ?></option>
            <?php foreach (greenfarm_core_get_availability_options() as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($availability, $value); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

/**
 * Render the progressively enhanced Product gallery field.
 */
function greenfarm_core_render_product_gallery_meta_box(WP_Post $post): void
{
    $ids = greenfarm_core_sanitize_gallery_ids(get_post_meta($post->ID, 'greenfarm_gallery_ids', true));
    ?>
    <div data-greenfarm-gallery>
        <input type="hidden" name="greenfarm_gallery_ids" value="<?php echo esc_attr(implode(',', $ids)); ?>" data-greenfarm-gallery-input>
        <div data-greenfarm-gallery-preview>
            <?php foreach ($ids as $attachment_id) : ?>
                <?php if (wp_attachment_is_image($attachment_id)) : ?>
                    <span class="greenfarm-gallery-item" data-greenfarm-gallery-item>
                        <?php echo wp_get_attachment_image($attachment_id, 'thumbnail'); ?>
                        <button type="button" class="button-link-delete" data-greenfarm-gallery-remove-item data-attachment-id="<?php echo esc_attr((string) $attachment_id); ?>">
                            <?php esc_html_e('Remove', 'greenfarm-core'); ?>
                        </button>
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <p>
            <button type="button" class="button" data-greenfarm-gallery-select><?php esc_html_e('Choose images', 'greenfarm-core'); ?></button>
            <button type="button" class="button-link-delete" data-greenfarm-gallery-remove><?php esc_html_e('Clear gallery', 'greenfarm-core'); ?></button>
        </p>
    </div>
    <?php
}

/**
 * Load Media Library gallery enhancement only on Product edit screens.
 */
function greenfarm_core_enqueue_product_admin_assets(string $hook_suffix): void
{
    if (! in_array($hook_suffix, array('post.php', 'post-new.php'), true)) {
        return;
    }

    $screen = get_current_screen();
    if (! $screen || 'greenfarm_product' !== $screen->post_type) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style(
        'greenfarm-core-product-admin',
        plugins_url('assets/css/product-admin.css', GREENFARM_CORE_FILE),
        array(),
        GREENFARM_CORE_VERSION
    );
    wp_enqueue_script(
        'greenfarm-core-product-gallery',
        plugins_url('assets/js/product-gallery.js', GREENFARM_CORE_FILE),
        array(),
        GREENFARM_CORE_VERSION,
        true
    );
}

/**
 * Save Product metadata after validating request and user context.
 */
function greenfarm_core_save_product_meta(int $post_id): void
{
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || 'greenfarm_product' !== get_post_type($post_id)) {
        return;
    }

    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    $nonce = isset($_POST['greenfarm_core_product_meta_nonce'])
        ? sanitize_text_field(wp_unslash($_POST['greenfarm_core_product_meta_nonce']))
        : '';
    if (! wp_verify_nonce($nonce, 'greenfarm_core_save_product_meta')) {
        return;
    }

    $fields = array(
        'greenfarm_origin'               => 'sanitize_text_field',
        'greenfarm_farming_method'       => 'sanitize_text_field',
        'greenfarm_harvest_season'       => 'sanitize_text_field',
        'greenfarm_storage_instructions' => 'sanitize_textarea_field',
        'greenfarm_availability'         => 'greenfarm_core_sanitize_availability',
    );

    foreach ($fields as $key => $sanitize) {
        if (! array_key_exists($key, $_POST)) {
            delete_post_meta($post_id, $key);
            continue;
        }

        $value = call_user_func($sanitize, wp_unslash($_POST[$key]));
        if ('' === $value) {
            delete_post_meta($post_id, $key);
        } else {
            update_post_meta($post_id, $key, $value);
        }
    }

    if (array_key_exists('greenfarm_gallery_ids', $_POST)) {
        $gallery_ids = greenfarm_core_sanitize_gallery_ids(wp_unslash($_POST['greenfarm_gallery_ids']));
        if ($gallery_ids) {
            update_post_meta($post_id, 'greenfarm_gallery_ids', $gallery_ids);
        } else {
            delete_post_meta($post_id, 'greenfarm_gallery_ids');
        }
    }
}
