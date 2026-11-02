<?php
/**
 * Custom WooCommerce checkout/thankyou.php replacement for Mực Mây.
 * $order is provided by WooCommerce after access/email verification.
 * Never trust the 'mmvnpay_result' query parameter as payment proof.
 *
 * @var WC_Order|false $order
 */
defined('ABSPATH') || exit;

$has_order = $order instanceof WC_Order;
$paid = $has_order && $order->is_paid();
$failed = $has_order && $order->has_status(array('failed', 'cancelled', 'refunded'));
$state = $paid ? 'paid' : ($failed ? 'failed' : 'pending');
$label = $paid ? 'ĐÃ THANH TOÁN' : ($failed ? 'CHƯA THANH TOÁN' : 'ĐANG XÁC NHẬN');
$title = $paid ? "Một chiếc đơn hàng\nđã về với Mực Mây." : ($failed ? "Chuyến ghé thăm\nchưa trọn vẹn." : "Mực Mây đã nhận\nđơn của bạn.");
$intro = $paid
    ? 'Cảm ơn bạn đã chọn những món nhỏ xinh tại Mực Mây. Chúng mình sẽ gói ghém đơn hàng thật cẩn thận để gửi đến bạn.'
    : ($failed
        ? 'Thanh toán chưa được ghi nhận. Bạn có thể thử lại hoặc quay về cửa tiệm để tiếp tục khám phá.'
        : 'Đơn hàng đã được tạo. Chúng mình đang chờ hệ thống thanh toán xác nhận, bạn chưa cần đặt lại đơn mới.');
