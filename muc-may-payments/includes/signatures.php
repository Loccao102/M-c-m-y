<?php
/** VNPAY PAY 2.1.0 canonical query and HMAC helpers; no WordPress dependencies. */
if (!defined('ABSPATH') && !defined('MUC_MAY_TESTING')) { exit; }

function mm_vnpay_query(array $params) {
    unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);
    ksort($params, SORT_STRING);
    return http_build_query($params, '', '&', PHP_QUERY_RFC1738);
}

function mm_vnpay_sign(array $params, $secret) {
    return hash_hmac('sha512', mm_vnpay_query($params), (string) $secret);
}

function mm_vnpay_verified(array $params, $secret) {
    if (!$secret || empty($params['vnp_SecureHash']) || !is_string($params['vnp_SecureHash'])) {
        return false;
    }
    return hash_equals(strtolower(mm_vnpay_sign($params, $secret)), strtolower($params['vnp_SecureHash']));
}
