<?php

namespace AccordionCategoryDisplay;

class AdminScripts {
    public function init() {
        add_action('admin_enqueue_scripts', array($this, 'my_admin_enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'my_enqueue_media_validation_script'));
    }

    public function my_admin_enqueue_scripts()
    {
        global $typenow;
        if ($typenow == 'document') {
            wp_enqueue_media();
        }
    }
    /**
     * Enqueues the media validation script for the Document post type.
     */

    public function my_enqueue_media_validation_script()
    {
        global $typenow;
        if ($typenow == 'document') {
            wp_enqueue_script('media-library-validation', plugins_url('../public/js/document-media-uploader.js', __FILE__), array('jquery'), null, true);

            $options = get_option('accordion_category_options');
            $allowed_file_types = array_keys(array_filter($options));

            // wp_localize_script('media-library-validation', 'myPluginData', array(
            //     'allowedFileTypes' => $allowed_file_types
            // ));
        }
    }
}

