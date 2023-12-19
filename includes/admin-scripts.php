<?php
//handle media uploads
function my_admin_enqueue_scripts()
{
    global $typenow;
    if ($typenow == 'document') { // this is our custom post type
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'my_admin_enqueue_scripts');

// Enqueue the JavaScript file
// function my_enqueue_document_scripts()
// {
//     global $typenow;
//     if ($typenow == 'document') {
//         wp_enqueue_media();
//         wp_enqueue_script('document-media-uploader', plugins_url('../public/js/document-media-uploader.js', __FILE__), array('jquery'), null, true);
//     }
// }
//add_action('admin_enqueue_scripts', 'my_enqueue_document_scripts');

//Upload file validation
function my_enqueue_media_validation_script() {
    global $typenow;
    if ($typenow == 'document') {
        wp_enqueue_script('media-library-validation', plugins_url('../public/js/document-media-uploader.js', __FILE__), array('jquery'), null, true);

        // Get allowed file types from the options
        $options = get_option('accordion_category_options');
        $allowed_file_types = array_keys(array_filter($options));

        // Localize the script with new data
        wp_localize_script('media-library-validation', 'myPluginData', array(
            'allowedFileTypes' => $allowed_file_types
        ));
    }
}
add_action('admin_enqueue_scripts', 'my_enqueue_media_validation_script');

