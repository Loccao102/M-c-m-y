<?php
/**
 * Plugin Name: Mực Mây Payments — VNPAY Sandbox + COD
 * Description: Thanh toán VNPAY QR ở Sandbox cho WooCommerce Checkout Blocks và Classic; kích hoạt COD làm phương án dự phòng.
 * Version: 1.0.0
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Author: Mực Mây Studio
 * Text Domain: muc-may-payments
 */
if (!defined('ABSPATH')) { exit; }

define('MM_PAYMENTS_FILE', __FILE__);
require_once __DIR__ . '/includes/signatures.php';

register_activation_hook(__FILE__, function () {
    // Only set COD defaults when it was never configured. Do not override existing choices.
    if (null === get_option('woocommerce_cod_settings', null)) {
        update_option('woocommerce_cod_settings', array(
            'enabled' => 'yes',
            'title' => 'Thanh toán khi nhận hàng (COD)',
            'description' => 'Thanh toán bằng tiền mặt khi nhận sản phẩm.',
            'instructions' => 'Đây là đơn đặt hàng demo. Không chuyển tiền trước.',
            'enable_for_methods' => array(),
            'enable_for_virtual' => 'yes',
        ));
    }
});

add_action('plugins_loaded', function () {
    if (!class_exists('WC_Payment_Gateway')) { return; }

    class WC_Gateway_Muc_May_Vnpay extends WC_Payment_Gateway {
        public $tmn_code;
        private $hash_secret;
        private $bank_code;

        public function __construct() {
            $this->id = 'mucmay_vnpay';
            $this->method_title = 'Mực Mây — VNPAY QR (Sandbox)';
            $this->method_description = 'Chỉ dùng để thử nghiệm: thanh toán bằng mã QR VNPAY trên môi trường Sandbox.';
            $this->has_fields = false;
            $this->supports = array('products');
            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title', 'VNPAY QR (thử nghiệm)');
            $this->description = $this->get_option('description', 'Thanh toán QR trong môi trường VNPAY Sandbox; không chuyển tiền thật.');
            $this->enabled = $this->get_option('enabled', 'no');
            $this->tmn_code = defined('MUC_MAY_VNPAY_TMN_CODE') ? MUC_MAY_VNPAY_TMN_CODE : $this->get_option('tmn_code', '');
            $this->hash_secret = defined('MUC_MAY_VNPAY_HASH_SECRET') ? MUC_MAY_VNPAY_HASH_SECRET : $this->get_option('hash_secret', '');
            $this->bank_code = $this->get_option('qr_only', 'yes') === 'yes' ? 'VNPAYQR' : '';

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_api_mucmay_vnpay_ipn', array($this, 'handle_ipn'));
            add_action('woocommerce_api_mucmay_vnpay_return', array($this, 'handle_return'));
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array('title' => 'Bật thanh toán Sandbox', 'type' => 'checkbox', 'label' => 'Chỉ bật khi có mã Sandbox hợp lệ và đã cấu hình IPN', 'default' => 'no'),
                'title' => array('title' => 'Tên hiển thị', 'type' => 'text', 'default' => 'VNPAY QR (thử nghiệm)'),
                'description' => array('title' => 'Mô tả', 'type' => 'textarea', 'default' => 'Thanh toán thử nghiệm qua VNPAY Sandbox; không chuyển tiền thật.'),
                'tmn_code' => array('title' => 'TmnCode Sandbox', 'type' => 'text', 'description' => 'Nhận từ VNPAY. Có thể định nghĩa MUC_MAY_VNPAY_TMN_CODE trong wp-config.php.', 'default' => ''),
                'hash_secret' => array('title' => 'HashSecret Sandbox', 'type' => 'password', 'description' => 'Giữ bí mật, không commit lên GitHub. Có thể định nghĩa MUC_MAY_VNPAY_HASH_SECRET trong wp-config.php.', 'default' => ''),
                'qr_only' => array('title' => 'Ưu tiên QR', 'type' => 'checkbox', 'label' => 'Chỉ mở VNPAYQR (tắt nếu Sandbox không hỗ trợ)', 'default' => 'yes'),
            );
        }

        public function is_available() {
            return parent::is_available() && get_woocommerce_currency() === 'VND' &&
                (bool) $this->tmn_code && (bool) $this->hash_secret;
        }

        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            if (!$order || $order->get_payment_method() !== $this->id || $order->is_paid()) {
                wc_add_notice('Đơn hàng không hợp lệ hoặc đã thanh toán.', 'error');
                return array('result' => 'failure');
            }
            if (strtoupper((string) $order->get_currency()) !== 'VND' || !$this->tmn_code || !$this->hash_secret) {
                wc_add_notice('VNPAY Sandbox chưa được cấu hình hoặc tiền tệ không phải VND.', 'error');
                return array('result' => 'failure');
            }
            $amount = (int) round((float) $order->get_total() * 100);
            if ($amount <= 0) {
                wc_add_notice('Giá trị đơn hàng không hợp lệ.', 'error');
                return array('result' => 'failure');
            }
            try { $suffix = bin2hex(random_bytes(6)); }
            catch (Exception $e) { $suffix = wp_generate_password(12, false, false); }
            $ref = $order_id . '-' . $suffix;
            $order->update_meta_data('_mm_vnpay_ref', $ref);
            $order->update_meta_data('_mm_vnpay_amount', $amount);
            $order->save();
            $date = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
            $ip = isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)
                ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
            $data = array(
                'vnp_Version' => '2.1.0', 'vnp_Command' => 'pay', 'vnp_TmnCode' => $this->tmn_code,
                'vnp_Amount' => (string) $amount, 'vnp_CurrCode' => 'VND',
                'vnp_TxnRef' => $ref, 'vnp_OrderInfo' => 'Thanh toan don hang ' . $order_id,
                'vnp_OrderType' => 'other', 'vnp_Locale' => 'vn', 'vnp_IpAddr' => $ip,
                'vnp_ReturnUrl' => $this->callback_url('mucmay_vnpay_return'),
                'vnp_CreateDate' => $date->format('YmdHis'),
                'vnp_ExpireDate' => $date->modify('+15 minutes')->format('YmdHis'),
            );
            if ($this->bank_code) { $data['vnp_BankCode'] = $this->bank_code; }
            $data['vnp_SecureHash'] = mm_vnpay_sign($data, $this->hash_secret);
            $order->add_order_note('Đã khởi tạo giao dịch VNPAY Sandbox, chờ IPN xác nhận.');
            return array('result' => 'success', 'redirect' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?' . mm_vnpay_query_with_hash($data));
        }

        private function callback_url($action) {
            return add_query_arg('wc-api', $action, home_url('/'));
        }

        private function callback_data() {
            $data = array();
            foreach ($_GET as $k => $v) {
                if (strncmp((string) $k, 'vnp_', 4) === 0 && is_scalar($v)) {
                    $data[$k] = (string) wp_unslash($v);
                }
            }
            return $data;
        }

        private function validate_callback($data) {
            if (!mm_vnpay_verified($data, $this->hash_secret)) { return array('97', 'Invalid Signature', null); }
            if (!isset($data['vnp_TmnCode']) || !hash_equals((string) $this->tmn_code, (string) $data['vnp_TmnCode'])) {
                return array('97', 'Invalid Terminal', null);
            }
            if (empty($data['vnp_TxnRef']) || !preg_match('/^([1-9][0-9]*)-[a-zA-Z0-9]{12}$/', $data['vnp_TxnRef'], $m)) {
                return array('01', 'Invalid transaction reference', null);
            }
            $order = wc_get_order((int) $m[1]);
            if (!$order || $order->get_payment_method() !== $this->id ||
                !hash_equals((string) $order->get_meta('_mm_vnpay_ref'), (string) $data['vnp_TxnRef'])) {
                return array('01', 'Order not found', null);
            }
            $sent = isset($data['vnp_Amount']) ? (string) $data['vnp_Amount'] : '';
            $expected = (string) $order->get_meta('_mm_vnpay_amount');
            if (!ctype_digit($sent) || $sent !== $expected || $expected !== (string) (int) round((float) $order->get_total() * 100) || $order->get_currency() !== 'VND') {
                return array('04', 'Invalid amount', null);
            }
            return array('00', 'Valid', $order);
        }

        public function handle_ipn() {
            nocache_headers();
            $data = $this->callback_data();
            list($code, $message, $order) = $this->validate_callback($data);
            if ($code !== '00') { wp_send_json(array('RspCode' => $code, 'Message' => $message), 200); }
            if ($order->is_paid() || in_array($order->get_status(), array('failed', 'cancelled', 'refunded'), true)) {
                wp_send_json(array('RspCode' => '02', 'Message' => 'Order already confirmed'), 200);
            }
            if (empty($data['vnp_ResponseCode']) || empty($data['vnp_TransactionStatus'])) {
                wp_send_json(array('RspCode' => '99', 'Message' => 'Missing status'), 200);
            }
            if ($data['vnp_ResponseCode'] === '00' && $data['vnp_TransactionStatus'] === '00') {
                $transaction = isset($data['vnp_TransactionNo']) ? sanitize_text_field($data['vnp_TransactionNo']) : '';
                $order->payment_complete($transaction);
                $order->add_order_note('VNPAY Sandbox IPN xác nhận thanh toán thành công.');
            } else {
                $order->update_status('failed', 'VNPAY Sandbox IPN báo thanh toán không thành công.');
            }
            wp_send_json(array('RspCode' => '00', 'Message' => 'Confirm Success'), 200);
        }

        public function handle_return() {
            $data = $this->callback_data();
            list($code, $message, $order) = $this->validate_callback($data);
            if ($code !== '00' || !$order) {
                wp_die(esc_html('Dữ liệu trả về từ VNPAY không hợp lệ. Không thể xác nhận giao dịch.'), 'VNPAY Sandbox', array('response' => 400));
            }
            // VNPAY: browser return is informational, payment status is ONLY updated by signed IPN.
            $result = ($data['vnp_ResponseCode'] ?? '') === '00' && ($data['vnp_TransactionStatus'] ?? '') === '00'
                ? 'awaiting-ipn' : 'unsuccessful';
            wp_safe_redirect(add_query_arg('mm_vnpay_result', $result, $this->get_return_url($order)));
            exit;
        }
    }

    add_filter('woocommerce_payment_gateways', function ($methods) {
        $methods[] = 'WC_Gateway_Muc_May_Vnpay';
        return $methods;
    });

    add_action('woocommerce_before_thankyou', function ($order_id) {
        if (!isset($_GET['mm_vnpay_result'])) { return; }
        $order = wc_get_order($order_id);
        if (!$order || $order->get_payment_method() !== 'mucmay_vnpay') { return; }
        $result = sanitize_key(wp_unslash($_GET['mm_vnpay_result']));
        if ($result === 'awaiting-ipn' && !$order->is_paid()) {
            wc_print_notice('VNPAY đã chuyển bạn về cửa hàng. Đơn đang chờ thông báo xác nhận thanh toán (IPN).', 'notice');
        } elseif ($result === 'unsuccessful') {
            wc_print_notice('Thanh toán chưa hoàn tất. Vui lòng kiểm tra trạng thái đơn hàng.', 'notice');
        }
    });
});

