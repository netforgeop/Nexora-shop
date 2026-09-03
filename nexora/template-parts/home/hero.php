<?php
/**
 * Homepage hero slider + side tiles.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_home   = $args['home'];
$nexora_slides = array_values( array_filter( (array) $nexora_home['hero_slides'], static function ( $s ) { return ! empty( $s['image'] ) || ! empty( $s['title'] ); } ) );
$nexora_tiles  = array_slice( array_values( array_filter( (array) $nexora_home['hero_tiles'], static function ( $t ) { return ! empty( $t['image'] ); } ) ), 0, 2 );
if ( ! $nexora_slides ) {
	return;
}
?>
<section class="home-hero" aria-label="<?php echo esc_attr( $nexora_slides[0]['title'] ?: __( 'Highlights', 'nexora' ) ); ?>">
	<div class="container">
		<div class="hero-grid<?php echo $nexora_tiles ? '' : ' hero-grid--full'; ?>">
			<div class="hero swiper" data-swiper="hero" data-autoplay="<?php echo esc_attr( (int) $nexora_home['hero_autoplay'] * 1000 ); ?>">
				<div class="swiper-wrapper">
					<?php
					foreach ( $nexora_slides as $nexora_i => $nexora_slide ) :
						$nexora_b1 = nexora_link_value( $nexora_slide['button1'] );
						$nexora_b2 = nexora_link_value( $nexora_slide['button2'] );
						?>
						<div class="swiper-slide">
							<article class="hero__slide">
								<div class="hero__media">
									<?php
									if ( $nexora_slide['image'] ) {
										echo wp_get_attachment_image(
											(int) $nexora_slide['image'],
											'nexora-hero',
											false,
											array(
												'alt'           => '',
												'sizes'         => '(min-width: 1200px) 880px, 100vw',
												'fetchpriority' => 0 === $nexora_i ? 'high' : 'auto',
												'loading'       => 0 === $nexora_i ? 'eager' : 'lazy',
												'decoding'      => 'async',
											)
										);
									}
									?>
								</div>
								<div class="container"><div class="hero__content">
									<?php if ( $nexora_slide['eyebrow'] ) : ?><span class="hero__eyebrow"><?php echo esc_html( $nexora_slide['eyebrow'] ); ?></span><?php endif; ?>
									<?php if ( $nexora_slide['title'] ) : ?><h2 class="hero__title"><?php echo wp_kses( $nexora_slide['title'], nexora_kses_inline() ); ?></h2><?php endif; ?>
									<?php if ( $nexora_slide['text'] ) : ?><p class="hero__text"><?php echo esc_html( $nexora_slide['text'] ); ?></p><?php endif; ?>
									<?php if ( $nexora_b1['url'] || $nexora_b2['url'] ) : ?>
										<div class="hero__cta">
											<?php if ( $nexora_b1['url'] && $nexora_b1['text'] ) : ?><a class="btn btn--primary btn--lg" href="<?php echo esc_url( $nexora_b1['url'] ); ?>"><?php echo esc_html( $nexora_b1['text'] ); ?><?php nexora_the_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ); ?></a><?php endif; ?>
											<?php if ( $nexora_b2['url'] && $nexora_b2['text'] ) : ?><a class="btn btn--outline-light btn--lg" href="<?php echo esc_url( $nexora_b2['url'] ); ?>"><?php echo esc_html( $nexora_b2['text'] ); ?></a><?php endif; ?>
										</div>
									<?php endif; ?>
								</div></div>
							</article>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if ( count( $nexora_slides ) > 1 ) : ?>
					<div class="swiper-pagination"></div>
					<button type="button" class="hero__nav hero__nav--prev" data-swiper-prev aria-label="<?php esc_attr_e( 'Previous slide', 'nexora' ); ?>"><?php nexora_the_icon( 'chevron-right', 'sm', 'icon--flip-ltr' ); ?></button>
					<button type="button" class="hero__nav hero__nav--next" data-swiper-next aria-label="<?php esc_attr_e( 'Next slide', 'nexora' ); ?>"><?php nexora_the_icon( 'chevron-left', 'sm', 'icon--flip-ltr' ); ?></button>
				<?php endif; ?>
			</div>

			<?php if ( $nexora_tiles ) : ?>
				<div class="hero-grid__aside">
					<?php
					foreach ( $nexora_tiles as $nexora_tile ) :
						$nexora_tl = nexora_link_value( $nexora_tile['link'] );
						?>
						<a class="hero-tile<?php echo ! empty( $nexora_tile['dark'] ) ? ' hero-tile--dark' : ''; ?>" href="<?php echo esc_url( $nexora_tl['url'] ?: nexora_shop_url() ); ?>">
							<?php echo wp_get_attachment_image( (int) $nexora_tile['image'], 'nexora-banner', false, array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
							<span class="hero-tile__body">
								<?php if ( $nexora_tile['kicker'] ) : ?><span class="hero-tile__kicker"><?php echo esc_html( $nexora_tile['kicker'] ); ?></span><?php endif; ?>
								<?php if ( $nexora_tile['title'] ) : ?><span class="hero-tile__title"><?php echo esc_html( $nexora_tile['title'] ); ?></span><?php endif; ?>
								<span class="hero-tile__link"><?php echo esc_html( $nexora_tl['text'] ?: __( 'Shop now', 'nexora' ) ); ?> <?php nexora_the_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
