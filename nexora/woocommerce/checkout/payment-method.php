<?php
/**
 * Single payment method as an option card.
 *
 * @package Nexora
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
$nexora_gid  = $gateway->id;
$nexora_icon = 'credit-card';
if ( false !== strpos( $nexora_gid, 'cod' ) ) {
	$nexora_icon = 'cash-dollar';
} elseif ( false !== strpos( $nexora_gid, 'bacs' ) ) {
	$nexora_icon = 'bank';
} elseif ( false !== strpos( $nexora_gid, 'cheque' ) ) {
	$nexora_icon = 'file-empty';
} elseif ( false !== strpos( $nexora_gid, 'wallet' ) ) {
	$nexora_icon = 'wallet';
}
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr( $nexora_gid ); ?> option-card">
	<input id="payment_method_<?php echo esc_attr( $nexora_gid ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $nexora_gid ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />
	<label class="option-card__body" for="payment_method_<?php echo esc_attr( $nexora_gid ); ?>">
		<span class="option-card__icon"><?php nexora_the_icon( apply_filters( 'nexora_gateway_icon', $nexora_icon, $gateway ), 'sm' ); ?></span>
		<span class="option-card__content"><span class="option-card__title"><?php echo wp_kses_post( $gateway->get_title() ); ?></span></span>
		<span class="option-card__logos"><?php echo $gateway->get_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</label>
	<?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
		<div class="payment_box payment_method_<?php echo esc_attr( $nexora_gid ); ?> option-card__box" <?php if ( ! $gateway->chosen ) : ?>style="display:none;"<?php endif; ?>>
			<?php $gateway->payment_fields(); ?>
		</div>
	<?php endif; ?>
</li>
