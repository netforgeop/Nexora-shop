<?php
/**
 * Result count.
 *
 * @package Nexora
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;
?>
<span class="shop-toolbar__count woocommerce-result-count" <?php echo ( empty( $orderedby ) ) ? '' : 'role="alert" aria-relevant="all" data-is-sorted-by="true"'; ?>>
	<?php
	if ( 1 === intval( $total ) ) {
		esc_html_e( 'Showing the single result', 'nexora' );
	} elseif ( $total <= $per_page || -1 === $per_page ) {
		/* translators: %d: total results */
		printf( esc_html( _n( 'Showing all %s result', 'Showing all %s results', $total, 'nexora' ) ), esc_html( nexora_num( $total ) ) ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain
	} else {
		$first = ( $per_page * $current ) - $per_page + 1;
		$last  = min( $total, $per_page * $current );
		/* translators: 1: first result 2: last result 3: total results */
		printf( esc_html__( 'Showing %1$s–%2$s of %3$s results', 'nexora' ), esc_html( nexora_num( $first ) ), esc_html( nexora_num( $last ) ), esc_html( nexora_num( $total ) ) );
	}
	?>
</span>
