<?php
/** VNPAY PAY 2.1.0 signing for payment URL & signed IPN/return. No WordPress dependencies. */
if (!defined('ABSPATH') && !defined('MMVNPAY_TESTING')) { exit; }

function mmvnpay_hash_data(array $params) {
    unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);
    ksort($params, SORT_STRING);
    $pairs = array();
    foreach ($params as $key => $value) {
        if (!is_scalar($value) || (string) $value === '') { continue; }
        // VNPAY PAY 2.1.0: sign the canonical URL-encoded query (RFC 1738).
        // This must match the bytes sent in the outgoing GET query string.
        $pairs[] = urlencode((string) $key) . '=' . urlencode((string) $value);
    }
    return implode('&', $pairs);
}
function mmvnpay_sign(array $params, $secret) {
    return hash_hmac('sha512', mmvnpay_hash_data($params), (string) $secret);
}
function mmvnpay_verify(array $params, $secret) {
    return (string) $secret !== '' && isset($params['vnp_SecureHash']) && is_string($params['vnp_SecureHash']) &&
        preg_match('/^[a-f0-9]{128}$/iD', $params['vnp_SecureHash']) === 1 &&
        hash_equals(strtolower(mmvnpay_sign($params, $secret)), strtolower($params['vnp_SecureHash']));
}
function mmvnpay_payment_url(array $params, $secret) {
    unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);
    $params['vnp_SecureHash'] = mmvnpay_sign($params, $secret);
    ksort($params, SORT_STRING);
    return 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?' . http_build_query($params, '', '&', PHP_QUERY_RFC1738);
}
