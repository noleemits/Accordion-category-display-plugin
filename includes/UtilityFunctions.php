<?php

namespace AccordionCategoryDisplay;

class UtilityFunctions{
    public function init(){
        add_action('init', array($this, 'create_document_post_type'));
        add_action('init', array($this, 'create_document_category_taxonomy'));
    }
}
function save_custom_user_field($term_id) {
    if (isset($_POST['my_custom_user_field'])) {
        update_term_meta($term_id, 'my_custom_user_field', sanitize_text_field($_POST['my_custom_user_field']));
    }
}
add_action('edited_document_category', 'save_custom_user_field', 10, 2);


// Standalone function to recursively get allowed users from parent categories
function get_allowed_users_from_parents($term_id) {
    $allowed_users = [];

    while ($term_id != 0) {
        $user_ids = get_term_meta($term_id, 'my_custom_user_field', true);
        if ($user_ids) {
            if (is_array($user_ids)) {
                $allowed_users = array_merge($allowed_users, $user_ids);
            } else {
                $allowed_users[] = $user_ids;
            }
        }
        $term_id = get_term($term_id)->parent;
    }

    return array_unique($allowed_users);
}

//Check user access
function user_has_access_to_category($category_id) {
    $current_user_id = get_current_user_id();
    $allowed_users = get_allowed_users_from_parents($category_id);

    return in_array($current_user_id, $allowed_users);
}

//Change default directory for the file upload
// Target 'file_upload' field for custom upload directory and filename
add_filter('acf/upload_prefilter/name=file_upload', 'my_acf_upload_prefilter');
add_filter('wp_handle_upload_prefilter', 'my_custom_file_upload_prefilter');
function my_custom_file_upload_prefilter($file) {
    add_filter('upload_dir', 'file_upload_folder', 20);
    return $file;
}

// File upload folder - it creates it it doesn't exist
function file_upload_folder($uploads) {
    // Get the post ID if available (it might not be available on every admin page)
    $post_id = isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']) : 0;

    // If we have a post ID, check the post type
    if ($post_id) {
        $post_type = get_post_type($post_id);

        // Apply custom folder only for 'document' post type
        if ($post_type == 'document') {
            // Define the custom folder path
            $custom_dir = '/customer_files';

            // Full path to the custom directory
            $custom_path = $uploads['basedir'] . $custom_dir;

            // Check if the custom directory exists, and if not, create it
            if (!file_exists($custom_path)) {
                wp_mkdir_p($custom_path);
            }

            // Set the custom folder for uploads
            $uploads['path'] = $custom_path;
            $uploads['url'] = $uploads['baseurl'] . $custom_dir;

            return $uploads;
        }
    }

    // Return default directory for other cases
    return $uploads;
}



// File upload name
function file_upload_name($file) {
    $file['name'] = "{$file['name']}";
    return $file;
}

// Clean up thumbnails for PDFs after upload
add_filter('wp_generate_attachment_metadata', 'cleanup_pdf_thumbnails', 10, 2);
function cleanup_pdf_thumbnails($metadata, $attachment_id) {
    $attachment = get_post($attachment_id);
    if ($attachment->post_mime_type === 'application/pdf') {
        $upload_dir = wp_upload_dir();
        foreach ($metadata['sizes'] as $size => $fileinfo) {
            $filepath = $upload_dir['path'] . '/' . $fileinfo['file'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        $metadata['sizes'] = []; // Reset sizes to prevent database bloat
    }
    return $metadata;
}