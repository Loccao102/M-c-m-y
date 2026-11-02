# Mực Mây VNPAY Sandbox plugin (WooCommerce)

Plugin ZIP cài đặt: [`dist/muc-may-vnpay.zip`](../dist/muc-may-vnpay.zip)

Phiên bản: 1.0.0. Chỉ VNPAY PAY 2.1.0 **Sandbox**, không dùng giao dịch tiền thật.
Độc lập với theme, hỗ trợ WooCommerce Classic Checkout và Checkout Blocks.

## Cài đặt

1. Sao lưu website và WooCommerce.
2. Tắt `vnpayInst` và plugin `muc-may-payments` cũ nếu đang được bật để tránh nhiều phương thức VNPAY.
3. WordPress → Plugins → Add New → Upload Plugin → tải `dist/muc-may-vnpay.zip` → Activate.
4. WooCommerce → Settings → General: chọn tiền tệ VND.
5. WooCommerce → Mực Mây · VNPAY → Cấu hình, nhập `TmnCode` và `HashSecret` Sandbox, bật cổng.
6. Đăng ký IPN trong trang VNPAY merchant Sandbox:
   `https://mucmayyy.id.vn/?wc-api=mmvnpay_ipn`.
7. Return URL: `https://mucmayyy.id.vn/?wc-api=mmvnpay_return`.
8. Thử đặt hàng và xác nhận trạng thái ở WooCommerce → Orders.

IPN ký hợp lệ mới được xác nhận thanh toán. Không commit khóa bí mật vào GitHub.
Để kiểm tra mã nguồn, giải nén gói ZIP trong `dist/`.

Tài liệu: https://sandbox.vnpayment.vn/apis/files/VNPAY%20Payment%20Gateway_Techspec%20Post%20method%202.1.0-VN.pdf
