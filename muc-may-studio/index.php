<?php get_header(); ?>
<main id="main" class="mm-container mm-blog-content"><header><span class="mm-eyebrow">JOURNAL</span><h1><?php echo is_search() ? 'Kết quả tìm kiếm' : esc_html( get_the_archive_title() ?: 'Nhật ký Mực Mây' ); ?></h1></header>
<?php if ( have_posts() ) : ?><div class="mm-post-grid"><?php while ( have_posts() ) : the_post(); ?><article class="mm-post-card"><a href="<?php the_permalink(); ?>"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?><h2><?php the_title(); ?></h2></a><p><?php the_excerpt(); ?></p></article><?php endwhile; ?></div><?php the_posts_pagination(); ?><?php else : ?><p>Chưa có bài viết nào.</p><?php endif; ?>
</main>
<?php get_footer(); ?>
