<?php
/**
 * Mực Mây Studio visual content controls.
 * Values live in WordPress theme_mods (database), never in the theme ZIP.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Register organized, accessible Customizer controls. */
function mucmay_register_studio_controls( $manager ) {
    $manager->add_panel( 'mucmay_studio', array(
        'title' => 'Mực Mây Studio · Nội dung & giao diện',
        'description' => 'Sửa nội dung trang chủ, hình ảnh, màu thương hiệu và chân trang mà không cần tải lại theme.',
        'priority' => 25,
    ) );

    $sections = array(
        'hero'       => array( 'Banner & hình ảnh', 10 ),
        'categories' => array( 'Bộ sưu tập & danh mục', 20 ),
        'editorial'  => array( 'Lời nhắn & câu chuyện', 30 ),
        'products'   => array( 'Sản phẩm nổi bật', 40 ),
        'branding'   => array( 'Thương hiệu & màu sắc', 50 ),
        'footer'     => array( 'Chân trang', 60 ),
    );
    foreach ( $sections as $id => $values ) {
        $manager->add_section( 'mucmay_' . $id, array(
            'title' => $values[0], 'panel' => 'mucmay_studio', 'priority' => $values[1],
        ) );
    }

    $fields = array(
        'mucmay_announcement_left' => array( 'branding', 'Dòng thông báo trái', 'text', 'GỬI TỪ MỰC MÂY ✳ GÓC NHỎ CỦA NHỮNG ĐIỀU HAY HO' ),
        'mucmay_announcement' => array( 'branding', 'Dòng thông báo phải', 'text', 'TIỆM GIẤY, MỰC & MỘT CHÚT MƠ MỘNG — 2026' ),
        'mucmay_logo_subtitle' => array( 'branding', 'Dòng chữ dưới logo', 'text', 'TIỆM ĐỒ GIẤY & NHỮNG ĐIỀU CHƯA VIẾT' ),
        'mucmay_hero_overline_left' => array( 'hero', 'Dòng chỉ mục banner', 'text', 'THƯ SỐ 001 / MỰC MÂY STUDIO' ),
        'mucmay_hero_overline_right' => array( 'hero', 'Địa điểm / năm', 'text', 'HÀ NỘI · 2026' ),
        'mucmay_hero_eyebrow' => array( 'hero', 'Dòng giới thiệu nhỏ', 'text', 'Một tiệm nhỏ dành cho người hay nghĩ ngợi' ),
        'mucmay_hero_line_1' => array( 'hero', 'Tiêu đề dòng 1', 'text', 'Những điều' ),
        'mucmay_hero_line_2' => array( 'hero', 'Tiêu đề dòng 2 (chữ nghiêng)', 'text', 'chưa viết' ),
        'mucmay_hero_line_3' => array( 'hero', 'Tiêu đề dòng 3', 'text', 'đều đáng giữ' ),
        'mucmay_hero_text' => array( 'hero', 'Đoạn giới thiệu banner', 'textarea', 'Một cuốn sổ. Một nét mực. Một khoảng lặng cho suy nghĩ. Mực Mây góp nhặt những món đồ giấy nhỏ xinh để bạn có chỗ cho mọi ý tưởng.' ),
        'mucmay_hero_cta' => array( 'hero', 'Nút đến cửa hàng', 'text', 'GHÉ CỬA TIỆM' ),
        'mucmay_hero_story_cta' => array( 'hero', 'Liên kết giới thiệu', 'text', 'Đọc chuyện của tiệm' ),
        'mucmay_hero_primary_image' => array( 'hero', 'Ảnh collage chính (sổ tay)', 'image', '' ),
        'mucmay_hero_secondary_image' => array( 'hero', 'Ảnh collage phụ (bút)', 'image', '' ),
        'mucmay_categories_enabled' => array( 'categories', 'Hiện phần danh mục', 'checkbox', true ),
        'mucmay_categories_title' => array( 'categories', 'Tiêu đề (chữ thường)', 'text', 'Mở từng' ),
        'mucmay_categories_title_italic' => array( 'categories', 'Tiêu đề (chữ nghiêng)', 'text', 'ngăn kéo.' ),
        'mucmay_categories_intro' => array( 'categories', 'Mô tả danh mục', 'textarea', 'Mỗi món đồ là một cái cớ đáng yêu để ngồi xuống, nghĩ ngợi và bắt đầu một điều gì đó.' ),
        'mucmay_editorial_enabled' => array( 'editorial', 'Hiện lời nhắn bên lề', 'checkbox', true ),
        'mucmay_editorial_label' => array( 'editorial', 'Nhãn lời nhắn', 'text', 'GHI CHÚ BÊN LỀ #02' ),
        'mucmay_editorial_quote' => array( 'editorial', 'Lời nhắn bên lề', 'textarea', 'Không phải ý tưởng nào cũng cần thật lớn. Đôi khi, nó chỉ cần một trang giấy để bắt đầu.' ),
        'mucmay_editorial_signature' => array( 'editorial', 'Chữ ký', 'text', '— mực.mây' ),
        'mucmay_products_enabled' => array( 'products', 'Hiện danh sách sản phẩm', 'checkbox', true ),
        'mucmay_products_title' => array( 'products', 'Tiêu đề (chữ thường)', 'text', 'Những thứ' ),
        'mucmay_products_title_italic' => array( 'products', 'Tiêu đề (chữ nghiêng)', 'text', 'muốn giữ lại.' ),
        'mucmay_products_intro' => array( 'products', 'Mô tả danh sách sản phẩm', 'textarea', 'Một tuyển tập nho nhỏ, từ giấy bút đến những điều khiến góc bàn bớt cô đơn.' ),
        'mucmay_products_count' => array( 'products', 'Số sản phẩm trang chủ (4–16)', 'number', 8 ),
        'mucmay_products_featured_only' => array( 'products', 'Chỉ hiển thị sản phẩm đã đánh dấu nổi bật', 'checkbox', false ),
        'mucmay_story_enabled' => array( 'editorial', 'Hiện câu chuyện thương hiệu', 'checkbox', true ),
        'mucmay_story_heading' => array( 'editorial', 'Tiêu đề câu chuyện (chữ thường)', 'textarea', 'Có những ngày, ta cần một chỗ để' ),
        'mucmay_story_heading_italic' => array( 'editorial', 'Tiêu đề câu chuyện (chữ nghiêng)', 'text', 'viết ra.' ),
        'mucmay_story_paragraph_1' => array( 'editorial', 'Câu chuyện, đoạn 1', 'textarea', 'Mực Mây không hứa sẽ làm cuộc sống chậm lại. Chúng mình chỉ muốn dành một góc nho nhỏ cho việc ghi nhớ, phác thảo, viết linh tinh — và những suy nghĩ chưa biết gửi cho ai.' ),
        'mucmay_story_paragraph_2' => array( 'editorial', 'Câu chuyện, đoạn 2', 'textarea', 'Đồ giấy thì bé thôi. Nhưng những gì bạn viết lên đó, biết đâu lại rất lớn.' ),
        'mucmay_story_cta' => array( 'editorial', 'Nút đọc câu chuyện', 'text', 'ĐỌC CÂU CHUYỆN CỦA TIỆM' ),
        'mucmay_footer_note' => array( 'footer', 'Lời nhắn chân trang', 'textarea', 'Cảm ơn bạn đã ghé qua. Hy vọng hôm nay có một điều nhỏ khiến bạn muốn viết lại.' ),
        'mucmay_footer_disclaimer' => array( 'footer', 'Lưu ý cửa hàng', 'textarea', 'Đây là website thực hành WordPress & WooCommerce. Các sản phẩm và đơn hàng chỉ phục vụ mục đích minh họa.' ),
        'mucmay_footer_tagline' => array( 'footer', 'Khẩu hiệu cuối chân trang', 'text', 'CHẾ TÁC TỪ GIẤY, MỰC & CHÚT TÒ MÒ.' ),
    );

    $cat_defaults = array(
        array( 'Sổ tay & Planner', 'Có những điều chỉ giấy mới giữ được.' ),
        array( 'Bút & Mực', 'Bắt đầu từ một nét đầu tiên.' ),
        array( 'Đồ cho góc bàn', 'Một chỗ ngồi, nhiều ý tưởng.' ),
        array( 'Đèn & Chuyện nhỏ', 'Giữ chút ấm áp ở lại.' ),
    );
    foreach ( $cat_defaults as $index => $pair ) {
        $n = $index + 1;
        $fields[ 'mucmay_category_' . $n . '_title' ] = array( 'categories', 'Danh mục ' . $n . ': Tên', 'text', $pair[0] );
        $fields[ 'mucmay_category_' . $n . '_subtitle' ] = array( 'categories', 'Danh mục ' . $n . ': Mô tả', 'textarea', $pair[1] );
         $fields[ 'mucmay_category_' . $n . '_slug' ] = array( 'categories', 'Danh mục ' . $n . ': Slug WooCommerce', 'slug', array( 'so-tay-planner', 'but-viet', 'phu-kien-ban-hoc', 'den-trang-tri' )[ $index ] );
        $fields[ 'mucmay_category_' . $n . '_image' ] = array( 'categories', 'Danh mục ' . $n . ': Hình ảnh', 'image', '' );
    }

    foreach ( $fields as $key => $field ) {
        list( $section, $label, $type, $default ) = $field;
        $sanitize = 'sanitize_text_field';
        if ( 'textarea' === $type ) { $sanitize = 'sanitize_textarea_field'; }
        if ( 'image' === $type ) { $sanitize = 'esc_url_raw'; }
        if ( 'slug' === $type ) { $sanitize = 'sanitize_title'; }
        if ( 'checkbox' === $type ) { $sanitize = 'mucmay_sanitize_checkbox'; }
        if ( 'number' === $type ) { $sanitize = 'mucmay_sanitize_product_count'; }
        $manager->add_setting( $key, array( 'default' => $default, 'sanitize_callback' => $sanitize, 'transport' => 'refresh' ) );
        if ( 'image' === $type ) {
            $manager->add_control( new WP_Customize_Image_Control( $manager, $key, array( 'label' => $label, 'section' => 'mucmay_' . $section, 'settings' => $key ) ) );
        } else {
            $args = array( 'label' => $label, 'section' => 'mucmay_' . $section, 'type' => ( 'slug' === $type ? 'text' : $type ) );
            if ( 'number' === $type ) { $args['input_attrs'] = array( 'min' => 4, 'max' => 16, 'step' => 1 ); }
            $manager->add_control( $key, $args );
        }
    }

    $colors = array(
        'mucmay_color_paper' => array( 'Màu nền giấy', '#f5f0e6' ),
        'mucmay_color_ink' => array( 'Màu chữ / mực', '#22231f' ),
        'mucmay_color_red' => array( 'Màu nhấn / con dấu', '#bd3c2d' ),
    );
    foreach ( $colors as $key => $data ) {
        $manager->add_setting( $key, array( 'default' => $data[1], 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
        $manager->add_control( new WP_Customize_Color_Control( $manager, $key, array( 'label' => $data[0], 'section' => 'mucmay_branding' ) ) );
    }
}
add_action( 'customize_register', 'mucmay_register_studio_controls', 20 );

function mucmay_sanitize_checkbox( $value ) { return (bool) $value; }
function mucmay_sanitize_product_count( $value ) { return max( 4, min( 16, absint( $value ) ) ); }

/** Output only validated color values. Never interpolate arbitrary CSS from settings. */
function mucmay_custom_brand_colors() {
    $colors = array(
        '--paper' => array( 'mucmay_color_paper', '#f5f0e6' ),
        '--ink'   => array( 'mucmay_color_ink', '#22231f' ),
        '--red'   => array( 'mucmay_color_red', '#bd3c2d' ),
    );
    $parts = array();
    foreach ( $colors as $property => $config ) {
        $value = sanitize_hex_color( get_theme_mod( $config[0], $config[1] ) );
        if ( $value ) { $parts[] = $property . ':' . $value; }
    }
    // Keep default secondary surfaces and borders unless changed intentionally.
    if ( $parts ) { wp_add_inline_style( 'mucmay-style', ':root{' . implode( ';', $parts ) . '}' ); }
}
add_action( 'wp_enqueue_scripts', 'mucmay_custom_brand_colors', 20 );

/** Admin shortcut: Appearance → Mực Mây Studio opens the WordPress Customizer. */
function mucmay_admin_settings_link() {
    add_theme_page( 'Mực Mây Studio', 'Mực Mây Studio', 'edit_theme_options', 'mucmay-studio', 'mucmay_render_admin_settings' );
}
add_action( 'admin_menu', 'mucmay_admin_settings_link' );
function mucmay_render_admin_settings() {
    if ( ! current_user_can( 'edit_theme_options' ) ) { return; }
    $customize = admin_url( 'customize.php?autofocus[panel]=mucmay_studio' );
    ?>
    <div class="wrap"><h1>Mực Mây Studio · Quản trị nội dung</h1>
      <p>Thay banner, ảnh, tiêu đề, mô tả, màu thương hiệu, danh mục và footer mà <strong>không cần cài lại theme</strong>.</p>
      <p><a class="button button-primary button-hero" href="<?php echo esc_url( $customize ); ?>">Mở trình chỉnh sửa Mực Mây →</a></p>
      <hr>
      <h2>Những nội dung được quản lý ở đâu?</h2>
      <table class="widefat striped" style="max-width:750px"><tbody>
        <tr><td><strong>Trang chủ & hình ảnh</strong></td><td>Giao diện → Mực Mây Studio → Mở trình chỉnh sửa</td></tr>
        <tr><td><strong>Sản phẩm / giá / tồn kho</strong></td><td>Sản phẩm → Tất cả sản phẩm (WooCommerce)</td></tr>
        <tr><td><strong>Đơn hàng</strong></td><td>WooCommerce → Đơn hàng</td></tr>
        <tr><td><strong>Bài viết / Giới thiệu / Liên hệ</strong></td><td>Trang hoặc Bài viết → Chỉnh sửa</td></tr>
        <tr><td><strong>Menu</strong></td><td>Giao diện → Menu</td></tr>
      </tbody></table>
      <p><em>Nội dung tùy chỉnh được lưu trong cơ sở dữ liệu WordPress. Các lần cập nhật theme sau không xóa nội dung này, miễn là giữ nguyên thư mục/tên theme.</em></p>
    </div><?php
}
