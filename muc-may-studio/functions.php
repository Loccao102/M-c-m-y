<?php
/** Theme functionality for Mực Mây Studio. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function mucmay_setup() {
    load_theme_textdomain( 'muc-may-studio', get_template_directory() . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'custom-logo', array( 'height' => 90, 'width' => 240, 'flex-height' => true, 'flex-width' => true ) );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'woocommerce', array( 'thumbnail_image_width' => 600, 'single_image_width' => 800, 'product_grid' => array( 'default_rows' => 3, 'min_rows' => 2, 'max_rows' => 6, 'default_columns' => 4, 'min_columns' => 2, 'max_columns' => 4 ) ) );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    register_nav_menus( array( 'primary' => 'Menu chính', 'footer' => 'Menu chân trang' ) );
}
add_action( 'after_setup_theme', 'mucmay_setup' );

function mucmay_assets() {
    // All three families provide Vietnamese glyphs; Google Fonts serves its unicode-range subsets.
    $font_css = 'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600;700&family=Lora:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&display=swap&subset=vietnamese';
    wp_enqueue_style( 'mucmay-fonts', $font_css, array(), null );
    wp_enqueue_style( 'mucmay-style', get_stylesheet_uri(), array( 'mucmay-fonts' ), '2.2.0' );
    wp_enqueue_script( 'mucmay-main', get_template_directory_uri() . '/assets/js/main.js', array(), '2.2.0', true );
}
add_action( 'wp_enqueue_scripts', 'mucmay_assets' );

function mucmay_shop_url() {
    return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/cua-tiem/' );
}
function mucmay_page_url( $slug ) {
    $page = get_page_by_path( $slug );
    return $page ? get_permalink( $page ) : home_url( '/' );
}
function mucmay_category_url( $slug ) {
    if ( taxonomy_exists( 'product_cat' ) ) {
        $term = get_term_by( 'slug', $slug, 'product_cat' );
        if ( $term ) {
            $link = get_term_link( $term );
            if ( ! is_wp_error( $link ) ) { return $link; }
        }
    }
    return mucmay_shop_url();
}
function mucmay_cart_count() {
    return function_exists( 'WC' ) && WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
}
function mucmay_cart_badge() {
    return '<span class="mm-cart-count" aria-label="Số sản phẩm trong giỏ">' . (int) mucmay_cart_count() . '</span>';
}
function mucmay_cart_fragments( $fragments ) {
    $fragments['span.mm-cart-count'] = mucmay_cart_badge();
    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'mucmay_cart_fragments' );

function mucmay_fallback_menu() {
    $links = array(
        array( home_url( '/' ), 'Trang chủ' ),
        array( mucmay_shop_url(), 'Cửa tiệm' ),
        array( mucmay_category_url( 'so-tay-planner' ), 'Sổ tay' ),
        array( mucmay_category_url( 'phu-kien-ban-hoc' ), 'Góc học tập' ),
        array( mucmay_page_url( 'gioi-thieu' ), 'Về Mực Mây' ),
    );
    echo '<ul class="mm-nav-list">';
    foreach ( $links as $link ) {
        echo '<li><a href="' . esc_url( $link[0] ) . '">' . esc_html( $link[1] ) . '</a></li>';
    }
    echo '</ul>';
}

function mucmay_product_card( $product_id ) {
    if ( ! function_exists( 'wc_get_product' ) ) { return; }
    $product = wc_get_product( $product_id );
    if ( ! $product ) { return; }
    $url = get_permalink( $product_id );
    $thumb = $product->get_image_id() ? wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, array( 'loading' => 'lazy', 'alt' => $product->get_name() ) ) : wc_placeholder_img( 'woocommerce_thumbnail' );
    echo '<article class="mm-product-card">';
    echo '<a class="mm-product-photo" href="' . esc_url( $url ) . '">' . $thumb . '<span class="mm-photo-arrow" aria-hidden="true">↗</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress image HTML.
    echo '<div class="mm-product-meta">';
    $terms = get_the_terms( $product_id, 'product_cat' );
    if ( ! is_wp_error( $terms ) && $terms ) { echo '<span class="mm-product-category">' . esc_html( $terms[0]->name ) . '</span>'; }
    echo '<h3><a href="' . esc_url( $url ) . '">' . esc_html( $product->get_name() ) . '</a></h3>';
    echo '<div class="mm-product-row"><span class="mm-price">' . wp_kses_post( $product->get_price_html() ) . '</span>';
    if ( $product->is_purchasable() && $product->is_in_stock() && $product->is_type( 'simple' ) ) {
        echo '<a class="mm-add-btn" href="' . esc_url( $product->add_to_cart_url() ) . '" aria-label="Thêm ' . esc_attr( $product->get_name() ) . ' vào giỏ">+</a>';
    } else {
        echo '<a class="mm-add-btn" href="' . esc_url( $url ) . '" aria-label="Xem sản phẩm">↗</a>';
    }
    echo '</div></div></article>';
}

require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/payments/vnpay.php';

function mucmay_excerpt_length( $length ) { return 22; }
add_filter( 'excerpt_length', 'mucmay_excerpt_length' );

/** Establish early font connections on the public site. */
function mucmay_vietnamese_font_preconnect( $urls, $relation_type ) {
    if ( 'preconnect' === $relation_type ) {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );
    }
    return $urls;
}
add_filter( 'wp_resource_hints', 'mucmay_vietnamese_font_preconnect', 10, 2 );
