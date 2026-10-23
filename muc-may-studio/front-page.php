<?php
get_header();
$dir = get_template_directory_uri() . '/assets/images/';
$categories = array(
 array( 'slug'=>get_theme_mod( 'mucmay_category_1_slug', 'so-tay-planner' ), 'title'=>get_theme_mod( 'mucmay_category_1_title', 'Sổ tay & Planner' ), 'subtitle'=>get_theme_mod( 'mucmay_category_1_subtitle', 'Có những điều chỉ giấy mới giữ được.' ), 'image'=>get_theme_mod( 'mucmay_category_1_image', $dir . 'category-so-tay-planner.jpg' ) ),
 array( 'slug'=>get_theme_mod( 'mucmay_category_2_slug', 'but-viet' ), 'title'=>get_theme_mod( 'mucmay_category_2_title', 'Bút & Mực' ), 'subtitle'=>get_theme_mod( 'mucmay_category_2_subtitle', 'Bắt đầu từ một nét đầu tiên.' ), 'image'=>get_theme_mod( 'mucmay_category_2_image', $dir . 'category-but-viet.jpg' ) ),
 array( 'slug'=>get_theme_mod( 'mucmay_category_3_slug', 'phu-kien-ban-hoc' ), 'title'=>get_theme_mod( 'mucmay_category_3_title', 'Đồ cho góc bàn' ), 'subtitle'=>get_theme_mod( 'mucmay_category_3_subtitle', 'Một chỗ ngồi, nhiều ý tưởng.' ), 'image'=>get_theme_mod( 'mucmay_category_3_image', $dir . 'category-phu-kien-ban-hoc.jpg' ) ),
 array( 'slug'=>get_theme_mod( 'mucmay_category_4_slug', 'den-trang-tri' ), 'title'=>get_theme_mod( 'mucmay_category_4_title', 'Đèn & Chuyện nhỏ' ), 'subtitle'=>get_theme_mod( 'mucmay_category_4_subtitle', 'Giữ chút ấm áp ở lại.' ), 'image'=>get_theme_mod( 'mucmay_category_4_image', $dir . 'category-den-trang-tri.jpg' ) )
);
?>
<main id="main">
<section class="mm-hero" aria-labelledby="mm-hero-title">
  <div class="mm-container mm-hero-grid">
    <div class="mm-hero-content">
      <div class="mm-hero-overline"><span><?php echo esc_html( get_theme_mod( 'mucmay_hero_overline_left', 'THƯ SỐ 001 / MỰC MÂY STUDIO' ) ); ?></span><span><?php echo esc_html( get_theme_mod( 'mucmay_hero_overline_right', 'HÀ NỘI · 2026' ) ); ?></span></div>
      <div class="mm-hero-copy">
        <span class="mm-eyebrow"><span class="mm-asterisk" aria-hidden="true">✳</span> <?php echo esc_html( get_theme_mod( 'mucmay_hero_eyebrow', 'Một tiệm nhỏ dành cho người hay nghĩ ngợi' ) ); ?></span>
        <h1 id="mm-hero-title"><?php echo esc_html( get_theme_mod( 'mucmay_hero_line_1', 'Những điều' ) ); ?><br><em><?php echo esc_html( get_theme_mod( 'mucmay_hero_line_2', 'chưa viết' ) ); ?></em><br><span><?php echo esc_html( get_theme_mod( 'mucmay_hero_line_3', 'đều đáng giữ' ) ); ?><span class="mm-red-dot">.</span></span></h1>
        <p><?php echo esc_html( get_theme_mod( 'mucmay_hero_text', 'Một cuốn sổ. Một nét mực. Một khoảng lặng cho suy nghĩ. Mực Mây góp nhặt những món đồ giấy nhỏ xinh để bạn có chỗ cho mọi ý tưởng.' ) ); ?></p>
        <div class="mm-hero-cta"><a class="mm-button mm-button-primary" href="<?php echo esc_url( mucmay_shop_url() ); ?>"><span><?php echo esc_html( get_theme_mod( 'mucmay_hero_cta', 'GHÉ CỬA TIỆM' ) ); ?></span><span class="mm-button-arrow" aria-hidden="true">↗</span></a><a class="mm-link" href="<?php echo esc_url( mucmay_page_url( 'gioi-thieu' ) ); ?>"><?php echo esc_html( get_theme_mod( 'mucmay_hero_story_cta', 'Đọc chuyện của tiệm' ) ); ?> <span aria-hidden="true">↗</span></a></div>
      </div>
      <div class="mm-hero-foot"><span>MADE FOR THE MESSY & THE MINDFUL</span><span>CUỘN XUỐNG <span aria-hidden="true">↓</span></span></div>
    </div>
    <div class="mm-hero-art" aria-label="Bàn giấy với sổ tay, bút viết và những bức thư nhỏ">
      <div class="mm-art-grid" aria-hidden="true"></div>
      <div class="mm-art-sheet mm-art-sheet-back" aria-hidden="true"><span>Dear, future me</span></div>
      <div class="mm-art-photo mm-art-photo-main"><span class="mm-photo-label">HỒ SƠ 001 / SỔ TAY</span><img src="<?php echo esc_url( get_theme_mod( 'mucmay_hero_primary_image', $dir . 'products/so-tay-linen-ivory.jpg' ) ); ?>" alt="Sổ tay Linen Ivory" fetchpriority="high"><span class="mm-photo-date">KEEP THIS / 2026</span></div>
      <div class="mm-art-photo mm-art-photo-mini"><img src="<?php echo esc_url( get_theme_mod( 'mucmay_hero_secondary_image', $dir . 'products/bo-but-pastel.jpg' ) ); ?>" alt="Bộ bút màu pastel" loading="eager"><span>02 / WRITE SOMETHING</span></div>
      <div class="mm-post-stamp" aria-hidden="true"><svg viewBox="0 0 140 140" role="presentation"><defs><path id="stamp-ring" d="M70,70m-51,0a51,51 0 1,1 102,0a51,51 0 1,1 -102,0"/></defs><circle cx="70" cy="70" r="64" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="70" cy="70" r="43" stroke="currentColor" stroke-width="1" fill="none"/><text font-size="11" font-weight="700" letter-spacing="2" fill="currentColor"><textPath href="#stamp-ring">MỰC MÂY STUDIO · LETTERS & LITTLE THINGS · </textPath></text><path d="M55 64c-4-8 4-15 12-11 5-9 17-5 18 5 10 4 4 16-6 15H58c-8-1-9-7-3-9Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M60 81h21" stroke="currentColor" stroke-width="2"/></svg></div>
      <span class="mm-art-note mm-art-note-a">để dành<br><em>ngày mai</em> ↗</span>
      <span class="mm-art-mark" aria-hidden="true">✳</span>
      <div class="mm-art-tape mm-art-tape-a" aria-hidden="true"></div><div class="mm-art-tape mm-art-tape-b" aria-hidden="true"></div>
    </div>
  </div>
