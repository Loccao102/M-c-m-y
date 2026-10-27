<?php
/** VNPAY PAY 2.1.0, Sandbox-only online payment for Mực Mây Studio theme. */
if ( ! defined('ABSPATH') ) { exit; }
require_once __DIR__ . '/signatures.php';

function mucmay_vnpay_theme_keys() {
    $settings = (array) get_option('woocommerce_mucmay_theme_vnpay_settings', array());
    $code = defined('MUC_MAY_VNPAY_TMN_CODE') ? constant('MUC_MAY_VNPAY_TMN_CODE') : ($settings['tmn_code'] ?? '');
    $secret = defined('MUC_MAY_VNPAY_HASH_SECRET') ? constant('MUC_MAY_VNPAY_HASH_SECRET') : ($settings['hash_secret'] ?? '');
    return array(trim((string) $code), trim((string) $secret));
}
function mucmay_vnpay_theme_url($name) {
    return add_query_arg('wc-api', $name, home_url('/'));
}
function mucmay_vnpay_theme_boot() {
    if (!class_exists('WC_Payment_Gateway') || class_exists('WC_Gateway_MucMay_Vnpay_Theme')) { return; }
    class WC_Gateway_MucMay_Vnpay_Theme extends WC_Payment_Gateway {
        public $tmn_code = '';
        private $secret = '';
        private $qr_only = false;
        public function __construct() {
            $this->id = 'mucmay_theme_vnpay';
            $this->method_title = 'Mực Mây · Thanh toán VNPAY Sandbox';
            $this->method_description = 'Thanh toán trực tuyến trong môi trường thử nghiệm VNPAY, không chuyển tiền thật.';
            $this->has_fields = false;
            $this->supports = array('products');
            $this->init_form_fields();
            $this->init_settings();
            $this->enabled = $this->get_option('enabled', 'no');
            $this->title = $this->get_option('title', 'Thanh toán online qua VNPAY');
            $this->description = $this->get_option('description', 'Thanh toán VNPAY Sandbox (không trừ tiền thật).');
            list($this->tmn_code, $this->secret) = mucmay_vnpay_theme_keys();
            $this->qr_only = 'yes' === $this->get_option('qr_only', 'no');
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array('title'=>'Kích hoạt', 'type'=>'checkbox', 'label'=>'Bật VNPAY Sandbox khi đã thiết lập merchant và IPN', 'default'=>'no'),
                'title' => array('title'=>'Tên ở checkout', 'type'=>'text', 'default'=>'Thanh toán online qua VNPAY'),
                'description' => array('title'=>'Mô tả ở checkout', 'type'=>'textarea', 'default'=>'Thanh toán VNPAY Sandbox (không trừ tiền thật).'),
                'tmn_code' => array('title'=>'TmnCode Sandbox', 'type'=>'text', 'default'=>'', 'description'=>'Lấy tại VNPAY Sandbox. Có thể đặt MUC_MAY_VNPAY_TMN_CODE trong wp-config.php.'),
                'hash_secret' => array('title'=>'HashSecret Sandbox', 'type'=>'password', 'default'=>'', 'description'=>'Không đưa secret lên GitHub. Có thể đặt MUC_MAY_VNPAY_HASH_SECRET trong wp-config.php.'),
                'qr_only' => array('title'=>'Chỉ dùng VNPAYQR', 'type'=>'checkbox', 'label'=>'Tắt nếu muốn chọn ngân hàng/phương thức trên trang VNPAY', 'default'=>'no'),
            );
        }
        public function is_available() {
            return parent::is_available() && 'VND' === get_woocommerce_currency() && '' !== $this->tmn_code && '' !== $this->secret;
        }
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            if (!$order || $order->get_payment_method() !== $this->id || $order->is_paid()) {
                wc_add_notice('Đơn hàng không hợp lệ hoặc đã thanh toán.', 'error');
                return array('result'=>'failure');
            }
            if (!$this->is_available() || 'VND' !== strtoupper((string) $order->get_currency())) {
                wc_add_notice('VNPAY Sandbox chưa được cấu hình đầy đủ cho đơn hàng.', 'error');
                return array('result'=>'failure');
            }
            $amount = (int) round((float) $order->get_total() * 100);
            if ($amount <= 0) {
                wc_add_notice('Tổng tiền không hợp lệ.', 'error');
                return array('result'=>'failure');
            }
            try { $suffix = bin2hex(random_bytes(6)); }
            catch (Exception $e) { $suffix = strtolower(wp_generate_password(12, false, false)); }
            $reference = $order_id . '-' . $suffix;
            $order->update_meta_data('_mucmay_vnpay_theme_reference', $reference);
            $order->update_meta_data('_mucmay_vnpay_theme_amount', $amount);
            $order->save();

            $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
            $ip = isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)
                ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
            $data = array(
                'vnp_Version'=>'2.1.0', 'vnp_Command'=>'pay', 'vnp_TmnCode'=>$this->tmn_code,
                'vnp_Amount'=>(string) $amount, 'vnp_CurrCode'=>'VND', 'vnp_TxnRef'=>$reference,
                'vnp_OrderInfo'=>'Thanh toan don hang ' . $order_id,
                'vnp_OrderType'=>'other', 'vnp_Locale'=>'vn', 'vnp_IpAddr'=>$ip,
                'vnp_ReturnUrl'=>mucmay_vnpay_theme_url('mucmay_theme_vnpay_return'),
                'vnp_CreateDate'=>$now->format('YmdHis'),
                'vnp_ExpireDate'=>$now->modify('+15 minutes')->format('YmdHis'),
            );
            if ($this->qr_only) { $data['vnp_BankCode'] = 'VNPAYQR'; }
            $data['vnp_SecureHash'] = mucmay_vnpay_theme_sign($data, $this->secret);
            ksort($data, SORT_STRING);
            $order->add_order_note('Đã tạo giao dịch VNPAY Sandbox, chờ xác nhận IPN.');
            return array('result'=>'success',
                'redirect'=>'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?' .
                    http_build_query($data, '', '&', PHP_QUERY_RFC1738));
        }
    }
}
add_action('init', 'mucmay_vnpay_theme_boot', 0);

