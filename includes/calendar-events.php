<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

function classtime_get_instructor_tooltip($instructor_name) {
    return $instructor_name;
}

function classtime_smart_time($time) {
    $t = strtotime($time);
    if (!$t) return '';
    $minutes = gmdate('i', $t);
    return gmdate('g', $t) . ($minutes !== '00' ? ':' . $minutes : '') . ' ' . gmdate('A', $t);
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
        $days_of_week = get_post_meta($id, '_classtime_day', true); // array
        $repeat_until = get_post_meta($id, '_classtime_repeat_until', true);
        $notes        = get_post_meta($id, '_classtime_notes', true);

        $instructor_ids = get_post_meta($id, '_classtime_instructors', true);
       $instructors = [];
        if (!empty($instructor_ids) && is_array($instructor_ids)) {
            foreach ($instructor_ids as $instructor_id) {
                $instructor_name = get_the_title($instructor_id);
                $instructor_certification = get_post_meta($instructor_id, '_classtime_instructor_certification', true);

                // Only include link if Pro is active
                $link = (function_exists('classtime_pro_is_active') && classtime_pro_is_active())
                    ? get_permalink($instructor_id)
                    : '';

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
        $level_color = '';
        if (function_exists('classtime_pro_is_active') && classtime_pro_is_active()) {
            if (!empty($level_terms) && !is_wp_error($level_terms)) {
                $level_color = get_term_meta($level_terms[0]->term_id, 'classtime_level_color', true);
            }
        }
        $badge_color = (!empty($type_terms) && !is_wp_error($type_terms)) ? get_term_meta($type_terms[0]->term_id, 'classtime_type_color', true) : '';

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
                        if (!empty($ov['cancelled'])) {
                            $classes[] = 'classtime-cancelled';
                            $title_lines[] = '❌ CANCELLED';
                        }
                        if (!empty($ov['guest_instructor'])) {
                            $title_lines[] = '👤 Guest Instructor...';
                            $props['guest_instructor'] = true;
                        }
                        if (!empty($ov['technique_focus'])) {
                            $title_lines[] = '📘 Teaching Focus...';
                            $props['technique_focus'] = true;
                        }
                        $props['override_note'] = $ov['note'] ?? '';
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
                        'level_badge_color' => $level_color,
                        'extendedProps' => $props,
                    ];

                    $current->modify('+1 week');
                }
            }
        }

        // Fallback for non-recurring single date
        elseif ($recurrence !== 'weekly') {
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
                'level_badge_color' => $level_color,
                'extendedProps' => $event_base,
            ];
        }
    }

    wp_reset_postdata();
    return $events;
}

function classtime_render_calendar_shortcode() {
    ob_start();
    ?>
    <!-- Filters -->
    <div id="classtime-filters-wrapper">
        <div class="filter-heading">Filter by:</div>
        <div id="classtime-filters">
            <?php
            $instructors = [];
            $query = new WP_Query([
                'post_type'      => 'classtime_class',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            ]);
            while ($query->have_posts()) {
                $query->the_post();
                $list = get_post_meta(get_the_ID(), '_classtime_instructors', true);
                if (is_array($list)) {
                    foreach ($list as $instructor_id) {
                        $instructor_certification = get_post_meta($instructor_id, '_classtime_instructor_certification', true);    
                        $name = get_the_title($instructor_id);
                        if ($name && !in_array($name, $instructors)) {
                            $instructors[] = $name;
                        }
                    }
                }
            }
            wp_reset_postdata();
            sort($instructors);
            classtime_render_filter('instructor-filter', 'Instructor', $instructors);

            $types = get_terms([
                'taxonomy'   => 'classtime_type',
                'hide_empty' => false,
            ]);
            $type_names = array_map(function($term) { return $term->name; }, $types);
            sort($type_names);
            classtime_render_filter('type-filter', 'Class Type', $type_names);

            $levels = get_terms([
                'taxonomy'   => 'classtime_level',
                'hide_empty' => false,
            ]);
            $level_names = array_map(function($term) { return $term->name; }, $levels);
            sort($level_names);
            classtime_render_filter('level-filter', 'Class Level', $level_names);
            ?>
        </div>
    </div>

    <!-- Calendar -->
    <div id="classtime-calendar" style="max-width: 1000px; margin: 2rem auto;"></div>

   <!-- ✅ CLASS MODAL -->
   <!-- ✅ CLASS MODAL -->
<div id="classtime-modal" class="classtime-modal">
  <div class="classtime-modal-content">
    <button id="classtime-modal-close" class="classtime-modal-close">&times;</button>

    <h2 class="classtime-title" style="text-align: center;"></h2>
    <div class="classtime-override-labels" style="text-align: center; margin: 0.5rem 0;"></div>
    <div class="classtime-override-note" style="font-weight: bold; font-size: 1.1rem; color: var(--classtime-accent); text-align: center; display: none; margin-bottom: 0.5rem;"></div>

    <div class="classtime-level" style="margin-bottom: 0.5rem;"></div>

    <p><strong>Time:</strong> <span class="classtime-time"></span></p>

    <div>
      <strong>Instructors:</strong>
      <div class="classtime-instructors" style="margin-left: 1rem;"></div>
    </div>

    <div style="margin-top: 1rem;">
      <strong>Description:</strong><br>
      <div class="classtime-notes" style="margin-left: 1rem;"></div>
    </div>
  </div>
</div>


  <!-- ✅ CLINIC MODAL -->
<div id="classtime-clinic-modal" class="classtime-modal">
  <div class="classtime-modal-content">
    <button id="classtime-clinic-modal-close" class="classtime-modal-close">&times;</button>

    <h2><span class="classtime-title"></span></h2>
    <p><strong>Sessions:</strong></p>
    <ul class="classtime-sessions"></ul>
    <div class="classtime-info" style="margin-top: 1rem;"></div>
  </div>
</div>

</div>


    <?php
    return apply_filters('classtime_calendar_output', ob_get_clean());
}

add_shortcode('classtime_calendar', 'classtime_render_calendar_shortcode');
