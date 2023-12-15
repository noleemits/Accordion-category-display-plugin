<?php

// Add meta box for file uploads
function my_custom_file_upload_meta_box()
{
    add_meta_box(
        'my_custom_file_upload',          // Unique ID of the meta box
        __('Upload File', 'textdomain'),  // Title of the meta box
        'my_custom_file_upload_callback', // Callback function
        'document',                       // Post type
        'advanced',                       // Context
        'high'                            // Priority
    );
}
add_action('add_meta_boxes', 'my_custom_file_upload_meta_box');

// Callback function to display the meta box
function my_custom_file_upload_callback($post)
{
    wp_nonce_field('my_custom_file_upload', 'my_custom_file_upload_nonce');

    // Button that triggers the media uploader
    echo '<button type="button" class="button" id="my_media_manager">Upload or Select Document</button>';
    // Hidden input to store the file URL
    echo '<input type="hidden" id="my_custom_document" name="my_custom_document" value="" />';
}


// Save the file when the post is saved
function my_save_custom_file($post_id)
{
    // Check if our nonce is set and verify the admin has the intent to save the file.
    if (!isset($_POST['my_custom_file_upload_nonce']) || !wp_verify_nonce($_POST['my_custom_file_upload_nonce'], 'my_custom_file_upload')) {
        return;
    }
    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // Check the user's permissions.
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    // Its safe for us to save the data now.
    if (!empty($_POST['my_custom_document'])) {
        // Sanitize the URL
        $file_url = esc_url_raw($_POST['my_custom_document']);
        // Save the file URL
        update_post_meta($post_id, 'my_custom_document_file', $file_url);
    }
}
add_action('save_post', 'my_save_custom_file');


// Make sure the file array isn't empty
function update_edit_form()
{
    echo ' enctype="multipart/form-data"';
}
add_action('post_edit_form_tag', 'update_edit_form');
