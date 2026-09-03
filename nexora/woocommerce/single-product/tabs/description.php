<?php
/**
 * Description tab.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

global $product;
$nexora_highlights = $args['highlights'] ?? array();
$nexora_tags       = get_the_terms( $product->get_id(), 'product_tag' );
$nexora_has_side   = $nexora_highlights || ( $nexora_tags && ! is_wp_error( $nexora_tags ) );
?>
<div class="product-desc-grid<?php echo $nexora_has_side ? '' : ' product-desc-grid--single'; ?>">
	<div class="prose">
		<?php
		$nexora_content = get_the_content();
		if ( trim( $nexora_content ) ) {
			the_content();
		} elseif ( $product->get_short_description() ) {
			echo wp_kses_post( wpautop( $product->get_short_description() ) );
		} else {
			echo '<p class="text-muted">' . esc_html__( 'No description available for this product yet.', 'nexora' ) . '</p>';
		}
		?>
	</div>
	<?php if ( $nexora_has_side ) : ?>
		<div>
			<div class="card-surface card-surface--pad">
				<?php if ( $nexora_highlights ) : ?>
					<h3 class="h5 card-surface__title"><?php esc_html_e( 'Highlights', 'nexora' ); ?></h3>
					<ul class="product-info__highlights product-info__highlights--plain">
						<?php foreach ( $nexora_highlights as $nexora_h ) : ?><li><?php nexora_the_icon( 'check', 'xs' ); ?><span><?php echo esc_html( $nexora_h ); ?></span></li><?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( $nexora_tags && ! is_wp_error( $nexora_tags ) ) : ?>
					<?php if ( $nexora_highlights ) : ?><hr class="divider"><?php endif; ?>
					<div class="cluster">
						<?php foreach ( $nexora_tags as $nexora_t ) : ?><a class="chip" href="<?php echo esc_url( get_term_link( $nexora_t ) ); ?>"><?php nexora_the_icon( 'tag', 'xs' ); ?><?php echo esc_html( $nexora_t->name ); ?></a><?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