function mucmay_vnpay_theme_register_gateway($gateways) {
    if (class_exists('WC_Gateway_MucMay_Vnpay_Theme')) { $gateways[] = 'WC_Gateway_MucMay_Vnpay_Theme'; }
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'mucmay_vnpay_theme_register_gateway', 15);

/** Block offline methods in classic and block checkout without changing stored settings. */
function mucmay_vnpay_theme_disable_offline($settings) {
    $settings = is_array($settings) ? $settings : array();
    $settings['enabled'] = 'no';
    return $settings;
}
foreach (array('cod', 'bacs', 'cheque') as $offline_id) {
    add_filter('option_woocommerce_' . $offline_id . '_settings', 'mucmay_vnpay_theme_disable_offline');
}
unset($offline_id);

/** IPN and browser callbacks are independent from gateway instantiation. */
function mucmay_vnpay_theme_request_data() {
    $result = array();
    foreach ($_GET as $key=>$value) {
        if (0 === strpos((string) $key, 'vnp_') && is_scalar($value)) {
            $result[$key] = (string) wp_unslash($value);
        }
    }
    return $result;
}
function mucmay_vnpay_theme_validate(array $data) {
    list($tmn, $secret) = mucmay_vnpay_theme_keys();
    if (!mucmay_vnpay_theme_verify($data, $secret)) {
        return array('97', 'Invalid signature', null);
    }
    if ('' === $tmn || !isset($data['vnp_TmnCode']) || !hash_equals($tmn, (string) $data['vnp_TmnCode'])) {
        return array('97', 'Invalid terminal', null);
    }
    $ref = isset($data['vnp_TxnRef']) ? (string) $data['vnp_TxnRef'] : '';
    if (!preg_match('/^([1-9][0-9]*)-[a-zA-Z0-9]{12}$/D', $ref, $match)) {
        return array('01', 'Invalid reference', null);
    }
    $order = function_exists('wc_get_order') ? wc_get_order((int) $match[1]) : false;
    if (!$order || 'mucmay_theme_vnpay' !== $order->get_payment_method() ||
        !hash_equals((string) $order->get_meta('_mucmay_vnpay_theme_reference'), $ref)) {
        return array('01', 'Order not found', null);
    }
    $sent = isset($data['vnp_Amount']) ? (string) $data['vnp_Amount'] : '';
    $stored = (string) $order->get_meta('_mucmay_vnpay_theme_amount');
    if (!ctype_digit($sent) || '' === $stored || $sent !== $stored ||
        $stored !== (string) (int) round((float) $order->get_total() * 100) ||
        'VND' !== $order->get_currency()) {
        return array('04', 'Invalid amount', null);
    }
    if (!isset($data['vnp_ResponseCode'], $data['vnp_TransactionStatus'])) {
        return array('99', 'Missing status', null);
    }
    return array('00', 'Valid', $order);
}
function mucmay_vnpay_theme_ipn() {
    nocache_headers();
    $data = mucmay_vnpay_theme_request_data();
    list($result, $message, $order) = mucmay_vnpay_theme_validate($data);
    if ('00' !== $result) { wp_send_json(array('RspCode'=>$result, 'Message'=>$message)); }
    if ($order->is_paid() || in_array($order->get_status(), array('failed', 'cancelled', 'refunded'), true)) {
        wp_send_json(array('RspCode'=>'02', 'Message'=>'Order already confirmed'));
    }
    if ('00' === $data['vnp_ResponseCode'] && '00' === $data['vnp_TransactionStatus']) {
        $transaction = isset($data['vnp_TransactionNo']) ? sanitize_text_field($data['vnp_TransactionNo']) : '';
        $order->payment_complete($transaction);
        $order->add_order_note('VNPAY Sandbox IPN đã xác nhận thanh toán.');
    } else {
        $order->update_status('failed', 'VNPAY Sandbox báo giao dịch không thành công.');
    }
    wp_send_json(array('RspCode'=>'00', 'Message'=>'Confirm Success'));
}
add_action('woocommerce_api_mucmay_theme_vnpay_ipn', 'mucmay_vnpay_theme_ipn');
function mucmay_vnpay_theme_return() {
    $data = mucmay_vnpay_theme_request_data();
    list($result, $message, $order) = mucmay_vnpay_theme_validate($data);
    if ('00' !== $result || !$order) {
        wp_die('Dữ liệu trả về từ VNPAY không hợp lệ.', 'VNPAY Sandbox', array('response'=>400));
    }
    // This is informational only; browser return NEVER marks an order paid.
    $status = '00' === $data['vnp_ResponseCode'] && '00' === $data['vnp_TransactionStatus']
        ? 'awaiting-ipn' : 'unsuccessful';
    wp_safe_redirect(add_query_arg('mucmay_vnpay_status', $status, $order->get_checkout_order_received_url()));
    exit;
}
add_action('woocommerce_api_mucmay_theme_vnpay_return', 'mucmay_vnpay_theme_return');
function mucmay_vnpay_theme_thankyou($order_id) {
    $order = function_exists('wc_get_order') ? wc_get_order($order_id) : false;
    if (!$order || 'mucmay_theme_vnpay' !== $order->get_payment_method() || $order->is_paid()) { return; }
    $result = isset($_GET['mucmay_vnpay_status']) ? sanitize_key(wp_unslash($_GET['mucmay_vnpay_status'])) : '';
    $text = 'unsuccessful' === $result
        ? 'Thanh toán chưa thành công. Đơn hàng chưa được đánh dấu đã thanh toán.'
        : 'Đơn hàng đang chờ thông báo xác nhận thanh toán từ VNPAY.';
    echo '<p class="woocommerce-info">' . esc_html($text) . '</p>';
}
add_action('woocommerce_before_thankyou', 'mucmay_vnpay_theme_thankyou');

