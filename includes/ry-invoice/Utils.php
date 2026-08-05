<?php

namespace RY\Invoice\V20260805;

defined('ABSPATH') or exit;

final class Utils
{
    public static function invoice_type_to_name($value = '')
    {
        static $list = [];
        if (empty($list)) {
            $list = [
                'personal' => _x('personal', 'invoice type', 'ry-invoice-for-smilepay'),
                'company' => _x('company', 'invoice type', 'ry-invoice-for-smilepay'),
                'donate' => _x('donate', 'invoice type', 'ry-invoice-for-smilepay'),
            ];
        }

        return $list[$value] ?? $value;
    }

    public static function carruer_type_to_name($value = '')
    {
        static $list = [];
        if (empty($list)) {
            $list = [
                'amego_host' => _x('amego_host', 'carruer type', 'ry-invoice-for-smilepay'),
                'ezpay_host' => _x('ezpay_host', 'carruer type', 'ry-invoice-for-smilepay'),
                'ecpay_host' => _x('ecpay_host', 'carruer type', 'ry-invoice-for-smilepay'),
                'smilepay_host' => _x('smilepay_host', 'carruer type', 'ry-invoice-for-smilepay'),
                'MOICA' => _x('MOICA', 'carruer type', 'ry-invoice-for-smilepay'),
                'phone_barcode' => _x('phone_barcode', 'carruer type', 'ry-invoice-for-smilepay'),
            ];
        }

        return $list[$value] ?? $value;
    }
}
