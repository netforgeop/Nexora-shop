<?php
/**
 * Template Name: Wishlist
 * Template Post Type: page
 *
 * Server-renders the list for logged-in users; guests are hydrated by JS from localStorage.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();
$nexora_ids = class_exists( 'WooCommerce' ) ? nexora_list_for_template( 'wishlist' ) : array();
nexora_breadcrumb();
?>
<section class="section section--sm" data-wishlist-page data-logged-in="<?php echo is_user_logged_in() ? '1' : '0'; ?>">
	<div class="container">
		<div class="wishlist-head">
			<h1 class="h3"><?php nexora_the_icon( 'heart', 'md' ); ?> <?php the_title(); ?> <span class="wishlist-head__count" data-wishlist-count><?php echo $nexora_ids ? esc_html( '(' . nexora_num( count( $nexora_ids ) ) . ')' ) : ''; ?></span></h1>
			<div class="cluster" data-wishlist-actions <?php echo $nexora_ids ? '' : 'hidden'; ?>>
				<button type="button" class="btn btn--dark btn--sm" data-wishlist-add-all><?php nexora_the_icon( 'cart-add', 'xs' ); ?><?php esc_html_e( 'Add all to cart', 'nexora' ); ?></button>
				<button type="button" class="btn btn--ghost btn--sm text-danger" data-wishlist-clear><?php nexora_the_icon( 'trash2', 'xs' ); ?><?php esc_html_e( 'Clear list', 'nexora' ); ?></button>
			</div>
		</div>
		<?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
			<?php nexora_empty_state( array( 'icon' => 'heart', 'title' => __( 'Wishlist needs WooCommerce', 'nexora' ), 'text' => __( 'Install and activate WooCommerce to use the wishlist.', 'nexora' ) ) ); ?>
		<?php else : ?>
			<div class="card-surface wishlist-box" data-wishlist-filled <?php echo $nexora_ids ? '' : 'hidden'; ?>>
				<div data-wishlist-items>
					<?php foreach ( $nexora_ids as $nexora_pid ) { get_template_part( 'template-parts/products/wish-item', null, array( 'id' => $nexora_pid ) ); } ?>
				</div>
			</div>
			<div data-wishlist-empty <?php echo $nexora_ids ? 'hidden' : ''; ?>>
				<?php nexora_empty_state( array( 'icon' => 'heart', 'title' => __( 'Your wishlist is empty', 'nexora' ), 'text' => __( 'Tap the heart on any product to save it here.', 'nexora' ), 'cta' => __( 'Go to shop', 'nexora' ), 'href' => nexora_shop_url() ) ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
if ( class_exists( 'WooCommerce' ) ) :
	$nexora_sugg = nexora_query_products( 'featured', 10 );
	if ( $nexora_sugg ) :
		?>
		<section class="section bg-surface" aria-labelledby="sec-wish-suggest">
			<div class="container">
				<?php nexora_section_head( array( 'title' => __( 'You may also like', 'nexora' ), 'id' => 'sec-wish-suggest', 'carousel' => 'suggest', 'reveal' => false ) ); ?>
				<?php nexora_product_carousel( $nexora_sugg, 'suggest' ); ?>
			</div>
		</section>
		<?php
	endif;
endif;
get_footer();
