<?php

defined('ABSPATH') or exit;

include_once RY_IFSMILEPAY_PLUGIN_DIR . 'includes/ry-paid/abstract-link-server.php';

final class RY_IFSMILEPAY_LinkServer extends RY_Abstract_Link_Server
{
    protected static ?self $_instance = null;

    protected string $plugin_slug = 'ry-invoice-for-smilepay';

    public static function instance(): RY_IFSMILEPAY_LinkServer
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    protected function get_base_info(): array
    {
        return [
            'plugin' => RY_IFSMILEPAY_VERSION,
            'php' => PHP_VERSION,
            'wp' => get_bloginfo('version'),
        ];
    }

    protected function get_user_agent()
    {
        return sprintf(
            'RY_IFSMILEPAY %s (WordPress/%s)',
            RY_IFSMILEPAY_VERSION,
            get_bloginfo('version'),
        );
    }
}