</section>
<div class="mm-marquee" aria-label="Mực Mây — Tiệm đồ giấy và những điều chưa viết"><div class="mm-marquee-track" aria-hidden="true"><span>THƯ TAY <b>✳</b> NHỮNG NÉT MỰC <b>✳</b> TRANG GIẤY TRẮNG <b>✳</b> CHUYỆN NHỎ MỖI NGÀY <b>✳</b></span><span>THƯ TAY <b>✳</b> NHỮNG NÉT MỰC <b>✳</b> TRANG GIẤY TRẮNG <b>✳</b> CHUYỆN NHỎ MỖI NGÀY <b>✳</b></span></div></div>
<?php if ( get_theme_mod( 'mucmay_categories_enabled', true ) ) : ?><section class="mm-section mm-categories-section" aria-labelledby="mm-categories-title"><div class="mm-container">
 <div class="mm-section-heading"><div class="mm-section-index">CHƯƠNG 01 <span>—</span> DANH MỤC</div><div class="mm-section-titles"><h2 id="mm-categories-title"><?php echo esc_html( get_theme_mod( 'mucmay_categories_title', 'Mở từng' ) ); ?> <em><?php echo esc_html( get_theme_mod( 'mucmay_categories_title_italic', 'ngăn kéo.' ) ); ?></em></h2><p><?php echo esc_html( get_theme_mod( 'mucmay_categories_intro', 'Mỗi món đồ là một cái cớ đáng yêu để ngồi xuống, nghĩ ngợi và bắt đầu một điều gì đó.' ) ); ?></p></div></div>
 <div class="mm-category-grid">
 <?php foreach ( $categories as $i=>$cat ) : ?>
 <a class="mm-category-card mm-category-<?php echo esc_attr( $i+1 ); ?>" href="<?php echo esc_url( mucmay_category_url( $cat['slug'] ) ); ?>"><div class="mm-category-image"><img src="<?php echo esc_url( $cat['image'] ); ?>" alt="<?php echo esc_attr( $cat['title'] ); ?>" loading="lazy"></div><span class="mm-cat-index">0<?php echo esc_html( $i+1 ); ?> / 04</span><div class="mm-category-caption"><div><h3><?php echo esc_html( $cat['title'] ); ?></h3><p><?php echo esc_html( $cat['subtitle'] ); ?></p></div><span class="mm-cat-arrow" aria-hidden="true">↗</span></div></a>
 <?php endforeach; ?>
 </div>
 </div></section><?php endif; ?>
