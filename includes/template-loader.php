<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// === Load custom templates for Instructors ===
add_filter('template_include', function($template) {
    if (is_singular('classtime_instructor')) {
        $plugin_template = CLASSTIME_PATH . 'templates/single-classtime_instructor.php';
        if (file_exists($plugin_template)) return $plugin_template;
    }

    if (is_post_type_archive('classtime_instructor')) {
        $plugin_template = CLASSTIME_PATH . 'templates/archive-classtime_instructor.php';
        if (file_exists($plugin_template)) return $plugin_template;
    }

    return $template;
});
