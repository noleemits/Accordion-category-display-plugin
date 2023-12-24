<?php

namespace AccordionCategoryDisplay;

class CustomRoleManager {
    /**
     * Initializes the custom role manager.
     */
    public function init() {
        register_activation_hook(__FILE__, array($this, 'acd_add_custom_role'));
        register_deactivation_hook(__FILE__, array($this, 'acd_remove_custom_role'));
    }

    /**
     * Adds a custom role for document viewers.
     */
    public function acd_add_custom_role() {
        add_role(
            'custom_document_viewer', // Role name (should be unique)
            'Custom Document Viewer', // Display name
            array(
                'read' => true, // Basic WordPress capability
                'view_specific_document_category' => true, // Custom capability
            )
        );
    }

    /**
     * Removes the custom document viewer role.
     */
    public function acd_remove_custom_role() {
        remove_role('custom_document_viewer');
    }
}



