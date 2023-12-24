<?php
// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
//Include category settings
require_once plugin_dir_path( __FILE__ ) . '../admin/AccordionCategorySettings.php';

// Include shortcode handlers
require_once plugin_dir_path(__FILE__) . '/ShortcodeHandlers.php';

// Include metabox-related functions
require_once plugin_dir_path(__FILE__) . '/MetaBoxes.php';

// Include utility functions
require_once plugin_dir_path(__FILE__) . '/UtilityFunctions.php';

// Include admin scripts
require_once plugin_dir_path(__FILE__) . '/AdminScripts.php';

// Include frontend display functions
require_once plugin_dir_path(__FILE__) . '/FrontendDisplay.php';

// Include frontend display functions
require_once plugin_dir_path(__FILE__) . '/CustomRoleManager.php';

// Include post t
require_once plugin_dir_path(__FILE__) . '/PostTypes.php';

