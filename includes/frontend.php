<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Enqueue frontend assets for the schedule page
 */
add_action('wp_enqueue_scripts', function () {
    global $post;
    $is_calendar_page = false;

    if (is_page() && isset($post) && has_shortcode(get_post_field('post_content', $post->ID), 'classtime_calendar')) {
        $is_calendar_page = true;
    }

    $is_instructor_archive = is_post_type_archive('classtime_instructor');
    $is_instructor_single = is_singular('classtime_instructor');

    if ($is_calendar_page || $is_instructor_archive || $is_instructor_single) {
        // ✅ Load frontend styles globally for ClassTime views
        wp_enqueue_style(
            'classtime-style',
            CLASSTIME_URL . 'assets/style.css',
            [],
            CLASSTIME_VERSION
        );

        // ✅ Load FullCalendar scripts from local assets
        if ($is_calendar_page) {
            wp_enqueue_style(
                'fullcalendar-css',
                CLASSTIME_URL . 'assets/fullcalendar/index.global.min.css',
                [],
                '6.1.8'
            );
            wp_enqueue_script(
                'fullcalendar-js',
                CLASSTIME_URL . 'assets/fullcalendar/index.global.min.js',
                [],
                '6.1.8',
                true
            );

            wp_enqueue_script(
                'classtime-calendar',
                CLASSTIME_URL . 'assets/frontend-calendar.js',
                ['fullcalendar-js'],
                CLASSTIME_VERSION,
                true
            );

            wp_localize_script('classtime-calendar', 'classtimeCalendarData', [
                'events' => classtime_get_events_json(),
            ]);
        }
    }
});
