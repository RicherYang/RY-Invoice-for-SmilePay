<?php

namespace RY\Invoice\Smilepay\Admin\Page;

defined('ABSPATH') or exit;

use RY\General\V20260727\AbstractAdminPage;
use RY\General\V20260727\Utils;
use RY\Invoice\Smilepay\Main;

final class Option extends AbstractAdminPage
{
    public static function init_menu(): void
    {
        add_filter('ry_invoice-navs', [__CLASS__, 'add_nav']);
        add_action('ry_invoice-show_page-smilepay-option', [__CLASS__, 'pre_show_page']);
        add_action('admin_post_ry-invoice-smilepay-option', [__CLASS__, 'admin_action']);
    }

    public static function add_nav(array $navs): array
    {
        $navs[] = [
            'name' => __('SmilePay options', 'ry-invoice-for-smilepay'),
            'type' => 'smilepay-option',
        ];

        return $navs;
    }

    protected function do_init(): void {}

    public function output_page(): void
    {
        echo '<form method="post" action="admin-post.php">';
        echo '<input type="hidden" name="action" value="ry-invoice-smilepay-option">';
        wp_nonce_field('ry-invoice-smilepay-option');
        include __DIR__ . '/html/option.php';
        submit_button();
        echo '</form>';
    }

    public function do_admin_action(string $action): void
    {
        if ('ry-invoice-smilepay-option' !== $action) {
            return;
        }

        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'ry-invoice-smilepay-option')) {
            wp_die('Invalid nonce');
        }

        Main::update_option('log', Utils::bool_to_string($_POST['log'] ?? ''));
        $api_info = [
            'testmode' => Utils::bool_to_string($_POST['testmode'] ?? ''),
            'Grvc' => sanitize_locale_name($_POST['Grvc'] ?? ''),
            'VerifyKey' => sanitize_locale_name($_POST['VerifyKey'] ?? ''),
        ];
        Main::update_option('apiinfo', $api_info, false);
        $this->add_notice('success', __('Settings saved.', 'ry-invoice-for-smilepay'));

        wp_safe_redirect(admin_url('admin.php?page=ry-invoice&type=smilepay-option'));
    }
}
