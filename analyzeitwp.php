<?php
/*
Plugin Name: AnalyzeItWP
Plugin URI: http://example.com/analyzeitwp
Description: A plugin to scrape web pages and save the data to the database. Offers options to scrape a single page or recursively scrape all internal links.
Version: 1.0.0
Author: James Welbes
Author URI: http://example.com
License: GPL2
*/
namespace AnalyzeItWP;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin paths and URLs
define('ANALYZEITWP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ANALYZEITWP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include other necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . 'app/admin-pages.php';
require_once plugin_dir_path(__FILE__) . 'app/functions.php';
require_once plugin_dir_path(__FILE__) . 'app/ajax-handlers.php';
// require_once(ANALYZEITWP_PLUGIN_PATH . 'includes/scraping-functions.php');

// Activation hook
function activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'analyzeitwp_scraped_data';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        url varchar(255) NOT NULL,
        content longtext NOT NULL,
        scraped_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        error_log('Failed to create table: ' . $table_name);
    } else {
        error_log('Table created successfully: ' . $table_name);
    }
}



register_activation_hook(__FILE__, __NAMESPACE__ . '\\activate');

    

// Deactivation hook
function analyzeitwp_deactivate() {
    // Code to execute on plugin deactivation, like cleanup tasks
}
register_deactivation_hook(__FILE__, 'analyzeitwp_deactivate');

// Main plugin class
class AnalyzeItWP {
    public function __construct() {
        // Initialize plugin functionality, like hooking into actions and filters
        
    }
}

// Initialize the plugin
$analyzeitwp_plugin = new AnalyzeItWP();

  // Add a top-level menu for AnalyzeItWP
  function add_admin_menu() {
    // Create a top-level menu item
    add_menu_page(
        'AnalyzeItWP',                // Page title
        'AnalyzeItWP',                // Menu title
        'manage_options',             // Capability
        'analyzeitwp_main_menu',      // Menu slug
        'analyzeitwp_main_page',      // Function to display the main page
        'dashicons-search'            // Icon (optional)
    );

    // Create a submenu item for Settings
    add_submenu_page(
        'analyzeitwp_main_menu',      // Parent slug
        'Settings',                   // Page title
        'Settings',                   // Menu title
        'manage_options',             // Capability
        'analyzeitwp_settings',       // Menu slug
        'AnalyzeItWP\Admin\Pages\settings_page'   // Function to display the settings page
    );

    // Create a submenu item for OpenAI API
    add_submenu_page(
        'analyzeitwp_main_menu',      // Parent slug
        'OpenAI API',                 // Page title
        'OpenAI API',                 // Menu title
        'manage_options',             // Capability
        'analyzeitwp_openai_api',     // Menu slug
        'AnalyzeItWP\Admin\Pages\openai_api_page' // Function to display the OpenAI API page
    );
}

add_action('admin_menu', __NAMESPACE__ . '\\add_admin_menu');
