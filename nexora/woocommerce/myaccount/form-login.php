<?php
/**
 * Login / register.
 *
 * @package Nexora
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );
$nexora_register = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
$nexora_show     = isset( $_GET['action'] ) && 'register' === $_GET['action'] && $nexora_register ? 'register' : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$nexora_img      = nexora_image_url( nexora_option( 'pages', 'auth_image' ), 'large' );
$nexora_redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$nexora_perks    = array( __( 'Track orders in real time', 'nexora' ), __( 'Save addresses for faster checkout', 'nexora' ), __( 'Wishlist synced across devices', 'nexora' ), __( 'Exclusive member offers', 'nexora' ) );
?>
<section class="auth" data-auth-page>
	<div class="auth__visual<?php echo $nexora_img ? '' : ' auth__visual--gradient'; ?>">
		<?php if ( $nexora_img ) : ?><img src="<?php echo esc_url( $nexora_img ); ?>" width="800" height="597" alt="" decoding="async"><?php endif; ?>
		<div class="auth__visual-body">
			<h2 class="auth__visual-title"><?php /* translators: %s: site name */ printf( esc_html__( 'Welcome to %s', 'nexora' ), esc_html( get_bloginfo( 'name' ) ) ); ?></h2>
			<ul class="auth__visual-list"><?php foreach ( $nexora_perks as $nexora_perk ) : ?><li><?php nexora_the_icon( 'checkmark-circle', 'sm' ); ?><?php echo esc_html( $nexora_perk ); ?></li><?php endforeach; ?></ul>
		</div>
	</div>
	<div class="auth__form-wrap">
		<?php woocommerce_output_all_notices(); ?>
		<form class="auth-card woocommerce-form woocommerce-form-login login" method="post" data-auth-form="login" <?php echo 'login' === $nexora_show ? '' : 'hidden'; ?> <?php do_action( 'woocommerce_login_form_tag' ); ?>>
			<div class="auth-card__head">
				<h1 class="auth-card__title"><?php esc_html_e( 'Log in', 'nexora' ); ?></h1>
				<?php if ( $nexora_register ) : ?><p class="auth-card__sub"><?php esc_html_e( 'New here?', 'nexora' ); ?> <a href="<?php echo esc_url( add_query_arg( 'action', 'register', wc_get_page_permalink( 'myaccount' ) ) ); ?>" data-auth-switch="register"><?php esc_html_e( 'Create an account', 'nexora' ); ?></a></p><?php endif; ?>
			</div>
			<?php do_action( 'woocommerce_login_form_start' ); ?>
			<div class="form-group">
				<label class="form-label" for="username"><?php esc_html_e( 'Username or email', 'nexora' ); ?> <span class="req">*</span></label>
				<div class="input-icon"><?php nexora_the_icon( 'user', 'sm' ); ?><input id="username" class="form-control woocommerce-Input" name="username" autocomplete="username" required dir="ltr" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore ?>"></div>
			</div>
			<div class="form-group">
				<label class="form-label" for="password"><?php esc_html_e( 'Password', 'nexora' ); ?> <span class="req">*</span></label>
				<div class="input-icon input-icon--action"><?php nexora_the_icon( 'lock', 'sm' ); ?><input id="password" class="form-control woocommerce-Input" name="password" type="password" autocomplete="current-password" required dir="ltr"><button type="button" class="icon-btn icon-btn--ghost input-action" data-toggle-password aria-label="<?php esc_attr_e( 'Show password', 'nexora' ); ?>" aria-pressed="false"><?php nexora_the_icon( 'eye', 'sm' ); ?></button></div>
			</div>
			<?php do_action( 'woocommerce_login_form' ); ?>
			<div class="auth-card__row">
				<label class="check woocommerce-form__label-for-checkbox"><input class="check__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" value="forever" checked><span class="check__box"></span><span class="check__label"><?php esc_html_e( 'Remember me', 'nexora' ); ?></span></label>
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot password?', 'nexora' ); ?></a>
			</div>
			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
			<?php if ( $nexora_redirect ) : ?><input type="hidden" name="redirect" value="<?php echo esc_url( $nexora_redirect ); ?>"><?php endif; ?>
			<button type="submit" class="btn btn--primary btn--lg btn--block woocommerce-button" name="login" value="<?php esc_attr_e( 'Log in', 'nexora' ); ?>"><?php esc_html_e( 'Log in', 'nexora' ); ?></button>
			<?php do_action( 'woocommerce_login_form_end' ); ?>
			<?php if ( has_action( 'nexora_social_login' ) ) : ?><div class="divider--text"><?php esc_html_e( 'or continue with', 'nexora' ); ?></div><div class="auth-card__social"><?php do_action( 'nexora_social_login' ); ?></div><?php endif; ?>
		</form>

		<?php if ( $nexora_register ) : ?>
			<form class="auth-card woocommerce-form woocommerce-form-register register" method="post" data-auth-form="register" <?php echo 'register' === $nexora_show ? '' : 'hidden'; ?> <?php do_action( 'woocommerce_register_form_tag' ); ?>>
				<div class="auth-card__head">
					<h1 class="auth-card__title"><?php esc_html_e( 'Create account', 'nexora' ); ?></h1>
					<p class="auth-card__sub"><?php esc_html_e( 'Already registered?', 'nexora' ); ?> <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" data-auth-switch="login"><?php esc_html_e( 'Log in', 'nexora' ); ?></a></p>
				</div>
				<?php do_action( 'woocommerce_register_form_start' ); ?>
				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
					<div class="form-group"><label class="form-label" for="reg_username"><?php esc_html_e( 'Username', 'nexora' ); ?> <span class="req">*</span></label><div class="input-icon"><?php nexora_the_icon( 'user', 'sm' ); ?><input class="form-control woocommerce-Input" name="username" id="reg_username" autocomplete="username" dir="ltr" required value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore ?>"></div></div>
				<?php endif; ?>
				<div class="form-group"><label class="form-label" for="reg_email"><?php esc_html_e( 'Email address', 'nexora' ); ?> <span class="req">*</span></label><div class="input-icon"><?php nexora_the_icon( 'envelope', 'sm' ); ?><input type="email" class="form-control woocommerce-Input" name="email" id="reg_email" autocomplete="email" dir="ltr" required value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore ?>"></div></div>
				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
					<div class="form-group"><label class="form-label" for="reg_password"><?php esc_html_e( 'Password', 'nexora' ); ?> <span class="req">*</span></label><div class="input-icon input-icon--action"><?php nexora_the_icon( 'lock', 'sm' ); ?><input type="password" class="form-control woocommerce-Input" name="password" id="reg_password" autocomplete="new-password" dir="ltr" required><button type="button" class="icon-btn icon-btn--ghost input-action" data-toggle-password aria-label="<?php esc_attr_e( 'Show password', 'nexora' ); ?>" aria-pressed="false"><?php nexora_the_icon( 'eye', 'sm' ); ?></button></div></div>
				<?php else : ?>
					<p class="small text-muted"><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'nexora' ); ?></p>
				<?php endif; ?>
				<?php do_action( 'woocommerce_register_form' ); ?>
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="btn btn--primary btn--lg btn--block woocommerce-button" name="register" value="<?php esc_attr_e( 'Register', 'nexora' ); ?>"><?php esc_html_e( 'Create account', 'nexora' ); ?></button>
				<?php do_action( 'woocommerce_register_form_end' ); ?>
			</form>
		<?php endif; ?>
	</div>
</section>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
