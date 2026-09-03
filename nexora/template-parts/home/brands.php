<?php
/**
 * Brand strip.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_home = $args['home'];
$nexora_list = array();
if ( 'manual' === $nexora_home['brands_source'] ) {
	foreach ( (array) $nexora_home['brands_manual'] as $nexora_b ) {
		if ( $nexora_b['name'] ) {
			$nexora_list[] = array( $nexora_b['name'], $nexora_b['url'] ?: nexora_shop_url(), $nexora_b['logo'] ? wp_get_attachment_image_url( (int) $nexora_b['logo'], 'medium' ) : '' );
		}
	}
} else {
	foreach ( nexora_brands( 16 ) as $nexora_term ) {
		$nexora_list[] = array( $nexora_term->name, nexora_brand_url( $nexora_term ), nexora_brand_logo( $nexora_term ) );
	}
}
if ( ! $nexora_list ) {
	return;
}
?>
<section class="section section--sm brand-strip" aria-labelledby="sec-brands">
	<div class="container">
		<div class="section-head section-head--center section-head--tight">
			<div><h2 class="section-head__title section-head__title--sm" id="sec-brands"><?php echo esc_html( $nexora_home['brands_title'] ); ?></h2><?php if ( $nexora_home['brands_sub'] ) : ?><p class="section-head__sub"><?php echo esc_html( $nexora_home['brands_sub'] ); ?></p><?php endif; ?></div>
		</div>
		<div class="swiper" data-swiper="brands">
			<div class="swiper-wrapper">
				<?php foreach ( $nexora_list as $nexora_b ) : ?>
					<div class="swiper-slide" style="inline-size:auto">
						<a class="brand-logo" href="<?php echo esc_url( $nexora_b[1] ); ?>" aria-label="<?php echo esc_attr( $nexora_b[0] ); ?>">
							<?php if ( $nexora_b[2] ) : ?>
								<img src="<?php echo esc_url( $nexora_b[2] ); ?>" alt="<?php echo esc_attr( $nexora_b[0] ); ?>" loading="lazy" height="40">
							<?php else : ?>
								<span aria-hidden="true"><?php echo esc_html( $nexora_b[0] ); ?></span>
							<?php endif; ?>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
