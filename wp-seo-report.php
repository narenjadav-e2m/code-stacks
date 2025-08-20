<?php

/*
Plugin Name: WP SEO Report (CSV Export)
Description: Export a CSV report of published entries and their Rank Math SEO data.
Version: 1.0
Author: Naren J
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add WP SEO Report submenu under Tools
 */
add_action('admin_menu', function () {
    add_submenu_page(
        'tools.php',
        esc_html__('WP SEO Report', 'wp-seo-report'),
        esc_html__('WP SEO Report', 'wp-seo-report'),
        'manage_options',
        'wp-seo-report',
        'wp_seo_report_page_callback'
    );
});

/**
 * Render the report page
 */
function wp_seo_report_page_callback() {
    // Prevent non-admins from seeing the page
    if ( ! current_user_can( 'manage_options' ) ) return;

    $post_types = get_post_types(['public' => true], 'objects');
    ?>
    <div class="wrap">
        <h2><?php esc_html_e('WP SEO Report', 'wp-seo-report'); ?></h2>
        <p><?php esc_html_e('Select a post type to download a CSV file of all published entries with their URLs and Rank Math SEO data.', 'wp-seo-report'); ?></p>
        <form method="get" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="download_dynamic_csv" />
            <select name="post_type">
                <?php foreach ($post_types as $pt) : ?>
                    <option value="<?php echo esc_attr($pt->name); ?>"><?php echo esc_html($pt->labels->singular_name); ?></option>
                <?php endforeach; ?>
            </select>
            <?php submit_button(esc_html__('Download CSV', 'wp-seo-report'), 'primary', '', false); ?>
        </form>
    </div>
    <?php
}

/**
 * Handle CSV export
 */
add_action('admin_post_download_dynamic_csv', 'wp_seo_report_generate_csv');

function wp_seo_report_generate_csv() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wp-seo-report'));
    }

    $post_type = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : 'post';

    // Set CSV download headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $post_type . '-seo-report-' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, [
        'Post Type',
        'URL',
        'Title',
        'SEO Title (Rank Math)',
        'SEO Description (Rank Math)',
        'Focus Keyword (Rank Math)',
        'Is Noindex?',
        'Is Nofollow?',
    ]);

    // Query all posts
    $query = new WP_Query([
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'fields'         => 'ids',
    ]);

    if ( $query->have_posts() ) {
        foreach ( $query->posts as $post_id ) {
            // Rank Math meta
            $seo_title    = get_post_meta($post_id, 'rank_math_title', true);
            $seo_desc     = get_post_meta($post_id, 'rank_math_description', true);
            $seo_keywords = get_post_meta($post_id, 'rank_math_focus_keyword', true);
            $robots_meta  = get_post_meta($post_id, 'rank_math_robots', true);

            // Handle possible serialized value from older versions
            if ( is_string( $robots_meta ) ) {
                $robots = array_map( 'trim', explode( ',', $robots_meta ) );
            } else {
                $robots = is_array($robots_meta) ? $robots_meta : [];
            }

            $is_noindex  = in_array( 'noindex', $robots, true ) ? 'yes' : 'no';
            $is_nofollow = in_array( 'nofollow', $robots, true ) ? 'yes' : 'no';

            fputcsv($output, [
                $post_type,
                get_permalink($post_id),
                get_the_title($post_id),
                $seo_title,
                $seo_desc,
                $seo_keywords,
                $is_noindex,
                $is_nofollow
            ]);
        }
    }

    fclose($output);
    exit;
}