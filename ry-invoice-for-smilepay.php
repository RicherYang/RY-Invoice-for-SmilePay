<?php

/**
 * Plugin Name: RY Invoice for Smilepay
 * Plugin URI: https://ry-plugin.com/ry-invoice-for-smilepay
 * Description: Smilepay E-invoice, support WooCommerce.
 * Version: 2026.7.3
 * Requires at least: 6.8
 * Requires PHP: 8.2
 * Author: Richer Yang
 * Author URI: https://richer.tw/
 * License: GPLv3
 * Update URI: https://ry-plugin.com/ry-invoice-for-smilepay
 *
 * Text Domain: ry-invoice-for-smilepay
 * Domain Path: /languages
 */

defined('ABSPATH') or exit;

define('RY_IFSMILEPAY_VERSION', '2026.7.3');
define('RY_IFSMILEPAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RY_IFSMILEPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RY_IFSMILEPAY_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('RY_IFSMILEPAY_PLUGIN_LANGUAGES_DIR', plugin_dir_path(__FILE__) . '/languages');

require_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/main.php';

register_activation_hook(__FILE__, ['RY_IFSMILEPAY', 'plugin_activation']);
register_deactivation_hook(__FILE__, ['RY_IFSMILEPAY', 'plugin_deactivation']);

function RY_IFSMILEPAY(): RY_IFSMILEPAY
{
    return RY_IFSMILEPAY::instance();
}

RY_IFSMILEPAY();
