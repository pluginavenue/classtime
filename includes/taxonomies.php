<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// === Register Custom Taxonomies for ClassTime ===

function classtime_register_taxonomies() {

    // === Class Type Taxonomy ===
    $class_type_labels = [
        'name'              => 'Class Types',
        'singular_name'     => 'Class Type',
        'menu_name'         => 'Class Types',
        'all_items'         => 'All Class Types',
        'edit_item'         => 'Edit Class Type',
        'view_item'         => 'View Class Type',
        'update_item'       => 'Update Class Type',
        'add_new_item'      => 'Add New Class Type',
        'new_item_name'     => 'New Class Type',
        'search_items'      => 'Search Class Types',
    ];

    $class_type_args = [
        'labels'            => $class_type_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'hierarchical'      => false,
        'show_in_rest'      => true,
        'show_in_menu'      => false, // 👈 Hide default taxonomy menu
    ];

    register_taxonomy('classtime_type', ['classtime_class'], $class_type_args);

    // === Class Level Taxonomy ===
    $class_level_labels = [
        'name'              => 'Class Levels',
        'singular_name'     => 'Class Level',
        'menu_name'         => 'Class Levels',
        'all_items'         => 'All Class Levels',
        'edit_item'         => 'Edit Class Level',
        'view_item'         => 'View Class Level',
        'update_item'       => 'Update Class Level',
        'add_new_item'      => 'Add New Class Level',
        'new_item_name'     => 'New Class Level',
        'search_items'      => 'Search Class Levels',
    ];

    $class_level_args = [
        'labels'            => $class_level_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'hierarchical'      => false,
        'show_in_rest'      => true,
        'show_in_menu'      => false, // 👈 Hide default taxonomy menu
    ];

    register_taxonomy('classtime_level', ['classtime_class'], $class_level_args);
}
add_action('init', 'classtime_register_taxonomies');
