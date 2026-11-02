<?php
if (!defined('ABSPATH')) { exit; }

class MMVNPAY_Gateway extends WC_Payment_Gateway {
    public $tmn_code = '';
    private $secret = '';
    private $qr_only = false;

    public function __construct() {
        $this->id = 'mmvnpay';
        $this->method_title = 'Mực Mây — VNPAY Sandbox';
        $this->method_description = 'Thanh toán VNPAY 2.1.0 trên Sandbox. Không thu tiền thật.';
        $this->has_fields = false;
        $this->supports = array('products');
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->get_option('title', 'Thanh toán online qua VNPAY');
        $this->description = $this->get_option('description', 'Thanh toán trực tuyến qua VNPAY Sandbox (thử nghiệm).');
        $this->enabled = $this->get_option('enabled', 'no');
        list($this->tmn_code, $this->secret) = mmvnpay_credentials();
        $this->qr_only = 'yes' === $this->get_option('qr_only', 'no');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array('title' => 'Bật VNPAY Sandbox', 'type' => 'checkbox',
                'label' => 'Bật sau khi đã nhận mã Sandbox', 'default' => 'no'),
            'title' => array('title' => 'Tên ở trang thanh toán', 'type' => 'text', 'default' => 'Thanh toán online qua VNPAY'),
            'description' => array('title' => 'Mô tả', 'type' => 'textarea',
                'default' => 'Thanh toán trực tuyến qua VNPAY Sandbox (thử nghiệm).'),
            'tmn_code' => array('title' => 'TmnCode (Sandbox)', 'type' => 'text', 'default' => '',
                'description' => 'Mã website VNPAY cấp; không phải mã tài khoản đăng nhập.'),
            'hash_secret' => array('title' => 'HashSecret (Sandbox)', 'type' => 'password', 'default' => '',
                'description' => 'Giữ bí mật. Để trống khi lưu lại nhằm giữ khóa đã nhập.'),
            'sandbox_return_confirm' => array('title' => 'Xác nhận qua Return (Sandbox)', 'type' => 'checkbox', 'default' => 'yes',
                'label' => 'Tự xác nhận đơn sau Return URL có chữ ký hợp lệ khi chưa cấu hình IPN',
                'description' => 'Chỉ dùng cho Sandbox/demo. Vận hành tiền thật phải dùng IPN hoặc API QueryDR kiểm tra phía máy chủ.'),
            'qr_only' => array('title' => 'Ưu tiên VNPAYQR', 'type' => 'checkbox', 'default' => 'no',
                'label' => 'Chỉ mở QR VNPAY (nên để tắt khi lần đầu thử nghiệm).'),
        );
    }

    /** Prevent a blank password input from deleting a previously stored HashSecret. */
    public function validate_hash_secret_field($key, $value) {
        $value = trim((string) $value);
        return $value !== '' ? sanitize_text_field($value) : (string) $this->get_option('hash_secret', '');
    }

    public function is_available() {
        return parent::is_available() && mmvnpay_enabled();
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        if (!$order || $order->get_payment_method() !== $this->id || $order->is_paid() || !$this->is_available()
            || $order->get_currency() !== 'VND') {
            wc_add_notice('Đơn hàng hoặc cấu hình VNPAY không hợp lệ.', 'error');
            return array('result' => 'failure');
        }
        $amount = (int) round((float) $order->get_total() * 100);
        if ($amount < 100 || $amount > 999999999999) {
            wc_add_notice('Số tiền VNPAY không hợp lệ.', 'error');
            return array('result' => 'failure');
        }
        try { $random = bin2hex(random_bytes(5)); }
        catch (Exception $e) {
            wc_add_notice('Không thể tạo mã giao dịch an toàn. Vui lòng thử lại.', 'error');
            return array('result' => 'failure');
        }
        $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        // Alphanumeric per VNPAY: MM + Woo order ID + T + timestamp + R + 10 hex digits.
        $ref = 'MM' . $order_id . 'T' . $now->format('YmdHis') . 'R' . $random;
        $attempts = $order->get_meta('_mmvnpay_attempts', true);
        if (!is_array($attempts)) { $attempts = array(); }
        $attempts[$ref] = (string) $amount;
        $order->update_meta_data('_mmvnpay_attempts', $attempts);
        $order->update_meta_data('_mmvnpay_latest_ref', $ref);
        $order->save();

        $ip = isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)
            ? (string) $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $params = array(
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $this->tmn_code,
            'vnp_Amount' => (string) $amount,
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $ref,
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $order_id . ' tai Muc May',
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_IpAddr' => $ip,
            'vnp_ReturnUrl' => mmvnpay_endpoint('return'),
            'vnp_CreateDate' => $now->format('YmdHis'),
            'vnp_ExpireDate' => $now->modify('+15 minutes')->format('YmdHis'),
        );
        if ($this->qr_only) { $params['vnp_BankCode'] = 'VNPAYQR'; }
        $order->add_order_note('Đã chuyển tới VNPAY Sandbox, chờ IPN xác nhận. Ref: ' . $ref);
        return array('result' => 'success', 'redirect' => mmvnpay_payment_url($params, $this->secret));
    }
}
