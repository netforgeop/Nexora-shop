<?php
/**
 * Edit account form.
 *
 * @package Nexora
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' );
$nexora_since = nexora_num( date_i18n( 'F Y', strtotime( $user->user_registered ) ) );
?>
<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>
	<div class="account-panel">
		<div class="account-panel__head"><h1 class="account-panel__title"><?php esc_html_e( 'Personal information', 'nexora' ); ?></h1></div>
		<div class="profile-head">
			<span class="avatar avatar--xl avatar--initial" aria-hidden="true"><?php echo esc_html( nexora_avatar_initial( $user->display_name ) ); ?></span>
			<div><div class="fw-bold text-strong"><?php echo esc_html( $user->display_name ); ?></div><div class="small text-muted"><?php /* translators: %s: date */ printf( esc_html__( 'Member since %s', 'nexora' ), esc_html( $nexora_since ) ); ?></div></div>
		</div>
		<?php do_action( 'woocommerce_edit_account_form_start' ); ?>
		<div class="form-row">
			<div class="form-group woocommerce-form-row"><label class="form-label" for="account_first_name"><?php esc_html_e( 'First name', 'nexora' ); ?> <span class="req">*</span></label><input type="text" class="form-control woocommerce-Input" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" required /></div>
			<div class="form-group woocommerce-form-row"><label class="form-label" for="account_last_name"><?php esc_html_e( 'Last name', 'nexora' ); ?> <span class="req">*</span></label><input type="text" class="form-control woocommerce-Input" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" required /></div>
		</div>
		<div class="form-row">
			<div class="form-group woocommerce-form-row"><label class="form-label" for="account_display_name"><?php esc_html_e( 'Display name', 'nexora' ); ?> <span class="req">*</span></label><input type="text" class="form-control woocommerce-Input" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" required /><span class="form-hint"><?php esc_html_e( 'This will be how your name will be displayed in the account section and in reviews', 'nexora' ); ?></span></div>
			<div class="form-group woocommerce-form-row"><label class="form-label" for="account_email"><?php esc_html_e( 'Email address', 'nexora' ); ?> <span class="req">*</span></label><input type="email" class="form-control woocommerce-Input" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" dir="ltr" required /></div>
		</div>
		<?php do_action( 'woocommerce_edit_account_form_fields' ); ?>
		<?php do_action( 'woocommerce_edit_account_form' ); ?>
		<div class="cluster account-panel__footer">
			<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
			<button type="submit" class="btn btn--primary woocommerce-Button" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'nexora' ); ?>"><?php nexora_the_icon( 'check', 'xs' ); ?><?php esc_html_e( 'Save changes', 'nexora' ); ?></button>
			<input type="hidden" name="action" value="save_account_details" />
		</div>
	</div>

	<div class="account-panel">
		<div class="account-panel__head"><h2 class="account-panel__title"><?php esc_html_e( 'Change password', 'nexora' ); ?></h2></div>
		<div class="form-row">
			<div class="form-group woocommerce-form-row"><label class="form-label" for="password_current"><?php esc_html_e( 'Current password', 'nexora' ); ?></label><input type="password" class="form-control woocommerce-Input" name="password_current" id="password_current" autocomplete="off" dir="ltr" /><span class="form-hint"><?php esc_html_e( 'Leave blank to leave unchanged', 'nexora' ); ?></span></div>
			<div class="form-group woocommerce-form-row"><label class="form-label" for="password_1"><?php esc_html_e( 'New password', 'nexora' ); ?></label><input type="password" class="form-control woocommerce-Input" name="password_1" id="password_1" autocomplete="off" dir="ltr" /></div>
			<div class="form-group woocommerce-form-row"><label class="form-label" for="password_2"><?php esc_html_e( 'Confirm new password', 'nexora' ); ?></label><input type="password" class="form-control woocommerce-Input" name="password_2" id="password_2" autocomplete="off" dir="ltr" /></div>
		</div>
		<div class="cluster account-panel__footer"><button type="submit" class="btn btn--dark" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'nexora' ); ?>"><?php nexora_the_icon( 'lock', 'xs' ); ?><?php esc_html_e( 'Change password', 'nexora' ); ?></button></div>
	</div>
	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>
<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
