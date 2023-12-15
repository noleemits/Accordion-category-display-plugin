<?php

// Standalone function to recursively get allowed users from parent categories
function get_allowed_users_from_parents($term_id)
{
    $allowed_users = get_field('user_field', 'document_category_' . $term_id);
    if (!$allowed_users) {
        $parent_id = get_term($term_id)->parent;
        if ($parent_id != 0) {
            return get_allowed_users_from_parents($parent_id);
        }
    }
    return $allowed_users;
}

// File upload folder - it creates it it doesn't exist
function file_upload_folder($uploads)
{
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

// File upload name
function file_upload_name($file)
{
    $file['name'] = "{$file['name']}";
    return $file;
}

// Clean up thumbnails for PDFs after upload
add_filter('wp_generate_attachment_metadata', 'cleanup_pdf_thumbnails', 10, 2);
function cleanup_pdf_thumbnails($metadata, $attachment_id)
{
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
