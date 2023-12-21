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
    error_log("display_category_with_posts called with category ID: " . $category_id);

    $output = '';
    $current_user_id = get_current_user_id();
    // Fetch allowed roles and user exclusions for the category
    $allowed_roles = get_term_meta($category_id, 'allowed_roles', true);
    $user_exclusions = get_term_meta($category_id, 'acd_user_exclusions', true);

    // Check if the current user is an admin, is excluded, or has an allowed role
    if (
        !current_user_can('administrator') &&
        !in_array($current_user_id, (array)$user_exclusions) &&
        !array_intersect(wp_get_current_user()->roles, (array)$allowed_roles)
    ) {
        return ''; // Don't display the category if user is not an admin, not excluded, and doesn't have an allowed role
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
    
    // Debugging
    error_log(print_r($subcategories, true));

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

                // Retrieve the file URL from the post meta 
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
                // Debugging
                error_log(print_r($subcategory, true));
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


function acd_add_role_selection_to_category($term)
{
    $roles = get_editable_roles(); // Get all WordPress roles
    $saved_roles = get_term_meta($term->term_id, 'allowed_roles', true); // Retrieve saved roles

    // Check if $term is an object. If not, it's a new term addition screen
    $term_id = is_object($term) ? $term->term_id : 0;

    // Output label and 'Select All / Deselect All' checkbox
    echo '<label>Hide for:</label><br>';
    echo '<input type="checkbox" id="select_all_roles" /> Select All / Deselect All<br>';

    // Output checkboxes for each role except 'administrator'
    foreach ($roles as $role_key => $role_info) {
        if ($role_key == 'administrator') continue; // Skip the admin role

        $is_checked = in_array($role_key, (array)$saved_roles) ? 'checked' : '';
        echo '<input type="checkbox" class="role_checkbox" name="allowed_roles[]" value="' . esc_attr($role_key) . '" ' . $is_checked . '> ' . esc_html($role_info['name']) . '<br>';
    }

    // Add JavaScript for 'Select All / Deselect All' functionality
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#select_all_roles').change(function() {
                var checked = $(this).is(':checked');
                $('.role_checkbox').prop('checked', checked);
            });
        });
    </script>
<?php
}


add_action('document_category_edit_form_fields', 'acd_add_role_selection_to_category');

function acd_save_allowed_roles($term_id)
{
    if (isset($_POST['allowed_roles'])) {
        update_term_meta($term_id, 'allowed_roles', $_POST['allowed_roles']);
    } else {
        // If no roles are selected, save an empty array
        update_term_meta($term_id, 'allowed_roles', array());
    }
}

add_action('created_document_category', 'acd_save_allowed_roles');
add_action('edited_document_category', 'acd_save_allowed_roles');
