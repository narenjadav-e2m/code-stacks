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

    // Add download button
    echo '<p><button id="download-csv" class="button">Download CSV</button></p>';

    echo '<table id="gf-usage-table" class="widefat striped"><thead>
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
                <td>' . esc_url(get_permalink($post->ID)) . '</td>
                <td>' . esc_html(implode(', ', $unique_ids)) . '</td>
            </tr>';
        }
    }

    echo '</tbody></table></div>';

    // JS for CSV download
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('download-csv').addEventListener('click', function() {
            var table = document.getElementById('gf-usage-table');
            var csv = [];
            for (var i = 0; i < table.rows.length; i++) {
                var row = [], cols = table.rows[i].cells;
                for (var j = 0; j < cols.length; j++) {
                    // Escape quotes and enclose in double-quotes
                    row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
                }
                csv.push(row.join(','));
            }
            var csv_file = new Blob([csv.join('\n')], {type: 'text/csv'});
            var download_link = document.createElement('a');
            download_link.download = 'gravity-forms-usage.csv';
            download_link.href = window.URL.createObjectURL(csv_file);
            download_link.style.display = 'none';
            document.body.appendChild(download_link);
            download_link.click();
            document.body.removeChild(download_link);
        });
    });
    </script>
    <?php
}