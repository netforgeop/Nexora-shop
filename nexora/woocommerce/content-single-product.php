<?php
/**
 * Single product content.
 *
 * @package Nexora
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}
$nexora_brand      = nexora_option( 'product', 'show_brand' ) ? nexora_product_brand( $product ) : null;
$nexora_highlights = nexora_product_highlights( $product );
$nexora_specs      = nexora_product_specs( $product );
$nexora_max        = $product->get_max_purchase_quantity();
$nexora_seller     = nexora_option( 'product', 'seller_enable' );
$nexora_seller_nm  = nexora_option( 'product', 'seller_name' ) ?: get_bloginfo( 'name' );
$nexora_assurance  = nexora_option( 'product', 'assurance_enable' ) ? array_filter( (array) nexora_option( 'product', 'assurance' ), static function ( $a ) { return ! empty( $a['title'] ); } ) : array();
$nexora_reviews_n  = (int) $product->get_review_count();
$nexora_sold       = (int) $product->get_total_sales();
$nexora_tabs       = array( 'description' => array( __( 'Description', 'nexora' ), 'file-empty' ) );
if ( nexora_option( 'product', 'tab_specs' ) && $nexora_specs ) {
	$nexora_tabs['specs'] = array( __( 'Specifications', 'nexora' ), 'list' );
}
if ( comments_open() ) {
	$nexora_tabs['reviews'] = array( __( 'Reviews', 'nexora' ), 'star' );
}
if ( nexora_option( 'product', 'tab_shipping' ) && nexora_option( 'product', 'tab_shipping_content' ) ) {
	$nexora_tabs['shipping'] = array( __( 'Shipping & returns', 'nexora' ), 'truck' );
}
$nexora_tabs = apply_filters( 'nexora_product_tabs', $nexora_tabs, $product );
?>
<section id="product-<?php the_ID(); ?>" <?php wc_product_class( 'section section--sm single-product-page', $product ); ?> data-product-page data-id="<?php echo (int) $product->get_id(); ?>" data-max="<?php echo (int) ( $nexora_max > 0 ? $nexora_max : 99 ); ?>">
	<div class="container">
		<?php woocommerce_output_all_notices(); ?>
		<div class="product-layout<?php echo nexora_option( 'product', 'sticky_aside' ) ? ' product-layout--aside' : ''; ?>">
			<?php wc_get_template( 'single-product/product-image.php' ); ?>

			<div class="product-info">
				<div class="product-info__brand">
					<?php if ( $nexora_brand ) : ?><span><?php esc_html_e( 'Brand:', 'nexora' ); ?> <a class="ltr" href="<?php echo esc_url( $nexora_brand[1] ); ?>"><?php echo esc_html( $nexora_brand[0] ); ?></a></span><?php endif; ?>
					<?php if ( $nexora_brand && nexora_option( 'product', 'show_sku' ) && $product->get_sku() ) : ?><span class="sep"></span><?php endif; ?>
					<?php if ( nexora_option( 'product', 'show_sku' ) && $product->get_sku() ) : ?><span><?php esc_html_e( 'SKU:', 'nexora' ); ?> <span class="ltr"><?php echo esc_html( $product->get_sku() ); ?></span></span><?php endif; ?>
				</div>
				<h1 class="product-info__title"><?php the_title(); ?></h1>
				<div class="product-info__meta">
					<?php if ( wc_review_ratings_enabled() && $nexora_reviews_n ) : ?>
						<?php echo nexora_rating_html( $product->get_average_rating(), $nexora_reviews_n, array( 'size' => 'lg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a href="#tab-reviews" data-tab-jump="reviews"><?php echo esc_html( sprintf( /* translators: %s: count */ _n( '(%s review)', '(%s reviews)', $nexora_reviews_n, 'nexora' ), nexora_num( $nexora_reviews_n ) ) ); ?></a>
					<?php elseif ( comments_open() ) : ?>
						<a href="#tab-reviews" data-tab-jump="reviews"><?php esc_html_e( 'Be the first to review', 'nexora' ); ?></a>
					<?php endif; ?>
					<?php if ( $nexora_sold > 0 && apply_filters( 'nexora_show_sold_count', true ) ) : ?>
						<span class="sep"></span><span><?php echo esc_html( sprintf( /* translators: %s: number */ __( '%s sold', 'nexora' ), nexora_num( $nexora_sold ) ) ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( $nexora_highlights ) : ?>
					<ul class="product-info__highlights" aria-label="<?php esc_attr_e( 'Highlights', 'nexora' ); ?>">
						<?php foreach ( $nexora_highlights as $nexora_h ) : ?><li><?php nexora_the_icon( 'check', 'xs' ); ?><span><?php echo esc_html( $nexora_h ); ?></span></li><?php endforeach; ?>
					</ul>
				<?php elseif ( $product->get_short_description() ) : ?>
					<div class="product-info__excerpt prose"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
				<?php endif; ?>

				<div class="buy-box">
					<div class="product-info__price">
						<?php echo nexora_price_html( $product, 'lg', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php
						if ( $product->is_on_sale() && $product->is_type( 'simple' ) ) :
							$nexora_save = (float) $product->get_regular_price() - (float) $product->get_sale_price();
							if ( $nexora_save > 0 ) :
								?>
								<span class="product-info__saving"><?php echo wp_kses_post( sprintf( /* translators: %s: amount */ __( 'You save %s', 'nexora' ), wc_price( $nexora_save ) ) ); ?></span>
							<?php endif; endif; ?>
					</div>
					<?php get_template_part( 'template-parts/products/stock', null, array( 'product' => $product ) ); ?>
					<?php
					/**
					 * Outputs the add-to-cart form (simple/variable/grouped/external) via the
					 * overridden templates in woocommerce/single-product/add-to-cart/.
					 */
					do_action( 'woocommerce_single_product_summary' );
					?>
					<div class="buy-box__secondary">
						<button type="button" data-action="wishlist" data-id="<?php echo (int) $product->get_id(); ?>" aria-pressed="false"><?php nexora_the_icon( 'heart', 'xs' ); ?><span><?php esc_html_e( 'Add to wishlist', 'nexora' ); ?></span></button>
						<button type="button" data-action="compare" data-id="<?php echo (int) $product->get_id(); ?>" aria-pressed="false"><?php nexora_the_icon( 'compare', 'xs' ); ?><span><?php esc_html_e( 'Add to compare', 'nexora' ); ?></span></button>
						<?php if ( nexora_option( 'product', 'show_share' ) ) : ?><button type="button" data-action="share" data-share-url="<?php the_permalink(); ?>" data-share-title="<?php the_title_attribute(); ?>"><?php nexora_the_icon( 'share2', 'xs' ); ?><span><?php esc_html_e( 'Share', 'nexora' ); ?></span></button><?php endif; ?>
					</div>
				</div>

				<?php if ( $nexora_assurance ) : ?>
					<div class="assurance">
						<?php foreach ( $nexora_assurance as $nexora_a ) : ?>
							<div class="assurance__item"><?php nexora_the_icon( $nexora_a['icon'] ?: 'shield-check' ); ?><div><strong><?php echo esc_html( $nexora_a['title'] ); ?></strong><?php echo esc_html( $nexora_a['text'] ); ?></div></div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( $nexora_seller ) : ?>
					<div class="seller-box">
						<div><div class="seller-box__name"><?php nexora_the_icon( 'store', 'sm' ); ?><?php echo esc_html( sprintf( /* translators: %s: seller */ __( 'Seller: %s', 'nexora' ), $nexora_seller_nm ) ); ?> <?php nexora_the_icon( 'checkmark-circle', 'xs' ); ?></div><div class="seller-box__meta"><?php echo esc_html( nexora_option( 'product', 'seller_meta' ) ); ?></div></div>
						<?php $nexora_about = get_page_by_path( 'about' ); ?>
						<?php if ( $nexora_about ) : ?><a class="btn btn--outline btn--sm" href="<?php echo esc_url( get_permalink( $nexora_about ) ); ?>"><?php esc_html_e( 'Details', 'nexora' ); ?></a><?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( is_active_sidebar( 'product-after' ) ) : ?><div class="product-info__widgets"><?php dynamic_sidebar( 'product-after' ); ?></div><?php endif; ?>
			</div>

			<?php if ( nexora_option( 'product', 'sticky_aside' ) ) : ?>
				<aside class="product-layout__aside" aria-label="<?php esc_attr_e( 'Quick buy', 'nexora' ); ?>">
					<div class="product-aside-box">
						<?php if ( $nexora_seller ) : ?><div class="text-muted small"><?php esc_html_e( 'Seller:', 'nexora' ); ?> <strong class="text-strong"><?php echo esc_html( $nexora_seller_nm ); ?></strong></div><?php endif; ?>
						<?php get_template_part( 'template-parts/products/stock', null, array( 'product' => $product ) ); ?>
						<?php echo nexora_price_html( $product, 'lg', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
							<button type="button" class="btn btn--primary btn--block" data-aside-add><?php nexora_the_icon( 'cart-add', 'sm' ); ?><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
						<?php endif; ?>
						<?php if ( $nexora_assurance ) : ?>
							<ul class="product-aside-box__list small text-muted">
								<?php foreach ( array_slice( $nexora_assurance, 0, 3 ) as $nexora_a ) : ?><li><?php nexora_the_icon( $nexora_a['icon'] ?: 'shield-check', 'xs' ); ?><?php echo esc_html( $nexora_a['title'] ); ?></li><?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</aside>
			<?php endif; ?>
		</div>

		<div class="product-details tabs" data-tabs>
			<div class="tabs__list" role="tablist" aria-label="<?php esc_attr_e( 'Product details', 'nexora' ); ?>">
				<?php $nexora_first = true; foreach ( $nexora_tabs as $nexora_key => $nexora_tab ) : ?>
					<button class="tabs__tab" role="tab" id="tab-btn-<?php echo esc_attr( $nexora_key ); ?>" aria-selected="<?php echo $nexora_first ? 'true' : 'false'; ?>" aria-controls="tab-<?php echo esc_attr( $nexora_key ); ?>" data-tab="<?php echo esc_attr( $nexora_key ); ?>" <?php echo $nexora_first ? '' : 'tabindex="-1"'; ?>><?php nexora_the_icon( $nexora_tab[1], 'xs' ); ?><?php echo esc_html( $nexora_tab[0] ); ?><?php if ( 'reviews' === $nexora_key && $nexora_reviews_n ) : ?><span class="badge badge--soft"><?php echo esc_html( nexora_num( $nexora_reviews_n ) ); ?></span><?php endif; ?></button>
					<?php $nexora_first = false; ?>
				<?php endforeach; ?>
			</div>
			<?php $nexora_first = true; foreach ( $nexora_tabs as $nexora_key => $nexora_tab ) : ?>
				<div class="tabs__panel" role="tabpanel" id="tab-<?php echo esc_attr( $nexora_key ); ?>" aria-labelledby="tab-btn-<?php echo esc_attr( $nexora_key ); ?>" <?php echo $nexora_first ? '' : 'hidden'; ?>>
					<h2 class="visually-hidden"><?php echo esc_html( $nexora_tab[0] ); ?></h2>
					<?php
					switch ( $nexora_key ) {
						case 'description':
							wc_get_template( 'single-product/tabs/description.php', array( 'highlights' => $nexora_highlights ) );
							break;
						case 'specs':
							wc_get_template( 'single-product/tabs/specs.php', array( 'specs' => $nexora_specs ) );
							break;
						case 'reviews':
							comments_template();
							break;
						case 'shipping':
							echo '<div class="prose">' . wp_kses_post( wpautop( nexora_option( 'product', 'tab_shipping_content' ) ) ) . '</div>';
							break;
						default:
							do_action( 'nexora_product_tab_' . $nexora_key, $product );
					}
					?>
				</div>
				<?php $nexora_first = false; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php
$nexora_rel_n = (int) nexora_option( 'product', 'related_count', 8 );
if ( $nexora_rel_n > 0 ) {
	$nexora_related = wc_get_related_products( $product->get_id(), $nexora_rel_n );
	if ( $nexora_related ) {
		echo '<section class="section bg-surface" aria-labelledby="sec-related"><div class="container">';
		nexora_section_head( array( 'title' => __( 'Related products', 'nexora' ), 'sub' => __( 'You might also like these', 'nexora' ), 'id' => 'sec-related', 'carousel' => 'related', 'reveal' => false ) );
		nexora_product_carousel( $nexora_related, 'related' );
		echo '</div></section>';
	}
}
$nexora_up_n = (int) nexora_option( 'product', 'upsells_count', 4 );
if ( $nexora_up_n > 0 && $product->get_upsell_ids() ) {
	echo '<section class="section" aria-labelledby="sec-upsells"><div class="container">';
	nexora_section_head( array( 'title' => __( 'Complete the set', 'nexora' ), 'id' => 'sec-upsells', 'carousel' => 'upsells', 'reveal' => false ) );
	nexora_product_carousel( array_slice( $product->get_upsell_ids(), 0, $nexora_up_n ), 'upsells' );
	echo '</div></section>';
}
do_action( 'woocommerce_after_single_product' );
