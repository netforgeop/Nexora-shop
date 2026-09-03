<?php
/**
 * 404 page.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();
$nexora_contact = get_page_by_path( 'contact' ) ? get_permalink( get_page_by_path( 'contact' ) ) : '';
?>
<section class="error-page container">
	<div class="error-page__code" aria-hidden="true">404</div>
	<h1 class="error-page__title"><?php esc_html_e( 'Page not found', 'nexora' ); ?></h1>
	<p class="error-page__text"><?php echo esc_html( nexora_option( 'pages', 'notfound_text' ) ); ?></p>
	<div class="search" data-search>
		<form class="search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label for="err-search" class="visually-hidden"><?php esc_html_e( 'Search', 'nexora' ); ?></label>
			<input id="err-search" class="search__input" type="search" name="s" placeholder="<?php esc_attr_e( 'Search products…', 'nexora' ); ?>" autocomplete="off" data-search-input aria-autocomplete="list" aria-controls="err-search-suggest" aria-expanded="false">
			<?php if ( class_exists( 'WooCommerce' ) ) : ?><input type="hidden" name="post_type" value="product"><?php endif; ?>
			<button type="submit" class="search__submit" aria-label="<?php esc_attr_e( 'Search', 'nexora' ); ?>"><?php nexora_the_icon( 'magnifier', 'sm' ); ?></button>
		</form>
		<div class="search__suggest" id="err-search-suggest" role="listbox" data-search-suggest></div>
	</div>
	<div class="error-page__links">
		<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php nexora_the_icon( 'home', 'xs' ); ?><?php esc_html_e( 'Back to home', 'nexora' ); ?></a>
		<?php if ( class_exists( 'WooCommerce' ) ) : ?><a class="btn btn--outline" href="<?php echo esc_url( nexora_shop_url() ); ?>"><?php nexora_the_icon( 'store', 'xs' ); ?><?php esc_html_e( 'Go to shop', 'nexora' ); ?></a><?php endif; ?>
		<?php if ( $nexora_contact ) : ?><a class="btn btn--ghost" href="<?php echo esc_url( $nexora_contact ); ?>"><?php nexora_the_icon( 'headset', 'xs' ); ?><?php esc_html_e( 'Contact support', 'nexora' ); ?></a><?php endif; ?>
	</div>
</section>
<?php
get_footer();
