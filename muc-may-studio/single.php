<?php get_header(); ?>
<main id="main"><div class="mm-container mm-blog-content"><?php while ( have_posts() ) : the_post(); ?><article <?php post_class(); ?>><p class="mm-eyebrow">NHẬT KÝ MỰC MÂY</p><h1><?php the_title(); ?></h1><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?><div class="entry-content"><?php the_content(); ?></div><?php the_post_navigation(); ?></article><?php endwhile; ?></div></main>
<?php get_footer(); ?>
