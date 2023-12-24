<?php

namespace AccordionCategoryDisplay;

class PostTypes{
    public function init(){
        add_action('init', array($this, 'create_document_post_type'));
        add_action('init', array($this, 'create_document_category_taxonomy'));
    }
    // Register Custom Post Type Document
    public function create_document_post_type()
    {
        $args = array(
            'labels' => array(
                'name' => 'Documents',
                'singular_name' => 'Document'
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-media-document',
        );
        register_post_type('document', $args);
    }
    // Register Custom Taxonomy
    function create_document_category_taxonomy()
    {
        $args = array(
            'labels' => array(
                'name' => 'Folder',
                'singular_name' => 'Folders'
            ),
            'public' => true,
            'hierarchical' => true,
        );
        register_taxonomy('document_category', array('document'), $args);
    }
}



