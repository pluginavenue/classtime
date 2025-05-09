<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// ✅ Register the meta box
add_action('add_meta_boxes', function() {
    add_meta_box(
        'classtime_instructor_meta',
        'Instructor Details',
        'classtime_render_instructor_meta_box',
        'classtime_instructor',
        'normal',
        'default'
    );
});

// ✅ Enqueue media uploader for image upload
add_action('admin_enqueue_scripts', function($hook) {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'classtime_instructor') {
        wp_enqueue_media();
    }
});

// ✅ Render the meta box content
function classtime_render_instructor_meta_box($post) {
    // ✅ Load existing meta
    $certification = get_post_meta($post->ID, '_classtime_instructor_certification', true);
    $bio = get_post_meta($post->ID, '_classtime_instructor_bio', true);
    $image_id = get_post_meta($post->ID, 'classtime_instructor_image', true);

    wp_nonce_field('classtime_save_meta', 'classtime_meta_nonce');
    ?>
    <p>
        <label for="classtime_instructor_certification"><strong>Certification(s):</strong></label><br>
        <input type="text" name="classtime_instructor_certification" id="classtime_instructor_certification"
               value="<?php echo esc_attr($certification); ?>" style="width: 100%;">
        <small>Separate multiple certifications with commas. (Example: "Black Belt, USA Judo Coach")</small>
    </p>

    <?php if ( function_exists('classtime_pro_is_active') && classtime_pro_is_active() ): ?>
    <p>
        <label for="classtime_instructor_bio"><strong>Instructor Bio:</strong></label><br>
        <?php
        wp_editor(
            $bio,
            'classtime_instructor_bio',
            [
                'textarea_name' => 'classtime_instructor_bio',
                'textarea_rows' => 8,
                'media_buttons' => false,
            ]
        );
        ?>
    </p>

    <p>
        <label for="classtime_instructor_image"><strong>Instructor Photo:</strong></label><br>
        <?php if ($image_id): ?>
            <img id="classtime_instructor_image_preview" src="<?php echo esc_url(wp_get_attachment_url($image_id)); ?>" alt="Instructor Image" style="width:120px;height:auto;margin-bottom:10px;display:block;">
        <?php else: ?>
            <img id="classtime_instructor_image_preview" src="" style="display:none;width:120px;height:auto;margin-bottom:10px;">
        <?php endif; ?>
        <input type="hidden" name="classtime_instructor_image" id="classtime_instructor_image" value="<?php echo esc_attr($image_id); ?>">
        <button type="button" class="button" id="classtime_instructor_image_upload">Upload Image</button>
    </p>

    <script>
    (function($){
        $(document).ready(function(){
            var frame;
            $('#classtime_instructor_image_upload').on('click', function(e){
                e.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: 'Select or Upload Instructor Image',
                    button: { text: 'Use this image' },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#classtime_instructor_image').val(attachment.id);

                    let preview = $('#classtime_instructor_image_preview');
                    if (preview.length === 0) {
                        $('<img>', {
                            id: 'classtime_instructor_image_preview',
                            src: attachment.url,
                            style: 'width:120px;height:auto;margin-bottom:10px;display:block;'
                        }).insertBefore('#classtime_instructor_image');
                    } else {
                        preview.attr('src', attachment.url).show();
                    }
                });

                frame.open();
            });
        });
    })(jQuery);
    </script>
    <?php endif;
}

// ✅ Save the custom fields
add_action('save_post', function($post_id) {
    if (get_post_type($post_id) !== 'classtime_instructor') return;
    if (!isset($_POST['classtime_meta_nonce']) || !wp_verify_nonce($_POST['classtime_meta_nonce'], 'classtime_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['classtime_instructor_certification'])) {
        update_post_meta($post_id, '_classtime_instructor_certification', sanitize_text_field($_POST['classtime_instructor_certification']));
    }

    if (function_exists('classtime_pro_is_active') && classtime_pro_is_active()) {
        if (isset($_POST['classtime_instructor_bio'])) {
            update_post_meta($post_id, '_classtime_instructor_bio', wp_kses_post($_POST['classtime_instructor_bio']));
        }

        if (isset($_POST['classtime_instructor_image'])) {
            update_post_meta($post_id, 'classtime_instructor_image', intval($_POST['classtime_instructor_image']));
            set_post_thumbnail($post_id, intval($_POST['classtime_instructor_image']));
        }
    }
});
