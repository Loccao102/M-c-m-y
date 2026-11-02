<?php
/**
 * Plugin Name: Mực Mây — Trang xác nhận đơn hàng
 * Description: Trang cảm ơn riêng theo phong cách Mực Mây, dùng dữ liệu đơn hàng WooCommerce thật. Không thay đổi cổng thanh toán.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Author: Mực Mây Studio
 * Text Domain: muc-may-order-note
 */

defined('ABSPATH') || exit;

/**
 * Replace only WooCommerce's standard thank-you view. WooCommerce has already
 * completed its customer ownership / guest email verification before rendering
 * this template. Do not intercept the route through template_include.
 */
add_filter('woocommerce_locate_template', static function ($template, $template_name) {
    if ($template_name !== 'checkout/thankyou.php' || !function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('order-received')) {
        return $template;
    }
    $custom = plugin_dir_path(__FILE__) . 'templates/thankyou.php';
    return is_readable($custom) ? $custom : $template;
}, 100, 2);

add_action('wp_enqueue_scripts', static function () {
    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
        wp_enqueue_style('mm-order-note', plugin_dir_url(__FILE__) . 'assets/order-note.css', array(), '1.0.0');
    }
}, 25);