?>
<main class="mm-note mm-note--<?php echo esc_attr($state); ?>" id="mm-note-main">
    <div class="mm-note__container">
        <div class="mm-note__breadcrumb" aria-label="Điều hướng">
            <a href="<?php echo esc_url(home_url('/')); ?>">MỰC MÂY STUDIO</a>
            <span aria-hidden="true">/</span>
            <span>THƯ XÁC NHẬN ĐƠN HÀNG</span>
        </div>

        <section class="mm-note__letter" aria-labelledby="mm-note-title">
            <div class="mm-note__letter-top"><span>GỬI BẠN, TỪ MỰC MÂY</span><span class="mm-note__issue">NOTE N° <?php echo $has_order ? esc_html($order->get_order_number()) : '—'; ?></span></div>
            <div class="mm-note__letter-inner">
                <div class="mm-note__hero-content">
                    <div class="mm-note__tag"><span class="mm-note__tag-dot" aria-hidden="true"></span><?php echo esc_html($label); ?></div>
                    <h1 id="mm-note-title"><?php
                        $lines = explode("\n", $title);
                        echo esc_html($lines[0]);
                        if (!empty($lines[1])) { echo '<span>' . esc_html($lines[1]) . '</span>'; }
                    ?></h1>
                    <p class="mm-note__intro"><?php echo esc_html($intro); ?></p>
                    <div class="mm-note__actions">
                        <a class="mm-note__button mm-note__button--primary" href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/')); ?>">Tiếp tục khám phá <span aria-hidden="true">↗</span></a>
                        <?php if ($has_order && !$paid && $order->needs_payment()) : ?>
                            <a class="mm-note__button mm-note__button--secondary" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>">Thử thanh toán lại <span aria-hidden="true">↗</span></a>
                        <?php elseif ($has_order && is_user_logged_in() && (int) $order->get_customer_id() === get_current_user_id()) : ?>
                            <a class="mm-note__button mm-note__button--secondary" href="<?php echo esc_url($order->get_view_order_url()); ?>">Xem đơn hàng <span aria-hidden="true">↗</span></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mm-note__hero-art" aria-hidden="true">
                    <div class="mm-note__art-sun"></div>
                    <div class="mm-note__art-envelope">
                        <span class="mm-note__art-note">a little<br><em>thank you</em></span>
                        <span class="mm-note__art-line"></span>
                        <span class="mm-note__art-stamp">MM<br>STUDIO</span>
                    </div>
                    <div class="mm-note__art-caption">MỘT CHÚT GIẤY • MỘT CHÚT MỰC • MỘT CHÚT NIỀM VUI</div>
                </div>
            </div>
            <div class="mm-note__ticket-edge" aria-hidden="true"></div>
            <div class="mm-note__letter-bottom"><span>CHÂN THÀNH CẢM ƠN BẠN ĐÃ GHÉ QUA</span><span>MỰC MÂY ✳</span></div>
        </section>

        <?php if ($has_order) : ?>
            <div class="mm-note__section-heading"><span>01 / THÔNG TIN ĐƠN HÀNG</span><span>NHỮNG MÓN NHỎ ĐANG CHỜ ĐƯỢC GÓI GHÉM</span></div>
            <div class="mm-note__layout">
                <section class="mm-note__items" aria-labelledby="mm-note-items-title">
                    <div class="mm-note__panel-top"><h2 id="mm-note-items-title">Trong gói hàng của bạn</h2><span><?php echo esc_html($order->get_item_count()); ?> MÓN</span></div>
                    <div class="mm-note__item-list">
                        <?php foreach ($order->get_items('line_item') as $item) :
                            $product = $item->get_product();
                            $image = $product ? $product->get_image('woocommerce_thumbnail', array('loading' => 'lazy')) : (function_exists('wc_placeholder_img') ? wc_placeholder_img('woocommerce_thumbnail') : '');
                            $item_name = $item->get_name();
                            $link = $product && $product->is_visible() ? get_permalink($product->get_id()) : '';
                        ?>
                        <article class="mm-note__item">
                            <div class="mm-note__item-photo"><?php echo wp_kses_post($image); ?></div>
                            <div class="mm-note__item-info">
                                <h3><?php if ($link) : ?><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($item_name); ?></a><?php else : ?><?php echo esc_html($item_name); ?><?php endif; ?></h3>
                                <p>SỐ LƯỢNG <strong><?php echo esc_html($item->get_quantity()); ?></strong></p>
                            </div>
                            <strong class="mm-note__item-price"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></strong>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="mm-note__message"><span aria-hidden="true">✳</span> Những điều nhỏ bé cũng xứng đáng được nâng niu.</div>
                </section>

                <aside class="mm-note__summary" aria-label="Tóm tắt đơn hàng">
                    <div class="mm-note__panel-top"><h2>Ghi chú đơn hàng</h2><span>#<?php echo esc_html($order->get_order_number()); ?></span></div>
                    <dl class="mm-note__receipt-metadata">
                        <div><dt>Ngày đặt</dt><dd><?php echo esc_html($order->get_date_created() ? wc_format_datetime($order->get_date_created(), 'd/m/Y') : '—'); ?></dd></div>
                        <div><dt>Trạng thái</dt><dd class="mm-note__status-text"><?php echo esc_html($paid ? 'Đã thanh toán' : ($failed ? 'Chưa thanh toán' : 'Chờ xác nhận')); ?></dd></div>
                        <div><dt>Phương thức</dt><dd><?php echo esc_html($order->get_payment_method_title() ?: 'Thanh toán trực tuyến'); ?></dd></div>
                    </dl>
                    <div class="mm-note__summary-divider"></div>
                    <dl class="mm-note__receipt-totals">
                        <div><dt>Tạm tính</dt><dd><?php echo wp_kses_post(wc_price($order->get_subtotal(), array('currency' => $order->get_currency()))); ?></dd></div>
                        <?php if ((float) $order->get_discount_total() > 0) : ?><div><dt>Giảm giá</dt><dd>−<?php echo wp_kses_post(wc_price($order->get_discount_total(), array('currency' => $order->get_currency()))); ?></dd></div><?php endif; ?>
                        <div><dt>Phí vận chuyển</dt><dd><?php echo (float) $order->get_shipping_total() > 0 ? wp_kses_post(wc_price($order->get_shipping_total(), array('currency' => $order->get_currency()))) : 'Miễn phí'; ?></dd></div>
                        <?php if ((float) $order->get_total_tax() > 0) : ?><div><dt>Thuế</dt><dd><?php echo wp_kses_post(wc_price($order->get_total_tax(), array('currency' => $order->get_currency()))); ?></dd></div><?php endif; ?>
                        <div class="mm-note__grand-total"><dt>Tổng cộng</dt><dd><?php echo wp_kses_post($order->get_formatted_order_total()); ?></dd></div>
                    </dl>
                    <p class="mm-note__summary-footnote">Tất cả thông tin đơn hàng đều được lưu trong tài khoản WooCommerce của cửa tiệm.</p>
                </aside>
            </div>

            <div class="mm-note__section-heading mm-note__section-heading--second"><span>02 / ĐIỂM ĐẾN</span><span>GỬI MỘT CHÚT NIỀM VUI ĐẾN BẠN</span></div>
            <div class="mm-note__delivery">
                <div><span class="mm-note__eyebrow">GIAO TỚI</span><h2><?php echo esc_html($order->get_formatted_shipping_full_name() ?: $order->get_formatted_billing_full_name() ?: 'Người nhận'); ?></h2>
                    <div class="mm-note__address"><?php echo wp_kses_post($order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address()); ?></div>
                    <?php if ($order->get_billing_phone()) : ?><p><?php echo esc_html($order->get_billing_phone()); ?></p><?php endif; ?>
                </div>
                <div class="mm-note__delivery-side"><span class="mm-note__eyebrow">LỜI NHẮN NHỎ</span><p>“Cảm ơn vì đã để Mực Mây góp một chút xinh xắn vào ngày của bạn.”</p><span class="mm-note__signature">Thương mến, Mực Mây.</span></div>
            </div>
            <?php
            // Preserve gateway/third-party order-received hooks without duplicating Woo's native table.
            // WooCommerce has already validated shopper access before loading this template.
            if (function_exists('remove_action')) { remove_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10); }
            ?>
            <div class="mm-note__integrations">
                <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
                <?php do_action('woocommerce_thankyou', $order->get_id()); ?>
            </div>
            <?php if (function_exists('add_action')) { add_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10); } ?>
        <?php endif; ?>
        <div class="mm-note__back"><span>ĐẸP TỪ NHỮNG ĐIỀU NHỎ BÉ</span><a href="<?php echo esc_url(home_url('/')); ?>">VỀ TRANG CHỦ <span aria-hidden="true">↗</span></a></div>
    </div>
</main>