<?php if ( get_theme_mod( 'mucmay_editorial_enabled', true ) ) : ?><section class="mm-editorial-note" aria-label="Một lời nhắn của Mực Mây"><div class="mm-container mm-editorial-inner"><span><?php echo esc_html( get_theme_mod( 'mucmay_editorial_label', 'GHI CHÚ BÊN LỀ #02' ) ); ?></span><p>“<?php echo nl2br( esc_html( get_theme_mod( 'mucmay_editorial_quote', 'Không phải ý tưởng nào cũng cần thật lớn. Đôi khi, nó chỉ cần một trang giấy để bắt đầu.' ) ) ); ?>”</p><span class="mm-note-signature"><?php echo esc_html( get_theme_mod( 'mucmay_editorial_signature', '— mực.mây' ) ); ?></span></div></section><?php endif; ?>
<?php if ( get_theme_mod( 'mucmay_products_enabled', true ) ) : ?><section class="mm-section mm-products-section" aria-labelledby="mm-products-title"><div class="mm-container">
 <div class="mm-section-heading mm-products-heading"><div class="mm-section-index">CHƯƠNG 02 <span>—</span> ĐỒ VẬT</div><div class="mm-section-titles"><h2 id="mm-products-title"><?php echo esc_html( get_theme_mod( 'mucmay_products_title', 'Những thứ' ) ); ?> <em><?php echo esc_html( get_theme_mod( 'mucmay_products_title_italic', 'muốn giữ lại.' ) ); ?></em></h2><p><?php echo esc_html( get_theme_mod( 'mucmay_products_intro', 'Một tuyển tập nho nhỏ, từ giấy bút đến những điều khiến góc bàn bớt cô đơn.' ) ); ?></p></div><a class="mm-text-link" href="<?php echo esc_url( mucmay_shop_url() ); ?>">XEM TOÀN BỘ BỘ SƯU TẬP <span>↗</span></a></div>
 <div class="mm-product-grid">
 <?php if ( post_type_exists( 'product' ) ) :
 $args = array( 'post_type'=>'product', 'posts_per_page'=>mucmay_sanitize_product_count( get_theme_mod( 'mucmay_products_count', 8 ) ), 'post_status'=>'publish', 'orderby'=>'date', 'order'=>'ASC', 'no_found_rows'=>true );
 if ( get_theme_mod( 'mucmay_products_featured_only', false ) && taxonomy_exists( 'product_visibility' ) ) {
     $args['tax_query'] = array( array( 'taxonomy'=>'product_visibility', 'field'=>'name', 'terms'=>array('featured') ) );
 }
 $products = new WP_Query( $args );
 if ( $products->have_posts() ) { while ( $products->have_posts() ) { $products->the_post(); mucmay_product_card( get_the_ID() ); } wp_reset_postdata(); }
 else { echo '<p class="mm-empty-state">Chưa có món đồ nào được xếp lên kệ. Hãy vào Công cụ → Mực Mây Demo để nhập 16 sản phẩm mẫu.</p>'; }
 else : echo '<p class="mm-empty-state">Vui lòng kích hoạt WooCommerce để hiển thị sản phẩm.</p>'; endif; ?>
 </div><div class="mm-products-after"><span>ĐÃ GÓP NHẶT / TỪNG CHÚT MỘT</span><a href="<?php echo esc_url( mucmay_shop_url() ); ?>">Xem thêm những món hay ho <span aria-hidden="true">↗</span></a></div>
 </div></section><?php endif; ?>
