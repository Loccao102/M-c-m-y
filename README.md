# Mực Mây Studio — WordPress & WooCommerce

Trang web: https://mucmayyy.id.vn · Mã nguồn giao diện **2.2.0**.

## Không cần tải lại theme để sửa nội dung

Sau khi cài bản theme 2.2:

1. Vào `/wp-admin` → **Giao diện → Mực Mây Studio**.
2. Bấm **Mở trình chỉnh sửa Mực Mây** (WordPress Customizer).
3. Chọn nhóm **Banner & hình ảnh**, **Bộ sưu tập & danh mục**, **Lời nhắn & câu chuyện**, **Sản phẩm nổi bật**, **Thương hiệu & màu sắc**, hoặc **Chân trang**.
4. Sửa và chọn **Đăng / Publish**. Thay đổi được lưu trong CSDL WordPress; **không cần cập nhật ZIP**.

Sản phẩm, ảnh, giá, tồn kho, đơn hàng được quản lý **trực tiếp trong WooCommerce**, không qua GitHub. Các bài viết, trang Giới thiệu và Liên hệ sửa bằng WordPress Pages.

## Thư mục

- `muc-may-studio/`: theme PHP/CSS/JS (code)
- `muc-may-demo-content/`: công cụ khởi tạo danh mục và sản phẩm (chỉ chạy khi quản trị viên yêu cầu)
- `.github/workflows/`: kiểm tra cú pháp và quy trình triển khai tùy chọn

Lưu ý: các ảnh gốc trong theme v2.1 được giữ nguyên để không làm thay đổi nội dung hiện có. ZIP bản đầy đủ đóng gói kèm các ảnh này. Việc đồng bộ bằng SFTP **không dùng `--delete`**, nhằm không xóa ảnh đã có trên hosting.

## Nâng cấp bản 2.1 → 2.2

- **Sao lưu cả files và database** trên iNET trước khi nâng cấp.
- Vào WordPress → Giao diện → Themes → Add New → Upload Theme, chọn ZIP `muc-may-studio-v2.2-theme.zip` và thay thế theme hiện tại.
- Thư mục theme **phải tiếp tục là `muc-may-studio`** để giữ các `theme_mod` tùy chỉnh.
- Dữ liệu WooCommerce (sản phẩm / đơn hàng) nằm trong database và không bị ghi đè bởi bản cập nhật theme.
- Xóa cache LiteSpeed nếu có, kiểm tra homepage, shop, giỏ hàng, thanh toán, desktop/mobile và font tiếng Việt.

## Triển khai tự động từ GitHub lên iNET

Để không phải cập nhật ZIP từng lần, có thể dùng SFTP theo **`.github/workflows/deploy-sftp.yml`**.

Điều kiện: iNET phải bật **SSH/SFTP** cho tài khoản; FTP cổng 21 không đồng nghĩa với SFTP. Truy cập server qua SFTP và **xác minh host key** trước khi dùng workflow. Secret trong GitHub: `SFTP_HOST`, `SFTP_PORT`, `SFTP_USERNAME`, `SFTP_PRIVATE_KEY`, `SFTP_KNOWN_HOSTS`, `WP_THEME_DIR`. Không commit credentials vào repo public.

Workflow triển khai là **thủ công (Run workflow)**, không tự cập nhật ngay khi push và không xóa thư mục theme trên hosting. Đường dẫn `WP_THEME_DIR` phải là đường dẫn tuyệt đối tới `wp-content/themes/muc-may-studio/` trên iNET.

Nếu tài khoản iNET chỉ hỗ trợ FTP, tiếp tục nâng cấp qua ZIP hoặc đề nghị iNET cấp SFTP/SSH. Không khuyến nghị FTP không mã hóa.

## Kiểm thử

Chạy `php -l` với toàn bộ `.php` trong theme/plugin. Kiểm tra quy trình mua hàng trong môi trường kiểm thử và không lưu dữ liệu khách hàng hay thông tin bí mật trong repository.
