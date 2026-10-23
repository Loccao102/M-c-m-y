<?php get_header(); ?>
<main id="main"><section class="mm-page-banner"><div class="mm-container"><span class="mm-eyebrow">MỰC MÂY / MỘT TRANG GIẤY KHÁC</span><h1><?php the_title(); ?></h1><p>Chuyện nhỏ ở chiếc tiệm đầy giấy, mực và những điều chưa viết.</p></div></section><div class="mm-container mm-content-page"><?php while ( have_posts() ) : the_post(); ?><article <?php post_class(); ?>><?php the_content(); wp_link_pages(); ?></article><?php endwhile; ?></div></main>
<?php get_footer(); ?>
