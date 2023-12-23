<?php
/**
 * Plugin Name: Accordion Category Display
 * Plugin URI:  http://wordpress.org/
 * Description: This plugin displays document categories and posts in an accordion style.
 * Version:     1.0
 * Author:      Lee Hernandez
 * Author URI:  http://wordpress.org/
 */

 namespace AccordionCategoryDisplay;

//Enqueue styles and scripts

function acd_enqueue_scripts()
{
    // Enqueue public styles
    wp_enqueue_style('acd-styles', plugin_dir_url(__FILE__) . 'public/css/style.css');

    // Enqueue public scripts
    wp_enqueue_script('acd-scripts', plugin_dir_url(__FILE__) . 'public/js/script.js', array('jquery'), false, true);
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\acd_enqueue_scripts');


//Post types
require_once plugin_dir_path(__FILE__) . 'includes/post-types.php';

//Functions and shortcode
require_once plugin_dir_path(__FILE__) . 'includes/index.php';

//Functions and shortcode
require_once plugin_dir_path(__FILE__) . 'admin/accordion_category_settings.php';

//Activation and deactivation
function acd_activate()
{

    create_document_post_type();
    create_document_category_taxonomy();
    flush_rewrite_rules();
    acd_add_custom_role();
}




require_once plugin_dir_path(__FILE__) . 'includes/AdminScripts.php';

class Plugin {
    private $adminScripts;
    private $customRoleManager;

    public function __construct() {
        $this->adminScripts = new AdminScripts();
        $this->customRoleManager = new CustomRoleManager();
        $this->metaBoxes = new MetaBoxes();
    }

    public function init() {
        $this->adminScripts->init();
        $this->customRoleManager->init();
        $this->metaBoxes->init();
    }
}

$plugin = new Plugin();
$plugin->init();
