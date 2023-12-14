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


// Standalone function to recursively get allowed users from parent categories
function get_allowed_users_from_parents($term_id) {
    $allowed_users = get_field('user_field', 'document_category_' . $term_id);
    if (!$allowed_users) {
        $parent_id = get_term($term_id)->parent;
        if ($parent_id != 0) {
            return get_allowed_users_from_parents($parent_id);
        }
    }
    return $allowed_users;
}


function display_category_with_posts($category_id, $is_parent = false) {
    $output = '';
    $current_user_id = get_current_user_id();

    // Fetch allowed users from ACF field
    $allowed_users = get_allowed_users_from_parents($category_id);

    // Ensure $allowed_users is always an array of user IDs
    if (isset($allowed_users['ID'])) {
        $allowed_user_ids = array($allowed_users['ID']); // Single user
    } else {
        $allowed_user_ids = array_column((array)$allowed_users, 'ID'); // Multiple users
    }

    // Check if the current user is an administrator or allowed user
    if (!current_user_can('administrator') && !in_array($current_user_id, $allowed_user_ids)) {
        return ''; // Return empty string if user is not allowed
    }

    // WP_Query for fetching posts
    $args = array(
        'post_type' => 'document',
        'post_status' => array('publish', 'private'),
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
            $output .= '<img class="folder-icon"  src="' . plugin_dir_url(dirname(__FILE__)) . 'public/img/icon-closed-folder.png" alt="Folder icon">';
            $output .= '<h3>' . get_term($category_id)->name . '</h3></div>';
        }

        // Output posts
        if ($query->have_posts()) {
            $output .= '<ul class="post-list">';
            while ($query->have_posts()) {
                $query->the_post();
                
                // Retrieve the file URL from the post meta instead of the ACF field
                $file_url = get_post_meta(get_the_ID(), 'my_custom_document_file', true);
                
                // Generate the file link only if a URL is saved in the post meta
                $file_link = '';
                if (!empty($file_url)) {
                    // Here you can add additional logic to determine the icon based on file type if needed
                    $icon_url = plugin_dir_url(dirname(__FILE__)) . 'public/img/PDF-icon.png'; // Update path if necessary
                    $file_link = '<a href="' . esc_url($file_url) . '" download><img src="' . esc_url($icon_url) . '"></a>';
                }
                
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
                    $output .= '<div class="subcategory-title" data-toggle="accordion"><span class="toggle-icon"><span>+</span></span><img class="folder-icon" src="' . plugin_dir_url(dirname(__FILE__)) . 'public/img/icon-closed-folder.png"><h4>' . $subcategory->name . '</h4></div>';
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
// add_filter('acf/upload_prefilter/name=file_upload', 'my_acf_upload_prefilter');
// function my_acf_upload_prefilter($errors) {
//     // File upload folder
//     add_filter('upload_dir', 'file_upload_folder', 20);
    
//     // File upload name
//     add_filter('wp_handle_upload_prefilter', 'file_upload_name', 20);

//     return $errors;
// }

// // File upload folder - it creates it it doesn't exist
// function file_upload_folder($uploads) {
//     // Define the custom folder path
//     $custom_dir = '/customer_files';

//     // Full path to the custom directory
//     $custom_path = $uploads['basedir'] . $custom_dir;

//     // Check if the custom directory exists, and if not, create it
//     if (!file_exists($custom_path)) {
//         wp_mkdir_p($custom_path);
//     }

//     // Set the custom folder for uploads
//     $uploads['path'] = $custom_path;
//     $uploads['url'] = $uploads['baseurl'] . $custom_dir;
//     return $uploads;
// }


// // File upload name
// function file_upload_name($file) {
//     $file['name'] = "{$file['name']}";
//     return $file;
// }

// Clean up thumbnails for PDFs after upload
// add_filter('wp_generate_attachment_metadata', 'cleanup_pdf_thumbnails', 10, 2);
// function cleanup_pdf_thumbnails($metadata, $attachment_id) {
//     $attachment = get_post($attachment_id);
//     if ($attachment->post_mime_type === 'application/pdf') {
//         $upload_dir = wp_upload_dir();
//         foreach ($metadata['sizes'] as $size => $fileinfo) {
//             $filepath = $upload_dir['path'] . '/' . $fileinfo['file'];
//             if (file_exists($filepath)) {
//                 unlink($filepath);
//             }
//         }
//         $metadata['sizes'] = []; // Reset sizes to prevent database bloat
//     }
//     return $metadata;
// }

//handle media uploads

function my_admin_enqueue_scripts() {
    global $typenow;
    if ($typenow == 'document') { 
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'my_admin_enqueue_scripts');


// Add meta box for file uploads
function my_custom_file_upload_meta_box() {
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
function my_custom_file_upload_callback($post) {
    wp_nonce_field('my_custom_file_upload', 'my_custom_file_upload_nonce');
    
    // Button that triggers the media uploader
    echo '<button type="button" class="button" id="my_media_manager">Upload or Select Document</button>';
    // Hidden input to store the file URL
    echo '<input type="hidden" id="my_custom_document" name="my_custom_document" value="" />';
}

// Save the file when the post is saved
function my_save_custom_file($post_id) {
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
    if (!empty($_FILES['my_custom_document']['name'])) {
        // The wp_handle_upload function will take care of the rest.
        $file = wp_handle_upload($_FILES['my_custom_document'], ['test_form' => false]);
        if (isset($file['url'])) {
            update_post_meta($post_id, 'my_custom_document_file', $file['url']);
        }
    }
}
add_action('save_post', 'my_save_custom_file');

// Make sure the file array isn't empty
function update_edit_form() {
    echo ' enctype="multipart/form-data"';
}
add_action('post_edit_form_tag', 'update_edit_form');

function my_enqueue_media_uploader() {
    global $typenow;
    if ($typenow == 'document') {
        // Your enqueue media script
        wp_enqueue_media();
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            $('#my_media_manager').click(function(e) {
                e.preventDefault();
                var image_frame;
                if(image_frame){
                    image_frame.open();
                }
                // Define image_frame as wp.media object
                image_frame = wp.media({
                    title: 'Select Media',
                    multiple : false,
                    library : {
                        type : 'image,application/pdf' // Modify to accept the types you want
                    }
                });

                image_frame.on('close',function() {
                    // On close, get selections and save to the hidden input
                    var selection =  image_frame.state().get('selection').first().toJSON();
                    $('#my_custom_document').val(selection.url);
                });

                image_frame.on('select',function() {
                    // On select, get selections and save to the hidden input
                    var selection =  image_frame.state().get('selection').first().toJSON();
                    $('#my_custom_document').val(selection.url);
                });

                image_frame.open();
            });
        });
        </script>
        <?php
    }
}
add_action('admin_footer', 'my_enqueue_media_uploader'); // admin_footer is just one of the hooks you could use
