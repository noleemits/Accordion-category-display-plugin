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
function my_enqueue_document_scripts()
{
    global $typenow;
    if ($typenow == 'document') {
        wp_enqueue_media();
        wp_enqueue_script('document-media-uploader', plugins_url('../public/js/document-media-uploader.js', __FILE__), array('jquery'), null, true);
    }
}
add_action('admin_enqueue_scripts', 'my_enqueue_document_scripts');
