<?php

defined('ABSPATH') or exit;

$order_statuses = wc_get_order_statuses();
$paid_status = [];
foreach (wc_get_is_paid_statuses() as $status) {
    $paid_status[] = $order_statuses['wc-' . $status];
}
$paid_status = implode(', ', $paid_status);

return [
    [
        'title' => __('Base options', 'ry-invoice-for-smilepay'),
        'id' => 'base_options',
        'type' => 'title',
    ],
    [
        'title' => __('Show invoice number', 'ry-invoice-for-smilepay'),
        'id' => RY_IFSMILEPAY::OPTION_PREFIX . 'show_invoice_number',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Show invoice number in Frontend order list', 'ry-invoice-for-smilepay'),
    ],
    [
        'title' => __('Move billing company', 'ry-invoice-for-smilepay'),
        'id' => RY_IFSMILEPAY::OPTION_PREFIX . 'move_billing_company',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Move billing company to invoice area', 'ry-invoice-for-smilepay'),
    ],
    [
        'id' => 'base_options',
        'type' => 'sectionend',
    ],
    [
        'title' => __('Invoice options', 'ry-invoice-for-smilepay'),
        'id' => 'invoice_options',
        'type' => 'title',
    ],
    [
        'title' => __('Get mode', 'ry-invoice-for-smilepay'),
        'id' => RY_IFSMILEPAY::OPTION_PREFIX . 'get_mode',
        'type' => 'select',
        'default' => 'manual',
        'options' => [
            'manual' => _x('manual', 'get mode', 'ry-invoice-for-smilepay'),
            'auto_paid' => _x('auto ( when order paid )', 'get mode', 'ry-invoice-for-smilepay'),
            'auto_completed' => _x('auto ( when order completed )', 'get mode', 'ry-invoice-for-smilepay'),
        ],
        'desc' => sprintf(
            /* translators: %s: paid status */
            __('Order paid status: %s', 'ry-invoice-for-smilepay'),
            $paid_status,
        ),
    ],
    [
        'title' => __('Skip foreign orders', 'ry-invoice-for-smilepay'),
        'id' => RY_IFSMILEPAY::OPTION_PREFIX . 'skip_foreign_order',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Disable auto get invoice for order billing country and shipping country are not in Taiwan.', 'ry-invoice-for-smilepay'),
        'autoload' => false,
    ],
    [
        'title' => __('Delay time', 'ry-invoice-for-smilepay'),
        'id' => RY_IFSMILEPAY::OPTION_PREFIX . 'get_delay_time',
        'type' => 'number',
        'default' => '0',
        'min' => '0',
        'max' => '336',
        'step' => '1',
        'desc' => __('After N hours get invoice.', 'ry-invoice-for-smilepay')
            . __('According to WordPress cron job, the actual execution time will be later than the specified time.', 'ry-invoice-for-smilepay'),
        'autoload' => false,
    ],
    [
        'title' => __('Invalid mode', 'ry-invoice-for-smilepay'),
        'id' => RY_IFSMILEPAY::OPTION_PREFIX . 'invalid_mode',
        'type' => 'select',
        'default' => 'manual',
        'options' => [
            'manual' => _x('manual', 'invalid mode', 'ry-invoice-for-smilepay'),
            'auto_cancel' => _x('auto ( when order status cancelled OR refunded )', 'invalid mode', 'ry-invoice-for-smilepay'),
        ],
    ],
    [
        'title' => __('Order no prefix', 'ry-invoice-for-smilepay'),
        'id' => RY_IFSMILEPAY::OPTION_PREFIX . 'prefix',
        'type' => 'text',
        'desc' => __('The prefix string of order no. Only letters and numbers allowed.', 'ry-invoice-for-smilepay'),
        'desc_tip' => true,
        'autoload' => false,
    ],
    [
        'title' => __('Custom track code', 'ry-invoice-for-smilepay'),
        'id' => RY_IFSMILEPAY::OPTION_PREFIX . 'trackcode',
        'type' => 'text',
        'default' => '',
        'autoload' => false,
    ],
    [
        'id' => 'invoice_options',
        'type' => 'sectionend',
    ],
];
