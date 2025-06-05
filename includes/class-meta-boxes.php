<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Add custom meta boxes to ClassTime Class post type
 */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'classtime_details',
        'Class Details',
        'classtime_render_meta_box',
        'classtime_class',
        'normal',
        'high'
    );
});

function classtime_render_meta_box($post) {
    $start_time   = get_post_meta($post->ID, '_classtime_start', true);
    $end_time     = get_post_meta($post->ID, '_classtime_end', true);
    $instructors  = get_post_meta($post->ID, '_classtime_instructors', true);
    $notes        = get_post_meta($post->ID, '_classtime_notes', true);
    $recurrence   = get_post_meta($post->ID, '_classtime_recurrence', true);
    $day          = get_post_meta($post->ID, '_classtime_day', true);
    $date         = get_post_meta($post->ID, '_classtime_date', true);
    $repeat_until = get_post_meta($post->ID, '_classtime_repeat_until', true);
    $instructors = get_post_meta($id, '_classtime_instructors', true);
    if (!is_array($instructors)) {
        $instructors = [];
    }

    wp_nonce_field('classtime_save_meta', 'classtime_meta_nonce');

    // === Instructor Dropdown ===
    $instructor_posts = get_posts([
        'post_type' => 'classtime_instructor',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    $selected_instructors = get_post_meta(get_the_ID(), '_classtime_instructors', true);
    if (!is_array($selected_instructors)) $selected_instructors = [];
    ?>
    <p>
        <label>Instructor(s):<br>
        <small>Hold <kbd>Ctrl</kbd> (Windows) or <kbd>Cmd</kbd> (Mac) to select multiple instructors</small><br>
            <select name="classtime_instructors[]" multiple style="width: 100%; height: auto;">
                <?php foreach ($instructor_posts as $instructor): ?>
                    <option value="<?php echo esc_attr($instructor->ID); ?>" <?php selected(in_array($instructor->ID, $selected_instructors)); ?>>
                        <?php echo esc_html($instructor->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </p>
    
    
    <?php
        $class_type = wp_get_post_terms($post->ID, 'classtime_type', ['fields' => 'ids']);
        $class_level = wp_get_post_terms($post->ID, 'classtime_level', ['fields' => 'ids']);
    ?>

<p>
    <label>Class Type:<br>
    <select name="classtime_type" style="width: 100%;">
        <option value="">-- Select --</option>
        <?php foreach (get_terms(['taxonomy' => 'classtime_type', 'hide_empty' => false]) as $term): ?>
            <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected(in_array($term->term_id, $class_type)); ?>>
                <?php echo esc_html($term->name); ?>
            </option>
        <?php endforeach; ?>
    </select>
</label>
</p>

<p>
    <label>Class Level:<br>
        <select name="classtime_level" style="width: 100%;">
            <option value="">-- Select --</option>
            <?php foreach (get_terms(['taxonomy' => 'classtime_level', 'hide_empty' => false]) as $term): ?>
                <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected(in_array($term->term_id, $class_level)); ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
</p>


    <p>
        <label>Start Time:<br>
            <input type="time" name="classtime_start" value="<?php echo esc_attr($start_time); ?>">
        </label>
    </p>

    <p>
        <label>End Time:<br>
            <input type="time" name="classtime_end" value="<?php echo esc_attr($end_time); ?>">
        </label>
    </p>

    <p>
        <label>Notes:<br>
            <textarea name="classtime_notes" rows="4" style="width: 100%;"><?php echo esc_textarea($notes); ?></textarea>
        </label>
    </p>

    <p>
        <label>Recurrence:<br>
            <select name="classtime_recurrence">
                <option value="">None (One-time)</option>
                <option value="daily" <?php selected($recurrence, 'daily'); ?>>Daily</option>
                <option value="weekly" <?php selected($recurrence, 'weekly'); ?>>Weekly</option>
            </select>
        </label>
    </p>

    <p>
        <label>Day of the Week (for Weekly Recurrence):<br>
        <small>Hold <kbd>Ctrl</kbd> (Windows) or <kbd>Cmd</kbd> (Mac) to select multiple days</small><br>
        <?php $saved_days = (array) get_post_meta($post->ID, '_classtime_day', true); ?>
            <select name="classtime_day_of_week[]" multiple size="7">
                <?php
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                foreach ($days as $day) {
                    printf(
                        '<option value="%1$s"%2$s>%3$s</option>',
                        esc_attr($day),
                        in_array($day, $saved_days) ? ' selected' : '',
                        esc_html(ucfirst($day))
                    );
                }
                ?>
            </select>
        </label>
    </p>

    <p>
        <label>Class Start Date (required for all classes):<br>
            <input type="date" name="classtime_date" value="<?php echo esc_attr($date); ?>">
        </label>
    </p>

    <p>
        <label>Repeat Until (for Recurring Classes):<br>
            <input type="date" name="classtime_repeat_until" value="<?php echo esc_attr($repeat_until); ?>">
        </label>
    </p>
    <?php
}

// === Save Meta Fields ===
// === Save Meta Fields ===
add_action('save_post', function ($post_id) {
    if (
    !isset($_POST['classtime_meta_nonce']) ||
    !wp_verify_nonce(
        sanitize_text_field(wp_unslash($_POST['classtime_meta_nonce'])),
        'classtime_save_meta'
    )
) {
    return;
}
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    update_post_meta($post_id, '_classtime_start', sanitize_text_field(wp_unslash($_POST['classtime_start'] ?? '')));
    update_post_meta($post_id, '_classtime_end', sanitize_text_field(wp_unslash($_POST['classtime_end'] ?? '')));
    update_post_meta($post_id, '_classtime_notes', sanitize_textarea_field(wp_unslash($_POST['classtime_notes'] ?? '')));
    update_post_meta($post_id, '_classtime_recurrence', sanitize_text_field(wp_unslash($_POST['classtime_recurrence'] ?? '')));
    update_post_meta($post_id, '_classtime_date', sanitize_text_field(wp_unslash($_POST['classtime_date'] ?? '')));
    update_post_meta($post_id, '_classtime_repeat_until', sanitize_text_field(wp_unslash($_POST['classtime_repeat_until'] ?? '')));

    // ✅ Save multiple selected days
   if (isset($_POST['classtime_day_of_week'])) {
        $raw_input = wp_unslash($_POST);
        $day_input = isset($raw_input['classtime_day_of_week']) ? $raw_input['classtime_day_of_week'] : [];

        if (is_array($day_input)) {
            $days = array_map('sanitize_text_field', $day_input);
            update_post_meta($post_id, '_classtime_day', $days);
        } else {
            delete_post_meta($post_id, '_classtime_day');
        }
    } else {
        delete_post_meta($post_id, '_classtime_day');
    }

    // ✅ Save instructors
    if (!empty($_POST['classtime_instructors']) && is_array($_POST['classtime_instructors'])) {
        $cleaned = array_map('intval', $_POST['classtime_instructors']);
        update_post_meta($post_id, '_classtime_instructors', $cleaned);
    } else {
        delete_post_meta($post_id, '_classtime_instructors');
    }

    // ✅ Save selected Class Type taxonomy
    if (!empty($_POST['classtime_type'])) {
        wp_set_object_terms($post_id, (int) $_POST['classtime_type'], 'classtime_type');
    }

    // ✅ Save selected Class Level taxonomy
    if (!empty($_POST['classtime_level'])) {
        wp_set_object_terms($post_id, (int) $_POST['classtime_level'], 'classtime_level');
    }
});

// === Save Instructor Meta Fields ===
add_action('save_post_classtime_instructor', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Bail early if nonce not set
    if (empty($_POST['classtime_instructor_nonce'])) return;

    // Always unslash and sanitize input before use
    $nonce = sanitize_text_field(wp_unslash($_POST['classtime_instructor_nonce']));

    if (!wp_verify_nonce($nonce, 'save_classtime_instructor')) return;

    // Certification field
    $certification = '';
    if (!empty($_POST['classtime_instructor_certification'])) {
        $certification = sanitize_text_field(wp_unslash($_POST['classtime_instructor_certification']));
    }

    update_post_meta($post_id, 'classtime_instructor_certification', $certification);
});


