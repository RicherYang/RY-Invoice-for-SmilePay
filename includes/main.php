<?php

defined('ABSPATH') or exit;

use RY\General\AbstractBasic;
use RY\General\Logs;

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
            include_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/update.php';
            RY_IFSMILEPAY_Update::update();
        }

        add_action('init', [$this, 'do_wp_init'], 9);
    }

    public function do_wp_init(): void
    {
        include_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/functions.php';
        include_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/license.php';
        include_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/link-server.php';
        include_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/updater.php';
        RY_IFSMILEPAY_Updater::instance();

        if (is_admin()) {
            include_once RY_IFSMILEPAY_PLUGIN_DIR . 'admin/admin.php';
            RY_IFSMILEPAY_Admin::instance();
        }

        if (RY_IFSMILEPAY_License::instance()->is_activated()) {
            include_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/abstracts/abstract-smilepay.php';
            include_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/invoice.php';

            include_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/cron.php';
            RY_IFSMILEPAY_Cron::add_action();
        }

        if (has_action('woocommerce_init')) {
            include_once RY_IFSMILEPAY_PLUGIN_DIR . 'woocommerce/invoice-basic.php';
            RY_IFSMILEPAY_WC_Invoice_Basic::instance();

            if (RY_IFSMILEPAY_License::instance()->is_activated()) {
                include_once RY_IFSMILEPAY_PLUGIN_DIR . 'woocommerce/invoice.php';
                RY_IFSMILEPAY_WC_Invoice::instance();
            }
        }
    }

    public static function plugin_activation() {}

    public static function plugin_deactivation()
    {
        wp_unschedule_hook(self::OPTION_PREFIX . 'check_expire');
    }
}
