<?php
/**
 * Default page template.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();

/*
 * WooCommerce shortcode pages (cart, checkout, my-account) print their own
 * page chrome (steps, heading, layout) — render them bare.
 */
if ( function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
	while ( have_posts() ) :
		the_post();
		echo '<div class="wc-page wc-page--' . esc_attr( is_cart() ? 'cart' : ( is_checkout() ? 'checkout' : 'account' ) ) . '">';
		the_content();
		echo '</div>';
	endwhile;
	get_footer();
	return;
}

$nexora_has_side = is_active_sidebar( 'sidebar-page' );
?>
<section class="section section--sm page-default">
	<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<header class="page-head__inner page-head__inner--page">
				<div><h1 class="h2"><?php the_title(); ?></h1><?php if ( has_excerpt() ) : ?><p class="text-muted"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?></div>
			</header>
			<div class="<?php echo $nexora_has_side ? 'with-sidebar with-sidebar--end' : 'page-narrow'; ?>">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'card-surface card-surface--pad' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?><figure class="article-hero"><?php the_post_thumbnail( 'nexora-post-wide', array( 'fetchpriority' => 'high' ) ); ?></figure><?php endif; ?>
					<div class="prose">
						<?php
						the_content();
						wp_link_pages( array( 'before' => '<nav class="pagination pagination--pages">', 'after' => '</nav>', 'link_before' => '<span class="pagination__link">', 'link_after' => '</span>' ) );
						?>
					</div>
					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</article>
				<?php if ( $nexora_has_side ) { get_sidebar( null, array( 'area' => 'sidebar-page' ) ); } ?>
			</div>
		<?php endwhile; ?>
	</div>
</section>
<?php
get_footer();
