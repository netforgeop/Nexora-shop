<?php
/**
 * Generic product section (featured / newest / best).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_home = $args['home'];
$nexora_key  = $args['key'];
$nexora_ids  = nexora_query_products(
	$nexora_home[ $nexora_key . '_source' ],
	(int) $nexora_home[ $nexora_key . '_count' ],
	array( 'category' => $nexora_home[ $nexora_key . '_category' ], 'products' => $nexora_home[ $nexora_key . '_products' ] )
);
if ( ! $nexora_ids ) {
	return;
}
$nexora_links = array( 'featured' => array( 'featured' => 1 ), 'sale' => array( 'on_sale' => 1 ), 'newest' => array( 'orderby' => 'date' ), 'best' => array( 'orderby' => 'popularity' ), 'rating' => array( 'orderby' => 'rating' ) );
$nexora_link  = $nexora_home[ $nexora_key . '_link' ];
if ( ! $nexora_link ) {
	$nexora_src = $nexora_home[ $nexora_key . '_source' ];
	if ( 'category' === $nexora_src && $nexora_home[ $nexora_key . '_category' ] ) {
		$nexora_t    = get_term( (int) $nexora_home[ $nexora_key . '_category' ], 'product_cat' );
		$nexora_link = $nexora_t && ! is_wp_error( $nexora_t ) ? get_term_link( $nexora_t ) : nexora_shop_url();
	} else {
		$nexora_link = nexora_shop_url( $nexora_links[ $nexora_src ] ?? array() );
	}
}
$nexora_carousel = 'carousel' === $nexora_home[ $nexora_key . '_layout' ];
$nexora_class    = $args['class'] ?? 'section';
?>
<section class="<?php echo esc_attr( $nexora_class ); ?>" aria-labelledby="sec-<?php echo esc_attr( $nexora_key ); ?>">
	<div class="container">
		<?php nexora_section_head( array( 'title' => $nexora_home[ $nexora_key . '_title' ], 'sub' => $nexora_home[ $nexora_key . '_sub' ], 'id' => 'sec-' . $nexora_key, 'link' => $nexora_link, 'carousel' => $nexora_carousel ? $nexora_key : '' ) ); ?>
		<?php
		if ( $nexora_carousel ) {
			nexora_product_carousel( $nexora_ids, $nexora_key, array( 'columns' => (int) $nexora_home[ $nexora_key . '_columns' ] ) );
		} else {
			nexora_product_grid( $nexora_ids, array( 'columns' => (int) $nexora_home[ $nexora_key . '_columns' ] ) );
		}
		?>
	</div>
</section>
