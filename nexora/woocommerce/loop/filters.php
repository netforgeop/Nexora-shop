<?php
/**
 * Shop filters (GET-driven, progressively enhanced by JS).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended
$nexora_g        = wp_unslash( $_GET );
$nexora_term     = is_product_taxonomy() ? get_queried_object() : null;
$nexora_action   = $nexora_term ? get_term_link( $nexora_term ) : nexora_shop_url();
$nexora_sel_cats = isset( $nexora_g['product_cat'] ) ? array_map( 'sanitize_title', explode( ',', sanitize_text_field( $nexora_g['product_cat'] ) ) ) : array();
$nexora_sel_br   = isset( $nexora_g['brand'] ) ? array_map( 'sanitize_title', explode( ',', sanitize_text_field( $nexora_g['brand'] ) ) ) : array();
$nexora_sel_col  = isset( $nexora_g['color'] ) ? array_map( 'sanitize_title', explode( ',', sanitize_text_field( $nexora_g['color'] ) ) ) : array();
$nexora_rating   = isset( $nexora_g['rating'] ) ? (int) $nexora_g['rating'] : 0;
$nexora_step     = max( 1, (int) apply_filters( 'nexora_price_step', 'IRT' === get_woocommerce_currency() || 'IRR' === get_woocommerce_currency() ? 10000 : 1 ) );

// Price bounds (cached per archive).
$nexora_pkey   = 'nexora_price_bounds_' . ( $nexora_term ? $nexora_term->term_id : 0 );
$nexora_bounds = get_transient( $nexora_pkey );
if ( ! is_array( $nexora_bounds ) ) {
	global $wpdb;
	$nexora_where = '';
	if ( $nexora_term ) {
		$nexora_where = $wpdb->prepare( ' AND p.ID IN (SELECT object_id FROM ' . $wpdb->term_relationships . ' WHERE term_taxonomy_id = %d)', $nexora_term->term_taxonomy_id );
	}
	$nexora_row    = $wpdb->get_row( "SELECT MIN(min_price) AS min_p, MAX(max_price) AS max_p FROM {$wpdb->wc_product_meta_lookup} l INNER JOIN {$wpdb->posts} p ON p.ID = l.product_id WHERE p.post_status = 'publish'" . $nexora_where ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery
	$nexora_bounds = array( (float) ( $nexora_row->min_p ?? 0 ), (float) ( $nexora_row->max_p ?? 0 ) );
	set_transient( $nexora_pkey, $nexora_bounds, HOUR_IN_SECONDS );
}
$nexora_pmin = (int) floor( $nexora_bounds[0] / $nexora_step ) * $nexora_step;
$nexora_pmax = (int) ceil( $nexora_bounds[1] / $nexora_step ) * $nexora_step;
if ( $nexora_pmax <= $nexora_pmin ) {
	$nexora_pmax = $nexora_pmin + $nexora_step;
}
$nexora_cur_min = isset( $nexora_g['min_price'] ) ? max( $nexora_pmin, (int) $nexora_g['min_price'] ) : $nexora_pmin;
$nexora_cur_max = isset( $nexora_g['max_price'] ) ? min( $nexora_pmax, (int) $nexora_g['max_price'] ) : $nexora_pmax;

$nexora_cats = array();
if ( nexora_option( 'shop', 'filter_categories' ) ) {
	$nexora_cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => $nexora_term && 'product_cat' === $nexora_term->taxonomy ? $nexora_term->term_id : 0, 'number' => 30 ) );
	if ( is_wp_error( $nexora_cats ) || ( $nexora_term && empty( $nexora_cats ) ) ) {
		$nexora_cats = array();
	}
}
$nexora_brands = nexora_option( 'shop', 'filter_brand' ) ? nexora_brands( 40 ) : array();
$nexora_colors = array();
if ( nexora_option( 'shop', 'filter_color' ) ) {
	$nexora_ctax = 'pa_' . sanitize_key( nexora_option( 'shop', 'filter_color_attr', 'color' ) );
	if ( taxonomy_exists( $nexora_ctax ) ) {
		$nexora_colors = get_terms( array( 'taxonomy' => $nexora_ctax, 'hide_empty' => true, 'number' => 24 ) );
		$nexora_colors = is_wp_error( $nexora_colors ) ? array() : $nexora_colors;
	}
}
// phpcs:enable
?>
<form class="filters" data-filters method="get" action="<?php echo esc_url( $nexora_action ); ?>" novalidate>
	<?php if ( is_search() ) : ?><input type="hidden" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"><input type="hidden" name="post_type" value="product"><?php endif; ?>
	<?php if ( isset( $nexora_g['orderby'] ) ) : ?><input type="hidden" name="orderby" value="<?php echo esc_attr( sanitize_key( $nexora_g['orderby'] ) ); ?>"><?php endif; ?>
	<div class="filters__head">
		<h2><?php nexora_the_icon( 'funnel', 'sm' ); ?><?php esc_html_e( 'Filters', 'nexora' ); ?></h2>
		<a class="filters__clear" href="<?php echo esc_url( $nexora_action ); ?>" data-filters-clear><?php esc_html_e( 'Clear all', 'nexora' ); ?></a>
	</div>

	<?php if ( $nexora_cats ) : ?>
		<details class="filter-group" open>
			<summary class="filter-group__summary"><?php esc_html_e( 'Categories', 'nexora' ); ?><?php nexora_the_icon( 'chevron-down', 'xs' ); ?></summary>
			<div class="filter-group__body">
				<div class="filter-group__list scroll-thin" data-filter="product_cat">
					<?php foreach ( $nexora_cats as $nexora_c ) : ?>
						<label class="check"><input class="check__input" type="checkbox" name="product_cat[]" value="<?php echo esc_attr( $nexora_c->slug ); ?>" <?php checked( in_array( $nexora_c->slug, $nexora_sel_cats, true ) ); ?> data-href="<?php echo esc_url( get_term_link( $nexora_c ) ); ?>"><span class="check__box"></span><span class="check__label"><?php echo esc_html( $nexora_c->name ); ?></span><span class="check__count"><?php echo esc_html( nexora_num( $nexora_c->count ) ); ?></span></label>
					<?php endforeach; ?>
				</div>
			</div>
		</details>
	<?php endif; ?>

	<?php if ( nexora_option( 'shop', 'filter_price' ) && $nexora_pmax > $nexora_pmin ) : ?>
		<details class="filter-group" open>
			<summary class="filter-group__summary"><?php esc_html_e( 'Price', 'nexora' ); ?><?php nexora_the_icon( 'chevron-down', 'xs' ); ?></summary>
			<div class="filter-group__body" data-filter="price" data-min="<?php echo (int) $nexora_pmin; ?>" data-max="<?php echo (int) $nexora_pmax; ?>" data-currency="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>">
				<div class="range">
					<div class="range__track"><div class="range__fill" data-range-fill></div></div>
					<input type="range" name="min_price" min="<?php echo (int) $nexora_pmin; ?>" max="<?php echo (int) $nexora_pmax; ?>" value="<?php echo (int) $nexora_cur_min; ?>" step="<?php echo (int) $nexora_step; ?>" aria-label="<?php esc_attr_e( 'Minimum price', 'nexora' ); ?>" data-range-min>
					<input type="range" name="max_price" min="<?php echo (int) $nexora_pmin; ?>" max="<?php echo (int) $nexora_pmax; ?>" value="<?php echo (int) $nexora_cur_max; ?>" step="<?php echo (int) $nexora_step; ?>" aria-label="<?php esc_attr_e( 'Maximum price', 'nexora' ); ?>" data-range-max>
				</div>
				<div class="price-range__values"><span data-range-min-label><?php echo wp_kses_post( wc_price( $nexora_cur_min ) ); ?></span><span data-range-max-label><?php echo wp_kses_post( wc_price( $nexora_cur_max ) ); ?></span></div>
				<div class="price-inputs">
					<label class="visually-hidden" for="price-from"><?php esc_html_e( 'From', 'nexora' ); ?></label>
					<input id="price-from" class="form-control form-control--sm" type="text" inputmode="numeric" data-price-input="min" placeholder="<?php esc_attr_e( 'Min', 'nexora' ); ?>" value="<?php echo $nexora_cur_min !== $nexora_pmin ? (int) $nexora_cur_min : ''; ?>">
					<span class="price-inputs__sep"><?php esc_html_e( 'to', 'nexora' ); ?></span>
					<label class="visually-hidden" for="price-to"><?php esc_html_e( 'To', 'nexora' ); ?></label>
					<input id="price-to" class="form-control form-control--sm" type="text" inputmode="numeric" data-price-input="max" placeholder="<?php esc_attr_e( 'Max', 'nexora' ); ?>" value="<?php echo $nexora_cur_max !== $nexora_pmax ? (int) $nexora_cur_max : ''; ?>">
				</div>
			</div>
		</details>
	<?php endif; ?>

	<?php if ( $nexora_brands ) : ?>
		<details class="filter-group" open>
			<summary class="filter-group__summary"><?php esc_html_e( 'Brands', 'nexora' ); ?><?php nexora_the_icon( 'chevron-down', 'xs' ); ?></summary>
			<div class="filter-group__body">
				<?php if ( count( $nexora_brands ) > 6 ) : ?>
					<div class="input-icon filter-group__search"><?php nexora_the_icon( 'magnifier', 'xs' ); ?><input class="form-control form-control--sm" type="search" placeholder="<?php esc_attr_e( 'Search brand', 'nexora' ); ?>" aria-label="<?php esc_attr_e( 'Search brand', 'nexora' ); ?>" data-brand-search></div>
				<?php endif; ?>
				<div class="filter-group__list scroll-thin" data-filter="brand">
					<?php foreach ( $nexora_brands as $nexora_b ) : ?>
						<label class="check" data-brand-item="<?php echo esc_attr( $nexora_b->name ); ?>"><input class="check__input" type="checkbox" name="brand[]" value="<?php echo esc_attr( $nexora_b->slug ); ?>" <?php checked( in_array( $nexora_b->slug, $nexora_sel_br, true ) ); ?>><span class="check__box"></span><span class="check__label ltr"><?php echo esc_html( $nexora_b->name ); ?></span><span class="check__count"><?php echo esc_html( nexora_num( $nexora_b->count ) ); ?></span></label>
					<?php endforeach; ?>
				</div>
			</div>
		</details>
	<?php endif; ?>

	<?php if ( nexora_option( 'shop', 'filter_rating' ) && wc_review_ratings_enabled() ) : ?>
		<details class="filter-group" <?php echo $nexora_rating ? 'open' : ''; ?>>
			<summary class="filter-group__summary"><?php esc_html_e( 'Rating', 'nexora' ); ?><?php nexora_the_icon( 'chevron-down', 'xs' ); ?></summary>
			<div class="filter-group__body rating-filter" data-filter="rating">
				<?php foreach ( array( 4, 3, 2 ) as $nexora_r ) : ?>
					<label class="check check--radio"><input class="check__input" type="radio" name="rating" value="<?php echo (int) $nexora_r; ?>" <?php checked( $nexora_rating, $nexora_r ); ?>><span class="check__box"></span><span class="check__label"><?php echo nexora_rating_html( $nexora_r, null, array( 'show_value' => false, 'show_count' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'and up', 'nexora' ); ?></span></span></label>
				<?php endforeach; ?>
				<label class="check check--radio"><input class="check__input" type="radio" name="rating" value="" <?php checked( $nexora_rating, 0 ); ?>><span class="check__box"></span><span class="check__label"><?php esc_html_e( 'All', 'nexora' ); ?></span></label>
			</div>
		</details>
	<?php endif; ?>

	<?php if ( $nexora_colors ) : ?>
		<details class="filter-group" <?php echo $nexora_sel_col ? 'open' : ''; ?>>
			<summary class="filter-group__summary"><?php esc_html_e( 'Colours', 'nexora' ); ?><?php nexora_the_icon( 'chevron-down', 'xs' ); ?></summary>
			<div class="filter-group__body">
				<div class="color-filter" data-filter="color">
					<?php
					foreach ( $nexora_colors as $nexora_c ) :
						$nexora_hex = get_term_meta( $nexora_c->term_id, 'nexora_color', true ) ?: nexora_color_from_name( $nexora_c->slug );
						?>
						<label class="color-filter__item"><input type="checkbox" name="color[]" value="<?php echo esc_attr( $nexora_c->slug ); ?>" <?php checked( in_array( $nexora_c->slug, $nexora_sel_col, true ) ); ?>><span class="color-filter__swatch" style="background:<?php echo esc_attr( $nexora_hex ); ?>" title="<?php echo esc_attr( $nexora_c->name ); ?>"><?php nexora_the_icon( 'check', 'xs' ); ?></span><span class="visually-hidden"><?php echo esc_html( $nexora_c->name ); ?></span></label>
					<?php endforeach; ?>
				</div>
			</div>
		</details>
	<?php endif; ?>

	<?php if ( nexora_option( 'shop', 'filter_stock' ) ) : ?>
		<details class="filter-group" open>
			<summary class="filter-group__summary"><?php esc_html_e( 'Availability', 'nexora' ); ?><?php nexora_the_icon( 'chevron-down', 'xs' ); ?></summary>
			<div class="filter-group__body">
				<label class="switch"><input class="switch__input" type="checkbox" name="in_stock" value="1" <?php checked( ! empty( $nexora_g['in_stock'] ) ); ?>><span class="switch__track"></span><span><?php esc_html_e( 'Only in stock', 'nexora' ); ?></span></label>
				<label class="switch"><input class="switch__input" type="checkbox" name="on_sale" value="1" <?php checked( ! empty( $nexora_g['on_sale'] ) ); ?>><span class="switch__track"></span><span><?php esc_html_e( 'Only on sale', 'nexora' ); ?></span></label>
			</div>
		</details>
	<?php endif; ?>
	<noscript><button type="submit" class="btn btn--primary btn--block"><?php esc_html_e( 'Apply filters', 'nexora' ); ?></button></noscript>
</form>
