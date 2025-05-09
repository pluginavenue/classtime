<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// === Classes CPT ===

/**
 * Register the 'ClassTime' Class Post Type
 */
function classtime_register_class_post_type() {
    $labels = [
        'name'                  => 'Classes',
        'singular_name'         => 'Class',
        'add_new'               => 'Add New',
        'edit_item'             => 'Edit Class',
        'new_item'              => 'New Class',
        'view_item'             => 'View Class',
        'search_items'          => 'Search Classes',
        'not_found'             => 'No classes found',
        'not_found_in_trash'    => 'No classes found in Trash',
        'all_items'             => 'All Classes',
        'menu_name'             => 'ClassTime',
        'name_admin_bar'        => 'Class',
    ];

    $args = [
        'labels'                => $labels,
        'public'                => true,
        'has_archive'           => true,
        'rewrite'               => [
            'slug'       => 'classes',
            'with_front' => false,
        ],
        'supports'              => ['title', 'editor'],
        'menu_icon'             => 'dashicons-calendar-alt',
        'show_in_rest'          => true,
        'show_in_menu'          => false, // 👈 Hides from auto menu
    ];

    register_post_type('classtime_class', $args);
}
add_action('init', 'classtime_register_class_post_type');
