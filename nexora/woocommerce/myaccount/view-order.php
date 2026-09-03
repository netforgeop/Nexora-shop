<?php
/**
 * Single order view.
 *
 * @package Nexora
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

$nexora_status = $order->get_status();
$nexora_steps  = array(
	'pending'    => array( __( 'Order placed', 'nexora' ), 'cart' ),
	'processing' => array( __( 'Processing', 'nexora' ), 'cog' ),
	'shipped'    => array( __( 'Shipped', 'nexora' ), 'truck' ),
	'completed'  => array( __( 'Delivered', 'nexora' ), 'checkmark-circle' ),
);
$nexora_order_idx = array( 'pending' => 0, 'on-hold' => 0, 'processing' => 1, 'shipped' => 2, 'completed' => 3 );
$nexora_current   = $nexora_order_idx[ $nexora_status ] ?? -1;
$nexora_failed    = in_array( $nexora_status, array( 'cancelled', 'refunded', 'failed' ), true );
$nexora_notes     = wc_get_order_notes( array( 'order_id' => $order->get_id(), 'type' => 'customer' ) );
?>
<div class="account-panel">
	<div class="account-panel__head">
		<div>
			<a class="link--arrow small" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php nexora_the_icon( 'arrow-right', 'xs', 'icon--flip-ltr' ); ?><?php esc_html_e( 'Back to orders', 'nexora' ); ?></a>
			<h1 class="account-panel__title"><?php esc_html_e( 'Order', 'nexora' ); ?> <span class="num ltr">#<?php echo esc_html( $order->get_order_number() ); ?></span></h1>
			<p class="text-muted small"><?php /* translators: %s: date */ printf( esc_html__( 'Placed on %s', 'nexora' ), esc_html( nexora_num( wc_format_datetime( $order->get_date_created() ) ) ) ); ?></p>
		</div>
		<?php nexora_order_status_badge( $order ); ?>
	</div>
	<?php if ( ! $nexora_failed ) : ?>
		<ol class="timeline" aria-label="<?php esc_attr_e( 'Order progress', 'nexora' ); ?>">
			<?php $nexora_i = 0; foreach ( $nexora_steps as $nexora_step ) : ?>
				<li class="timeline__step<?php echo $nexora_i < $nexora_current ? ' is-done' : ''; ?><?php echo $nexora_i === $nexora_current ? ' is-active' : ''; ?>" <?php echo $nexora_i === $nexora_current ? 'aria-current="step"' : ''; ?>>
					<span class="timeline__dot"><?php nexora_the_icon( $nexora_i < $nexora_current ? 'check' : $nexora_step[1], 'xs' ); ?></span>
					<span class="timeline__label"><?php echo esc_html( $nexora_step[0] ); ?></span>
				</li>
			<?php $nexora_i++; endforeach; ?>
		</ol>
	<?php else : ?>
		<div class="alert alert--danger"><?php nexora_the_icon( 'warning', 'sm' ); ?><span><?php echo esc_html( wc_get_order_status_name( $nexora_status ) ); ?></span></div>
	<?php endif; ?>
</div>

