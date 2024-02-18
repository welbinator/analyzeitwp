<?php
use voku\helper\HtmlDomParser;

function analyzeitwp_scrape_url($url) {
    // Use WordPress's HTTP API to fetch the content of the URL
    $response = wp_remote_get($url);

    // Check for errors or an invalid response
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
        return 'Error fetching URL';
    }

    // Get the body of the response
    $html_content = wp_remote_retrieve_body($response);

    // Parse the HTML content
    $html = HtmlDomParser::str_get_html($html_content);

    if (!$html) {
        return 'Error parsing HTML';
    }

    // Example: Extract the entire body or a specific part of the page
    $body = $html->findOne('body');
    if ($body) {
        $body_content = $body->innerHtml();  // Extract the entire body content
    } else {
        $body_content = 'Body content not found';
    }

    return $body_content; // Return the entire body content or a specific part
}


register_setting('analyzeitwp_settings', 'analyzeitwp_scrape_url', [
    'type' => 'string',
    'sanitize_callback' => __NAMESPACE__ . '\\handle_scrape_request',
]);

function handle_scrape_request($input) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'analyzeitwp_scraped_data';
    
    // Verify nonce for security
    if (!isset($_POST['analyzeitwp_scrape_nonce_field']) || 
        !wp_verify_nonce($_POST['analyzeitwp_scrape_nonce_field'], 'analyzeitwp_scrape_nonce')) {
        wp_die('Security check failed');
    }

    $input = esc_url_raw($input); // Sanitize URL

    // Check if URL exists in the database
    $existing_url = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE url = %s",
        $input
    ));

    if ($existing_url > 0) {
        // Generate a nonce for the AJAX request
        $nonce = wp_create_nonce('analyzeitwp_replace_scrape_nonce');

        // Inline JavaScript for confirmation and AJAX call
        add_action('admin_notices', function() use ($input, $nonce) {
            ?>
            <script type="text/javascript">
                if (confirm("This URL has been scraped before. Replace old data with new data?")) {
                    var data = {
                        'action': 'replace_scraped_data',
                        'url': "<?php echo esc_js($input); ?>",
                        'security': "<?php echo $nonce; ?>"
                    };

                    jQuery.post(ajaxurl, data, function(response) {
                        if (response.success) {
                            alert('Data replaced successfully.');
                        } else {
                            alert('Error: ' + response.data);
                        }
                    });
                }
            </script>
            <?php
        });
        return $input; // Stop further processing
    }

    // Proceed with scraping if URL doesn't exist
    $scraped_content = analyzeitwp_scrape_url($input);
    analyzeitwp_save_scraped_content($input, $scraped_content);

    return $input;
}

function analyzeitwp_save_scraped_content($url, $content) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'analyzeitwp_scraped_data';

    $wpdb->insert(
        $table_name,
        array(
            'url' => $url,
            'content' => wp_kses_post($content) // Sanitize HTML content
        ),
        array('%s', '%s')
    );
}

