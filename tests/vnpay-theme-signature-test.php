<?php
define('MUC_MAY_TESTING', true);
require __DIR__ . '/../muc-may-studio/inc/payments/signatures.php';
$in = array('vnp_Amount'=>'1000000', 'vnp_TxnRef'=>'5-a1b2c3d4e5f6', 'vnp_TmnCode'=>'TEST1234');
$key = 'test-only-key-not-merchant-credentials';
$in['vnp_SecureHash'] = mucmay_vnpay_theme_sign($in, $key);
if (!mucmay_vnpay_theme_verify($in, $key)) { exit(1); }
$in['vnp_Amount'] = '2000000';
if (mucmay_vnpay_theme_verify($in, $key)) { exit(2); }
if (mucmay_vnpay_theme_verify($in, 'other-key')) { exit(3); }
echo "VNPAY signature tests PASS\n";
