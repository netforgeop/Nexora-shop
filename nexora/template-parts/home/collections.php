<?php
/**
 * Three ranked mini lists: top rated, trending, top discounts.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_home  = $args['home'];
$nexora_n     = (int) $nexora_home['collections_count'];
$nexora_lists = array(
	array( __( 'Top rated', 'nexora' ), nexora_query_products( 'rating', $nexora_n ), nexora_shop_url( array( 'orderby' => 'rating' ) ) ),
	array( __( 'Trending', 'nexora' ), nexora_query_products( 'best', $nexora_n ), nexora_shop_url( array( 'orderby' => 'popularity' ) ) ),
	array( __( 'Top discounts', 'nexora' ), nexora_query_products( 'sale', $nexora_n ), nexora_shop_url( array( 'on_sale' => 1, 'orderby' => 'discount' ) ) ),
);
$nexora_lists = array_filter( $nexora_lists, static function ( $l ) { return ! empty( $l[1] ); } );
if ( ! $nexora_lists ) {
	return;
}
?>
<section class="section" aria-labelledby="sec-collections">
	<div class="container">
		<?php nexora_section_head( array( 'title' => $nexora_home['collections_title'], 'sub' => $nexora_home['collections_sub'], 'id' => 'sec-collections', 'center' => true ) ); ?>
		<div class="collections" data-reveal>
			<?php foreach ( $nexora_lists as $nexora_list ) : ?>
				<div class="collection">
					<h3 class="collection__title"><?php echo esc_html( $nexora_list[0] ); ?><a href="<?php echo esc_url( $nexora_list[2] ); ?>"><?php esc_html_e( 'View all', 'nexora' ); ?></a></h3>
					<div class="collection__list">
						<?php foreach ( $nexora_list[1] as $nexora_i => $nexora_pid ) { nexora_product_mini( $nexora_pid, $nexora_i + 1 ); } ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
