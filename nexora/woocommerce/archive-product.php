<?php
/**
 * Shop / category / search-in-shop archive.
 *
 * @package Nexora
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$nexora_sidebar = nexora_option( 'shop', 'sidebar', 'start' );
$nexora_term    = is_product_taxonomy() ? get_queried_object() : null;
$nexora_search  = is_search();
$nexora_title   = $nexora_search ? sprintf( /* translators: %s: query */ __( 'Search results for “%s”', 'nexora' ), get_search_query() ) : ( $nexora_term ? $nexora_term->name : nexora_option( 'shop', 'banner_title' ) );
$nexora_text    = $nexora_term ? wp_strip_all_tags( term_description( $nexora_term ) ) : ( $nexora_search ? '' : nexora_option( 'shop', 'banner_text' ) );
$nexora_banner  = nexora_option( 'shop', 'banner' ) && ! $nexora_search;
$nexora_img     = 0;
if ( $nexora_term && 'product_cat' === $nexora_term->taxonomy ) {
	$nexora_img = (int) get_term_meta( $nexora_term->term_id, 'thumbnail_id', true );
}
if ( ! $nexora_img ) {
	$nexora_img = (int) nexora_option( 'shop', 'banner_img' );
}
$nexora_view    = isset( $_GET['view'] ) && 'list' === $_GET['view'] ? 'list' : 'grid'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$nexora_orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$nexora_sorts   = nexora_wc_orderby( array() );
$nexora_cols    = (int) nexora_option( 'shop', 'columns', 4 );
$nexora_chips   = nexora_option( 'shop', 'chips' ) && ! $nexora_search ? nexora_top_categories( 12 ) : array();
wc_setup_loop(); // Populate loop props (total, per_page, current_page) before the toolbar renders.
?>
<?php nexora_breadcrumb(); ?>
<section class="section section--sm shop-archive" data-shop data-per-page="<?php echo (int) wc_get_loop_prop( 'per_page' ); ?>" data-view="<?php echo esc_attr( $nexora_view ); ?>">
	<div class="container">
		<?php if ( $nexora_banner ) : ?>
			<div class="shop-banner<?php echo $nexora_img ? '' : ' shop-banner--plain'; ?>">
				<?php if ( $nexora_img ) { echo wp_get_attachment_image( $nexora_img, 'nexora-hero', false, array( 'alt' => '', 'fetchpriority' => 'high', 'decoding' => 'async' ) ); } ?>
				<div class="shop-banner__body">
					<h1 class="shop-banner__title"><?php echo esc_html( $nexora_title ); ?></h1>
					<?php if ( $nexora_text ) : ?><p class="shop-banner__text"><?php echo esc_html( $nexora_text ); ?></p><?php endif; ?>
				</div>
			</div>
		<?php else : ?>
			<div class="page-head__inner page-head__inner--shop"><div><h1 class="h3"><?php echo esc_html( $nexora_title ); ?></h1><?php if ( $nexora_text ) : ?><p class="text-muted"><?php echo esc_html( $nexora_text ); ?></p><?php endif; ?></div></div>
		<?php endif; ?>

		<?php if ( $nexora_chips ) : ?>
			<div class="shop-categories" role="list" aria-label="<?php esc_attr_e( 'Categories', 'nexora' ); ?>">
				<a class="chip<?php echo $nexora_term ? '' : ' is-active'; ?>" role="listitem" href="<?php echo esc_url( nexora_shop_url() ); ?>"><?php nexora_the_icon( 'grid', 'xs' ); ?><?php esc_html_e( 'All', 'nexora' ); ?></a>
				<?php foreach ( $nexora_chips as $nexora_c ) : ?>
					<a class="chip<?php echo $nexora_term && $nexora_term->term_id === $nexora_c->term_id ? ' is-active' : ''; ?>" role="listitem" href="<?php echo esc_url( get_term_link( $nexora_c ) ); ?>"><?php nexora_the_icon( nexora_category_icon( $nexora_c ), 'xs' ); ?><?php echo esc_html( $nexora_c->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="with-sidebar<?php echo 'end' === $nexora_sidebar ? ' with-sidebar--end' : ''; ?><?php echo 'none' === $nexora_sidebar ? ' with-sidebar--none' : ''; ?>">
			<?php if ( 'none' !== $nexora_sidebar ) : ?>
				<aside class="with-sidebar__aside" data-filters-host aria-label="<?php esc_attr_e( 'Filters', 'nexora' ); ?>">
					<?php wc_get_template( 'loop/filters.php' ); ?>
					<?php if ( is_active_sidebar( 'sidebar-shop' ) ) { dynamic_sidebar( 'sidebar-shop' ); } ?>
				</aside>
			<?php endif; ?>
			<div>
				<?php woocommerce_output_all_notices(); ?>
				<div class="toolbar">
					<div class="toolbar__group">
						<button type="button" class="btn btn--outline btn--sm toolbar__filter-btn" data-drawer-open="drawer-filters" aria-controls="drawer-filters" aria-expanded="false"><?php nexora_the_icon( 'funnel', 'xs' ); ?><?php esc_html_e( 'Filters', 'nexora' ); ?><span class="badge badge--discount" data-filters-count hidden>0</span></button>
						<p class="toolbar__count" aria-live="polite" data-shop-count><?php woocommerce_result_count(); ?></p>
					</div>
					<div class="toolbar__group">
						<form class="toolbar__sort" method="get" data-sort-form>
							<label for="shop-sort" class="text-muted"><?php nexora_the_icon( 'sort-amount-desc', 'xs' ); ?> <?php esc_html_e( 'Sort by', 'nexora' ); ?></label>
							<?php if ( nexora_option( 'shop', 'sort_tabs' ) ) : ?>
								<div class="sort-tabs" role="group" aria-label="<?php esc_attr_e( 'Sort by', 'nexora' ); ?>">
									<?php foreach ( $nexora_sorts as $nexora_k => $nexora_l ) : ?>
										<a class="sort-tabs__btn<?php echo $nexora_orderby === $nexora_k ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'orderby' => $nexora_k, 'paged' => false ) ) ); ?>" data-sort="<?php echo esc_attr( $nexora_k ); ?>"><?php echo esc_html( $nexora_l ); ?></a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<select id="shop-sort" class="form-control" name="orderby" data-sort-select>
								<?php foreach ( $nexora_sorts as $nexora_k => $nexora_l ) : ?><option value="<?php echo esc_attr( $nexora_k ); ?>" <?php selected( $nexora_orderby, $nexora_k ); ?>><?php echo esc_html( $nexora_l ); ?></option><?php endforeach; ?>
							</select>
							<?php
							// Preserve other query args (filters, search) when the select submits.
							foreach ( $_GET as $nexora_gk => $nexora_gv ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended
								if ( in_array( $nexora_gk, array( 'orderby', 'paged' ), true ) || is_array( $nexora_gv ) ) { continue; }
								?>
								<input type="hidden" name="<?php echo esc_attr( sanitize_key( $nexora_gk ) ); ?>" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $nexora_gv ) ) ); ?>">
							<?php endforeach; ?>
						</form>
						<?php if ( nexora_option( 'shop', 'view_switch' ) ) : ?>
							<div class="view-switch" role="group" aria-label="<?php esc_attr_e( 'Layout', 'nexora' ); ?>">
								<button type="button" class="view-switch__btn" data-view="grid" aria-pressed="<?php echo 'grid' === $nexora_view ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e( 'Grid', 'nexora' ); ?>"><?php nexora_the_icon( 'grid', 'xs' ); ?></button>
								<button type="button" class="view-switch__btn" data-view="list" aria-pressed="<?php echo 'list' === $nexora_view ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e( 'List', 'nexora' ); ?>"><?php nexora_the_icon( 'list', 'xs' ); ?></button>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="active-filters" data-active-filters aria-live="polite"><?php wc_get_template( 'loop/active-filters.php' ); ?></div>
				<div class="shop-results" data-shop-results aria-live="polite" aria-busy="false">
					<div class="shop-results__loader" aria-hidden="true"><div class="spinner"></div></div>
					<?php if ( woocommerce_product_loop() ) : ?>
						<div class="product-grid product-grid--<?php echo (int) $nexora_cols; ?><?php echo 'list' === $nexora_view ? ' product-grid--list' : ''; ?>" data-shop-grid>
							<?php
							$nexora_i = 0;
							while ( have_posts() ) :
								the_post();
								nexora_product_card( wc_get_product( get_the_ID() ), array( 'view' => $nexora_view, 'priority' => $nexora_i < 4 && ! is_paged() ) );
								$nexora_i++;
							endwhile;
							?>
						</div>
					<?php else : ?>
						<div data-shop-empty>
							<?php nexora_empty_state( array( 'icon' => 'magnifier', 'title' => __( 'No products found', 'nexora' ), 'text' => __( 'Try removing some filters or searching for something else.', 'nexora' ), 'cta' => __( 'Clear filters', 'nexora' ), 'href' => $nexora_term ? get_term_link( $nexora_term ) : nexora_shop_url() ) ); ?>
						</div>
					<?php endif; ?>
				</div>
				<div data-shop-pagination><?php nexora_pagination(); ?></div>
				<?php
				do_action( 'woocommerce_after_shop_loop' );
				if ( $nexora_term && term_description( $nexora_term ) && is_paged() === false && ! $nexora_banner ) :
					?>
					<div class="prose shop-description"><?php echo wp_kses_post( term_description( $nexora_term ) ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<?php
get_footer( 'shop' );
