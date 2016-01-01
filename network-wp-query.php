<?php
/*
Plugin Name: Network WP Query
Description: Network-wide WP Query for Multisite environment
Version: 0.1.1
Author: Eric Lewis, Miguel Peixe, Whizark
Author URI: http://whizark.com
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: network-wp-query
Domain Path: /languages
Network: true
*/

if (!defined('ABSPATH')) {
    exit;
}

use Devaloka\Plugin\NetworkWpQuery\Plugin;

require_once __DIR__ . '/src/NetworkWpQuery.php';
require_once __DIR__ . '/src/Subscriber.php';
require_once __DIR__ . '/src/Plugin.php';

$Network_WP_Query = new Plugin();

$Network_WP_Query->boot();
