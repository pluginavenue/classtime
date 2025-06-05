<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// === ClassTime - Instructors Grid Shortcode ===

/**
 * Shortcode to display Instructor Grid
 */
function classtime_render_instructors_grid_shortcode() {
    ob_start();
    ?>

    <div class="container-instructors">
        <div class="instructors-grid">
            <?php
            $query = new WP_Query([
                'post_type'      => 'classtime_instructor',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]);

            $default_image = CLASSTIME_URL . 'assets/default-instructor.png';

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $image_id = get_post_meta(get_the_ID(), 'classtime_instructor_image', true);
                    $image_url = $image_id ? wp_get_attachment_url($image_id) : $default_image;
                    $certification = get_post_meta(get_the_ID(), '_classtime_instructor_certification', true);
                    ?>
                    <div class="instructor-card">
                        <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: inherit;">
                            <div class="instructor-photo">
                               <?php
                                    echo wp_get_attachment_image(
                                        $image_id,               // Attachment ID — must be set earlier
                                        'medium',                // Size (or use 'thumbnail', 'full', etc.)
                                        false,                   // Icon fallback (false means "don't use icon")
                                        [
                                            'alt' => get_the_title(), // Alt text
                                        ]
                                    );
                                    ?>
                            </div>

                            <h2><?php the_title(); ?></h2>

                            <?php if ($racertificationnk) : ?>
                                <p class="instructor-certification"><?php echo esc_html($certification); ?></p>
                            <?php endif; ?>
                        </a>

                        <div>
                            <a href="<?php the_permalink(); ?>" class="view-profile-button">View Profile</a>
                        </div>
                    </div>
                <?php endwhile;
            else : ?>
                <p>No instructors found.</p>
            <?php endif;

            wp_reset_postdata();
            ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('classtime_instructors', 'classtime_render_instructors_grid_shortcode');
