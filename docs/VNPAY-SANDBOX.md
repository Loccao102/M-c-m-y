# Mực Mây — Thanh toán WooCommerce (VNPAY Sandbox + COD)

Plugin mới nằm trong `muc-may-payments/`. Không chỉnh sửa theme, product SKU, database schema hoặc WooCommerce core.

## Cài đặt

1. Sao lưu website. WordPress → **Plugins → Add New → Upload Plugin** → chọn ZIP `muc-may-payments.zip` → **Activate**.
2. Plugin tự bật COD **chỉ khi COD chưa từng được cấu hình**. Nếu không thấy COD: **WooCommerce → Settings → Payments** → bật **Cash on delivery**.
3. Chọn tiền tệ **VND** tại **WooCommerce → Settings → General**.
4. Đăng ký thông tin VNPAY **Sandbox** tại https://sandbox.vnpayment.vn/devreg/. Nhà cung cấp phải cấp `TmnCode`, `HashSecret` và chấp nhận IPN URL.
5. Vào **WooCommerce → Mực Mây thanh toán** để xem URL IPN chính xác của website.
6. Chỉ nhập `TmnCode` và `HashSecret` trong **WooCommerce → Settings → Payments → Mực Mây — VNPAY QR (Sandbox)**. Nên đặt secret trong `wp-config.php` qua hai hằng `MUC_MAY_VNPAY_TMN_CODE`, `MUC_MAY_VNPAY_HASH_SECRET` (không commit secret lên GitHub).
7. Khai báo IPN URL trong dashboard VNPAY Sandbox và **chỉ bật cổng VNPAY sau khi IPN đã được cấu hình**. Plugin dùng HTTPS Sandbox cố định, không có chế độ production.
8. Thử đơn hàng trong Checkout Blocks: chọn VNPAY → chuyển tới trang Sandbox → dùng công cụ/thông tin kiểm thử của VNPAY → kiểm tra **WooCommerce → Orders**. Cổng chỉ đánh dấu đơn `paid` khi VNPAY gửi IPN ký hợp lệ. Lần quay về trình duyệt không xác nhận thanh toán.

## Kết nối

- Payment URL: `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html`
- Return: `https://mucmayyy.id.vn/?wc-api=mucmay_vnpay_return`
- IPN: `https://mucmayyy.id.vn/?wc-api=mucmay_vnpay_ipn`
- Cả hai callback cần HTTPS, phải được truy cập công khai và bỏ qua cache/WAF nếu có.

**Chú ý:** Nếu VNPAY gắn TMN với hostname/IPN cụ thể, phải đăng ký đúng URL trên bảng điều khiển merchant. Đây không phải luồng chuyển khoản VietQR tĩnh, QR được hiển thị tại trang thanh toán VNPAY.

## An toàn

- Plugin xác thực `vnp_SecureHash` bằng HMAC-SHA512, `TmnCode`, reference độc nhất của lần thanh toán, đơn hàng, tiền VND và số tiền trước khi cập nhật.
- IPN được xử lý idempotent. Không cập nhật trạng thái khi người dùng quay về trang cảm ơn.
- Secret không bao giờ xuất hiện trong giao diện chẩn đoán; không đưa mã Sandbox lên public repo.
- Không thực hiện giao dịch thật hoặc dùng secret production trên hệ thống này.
- VNPAY Sandbox có thể thay đổi nghiệp vụ. Chưa có merchant credentials nên chưa thể chạy thử qua cổng thật.

## Kiểm thử tự động

`php -l muc-may-payments/muc-may-payments.php`

`php -l muc-may-payments/includes/signatures.php`

`php tests/signatures-test.php`
