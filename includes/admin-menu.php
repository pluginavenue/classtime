<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// === ClassTime Admin Menu ===

add_action('admin_menu', function () {
    // Top Level Menu: ClassTime
    add_menu_page(
        'ClassTime',
        'ClassTime',
        'manage_options',
        'edit.php?post_type=classtime_class',
        '', // ✅ No callback needed
        'dashicons-calendar-alt',
        5 // Position
    );

    add_submenu_page(
        'edit.php?post_type=classtime_class', // Parent menu
        'All Classes',                        // Page title
        'All Classes',                        // Submenu label
        'manage_options',
        'edit.php?post_type=classtime_class'  // Link back to CPT list
    );

    // Submenus under ClassTime
    add_submenu_page(
        'edit.php?post_type=classtime_class',
        'Class Types',
        'Class Types',
        'manage_options',
        'edit-tags.php?taxonomy=classtime_type&post_type=classtime_class'
    );

    add_submenu_page(
        'edit.php?post_type=classtime_class',
        'Class Levels',
        'Class Levels',
        'manage_options',
        'edit-tags.php?taxonomy=classtime_level&post_type=classtime_class'
    );

    add_submenu_page(
        'edit.php?post_type=classtime_class',
        'All Instructors',
        'All Instructors',
        'manage_options',
        'edit.php?post_type=classtime_instructor'
    );

    add_submenu_page(
        'edit.php?post_type=classtime_class',
        'ClassTime Help',
        'Help',
        'manage_options',
        'classtime-help',
        'classtime_render_help_page'
    );

    // ✅ Only show upgrade link if Pro is NOT active
    if (!function_exists('classtime_pro_enabled') || !classtime_pro_enabled()) {
        add_submenu_page(
            'edit.php?post_type=classtime_class',
            'Upgrade to ClassTime Pro',
            '<span style="color: #d63638;">Upgrade to Pro 🚀</span>',
            'manage_options',
            'classtime-upgrade',
            function () {
                echo '<div class="wrap"><h1>Upgrade to ClassTime Pro</h1>';
                echo '<p><a class="button button-primary" href="https://pluginavenue.com/plugins/classtime" target="_blank">Learn More</a></p>';
                echo '</div>';
            }
        );
    }
});
