<?php
/**
 * Header search with optional category select + live suggestions.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_with_cat = ! empty( $args['category'] ) && class_exists( 'WooCommerce' );
$nexora_cats     = $nexora_with_cat ? nexora_top_categories( 12 ) : array();
$nexora_current  = get_query_var( 'product_cat' );
?>
<div class="search" data-search>
	<form class="search__form" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<input type="hidden" name="post_type" value="product">
		<?php endif; ?>
		<?php if ( $nexora_cats ) : ?>
			<label class="search__category">
				<span class="visually-hidden"><?php esc_html_e( 'Search in', 'nexora' ); ?></span>
				<select name="product_cat" data-search-cat>
					<option value=""><?php esc_html_e( 'All categories', 'nexora' ); ?></option>
					<?php foreach ( $nexora_cats as $nexora_cat ) : ?>
						<option value="<?php echo esc_attr( $nexora_cat->slug ); ?>"<?php selected( $nexora_current, $nexora_cat->slug ); ?>><?php echo esc_html( $nexora_cat->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		<?php endif; ?>
		<label for="site-search" class="visually-hidden"><?php esc_html_e( 'Search products', 'nexora' ); ?></label>
		<input id="site-search" class="search__input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search products, brands and categories…', 'nexora' ); ?>" autocomplete="off" spellcheck="false" aria-autocomplete="list" aria-controls="search-suggest" aria-expanded="false" data-search-input>
		<button type="submit" class="search__submit" aria-label="<?php esc_attr_e( 'Search', 'nexora' ); ?>"><?php nexora_the_icon( 'magnifier', 'sm' ); ?></button>
	</form>
	<div class="search__suggest" id="search-suggest" role="listbox" data-search-suggest></div>
</div>
