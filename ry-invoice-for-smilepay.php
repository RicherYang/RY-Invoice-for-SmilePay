<?php

/**
 * Plugin Name: RY Invoice for SmilePay
 * Plugin URI: https://ry-plugin.com/ry-invoice-for-smilepay
 * Description: SmilePay E-invoice, support WooCommerce.
 * Version: 2026.8.5
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

use RY\Invoice\Smilepay\Main;

define('RY_IFSMILEPAY_VERSION', '2026.8.5');
define('RY_IFSMILEPAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RY_IFSMILEPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RY_IFSMILEPAY_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('RY_IFSMILEPAY_PLUGIN_LANGUAGES_DIR', plugin_dir_path(__FILE__) . '/languages');

require_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/vendor/autoload.php';

register_activation_hook(__FILE__, [Main::class, 'plugin_activation']);
register_deactivation_hook(__FILE__, [Main::class, 'plugin_deactivation']);

Main::instance();
