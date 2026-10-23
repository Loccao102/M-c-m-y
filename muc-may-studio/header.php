<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="mm-skip-link" href="#main">Chuyển tới nội dung chính</a>
<div class="mm-announcement"><div class="mm-container mm-announcement-inner"><span><?php echo esc_html( get_theme_mod( 'mucmay_announcement_left', 'GỬI TỪ MỰC MÂY ✳ GÓC NHỎ CỦA NHỮNG ĐIỀU HAY HO' ) ); ?></span><span><?php echo esc_html( get_theme_mod( 'mucmay_announcement', 'TIỆM GIẤY, MỰC & MỘT CHÚT MƠ MỘNG — 2026' ) ); ?></span></div></div>
<header class="mm-header">
  <div class="mm-container mm-header-inner">
    <a class="mm-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Mực Mây Studio – Trang chủ">
      <?php if ( has_custom_logo() ) : ?><span class="mm-uploaded-logo"><?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'medium', false, array( 'alt' => get_bloginfo( 'name' ) ) ); ?></span><?php else : ?><span class="mm-logo-doodle" aria-hidden="true"><svg viewBox="0 0 60 60" fill="none"><path d="M8 37c-8-17 10-29 23-18C40 3 61 14 55 31c12 15-8 26-18 20C23 61 7 52 8 37Z" stroke="currentColor" stroke-width="2"/><path d="m25 23 9 9-10 12m10-12 7 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="37" cy="18" r="2" fill="currentColor"/></svg></span><?php endif; ?>
      <span class="mm-wordmark"><strong>mực<span>·</span>mây</strong><small><?php echo esc_html( get_theme_mod( 'mucmay_logo_subtitle', 'TIỆM ĐỒ GIẤY & NHỮNG ĐIỀU CHƯA VIẾT' ) ); ?></small></span>
    </a>
    <nav class="mm-nav" id="mm-navigation" aria-label="Điều hướng chính">
    <?php if ( has_nav_menu( 'primary' ) ) { wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'mm-nav-list', 'depth' => 2 ) ); } else { mucmay_fallback_menu(); } ?>
    </nav>
    <div class="mm-actions">
      <form class="mm-search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search">
        <label class="screen-reader-text" for="mm-search-field">Tìm sản phẩm</label>
        <input id="mm-search-field" type="search" name="s" placeholder="Tìm trong tiệm..." value="<?php echo esc_attr( get_search_query() ); ?>">
        <input type="hidden" name="post_type" value="product">
        <button type="submit" aria-label="Tìm kiếm"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="7"/><path d="m16.1 16.1 5.1 5.1"/></svg></button>
      </form>
      <a class="mm-header-cart" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) ); ?>" aria-label="Xem giỏ hàng"><span class="mm-cart-label">GIỎ</span><?php echo mucmay_cart_badge(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
      <button class="mm-menu-toggle" type="button" aria-controls="mm-navigation" aria-expanded="false" aria-label="Mở menu"><span></span><span></span></button>
    </div>
  </div>
</header>