/** Preserve secure hash on the outgoing payment URL (normal canonical helper strips it). */
function mm_vnpay_query_with_hash(array $params) {
    ksort($params, SORT_STRING);
    return http_build_query($params, '', '&', PHP_QUERY_RFC1738);
}

add_action('woocommerce_blocks_loaded', function () {
    if (!class_exists('Automattic\\WooCommerce\\Blocks\\Payments\\Integrations\\AbstractPaymentMethodType')) { return; }
    class MM_Vnpay_Block_Integration extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
        protected $name = 'mucmay_vnpay';
        private $settings;
        public function initialize() { $this->settings = get_option('woocommerce_mucmay_vnpay_settings', array()); }
        public function is_active() {
            $code = defined('MUC_MAY_VNPAY_TMN_CODE') ? MUC_MAY_VNPAY_TMN_CODE : ($this->settings['tmn_code'] ?? '');
            $secret = defined('MUC_MAY_VNPAY_HASH_SECRET') ? MUC_MAY_VNPAY_HASH_SECRET : ($this->settings['hash_secret'] ?? '');
            return ($this->settings['enabled'] ?? 'no') === 'yes' && (bool) $code && (bool) $secret && get_woocommerce_currency() === 'VND';
        }
        public function get_payment_method_script_handles() {
            wp_register_script('mm-vnpay-blocks', plugins_url('assets/blocks.js', MM_PAYMENTS_FILE),
                array('wp-element', 'wp-html-entities', 'wc-blocks-registry', 'wc-settings'), '1.0.0', true);
            return array('mm-vnpay-blocks');
        }
        public function get_payment_method_data() {
            return array(
                'title' => $this->settings['title'] ?? 'VNPAY QR (thử nghiệm)',
                'description' => $this->settings['description'] ?? 'Thanh toán thử nghiệm qua VNPAY Sandbox.',
                'supports' => array('products'),
            );
        }
    }
    add_action('woocommerce_blocks_payment_method_type_registration', function ($registry) {
        $registry->register(new MM_Vnpay_Block_Integration());
    });
});

