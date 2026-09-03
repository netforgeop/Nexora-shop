<?php
/**
 * Posts index / blog home.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nexora_sidebar  = nexora_option( 'blog', 'sidebar', 'right' );
$nexora_featured = nexora_option( 'blog', 'featured' ) && is_home() && ! is_paged();
$nexora_sticky   = $nexora_featured ? array_slice( (array) get_option( 'sticky_posts' ), 0, 1 ) : array();
?>
<section class="section section--sm blog-archive">
	<div class="container">
		<?php
		nexora_breadcrumb();
		get_template_part( 'template-parts/blog/archive-head', null, array( 'title' => nexora_option( 'blog', 'heading' ), 'sub' => nexora_option( 'blog', 'sub' ) ) );

		if ( $nexora_sticky ) {
			$nexora_fq = new WP_Query( array( 'post__in' => $nexora_sticky, 'posts_per_page' => 1, 'no_found_rows' => true, 'ignore_sticky_posts' => true ) );
			while ( $nexora_fq->have_posts() ) {
				$nexora_fq->the_post();
				get_template_part( 'template-parts/blog/post-featured' );
			}
			wp_reset_postdata();
		}
		?>
		<div class="<?php echo 'none' === $nexora_sidebar ? 'blog-single-col' : 'with-sidebar' . ( 'left' === $nexora_sidebar ? ' with-sidebar--start' : ' with-sidebar--end' ); ?>">
			<div>
				<?php get_template_part( 'template-parts/blog/category-tabs' ); ?>
				<?php if ( have_posts() ) : ?>
					<div class="row g-4">
						<?php
						while ( have_posts() ) :
							the_post();
							if ( $nexora_sticky && in_array( get_the_ID(), $nexora_sticky, true ) ) {
								continue;
							}
							?>
							<div class="<?php echo 'none' === $nexora_sidebar ? 'col-md-6 col-lg-4' : 'col-md-6'; ?>"><?php get_template_part( 'template-parts/blog/post-card' ); ?></div>
						<?php endwhile; ?>
					</div>
					<?php nexora_pagination(); ?>
				<?php else : ?>
					<?php nexora_empty_state( array( 'icon' => 'file-empty', 'title' => __( 'No posts yet', 'nexora' ), 'text' => __( 'Check back soon — new articles are on the way.', 'nexora' ) ) ); ?>
				<?php endif; ?>
			</div>
			<?php if ( 'none' !== $nexora_sidebar ) { get_sidebar(); } ?>
		</div>
	</div>
</section>
<?php
get_footer();
