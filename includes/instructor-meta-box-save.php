<?php
if (!defined('ABSPATH')) exit;

function classtime_save_instructor_details($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (!isset($_POST['classtime_instructor_nonce'])) return;

    $nonce = sanitize_text_field(wp_unslash($_POST['classtime_instructor_nonce']));
    if (!wp_verify_nonce($nonce, 'classtime_instructor_nonce_action')) return;

    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['classtime_instructor_certification'])) {
        $certification = sanitize_text_field(wp_unslash($_POST['classtime_instructor_certification']));
        update_post_meta($post_id, 'classtime_instructor_certification', $certification);
    }

    if (isset($_POST['classtime_instructor_image'])) {
        $image_id = intval(wp_unslash($_POST['classtime_instructor_image']));
        update_post_meta($post_id, 'classtime_instructor_image', $image_id);
    }
}
add_action('save_post', 'classtime_save_instructor_details');
