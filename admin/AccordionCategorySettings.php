<?php

namespace AccordionCategoryDisplay;

class AccordionCategorySettings{
    public function init(){
        add_action('admin_menu', array($this, 'accordion_category_menu'));
        add_action('admin_init', array($this, 'accordion_category_settings_init'));
        add_action('admin_init', array($this, 'check_for_retroactive_application'));
        add_action('admin_notices', array($this, 'acd_show_retroactive_application_notice'));

    }
    
    public function accordion_category_menu()
    {
        add_options_page(
            'Accordion Category Settings',
            'Accordion Category',
            'manage_options',
            'accordion-category-settings',
            'accordion_category_settings_page'
        );
    }
    public function accordion_category_settings_page()
    {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
    
        // Render the settings template
    ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                // Output security fields for the registered setting "accordion_category_options"
                settings_fields('accordion_category_options');
                // Output setting sections and their fields
                do_settings_sections('accordion_category_settings');
                // Output save settings button
                submit_button('Save Settings');
    
                ?>
            </form>
        </div>
    <?php
        $options = get_option('accordion_category_options');
    }
    public function accordion_category_settings_init()
    {
    
    
        // Register a new section in the "accordion_category" page
        add_settings_section(
            'accordion_category_section_developers',
            __('File Permissions and Settings', 'my-plugin'),
            'accordion_category_section_developers_callback',
            'accordion_category_settings'
        );
    
        // Register a new field for max file size in the "accordion_category_section_developers" section
        add_settings_field(
            'accordion_category_field_max_file_size',
            __('Maximum File Size (MB)', 'my-plugin'),
            'accordion_category_field_max_file_size_render',
            'accordion_category_settings',
            'accordion_category_section_developers'
        );
    
    
        // Register a new setting for "accordion_category" page
        register_setting('accordion_category_options', 'accordion_category_options');
    
    
        // Add settings fields to the "accordion_category" page under the existing settings group
        add_settings_field(
            'accordion_category_default_roles_field', // Unique ID for the field
            __('Default Blocked Roles', 'accordion_category'), // Field title
            'accordion_category_default_roles_render', // Callback for rendering the field
            'accordion_category_settings', // Page on which to show the field
            'accordion_category_section_developers' // Section in which to show the field
        );
    
    
        // Register a new field in the "accordion_category_section_developers" section, inside the "accordion_category" page
        add_settings_field(
            'accordion_category_field_file_types', // As of WP 4.6 this value is used only internally
            __('Allowed File Types', 'my-plugin'),
            'accordion_category_field_file_types_render', // The function which renders the input field
            'accordion_category_settings',
            'accordion_category_section_developers'
        );
    }
    public function accordion_category_section_developers_callback()
    {
        echo '<p>' . __('Here you can select the file types you want to allow for upload, set the size and the global allowed role where the content will be visible', 'my-plugin') . '</p>';
    }
    public function accordion_category_field_file_types_render()
    {
        $options = get_option('accordion_category_options');
        // Define default file types
        $default_file_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpeg', 'png', 'webp'];
    
        // If options are not set, initialize them with all file types selected
        if (!$options) {
            $options = array_fill_keys($default_file_types, 1);
            // Save the initialized options
            update_option('accordion_category_options', $options);
        }
    
        // Render the checkboxes
        echo '<div class="accordion-category-options">';
        foreach ($default_file_types as $type) {
            $checked = isset($options[$type]) ? checked($options[$type], 1, false) : '';
            echo "<input type='checkbox' name='accordion_category_options[$type]' $checked value='1'>";
            echo strtoupper($type);
        }
        echo '</div>';
    }
    //Max file render
    public function accordion_category_field_max_file_size_render()
    {
        $options = get_option('accordion_category_options');
        $max_file_size = isset($options['max_file_size']) ? $options['max_file_size'] : '';
    ?>
        <input type='number' name='accordion_category_options[max_file_size]' value='<?php echo esc_attr($max_file_size); ?>' min='1'>
        <p class="description"><?php _e('Enter the maximum file size allowed for uploads in Megabytes (MB).', 'my-plugin'); ?></p>
    <?php
    }
    
    
    public function accordion_category_default_roles_render()
    {
        // Get the saved option value from the database
        $options = get_option('accordion_category_options');
        $roles = get_editable_roles();
    
        echo '<select multiple name="accordion_category_options[default_roles][]" id="accordion_category_default_roles_field" class="postform">';
        foreach ($roles as $role_key => $role_info) {
            $selected = (isset($options['default_roles']) && in_array($role_key, $options['default_roles'])) ? 'selected' : '';
            echo '<option value="' . esc_attr($role_key) . '" ' . $selected . '>' . esc_html($role_info['name']) . '</option>';
        }
        echo '</select>';
    }
    
    
    //Retroactive applications or default
    
    public function check_for_retroactive_application()
    {
        if (isset($_POST['apply_defaults_to_existing_categories'])) {
    
            // Set a transient to show a notice
            set_transient('acd_retroactive_application_notice', 'Defaults applied to existing categories.', 60);
    
            // Add a nonce check for security here
    
            $options = get_option('accordion_category_options');
            $default_roles = isset($options['default_roles']) ? $options['default_roles'] : [];
            $categories = get_terms(['taxonomy' => 'document_category', 'hide_empty' => false]);
    
            foreach ($categories as $category) {
                update_term_meta($category->term_id, 'allowed_roles', $default_roles);
            }
    
            // Add an admin notice or some form of confirmation message
        }
    }
    
    
    // Function to display the admin notice
    public   function acd_show_retroactive_application_notice()
    {
        if ($notice = get_transient('acd_retroactive_application_notice')) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($notice) . '</p></div>';
            delete_transient('acd_retroactive_application_notice');
        }
    }
}








