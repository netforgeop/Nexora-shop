<?php
/**
 * Promo banners (2–4).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_banners = array_values( array_filter( (array) $args['home']['promo_banners'], static function ( $b ) { return ! empty( $b['image'] ); } ) );
if ( ! $nexora_banners ) {
	return;
}
?>
<section class="section section--flush-top" aria-label="<?php esc_attr_e( 'Promotions', 'nexora' ); ?>">
	<div class="container">
		<div class="promo-grid">
			<?php
			foreach ( $nexora_banners as $nexora_i => $nexora_b ) :
				$nexora_l = nexora_link_value( $nexora_b['link'] );
				$nexora_dark = 'dark' === ( $nexora_b['style'] ?? 'dark' );
				?>
				<a class="promo <?php echo $nexora_dark ? 'promo--dark' : 'promo--light'; ?>" href="<?php echo esc_url( $nexora_l['url'] ?: nexora_shop_url() ); ?>" data-reveal style="--reveal-delay:<?php echo (int) $nexora_i * 100; ?>ms">
					<?php echo wp_get_attachment_image( (int) $nexora_b['image'], 'nexora-banner', false, array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
					<span class="promo__body">
						<?php if ( $nexora_b['kicker'] ) : ?><span class="promo__kicker"><?php echo esc_html( $nexora_b['kicker'] ); ?></span><?php endif; ?>
						<?php if ( $nexora_b['title'] ) : ?><span class="promo__title"><?php echo esc_html( $nexora_b['title'] ); ?></span><?php endif; ?>
						<?php if ( $nexora_b['text'] ) : ?><span class="promo__text"><?php echo esc_html( $nexora_b['text'] ); ?></span><?php endif; ?>
						<span class="btn <?php echo $nexora_dark ? 'btn--primary' : 'btn--dark'; ?> btn--sm"><?php echo esc_html( $nexora_l['text'] ?: __( 'Shop now', 'nexora' ) ); ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
