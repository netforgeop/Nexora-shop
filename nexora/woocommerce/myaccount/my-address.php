<?php
/**
 * Addresses overview.
 *
 * @package Nexora
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();
if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters( 'woocommerce_my_account_get_addresses', array( 'billing' => __( 'Billing address', 'nexora' ), 'shipping' => __( 'Shipping address', 'nexora' ) ), $customer_id );
} else {
	$get_addresses = apply_filters( 'woocommerce_my_account_get_addresses', array( 'billing' => __( 'Billing address', 'nexora' ) ), $customer_id );
}
?>
<div class="account-panel">
	<div class="account-panel__head"><h1 class="account-panel__title"><?php esc_html_e( 'Addresses', 'nexora' ); ?></h1></div>
	<p class="small text-muted"><?php echo esc_html( apply_filters( 'woocommerce_my_account_my_address_description', __( 'The following addresses will be used on the checkout page by default.', 'nexora' ) ) ); ?></p>
	<div class="address-grid">
		<?php foreach ( $get_addresses as $name => $address_title ) : ?>
			<?php $address = wc_get_account_formatted_address( $name ); ?>
			<article class="address-card<?php echo 'shipping' === $name || 1 === count( $get_addresses ) ? ' is-default' : ''; ?>">
				<div class="address-card__title"><?php nexora_the_icon( 'billing' === $name ? 'briefcase' : 'home', 'xs' ); ?><span><?php echo esc_html( $address_title ); ?></span><?php if ( 'shipping' === $name || 1 === count( $get_addresses ) ) : ?> <span class="badge badge--discount"><?php esc_html_e( 'Default', 'nexora' ); ?></span><?php endif; ?></div>
				<p class="address-card__text"><?php echo $address ? wp_kses_post( $address ) : esc_html__( 'You have not set up this type of address yet.', 'nexora' ); ?></p>
				<?php do_action( 'woocommerce_my_account_after_my_address', $name ); ?>
				<div class="address-card__actions"><a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>"><?php nexora_the_icon( $address ? 'pencil' : 'plus', 'xs' ); ?><?php echo $address ? esc_html__( 'Edit', 'nexora' ) : esc_html__( 'Add', 'nexora' ); ?></a></div>
			</article>
		<?php endforeach; ?>
	</div>
</div>
