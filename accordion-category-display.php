<?php
/**
 * Plugin Name: Accordion Category Display
 * Plugin URI:  http://wordpress.org/
 * Description: This plugin displays document categories and posts in an accordion style.
 * Version:     1.0
 * Author:      Lee Hernandez
 * Author URI:  http://wordpress.org/
 */


 //Enqueue styles and scripts
 function acd_enqueue_scripts() {
    // Enqueue public styles
    wp_enqueue_style('acd-styles', plugin_dir_url(__FILE__) . 'public/css/style.css');

    // Enqueue public scripts
    wp_enqueue_script('acd-scripts', plugin_dir_url(__FILE__) . 'public/js/script.js', array('jquery'), false, true);
}
add_action('wp_enqueue_scripts', 'acd_enqueue_scripts');

//Functions and shortcode
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

//Activation and deactivation
function acd_activate() {
    // Flush rewrite rules or other setup tasks
    flush_rewrite_rules();
}
function acd_deactivate() {
    // Flush rewrite rules or other setup tasks
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'acd_activate');
register_deactivation_hook(__FILE__, 'acd_deactivate');
