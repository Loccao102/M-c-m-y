# Mực Mây VNPAY Sandbox plugin (WooCommerce)

**Bản mới nhất: 1.0.1 — sửa lỗi “Sai chữ ký” của VNPAY.**

- [Tải ZIP mới nhất](../dist/muc-may-vnpay.zip)
- [Bản ZIP có gắn phiên bản](../dist/muc-may-vnpay-v1.0.1.zip)

## Cài đặt bản sửa

1. Sao lưu WordPress, sau đó vào **Plugins → Add New → Upload Plugin** và chọn ZIP 1.0.1.
2. Khi WordPress báo plugin đã tồn tại, chọn **Replace current with uploaded** (thay thế phiên bản hiện tại). Không cần xóa plugin trước.
3. Trong **WooCommerce → Settings → Payments → Mực Mây — VNPAY Sandbox**, kiểm tra TmnCode và HashSecret của **cùng merchant Sandbox** rồi lưu lại. Bật phương thức nếu đang tắt.
4. WooCommerce phải dùng **VND**. Chỉ giữ một cổng VNPAY đang hoạt động.
5. Tạo **đơn hàng mới** và thanh toán thử; không sử dụng URL thanh toán từ đơn cũ vì chữ ký cũ không thể sửa sau khi tạo.

## Nguyên nhân lỗi

Bản 1.0.0 tạo HMAC-SHA512 từ chuỗi tham số không encode. Theo ví dụ PHP VNPAY PAY 2.1.0, phải sắp xếp tham số alphabet và `urlencode` key/value trước khi ký; query gửi đi dùng form encoding RFC 1738. Bản 1.0.1 đã sửa theo chuẩn đó.

Sau khi cập nhật, nếu VNPAY vẫn báo **Sai chữ ký**, hãy kiểm tra chính xác cặp **TmnCode/HashSecret của môi trường Sandbox**, không dùng secret của merchant khác hoặc production, và xem có hằng `MUC_MAY_VNPAY_TMN_CODE`, `MUC_MAY_VNPAY_HASH_SECRET` trong wp-config.php đang ghi đè giá trị trong admin hay không.

Plugin **không đọc tự động** các khóa `VNP_TMN_CODE=...`, `VNP_HASH_SECRET=...`, `VNP_RETURN_URL=...` từ file .env. Đây chỉ là ví dụ cấu hình khác, không phải các trường của plugin WordPress.

## Callback

- Payment Sandbox: `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html`
- IPN: `https://mucmayyy.id.vn/?wc-api=mmvnpay_ipn`
- Return: `https://mucmayyy.id.vn/?wc-api=mmvnpay_return`

Return URL được plugin tự gắn khi tạo giao dịch. Không đặt `http://localhost:8000/vnpay-return` trên website Mực Mây chạy ở Internet. Return URL chỉ hiển thị trạng thái cho người dùng; IPN hợp lệ mới đánh dấu thanh toán trong WooCommerce.

Chỉ sử dụng **Sandbox**, không dùng cho tiền thật. Mã merchant bí mật phải được giữ kín, không commit lên GitHub.

Nguồn kỹ thuật: https://sandbox.vnpayment.vn/apis/docs/chuyen-doi-thuat-toan/changeTypeHash.html
