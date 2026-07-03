<?php

abstract class RY_IFSMILEPAY_Abstract_Invoice
{
    protected function generate_trade_no($object_ID, $order_prefix = '')
    {
        $trade_no = $order_prefix . $object_ID . 'T' . random_int(0, 9) . strrev((string) time());
        $trade_no = apply_filters('ry_invoice_smilepay-trade_no', $trade_no, $object_ID, $order_prefix);

        return substr($trade_no, 0, 18);
    }

    protected function link_server(string $url, array $args, string $Grvc, string $VerifyKey, int $timeout = 30)
    {
        wc_set_time_limit(40);

        $args['Grvc'] = $Grvc;
        $args['Verify_key'] = $VerifyKey;
        $response = wp_remote_post($url, [
            'timeout' => $timeout,
            'body' => $args,
            'user-agent' => apply_filters('http_headers_useragent', 'WordPress/' . get_bloginfo('version')),
        ]);

        if (is_wp_error($response)) {
            RY_Logs::log('smilepay-invoice', 'error', 'Link failed', $response->get_error_messages());
            return;
        }

        if (wp_remote_retrieve_response_code($response) != 200) {
            RY_Logs::log('smilepay-invoice', 'error', 'Link HTTP status error', ['status' => wp_remote_retrieve_response_code($response)]);
            return;
        }

        $result = @simplexml_load_string(wp_remote_retrieve_body($response));

        if (!is_object($result)) {
            RY_Logs::log('smilepay-invoice', 'error', 'Link response parse failed', ['response' => wp_remote_retrieve_body($response)]);
            return;
        }

        return $result;
    }
}
