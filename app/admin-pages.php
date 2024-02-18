<?php
/**
 * Admin Pages Functionality
 *
 * This file contains functions that handle the display and functionality
 * of various admin pages for the AnalyzeItWP plugin, including settings 
 * and OpenAI API key configuration.
 *
 * @package AnalyzeItWP
 * @subpackage Admin\Pages
 */

namespace AnalyzeItWP\Admin\Pages;

/**
 * Displays the main page content for the AnalyzeItWP plugin.
 */
function analyzeitwp_main_page() {
    echo '<h1>AnalyzeItWP Main Page</h1>';
}

/**
 * Displays the settings page content for the AnalyzeItWP plugin.
 */
function settings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'analyzeitwp_scraped_data';

    // Fetch URLs from the database
    $urls = $wpdb->get_results("SELECT id, url FROM $table_name");

    ?>
    <h1>Settings</h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('analyzeitwp_settings');
        do_settings_sections('analyzeitwp_settings');
        wp_nonce_field('analyzeitwp_scrape_nonce', 'analyzeitwp_scrape_nonce_field');
        submit_button('Scrape URL');
        ?>

        <h2>Compare URLs</h2>
        <select id="url1" name="url1">
            <?php foreach ($urls as $url): ?>
                <option value="<?php echo esc_attr($url->id); ?>">
                    <?php echo esc_html($url->url); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span> compare </span>
        <select id="url2" name="url2">
            <?php foreach ($urls as $url): ?>
                <option value="<?php echo esc_attr($url->id); ?>">
                    <?php echo esc_html($url->url); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="button" id="compare">Compare</button>
    </form>
    <?php
}


/**
 * Displays the OpenAI API settings page content for the AnalyzeItWP plugin.
 */
function openai_api_page() {
    // Contents of the OpenAI API page
    ?>
    <h1>OpenAI API Settings</h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('analyzeitwp_openai_api');
        do_settings_sections('analyzeitwp_openai_api');
        submit_button();
        ?>
    </form>
    <?php
}

/**
 * Initializes settings for the AnalyzeItWP plugin.
 */
function settings_init() {
    // Register a new setting for the OpenAI API key
    register_setting('analyzeitwp_openai_api', 'analyzeitwp_openai_api_key');

    // Add a section to the OpenAI API page
    add_settings_section(
        'analyzeitwp_openai_api_section', 
        'OpenAI API Key', 
        __NAMESPACE__ . '\\openai_api_section_callback', 
        'analyzeitwp_openai_api'
    );

    // Add a field for the OpenAI API key
    add_settings_field(
        'analyzeitwp_openai_api_key_field', 
        'API Key', 
        __NAMESPACE__ . '\\openai_api_key_field_callback', 
        'analyzeitwp_openai_api', 
        'analyzeitwp_openai_api_section'
    );

    // Register a new setting for the Scrape URL
    register_setting('analyzeitwp_settings', 'analyzeitwp_scrape_url');

    // Add a section to the Settings page
    add_settings_section(
        'analyzeitwp_settings_section', 
        'Scraping Settings', 
        __NAMESPACE__ . '\\settings_section_callback', 
        'analyzeitwp_settings'
    );

    // Add a field for the Scrape URL
    add_settings_field(
        'analyzeitwp_scrape_url_field', 
        'Scrape URL', 
        __NAMESPACE__ . '\\scrape_url_field_callback', 
        'analyzeitwp_settings', 
        'analyzeitwp_settings_section'
    );
}

add_action('admin_init', __NAMESPACE__ . '\\settings_init');

function openai_api_section_callback() {
    echo 'Enter your OpenAI API Key:';
}

function openai_api_key_field_callback() {
    $api_key = get_option('analyzeitwp_openai_api_key');
    echo '<input type="text" id="analyzeitwp_openai_api_key" name="analyzeitwp_openai_api_key" value="' . esc_attr($api_key) . '">';
}

function settings_section_callback() {
    echo 'Enter the URL you want to scrape:';
}

function scrape_url_field_callback() {
    echo '<input type="text" id="analyzeitwp_scrape_url" name="analyzeitwp_scrape_url">';
}


