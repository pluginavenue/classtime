<?php
if (!defined('ABSPATH')) exit;

class CT_Loader {
    public function __construct() {
        add_action('init', [$this, 'init_plugin']);
    }

    public function init_plugin() {
        // Placeholder: load text domain, register post types, etc.
    }
}

new CT_Loader();