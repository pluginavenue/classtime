<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

function classtime_get_instructor_tooltip($instructor_name) {
    return $instructor_name;
}

function classtime_smart_time($time) {
    $t = strtotime($time);
    if (!$t) return '';
    $minutes = date('i', $t);
    return date('g', $t) . ($minutes !== '00' ? ':' . $minutes : '') . ' ' . date('A', $t);
}

function classtime_get_events_json() {
    $events = [];

    // === Load class overrides ===
    $override_posts = get_posts([
        'post_type' => 'classtime_override',
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    $overrides_by_class_and_date = [];

    foreach ($override_posts as $override) {
        $class_id = get_post_meta($override->ID, 'parent_class_id', true);
        $date = get_post_meta($override->ID, 'override_date', true);
        $cancelled = get_post_meta($override->ID, 'is_cancelled', true);
        $note = get_post_meta($override->ID, 'override_note', true);
        $guest_instructor = get_post_meta($override->ID, '_classtime_is_guest_instructor', true);
        $technique_focus = get_post_meta($override->ID, '_classtime_is_technique_focus', true);

        if ($class_id && $date) {
            $key = "{$class_id}_{$date}";
            $overrides_by_class_and_date[$key] = [
                'cancelled' => $cancelled === '1',
                'note' => $note,
                'guest_instructor' => ($guest_instructor === '1'),
                'technique_focus' => ($technique_focus === '1'),
            ];
        }
    }

    $query = new WP_Query([
        'post_type' => 'classtime_class',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    while ($query->have_posts()) {
        $query->the_post();
        $id = get_the_ID();

        $date         = get_post_meta($id, '_classtime_date', true);
        $start_time   = get_post_meta($id, '_classtime_start', true);
        $end_time     = get_post_meta($id, '_classtime_end', true);
        $recurrence   = get_post_meta($id, '_classtime_recurrence', true);
        $days_of_week = get_post_meta($id, '_classtime_day', true);
        $repeat_until = get_post_meta($id, '_classtime_repeat_until', true);
        $notes        = get_post_meta($id, '_classtime_notes', true);

        $instructor_ids = get_post_meta($id, '_classtime_instructors', true);
        $instructors = [];
        if (!empty($instructor_ids) && is_array($instructor_ids)) {
            foreach ($instructor_ids as $instructor_id) {
                $instructor_name = get_the_title($instructor_id);
                $instructor_certification = get_post_meta($instructor_id, '_classtime_instructor_certification', true);
                $link = function_exists('classtime_pro_enabled') ? get_permalink($instructor_id) : '';
                $instructors[] = [
                    'id' => $instructor_id,
                    'name' => $instructor_name,
                    'certification' => $instructor_certification,
                    'link' => $link,
                ];
            }
        }

        $type_terms = get_the_terms($id, 'classtime_type');
        $level_terms = get_the_terms($id, 'classtime_level');
        $type_name = ($type_terms && !is_wp_error($type_terms)) ? $type_terms[0]->name : '';
        $level_name = ($level_terms && !is_wp_error($level_terms)) ? $level_terms[0]->name : '';
        $level_color = (!empty($level_terms)) ? get_term_meta($level_terms[0]->term_id, 'classtime_level_color', true) : '';
        $badge_color = (!empty($type_terms)) ? get_term_meta($type_terms[0]->term_id, 'classtime_type_color', true) : '';

        if (!$date || !$start_time) continue;

        $start_dt = new DateTime($date . 'T' . $start_time);
        $end_dt = $end_time ? new DateTime($date . 'T' . $end_time) : null;

        $time_range = classtime_smart_time($start_time);
        if ($end_time) $time_range .= ' – ' . classtime_smart_time($end_time);

        $event_base = [
            'instructors' => $instructors,
            'class_type' => $type_name,
            'class_level' => $level_name,
            'notes' => $notes,
            'time' => $time_range,
            'badge_color' => $badge_color,
            'level_color' => $level_color,
            'override' => null,
        ];

        if ($recurrence === 'weekly' && is_array($days_of_week)) {
            $repeat_until_dt = $repeat_until ? new DateTime($repeat_until) : (clone $start_dt)->modify('+12 months');
            $repeat_until_dt->setTime(23, 59, 59);

            foreach ($days_of_week as $day) {
                $day = strtolower(trim($day));
                $current = clone $start_dt;
                $start_day = strtolower($start_dt->format('l'));

                if ($start_day !== strtolower($day)) {
                    $current->modify("next $day");
                }

                while ($current <= $repeat_until_dt) {
                    $event_date = $current->format('Y-m-d');
                    $override_key = "{$id}_{$event_date}";
                    $props = $event_base;
                    $classes = ['classtime-event'];
                    $title_lines = [];

                    if (isset($overrides_by_class_and_date[$override_key])) {
                        $ov = $overrides_by_class_and_date[$override_key];
                        $props['override'] = $ov;

                        if (!empty($ov['cancelled'])) {
                            $classes[] = 'classtime-cancelled';
                            $title_lines[] = '❌ CANCELLED';
                        }
                        if (!empty($ov['guest_instructor'])) {
                            $title_lines[] = '👤 Guest Instructor...';
                        }
                        if (!empty($ov['technique_focus'])) {
                            $title_lines[] = '📘 Teaching Focus...';
                        }
                    }

                    if ($type_name) $title_lines[] = $type_name;
                    if ($level_name) $title_lines[] = $level_name;
                    $title_lines[] = $time_range;
                    if (!empty($instructors)) $title_lines[] = implode(', ', array_column($instructors, 'name'));

                    $events[] = [
                        'title' => implode("\n", $title_lines),
                        'start' => $current->format('Y-m-d\T' . $start_dt->format('H:i:s')),
                        'end' => $end_dt ? $current->format('Y-m-d\T' . $end_dt->format('H:i:s')) : null,
                        'classNames' => $classes,
                        'extendedProps' => $props,
                    ];

                    $current->modify('+1 week');
                }
            }
        } else {
            $events[] = [
                'title' => implode("\n", array_filter([
                    $type_name,
                    $level_name,
                    $time_range,
                    !empty($instructors) ? implode(', ', array_column($instructors, 'name')) : '',
                ])),
                'start' => $start_dt->format('Y-m-d\TH:i:s'),
                'end' => $end_dt ? $end_dt->format('Y-m-d\TH:i:s') : null,
                'classNames' => ['classtime-event'],
                'extendedProps' => $event_base,
            ];
        }
    }
    wp_reset_postdata();

    // === Load clinics ===
    $clinic_query = new WP_Query([
        'post_type' => 'classtime_clinic',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    while ($clinic_query->have_posts()) {
        $clinic_query->the_post();
        $clinic_id = get_the_ID();

        $clinic_title = get_the_title($clinic_id);
        $clinic_info = get_post_meta($clinic_id, 'classtime_clinic_info', true);
        $clinic_color = get_post_meta($clinic_id, '_classtime_clinic_color', true);
        $session_times = get_post_meta($clinic_id, '_classtime_clinic_sessions', true);

        $instructor_ids = get_post_meta($clinic_id, '_classtime_clinic_instructors', true);
        $instructor_data = [];
        if (!empty($instructor_ids) && is_array($instructor_ids)) {
            foreach ($instructor_ids as $instructor_id) {
                $instructor_data[] = [
                    'id' => $instructor_id,
                    'name' => get_the_title($instructor_id),
                    'certification' => get_post_meta($instructor_id, '_classtime_instructor_certification', true),
                    'link' => function_exists('classtime_pro_enabled') ? get_permalink($instructor_id) : '',
                ];
            }
        }

        $sessions_by_date = [];
        if (is_array($session_times)) {
            foreach ($session_times as $session) {
                if (!empty($session['date']) && !empty($session['start']) && !empty($session['end'])) {
                    $formatted = classtime_smart_time($session['start']) . '–' . classtime_smart_time($session['end']);
                    $session['formatted'] = $formatted;
                    $sessions_by_date[$session['date']][] = $formatted;
                }
            }
        }

        foreach ($sessions_by_date as $date => $time_ranges) {
            $lines = [$clinic_title, ''];
            foreach ($time_ranges as $range) {
                $lines[] = $range;
            }
            $title = implode('<br>', $lines);

            $events[] = [
                'title' => $title,
                'start' => $date . 'T00:00:00',
                'end' => $date . 'T23:59:59',
                'classNames' => ['classtime-clinic-session'],
                'extendedProps' => [
                    'clinic_id' => $clinic_id,
                    'clinic_title' => $clinic_title,
                    'clinic_info' => sanitize_textarea_field($clinic_info),
                    'clinic_color' => $clinic_color,
                    'instructors' => $instructor_data,
                    'sessions' => $session_times,
                ],
            ];
        }
    }

    wp_reset_postdata();
    return $events;
}

add_action('wp_enqueue_scripts', function () {
    if (is_page() && has_shortcode(get_post()->post_content ?? '', 'classtime_calendar')) {
        wp_localize_script('classtime-calendar', 'classtimeCalendarData', [
            'events' => classtime_get_events_json()
        ]);
    }
}, 20);

function classtime_render_calendar_shortcode() {
    ob_start();
    ?>
    <div id="classtime-filters-wrapper">
        <div class="filter-heading">Filter by:</div>
        <div id="classtime-filters">
            <select id="instructor-filter">
                <option value="">All Instructors</option>
            </select>
            <select id="type-filter">
                <option value="">All Class Types</option>
            </select>
            <select id="level-filter">
                <option value="">All Class Levels</option>
            </select>
        </div>
    </div>

    <div id="classtime-calendar" style="max-width: 1000px; margin: 2rem auto;"></div>

    <!-- CLASS MODAL -->
    <div id="classtime-modal" class="classtime-modal">
        <div class="classtime-modal-content">
            <button class="classtime-modal-close" aria-label="Close">×</button>
            <div class="classtime-override-banner"></div>
            <h3 class="classtime-title">Class Type</h3>
            <div class="classtime-level"></div>
            <div class="classtime-time"></div>
            <div class="classtime-instructors"></div>
            <div class="classtime-notes"></div>
        </div>
    </div>

    <!-- CLINIC MODAL -->
    <div id="classtime-clinic-modal" class="classtime-modal">
        <div class="classtime-modal-content">
            <button class="classtime-modal-close" aria-label="Close">×</button>
            <h3 class="clinic-title">Clinic Title</h3>
            <div class="clinic-notes"></div>
        </div>
    </div>
    <?php
    return apply_filters('classtime_calendar_output', ob_get_clean());
}
add_shortcode('classtime_calendar', 'classtime_render_calendar_shortcode');