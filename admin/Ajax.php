<?php

namespace RY\Invoice\Smilepay\Admin;

defined('ABSPATH') or exit;

use RY\Invoice\Smilepay\Main;
use RY\Invoice\Smilepay\WooCommerce\Invoice;

final class Ajax
{
    public static function init_action(): void
    {
        add_action('wp_ajax_RY_IFSMILEPAY_get', [__CLASS__, 'get_invoice']);
        add_action('wp_ajax_RY_IFSMILEPAY_cancel', [__CLASS__, 'cancel_invoice']);
        add_action('wp_ajax_RY_IFSMILEPAY_invalid', [__CLASS__, 'invalid_invoice']);
    }

    public static function get_invoice(): void
    {
        check_ajax_referer('get-invoice');

        $object_ID = intval($_POST['id'] ?? '');
        $type = sanitize_key($_POST['type'] ?? '');
        as_unschedule_action(Main::get_prefix_name('auto_get_invoice'), [$object_ID, $type], 'ry-invoice');

        switch ($type) {
            case 'woocommerce':
                if (function_exists('wc_get_order')) {
                    $order = wc_get_order($object_ID);
                    if ($order) {
                        if (current_user_can(get_post_type_object('shop_order')->cap->edit_post, $order->get_id()) || current_user_can('manage_woocommerce')) {
                            Invoice::instance()->get_invoice($order);
                        }
                    }
                }
                break;
        }

        wp_die();
    }

    public static function cancel_invoice(): void
    {
        check_ajax_referer('cancel-invoice');

        $object_ID = intval($_POST['id'] ?? '');
        $type = sanitize_key($_POST['type'] ?? '');
        as_unschedule_action(Main::get_prefix_name('auto_get_invoice'), [$object_ID, $type], 'ry-invoice');

        switch ($type) {
            case 'woocommerce':
                if (function_exists('wc_get_order')) {
                    $order = wc_get_order($object_ID);
                    if ($order) {
                        if (current_user_can(get_post_type_object('shop_order')->cap->edit_post, $order->get_id()) || current_user_can('manage_woocommerce')) {
                            Invoice::instance()->cancel_invoice($order);
                        }
                    }
                }
                break;
        }

        wp_die();
    }

    public static function invalid_invoice(): void
    {
        check_ajax_referer('invalid-invoice');

        $object_ID = intval($_POST['id'] ?? '');
        $type = sanitize_key($_POST['type'] ?? '');
        as_unschedule_action(Main::get_prefix_name('auto_get_invoice'), [$object_ID, $type], 'ry-invoice');

        switch ($type) {
            case 'woocommerce':
                if (function_exists('wc_get_order')) {
                    $order = wc_get_order($object_ID);
                    if ($order) {
                        if (current_user_can(get_post_type_object('shop_order')->cap->edit_post, $order->get_id()) || current_user_can('manage_woocommerce')) {
                            Invoice::instance()->invalid_invoice($order);
                        }
                    }
                }
                break;
        }

        wp_die();
    }
}
