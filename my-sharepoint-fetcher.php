<?php
/*
Plugin Name: SharePoint Document Fetcher
Description: Fetches and displays content from SharePoint html & plain text documents
Version: 1.0.2
Author: Darren Kandekore
*/

require_once plugin_dir_path(__FILE__) . 'includes/activation.php';
require_once plugin_dir_path(__FILE__) . 'includes/menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/import.php';
require_once plugin_dir_path(__FILE__) . 'includes/upload.php';
require_once plugin_dir_path(__FILE__) . 'includes/fetcher.php';

register_activation_hook(__FILE__, 'my_plugin_create_upload_folder');
add_action('admin_menu', 'my_sharepoint_fetcher_menu');
add_shortcode('sharepoint_content', 'my_sharepoint_fetcher_fetch_content');
