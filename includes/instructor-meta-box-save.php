<?php
if (!defined('ABSPATH')) exit;

function classtime_save_instructor_details($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['classtime_instructor_nonce']) || !wp_verify_nonce($_POST['classtime_instructor_nonce'], 'classtime_instructor_nonce_action')) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['classtime_instructor_certification '])) {
        update_post_meta($post_id, 'classtime_instructor_certification ', sanitize_text_field($_POST['classtime_instructor_certification']));
    }
    


    if (isset($_POST['classtime_instructor_image'])) {
        update_post_meta($post_id, 'classtime_instructor_image', intval($_POST['classtime_instructor_image']));
    }
}
add_action('save_post', 'classtime_save_instructor_details');
