<?php
/**
 * Compare table body (server-side).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_products = array_filter( array_map( 'wc_get_product', array_slice( (array) ( $args['ids'] ?? array() ), 0, 4 ) ) );
if ( ! $nexora_products ) {
	return;
}
$nexora_rows = array();
$nexora_all  = array();
foreach ( $nexora_products as $nexora_p ) {
	$nexora_rows[ $nexora_p->get_id() ] = nexora_compare_rows( $nexora_p );
	$nexora_all                         = array_merge( $nexora_all, array_keys( $nexora_rows[ $nexora_p->get_id() ] ) );
}
$nexora_all = array_values( array_unique( $nexora_all ) );
?>
<tbody>
	<tr>
		<th scope="row"><?php esc_html_e( 'Image', 'nexora' ); ?></th>
		<?php foreach ( $nexora_products as $nexora_p ) : ?>
			<td><div class="compare-table__media"><a href="<?php echo esc_url( $nexora_p->get_permalink() ); ?>"><?php echo $nexora_p->get_image( 'nexora-square', array( 'alt' => $nexora_p->get_name() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><button type="button" class="icon-btn icon-btn--light icon-btn--circle compare-table__remove" data-action="compare" data-id="<?php echo (int) $nexora_p->get_id(); ?>" aria-pressed="true" aria-label="<?php esc_attr_e( 'Remove from compare', 'nexora' ); ?>"><?php nexora_the_icon( 'cross', 'xs' ); ?></button></div></td>
		<?php endforeach; ?>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Name', 'nexora' ); ?></th>
		<?php foreach ( $nexora_products as $nexora_p ) : ?><td><a class="fw-medium text-strong" href="<?php echo esc_url( $nexora_p->get_permalink() ); ?>"><?php echo esc_html( $nexora_p->get_name() ); ?></a></td><?php endforeach; ?>
	</tr>
	<?php foreach ( $nexora_all as $nexora_label ) : ?>
		<tr>
			<th scope="row"><?php echo esc_html( $nexora_label ); ?></th>
			<?php foreach ( $nexora_products as $nexora_p ) : ?><td><?php echo isset( $nexora_rows[ $nexora_p->get_id() ][ $nexora_label ] ) ? wp_kses_post( $nexora_rows[ $nexora_p->get_id() ][ $nexora_label ] ) : '—'; ?></td><?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
	<tr>
		<th scope="row"><?php esc_html_e( 'Actions', 'nexora' ); ?></th>
		<?php foreach ( $nexora_products as $nexora_p ) : ?>
			<td>
				<?php if ( $nexora_p->is_type( 'simple' ) && $nexora_p->is_purchasable() && $nexora_p->is_in_stock() ) : ?>
					<button type="button" class="btn btn--dark btn--sm btn--block" data-action="add-to-cart" data-id="<?php echo (int) $nexora_p->get_id(); ?>"><?php nexora_the_icon( 'cart-add', 'xs' ); ?><?php esc_html_e( 'Add to cart', 'nexora' ); ?></button>
				<?php else : ?>
					<a class="btn btn--dark btn--sm btn--block" href="<?php echo esc_url( $nexora_p->get_permalink() ); ?>"><?php esc_html_e( 'View product', 'nexora' ); ?></a>
				<?php endif; ?>
			</td>
		<?php endforeach; ?>
	</tr>
</tbody>
