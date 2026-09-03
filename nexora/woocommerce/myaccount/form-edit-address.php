<?php
/**
 * Edit address form.
 *
 * @package Nexora
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? esc_html__( 'Billing address', 'nexora' ) : esc_html__( 'Shipping address', 'nexora' );
do_action( 'woocommerce_before_edit_account_address_form' );

if ( ! $load_address ) {
	wc_get_template( 'myaccount/my-address.php' );
} else {
	?>
	<form method="post" class="account-panel">
		<div class="account-panel__head">
			<div><a class="link--arrow small" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>"><?php nexora_the_icon( 'arrow-right', 'xs', 'icon--flip-ltr' ); ?><?php esc_html_e( 'All addresses', 'nexora' ); ?></a><h1 class="account-panel__title"><?php echo esc_html( apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ) ); ?></h1></div>
		</div>
		<div class="woocommerce-address-fields">
			<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>
			<div class="woocommerce-address-fields__field-wrapper form-grid">
				<?php foreach ( $address as $key => $field ) { woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) ); } ?>
			</div>
			<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>
			<div class="cluster account-panel__footer">
				<button type="submit" class="btn btn--primary" name="save_address" value="<?php esc_attr_e( 'Save address', 'nexora' ); ?>"><?php nexora_the_icon( 'check', 'xs' ); ?><?php esc_html_e( 'Save address', 'nexora' ); ?></button>
				<a class="btn btn--ghost" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>"><?php esc_html_e( 'Cancel', 'nexora' ); ?></a>
				<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
				<input type="hidden" name="action" value="edit_address" />
			</div>
		</div>
	</form>
	<?php
}
do_action( 'woocommerce_after_edit_account_address_form' );
