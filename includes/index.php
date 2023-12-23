<?php
// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
// Include shortcode handlers
require_once plugin_dir_path(__FILE__) . '/shortcode-handlers.php';

// Include metabox-related functions
require_once plugin_dir_path(__FILE__) . '/MetaBoxes.php';

// Include utility functions
require_once plugin_dir_path(__FILE__) . '/utility-functions.php';

// Include admin scripts
require_once plugin_dir_path(__FILE__) . '/AdminScripts.php';

// Include frontend display functions
require_once plugin_dir_path(__FILE__) . '/frontend-display.php';

// Include frontend display functions
require_once plugin_dir_path(__FILE__) . '/CustomRoleManager.php';

