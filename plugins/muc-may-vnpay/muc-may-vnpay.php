<?php
/**
 * Plugin Name: Mực Mây — VNPAY Sandbox cho WooCommerce
 * Description: Cổng thanh toán VNPAY PAY 2.1.0 (Sandbox) cho WooCommerce Classic và Checkout Blocks.
 * Version: 1.0.2
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Author: Mực Mây Studio
 * Text Domain: muc-may-vnpay
 */

if (!defined('ABSPATH')) { exit; }

define('MMVNPAY_VERSION', '1.0.2');
define('MMVNPAY_FILE', __FILE__);

require_once __DIR__ . '/includes/signature.php';

add_action('before_woocommerce_init', static function () {
    if (class_exists('Automattic\\WooCommerce\\Utilities\\FeaturesUtil')) {
        Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', MMVNPAY_FILE, true);
    }
});

function mmvnpay_settings() {
    return (array) get_option('woocommerce_mmvnpay_settings', array());
}
function mmvnpay_credentials() {
    $settings = mmvnpay_settings();
    $tmn = defined('MUC_MAY_VNPAY_TMN_CODE') ? MUC_MAY_VNPAY_TMN_CODE : ($settings['tmn_code'] ?? '');
    $secret = defined('MUC_MAY_VNPAY_HASH_SECRET') ? MUC_MAY_VNPAY_HASH_SECRET : ($settings['hash_secret'] ?? '');
    return array(trim((string) $tmn), trim((string) $secret));
}
function mmvnpay_endpoint($action) {
    return add_query_arg('wc-api', 'mmvnpay_' . $action, home_url('/'));
}
function mmvnpay_enabled() {
    list($tmn, $secret) = mmvnpay_credentials();
    $settings = mmvnpay_settings();
    return 'yes' === ($settings['enabled'] ?? 'no') && $tmn !== '' && $secret !== '' &&
        function_exists('get_woocommerce_currency') && 'VND' === get_woocommerce_currency();
}

add_action('plugins_loaded', static function () {
    if (!class_exists('WC_Payment_Gateway')) { return; }
    require_once __DIR__ . '/includes/class-gateway.php';
    add_filter('woocommerce_payment_gateways', static function ($gateways) {
        // The theme ships an older, independently configured VNPAY gateway.
        // Prefer this plugin to avoid two VNPAY options in Classic checkout.
        $gateways = array_filter($gateways, static function ($gateway) {
            return !in_array($gateway, array('WC_Gateway_MucMay_Vnpay_Theme', 'WC_Gateway_Muc_May_Vnpay'), true)
                && !(is_object($gateway) && in_array(get_class($gateway), array('WC_Gateway_MucMay_Vnpay_Theme', 'WC_Gateway_Muc_May_Vnpay'), true));
        });
        $gateways[] = 'MMVNPAY_Gateway';
        return array_values($gateways);
    }, 999);
}, 11);

/** Callbacks do not depend on the gateway having been instantiated during a WC-API request. */
function mmvnpay_request_data() {
    $result = array();
    foreach ($_GET as $key => $value) {
        if (is_string($key) && strpos($key, 'vnp_') === 0 && is_scalar($value)) {
            $result[$key] = (string) wp_unslash($value);
        }
    }
    return $result;
}

