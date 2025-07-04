<?php
/**
 * Plugin Name: RUP EDD Demo Plugin
 * Description: Demo plugin using the reusable RUP_EDD_Loader for licensing and updates.
 * Version: 1.0.0
 * Author: Your Company
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once plugin_dir_path( __FILE__ ) . 'inc/class-rup-edd-loader.php';

// Instantiate the loader with top-level menu as an example
new RUP_EDD_Loader([
    'plugin_file'    => __FILE__,
    'plugin_version' => '1.0.0',
    'store_url'      => 'https://example.com',
    'item_id'        => 123,
    'item_name'      => 'RUP EDD Demo Plugin',
    'author'         => 'Your Company',
    'menu_type'      => 'top',
    'menu_slug'      => 'rup-demo-license',
    'menu_title'     => 'Awesome License',
    'page_title'     => 'My Plugin Settings', 
]);
?>
