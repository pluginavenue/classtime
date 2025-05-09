<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// === Instructors CPT ===

/**
 * Register the 'Instructor' Custom Post Type
 */
function classtime_register_instructor_post_type() {
    $labels = [
        'name'                  => 'Instructors',
        'singular_name'         => 'Instructor',
        'menu_name'             => 'Instructors',
        'name_admin_bar'        => 'Instructor',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Instructor',
        'new_item'              => 'New Instructor',
        'edit_item'             => 'Edit Instructor',
        'view_item'             => 'View Instructor',
        'all_items'             => 'All Instructors',
        'search_items'          => 'Search Instructors',
        'parent_item_colon'     => 'Parent Instructors:',
        'not_found'             => 'No instructors found.',
        'not_found_in_trash'    => 'No instructors found in Trash.',
    ];

    $args = [
        'labels'                => $labels,
        'public'                => true,
        'has_archive'           => true,
        'rewrite'               => [
            'slug'       => 'instructors',
            'with_front' => false,
        ],
        'supports'              => ['title', 'thumbnail'],
        'show_in_rest'          => true,
        'show_in_menu'          => false, // ✅ keeps it grouped under ClassTime
    ];

    register_post_type('classtime_instructor', $args);
}
add_action('init', 'classtime_register_instructor_post_type');
