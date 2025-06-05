<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/* ===============================
   Instructor Meta Box - Free Version
================================== */

// ✅ Register the meta box
// add_action('add_meta_boxes', function() {
//     add_meta_box(
//         'classtime_instructor_meta',            // ID
//         'Instructor Details',                   // Title shown in editor
//         'classtime_render_instructor_meta_box', // Callback function
//         'classtime_instructor',                 // Post Type
//         'normal',
//         'default'
//     );
// });


// ✅ Save the meta box fields
add_action('save_post_classtime_instructor', function($post_id) {
    // ✅ Security checks
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // ✅ Check and verify nonce
   if (
        isset($_POST['classtime_instructor_nonce']) &&
        wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['classtime_instructor_nonce'])),
            'save_classtime_instructor'
        )
    ) {
        if (isset($_POST['classtime_instructor_certification'])) {
            update_post_meta(
                $post_id,
                'classtime_instructor_certification',
                sanitize_text_field(wp_unslash($_POST['classtime_instructor_certification']))
            );
        }
    }
});
?>
