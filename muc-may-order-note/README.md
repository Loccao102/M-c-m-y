# Mực Mây — Trang xác nhận đơn hàng

Plugin WooCommerce tạo trang xác nhận đơn hàng theo nhận diện Mực Mây: tấm thư cảm ơn, họa tiết phong bì, chi tiết sản phẩm, tổng tiền, địa chỉ giao hàng và nút mua sắm.

- Hoạt động cùng plugin thanh toán VNPAY hiện tại; **không sửa trạng thái đơn và không dùng Return URL để đánh dấu đã thanh toán**.
- Giữ việc WooCommerce kiểm tra quyền truy cập đơn hàng, email xác minh cho khách vãng lai.
- Trạng thái hiển thị lấy từ `$order->is_paid()` và `$order->get_status()`, không tin query string.
- Có giao diện pending/failed/paid; không hiện nút thanh toán lại khi đã thanh toán.
- Hỗ trợ WooCommerce Classic Checkout / trang `checkout/thankyou.php`. Nếu website dùng Checkout Blocks với template Order Confirmation riêng, WooCommerce có thể cần chuyển phần Order Confirmation sang mẫu Classic trước khi dùng plugin này.

## Cài đặt

1. WordPress → Plugins → Add New → Upload Plugin.
2. Chọn ZIP `muc-may-order-note.zip`, bấm Install Now rồi Activate.
3. Không phải tắt plugin VNPAY; plugin này chỉ thay trang cảm ơn WooCommerce Classic.
4. Thử đặt một đơn hàng mới hoặc mở lại URL `order-received/{id}/?key=...` hợp lệ.
5. Nếu không thấy thay đổi, kiểm tra xem trang Checkout có sử dụng WooCommerce Checkout Blocks hay Classic, và xóa cache trang nếu có.

**Không sửa WooCommerce core, không can thiệp thanh toán hoặc thông tin merchant.**
