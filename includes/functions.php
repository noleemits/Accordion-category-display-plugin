<?php

function document_category_list_shortcode() {
    $output = '';

    // Fetch top-level categories
    $parent_categories = get_terms(array(
        'taxonomy' => 'document_category',
        'parent'   => 0,
    ));

    if (!empty($parent_categories) && !is_wp_error($parent_categories)) {
        $output .= '<ul class="document-category-list">';

        foreach ($parent_categories as $category) {
            // Pass true to indicate it's a parent category
            // The category title will be included inside the display_category_with_posts function
            $category_output = display_category_with_posts($category->term_id, true);
            if (!empty($category_output)) {
                $output .= '<li class="category-item">';
                $output .= $category_output; // This will include the category title with data-toggle
                $output .= '</li>';
            }
        }

        $output .= '</ul>';
    }

    return $output;
}
add_shortcode('document_category_list', 'document_category_list_shortcode');

function display_category_with_posts($category_id, $is_parent = false) {
    $output = '';
    $current_user_id = get_current_user_id();

    // WP_Query for fetching posts
    $args = array(
        'post_type' => 'document',
      //  'author'    => $current_user_id, // Current user's posts
        'post_status' => array('publish', 'private'), // Include both published and private posts
        'tax_query' => array(
            array(
                'taxonomy' => 'document_category',
                'field'    => 'term_id',
                'terms'    => $category_id,
                'include_children' => false,
            ),
        ),
        'posts_per_page' => -1
    );

    $query = new WP_Query($args);

    // Fetch subcategories
    $subcategories = get_terms(array(
        'taxonomy' => 'document_category',
        'parent'   => $category_id
    ));

    // Check if there are posts or subcategories to display
    if ($query->have_posts() || !empty($subcategories)) {
        // Output category title with data-toggle only if it's a parent category
        if ($is_parent) {
            $output .= '<div class="category-title" data-toggle="accordion">';
            $output .= '<span class="toggle-icon"><span>+</span></span>'; // Plus icon by default
            $output .= '<img src="' . plugin_dir_url(dirname(__FILE__)) . 'public/img/Folder-icon.png" alt="Folder icon">';
            $output .= '<h3>' . get_term($category_id)->name . '</h3></div>';
        }

        // Output posts
        if ($query->have_posts()) {
            $output .= '<ul class="post-list">';
            while ($query->have_posts()) {
                $query->the_post();
                $file_url = get_field('file_upload', get_the_ID());
                $file_link = is_string($file_url) ? '<a href="' . esc_url($file_url) . '" download><img src="' . plugin_dir_url(dirname(__FILE__)) . 'public/img/PDF-icon.png"></a>' : '';
                $output .= '<li class="post-item">' . esc_html(get_the_title()) . ' ' . $file_link . '</li>';
            }
            $output .= '</ul>';
            wp_reset_postdata(); // Reset the global post object
        }

        // Output subcategories
        if (!empty($subcategories)) {
            $output .= '<ul class="subcategory-list">';
            foreach ($subcategories as $subcategory) {
                // Recursively call the function for subcategories
                $sub_output = display_category_with_posts($subcategory->term_id);
                if (!empty($sub_output)) {
                    $output .= '<li class="subcategory-item">';
                    $output .= '<div class="subcategory-title" data-toggle="accordion"><span class="toggle-icon"><span>+</span></span><img src="' . plugin_dir_url(dirname(__FILE__)) . 'public/img/Folder-icon.png"><h4>' . $subcategory->name . '</h4></div>';
                    $output .= $sub_output; // This will include the subcategory posts and nested subcategories
                    $output .= '</li>';
                }
            }
            $output .= '</ul>';
        }
    }

    return $output;
}

//Change default directory for the file upload
// Target 'file_upload' field for custom upload directory and filename
add_filter('acf/upload_prefilter/name=file_upload', 'my_acf_upload_prefilter');
function my_acf_upload_prefilter($errors) {
    // File upload folder
    add_filter('upload_dir', 'file_upload_folder', 20);
    
    // File upload name
    add_filter('wp_handle_upload_prefilter', 'file_upload_name', 20);

    return $errors;
}

// File upload folder - it creates it it doesn't exist
function file_upload_folder($uploads) {
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

