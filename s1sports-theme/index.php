<?php defined('ABSPATH') || exit; get_header(); ?>
<main><section class="page-hero"><div class="wrap"><h1><?php echo esc_html(get_bloginfo('name')); ?></h1></div></section><div class="wrap page-content"><?php if(have_posts()): while(have_posts()): the_post(); ?><article <?php post_class(); ?>><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article><?php endwhile; the_posts_pagination(); endif; ?></div></main>
<?php get_footer(); ?>