<?php if ( get_theme_mod( 'mucmay_story_enabled', true ) ) : ?><section class="mm-story-section" aria-labelledby="mm-story-title"><div class="mm-container mm-story-grid">
 <div class="mm-story-art"><div class="mm-story-card"><div class="mm-story-ruled"></div><span class="mm-story-meta">TRÍCH SỔ TAY / SỐ 001</span><strong>Một chút<br><em>thật lòng.</em></strong><span class="mm-story-bottom">GIỮ LẠI ĐIỀU BẠN THẤY QUÝ.</span></div><span class="mm-story-seal" aria-hidden="true">MM<br>✳</span></div>
 <div class="mm-story-copy"><span class="mm-section-index">CHƯƠNG 03 — LỜI TỰA</span><h2 id="mm-story-title"><?php echo nl2br( esc_html( get_theme_mod( 'mucmay_story_heading', 'Có những ngày, ta cần một chỗ để' ) ) ); ?> <em><?php echo esc_html( get_theme_mod( 'mucmay_story_heading_italic', 'viết ra.' ) ); ?></em></h2><p><?php echo esc_html( get_theme_mod( 'mucmay_story_paragraph_1', 'Mực Mây không hứa sẽ làm cuộc sống chậm lại. Chúng mình chỉ muốn dành một góc nho nhỏ cho việc ghi nhớ, phác thảo, viết linh tinh — và những suy nghĩ chưa biết gửi cho ai.' ) ); ?></p><p><?php echo esc_html( get_theme_mod( 'mucmay_story_paragraph_2', 'Đồ giấy thì bé thôi. Nhưng những gì bạn viết lên đó, biết đâu lại rất lớn.' ) ); ?></p><a class="mm-button mm-button-outline" href="<?php echo esc_url( mucmay_page_url( 'gioi-thieu' ) ); ?>"><span><?php echo esc_html( get_theme_mod( 'mucmay_story_cta', 'ĐỌC CÂU CHUYỆN CỦA TIỆM' ) ); ?></span><span class="mm-button-arrow">↗</span></a></div>
 </div></section><?php endif; ?>
<section class="mm-footnotes"><div class="mm-container mm-footnotes-inner"><span>✳</span><div><strong>Chọn thứ khiến bạn muốn bắt đầu.</strong><p>Giấy tốt, đồ xinh, cảm hứng thật — một tiệm nhỏ dành cho những ngày bình thường.</p></div><a href="<?php echo esc_url( mucmay_shop_url() ); ?>">GHÉ XEM ĐỒ <span aria-hidden="true">↗</span></a></div></section>
</main>
<?php get_footer(); ?>
