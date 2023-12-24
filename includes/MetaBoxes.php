<?php

namespace AccordionCategoryDisplay;


class MetaBoxes
{
    /*Initialize metaboxes*/
    public function init()
    {
        add_action('add_meta_boxes', array($this, 'my_custom_file_upload_meta_box'));
        add_action('save_post', array($this, 'my_save_custom_file'));
        add_action('post_edit_form_tag', array($this, 'update_edit_form'));
        add_action('document_category_edit_form_fields', array($this, 'custom_user_field_edit_term'));
        add_action('document_category_add_form_fields', array($this, 'custom_user_field_edit_term'));
        add_action('created_document_category', array($this, 'acd_save_user_exclusions'), 10, 2);
        add_action('edited_document_category', array($this, 'acd_save_user_exclusions'), 10, 2);
        add_action('created_document_category', array($this, 'apply_default_role_blocks_to_new_category'), 10, 3);
        add_action('admin_notices', array($this, 'accordion_category_settings_saved_notice'));
    }

    // Add meta box for file uploads
    public function my_custom_file_upload_meta_box()
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
    public function my_custom_file_upload_callback($post)
    {

        wp_nonce_field('my_custom_file_upload', 'my_custom_file_upload_nonce');

        // Retrieve the current file URL if it exists
        $current_file = get_post_meta($post->ID, 'my_custom_document_file', true);

        // Button that triggers the media uploader
        echo '<button type="button" class="button" id="my_media_manager">Upload or Select Document</button>';

        // Display the name of the current file if there is one
        if (!empty($current_file)) {
            // Get just the file name from the URL
            $file_name = basename($current_file);
            echo '<div id="current_file"><br /><strong>Current File:</strong> ' . esc_html($file_name) . '</div>';
        }

        // Hidden input to store the file URL
        echo '<input type="hidden" id="my_custom_document" name="my_custom_document" value="' . esc_attr($current_file) . '" />';
    }
    // Save the file when the post is saved
    public function my_save_custom_file($post_id)
    {
        error_log("Function start: my_save_custom_file");

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

            // Get file extension
            $file_extension = strtolower(pathinfo(parse_url($file_url, PHP_URL_PATH), PATHINFO_EXTENSION));
            // Retrieve the option and ensure it's an array before filtering
            $options = get_option('accordion_category_options');
            if (!$options || !is_array($options)) {
                $options = array(); // Initialize as an empty array if not set
            }
            $allowed_file_types = array_keys(array_filter($options));
            $allowed_extensions = array_map(function ($type) {
                return $type;
            }, $allowed_file_types);

            // Check if the file extension is allowed
            if (!in_array($file_extension, $allowed_extensions)) {
                error_log("Disallowed file type: " . $file_extension);
                // Output an admin notice error and stop the function
                add_action('admin_notices', function () use ($file_extension) {
                    echo "<div class='notice notice-error'><p>File type .{$file_extension} is not allowed.</p></div>";
                });
                return;
            }

            // Save the file URL
            update_post_meta($post_id, 'my_custom_document_file', $file_url);
        }
    }
    // Make sure the file array isn't empty
    public function update_edit_form()
    {
        echo ' enctype="multipart/form-data"';
    }
    //User selection metabox
    public function custom_user_field_edit_term($term)
    {
        // Check if $term is a string, which means it's the 'Add New Term' screen
        if (is_string($term)) {
            // Display a basic form for new terms or a message indicating that user exclusion can be set after creating the term.
            echo '<tr class="form-field"><td>Set user exclusions after adding the category.</td></tr>';
            return;
        }
    
        // If $term is an object, it's an existing term being edited
        $selected_users = get_term_meta($term->term_id, 'acd_user_exclusions', true);
    
        // Get all users
        $users = get_users();
    
        echo '<tr class="form-field">';
        echo '<th scope="row"><label for="acd_user_exclusions">Exclude Users</label></th>';
        echo '<td>';
        echo '<select name="acd_user_exclusions[]" id="acd_user_exclusions" class="postform" multiple>';
        echo '<option value="">Select users</option>';
    
        foreach ($users as $user) {
            $selected = in_array($user->ID, (array)$selected_users) ? ' selected' : '';
            echo '<option value="' . esc_attr($user->ID) . '"' . $selected . '>' . esc_html($user->display_name) . '</option>';
        }
    
        echo '</select>';
        echo '</td></tr>';
    }
    public function acd_save_user_exclusions($term_id)
    {
        // Save the user exclusions if provided, otherwise save as an empty array
        if (!empty($_POST['acd_user_exclusions'])) {
            // Ensure the input is what you expect, sanitize it accordingly
            $user_exclusions = array_map('intval', $_POST['acd_user_exclusions']);
            update_term_meta($term_id, 'acd_user_exclusions', $user_exclusions);
        } else {
            delete_term_meta($term_id, 'acd_user_exclusions');
        }
    }
    //Apply default roles to categories
    public function apply_defaults_to_new_category($term_id, $tt_id, $taxonomy)
    {
        if ($taxonomy !== 'document_category') {
            return;
        }
    
        $options = get_option('accordion_category_options');
        $default_roles = isset($options['default_roles']) ? $options['default_roles'] : [];
    
        update_term_meta($term_id, 'allowed_roles', $default_roles);
    }
    //Retrieve and apply default roles
    public function apply_default_role_blocks_to_new_category($term_id, $tt_id, $taxonomy_data)
    {
        // Extract taxonomy name from the array if needed
        $taxonomy = is_array($taxonomy_data) && isset($taxonomy_data['taxonomy']) ? $taxonomy_data['taxonomy'] : $taxonomy_data;
    
        if ($taxonomy !== 'document_category') {
            return;
        }
    
        $options = get_option('accordion_category_options');
        $default_roles = isset($options['default_roles']) ? $options['default_roles'] : [];
    
        update_term_meta($term_id, 'allowed_roles', $default_roles);
    }
    //Save notice in settings page
    public function accordion_category_settings_saved_notice()
    {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            $options = get_option('accordion_category_options');
            if (!empty($options['default_roles'])) {
                echo '<div class="notice notice-warning is-dismissible"><p>Warning: Default roles have been set. This will apply to all new categories.</p></div>';
            }
        }
    }
}

