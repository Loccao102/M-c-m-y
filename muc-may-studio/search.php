<?php get_header(); ?>
<main id="main"><section class="mm-page-banner"><div class="mm-container"><span class="mm-eyebrow">TRONG TỦ GIẤY / KẾT QUẢ TÌM KIẾM</span><h1><?php echo esc_html( 'Tìm: ' . get_search_query() ); ?></h1><p>Thử lật vài ngăn kéo, có khi món bạn tìm đang ở đây.</p></div></section>
<div class="mm-container mm-woo-wrapper">
<?php if ( have_posts() ) : ?><div class="mm-product-grid">
<?php while ( have_posts() ) : the_post(); ?>
<?php if ( 'product' === get_post_type() && function_exists( 'wc_get_product' ) ) { mucmay_product_card( get_the_ID() ); }
else { ?><article class="mm-post-card"><a href="<?php the_permalink(); ?>"><h2><?php the_title(); ?></h2></a><p><?php the_excerpt(); ?></p></article><?php } ?>
<?php endwhile; ?></div><div class="mm-pagination"><?php the_posts_pagination(); ?></div>
<?php else : ?><p class="mm-empty-state">Chưa tìm thấy món đồ phù hợp. Bạn thử một từ khóa khác nhé.</p><?php endif; ?>
</div></main>
<?php get_footer(); ?>
