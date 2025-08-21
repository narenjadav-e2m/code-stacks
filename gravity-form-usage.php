<?php

add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        'Gravity Forms Usage',
        'Gravity Forms Usage',
        'manage_options',
        'gf-usage',
        'list_gf_usage'
    );
});

function list_gf_usage() {
    global $wpdb;

    // Get all posts of the requested types
    $posts = $wpdb->get_results("
        SELECT ID, post_title, post_type, post_content
        FROM $wpdb->posts
        WHERE post_status IN ('publish', 'private', 'draft', 'pending', 'future')
        AND post_type IN ('post', 'page', 'elementor_library')
    ");

    echo '<div class="wrap"><h1>Gravity Forms Usage</h1>';
    echo '<table class="widefat striped"><thead>
            <tr>
                <th>Post Title</th>
                <th>URL</th>
                <th>Form ID(s)</th>
            </tr>
        </thead><tbody>';

    foreach ($posts as $post) {
        $all_content = $post->post_content;

        // Get _elementor_data if exists
        $elementor_data = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = '_elementor_data'",
            $post->ID
        ));
        if ($elementor_data) {
            $all_content .= "\n" . $elementor_data;
        }

        // Extract all gravityform IDs from post content and elementor, allow id="5", id='5', id=5
        preg_match_all('/\[gravityform[^\]]*id\s*=\s*([\'"]?)(\d+)\1/i', $all_content, $matches);
        $unique_ids = !empty($matches[2]) ? array_unique($matches[2]) : [];

        if (!empty($unique_ids)) {
            echo '<tr>
                <td>' . esc_html($post->post_title) . '</td>
                <td><a href="' . esc_url(get_permalink($post->ID)) . '" target="_blank">' . esc_html(get_permalink($post->ID)) . '</a></td>
                <td>' . esc_html(implode(', ', $unique_ids)) . '</td>
            </tr>';
        }
    }

    echo '</tbody></table></div>';
}