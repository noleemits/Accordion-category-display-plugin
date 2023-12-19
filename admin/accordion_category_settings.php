<?php

function accordion_category_menu() {
    add_options_page(
        'Accordion Category Settings',
        'Accordion Category',
        'manage_options',
        'accordion-category-settings',
        'accordion_category_settings_page'
    );
}
add_action('admin_menu', 'accordion_category_menu');

function accordion_category_settings_page() {
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
    error_log("Saved options: " . print_r($options, true));
    
}

function accordion_category_settings_init() {
    // Register a new setting for "accordion_category" page
    register_setting('accordion_category_options', 'accordion_category_options');

    // Register a new section in the "accordion_category" page
    add_settings_section(
        'accordion_category_section_developers',
        __('File Type Settings', 'my-plugin'),
        'accordion_category_section_developers_callback',
        'accordion_category_settings'
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
add_action('admin_init', 'accordion_category_settings_init');

function accordion_category_section_developers_callback() {
    echo '<p>' . __('Select the file types you want to allow for upload.', 'my-plugin') . '</p>';
}

function accordion_category_field_file_types_render() {
    $options = get_option('accordion_category_options');
    $file_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpeg', 'png', 'webp']; ?>
    <div class="accordion-category-options">
    <?php    
    foreach ($file_types as $type) {
        ?>
        <input type='checkbox' name='accordion_category_options[<?php echo $type; ?>]' <?php checked(isset($options[$type]), true); ?> value='1'>
        <?php echo strtoupper($type); ?>
        <?php
    } ?>
</div>
<?php
}
