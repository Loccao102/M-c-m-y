<?php
// Run: php tests/signatures-test.php
const MUC_MAY_TESTING = true;
require __DIR__ . '/../muc-may-payments/includes/signatures.php';
$sample = array('vnp_TmnCode' => 'TEST1234', 'vnp_Amount' => '11500000', 'vnp_TxnRef' => '81-abcdefabcdef');
$signature = mm_vnpay_sign($sample, 'sandbox-only-key');
assert(strlen($signature) === 128);
$sample['vnp_SecureHash'] = $signature;
if (!mm_vnpay_verified($sample, 'sandbox-only-key')) { throw new RuntimeException('Signed payload rejected'); }
$sample['vnp_Amount'] = '11500001';
if (mm_vnpay_verified($sample, 'sandbox-only-key')) { throw new RuntimeException('Tampering accepted'); }
$sample['vnp_Amount'] = '11500000';
if (mm_vnpay_verified($sample, 'incorrect-key')) { throw new RuntimeException('Incorrect secret accepted'); }
echo "PASS: VNPAY HMAC-SHA512 valid, tampering and wrong-secret tests\n";
