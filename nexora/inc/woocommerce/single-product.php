<?php
/**
 * Single product helpers: gallery, specs from attributes, tabs, sticky bar.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gallery image ids (featured first).
 *
 * @param WC_Product $product Product.
 * @return int[]
 */
function nexora_product_gallery_ids( $product ) {
	$ids = array();
	if ( $product->get_image_id() ) {
		$ids[] = (int) $product->get_image_id();
	}
	foreach ( $product->get_gallery_image_ids() as $gid ) {
		$ids[] = (int) $gid;
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Specifications grouped for the "Specs" tab: visible attributes.
 *
 * @param WC_Product $product Product.
 * @return array [ [ 'group' => string, 'rows' => [ [k,v] ] ] ]
 */
function nexora_product_specs( $product ) {
	$rows = array();
	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->get_visible() ) {
			continue;
		}
		$name = wc_attribute_label( $attribute->get_name(), $product );
		if ( $attribute->is_taxonomy() ) {
			$values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
		} else {
			$values = $attribute->get_options();
		}
		$rows[] = array( $name, implode( ', ', array_map( 'wp_strip_all_tags', (array) $values ) ) );
	}
	$groups = array();
	if ( $rows ) {
		$groups[] = array( 'group' => __( 'General specifications', 'nexora' ), 'rows' => $rows );
	}
	$extra = array();
	if ( $product->get_sku() ) {
		$extra[] = array( __( 'SKU', 'nexora' ), $product->get_sku() );
	}
	if ( $product->has_weight() ) {
		$extra[] = array( __( 'Weight', 'nexora' ), wc_format_weight( $product->get_weight() ) );
	}
	if ( $product->has_dimensions() ) {
		$extra[] = array( __( 'Dimensions', 'nexora' ), wc_format_dimensions( $product->get_dimensions( false ) ) );
	}
	if ( $extra ) {
		$groups[] = array( 'group' => __( 'Physical', 'nexora' ), 'rows' => $extra );
	}
	return apply_filters( 'nexora_product_specs', $groups, $product );
}

/**
 * Highlights: from ACF field "nexora_highlights" (one per line) or first excerpt list items.
 *
 * @param WC_Product $product Product.
 * @return string[]
 */
function nexora_product_highlights( $product ) {
	$raw = get_post_meta( $product->get_id(), 'nexora_highlights', true );
	if ( is_array( $raw ) ) {
		$raw = implode( "\n", array_map( static function ( $r ) { return is_array( $r ) ? reset( $r ) : $r; }, $raw ) );
	}
	$lines = array_filter( array_map( 'trim', explode( "\n", (string) $raw ) ) );
	if ( empty( $lines ) ) {
		// Try bullet list inside the short description.
		if ( preg_match_all( '#<li[^>]*>(.*?)</li>#is', $product->get_short_description(), $m ) ) {
			$lines = array_map( 'wp_strip_all_tags', $m[1] );
		}
	}
	return array_slice( array_values( $lines ), 0, 6 );
}

/**
 * Brand name + link (WooCommerce brands taxonomy or attribute).
 *
 * @param WC_Product $product Product.
 * @return array|null [name, url]
 */
function nexora_product_brand( $product ) {
	$tax = taxonomy_exists( 'product_brand' ) ? 'product_brand' : 'pa_' . sanitize_key( nexora_option( 'shop', 'filter_brand_attr', 'brand' ) );
	if ( ! taxonomy_exists( $tax ) ) {
		return null;
	}
	$terms = wc_get_product_terms( $product->get_id(), $tax, array( 'fields' => 'all' ) );
	if ( empty( $terms ) ) {
		return null;
	}
	$term = $terms[0];
	$url  = 'product_brand' === $tax ? get_term_link( $term ) : nexora_shop_url( array( 'brand' => $term->slug ) );
	return array( $term->name, is_wp_error( $url ) ? '' : $url );
}

/**
 * Rating distribution for the reviews tab.
 *
 * @param WC_Product $product Product.
 * @return array star => percent
 */
function nexora_rating_distribution( $product ) {
	$counts = $product->get_rating_counts();
	$total  = array_sum( $counts );
	$out    = array();
	for ( $s = 5; $s >= 1; $s-- ) {
		$out[ $s ] = $total ? (int) round( ( $counts[ $s ] ?? 0 ) / $total * 100 ) : 0;
	}
	return $out;
}

/**
 * Register the sticky mobile buy bar output.
 */