/** Supports Checkout Blocks as well as Classic WooCommerce checkout. */
function mucmay_vnpay_theme_blocks($registry) {
    if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) { return; }
    if (!class_exists('MucMay_Vnpay_Theme_Blocks')) {
        class MucMay_Vnpay_Theme_Blocks extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
            protected $name = 'mucmay_theme_vnpay';
            private $settings = array();
            public function initialize() {
                $this->settings = (array) get_option('woocommerce_mucmay_theme_vnpay_settings', array());
            }
            public function is_active() {
                list($code, $secret) = mucmay_vnpay_theme_keys();
                return 'yes' === ($this->settings['enabled'] ?? 'no') &&
                    '' !== $code && '' !== $secret && 'VND' === get_woocommerce_currency();
            }
            public function get_payment_method_script_handles() {
                wp_register_script('mucmay-vnpay-theme-blocks',
                    get_template_directory_uri() . '/assets/js/vnpay-blocks.js',
                    array('wp-element', 'wp-html-entities', 'wc-blocks-registry', 'wc-settings'),
                    '2.3.0', true);
                return array('mucmay-vnpay-theme-blocks');
            }
            public function get_payment_method_data() {
                return array(
                    'title'=>$this->settings['title'] ?? 'Thanh toán online qua VNPAY',
                    'description'=>$this->settings['description'] ?? 'VNPAY Sandbox (không trừ tiền thật).',
                    'supports'=>array('products'),
                );
            }
        }
    }
    $registry->register(new MucMay_Vnpay_Theme_Blocks());
}
add_action('woocommerce_blocks_payment_method_type_registration', 'mucmay_vnpay_theme_blocks');