/** Returns [response code, message, order], in that order. */
function mmvnpay_validate_callback(array $data) {
    list($tmn, $secret) = mmvnpay_credentials();
    if (!mmvnpay_verify($data, $secret)) {
        return array('97', 'Invalid signature', null);
    }
    if ($tmn === '' || !isset($data['vnp_TmnCode']) || !hash_equals($tmn, $data['vnp_TmnCode'])) {
        return array('97', 'Invalid terminal', null);
    }
    $reference = isset($data['vnp_TxnRef']) ? (string) $data['vnp_TxnRef'] : '';
    if (!preg_match('/^MM([1-9][0-9]*)T[0-9]{14}R[a-f0-9]{10}$/D', $reference, $matches)) {
        return array('01', 'Invalid transaction reference', null);
    }
    $order = function_exists('wc_get_order') ? wc_get_order((int) $matches[1]) : false;
    if (!$order || $order->get_payment_method() !== 'mmvnpay') {
        return array('01', 'Order not found', null);
    }
    $attempts = $order->get_meta('_mmvnpay_attempts', true);
    $amount = isset($data['vnp_Amount']) ? (string) $data['vnp_Amount'] : '';
    $expected = is_array($attempts) && isset($attempts[$reference]) ? (string) $attempts[$reference] : '';
    if ($expected === '') {
        return array('01', 'Unknown payment attempt', null);
    }
    if (!ctype_digit($amount) || $amount !== $expected || $amount !== (string) (int) round((float) $order->get_total() * 100)
        || 'VND' !== $order->get_currency()) {
        return array('04', 'Invalid amount', null);
    }
    if (!isset($data['vnp_ResponseCode'], $data['vnp_TransactionStatus']) ||
        !preg_match('/^[0-9]{2}$/', (string) $data['vnp_ResponseCode']) ||
        !preg_match('/^[0-9]{2}$/', (string) $data['vnp_TransactionStatus'])) {
        return array('99', 'Missing or invalid payment status', null);
    }
    return array('00', 'Valid', $order);
}

function mmvnpay_ipn() {
    nocache_headers();
    $data = mmvnpay_request_data();
    list($code, $message, $order) = mmvnpay_validate_callback($data);
    if ('00' !== $code) {
        wp_send_json(array('RspCode' => $code, 'Message' => $message), 200);
    }
    if ($order->is_paid()) {
        wp_send_json(array('RspCode' => '02', 'Message' => 'Order already confirmed'), 200);
    }
    // A late success on an explicitly cancelled/refunded order needs manual reconciliation.
    if (in_array($order->get_status(), array('cancelled', 'refunded'), true)) {
        $order->add_order_note('VNPAY IPN arrived after order cancellation/refund. Review in merchant portal.');
        wp_send_json(array('RspCode' => '99', 'Message' => 'Manual reconciliation required'), 200);
    }
    if ($data['vnp_ResponseCode'] === '00' && $data['vnp_TransactionStatus'] === '00') {
        $transaction_no = isset($data['vnp_TransactionNo']) && ctype_digit((string) $data['vnp_TransactionNo'])
            ? (string) $data['vnp_TransactionNo'] : '';
        if ($transaction_no === '') {
            wp_send_json(array('RspCode' => '99', 'Message' => 'Missing transaction number'), 200);
        }
        $order->payment_complete($transaction_no);
        $order->add_order_note('VNPAY Sandbox: IPN hợp lệ, thanh toán thành công. Ref: ' . sanitize_text_field($data['vnp_TxnRef']));
    } else {
        $order->add_order_note('VNPAY Sandbox: giao dịch không thành công. Mã: ' . sanitize_text_field($data['vnp_ResponseCode']));
        $settings = mmvnpay_settings();
        $last = (string) $order->get_meta('_mmvnpay_latest_ref', true);
        if ($last === $data['vnp_TxnRef'] && $order->has_status(array('pending', 'on-hold'))) {
            $order->update_status('failed', 'VNPAY Sandbox báo thanh toán không thành công.');
        }
    }
    wp_send_json(array('RspCode' => '00', 'Message' => 'Confirm Success'), 200);
}
add_action('woocommerce_api_mmvnpay_ipn', 'mmvnpay_ipn');

/**
 * Demo-only fallback when a Sandbox merchant has no configured IPN.
 * Every successful browser return is signed by VNPAY and checked against a
 * locally stored attempt, total, currency, terminal and transaction number.
 * For real-money production use independent IPN/QueryDR confirmation instead.
 */
