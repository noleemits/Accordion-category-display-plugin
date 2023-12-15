<?php

//Shortcode generator
function document_category_list_shortcode()
{
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

//Display category recursively

function display_category_with_posts($category_id, $is_parent = false)
{
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
