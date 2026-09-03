<?php
/**
 * Reset password form.
 *
 * @package Nexora
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_reset_password_form' );
?>
<section class="auth auth--single">
	<div class="auth__form-wrap">
		<?php woocommerce_output_all_notices(); ?>
		<form method="post" class="auth-card woocommerce-ResetPassword lost_reset_password">
			<div class="auth-card__head auth-card__head--icon">
				<span class="newsletter__icon"><?php nexora_the_icon( 'lock', 'lg' ); ?></span>
				<h1 class="auth-card__title"><?php esc_html_e( 'Choose a new password', 'nexora' ); ?></h1>
			</div>
			<div class="form-group"><label class="form-label" for="password_1"><?php esc_html_e( 'New password', 'nexora' ); ?> <span class="req">*</span></label><div class="input-icon input-icon--action"><?php nexora_the_icon( 'lock', 'sm' ); ?><input type="password" class="form-control woocommerce-Input" name="password_1" id="password_1" autocomplete="new-password" required dir="ltr" /><button type="button" class="icon-btn icon-btn--ghost input-action" data-toggle-password aria-label="<?php esc_attr_e( 'Show password', 'nexora' ); ?>" aria-pressed="false"><?php nexora_the_icon( 'eye', 'sm' ); ?></button></div></div>
			<div class="form-group"><label class="form-label" for="password_2"><?php esc_html_e( 'Confirm password', 'nexora' ); ?> <span class="req">*</span></label><div class="input-icon"><?php nexora_the_icon( 'lock', 'sm' ); ?><input type="password" class="form-control woocommerce-Input" name="password_2" id="password_2" autocomplete="new-password" required dir="ltr" /></div></div>
			<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
			<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />
			<?php do_action( 'woocommerce_resetpassword_form' ); ?>
			<input type="hidden" name="wc_reset_password" value="true" />
			<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>
			<button type="submit" class="btn btn--primary btn--lg btn--block woocommerce-Button" value="<?php esc_attr_e( 'Save', 'nexora' ); ?>"><?php esc_html_e( 'Save new password', 'nexora' ); ?></button>
		</form>
	</div>
</section>
<?php do_action( 'woocommerce_after_reset_password_form' ); ?>
