<?php

defined('ABSPATH') or exit;

final class RY_IFSMILEPAY_Invoice extends RY_IFSMILEPAY_Abstract_Invoice
{
    protected static ?self $_instance = null;

    private array $api_test_url = [
        'get' => 'https://ssl.smse.com.tw/api_test/SPEinvoice_Storage.asp',
        'invalid' => 'https://ssl.smse.com.tw/api_test/SPEinvoice_Storage_Modify.asp',
    ];

    private array $api_url = [
        'get' => 'https://ssl.smse.com.tw/api/SPEinvoice_Storage.asp',
        'invalid' => 'https://ssl.smse.com.tw/api/SPEinvoice_Storage_Modify.asp',
    ];

    public static function instance(): RY_IFSMILEPAY_Invoice
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
        $general_info = $this->get_info();
        $api_info = $this->get_api_info();

        $now = new DateTime('now', new DateTimeZone('Asia/Taipei'));
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
                $post_args['UnitTAX'] = 'N';
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

            $name = mb_strimwidth(str_replace('|', '', wp_strip_all_tags($invoice_item['name'])), 0, 80, '');
            $unit = mb_strimwidth(str_replace('|', '', wp_strip_all_tags($invoice_item['unit'])), 0, 6, '');
            $qty = round($invoice_item['qty'], $general_info['count_precision']);
            $total = $invoice_item['total'];
            if ($post_args['UnitTAX'] === 'N') {
                $total = round($total / 1.05, 0);
                $unit_price = round($total / $qty, $general_info['count_precision']);
                $total = round($unit_price * $qty, $general_info['count_precision']);
            } else {
                $unit_price = round($total / $qty, $general_info['count_precision']);
                $total = round($unit_price * $qty, $general_info['count_precision']);
            }

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

        $post_args['SalesAmount'] = round($post_args['SalesAmount'], 0);
        $amount = round($post_args['SalesAmount'] + $post_args['FreeTaxSalesAmount'] + $post_args['ZeroTaxSalesAmount'], 0);
        $post_args['TaxAmount'] = $post_args['AllAmount'] - $amount;

        $post_args['MainRemark'] = apply_filters('ry_invoice-main_remark', $post_args['MainRemark'], $object_ID);
        $post_args['MainRemark'] = mb_strimwidth(wp_strip_all_tags($post_args['MainRemark']), 0, 200, '');

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
        RY_Logs::log('smilepay-invoice', 'info', 'Get LINK #' . $object_ID, $post_args);
        $result = $this->link_server($post_url, $post_args, $api_info['Grvc'], $api_info['VerifyKey']);
        if ($result) {
            RY_Logs::log('smilepay-invoice', 'info', 'Get response #' . $object_ID, $result);
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
        RY_Logs::log('smilepay-invoice', 'info', 'Invalid LINK #' . $object_ID, $post_args);
        $result = $this->link_server($post_url, $post_args, $api_info['Grvc'], $api_info['VerifyKey']);
        if ($result) {
            RY_Logs::log('smilepay-invoice', 'info', 'Invalid response #' . $object_ID, $result);
            do_action('ry_invoice_smilepay-post_invalid_invoice', $post_args, $result, $object_ID);
        }
    }

    public function get_info()
    {
        $general_info = RY_IFSMILEPAY::get_option('general', []);
        if (!is_array($general_info)) {
            $general_info = [];
        }

        $general_info = array_merge([
            'count_precision' => 3,
            'amount_precision' => 7,
        ], $general_info);
        $general_info['count_precision'] = (int) $general_info['count_precision'];
        $general_info['amount_precision'] = (int) $general_info['amount_precision'];

        return $general_info;
    }

    public function get_api_info()
    {
        $api_info = RY_IFSMILEPAY::get_option('apiinfo', []);
        if (!is_array($api_info)) {
            $api_info = [];
        }
        $api_info = array_merge([
            'testmode' => 'no',
            'Grvc' => '',
            'VerifyKey' => '',
        ], $api_info);
        $api_info['testmode'] = $api_info['testmode'] === 'yes';

        if ($api_info['testmode'] === true) {
            $api_info['Grvc'] = 'SEI1000034';
            $api_info['VerifyKey'] = '9D73935693EE0237FABA6AB744E48661';
        }

        return $api_info;
    }
}
