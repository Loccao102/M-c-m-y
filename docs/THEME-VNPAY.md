# Mực Mây Studio - Online payment (VNPAY Sandbox)

Payment is integrated into the theme at muc-may-studio/inc/payments/vnpay.php, without activating the old payment plugin.
Only VNPAY Sandbox is supported. Do not use production credentials.

Setup:
1. Enable WooCommerce and set currency to VND.
2. Register Sandbox merchant at https://sandbox.vnpayment.vn/devreg/ to get TmnCode and HashSecret.
3. WordPress > WooCommerce > Mực Mây · VNPAY > Cấu hình VNPAY Sandbox.
4. Enter credentials, enable gateway, save.
5. Configure the IPN URL displayed in WordPress at the merchant dashboard.
6. Test checkout with Sandbox test payment method. Confirm IPN marks order paid.

Never commit the merchant HashSecret. Optionally configure WordPress wp-config.php constants
MUC_MAY_VNPAY_TMN_CODE and MUC_MAY_VNPAY_HASH_SECRET.

COD, BACS and cheque are disabled while this theme is active.
Missing merchant keys means no checkout gateway appears; this fails closed.
The signed IPN, not the browser return, marks an order as paid.
Do not reactivate old plugin muc-may-payments, which defines a second VNPAY gateway.
Theme changes disable pending callbacks, so keep this theme active during payment testing.
Review this integration before any real-money production use.

Official docs: https://sandbox.vnpayment.vn/apis/docs/thanh-toan-pay/pay.html