function nexora_sticky_buy_bar() {
	global $product;
	if ( ! is_product() || ! nexora_option( 'product', 'sticky_mobile' ) || ! $product || ! $product->is_purchasable() ) {
		return;
	}
	?>
	<div class="sticky-buy" data-sticky-buy aria-hidden="true">
		<div class="sticky-buy__price"><?php echo nexora_price_html( $product, 'sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<button type="button" class="btn btn--primary" data-sticky-add><?php nexora_the_icon( 'cart-add', 'sm' ); ?><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
	</div>
	<?php
}
add_action( 'wp_footer', 'nexora_sticky_buy_bar' );

/**
 * WooCommerce review form: reuse theme classes.
 */
add_filter(
	'woocommerce_product_review_comment_form_args',
	static function ( $args ) {
		$args['class_form']         = 'review-form';
		$args['class_submit']       = 'btn btn--primary';
		$args['title_reply_before'] = '<h3 class="review-form__title" id="reply-title">';
		$args['title_reply_after']  = '</h3>';
		$args['comment_field']      = '';
		if ( wc_review_ratings_enabled() ) {
			$stars = '';
			for ( $n = 5; $n >= 1; $n-- ) {
				/* translators: %s: number of stars */
				$label  = sprintf( _n( '%s star', '%s stars', $n, 'nexora' ), nexora_num( $n ) );
				$stars .= '<input type="radio" id="rate-' . $n . '" name="rating" value="' . $n . '"' . ( wc_review_ratings_required() ? ' required' : '' ) . '><label for="rate-' . $n . '" title="' . esc_attr( $label ) . '"><svg class="rating__star" aria-hidden="true" focusable="false"><use href="#i-star"></use></svg><span class="visually-hidden">' . esc_html( $label ) . '</span></label>';
			}
			$args['comment_field'] .= '<div class="form-group"><span class="form-label">' . esc_html__( 'Your rating', 'nexora' ) . ( wc_review_ratings_required() ? ' <span class="req">*</span>' : '' ) . '</span><div class="rating-input" role="radiogroup" aria-label="' . esc_attr__( 'Your rating', 'nexora' ) . '">' . $stars . '</div></div>';
		}
		$args['comment_field'] .= '<div class="form-group"><label class="form-label" for="comment">' . esc_html__( 'Your review', 'nexora' ) . ' <span class="req">*</span></label><textarea id="comment" class="form-control" name="comment" rows="5" required></textarea></div>';
		return $args;
	}
);

/**
 * Review item markup (used by wp_list_comments callback).
 *
 * @param WP_Comment $comment Comment.
 * @param array      $args    Args.
 * @param int        $depth   Depth.
 */
function nexora_review( $comment, $args, $depth ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$rating   = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
	$verified = wc_review_is_from_verified_owner( $comment->comment_ID );
	?>
	<article id="comment-<?php comment_ID(); ?>" <?php comment_class( 'review' ); ?>>
		<div>
			<div class="review__author">
				<?php nexora_avatar_initial( get_comment_author( $comment ), 'avatar--sm' ); ?>
				<div><div class="review__name"><?php comment_author( $comment ); ?></div><div class="review__date"><time datetime="<?php echo esc_attr( get_comment_date( 'c', $comment ) ); ?>"><?php echo esc_html( nexora_num( get_comment_date( wc_date_format(), $comment ) ) ); ?></time></div></div>
			</div>
			<?php if ( $verified ) : ?><div class="review-card__verified review__verified"><?php nexora_the_icon( 'checkmark-circle', 'xs' ); ?> <?php esc_html_e( 'Verified purchase', 'nexora' ); ?></div><?php endif; ?>
		</div>
		<div>
			<?php if ( $rating && wc_review_ratings_enabled() ) { echo nexora_rating_html( $rating, null, array( 'show_value' => false, 'show_count' => false ) ); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( '0' === $comment->comment_approved ) : ?><p class="small text-muted"><?php esc_html_e( 'Your review is awaiting approval.', 'nexora' ); ?></p><?php endif; ?>
			<div class="review__text"><?php comment_text( $comment ); ?></div>
			<?php
			$pros = get_comment_meta( $comment->comment_ID, 'nexora_pros', true );
			$cons = get_comment_meta( $comment->comment_ID, 'nexora_cons', true );
			if ( $pros ) {
				echo '<ul class="review__pros review__pros--plus">';
				foreach ( array_filter( array_map( 'trim', explode( "\n", $pros ) ) ) as $p ) {
					echo '<li>' . nexora_icon( 'plus-circle' ) . ' ' . esc_html( $p ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo '</ul>';
			}
			if ( $cons ) {
				echo '<ul class="review__pros review__pros--minus">';
				foreach ( array_filter( array_map( 'trim', explode( "\n", $cons ) ) ) as $c ) {
					echo '<li>' . nexora_icon( 'circle-minus' ) . ' ' . esc_html( $c ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo '</ul>';
			}
			?>
		</div>
	</article>
	<?php
}

/**
 * Pros / cons fields on the review form + save.
 */
add_filter(
	'woocommerce_product_review_comment_form_args',
	static function ( $args ) {
		$args['comment_field'] .= '<div class="form-cols"><div class="form-group"><label class="form-label" for="nexora_pros">' . esc_html__( 'Pros (one per line)', 'nexora' ) . '</label><textarea id="nexora_pros" class="form-control" name="nexora_pros" rows="2"></textarea></div><div class="form-group"><label class="form-label" for="nexora_cons">' . esc_html__( 'Cons (one per line)', 'nexora' ) . '</label><textarea id="nexora_cons" class="form-control" name="nexora_cons" rows="2"></textarea></div></div>';
		return $args;
	},
	20
);
add_action(
	'comment_post',
	static function ( $comment_id ) {
		if ( 'product' !== get_post_type( get_comment( $comment_id )->comment_post_ID ) ) {
			return;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- comment form is protected by WP core checks; values are plain text.
		foreach ( array( 'nexora_pros', 'nexora_cons' ) as $k ) {
			if ( ! empty( $_POST[ $k ] ) ) {
				add_comment_meta( $comment_id, $k, sanitize_textarea_field( wp_unslash( $_POST[ $k ] ) ), true );
			}
		}
		// phpcs:enable
	}
);

/**
 * "Buy now": add to cart then jump straight to checkout.
 */
add_filter(
	'woocommerce_add_to_cart_redirect',
	static function ( $url ) {
		if ( isset( $_REQUEST['nexora_buy_now'] ) && isset( $_REQUEST['add-to-cart'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wc_clear_notices();
			return wc_get_checkout_url();
		}
		return $url;
	}
);
