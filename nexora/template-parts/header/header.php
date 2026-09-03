<?php
/**
 * Full site header: announcement, top bar, main bar, navigation.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_header = nexora_options( 'header' );
$nexora_woo    = class_exists( 'WooCommerce' );
$nexora_phone  = nexora_option( 'general', 'phone' );
?>
<header class="site-header" id="site-header" data-header<?php echo $nexora_header['sticky'] ? '' : ' data-no-sticky'; ?>>

	<?php if ( $nexora_header['announcement_enable'] && $nexora_header['announcement_text'] ) : ?>
		<?php $nexora_ann_link = nexora_link_value( $nexora_header['announcement_link'] ); ?>
		<div class="announcement" id="announcement" data-announcement>
			<div class="container">
				<div class="announcement__inner">
					<?php nexora_the_icon( $nexora_header['announcement_icon'] ?: 'truck', 'sm' ); ?>
					<p><?php echo wp_kses( $nexora_header['announcement_text'], nexora_kses_inline() ); ?>
						<?php if ( $nexora_ann_link['url'] && $nexora_ann_link['text'] ) : ?>
							<a class="announcement__link" href="<?php echo esc_url( $nexora_ann_link['url'] ); ?>"<?php echo $nexora_ann_link['target'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $nexora_ann_link['text'] ); ?></a>
						<?php endif; ?>
					</p>
				</div>
				<button type="button" class="announcement__close" data-announcement-close aria-label="<?php esc_attr_e( 'Dismiss', 'nexora' ); ?>"><?php nexora_the_icon( 'cross', 'xs' ); ?></button>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $nexora_header['topbar_enable'] ) : ?>
		<div class="topbar">
			<div class="container">
				<div class="topbar__inner">
					<ul class="topbar__list">
						<?php if ( $nexora_phone ) : ?>
							<li class="topbar__item"><?php nexora_the_icon( 'headset', 'xs' ); ?><span><?php echo esc_html( $nexora_header['topbar_support_text'] ); ?></span> <a class="topbar__link ltr" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $nexora_phone ) ); ?>"><?php echo esc_html( nexora_num( $nexora_phone ) ); ?></a></li>
							<li class="topbar__sep" aria-hidden="true"></li>
						<?php endif; ?>
						<?php
						if ( has_nav_menu( 'topbar_start' ) ) {
							echo nexora_trim_topbar_sep( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								wp_nav_menu(
									array(
										'theme_location' => 'topbar_start',
										'container'      => false,
										'items_wrap'     => '%3$s',
										'depth'          => 1,
										'echo'           => false,
										'fallback_cb'    => false,
										'walker'         => new Nexora_Walker_Flat( 'topbar__item', 'topbar__link', true ),
									)
								)
							);
						}
						?>
					</ul>
					<ul class="topbar__list">
						<?php
						if ( has_nav_menu( 'topbar_end' ) ) {
							echo nexora_trim_topbar_sep( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								wp_nav_menu(
									array(
										'theme_location' => 'topbar_end',
										'container'      => false,
										'items_wrap'     => '%3$s',
										'depth'          => 1,
										'echo'           => false,
										'fallback_cb'    => false,
										'walker'         => new Nexora_Walker_Flat( 'topbar__item', 'topbar__link', true ),
									)
								)
							);
						}
						do_action( 'nexora_topbar_end' );
						?>
					</ul>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="header-main">
		<div class="container">
			<div class="header-main__inner">
				<button type="button" class="icon-btn header-menu-btn" data-drawer-open="drawer-menu" aria-label="<?php esc_attr_e( 'Open menu', 'nexora' ); ?>" aria-controls="drawer-menu" aria-expanded="false"><?php nexora_the_icon( 'menu' ); ?></button>

				<?php nexora_brand(); ?>

				<?php if ( $nexora_header['show_search'] ) : ?>
					<?php get_template_part( 'template-parts/header/search', null, array( 'category' => (bool) $nexora_header['search_category'] ) ); ?>
				<?php else : ?>
					<span></span>
				<?php endif; ?>

				<div class="header-actions">
					<?php $nexora_btn = nexora_link_value( $nexora_header['header_button'] ); ?>
					<?php if ( $nexora_btn['url'] && $nexora_btn['text'] ) : ?>
						<a class="btn btn--outline btn--sm header-action--cta" href="<?php echo esc_url( $nexora_btn['url'] ); ?>"><?php echo esc_html( $nexora_btn['text'] ); ?></a>
					<?php endif; ?>

					<?php if ( $nexora_header['show_account'] ) : ?>
						<?php
						$nexora_acc_url = $nexora_woo ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
						$nexora_logged  = is_user_logged_in();
						?>
						<a class="header-action header-action--account" href="<?php echo esc_url( $nexora_acc_url ); ?>" data-auth-link>
							<span class="header-action__icon"><?php nexora_the_icon( 'user' ); ?></span>
							<span class="header-action__text"><span class="header-action__label"><?php esc_html_e( 'My account', 'nexora' ); ?></span><span class="header-action__value" data-auth-label><?php echo $nexora_logged ? esc_html( wp_get_current_user()->display_name ) : esc_html__( 'Sign in / Register', 'nexora' ); ?></span></span>
						</a>
					<?php endif; ?>

					<?php if ( $nexora_woo && $nexora_header['show_compare'] ) : ?>
						<a class="header-action header-action--compare icon-btn" href="<?php echo esc_url( nexora_compare_url() ); ?>" aria-label="<?php esc_attr_e( 'Compare', 'nexora' ); ?>">
							<span class="header-action__icon"><?php nexora_the_icon( 'compare' ); ?><span class="icon-btn__badge" data-count="compare">0</span></span>
						</a>
					<?php endif; ?>

					<?php if ( $nexora_woo && $nexora_header['show_wishlist'] ) : ?>
						<a class="header-action header-action--wishlist icon-btn" href="<?php echo esc_url( nexora_wishlist_url() ); ?>" aria-label="<?php esc_attr_e( 'Wishlist', 'nexora' ); ?>">
							<span class="header-action__icon"><?php nexora_the_icon( 'heart' ); ?><span class="icon-btn__badge" data-count="wishlist">0</span></span>
						</a>
					<?php endif; ?>

					<?php if ( $nexora_woo && $nexora_header['show_cart'] ) : ?>
						<div class="header-action--cart-wrap" data-mini-cart-wrap>
							<?php $nexora_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
							<a class="header-action header-action--cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'nexora' ); ?>" data-mini-cart-toggle aria-haspopup="true" aria-expanded="false" aria-controls="mini-cart">
								<span class="header-action__icon"><?php nexora_the_icon( 'cart' ); ?><span class="icon-btn__badge" data-count="cart"><?php echo esc_html( nexora_num( $nexora_count ) ); ?></span></span>
								<span class="header-action__text"><span class="header-action__label"><?php esc_html_e( 'Cart', 'nexora' ); ?></span><span class="header-action__value" data-cart-total><?php echo $nexora_count && WC()->cart ? wp_kses_post( WC()->cart->get_cart_subtotal() ) : esc_html__( 'Empty', 'nexora' ); ?></span></span>
							</a>
							<?php get_template_part( 'template-parts/header/mini-cart' ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php if ( $nexora_header['show_nav'] ) : ?>
		<nav class="header-nav" aria-label="<?php esc_attr_e( 'Main menu', 'nexora' ); ?>">
			<div class="container">
				<div class="header-nav__inner">
					<?php if ( $nexora_woo && $nexora_header['show_cat_menu'] ) : ?>
						<?php get_template_part( 'template-parts/header/cat-menu', null, array( 'header' => $nexora_header ) ); ?>
					<?php endif; ?>

					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'nav',
							'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
							'depth'          => 2,
							'fallback_cb'    => 'nexora_primary_fallback',
							'walker'         => new Nexora_Walker_Nav(),
						)
					);
					?>

					<?php if ( ! empty( $nexora_header['nav_aside'] ) ) : ?>
						<div class="header-nav__aside">
							<?php foreach ( $nexora_header['nav_aside'] as $nexora_aside ) : ?>
								<a href="<?php echo esc_url( $nexora_aside['url'] ?: '#' ); ?>"><?php nexora_the_icon( $nexora_aside['icon'], 'sm' ); ?><span><?php echo esc_html( $nexora_aside['text'] ); ?></span></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</nav>
	<?php endif; ?>
</header>
