<?php
/**
 * Variable product add to cart.
 *
 * @package Nexora
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
$nexora_color    = 'pa_' . sanitize_key( nexora_option( 'shop', 'filter_color_attr', 'color' ) );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart buy-box__form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data" data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-add-to-cart-form>
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock stock--out"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'nexora' ) ) ); ?></p>
	<?php else : ?>
		<div class="variants variations">
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<?php
				$nexora_is_color = $attribute_name === $nexora_color;
				$nexora_terms    = taxonomy_exists( $attribute_name ) ? wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) ) : array();
				$nexora_selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) : $product->get_variation_default_attribute( $attribute_name ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
				<fieldset class="variant" data-variant="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>" data-attribute="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>">
					<legend class="variant__label"><?php echo esc_html( wc_attribute_label( $attribute_name ) ); ?>: <span data-variant-value></span></legend>
					<div class="variant__options">
						<?php
						if ( $nexora_terms ) :
							foreach ( $nexora_terms as $nexora_t ) :
								if ( ! in_array( $nexora_t->slug, $options, true ) ) {
									continue;
								}
								$nexora_hex = $nexora_is_color ? ( get_term_meta( $nexora_t->term_id, 'nexora_color', true ) ?: nexora_color_from_name( $nexora_t->slug ) ) : '';
								?>
								<label class="variant__option"><input type="radio" name="nexora_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>" value="<?php echo esc_attr( $nexora_t->slug ); ?>" data-label="<?php echo esc_attr( $nexora_t->name ); ?>" <?php checked( $nexora_selected, $nexora_t->slug ); ?>><?php if ( $nexora_is_color ) : ?><span class="swatch" style="background:<?php echo esc_attr( $nexora_hex ); ?>"></span><span class="visually-hidden"><?php echo esc_html( $nexora_t->name ); ?></span><?php else : ?><span class="size-chip"><?php echo esc_html( $nexora_t->name ); ?></span><?php endif; ?></label>
								<?php
							endforeach;
						else :
							foreach ( $options as $nexora_o ) :
								?>
								<label class="variant__option"><input type="radio" name="nexora_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>" value="<?php echo esc_attr( $nexora_o ); ?>" data-label="<?php echo esc_attr( $nexora_o ); ?>" <?php checked( $nexora_selected, $nexora_o ); ?>><span class="size-chip"><?php echo esc_html( $nexora_o ); ?></span></label>
								<?php
							endforeach;
						endif;
						?>
					</div>
					<div class="visually-hidden">
						<?php
						// The real select WooCommerce's variation script drives; the radios above mirror into it.
						wc_dropdown_variation_attribute_options( array( 'options' => $options, 'attribute' => $attribute_name, 'product' => $product, 'selected' => $nexora_selected ) );
						?>
					</div>
				</fieldset>
			<?php endforeach; ?>
			<a class="reset_variations variant__guide" href="#"><?php esc_html_e( 'Clear', 'nexora' ); ?></a>
		</div>
		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
			do_action( 'woocommerce_before_single_variation' );
			/**
			 * Renders the variation price/availability + add-to-cart button (variation-add-to-cart-button.php).
			 */
			do_action( 'woocommerce_single_variation' );
			do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