add_action('admin_menu', function () {
    add_submenu_page('woocommerce', 'Mực Mây — Thanh toán', 'Mực Mây thanh toán', 'manage_woocommerce', 'mucmay-payments', function () {
        $settings = get_option('woocommerce_mucmay_vnpay_settings', array());
        $code = defined('MUC_MAY_VNPAY_TMN_CODE') ? MUC_MAY_VNPAY_TMN_CODE : ($settings['tmn_code'] ?? '');
        $secret = defined('MUC_MAY_VNPAY_HASH_SECRET') ? MUC_MAY_VNPAY_HASH_SECRET : ($settings['hash_secret'] ?? '');
        $url = add_query_arg('wc-api', 'mucmay_vnpay_ipn', home_url('/'));
        echo '<div class="wrap"><h1>Mực Mây — Thanh toán thử nghiệm</h1><p>Chỉ dùng VNPAY Sandbox. Không có chế độ thanh toán thật trong plugin này.</p>';
        echo '<p><strong>Checkout Blocks:</strong> Hỗ trợ. <strong>VND:</strong> ' . (get_woocommerce_currency() === 'VND' ? 'OK' : 'Cần chọn VND') . '.</p>';
        echo '<p><strong>TmnCode:</strong> ' . ($code ? 'Đã cấu hình' : 'Chưa có') . '. <strong>HashSecret:</strong> ' . ($secret ? 'Đã cấu hình' : 'Chưa có') . '.</p>';
        echo '<p><strong>IPN URL cần đăng ký với VNPAY:</strong> <code>' . esc_html($url) . '</code></p>';
        echo '<p><a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=mucmay_vnpay')) . '">Cấu hình VNPAY Sandbox</a></p>';
        echo '<p>Chỉ IPN có chữ ký hợp lệ mới đánh dấu đơn đã thanh toán; việc khách mở trang thông báo thành công không cập nhật trạng thái đơn.</p></div>';
    });
});