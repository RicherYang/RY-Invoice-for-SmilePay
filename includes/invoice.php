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
            'SalesAmount' => round($invoice_data['total'], 0),
            'FreeTaxSalesAmount' => 0,
            'ZeroTaxSalesAmount' => 0,
            'TaxAmount' => -1,

            'Name' => __('Customer', 'ry-invoice-for-smilepay'),
            'Address' => $invoice_data['address'],
            'Email' => $invoice_data['email'],
            'CarrierType' => '',
            'CarrierID' => '',
            'CarrierID2' => '',
        ];

        switch ($invoice_data['type']) {
            case 'smilepay_host':
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
                $post_args['SalesAmount'] = round($post_args['AllAmount'] / 1.05, 0);
                $post_args['TaxAmount'] = $post_args['AllAmount'] - $post_args['SalesAmount'];
                $post_args['UnitTAX'] = 'Y';
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

            $name = mb_strimwidth(str_replace('|', '', strip_tags($invoice_item['name'])), 0, 80, '');
            $unit = mb_strimwidth(str_replace('|', '', strip_tags($invoice_item['unit'])), 0, 6, '');
            $qty = round($invoice_item['qty'], 3);
            $unit_price = round($invoice_item['total'] / $qty, 6);
            $total = round($qty * $unit_price, 0);

            $post_args['Description'][] = $name;
            $post_args['Quantity'][] = $qty;
            $post_args['UnitPrice'][] = $unit_price;
            $post_args['Unit'][] = $unit;
            $post_args['ProductTaxType'][] = 1;
            $post_args['Amount'][] = $total;
        }

        $item_total = array_sum($post_args['Amount']);
        if ($item_total !== $post_args['AllAmount']) {
            switch ($general_info['abnormal_mode']) {
                case 'order':
                    $post_args['AllAmount'] = $item_total;
                    if ($post_args['TaxAmount'] !== -1) {
                        $post_args['SalesAmount'] = round($post_args['AllAmount'] / 1.05, 0);
                        $post_args['TaxAmount'] = $post_args['AllAmount'] - $post_args['SalesAmount'];
                    }
                    break;
                case 'product':
                    $name = mb_strimwidth(str_replace('|', '', strip_tags($general_info['abnormal_product'])), 0, 80, '');
                    $unit = apply_filters('ry_invoice-item_unit_name', __('parcel', 'ry-invoice-for-smilepay'), $object_ID, 'abnormal');
                    $unit = mb_strimwidth(str_replace('|', '', $unit), 0, 6, '');

                    $post_args['Description'][] = $name;
                    $post_args['Quantity'][] = 1;
                    $post_args['UnitPrice'][] = $post_args['TotalAmount'] - $item_total;
                    $post_args['Unit'][] = $unit;
                    $post_args['ProductTaxType'][] = 1;
                    $post_args['Amount'][] = $post_args['TotalAmount'] - $item_total;
                    break;
            }
        }

        if ($post_args['TaxAmount'] === -1) {
            $post_args['TaxAmount'] = 0;
        }
        $post_args['MainRemark'] = apply_filters('ry_invoice-main_remark', $post_args['MainRemark'], $object_ID);
        $post_args['MainRemark'] = mb_strimwidth(strip_tags($post_args['MainRemark']), 0, 200, '');

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

        return array_merge([
            'abnormal_mode' => '',
            'abnormal_product' => __('Discount', 'ry-invoice-for-smilepay'),
        ], $general_info);
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
