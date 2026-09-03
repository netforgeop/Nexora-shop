<?php
/**
 * Lost password confirmation.
 *
 * @package Nexora
 * @version 3.9.0
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="auth auth--single">
	<div class="auth__form-wrap">
		<div class="auth-card">
			<div class="auth-card__head auth-card__head--icon">
				<span class="newsletter__icon"><?php nexora_the_icon( 'envelope', 'lg' ); ?></span>
				<h1 class="auth-card__title"><?php esc_html_e( 'Check your inbox', 'nexora' ); ?></h1>
				<p class="auth-card__sub"><?php echo esc_html( apply_filters( 'woocommerce_lost_password_confirmation_message', __( 'Password reset email has been sent.', 'nexora' ) ) ); ?></p>
				<p class="small text-muted"><?php esc_html_e( 'A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.', 'nexora' ); ?></p>
			</div>
			<p class="auth-card__footer"><a class="btn btn--outline" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Back to login', 'nexora' ); ?></a></p>
		</div>
	</div>
</section>
