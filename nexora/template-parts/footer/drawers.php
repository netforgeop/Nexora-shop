<?php
/**
 * Off-canvas drawers, quick view modal, compare bar.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_woo   = class_exists( 'WooCommerce' );
$nexora_g     = nexora_options( 'general' );
$nexora_cats  = $nexora_woo ? nexora_top_categories( 12 ) : array();
$nexora_phone = $nexora_g['phone'];
?>
<div class="overlay" data-overlay hidden></div>

<aside class="drawer" id="drawer-menu" data-drawer aria-label="<?php esc_attr_e( 'Menu', 'nexora' ); ?>" aria-hidden="true" inert>
	<div class="drawer__header">
		<?php nexora_brand(); ?>
		<button type="button" class="icon-btn" data-drawer-close aria-label="<?php esc_attr_e( 'Close', 'nexora' ); ?>"><?php nexora_the_icon( 'cross' ); ?></button>
	</div>
	<div class="drawer__body scroll-thin">
		<?php if ( $nexora_cats ) : ?>
			<div class="mobile-nav__tabs" role="tablist">
				<button type="button" class="mobile-nav__tab" role="tab" id="mtab-menu" aria-selected="true" aria-controls="mpanel-menu" data-mobile-tab="menu"><?php esc_html_e( 'Menu', 'nexora' ); ?></button>
				<button type="button" class="mobile-nav__tab" role="tab" id="mtab-cats" aria-selected="false" aria-controls="mpanel-cats" data-mobile-tab="cats" tabindex="-1"><?php esc_html_e( 'Categories', 'nexora' ); ?></button>
			</div>
		<?php endif; ?>

		<div id="mpanel-menu" role="tabpanel" aria-labelledby="mtab-menu" data-mobile-panel="menu">
			<?php
			$nexora_mobile_loc = has_nav_menu( 'mobile' ) ? 'mobile' : 'primary';
			if ( has_nav_menu( $nexora_mobile_loc ) ) {
				wp_nav_menu(
					array(
						'theme_location' => $nexora_mobile_loc,
						'container'      => false,
						'items_wrap'     => '<ul class="mobile-nav__list">%3$s</ul>',
						'depth'          => 2,
						'fallback_cb'    => false,
						'walker'         => new Nexora_Walker_Mobile(),
					)
				);
			} else {
				echo '<ul class="mobile-nav__list"><li class="mobile-nav__item"><a class="mobile-nav__link" href="' . esc_url( home_url( '/' ) ) . '">' . nexora_icon( 'home', 'sm' ) . esc_html__( 'Home', 'nexora' ) . '</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				if ( $nexora_woo ) {
					echo '<li class="mobile-nav__item"><a class="mobile-nav__link" href="' . esc_url( nexora_shop_url() ) . '">' . nexora_icon( 'store', 'sm' ) . esc_html__( 'Shop', 'nexora' ) . '</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo '</ul>';
			}
			?>

			<?php if ( $nexora_woo ) : ?>
				<div class="mobile-nav__section"><?php esc_html_e( 'Quick access', 'nexora' ); ?></div>
				<ul class="mobile-nav__list">
					<li class="mobile-nav__item"><a class="mobile-nav__link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" data-auth-link><?php nexora_the_icon( 'user', 'sm' ); ?><span data-auth-label><?php echo is_user_logged_in() ? esc_html__( 'My account', 'nexora' ) : esc_html__( 'Sign in / Register', 'nexora' ); ?></span></a></li>
					<li class="mobile-nav__item"><a class="mobile-nav__link" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php nexora_the_icon( 'map-marker', 'sm' ); ?><?php esc_html_e( 'Track order', 'nexora' ); ?></a></li>
					<li class="mobile-nav__item"><a class="mobile-nav__link" href="<?php echo esc_url( nexora_wishlist_url() ); ?>"><?php nexora_the_icon( 'heart', 'sm' ); ?><?php esc_html_e( 'Wishlist', 'nexora' ); ?> <span class="badge badge--soft" data-count="wishlist">0</span></a></li>
					<li class="mobile-nav__item"><a class="mobile-nav__link" href="<?php echo esc_url( nexora_compare_url() ); ?>"><?php nexora_the_icon( 'compare', 'sm' ); ?><?php esc_html_e( 'Compare', 'nexora' ); ?> <span class="badge badge--soft" data-count="compare">0</span></a></li>
				</ul>
			<?php endif; ?>

			<?php if ( $nexora_phone || $nexora_g['email'] ) : ?>
				<div class="mobile-nav__section"><?php esc_html_e( 'Contact us', 'nexora' ); ?></div>
				<div class="mobile-nav__contact">
					<?php if ( $nexora_phone ) : ?>
						<a class="ltr" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $nexora_phone ) ); ?>" style="direction:ltr"><?php nexora_the_icon( 'telephone', 'xs' ); ?><?php echo esc_html( nexora_num( $nexora_phone ) ); ?></a>
					<?php endif; ?>
					<?php if ( $nexora_g['email'] ) : ?>
						<a href="mailto:<?php echo esc_attr( $nexora_g['email'] ); ?>"><?php nexora_the_icon( 'envelope', 'xs' ); ?><span class="ltr"><?php echo esc_html( $nexora_g['email'] ); ?></span></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $nexora_cats ) : ?>
			<div id="mpanel-cats" role="tabpanel" aria-labelledby="mtab-cats" data-mobile-panel="cats" hidden>
				<ul class="mobile-nav__list">
					<?php
					foreach ( $nexora_cats as $nexora_cat ) :
						$nexora_kids = nexora_category_children( $nexora_cat, 10 );
						?>
						<li class="mobile-nav__item">
							<div class="mobile-nav__link">
								<a href="<?php echo esc_url( get_term_link( $nexora_cat ) ); ?>" class="mobile-nav__link-main"><?php nexora_the_icon( nexora_category_icon( $nexora_cat ), 'sm' ); ?><?php echo esc_html( $nexora_cat->name ); ?></a>
								<?php if ( $nexora_kids ) : ?>
									<button type="button" class="mobile-nav__toggle" aria-expanded="false" aria-controls="msub-<?php echo esc_attr( $nexora_cat->slug ); ?>" aria-label="<?php echo esc_attr( $nexora_cat->name ); ?>" data-mobile-sub-toggle><?php nexora_the_icon( 'chevron-down', 'xs' ); ?></button>
								<?php endif; ?>
							</div>
							<?php if ( $nexora_kids ) : ?>
								<div class="mobile-nav__sub" id="msub-<?php echo esc_attr( $nexora_cat->slug ); ?>">
									<?php foreach ( $nexora_kids as $nexora_kid ) : ?>
										<a href="<?php echo esc_url( get_term_link( $nexora_kid ) ); ?>"><?php echo esc_html( $nexora_kid->name ); ?></a>
									<?php endforeach; ?>
									<a href="<?php echo esc_url( get_term_link( $nexora_cat ) ); ?>" class="fw-medium"><?php echo esc_html( sprintf( /* translators: %s: category */ __( 'View all in %s', 'nexora' ), $nexora_cat->name ) ); ?></a>
								</div>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
	<?php if ( $nexora_woo ) : ?>
		<div class="drawer__footer">
			<a class="btn btn--primary btn--block" href="<?php echo esc_url( nexora_shop_url() ); ?>"><?php nexora_the_icon( 'store', 'sm' ); ?><?php esc_html_e( 'Go to shop', 'nexora' ); ?></a>
		</div>
	<?php endif; ?>
