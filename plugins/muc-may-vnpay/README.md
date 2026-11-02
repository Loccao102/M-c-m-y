# Mực Mây — VNPAY Sandbox cho WooCommerce

Plugin độc lập cho **WooCommerce Classic Checkout + Checkout Blocks**, dùng VNPAY PAY **2.1.0**, chỉ trên môi trường **Sandbox**.

## Cài đặt

1. **Sao lưu** website WordPress và dữ liệu WooCommerce.
2. Vào **Plugins → Installed Plugins**: tắt plugin `Mực Mây Payments — VNPAY Sandbox + COD` cũ (nếu có) và các plugin VNPAY khác nếu không muốn hiện nhiều lựa chọn. Trong hình hiện tại, `vnpayInst` là một cổng khác; hãy tắt cổng đó khi dùng plugin này.
3. Vào **Plugins → Add New → Upload Plugin**, tải `muc-may-vnpay.zip`, chọn **Install Now → Activate**. Chỉ tải ZIP ngoài cùng, không chọn thư mục `includes/`.
4. Đặt WooCommerce **Currency: VND** trong **WooCommerce → Settings → General**.
5. Truy cập **WooCommerce → Mực Mây · VNPAY** hoặc **WooCommerce → Settings → Payments → Mực Mây — VNPAY Sandbox → Manage/Set up**.
6. Nhập `TmnCode` và `HashSecret` **Sandbox** nhận từ https://sandbox.vnpayment.vn/devreg/ . Bật thanh toán, lưu.
7. Đăng ký URL **IPN** trên merchant dashboard VNPAY. Cần đảm bảo máy chủ nhận HTTPS GET từ ngoài Internet, không bị cache/WAF chặn.
8. Tạo đơn hàng và thanh toán thử. Chỉ callback IPN có chữ ký hợp lệ mới gọi `$order->payment_complete()`.

## URL cấu hình tại site Mực Mây

- **Payment URL Sandbox (cố định):** `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html`
- **IPN URL:** `https://mucmayyy.id.vn/?wc-api=mmvnpay_ipn`
- **Return URL:** `https://mucmayyy.id.vn/?wc-api=mmvnpay_return`

**Lưu ý**: WooCommerce → Mực Mây · VNPAY hiển thị chính xác 2 URL dựa trên `home_url()` của site đã cài, hãy ưu tiên copy từ đây.

## An toàn và vận hành

- Chỉ Sandbox, không có nút chuyển sang production.
- `TmnCode`, `HashSecret` không được ghi vào GitHub hoặc log. Có thể đặt trong `wp-config.php`:

  ```php
  define('MUC_MAY_VNPAY_TMN_CODE', 'SANDBOX_TMN_CODE');
  define('MUC_MAY_VNPAY_HASH_SECRET', 'SANDBOX_HASH_SECRET');
  ```

- HMAC-SHA512, kiểm tra `TmnCode`, `TxnRef` theo thứ tự chữ/số, số tiền = tổng đơn × 100, đơn hàng, trạng thái và số giao dịch.
- Không coi Return URL (trình duyệt khách) là chứng minh đã thanh toán.
- IPN lặp lại trên đơn đã thanh toán trả về `RspCode=02`.
- Mã giao dịch cho mỗi lần bấm thanh toán là duy nhất; lưu danh sách attempt trong WooCommerce order meta để xử lý được IPN đến muộn.
- Nếu đã hủy/hoàn tiền mà nhận thêm IPN, cần kiểm tra thủ công tại VNPAY merchant portal.
- Chưa có truy vấn `querydr`, hoàn tiền `refund` hoặc production. Không dùng plugin này để nhận tiền thật.
- Plugin này không tắt COD hay các cổng thanh toán khác: chủ website tự bật/tắt ở WooCommerce → Settings → Payments.
- Theme Mực Mây hiện có một cổng VNPAY Sandbox nội bộ; plugin cố gắng lọc cổng này khỏi Classic Checkout. Nếu cổng theme đã được cấu hình và vẫn xuất hiện ở Checkout Blocks, hãy tắt nó trong WooCommerce hoặc vô hiệu hóa tích hợp VNPAY của theme sau khi cập nhật theme. Chỉ để **một** cổng VNPAY hoạt động khi test.

## Sửa lỗi 1.0.1 — Sai chữ ký

VNPAY 2.1.0 cần ký trên chuỗi tham số sau khi URL-encode (RFC 1738) giống query gửi đến VNPAY. Bản 1.0.0 ký trên chuỗi thô nên có thể bị cổng từ chối. Nâng cấp 1.0.1, giữ nguyên cấu hình TmnCode/HashSecret đã nhập rồi tạo **đơn hàng mới** để test (không dùng lại URL thanh toán cũ).

Nếu vẫn lỗi, hãy kiểm tra TmnCode và HashSecret **Sandbox** đúng cặp trong WooCommerce → Mực Mây · VNPAY. Plugin đọc các giá trị tại trang cấu hình đó hoặc các hằng `MUC_MAY_VNPAY_TMN_CODE` và `MUC_MAY_VNPAY_HASH_SECRET` trong `wp-config.php`; các dòng `VNP_TMN_CODE=...` trong `.env` không tự động được plugin WordPress đọc.

Return URL cho website: `https://mucmayyy.id.vn/?wc-api=mmvnpay_return`, do plugin tự tạo. Return URL không thay thế IPN.

## Kiểm thử

```bash
php -l muc-may-vnpay/muc-may-vnpay.php
php -l muc-may-vnpay/includes/signature.php
php -l muc-may-vnpay/includes/class-gateway.php
php tests/vnpay-sandbox-signatures.php
```

Thử thành công, thất bại/hủy, IPN lặp, IPN sai chữ ký, sai tiền, sai mã đơn, và trở về từ Return trước IPN.

Tài liệu: https://sandbox.vnpayment.vn/apis/files/VNPAY%20Payment%20Gateway_Techspec%20Post%20method%202.1.0-VN.pdf

## Sandbox Return confirmation — v1.0.2

Khi tài khoản Merchant Sandbox không thể đăng ký IPN, plugin có thể xác nhận từ **Return URL có chữ ký hợp lệ**. Chế độ này mặc định bật và có thể tắt trong WooCommerce → Settings → Payments → Mực Mây — VNPAY Sandbox → Manage → **Xác nhận qua Return (Sandbox)**.

- Không phải cứ khách quay lại website là đã thanh toán. Plugin kiểm tra `vnp_SecureHash`, `TmnCode`, `TxnRef`, VND/Amount, trạng thái `00/00`, mã giao dịch hợp lệ và đúng lần thanh toán mới nhất.
- Khi hợp lệ, gọi WooCommerce `payment_complete()` và trang cảm ơn hiển thị kết quả thanh toán rõ ràng; giao dịch lỗi/hủy giữ trạng thái chưa thanh toán.
- IPN cũ vẫn hoạt động. Khi bắt đầu dùng tiền thật, **tắt chế độ Return confirmation**, đăng ký IPN/QueryDR độc lập với trình duyệt và kiểm thử đối soát. Không dùng plugin Sandbox cho production.
- Bản v1.0.2 bổ sung giao diện trang xác nhận theo Mực Mây Studio, responsive. Không cần thay đổi theme.
- Đơn hàng đã ở trạng thái `Pending` từ bản cũ không tự được xác nhận lại khi nâng cấp. Đối chiếu mã giao dịch trên Merchant Sandbox rồi cập nhật thủ công, hoặc thử bằng đơn mới.
