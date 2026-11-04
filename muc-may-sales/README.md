# Mực Mây — Thống kê mua & bán (v1.1.0)

Plugin WooCommerce độc lập với theme, dùng API WooCommerce để đọc số liệu thật, tương thích cách lưu đơn HPOS.

## Hiển thị

- Trang chủ Mực Mây: ghi chú **Đã bán N** trong thẻ sản phẩm, đặt dưới giá.
- Trang cửa hàng, danh mục: ghi chú **Đã bán N** bên dưới giá sản phẩm.
- Trang chi tiết: ghi chú lớn hơn bên dưới giá.
- WooCommerce Admin → **Mực Mây · Thống kê**: tổng số **đơn ở trạng thái đã thanh toán**, tổng **số lượng sản phẩm đã bán** và bảng 5 sản phẩm bán nhiều nhất.

`get_total_sales()` là số đơn vị sản phẩm bán ra, không phải số người mua hoặc số lần truy cập. Đơn chờ thanh toán không được tính là đơn đã thanh toán. Lượt xem trang/lượt khách không được ghi nhận.

## Cài đặt

1. Sao lưu WordPress; giữ plugin VNPAY và plugin trang cảm ơn hiện tại.
2. Plugins → Add New → Upload Plugin → chọn `muc-may-sales.zip` → Install Now → Activate.
3. Vào `/wp-admin/admin.php?page=mucmay-sales` để xem thống kê.
4. Ghé trang chủ và trang chi tiết sản phẩm; nếu dùng LiteSpeed Cache, xóa cache của website.

Plugin chỉ đọc dữ liệu WooCommerce, không tạo bảng DB, không sửa trạng thái đơn, không tự tạo lượt bán. Hệ thống **chưa triển khai tự động** trên iNET khi bạn push GitHub; cần cài ZIP như trên hoặc thiết lập SFTP riêng.

## Điểm mới v1.1.0

- Cửa hàng / danh mục có lựa chọn **Bán chạy nhất (theo lượt bán)** trong ô sắp xếp WooCommerce. Đây là chế độ sắp xếp `popularity` chính thức của WooCommerce (từ nhiều đến ít).
- Nhãn **ĐÃ BÁN N** được tăng độ tương phản và luôn hiển thị số 0 khi chưa bán.
- WordPress Admin → Sản phẩm: thêm cột **Đã bán**.
- Trang thống kê có đường dẫn xem cửa hàng sắp xếp theo lượt bán.
- Sau khi cập nhật, xóa cache LiteSpeed/CDN và thử ở cửa sổ ẩn danh. Nếu số vẫn =0, kiểm tra đơn thực sự ở Processing/Completed; đơn Pending/On hold không được tính.
