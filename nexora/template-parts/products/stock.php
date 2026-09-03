<?php
/**
 * Stock status pill.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_p = $args['product'] ?? null;
if ( ! $nexora_p ) {
	return;
}
if ( ! $nexora_p->is_in_stock() ) {
	echo '<span class="stock stock--out" data-stock>' . esc_html__( 'Out of stock', 'nexora' ) . '</span>';
} elseif ( $nexora_p->is_on_backorder() ) {
	echo '<span class="stock stock--low" data-stock>' . esc_html__( 'Available on backorder', 'nexora' ) . '</span>';
} elseif ( $nexora_p->managing_stock() && null !== $nexora_p->get_stock_quantity() && $nexora_p->get_stock_quantity() <= (int) get_option( 'woocommerce_notify_low_stock_amount', 2 ) ) {
	/* translators: %s: quantity */
	echo '<span class="stock stock--low" data-stock>' . esc_html( sprintf( __( 'Only %s left', 'nexora' ), nexora_num( $nexora_p->get_stock_quantity() ) ) ) . '</span>';
} else {
	echo '<span class="stock stock--in" data-stock>' . esc_html__( 'In stock', 'nexora' ) . '</span>';
}
