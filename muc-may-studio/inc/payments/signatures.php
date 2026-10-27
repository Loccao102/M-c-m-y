<?php
/** VNPAY PAY 2.1.0 HMAC helpers; these can be tested without WordPress. */
if ( ! defined('ABSPATH') && ! defined('MUC_MAY_TESTING') ) { exit; }
function mucmay_vnpay_theme_query(array $data) {
    unset($data['vnp_SecureHash'], $data['vnp_SecureHashType']);
    ksort($data, SORT_STRING);
    return http_build_query($data, '', '&', PHP_QUERY_RFC1738);
}
function mucmay_vnpay_theme_sign(array $data, $secret) {
    return hash_hmac('sha512', mucmay_vnpay_theme_query($data), (string) $secret);
}
function mucmay_vnpay_theme_verify(array $data, $secret) {
    return !empty($secret) && isset($data['vnp_SecureHash']) && is_string($data['vnp_SecureHash']) &&
        hash_equals(strtolower(mucmay_vnpay_theme_sign($data, $secret)), strtolower($data['vnp_SecureHash']));
}
