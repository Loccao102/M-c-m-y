<?php
/**
 * Plugin Name: Mực Mây – Catalog Setup
 * Description: Thiết lập danh mục, sản phẩm, hình ảnh và các trang cơ bản cho Mực Mây Studio. Chỉ chạy khi quản trị viên chủ động xác nhận.
 * Version: 1.0.1
 * Requires PHP: 7.4
 * Text Domain: muc-may-demo
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function mucmay_demo_admin_menu() {
    add_management_page( 'Mực Mây · Danh mục', 'Mực Mây · Danh mục', 'manage_options', 'muc-may-demo', 'mucmay_demo_admin_page' );
}
add_action( 'admin_menu', 'mucmay_demo_admin_menu' );

function mucmay_demo_ready() {
    return class_exists( 'WooCommerce' ) && class_exists( 'WC_Product_Simple' ) && 'muc-may-studio' === get_template();
}

function mucmay_demo_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $created = isset( $_GET['mm_created'] ) ? absint( wp_unslash( $_GET['mm_created'] ) ) : null;
    $status = isset( $_GET['mm_status'] ) ? sanitize_key( wp_unslash( $_GET['mm_status'] ) ) : '';
    ?>
    <div class="wrap" style="max-width:900px"><h1>Mực Mây Studio · Thiết lập danh mục</h1>
      <p>Khởi tạo 4 danh mục, 16 sản phẩm WooCommerce có ảnh trong Media Library và những trang cửa hàng còn thiếu. Không ghi đè sản phẩm đã có cùng SKU.</p>
      <?php if ( ! mucmay_demo_ready() ) : ?>
      <div class="notice notice-error"><p>Bạn phải <strong>kích hoạt WooCommerce và theme “Mực Mây Studio”</strong> trước khi nhập.</p></div>
      <?php endif; ?>
      <?php if ( 'done' === $status ) : ?><div class="notice notice-success"><p>Đã đồng bộ danh mục. Tạo thêm <?php echo esc_html( $created ); ?> sản phẩm (những SKU đã tồn tại sẽ được giữ nguyên). <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">Xem trang chủ ↗</a></p></div><?php endif; ?>
      <?php if ( 'error' === $status ) : ?><div class="notice notice-error"><p>Không thể nhập dữ liệu. Vui lòng kiểm tra WooCommerce, theme đang kích hoạt và quyền ghi thư mục uploads của hosting.</p></div><?php endif; ?>
      <div class="card" style="max-width:780px;padding:24px"><h2>Dữ liệu được thêm</h2>
      <ul style="list-style:disc;padding-left:22px"><li>16 sản phẩm, mô tả, giá VND, tồn kho và ảnh.</li><li>4 danh mục sản phẩm.</li><li>Trang Trang chủ, Giới thiệu, Liên hệ, Cửa tiệm (nếu chưa có).</li><li>Giỏ hàng, Thanh toán, Tài khoản (chỉ bổ sung khi WooCommerce chưa tạo).</li></ul>
      <p><strong>Lưu ý:</strong> Hãy rà soát giá bán, số lượng tồn, hình ảnh và khả năng giao hàng trước khi mở bán. Công cụ thiết lập trang chủ và đơn vị tiền tệ VND; không tự bật cổng thanh toán hoặc tạo đơn hàng.</p>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
      <input type="hidden" name="action" value="mucmay_demo_import">
      <?php wp_nonce_field( 'mucmay_demo_import' ); ?>
      <?php submit_button( 'Khởi tạo danh mục', 'primary', 'submit', false, mucmay_demo_ready() ? array() : array( 'disabled' => 'disabled' ) ); ?>
      </form></div>
      <p>Sau khi thiết lập: kiểm tra <strong>Products → All Products</strong> và cấu hình các phương thức thanh toán, vận chuyển tại <strong>WooCommerce → Settings</strong>.</p>
    </div>
    <?php
}

function mucmay_demo_insert_page( $title, $slug, $content ) {
    $page = get_page_by_path( $slug, OBJECT, 'page' );
    if ( $page ) { return (int) $page->ID; }
    $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content ), true );
    return is_wp_error( $id ) ? 0 : (int) $id;
}

function mucmay_demo_product_image( $source_file ) {
    if ( ! file_exists( $source_file ) || ! is_readable( $source_file ) ) { return 0; }
    $source_name = basename( $source_file );
    $existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_mucmay_demo_asset', 'meta_value' => $source_name, 'fields' => 'ids', 'posts_per_page' => 1 ) );
    if ( $existing ) { return (int) $existing[0]; }
    $bytes = file_get_contents( $source_file ); // Local, bundled theme asset; not a remote fetch.
    if ( false === $bytes ) { return 0; }
    $upload = wp_upload_bits( $source_name, null, $bytes );
    if ( ! empty( $upload['error'] ) ) { return 0; }
    $attachment_id = wp_insert_attachment( array( 'post_mime_type' => 'image/jpeg', 'post_title' => sanitize_text_field( pathinfo( $source_name, PATHINFO_FILENAME ) ), 'post_content' => '', 'post_status' => 'inherit' ), $upload['file'] );
    if ( is_wp_error( $attachment_id ) || ! $attachment_id ) { return 0; }
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
    if ( $metadata ) { wp_update_attachment_metadata( $attachment_id, $metadata ); }
    update_post_meta( $attachment_id, '_mucmay_demo_asset', $source_name );
    return (int) $attachment_id;
}

function mucmay_demo_import() {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Không có quyền thực hiện thao tác này.', '', array( 'response' => 403 ) ); }
    check_admin_referer( 'mucmay_demo_import' );
    $back = admin_url( 'tools.php?page=muc-may-demo' );
    if ( ! mucmay_demo_ready() ) { wp_safe_redirect( add_query_arg( 'mm_status', 'error', $back ) ); exit; }
    $path = plugin_dir_path( __FILE__ ) . 'data/products.json';
    $json = is_readable( $path ) ? file_get_contents( $path ) : false;
    $data = $json ? json_decode( $json, true ) : null;
    if ( ! is_array( $data ) || empty( $data['products'] ) || empty( $data['categories'] ) ) { wp_safe_redirect( add_query_arg( 'mm_status', 'error', $back ) ); exit; }

    $categories = array();
    foreach ( $data['categories'] as $item ) {
        $slug = sanitize_title( $item['slug'] );
        $term = get_term_by( 'slug', $slug, 'product_cat' );
        if ( ! $term ) {
            $result = wp_insert_term( sanitize_text_field( $item['name'] ), 'product_cat', array( 'slug' => $slug, 'description' => sanitize_text_field( $item['description'] ) ) );
            if ( ! is_wp_error( $result ) ) { $categories[ $slug ] = (int) $result['term_id']; }
        } else { $categories[ $slug ] = (int) $term->term_id; }
    }
    $created = 0;
    foreach ( $data['products'] as $i => $item ) {
        $sku = sanitize_text_field( $item['sku'] );
        if ( wc_get_product_id_by_sku( $sku ) ) { continue; }
        try {
            $product = new WC_Product_Simple();
            $product->set_name( sanitize_text_field( $item['name'] ) );
            $product->set_slug( sanitize_title( $item['slug'] ) );
            $product->set_status( 'publish' );
            $product->set_catalog_visibility( 'visible' );
            $product->set_description( sanitize_text_field( $item['description'] ) );
            $product->set_short_description( sanitize_text_field( $item['short_description'] ) );
            $product->set_sku( $sku );
            $product->set_regular_price( (string) absint( $item['regular_price'] ) );
            $product->set_manage_stock( true );
            $product->set_stock_quantity( absint( $item['stock'] ) );
            $product->set_stock_status( 'instock' );
            if ( isset( $categories[ $item['category'] ] ) ) { $product->set_category_ids( array( $categories[ $item['category'] ] ) ); }
            if ( 0 === $i % 4 ) { $product->set_featured( true ); }
            $image_path = get_template_directory() . '/assets/images/products/' . basename( $item['image'] );
            $image_id = mucmay_demo_product_image( $image_path );
            if ( $image_id ) { $product->set_image_id( $image_id ); }
            $product->save();
            $created++;
        } catch ( Exception $e ) {
            error_log( 'Mực Mây Catalog import: ' . $e->getMessage() ); // Logging without credentials.
        }
    }

    $home = mucmay_demo_insert_page( 'Trang chủ', 'trang-chu', '<p>Trang chủ được hiển thị bằng giao diện Mực Mây Studio.</p>' );
    mucmay_demo_insert_page( 'Giới thiệu', 'gioi-thieu', '<h2>Tiệm đồ giấy và những điều chưa viết</h2><p>Đôi khi ta cần một chỗ để cất lại những điều chưa biết nói với ai. Mực Mây Studio bắt đầu từ ý nghĩ đơn giản ấy: giấy, mực và một góc bàn có thể khiến người ta muốn ngồi xuống và viết.</p><h2>Mỗi trang giấy là một khả năng</h2><p>Chúng mình góp nhặt sổ tay, bút viết, đồ trang trí và những món nhỏ tạo cảm hứng. Đồ giấy thì bé thôi, nhưng những gì được viết lên đó có thể rất lớn.</p><p>Hy vọng mỗi món đồ ở Mực Mây sẽ giúp bạn giữ lại những ý tưởng, câu chuyện và cảm hứng của riêng mình.</p>' );
    mucmay_demo_insert_page( 'Liên hệ', 'lien-he', '<h2>Gửi Mực Mây một lời nhắn</h2><p>Có một món đồ bạn đang tìm, hay một điều nhỏ muốn kể? Mực Mây rất vui khi nhận được lời nhắn của bạn.</p><p>Để được hỗ trợ về sản phẩm hoặc đơn hàng, hãy sử dụng thông tin liên hệ chính thức được cập nhật trên cửa tiệm.</p><h3>Chuyện nhỏ của chúng mình</h3><p>Từ câu hỏi về một cuốn sổ đến góp ý cho trải nghiệm mua sắm, mỗi lời nhắn đều đáng được lắng nghe.</p>' );

    $shop_id = (int) get_option( 'woocommerce_shop_page_id', 0 );
    if ( $shop_id <= 0 || 'publish' !== get_post_status( $shop_id ) ) { $shop_id = mucmay_demo_insert_page( 'Cửa tiệm', 'cua-tiem', '' ); update_option( 'woocommerce_shop_page_id', $shop_id ); }
    $wc_pages = array(
        array( 'woocommerce_cart_page_id', 'Giỏ hàng', 'gio-hang', '[woocommerce_cart]' ),
        array( 'woocommerce_checkout_page_id', 'Thanh toán', 'thanh-toan', '[woocommerce_checkout]' ),
        array( 'woocommerce_myaccount_page_id', 'Tài khoản', 'tai-khoan', '[woocommerce_my_account]' ),
    );
    foreach ( $wc_pages as $entry ) {
        $id = (int) get_option( $entry[0], 0 );
        if ( $id <= 0 || 'publish' !== get_post_status( $id ) ) {
            $id = mucmay_demo_insert_page( $entry[1], $entry[2], $entry[3] );
            if ( $id ) { update_option( $entry[0], $id ); }
        }
    }
    if ( $home ) { update_option( 'show_on_front', 'page' ); update_option( 'page_on_front', $home ); }
    update_option( 'woocommerce_currency', 'VND' );
    flush_rewrite_rules( false );
    wp_safe_redirect( add_query_arg( array( 'mm_status' => 'done', 'mm_created' => $created ), $back ) );
    exit;
}
add_action( 'admin_post_mucmay_demo_import', 'mucmay_demo_import' );
