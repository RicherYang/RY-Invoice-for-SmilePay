<?php

defined('ABSPATH') or exit;

use RY\General\AbstractAdminPage;

final class RY_IFSMILEPAY_Admin_Page_Option extends AbstractAdminPage
{
    protected static $_instance = null;

    public static function init_menu(): void
    {
        add_filter('ry-invoice-navs', [__CLASS__, 'add_nav']);
        add_submenu_page('', __('SmilePay options', 'ry-invoice-for-smilepay'), '', 'manage_options', 'ry-invoice-smilepay-option', [__CLASS__, 'pre_show_page']);
        add_action('load-admin_page_ry-invoice-smilepay-option', [__CLASS__, 'instance']);
        add_action('admin_post_ry-invoice-smilepay-option', [__CLASS__, 'admin_action']);
    }

    public static function add_nav(array $navs): array
    {
        $navs[] = [
            'name' => __('SmilePay options', 'ry-invoice-for-smilepay'),
            'slug' => 'ry-invoice-smilepay-option',
        ];

        return $navs;
    }

    protected function do_init(): void
    {
        global $_wp_menu_nopriv, $_wp_real_parent_file, $submenu_file;

        if ($_wp_menu_nopriv) {
            $_wp_menu_nopriv['ry-invoice-smilepay-option'] = true;
            $_wp_real_parent_file['ry-invoice-smilepay-option'] = RY_IFSMILEPAY_Admin::instance()->main_slug;
            $submenu_file = 'ry-invoice';
        }
    }

    public function output_page(): void
    {
        echo '<div class="wrap">';

        $show_type = 'ry-invoice-smilepay-option';
        include RY_IFSMILEPAY_PLUGIN_DIR . 'admin/page/html/nav.php';

        echo '<form method="post" action="admin-post.php">';
        echo '<input type="hidden" name="action" value="ry-invoice-smilepay-option">';
        wp_nonce_field('ry-invoice-smilepay-option');
        include RY_IFSMILEPAY_PLUGIN_DIR . 'admin/page/html/option.php';
        submit_button();
        echo '</form>';

        echo '</div>';
    }

    public function do_admin_action(string $action): void
    {
        if ('ry-invoice-smilepay-option' !== $action) {
            return;
        }

        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'ry-invoice-smilepay-option')) {
            wp_die('Invalid nonce');
        }

        $log = sanitize_locale_name($_POST['log'] ?? '') === 'yes' ? 'yes' : 'no';
        RY_IFSMILEPAY::update_option('log', $log);
        $api_info = [
            'testmode' => sanitize_locale_name($_POST['testmode'] ?? '') === 'yes' ? 'yes' : 'no',
            'Grvc' => sanitize_locale_name($_POST['Grvc'] ?? ''),
            'VerifyKey' => sanitize_locale_name($_POST['VerifyKey'] ?? ''),
        ];
        RY_IFSMILEPAY::update_option('apiinfo', $api_info, false);
        $this->add_notice('success', __('Settings saved.', 'ry-invoice-for-smilepay'));

        wp_safe_redirect(admin_url('admin.php?page=ry-invoice-smilepay-option'));
    }
}

RY_IFSMILEPAY_Admin_Page_Option::init_menu();