function mucmay_vnpay_theme_admin_menu() {
    if (!class_exists('WooCommerce')) { return; }
    add_submenu_page('woocommerce', 'Mực Mây · VNPAY', 'Mực Mây · VNPAY',
        'manage_woocommerce', 'mucmay-theme-vnpay', 'mucmay_vnpay_theme_admin_page');
}
add_action('admin_menu', 'mucmay_vnpay_theme_admin_menu');
function mucmay_vnpay_theme_admin_page() {
    if (!current_user_can('manage_woocommerce')) { return; }
    list($tmn, $secret) = mucmay_vnpay_theme_keys();
    echo '<div class="wrap"><h1>Mực Mây · VNPAY Sandbox</h1>';
    echo '<p>Thanh toán online trong theme; COD không khả dụng. Giao dịch Sandbox không dùng tiền thật.</p>';
    echo '<ol><li>Đăng ký TmnCode và HashSecret tại <a target="_blank" rel="noopener noreferrer" href="https://sandbox.vnpayment.vn/devreg/">VNPAY Sandbox</a>.</li>';
    echo '<li>Mở cấu hình bên dưới, nhập thông tin và bật phương thức VNPAY.</li>';
    echo '<li>Cấu hình IPN URL trong VNPAY Sandbox và thanh toán thử một đơn hàng.</li></ol>';
    echo '<p><strong>Tiền tệ:</strong> ' . esc_html(get_woocommerce_currency()) .
        ' · <strong>TmnCode:</strong> ' . ($tmn ? 'Đã có' : 'Chưa có') .
        ' · <strong>HashSecret:</strong> ' . ($secret ? 'Đã có' : 'Chưa có') . '</p>';
    echo '<p><strong>IPN URL:</strong> <code>' .
        esc_html(mucmay_vnpay_theme_url('mucmay_theme_vnpay_ipn')) . '</code></p>';
    echo '<p><a class="button button-primary" href="' .
        esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=mucmay_theme_vnpay')) .
        '">Cấu hình VNPAY Sandbox</a></p>';
    echo '<p>Chỉ IPN có chữ ký hợp lệ xác nhận thanh toán. Return URL không đánh dấu đơn đã thanh toán.</p></div>';
}
