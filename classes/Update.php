<?php

namespace RY\Invoice\Smilepay;

defined('ABSPATH') or exit;

final class Update
{
    public static function update()
    {
        $now_version = \RY_IFSMILEPAY::get_option('version', '0.0.0');

        if (RY_IFSMILEPAY_VERSION === $now_version) {
            return;
        }

        if ($now_version === '0.0.0') {
            \RY_IFSMILEPAY::update_option('version', RY_IFSMILEPAY_VERSION, true);
            return;
        }

        if (version_compare($now_version, '2026.7.19', '<')) {
            \RY_IFSMILEPAY::update_option('version', '2026.7.19', true);
        }
    }
}
