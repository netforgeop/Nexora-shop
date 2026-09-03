<?php
/**
 * Search results. Product searches are handled by WooCommerce (archive-product.php);
 * this template covers posts/pages and mixed results.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();
$nexora_q = get_search_query();
?>
<section class="section section--sm search-page">
	<div class="container">
		<div class="search-page__head">
			<h1 class="h3"><?php esc_html_e( 'Search results', 'nexora' ); ?></h1>
			<div class="search" data-search>
				<form class="search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label for="page-search" class="visually-hidden"><?php esc_html_e( 'Search', 'nexora' ); ?></label>
					<input id="page-search" class="search__input" type="search" name="s" value="<?php echo esc_attr( $nexora_q ); ?>" placeholder="<?php esc_attr_e( 'Search products and articles…', 'nexora' ); ?>" autocomplete="off" data-search-input aria-autocomplete="list" aria-controls="page-search-suggest" aria-expanded="false">
					<?php if ( class_exists( 'WooCommerce' ) ) : ?><input type="hidden" name="post_type" value="product"><?php endif; ?>
					<button type="submit" class="search__submit" aria-label="<?php esc_attr_e( 'Search', 'nexora' ); ?>"><?php nexora_the_icon( 'magnifier', 'sm' ); ?></button>
				</form>
				<div class="search__suggest" id="page-search-suggest" role="listbox" data-search-suggest></div>
			</div>
			<p class="search-page__query" aria-live="polite">
				<?php
				if ( $nexora_q ) {
					/* translators: 1: count, 2: query */
					echo esc_html( sprintf( _n( '%1$s result for “%2$s”', '%1$s results for “%2$s”', (int) $GLOBALS['wp_query']->found_posts, 'nexora' ), nexora_num( (int) $GLOBALS['wp_query']->found_posts ), $nexora_q ) );
				}
				?>
			</p>
			<?php if ( class_exists( 'WooCommerce' ) && $nexora_q ) : ?>
				<div class="search-suggestions">
					<span><?php esc_html_e( 'Looking for products?', 'nexora' ); ?></span>
					<a class="chip" href="<?php echo esc_url( add_query_arg( array( 's' => $nexora_q, 'post_type' => 'product' ), home_url( '/' ) ) ); ?>"><?php nexora_the_icon( 'store', 'xs' ); ?><?php esc_html_e( 'Search in the shop', 'nexora' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( have_posts() ) : ?>
			<div class="row g-4">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<div class="col-md-6 col-lg-4"><?php get_template_part( 'template-parts/blog/post-card' ); ?></div>
				<?php endwhile; ?>
			</div>
			<?php nexora_pagination(); ?>
		<?php else : ?>
			<?php nexora_empty_state( array( 'icon' => 'magnifier', 'title' => __( 'No results found', 'nexora' ), 'text' => __( 'Try different keywords or browse the shop.', 'nexora' ), 'cta' => class_exists( 'WooCommerce' ) ? __( 'Go to shop', 'nexora' ) : '', 'href' => class_exists( 'WooCommerce' ) ? nexora_shop_url() : '' ) ); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
