<?php
/**
 * Account dashboard.
 *
 * @package Nexora
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$nexora_user   = wp_get_current_user();
$nexora_since  = nexora_num( date_i18n( 'Y' === get_option( 'date_format' ) ? 'Y' : 'F Y', strtotime( $nexora_user->user_registered ) ) );
$nexora_orders = wc_get_orders( array( 'customer_id' => $nexora_user->ID, 'limit' => 4, 'orderby' => 'date', 'order' => 'DESC' ) );
$nexora_addr   = wc_get_account_formatted_address( 'shipping' );
if ( ! $nexora_addr ) {
	$nexora_addr = wc_get_account_formatted_address( 'billing' );
}
?>
<div class="account-panel">
	<div class="account-panel__head">
		<div>
			<h1 class="account-panel__title"><?php /* translators: %s: display name */ printf( esc_html__( 'Welcome, %s', 'nexora' ), esc_html( $nexora_user->display_name ) ); ?></h1>
			<p class="text-muted small"><?php esc_html_e( 'From your dashboard you can view recent orders, manage your addresses and edit your account details.', 'nexora' ); ?></p>
		</div>
		<span class="status status--info"><?php nexora_the_icon( 'medal-first', 'xs' ); ?><?php /* translators: %s: date */ printf( esc_html__( 'Member since %s', 'nexora' ), esc_html( $nexora_since ) ); ?></span>
	</div>
	<div class="stat-grid">
		<?php foreach ( nexora_account_stats() as $nexora_stat ) : ?>
			<a class="stat" href="<?php echo esc_url( $nexora_stat['url'] ); ?>"><span class="stat__icon"><?php nexora_the_icon( $nexora_stat['icon'] ); ?></span><div><div class="stat__value"><?php echo esc_html( nexora_num( $nexora_stat['value'] ) ); ?></div><div class="stat__label"><?php echo esc_html( $nexora_stat['label'] ); ?></div></div></a>
		<?php endforeach; ?>
	</div>
</div>

<div class="account-panel">
	<div class="account-panel__head"><h2 class="account-panel__title"><?php esc_html_e( 'Recent orders', 'nexora' ); ?></h2><a class="link--arrow small" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'View all orders', 'nexora' ); ?><?php nexora_the_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ); ?></a></div>
	<?php if ( $nexora_orders ) : ?>
		<div class="table-scroll">
			<table class="data-table">
				<thead><tr><th scope="col"><?php esc_html_e( 'Order', 'nexora' ); ?></th><th scope="col"><?php esc_html_e( 'Date', 'nexora' ); ?></th><th scope="col"><?php esc_html_e( 'Items', 'nexora' ); ?></th><th scope="col"><?php esc_html_e( 'Total', 'nexora' ); ?></th><th scope="col"><?php esc_html_e( 'Status', 'nexora' ); ?></th><th scope="col"><span class="visually-hidden"><?php esc_html_e( 'Details', 'nexora' ); ?></span></th></tr></thead>
				<tbody>
					<?php foreach ( $nexora_orders as $nexora_order ) : ?>
						<tr>
							<td><span class="num ltr">#<?php echo esc_html( $nexora_order->get_order_number() ); ?></span></td>
							<td><?php echo esc_html( nexora_num( wc_format_datetime( $nexora_order->get_date_created() ) ) ); ?></td>
							<td><div class="data-table__thumbs"><?php foreach ( array_slice( $nexora_order->get_items(), 0, 3 ) as $nexora_item ) { $nexora_p = $nexora_item->get_product(); if ( $nexora_p ) { echo $nexora_p->get_image( 'nexora-thumb', array( 'loading' => 'lazy' ) ); } } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></td>
							<td><span class="num"><?php echo wp_kses_post( $nexora_order->get_formatted_order_total() ); ?></span></td>
							<td><?php nexora_order_status_badge( $nexora_order ); ?></td>
							<td><a class="btn btn--outline btn--sm" href="<?php echo esc_url( $nexora_order->get_view_order_url() ); ?>"><?php esc_html_e( 'View', 'nexora' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<?php nexora_empty_state( array( 'icon' => 'box', 'title' => __( 'No orders yet', 'nexora' ), 'text' => __( 'When you place an order it will show up here.', 'nexora' ), 'cta' => __( 'Start shopping', 'nexora' ), 'href' => nexora_shop_url(), 'compact' => true ) ); ?>
	<?php endif; ?>
</div>

<div class="row g-4">
	<div class="col-md-6">
		<div class="account-panel account-panel--full">
			<div class="account-panel__head"><h2 class="account-panel__title"><?php esc_html_e( 'Addresses', 'nexora' ); ?></h2><a class="link--arrow small" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>"><?php esc_html_e( 'Edit', 'nexora' ); ?><?php nexora_the_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ); ?></a></div>
			<?php if ( $nexora_addr ) : ?>
				<div class="address-card is-default address-card--dashed">
					<div class="address-card__title"><?php nexora_the_icon( 'home', 'xs' ); ?><?php esc_html_e( 'Default address', 'nexora' ); ?> <span class="badge badge--discount"><?php esc_html_e( 'Default', 'nexora' ); ?></span></div>
					<p class="address-card__text"><?php echo wp_kses_post( $nexora_addr ); ?></p>
					<?php if ( $nexora_user->billing_phone ) : ?><div class="address-card__meta"><?php echo esc_html( $nexora_user->display_name ); ?> · <span class="ltr"><?php echo esc_html( $nexora_user->billing_phone ); ?></span></div><?php endif; ?>
				</div>
			<?php else : ?>
				<p class="small text-muted"><?php esc_html_e( 'You have not set up an address yet.', 'nexora' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<div class="col-md-6">
		<div class="account-panel account-panel--full">
			<div class="account-panel__head"><h2 class="account-panel__title"><?php esc_html_e( 'Profile', 'nexora' ); ?></h2><a class="link--arrow small" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>"><?php esc_html_e( 'Edit', 'nexora' ); ?><?php nexora_the_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ); ?></a></div>
			<dl class="def-list small">
				<dt class="text-muted"><?php esc_html_e( 'Full name', 'nexora' ); ?></dt><dd class="fw-medium"><?php echo esc_html( trim( $nexora_user->first_name . ' ' . $nexora_user->last_name ) ?: $nexora_user->display_name ); ?></dd>
				<dt class="text-muted"><?php esc_html_e( 'Email', 'nexora' ); ?></dt><dd class="fw-medium ltr"><?php echo esc_html( $nexora_user->user_email ); ?></dd>
				<?php if ( $nexora_user->billing_phone ) : ?><dt class="text-muted"><?php esc_html_e( 'Phone', 'nexora' ); ?></dt><dd class="fw-medium ltr"><?php echo esc_html( $nexora_user->billing_phone ); ?></dd><?php endif; ?>
			</dl>
		</div>
	</div>
</div>
<?php
do_action( 'woocommerce_account_dashboard' );
do_action( 'woocommerce_before_my_account' );
do_action( 'woocommerce_after_my_account' );
