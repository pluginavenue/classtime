<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// === Frontend UI Helpers ===

/**
 * Render a filter dropdown for calendar or frontend lists
 *
 * @param string $id     The HTML ID of the select element
 * @param string $label  The label text
 * @param array  $options List of dropdown options
 */
function classtime_render_filter($id, $label, $options) {
    echo '<div style="margin-bottom: 1rem;">';
    echo '<label for="' . esc_attr($id) . '" style="font-weight: bold;">' . esc_html($label) . ':</label>';
    echo '<select id="' . esc_attr($id) . '" style="margin-left: 1rem;">';
    echo '<option value="">All ' . esc_html($label) . '</option>';
    foreach ($options as $option) {
        echo '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
    }
    echo '</select>';
    echo '</div>';
}
