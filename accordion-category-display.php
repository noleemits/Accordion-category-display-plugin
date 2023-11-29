<?php
/**
 * Plugin Name: Accordion Category Display
 * Plugin URI:  http://wordpress.org/
 * Description: This plugin displays document categories and posts in an accordion style.
 * Version:     1.0
 * Author:      Lee Hernandez
 * Author URI:  http://wordpress.org/
 */


 function acd_enqueue_scripts() {
    // Enqueue public styles
    wp_enqueue_style('acd-styles', plugin_dir_url(__FILE__) . 'public/css/style.css');

    // Enqueue public scripts
    wp_enqueue_script('acd-scripts', plugin_dir_url(__FILE__) . 'public/js/script.js', array('jquery'), false, true);
}
add_action('wp_enqueue_scripts', 'acd_enqueue_scripts');
