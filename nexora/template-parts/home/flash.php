<?php
/**
 * Flash sale: deal card with countdown + 3-up product carousel.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_home = $args['home'];
$nexora_ids  = nexora_query_products( $nexora_home['flash_source'], (int) $nexora_home['flash_count'], array( 'category' => $nexora_home['flash_category'], 'products' => $nexora_home['flash_products'] ) );
if ( ! $nexora_ids ) {
	return;
}
$nexora_end = trim( (string) $nexora_home['flash_end'] );
$nexora_ts  = $nexora_end ? strtotime( $nexora_end . ' ' . wp_timezone_string() ) : 0;
if ( $nexora_end && $nexora_ts && $nexora_ts < time() ) {
	return; // Sale ended — hide the section instead of showing 00:00:00.
}
$nexora_cta = nexora_link_value( $nexora_home['flash_cta'] );
?>
<section class="section flash-sale" aria-labelledby="sec-flash">
	<div class="container">
		<div class="home-deal">
			<div class="deal-card" data-reveal>
				<div>
					<span class="deal-card__kicker"><?php nexora_the_icon( 'alarm-ringing', 'sm' ); ?><?php echo esc_html( $nexora_home['flash_kicker'] ); ?></span>
					<h2 class="deal-card__title" id="sec-flash"><?php echo esc_html( $nexora_home['flash_title'] ); ?></h2>
					<p class="deal-card__text"><?php echo esc_html( $nexora_home['flash_headline'] ); ?><?php echo $nexora_home['flash_text'] ? ' — ' . esc_html( $nexora_home['flash_text'] ) : ''; ?></p>
				</div>
				<div class="countdown" data-countdown<?php echo $nexora_ts ? ' data-countdown-until="' . esc_attr( gmdate( 'c', $nexora_ts ) ) . '"' : ' data-countdown-midnight'; ?> aria-label="<?php esc_attr_e( 'Time remaining', 'nexora' ); ?>">
					<?php if ( $nexora_ts && $nexora_ts - time() > DAY_IN_SECONDS ) : ?>
						<span class="countdown__unit"><span class="countdown__value" data-cd="d">00</span><span class="countdown__label"><?php esc_html_e( 'days', 'nexora' ); ?></span></span>
						<span class="countdown__sep" aria-hidden="true">:</span>
					<?php endif; ?>
					<span class="countdown__unit"><span class="countdown__value" data-cd="h">00</span><span class="countdown__label"><?php esc_html_e( 'hours', 'nexora' ); ?></span></span>
					<span class="countdown__sep" aria-hidden="true">:</span>
					<span class="countdown__unit"><span class="countdown__value" data-cd="m">00</span><span class="countdown__label"><?php esc_html_e( 'minutes', 'nexora' ); ?></span></span>
					<span class="countdown__sep" aria-hidden="true">:</span>
					<span class="countdown__unit"><span class="countdown__value" data-cd="s">00</span><span class="countdown__label"><?php esc_html_e( 'seconds', 'nexora' ); ?></span></span>
				</div>
				<a class="btn btn--dark" href="<?php echo esc_url( $nexora_cta['url'] ?: nexora_shop_url( array( 'on_sale' => 1, 'orderby' => 'discount' ) ) ); ?>"><?php echo esc_html( $nexora_cta['text'] ?: __( 'All offers', 'nexora' ) ); ?><?php nexora_the_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ); ?></a>
			</div>
			<div class="product-carousel" data-reveal>
				<div class="flash-sale__head">
					<p class="flash-sale__sub"><?php echo esc_html( $nexora_home['flash_sub'] ); ?></p>
					<div class="carousel-nav carousel-nav--light"><button type="button" class="carousel-nav__btn" data-carousel-prev="flash" aria-label="<?php esc_attr_e( 'Previous', 'nexora' ); ?>"><?php nexora_the_icon( 'chevron-right', 'xs', 'icon--flip-ltr' ); ?></button><button type="button" class="carousel-nav__btn" data-carousel-next="flash" aria-label="<?php esc_attr_e( 'Next', 'nexora' ); ?>"><?php nexora_the_icon( 'chevron-left', 'xs', 'icon--flip-ltr' ); ?></button></div>
				</div>
				<div class="swiper" data-swiper="products" data-carousel-id="flash" data-slides-xl="3" data-slides-xxl="3">
					<div class="swiper-wrapper">
						<?php foreach ( $nexora_ids as $nexora_pid ) : ?>
							<div class="swiper-slide"><?php nexora_product_card( $nexora_pid, array( 'flash' => true ) ); ?></div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
