<?php
/**
 * Main footer.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_f     = nexora_options( 'footer' );
$nexora_g     = nexora_options( 'general' );
$nexora_woo   = class_exists( 'WooCommerce' );
$nexora_phone = $nexora_g['phone'];
?>
<footer class="site-footer" id="site-footer">
	<?php if ( $nexora_f['features_enable'] && ! empty( $nexora_f['features'] ) ) : ?>
		<div class="footer-features">
			<div class="container">
				<div class="footer-features__grid">
					<?php foreach ( $nexora_f['features'] as $nexora_feat ) : ?>
						<div class="footer-feature"><span class="footer-feature__icon"><?php nexora_the_icon( $nexora_feat['icon'] ); ?></span><div><div class="footer-feature__title"><?php echo esc_html( $nexora_feat['title'] ); ?></div><div class="footer-feature__text"><?php echo esc_html( $nexora_feat['text'] ); ?></div></div></div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="footer-main">
		<div class="container">
			<div class="footer-grid">
				<div class="footer-col">
					<?php nexora_brand( array( 'light' => true ) ); ?>
					<?php if ( $nexora_f['about'] ) : ?>
						<p class="footer-about"><?php echo esc_html( $nexora_f['about'] ); ?></p>
					<?php endif; ?>
					<?php if ( $nexora_f['show_contact'] ) : ?>
						<div class="footer-contact">
							<?php if ( $nexora_g['address'] ) : ?>
								<div class="footer-contact__item"><?php nexora_the_icon( 'map-marker', 'sm' ); ?><div><span class="footer-contact__label"><?php esc_html_e( 'Address', 'nexora' ); ?></span><?php echo esc_html( $nexora_g['address'] ); ?></div></div>
							<?php endif; ?>
							<?php if ( $nexora_phone ) : ?>
								<div class="footer-contact__item"><?php nexora_the_icon( 'telephone', 'sm' ); ?><div><span class="footer-contact__label"><?php esc_html_e( 'Phone', 'nexora' ); ?></span><a class="ltr" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $nexora_phone ) ); ?>"><?php echo esc_html( nexora_num( $nexora_phone ) ); ?></a><?php if ( $nexora_g['hours'] ) : ?> <span class="text-muted small">(<?php echo esc_html( $nexora_g['hours'] ); ?>)</span><?php endif; ?></div></div>
							<?php endif; ?>
							<?php if ( $nexora_g['email'] ) : ?>
								<div class="footer-contact__item"><?php nexora_the_icon( 'envelope', 'sm' ); ?><div><span class="footer-contact__label"><?php esc_html_e( 'Email', 'nexora' ); ?></span><a class="ltr" href="mailto:<?php echo esc_attr( $nexora_g['email'] ); ?>"><?php echo esc_html( $nexora_g['email'] ); ?></a></div></div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php
				foreach ( array( 'footer_1' => $nexora_f['col1_title'], 'footer_2' => $nexora_f['col2_title'] ) as $nexora_loc => $nexora_title ) :
					if ( ! has_nav_menu( $nexora_loc ) ) {
						continue;
					}
					?>
					<nav class="footer-col" aria-labelledby="<?php echo esc_attr( $nexora_loc ); ?>-title">
						<h2 class="footer-col__title" id="<?php echo esc_attr( $nexora_loc ); ?>-title"><?php echo esc_html( $nexora_title ); ?></h2>
						<?php
						wp_nav_menu(
							array(
								'theme_location' => $nexora_loc,
								'container'      => false,
								'items_wrap'     => '<ul class="footer-links">%3$s</ul>',
								'depth'          => 1,
								'fallback_cb'    => false,
								'walker'         => new Nexora_Walker_Flat(),
							)
						);
						?>
					</nav>
				<?php endforeach; ?>

				<?php
				if ( $nexora_woo && $nexora_f['cats_enable'] ) :
					$nexora_fcats = nexora_top_categories( (int) $nexora_f['cats_count'] );
					if ( $nexora_fcats ) :
						?>
						<nav class="footer-col" aria-labelledby="footer-cats">
							<h2 class="footer-col__title" id="footer-cats"><?php echo esc_html( $nexora_f['cats_title'] ); ?></h2>
							<ul class="footer-links">
								<?php foreach ( $nexora_fcats as $nexora_fcat ) : ?>
									<li><a href="<?php echo esc_url( get_term_link( $nexora_fcat ) ); ?>"><?php echo esc_html( $nexora_fcat->name ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</nav>
						<?php
					endif;
				endif;
				?>

				<div class="footer-col">
					<?php if ( $nexora_f['newsletter_enable'] ) : ?>
						<h2 class="footer-col__title"><?php echo esc_html( $nexora_f['newsletter_title'] ); ?></h2>
						<?php get_template_part( 'template-parts/components/newsletter-form', null, array( 'id' => 'footer-newsletter-email', 'text' => $nexora_f['newsletter_text'], 'class' => 'footer-newsletter', 'source' => 'footer' ) ); ?>
					<?php endif; ?>
					<?php if ( $nexora_f['apps_enable'] && ( $nexora_f['appstore_url'] || $nexora_f['googleplay_url'] ) ) : ?>
						<div class="footer-apps">
							<?php if ( $nexora_f['appstore_url'] ) : ?>
								<a class="app-badge" href="<?php echo esc_url( $nexora_f['appstore_url'] ); ?>" rel="noopener" target="_blank"><?php nexora_the_icon( 'apple', 'sm' ); ?><span><small><?php esc_html_e( 'Download on the', 'nexora' ); ?></small>App Store</span></a>
							<?php endif; ?>
							<?php if ( $nexora_f['googleplay_url'] ) : ?>
								<a class="app-badge" href="<?php echo esc_url( $nexora_f['googleplay_url'] ); ?>" rel="noopener" target="_blank"><?php nexora_the_icon( 'smartphone', 'sm' ); ?><span><small><?php esc_html_e( 'Get it on', 'nexora' ); ?></small>Google Play</span></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php if ( $nexora_f['trust_html'] ) : ?>
						<div class="footer-trust" aria-label="<?php esc_attr_e( 'Trust badges', 'nexora' ); ?>"><?php echo wp_kses( $nexora_f['trust_html'], nexora_kses_badges() ); ?></div>
					<?php endif; ?>
					<?php if ( is_active_sidebar( 'footer-extra' ) ) : ?>
						<div class="footer-widgets"><?php dynamic_sidebar( 'footer-extra' ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="footer-bottom">
		<div class="container">
			<div class="footer-bottom__inner">
				<p><?php nexora_copyright(); ?></p>
				<?php
				if ( has_nav_menu( 'footer_bottom' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'footer_bottom',
							'container'      => false,
							'items_wrap'     => '<ul class="footer-bottom__links">%3$s</ul>',
							'depth'          => 1,
							'fallback_cb'    => false,
							'walker'         => new Nexora_Walker_Flat(),
						)
					);
				}
				$nexora_social = $nexora_f['show_social'] ? nexora_social_links() : array();
				if ( $nexora_social ) :
					?>
					<ul class="social" aria-label="<?php esc_attr_e( 'Social networks', 'nexora' ); ?>">
						<?php foreach ( $nexora_social as $nexora_sid => $nexora_s ) : ?>
							<li><a class="social__link" href="<?php echo esc_url( $nexora_s[1] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $nexora_s[0] ); ?>"><?php echo nexora_svg( $nexora_sid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( $nexora_f['show_payments'] && ! empty( $nexora_f['payments'] ) ) : ?>
					<div class="payments" aria-label="<?php esc_attr_e( 'Payment methods', 'nexora' ); ?>">
						<?php foreach ( (array) $nexora_f['payments'] as $nexora_pay ) { echo nexora_svg( $nexora_pay ); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</footer>