<div class="row g-4">
	<div class="col-lg-8">
		<div class="account-panel">
			<div class="account-panel__head"><h2 class="account-panel__title"><?php esc_html_e( 'Items', 'nexora' ); ?> <span class="badge badge--soft"><?php echo esc_html( nexora_num( $order->get_item_count() ) ); ?></span></h2></div>
			<div class="order-items">
				<?php foreach ( $order->get_items() as $nexora_item_id => $nexora_item ) : ?>
					<?php $nexora_p = $nexora_item->get_product(); ?>
					<div class="order-item" data-product-id="<?php echo esc_attr( $nexora_item->get_product_id() ); ?>">
						<a class="order-item__img" href="<?php echo esc_url( $nexora_p ? $nexora_p->get_permalink() : '#' ); ?>"><?php echo $nexora_p ? $nexora_p->get_image( 'nexora-thumb', array( 'loading' => 'lazy' ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						<div class="order-item__body">
							<a class="order-item__title" href="<?php echo esc_url( $nexora_p ? $nexora_p->get_permalink() : '#' ); ?>"><?php echo esc_html( $nexora_item->get_name() ); ?></a>
							<div class="order-item__meta small text-muted"><?php echo wp_kses_post( wc_display_item_meta( $nexora_item, array( 'echo' => false, 'before' => '', 'after' => '', 'separator' => ' · ' ) ) ); ?><?php if ( $nexora_p && $nexora_p->get_sku() ) : ?><span class="ltr"><?php echo esc_html( $nexora_p->get_sku() ); ?></span><?php endif; ?></div>
							<div class="order-item__row"><span class="small text-muted"><?php echo esc_html( nexora_num( $nexora_item->get_quantity() ) ); ?> × <?php echo wp_kses_post( wc_price( $order->get_item_subtotal( $nexora_item, false, true ) ) ); ?></span><span class="num fw-bold"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $nexora_item ) ); ?></span></div>
							<?php do_action( 'woocommerce_order_item_meta_end', $nexora_item_id, $nexora_item, $order, false ); ?>
						</div>
						<?php if ( $nexora_p && $order->has_status( 'completed' ) && wc_review_ratings_enabled() ) : ?><a class="btn btn--ghost btn--sm order-item__review" href="<?php echo esc_url( $nexora_p->get_permalink() . '#reviews' ); ?>"><?php nexora_the_icon( 'star', 'xs' ); ?><?php esc_html_e( 'Review', 'nexora' ); ?></a><?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php do_action( 'woocommerce_order_details_after_order_table_items', $order ); ?>
		</div>

		<?php if ( $order->get_customer_note() || $nexora_notes ) : ?>
			<div class="account-panel">
				<div class="account-panel__head"><h2 class="account-panel__title"><?php esc_html_e( 'Order updates', 'nexora' ); ?></h2></div>
				<ol class="notes">
					<?php foreach ( $nexora_notes as $nexora_note ) : ?>
						<li class="notes__item"><time class="small text-muted"><?php echo esc_html( nexora_num( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $nexora_note->date_created ) ) ) ); ?></time><div><?php echo wp_kses_post( wpautop( wptexturize( $nexora_note->content ) ) ); ?></div></li>
					<?php endforeach; ?>
					<?php if ( $order->get_customer_note() ) : ?><li class="notes__item"><span class="small text-muted"><?php esc_html_e( 'Your note', 'nexora' ); ?></span><div><?php echo wp_kses_post( nl2br( esc_html( $order->get_customer_note() ) ) ); ?></div></li><?php endif; ?>
				</ol>
			</div>
		<?php endif; ?>
	</div>

	<div class="col-lg-4">
		<div class="summary summary--static">
			<h2 class="summary__title summary__title--plain"><?php esc_html_e( 'Summary', 'nexora' ); ?></h2>
			<div class="summary__rows">
				<?php foreach ( $order->get_order_item_totals() as $nexora_key => $nexora_total ) : ?>
					<?php if ( 'order_total' === $nexora_key ) { continue; } ?>
					<div class="summary__row<?php echo 'discount' === $nexora_key ? ' summary__row--discount' : ''; ?>"><span><?php echo esc_html( $nexora_total['label'] ); ?></span><span class="num"><?php echo wp_kses_post( $nexora_total['value'] ); ?></span></div>
				<?php endforeach; ?>
			</div>
			<div class="summary__total"><span><?php esc_html_e( 'Total', 'nexora' ); ?></span><span class="num"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span></div>
			<div class="summary__actions">
				<?php foreach ( wc_get_account_orders_actions( $order ) as $nexora_key => $nexora_action ) : ?>
					<?php if ( 'view' === $nexora_key ) { continue; } ?>
					<a class="btn <?php echo 'pay' === $nexora_key ? 'btn--primary' : ( 'cancel' === $nexora_key ? 'btn--danger-outline' : 'btn--outline' ); ?> btn--block woocommerce-button <?php echo esc_attr( $nexora_key ); ?>" href="<?php echo esc_url( $nexora_action['url'] ); ?>"><?php echo esc_html( $nexora_action['name'] ); ?></a>
				<?php endforeach; ?>
				<button type="button" class="btn btn--dark btn--block" data-reorder="<?php echo esc_attr( $order->get_id() ); ?>"><?php nexora_the_icon( 'refresh', 'xs' ); ?><?php esc_html_e( 'Reorder', 'nexora' ); ?></button>
				<button type="button" class="btn btn--ghost btn--block" onclick="window.print()"><?php nexora_the_icon( 'printer', 'xs' ); ?><?php esc_html_e( 'Print invoice', 'nexora' ); ?></button>
			</div>
		</div>

		<div class="account-panel">
			<div class="account-panel__head"><h2 class="account-panel__title"><?php esc_html_e( 'Delivery & payment', 'nexora' ); ?></h2></div>
			<dl class="def-list small">
				<?php if ( $order->get_formatted_shipping_address() ) : ?><dt class="text-muted"><?php esc_html_e( 'Ship to', 'nexora' ); ?></dt><dd><?php echo wp_kses_post( $order->get_formatted_shipping_address() ); ?></dd><?php endif; ?>
				<dt class="text-muted"><?php esc_html_e( 'Bill to', 'nexora' ); ?></dt><dd><?php echo wp_kses_post( $order->get_formatted_billing_address( esc_html__( 'N/A', 'nexora' ) ) ); ?></dd>
				<?php if ( $order->get_billing_phone() ) : ?><dt class="text-muted"><?php esc_html_e( 'Phone', 'nexora' ); ?></dt><dd class="ltr"><?php echo esc_html( $order->get_billing_phone() ); ?></dd><?php endif; ?>
				<?php if ( $order->get_shipping_method() ) : ?><dt class="text-muted"><?php esc_html_e( 'Shipping', 'nexora' ); ?></dt><dd><?php echo esc_html( $order->get_shipping_method() ); ?></dd><?php endif; ?>
				<?php if ( $order->get_payment_method_title() ) : ?><dt class="text-muted"><?php esc_html_e( 'Payment', 'nexora' ); ?></dt><dd><?php echo esc_html( $order->get_payment_method_title() ); ?></dd><?php endif; ?>
			</dl>
		</div>
	</div>
</div>
<?php do_action( 'woocommerce_view_order', $order->get_id() ); ?>
