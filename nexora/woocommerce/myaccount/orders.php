<?php
/**
 * Orders list.
 *
 * @package Nexora
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );
$nexora_filters = array(
	'all'        => __( 'All', 'nexora' ),
	'processing' => __( 'In progress', 'nexora' ),
	'completed'  => __( 'Delivered', 'nexora' ),
	'cancelled'  => __( 'Cancelled / returned', 'nexora' ),
);
?>
<div class="account-panel" id="orders">
	<div class="account-panel__head">
		<h1 class="account-panel__title"><?php esc_html_e( 'My orders', 'nexora' ); ?></h1>
		<div class="tabs tabs--pills" role="tablist" aria-label="<?php esc_attr_e( 'Filter orders', 'nexora' ); ?>">
			<?php foreach ( $nexora_filters as $nexora_key => $nexora_label ) : ?>
				<button type="button" class="tabs__tab<?php echo 'all' === $nexora_key ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo 'all' === $nexora_key ? 'true' : 'false'; ?>" data-order-filter="<?php echo esc_attr( $nexora_key ); ?>"><?php echo esc_html( $nexora_label ); ?></button>
			<?php endforeach; ?>
		</div>
	</div>
	<?php if ( $has_orders ) : ?>
		<div class="table-scroll">
			<table class="data-table woocommerce-orders-table shop_table my_account_orders" data-order-table>
				<thead><tr><th scope="col"><?php esc_html_e( 'Order', 'nexora' ); ?></th><th scope="col"><?php esc_html_e( 'Date', 'nexora' ); ?></th><th scope="col"><?php esc_html_e( 'Items', 'nexora' ); ?></th><th scope="col"><?php esc_html_e( 'Total', 'nexora' ); ?></th><th scope="col"><?php esc_html_e( 'Status', 'nexora' ); ?></th><th scope="col"><span class="visually-hidden"><?php esc_html_e( 'Actions', 'nexora' ); ?></span></th></tr></thead>
				<tbody>
					<?php foreach ( $customer_orders->orders as $customer_order ) : ?>
						<?php
						$order  = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						$status = $order->get_status();
						$group  = 'processing';
						if ( 'completed' === $status ) {
							$group = 'completed';
						} elseif ( in_array( $status, array( 'cancelled', 'refunded', 'failed' ), true ) ) {
							$group = 'cancelled';
						}
						$items = $order->get_items();
						?>
						<tr class="woocommerce-orders-table__row order" data-order-status="<?php echo esc_attr( $group ); ?>">
							<td><span class="num ltr">#<?php echo esc_html( $order->get_order_number() ); ?></span></td>
							<td><time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( nexora_num( wc_format_datetime( $order->get_date_created() ) ) ); ?></time></td>
							<td><div class="data-table__thumbs"><?php foreach ( array_slice( $items, 0, 3 ) as $nexora_item ) { $nexora_p = $nexora_item->get_product(); if ( $nexora_p ) { echo $nexora_p->get_image( 'nexora-thumb', array( 'loading' => 'lazy' ) ); } } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ( count( $items ) > 3 ) : ?><span class="data-table__more">+<?php echo esc_html( nexora_num( count( $items ) - 3 ) ); ?></span><?php endif; ?></div></td>
							<td><span class="num"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span></td>
							<td><?php nexora_order_status_badge( $order ); ?></td>
							<td>
								<div class="cluster cluster--sm">
									<a class="btn btn--outline btn--sm" href="<?php echo esc_url( $order->get_view_order_url() ); ?>"><?php esc_html_e( 'View', 'nexora' ); ?></a>
									<?php
									foreach ( wc_get_account_orders_actions( $order ) as $key => $action ) {
										if ( 'view' === $key ) {
											continue;
										}
										echo '<a href="' . esc_url( $action['url'] ) . '" class="btn btn--ghost btn--sm woocommerce-button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
									}
									?>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<p class="small text-muted" data-order-filter-empty hidden><?php esc_html_e( 'No orders match this filter.', 'nexora' ); ?></p>
		<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>
		<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
			<nav class="pagination pagination--simple woocommerce-pagination" aria-label="<?php esc_attr_e( 'Orders pagination', 'nexora' ); ?>">
				<?php if ( 1 !== $current_page ) : ?><a class="btn btn--outline btn--sm" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php nexora_the_icon( 'chevron-right', 'xs', 'icon--flip-ltr' ); ?><?php esc_html_e( 'Previous', 'nexora' ); ?></a><?php endif; ?>
				<span class="small text-muted"><?php /* translators: 1: current page 2: total pages */ printf( esc_html__( 'Page %1$s of %2$s', 'nexora' ), esc_html( nexora_num( $current_page ) ), esc_html( nexora_num( $customer_orders->max_num_pages ) ) ); ?></span>
				<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?><a class="btn btn--outline btn--sm" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'nexora' ); ?><?php nexora_the_icon( 'chevron-left', 'xs', 'icon--flip-ltr' ); ?></a><?php endif; ?>
			</nav>
		<?php endif; ?>
	<?php else : ?>
		<?php nexora_empty_state( array( 'icon' => 'box', 'title' => __( 'No orders yet', 'nexora' ), 'text' => __( 'When you place an order it will show up here.', 'nexora' ), 'cta' => __( 'Start shopping', 'nexora' ), 'href' => apply_filters( 'woocommerce_return_to_shop_redirect', nexora_shop_url() ) ) ); ?>
	<?php endif; ?>
</div>
<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
