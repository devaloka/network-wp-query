<?php
/*
Plugin Name: Network WP Query
Description: Network-wide WP Query for Multisite environment
Version: 0.2.0
Author: Eric Lewis, Miguel Peixe, Whizark
Author URI: http://whizark.com
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: network-wp-query
Domain Path: /network-wp-query/languages
Network: true
*/

if (!defined('ABSPATH')) {
    exit;
}

require WPMU_PLUGIN_DIR . '/network-wp-query/network-wp-query.php';
