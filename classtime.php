<?php
/**
 * Plugin Name: ClassTime
 * Description: Display and manage class schedules with recurring events and instructor notes.
 * Plugin URI: https://pluginavenue.com/plugins/classtime
 * Author: Plugin Avenue
 * Author URI: https://pluginavenue.com
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: classtime
 */

if (!defined('ABSPATH')) exit;

// === Define Constants ===
define('CLASSTIME_VERSION', '1.0.0');
define('CLASSTIME_PATH', plugin_dir_path(__FILE__));
define('CLASSTIME_URL', plugin_dir_url(__FILE__));

// === Load Core Files ===
require_once CLASSTIME_PATH . 'includes/class-cpt.php';
require_once CLASSTIME_PATH . 'includes/instructor-cpt.php';
require_once CLASSTIME_PATH . 'includes/taxonomies.php';
require_once CLASSTIME_PATH . 'includes/class-meta-boxes.php';
require_once CLASSTIME_PATH . 'includes/class-type-meta.php';
require_once CLASSTIME_PATH . 'includes/instructor-meta-box.php';
require_once CLASSTIME_PATH . 'includes/instructor-meta-box-save.php';
require_once CLASSTIME_PATH . 'includes/calendar-events.php';
require_once CLASSTIME_PATH . 'includes/frontend.php';
require_once CLASSTIME_PATH . 'includes/admin-ui.php';
require_once CLASSTIME_PATH . 'includes/admin-menu.php';
require_once CLASSTIME_PATH . 'includes/template-loader.php';
require_once CLASSTIME_PATH . 'includes/frontend-filters.php';
require_once CLASSTIME_PATH . 'includes/frontend-instructors.php';
require_once CLASSTIME_PATH . 'includes/admin-help.php';

// ✅ Force Classic Editor for classtime_instructor (Gutenberg breaks wp_editor)
add_filter('use_block_editor_for_post_type', function ($use_block_editor, $post_type) {
    if ($post_type === 'classtime_instructor') {
        return false;
    }
    return $use_block_editor;
}, 10, 2);

// ✅ Ensure editor scripts are loaded (TinyMCE, QuickTags, etc)
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_editor();
});
