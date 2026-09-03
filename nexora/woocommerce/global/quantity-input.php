<?php
/**
 * Quantity stepper.
 *
 * @package Nexora
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

/* translators: %s: Quantity. */
$label = ! empty( $args['product_name'] ) ? sprintf( esc_html__( '%s quantity', 'nexora' ), wp_strip_all_tags( $args['product_name'] ) ) : esc_html__( 'Quantity', 'nexora' );

if ( $max_value && $min_value === $max_value ) {
	?>
	<div class="quantity hidden">
		<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $min_value ); ?>" />
	</div>
	<?php
} else {
	$nexora_max = $max_value ? (int) $max_value : 99;
	?>
	<div class="qty quantity<?php echo ! empty( $args['classes'] ) && in_array( 'qty--sm', (array) $args['classes'], true ) ? ' qty--sm' : ''; ?>" data-qty data-max="<?php echo (int) $nexora_max; ?>">
		<button type="button" class="qty__btn" data-qty-dec aria-label="<?php esc_attr_e( 'Decrease quantity', 'nexora' ); ?>" <?php disabled( (float) $input_value <= (float) $min_value ); ?>><?php nexora_the_icon( 'minus', 'xs' ); ?></button>
		<input
			type="<?php echo esc_attr( $type ); ?>"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="qty__input <?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			aria-label="<?php echo esc_attr( $label ); ?>"
			min="<?php echo esc_attr( $min_value ); ?>"
			<?php if ( 0 < $max_value ) : ?>max="<?php echo esc_attr( $max_value ); ?>"<?php endif; ?>
			<?php if ( ! empty( $step ) ) : ?>step="<?php echo esc_attr( $step ); ?>"<?php endif; ?>
			<?php if ( ! empty( $placeholder ) ) : ?>placeholder="<?php echo esc_attr( $placeholder ); ?>"<?php endif; ?>
			inputmode="<?php echo esc_attr( $inputmode ); ?>"
			autocomplete="off"
		/>
		<button type="button" class="qty__btn" data-qty-inc aria-label="<?php esc_attr_e( 'Increase quantity', 'nexora' ); ?>" <?php disabled( $max_value && (float) $input_value >= (float) $max_value ); ?>><?php nexora_the_icon( 'plus', 'xs' ); ?></button>
	</div>
	<?php
}
