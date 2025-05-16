<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/* ===============================
   Instructor Meta Box - Free Version
================================== */

// ✅ Register the meta box
add_action('add_meta_boxes', function () {
    add_meta_box(
        'classtime_instructor_meta',
        'Instructor Details',
        'classtime_render_instructor_meta_box',
        'classtime_instructor',
        'normal',
        'default'
    );
});

// ✅ Save the meta box fields
add_action('save_post_classtime_instructor', function ($post_id) {
    // Security checks
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Extract, unslash, and sanitize nonce
    $nonce = '';
    $raw_post = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);
    if (isset($raw_post['classtime_instructor_meta_nonce'])) {
        $nonce = sanitize_text_field(wp_unslash($raw_post['classtime_instructor_meta_nonce']));
    }

    if (empty($nonce) || !wp_verify_nonce($nonce, 'classtime_save_instructor_meta')) return;

    // Save certification
    if (isset($_POST['classtime_instructor_certification'])) {
        update_post_meta(
            $post_id,
            'classtime_instructor_certification',
            sanitize_text_field(wp_unslash($_POST['classtime_instructor_certification']))
        );
    }
});
