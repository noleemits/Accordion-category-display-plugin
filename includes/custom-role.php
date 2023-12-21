<?php

function acd_add_custom_role() {
    add_role(
        'custom_document_viewer', // Role name (should be unique)
        'Custom Document Viewer', // Display name
        array(
            'read' => true, // Basic WordPress capability
            'view_specific_document_category' => true, // Custom capability
        )
    );
}

function acd_remove_custom_role() {
    remove_role('custom_document_viewer');
}



