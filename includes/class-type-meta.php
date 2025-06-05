<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Add color picker field to Class Type taxonomy (Add form)
 */
add_action('classtime_type_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="classtime_type_color">Badge Color</label>
        <input type="color" name="classtime_type_color" id="classtime_type_color" value="#3478f6">
        <p class="description">Select a color for this class type badge.</p>
    </div>
    <?php
});

/**
 * Add color picker field to Class Type taxonomy (Edit form)
 */
add_action('classtime_type_edit_form_fields', function ($term) {
    $color = get_term_meta($term->term_id, 'classtime_type_color', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="classtime_type_color">Badge Color</label></th>
        <td>
            <input type="color" name="classtime_type_color" id="classtime_type_color" value="<?php echo esc_attr($color ?: '#3478f6'); ?>">
            <p class="description">Select a color for this class type badge.</p>
        </td>
    </tr>
    <?php
}, 10, 1);

/**
 * Save badge color on term creation
 */
add_action('created_classtime_type', function ($term_id) {
    if (!empty($_POST) && isset($_POST['_wpnonce'], $_POST['classtime_type_color'])) {
        $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
        if (wp_verify_nonce($nonce, 'add-tag')) {
            $color = sanitize_hex_color(wp_unslash($_POST['classtime_type_color']));
            update_term_meta($term_id, 'classtime_type_color', $color);
        }
    }
});

/**
 * Save badge color on term edit
 */
add_action('edited_classtime_type', function ($term_id) {
    if (!empty($_POST) && isset($_POST['_wpnonce'], $_POST['classtime_type_color'])) {
        $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
        if (wp_verify_nonce($nonce, 'update-tag')) {
            $color = sanitize_hex_color(wp_unslash($_POST['classtime_type_color']));
            update_term_meta($term_id, 'classtime_type_color', $color);
        }
    }
});