function mmvnpay_return() {
    nocache_headers();
    $data = mmvnpay_request_data();
    list($code, , $order) = mmvnpay_validate_callback($data);
    if ('00' !== $code || !$order) {
        wp_die(esc_html__('Dữ liệu trả về từ VNPAY không hợp lệ.', 'muc-may-vnpay'), 'VNPAY', array('response' => 400));
    }
    $success = $data['vnp_ResponseCode'] === '00' && $data['vnp_TransactionStatus'] === '00';
    $reference = (string) $data['vnp_TxnRef'];
    $latest = (string) $order->get_meta('_mmvnpay_latest_ref', true);
    $settings = mmvnpay_settings();
    $demo_confirm = ($settings['sandbox_return_confirm'] ?? 'yes') === 'yes';

    if ($success && !$order->is_paid() && $demo_confirm && $latest === $reference &&
        $order->has_status(array('pending', 'on-hold'))) {
        $transaction_no = (string) ($data['vnp_TransactionNo'] ?? '');
        if (preg_match('/^[0-9]{1,15}$/D', $transaction_no)) {
            $order->update_meta_data('_mmvnpay_confirmed_via', 'sandbox_signed_return');
            $order->save();
            $order->payment_complete($transaction_no);
            $order->add_order_note('VNPAY Sandbox: xác nhận qua Return URL có chữ ký hợp lệ (chế độ demo, không thay thế IPN/QueryDR production). Ref: ' . sanitize_text_field($reference));
        } else {
            $order->add_order_note('VNPAY Sandbox Return: thiếu mã giao dịch hợp lệ, chưa xác nhận thanh toán.');
        }
    } elseif (!$success && !$order->is_paid() && $latest === $reference &&
        $order->has_status(array('pending', 'on-hold'))) {
        $order->update_status('failed', 'VNPAY Sandbox Return báo giao dịch không thành công: ' . sanitize_text_field($data['vnp_ResponseCode']));
    }

    $outcome = $order->is_paid() ? 'success' : ($success ? 'pending' : 'failed');
    wp_safe_redirect(add_query_arg('mmvnpay_result', $outcome, $order->get_checkout_order_received_url()));
    exit;
}
add_action('woocommerce_api_mmvnpay_return', 'mmvnpay_return');

/** Brand-consistent WooCommerce receipt, based on authoritative WC order state. */
function mmvnpay_receipt_order() {
    if (!function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('order-received')) { return false; }
    $id = absint(get_query_var('order-received', 0));
    $order = $id ? wc_get_order($id) : false;
    if (!$order || $order->get_payment_method() !== 'mmvnpay') { return false; }
    $provided_key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : '';
    if (!$provided_key || !hash_equals((string) $order->get_order_key(), (string) $provided_key)) { return false; }
    return $order;
}
add_filter('body_class', static function ($classes) {
    if (mmvnpay_receipt_order()) { $classes[] = 'mmvnpay-receipt'; }
    return $classes;
});
add_action('wp_enqueue_scripts', static function () {
    if (!mmvnpay_receipt_order()) { return; }
    wp_enqueue_style('mmvnpay-receipt', plugins_url('assets/receipt.css', MMVNPAY_FILE), array(), MMVNPAY_VERSION);
});
add_action('woocommerce_before_thankyou', static function ($order_id) {
    $order = mmvnpay_receipt_order();
    if (!$order || (int) $order->get_id() !== (int) $order_id) { return; }
    $paid = $order->is_paid();
    $failed = $order->has_status(array('failed', 'cancelled'));
    $title = $paid ? 'Thanh toán thành công!' : ($failed ? 'Thanh toán chưa hoàn tất' : 'Đơn hàng đang được xác nhận');
    $note = $paid
        ? 'Cảm ơn bạn đã ghé Mực Mây. Đơn hàng đã được ghi nhận thanh toán và đang được chuẩn bị.'
        : ($failed ? 'Giao dịch chưa thành công. Bạn có thể thử thanh toán lại khi sẵn sàng.'
            : 'Chúng mình đã nhận đơn hàng và đang chờ xác nhận kết quả giao dịch.');
    $state = $paid ? 'success' : ($failed ? 'failed' : 'pending');
    $status = $paid ? 'ĐÃ THANH TOÁN' : ($failed ? 'CHƯA THANH TOÁN' : 'ĐANG XÁC NHẬN');
    echo '<section class="mmvnpay-receipt-hero mmvnpay-receipt-hero--' . esc_attr($state) . '" aria-label="Kết quả đơn hàng">';
    echo '<div class="mmvnpay-receipt-topline"><span>MỰC MÂY STUDIO</span><span>THANK YOU NOTE / ' . esc_html($order->get_order_number()) . '</span></div>';
    echo '<div class="mmvnpay-receipt-body"><div class="mmvnpay-receipt-mark" aria-hidden="true">' . ($paid ? '&#10003;' : ($failed ? '!' : '&#8230;')) . '</div>';
    echo '<span class="mmvnpay-receipt-eyebrow">' . esc_html($status) . '</span><h1>' . esc_html($title) . '</h1>';
    echo '<p>' . esc_html($note) . '</p>';
    echo '<div class="mmvnpay-receipt-facts"><div><span>MÃ ĐƠN</span><strong>#' . esc_html($order->get_order_number()) . '</strong></div>';
    echo '<div><span>NGÀY ĐẶT</span><strong>' . esc_html(wc_format_datetime($order->get_date_created())) . '</strong></div>';
    echo '<div><span>TỔNG CỘNG</span><strong>' . wp_kses_post($order->get_formatted_order_total()) . '</strong></div></div>';
    echo '</div><div class="mmvnpay-receipt-footer"><span>Một chút giấy, một chút mực, một chút niềm vui.</span><span>✳ MỰC MÂY</span></div></section>';
}, 8);

