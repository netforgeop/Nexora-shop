<?php
/**
 * Lost password.
 *
 * @package Nexora
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>
<section class="auth auth--single">
	<div class="auth__form-wrap">
		<?php woocommerce_output_all_notices(); ?>
		<form method="post" class="auth-card woocommerce-ResetPassword lost_reset_password">
			<div class="auth-card__head auth-card__head--icon">
				<span class="newsletter__icon"><?php nexora_the_icon( 'key', 'lg' ); ?></span>
				<h1 class="auth-card__title"><?php esc_html_e( 'Reset your password', 'nexora' ); ?></h1>
				<p class="auth-card__sub"><?php echo esc_html( apply_filters( 'woocommerce_lost_password_message', __( 'Enter your username or email address and we will send you a link to create a new password.', 'nexora' ) ) ); ?></p>
			</div>
			<div class="form-group">
				<label class="form-label" for="user_login"><?php esc_html_e( 'Username or email', 'nexora' ); ?> <span class="req">*</span></label>
				<div class="input-icon"><?php nexora_the_icon( 'user', 'sm' ); ?><input class="form-control woocommerce-Input" type="text" name="user_login" id="user_login" autocomplete="username" required dir="ltr" /></div>
			</div>
			<?php do_action( 'woocommerce_lostpassword_form' ); ?>
			<input type="hidden" name="wc_reset_password" value="true" />
			<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
			<button type="submit" class="btn btn--primary btn--lg btn--block woocommerce-Button" value="<?php esc_attr_e( 'Send reset link', 'nexora' ); ?>"><?php esc_html_e( 'Send reset link', 'nexora' ); ?></button>
			<p class="auth-card__footer"><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php nexora_the_icon( 'arrow-right', 'xs', 'icon--flip-ltr' ); ?> <?php esc_html_e( 'Back to login', 'nexora' ); ?></a></p>
		</form>
	</div>
</section>
<?php do_action( 'woocommerce_after_lost_password_form' ); ?>
