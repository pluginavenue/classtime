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
            <?php echo wp_get_attachment_image(
                $image_id,
                'thumbnail',
                false,
                [
                    'id'    => 'classtime_instructor_image_preview',
                    'style' => 'width:120px;height:auto;margin-bottom:10px;display:block;',
                    'alt'   => esc_attr__('Instructor Image', 'classtime')
                ]
            ); ?>
        <?php else: ?>
           <?php
            // Optional: fallback image ID or blank image output
            echo wp_get_attachment_image(
                0,
                'thumbnail',
                false,
                [
                    'id'    => 'classtime_instructor_image_preview',
                    'style' => 'display:none;width:120px;height:auto;margin-bottom:10px;',
                    'alt'   => esc_attr__('Instructor Image', 'classtime'),
                    'src'   => '', // fallback if needed
                ]
            );
            ?>

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
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // 🔒 Get and sanitize the nonce safely
    $nonce_raw = filter_input(INPUT_POST, 'classtime_meta_nonce', FILTER_UNSAFE_RAW);
    if (!$nonce_raw) return;

    $nonce = sanitize_text_field(wp_unslash($nonce_raw));
    if (!wp_verify_nonce($nonce, 'classtime_save_meta')) return;

    // ✅ Save certification (text)
    $cert_raw = filter_input(INPUT_POST, 'classtime_instructor_certification', FILTER_UNSAFE_RAW);
    if (!is_null($cert_raw)) {
        update_post_meta($post_id, '_classtime_instructor_certification', sanitize_text_field(wp_unslash($cert_raw)));
    }

    // ✅ Save bio and image (Pro only)
    if (function_exists('classtime_pro_is_active') && classtime_pro_is_active()) {
        $bio_raw = filter_input(INPUT_POST, 'classtime_instructor_bio', FILTER_UNSAFE_RAW);
        if (!is_null($bio_raw)) {
            update_post_meta($post_id, '_classtime_instructor_bio', wp_kses_post(wp_unslash($bio_raw)));
        }

        $image_raw = filter_input(INPUT_POST, 'classtime_instructor_image', FILTER_UNSAFE_RAW);
        if (!is_null($image_raw)) {
            $image_id = intval($image_raw);
            update_post_meta($post_id, 'classtime_instructor_image', $image_id);
            set_post_thumbnail($post_id, $image_id);
        }
    }
});