/** WooCommerce Checkout Blocks. */
add_action('woocommerce_blocks_loaded', static function () {
    if (!class_exists('Automattic\\WooCommerce\\Blocks\\Payments\\Integrations\\AbstractPaymentMethodType')) { return; }
    class MMVNPAY_Block_Integration extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
        protected $name = 'mmvnpay';
        public function initialize() {
            $this->settings = mmvnpay_settings();
        }
        public function is_active() {
            return mmvnpay_enabled();
        }
        public function get_payment_method_script_handles() {
            wp_register_script('mmvnpay-checkout', plugins_url('assets/checkout.js', MMVNPAY_FILE),
                array('wp-element', 'wp-html-entities', 'wc-blocks-registry', 'wc-settings'), MMVNPAY_VERSION, true);
            return array('mmvnpay-checkout');
        }
        public function get_payment_method_data() {
            return array(
                'title' => $this->settings['title'] ?? 'Thanh toán VNPAY',
                'description' => $this->settings['description'] ?? 'Thanh toán qua cổng VNPAY Sandbox.',
                'supports' => array('products'),
            );
        }
    }
    add_action('woocommerce_blocks_payment_method_type_registration', static function ($registry) {
        $registry->register(new MMVNPAY_Block_Integration());
    });
});

/** Read-only admin diagnostic with exact registered URLs; no credential leakage. */
add_action('admin_menu', static function () {
    if (!class_exists('WooCommerce')) { return; }
    add_submenu_page('woocommerce', 'Mực Mây · VNPAY', 'Mực Mây · VNPAY', 'manage_woocommerce',
        'mmvnpay-dashboard', static function () {
            if (!current_user_can('manage_woocommerce')) { return; }
            list($tmn, $secret) = mmvnpay_credentials();
            echo '<div class="wrap"><h1>Mực Mây · VNPAY Sandbox</h1>';
            echo '<p><strong>Chỉ dành cho môi trường thử nghiệm.</strong> Plugin không hỗ trợ tiền thật.</p>';
            echo '<p>Tiền tệ: <strong>' . esc_html(get_woocommerce_currency()) . '</strong> — TmnCode: <strong>' . ($tmn ? 'Đã có' : 'Chưa nhập') . '</strong> — HashSecret: <strong>' . ($secret ? 'Đã có' : 'Chưa nhập') . '</strong> — Trạng thái: <strong>' . (mmvnpay_enabled() ? 'Đã bật' : 'Chưa sẵn sàng') . '</strong></p>';
            echo '<p><strong>IPN URL đăng ký tại VNPAY:</strong> <code>' . esc_html(mmvnpay_endpoint('ipn')) . '</code></p>';
            echo '<p><strong>Return URL:</strong> <code>' . esc_html(mmvnpay_endpoint('return')) . '</code></p>';
            echo '<p><a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=mmvnpay')) . '">Cấu hình phương thức thanh toán</a></p>';
            echo '<p>Sandbox có chế độ xác nhận qua Return URL ký hợp lệ khi không có IPN. Chỉ dùng cho thử nghiệm, không phải production. Giữ IPN để hỗ trợ xác nhận server-to-server.</p>';
            echo '<p>Nếu đang dùng cổng VNPAY khác, hãy tắt nó trong WooCommerce → Thanh toán và tắt plugin VNPAY cũ để tránh nhầm lẫn.</p></div>';
        });
});