</aside>

<?php if ( $nexora_woo ) : ?>
	<aside class="drawer drawer--end" id="drawer-filters" data-drawer aria-label="<?php esc_attr_e( 'Filters', 'nexora' ); ?>" aria-hidden="true" inert>
		<div class="drawer__header">
			<span class="drawer__title"><?php nexora_the_icon( 'funnel', 'sm' ); ?><?php esc_html_e( 'Filters', 'nexora' ); ?></span>
			<button type="button" class="icon-btn" data-drawer-close aria-label="<?php esc_attr_e( 'Close', 'nexora' ); ?>"><?php nexora_the_icon( 'cross' ); ?></button>
		</div>
		<div class="drawer__body scroll-thin" data-filters-drawer-body></div>
		<div class="drawer__footer" style="grid-template-columns:1fr 1fr">
			<button type="button" class="btn btn--outline" data-filters-clear><?php esc_html_e( 'Clear filters', 'nexora' ); ?></button>
			<button type="button" class="btn btn--primary" data-drawer-close><span data-filters-apply-label><?php esc_html_e( 'Apply filters', 'nexora' ); ?></span></button>
		</div>
	</aside>

	<dialog class="modal" id="quick-view" data-quick-view aria-label="<?php esc_attr_e( 'Quick view', 'nexora' ); ?>">
		<button type="button" class="icon-btn icon-btn--light modal__close" data-modal-close aria-label="<?php esc_attr_e( 'Close', 'nexora' ); ?>"><?php nexora_the_icon( 'cross' ); ?></button>
		<div class="modal__body scroll-thin" data-quick-view-body></div>
	</dialog>

	<div class="compare-bar" data-compare-bar aria-live="polite">
		<span class="compare-bar__thumbs" data-compare-thumbs></span>
		<span data-compare-label></span>
		<a class="btn btn--primary btn--sm btn--pill" href="<?php echo esc_url( nexora_compare_url() ); ?>"><?php esc_html_e( 'Compare now', 'nexora' ); ?></a>
		<button type="button" class="icon-btn icon-btn--circle" data-compare-clear aria-label="<?php esc_attr_e( 'Clear compare list', 'nexora' ); ?>" style="color:var(--color-gray-400)"><?php nexora_the_icon( 'cross', 'xs' ); ?></button>
	</div>
<?php endif; ?>
<?php do_action( 'nexora_after_drawers' ); ?>
