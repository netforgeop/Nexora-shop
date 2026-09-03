<?php
/**
 * Generic archive (category, tag, author, date).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();
$nexora_sidebar = nexora_option( 'blog', 'sidebar', 'right' );
?>
<section class="section section--sm blog-archive">
	<div class="container">
		<?php
		nexora_breadcrumb();
		get_template_part( 'template-parts/blog/archive-head', null, array( 'title' => wp_strip_all_tags( get_the_archive_title() ), 'sub' => get_the_archive_description() ) );
		?>
		<div class="<?php echo 'none' === $nexora_sidebar ? 'blog-single-col' : 'with-sidebar' . ( 'left' === $nexora_sidebar ? ' with-sidebar--start' : ' with-sidebar--end' ); ?>">
			<div>
				<?php get_template_part( 'template-parts/blog/category-tabs' ); ?>
				<?php if ( have_posts() ) : ?>
					<div class="row g-4">
						<?php
						while ( have_posts() ) :
							the_post();
							?>
							<div class="<?php echo 'none' === $nexora_sidebar ? 'col-md-6 col-lg-4' : 'col-md-6'; ?>"><?php get_template_part( 'template-parts/blog/post-card' ); ?></div>
						<?php endwhile; ?>
					</div>
					<?php nexora_pagination(); ?>
				<?php else : ?>
					<?php nexora_empty_state( array( 'icon' => 'file-empty', 'title' => __( 'Nothing found', 'nexora' ), 'text' => __( 'There are no posts in this archive yet.', 'nexora' ) ) ); ?>
				<?php endif; ?>
			</div>
			<?php if ( 'none' !== $nexora_sidebar ) { get_sidebar(); } ?>
		</div>
	</div>
</section>
<?php
get_footer();
