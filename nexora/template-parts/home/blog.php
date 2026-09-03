<?php
/**
 * Latest posts.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_home = $args['home'];
$nexora_q    = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => (int) $nexora_home['blog_count'], 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
if ( ! $nexora_q->have_posts() ) {
	return;
}
$nexora_blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/?post_type=post' );
?>
<section class="section home-blog" aria-labelledby="sec-blog">
	<div class="container">
		<?php nexora_section_head( array( 'title' => $nexora_home['blog_title'], 'sub' => $nexora_home['blog_sub'], 'id' => 'sec-blog', 'link' => $nexora_blog_url, 'link_text' => __( 'Read the blog', 'nexora' ) ) ); ?>
		<div class="row g-4">
			<?php
			$nexora_i = 0;
			while ( $nexora_q->have_posts() ) :
				$nexora_q->the_post();
				?>
				<div class="col-md-6 col-lg-4" data-reveal style="--reveal-delay:<?php echo (int) $nexora_i++ * 100; ?>ms">
					<?php get_template_part( 'template-parts/blog/post-card' ); ?>
				</div>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
