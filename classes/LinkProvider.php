<?php

namespace RY\Invoice\Smilepay;

defined('ABSPATH') or exit;

use RY\General\V20260801\Logs;
use RY\General\V20260801\Utils;
use RY\Invoice\V20260805\AbstractLinkProvider;

final class LinkProvider extends AbstractLinkProvider
{
    private static ?self $_instance = null;

    private array $api_test_url = [
        'get' => 'https://ssl.smse.com.tw/api_test/SPEinvoice_Storage.asp',
        'invalid' => 'https://ssl.smse.com.tw/api_test/SPEinvoice_Storage_Modify.asp',
    ];

    private array $api_url = [
        'get' => 'https://ssl.smse.com.tw/api/SPEinvoice_Storage.asp',
        'invalid' => 'https://ssl.smse.com.tw/api/SPEinvoice_Storage_Modify.asp',
    ];

    public static function instance(): LinkProvider
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void {}

    public function get_invoice($invoice_data, $object_ID)
    {
        $general_info = $this::get_info();
        $api_info = $this->get_api_info();

        $now = new \DateTime('now', new \DateTimeZone('Asia/Taipei'));
        $post_args = [
            'InvoiceDate' => $now->format('Y/m/d'),
            'InvoiceTime' => $now->format('H:i:s'),
            'TrackSystemID' => $invoice_data['trackcode'],
            'Intype' => '07',
            'TaxType' => 1,
            'TaxRate' => 0.05,
            'MainRemark' => '#' . $invoice_data['no'],
            'DonateMark' => 0,
            'orderid' => $this->generate_trade_no($object_ID, $invoice_data['prefix']),

            'Description' => [],
            'Quantity' => [],
            'UnitPrice' => [],
            'Unit' => [],
            'ProductTaxType' => [],
            'Amount' => [],
            'AllAmount' => round($invoice_data['total'], 0),
            'SalesAmount' => 0,
            'FreeTaxSalesAmount' => 0,
            'ZeroTaxSalesAmount' => 0,
            'UnitTAX' => 'Y',
            'TaxAmount' => 0,

            'Name' => __('Customer', 'ry-invoice-for-smilepay'),
            'Address' => __('Taiwan', 'ry-invoice-for-smilepay'),
            'Email' => $invoice_data['email'],
            'CarrierType' => '',
            'CarrierID' => '',
            'CarrierID2' => '',
        ];

        switch ($invoice_data['type']) {
            case 'host':
                $post_args['CarrierType'] = 'EJ0113';
                break;
            case 'MOICA':
                $post_args['CarrierType'] = 'CQ0001';
                $post_args['CarrierID'] = $invoice_data['moica_no'];
                break;
            case 'phone_barcode':
                $post_args['CarrierType'] = '3J0002';
                $post_args['CarrierID'] = $invoice_data['phone_barcode'];
                break;
            case 'company':
                unset($post_args['Name']);
                $post_args['Buyer_id'] = $invoice_data['tax_no'];
                $post_args['CompanyName'] = $invoice_data['tax_name'];
                if (empty($post_args['CompanyName'])) {
                    $post_args['CompanyName'] = $post_args['Buyer_id'];
                }
                break;
            case 'donate':
                $post_args['DonateMark'] = 1;
                $post_args['LoveKey'] = $invoice_data['donate_no'];
                break;
        }

        foreach ($invoice_data['item'] as $invoice_item) {
            if ($invoice_item['qty'] == 0 && $invoice_item['total'] == 0) {
                continue;
            }
            if ($invoice_item['qty'] == 0) {
                $invoice_item['qty'] = 1;
            }

            $name = mb_strimwidth($this->clean_string($invoice_item['name']), 0, 80, '');
            $unit = mb_strimwidth($this->clean_string($invoice_item['unit']), 0, 6, '');
            $qty = round($invoice_item['qty'], $general_info['count_precision']);
            $unit_price = round($invoice_item['total'] / $qty, $general_info['amount_precision']);
            $total = round($unit_price * $qty, $general_info['amount_precision']);

            match($invoice_item['tax']) {
                1 => $post_args['SalesAmount'] += $total,
            };
            $post_args['Description'][] = $name;
            $post_args['Quantity'][] = $qty;
            $post_args['UnitPrice'][] = $unit_price;
            $post_args['Unit'][] = $unit;
            $post_args['ProductTaxType'][] = $invoice_item['tax'];
            $post_args['Amount'][] = $total;
        }

        if (isset($post_args['Buyer_id'])) {
            $post_args['SalesAmount'] = round($post_args['SalesAmount'] / 1.05, 0);
        } else {
            $post_args['SalesAmount'] = round($post_args['SalesAmount'], 0);
        }
        $amount = $post_args['SalesAmount'] + $post_args['FreeTaxSalesAmount'] + $post_args['ZeroTaxSalesAmount'];
        $post_args['TaxAmount'] = $post_args['AllAmount'] - $amount;

        $post_args['MainRemark'] = apply_filters('ry_invoice-main_remark', $post_args['MainRemark'], $object_ID);
        $post_args['MainRemark'] = mb_strimwidth($this->clean_string($post_args['MainRemark']), 0, 200, '');

        foreach ($post_args as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $sub_key => $sub_value) {
                    if (is_int($sub_value) || is_float($sub_value)) {
                        $post_args[$key][$sub_key] = (string) $sub_value;
                    }
                }
                $post_args[$key] = implode('|', $post_args[$key]);
            }
            if (is_int($value) || is_float($value)) {
                $post_args[$key] = (string) $value;
            }
        }

        if ($api_info['testmode']) {
            $post_url = $this->api_test_url['get'];
        } else {
            $post_url = $this->api_url['get'];
        }

        do_action('ry_invoice_smilepay-pre_get_invoice', $post_args, $object_ID);
        Logs::log('smilepay-invoice', 'info', 'Get LINK #' . $object_ID, $post_args);
        $result = $this->link_server($post_url, $post_args, $api_info['Grvc'], $api_info['VerifyKey']);
        if ($result) {
            Logs::log('smilepay-invoice', 'info', 'Get response #' . $object_ID, $result);
            do_action('ry_invoice_smilepay-post_get_invoice', $post_args, $result, $object_ID);
        }
    }

    public function invalid_invoice($invoice_data, $object_ID = null)
    {
        $api_info = $this->get_api_info();

        $post_args = [
            'InvoiceNumber' => $invoice_data['no'],
            'InvoiceDate' => str_replace('-', '/', substr($invoice_data['date'], 0, 10)),
            'types' => 'Cancel',
            'CancelReason' => __('Order cancel', 'ry-invoice-for-smilepay'),
        ];

        if ($api_info['testmode']) {
            $post_url = $this->api_test_url['invalid'];
        } else {
            $post_url = $this->api_url['invalid'];
        }

        do_action('ry_invoice_smilepay-pre_invalid_invoice', $post_args, $object_ID);
        Logs::log('smilepay-invoice', 'info', 'Invalid LINK #' . $object_ID, $post_args);
        $result = $this->link_server($post_url, $post_args, $api_info['Grvc'], $api_info['VerifyKey']);
        if ($result) {
            Logs::log('smilepay-invoice', 'info', 'Invalid response #' . $object_ID, $result);
            do_action('ry_invoice_smilepay-post_invalid_invoice', $post_args, $result, $object_ID);
        }
    }

    public function get_api_info()
    {
        $api_info = Main::get_option('apiinfo', []);
        if (!is_array($api_info)) {
            $api_info = [];
        }
        $api_info = array_merge([
            'testmode' => 'no',
            'Grvc' => '',
            'VerifyKey' => '',
        ], $api_info);
        $api_info['testmode'] = Utils::string_to_bool($api_info['testmode']);

        return $api_info;
    }

    protected function generate_trade_no($object_ID, $order_prefix = '')
    {
        $trade_no = parent::generate_trade_no($object_ID, $order_prefix);
        $trade_no = apply_filters('ry_invoice_smilepay-trade_no', $trade_no, $object_ID, $order_prefix);

        return substr($trade_no, 0, 18);
    }

    protected function link_server(string $url, array $args, string $Grvc, string $VerifyKey, int $timeout = 30)
    {
        @set_time_limit(40);

        $args['Grvc'] = $Grvc;
        $args['Verify_key'] = $VerifyKey;
        $response = wp_remote_post($url, [
            'timeout' => $timeout,
            'body' => $args,
            'user-agent' => apply_filters('http_headers_useragent', 'WordPress/' . get_bloginfo('version')),
        ]);

        if (is_wp_error($response)) {
            Logs::log('smilepay-invoice', 'error', 'Link failed', $response->get_error_messages());
            return;
        }

        if (wp_remote_retrieve_response_code($response) != 200) {
            Logs::log('smilepay-invoice', 'error', 'Link HTTP status error', ['status' => wp_remote_retrieve_response_code($response)]);
            return;
        }

        $result = @simplexml_load_string(wp_remote_retrieve_body($response));

        if (!is_object($result)) {
            Logs::log('smilepay-invoice', 'error', 'Link response parse failed', ['response' => wp_remote_retrieve_body($response)]);
            return;
        }

        return $result;
    }
}
