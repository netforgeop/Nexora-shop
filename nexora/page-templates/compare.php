<?php
/**
 * Template Name: Compare
 * Template Post Type: page
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();
$nexora_ids = class_exists( 'WooCommerce' ) ? nexora_list_for_template( 'compare' ) : array();
nexora_breadcrumb();
?>
<section class="section section--sm" data-compare-page data-logged-in="<?php echo is_user_logged_in() ? '1' : '0'; ?>">
	<div class="container">
		<div class="wishlist-head">
			<h1 class="h3"><?php nexora_the_icon( 'compare', 'md' ); ?> <?php the_title(); ?></h1>
			<button type="button" class="btn btn--ghost btn--sm text-danger" data-compare-clear-page <?php echo $nexora_ids ? '' : 'hidden'; ?>><?php nexora_the_icon( 'trash2', 'xs' ); ?><?php esc_html_e( 'Clear', 'nexora' ); ?></button>
		</div>
		<?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
			<?php nexora_empty_state( array( 'icon' => 'compare', 'title' => __( 'Compare needs WooCommerce', 'nexora' ), 'text' => __( 'Install and activate WooCommerce to compare products.', 'nexora' ) ) ); ?>
		<?php else : ?>
			<div class="table-scroll card-surface" data-compare-filled <?php echo $nexora_ids ? '' : 'hidden'; ?>>
				<table class="compare-table" data-compare-table>
					<?php if ( $nexora_ids ) { get_template_part( 'template-parts/products/compare-table', null, array( 'ids' => $nexora_ids ) ); } ?>
				</table>
			</div>
			<div data-compare-empty <?php echo $nexora_ids ? 'hidden' : ''; ?>>
				<?php nexora_empty_state( array( 'icon' => 'compare', 'title' => __( 'Nothing to compare yet', 'nexora' ), 'text' => __( 'Add up to 4 products using the compare button on product cards.', 'nexora' ), 'cta' => __( 'Go to shop', 'nexora' ), 'href' => nexora_shop_url() ) ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
