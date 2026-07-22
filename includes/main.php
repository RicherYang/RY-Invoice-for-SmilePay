<?php

defined('ABSPATH') or exit;

use RY\General\AbstractBasic;
use RY\General\Logs;
use RY\Invoice\Smilepay\Admin\Admin;
use RY\Invoice\Smilepay\Cron;
use RY\Invoice\Smilepay\License;
use RY\Invoice\Smilepay\Update;
use RY\Invoice\Smilepay\Updater;
use RY\Invoice\Smilepay\WooCommerce\Fields;
use RY\Invoice\Smilepay\WooCommerce\Invoice;

final class RY_IFSMILEPAY extends AbstractBasic
{
    public const OPTION_PREFIX = 'RY_IFSMILEPAY_';

    public const PLUGIN_NAME = 'RY Invoice for SmilePay';

    private static ?self $_instance = null;

    public static function instance(): RY_IFSMILEPAY
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void
    {
        load_plugin_textdomain('ry-invoice-for-smilepay', false, plugin_basename(dirname(__DIR__)) . '/languages');
        include_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/vendor/woocommerce/action-scheduler/action-scheduler.php';

        Logs::set_log(RY_IFSMILEPAY::get_option('log', 'no') === 'yes', 'smilepay-invoice');

        if (is_admin()) {
            Update::update();
        }

        add_action('init', [$this, 'do_wp_init'], 9);
    }

    public function do_wp_init(): void
    {
        Updater::instance();

        if (is_admin()) {
            Admin::instance();
        }

        if (License::instance()->is_activated()) {
            Cron::add_action();
        }

        if (has_action('woocommerce_init')) {
            Fields::instance();

            if (License::instance()->is_activated()) {
                Invoice::instance();
            }
        }
    }

    public static function plugin_activation() {}

    public static function plugin_deactivation()
    {
        wp_unschedule_hook(self::OPTION_PREFIX . 'check_expire');
    }
}
