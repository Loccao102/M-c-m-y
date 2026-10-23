<?php get_header(); ?>
<main id="main" class="mm-woo-main">
 <section class="mm-page-banner"><div class="mm-container"><span class="mm-eyebrow">MỤC LỤC / MỰC MÂY STUDIO · 2026</span><h1><?php if ( is_product() ) { single_post_title(); } elseif ( is_shop() ) { echo esc_html( 'Tủ đồ giấy của Mực Mây' ); } else { woocommerce_page_title(); } ?></h1><p>Nhặt một món bé xinh. Biết đâu lại viết nên một điều thật hay.</p></div></section>
 <div class="mm-container mm-woo-wrapper"><?php if ( function_exists( 'woocommerce_content' ) ) { woocommerce_content(); } ?></div>
</main>
<?php get_footer(); ?>
