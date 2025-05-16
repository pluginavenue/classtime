<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Add color picker field to Class Type taxonomy
 */
add_action('classtime_type_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="classtime_type_color">Badge Color</label>
        <input type="color" name="classtime_type_color" id="classtime_type_color" value="#3478f6">
        <p class="description">Select a color for this class type badge.</p>
    </div>
    <input type="hidden" name="classtime_type_color_nonce" value="<?php echo esc_attr(wp_create_nonce('classtime_save_type_color')); ?>">
    <?php
});

add_action('classtime_type_edit_form_fields', function ($term) {
    $color = get_term_meta($term->term_id, 'classtime_type_color', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="classtime_type_color">Badge Color</label></th>
        <td>
            <input type="color" name="classtime_type_color" id="classtime_type_color" value="<?php echo esc_attr($color ?: '#3478f6'); ?>">
            <p class="description">Select a color for this class type badge.</p>
            <input type="hidden" name="classtime_type_color_nonce" value="<?php echo esc_attr(wp_create_nonce('classtime_save_type_color')); ?>">
        </td>
    </tr>
    <?php
}, 10, 1);

add_action('created_classtime_type', function ($term_id) {
    if (!isset($_POST['classtime_type_color_nonce'])) return;
    $nonce = sanitize_text_field(wp_unslash($_POST['classtime_type_color_nonce']));
    if (!wp_verify_nonce($nonce, 'classtime_save_type_color')) return;

    if (isset($_POST['classtime_type_color'])) {
        update_term_meta($term_id, 'classtime_type_color', sanitize_hex_color(wp_unslash($_POST['classtime_type_color'])));
    }
});

add_action('edited_classtime_type', function ($term_id) {
    if (!isset($_POST['classtime_type_color_nonce'])) return;
    $nonce = sanitize_text_field(wp_unslash($_POST['classtime_type_color_nonce']));
    if (!wp_verify_nonce($nonce, 'classtime_save_type_color')) return;

    if (isset($_POST['classtime_type_color'])) {
        update_term_meta($term_id, 'classtime_type_color', sanitize_hex_color(wp_unslash($_POST['classtime_type_color'])));
    }
});
