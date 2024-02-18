<?php
function replace_scraped_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'analyzeitwp_scraped_data';

    // Check for nonce for security
    check_ajax_referer('analyzeitwp_replace_scrape_nonce', 'security');

    // Sanitize and validate URL
    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
    if (empty($url)) {
        wp_send_json_error('Invalid URL');
        return;
    }

    // Scrape the URL
    $scraped_content = analyzeitwp_scrape_url($url);
    if ($scraped_content === 'Error fetching URL' || $scraped_content === 'Error parsing HTML') {
        wp_send_json_error('Error scraping URL');
        return;
    }

    // Update the existing record with new content
    $result = $wpdb->update(
        $table_name,
        array('content' => wp_kses_post($scraped_content)), // Data to update
        array('url' => $url), // Where clause
        array('%s'), // Format of new data
        array('%s')  // Format of where clause
    );

    if (false === $result) {
        wp_send_json_error('Failed to update the database.');
    } else {
        wp_send_json_success('Data replaced successfully.');
    }
}
add_action('wp_ajax_replace_scraped_data', 'replace_scraped_data');

