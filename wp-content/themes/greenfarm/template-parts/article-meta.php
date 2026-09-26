<?php
/**
 * Article author and date metadata.
 *
 * @package GreenFarm
 */

$greenfarm_published = (int) get_post_time('U', true);
$greenfarm_modified  = (int) get_post_modified_time('U', true);
$greenfarm_is_updated = $greenfarm_modified > ($greenfarm_published + DAY_IN_SECONDS);
?>
<div class="article-meta">
    <span class="article-meta__author">
        <?php esc_html_e('By', 'greenfarm'); ?>
        <a href="<?php echo esc_url(get_author_posts_url((int) get_the_author_meta('ID'))); ?>" rel="author">
            <?php echo esc_html(get_the_author()); ?>
        </a>
    </span>
    <time class="article-meta__published" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
        <?php echo esc_html(get_the_date()); ?>
    </time>
    <?php if ($greenfarm_is_updated) : ?>
        <time class="article-meta__updated" datetime="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>">
            <?php
            printf(
                /* translators: %s: last updated date. */
                esc_html__('Updated %s', 'greenfarm'),
                esc_html(get_the_modified_date())
            );
            ?>
        </time>
    <?php endif; ?>
</div>

