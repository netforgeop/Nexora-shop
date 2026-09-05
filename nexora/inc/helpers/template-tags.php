<?php
/**
 * Reusable template tags / components.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the current locale RTL Persian/Arabic?
 *
 * @return bool
 */
function nexora_is_fa() {
	return in_array( substr( get_locale(), 0, 2 ), array( 'fa', 'ar', 'ur' ), true );
}

/**
 * Convert Latin digits to Persian digits when the site language is Persian.
 *
 * @param string|int|float $value Value.
 * @return string
 */
function nexora_num( $value ) {
	$value = (string) $value;
	if ( ! nexora_is_fa() ) {
		return $value;
	}
	$map = array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' );
	if ( false === strpos( $value, '<' ) && false === strpos( $value, '&' ) ) {
		return strtr( $value, $map );
	}
	// HTML: leave tags and character entities (e.g. &#x62A;) untouched.
	return preg_replace_callback(
		'/(<[^>]*>|&#?[a-zA-Z0-9]+;)|([0-9]+)/',
		static function ( $m ) use ( $map ) {
			return isset( $m[2] ) && '' !== $m[2] ? strtr( $m[2], $map ) : $m[1];
		},
		$value
	);
}

/**
 * Shop URL (WooCommerce) with graceful fallback.
 *
 * @param array $query Query args.
 * @return string
 */
function nexora_shop_url( array $query = array() ) {
	$url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	return $query ? add_query_arg( array_map( 'rawurlencode', $query ), $url ) : $url;
}

/**
 * Page URL helper for WooCommerce pages, or home.
 *
 * @param string $page cart|checkout|myaccount|shop.
 * @return string
 */
function nexora_wc_url( $page ) {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( $page );
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/' );
}

/**
 * Wishlist page URL (theme-provided page, created on activation).
 *
 * @return string
 */
function nexora_wishlist_url() {
	$id = (int) nexora_get_state( 'page_wishlist' );
	return $id ? get_permalink( $id ) : home_url( '/?nexora_wishlist=1' );
}

/**
 * Compare page URL.
 *
 * @return string
 */
function nexora_compare_url() {
	$id = (int) nexora_get_state( 'page_compare' );
	return $id ? get_permalink( $id ) : home_url( '/?nexora_compare=1' );
}

/**
 * Brand / logo block.
 *
 * @param array $args light => bool, class => string.
 */
function nexora_brand( array $args = array() ) {
	$args  = wp_parse_args( $args, array( 'light' => false, 'class' => '' ) );
	$name  = get_bloginfo( 'name' );
	$tag   = nexora_option( 'general', 'tagline' );
	$text  = nexora_option( 'general', 'logo_text' ) ?: $name;
	$class = 'brand' . ( $args['light'] ? ' brand--light' : '' ) . ( $args['class'] ? ' ' . esc_attr( $args['class'] ) : '' );

	$logo_id = $args['light'] ? (int) nexora_option( 'general', 'logo_light' ) : 0;
	if ( ! $logo_id ) {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
	}

	echo '<a class="' . $class . '" href="' . esc_url( home_url( '/' ) ) . '" rel="home" aria-label="' . esc_attr( $name . ( $tag ? ' – ' . $tag : '' ) ) . '">';
	if ( $logo_id ) {
		echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'class' => 'brand__img', 'loading' => 'eager' ) );
	} else {
		echo '<span class="brand__mark">' . nexora_svg( 'logo' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( nexora_option( 'general', 'show_brand_text' ) || ! $logo_id ) {
		echo '<span class="brand__text"><span>' . esc_html( $text ) . '</span>';
		if ( $tag ) {
			echo '<span class="brand__tag">' . esc_html( $tag ) . '</span>';
		}
		echo '</span>';
	}
	echo '</a>';
}

/**
 * Section heading component.
 *
 * @param array $args title, sub, id, link, link_text, center, carousel (id for nav buttons), reveal.
 */
function nexora_section_head( array $args ) {
	$args = wp_parse_args(
		$args,
		array( 'title' => '', 'sub' => '', 'id' => '', 'link' => '', 'link_text' => __( 'View all', 'nexora' ), 'center' => false, 'carousel' => '', 'reveal' => true, 'tag' => 'h2' )
	);
	$tag  = in_array( $args['tag'], array( 'h1', 'h2', 'h3' ), true ) ? $args['tag'] : 'h2';
	echo '<div class="section-head' . ( $args['center'] ? ' section-head--center' : '' ) . '"' . ( $args['reveal'] ? ' data-reveal' : '' ) . '>';
	echo '<div><' . $tag . ' class="section-head__title"' . ( $args['id'] ? ' id="' . esc_attr( $args['id'] ) . '"' : '' ) . '>' . esc_html( $args['title'] ) . '</' . $tag . '>';
	if ( $args['sub'] ) {
		echo '<p class="section-head__sub">' . esc_html( $args['sub'] ) . '</p>';
	}
	echo '</div>';
	if ( $args['link'] || $args['carousel'] ) {
		echo '<div class="section-head__aside">';
		if ( $args['link'] ) {
			echo '<a class="link--arrow" href="' . esc_url( $args['link'] ) . '">' . esc_html( $args['link_text'] ) . nexora_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		if ( $args['carousel'] ) {
			nexora_carousel_nav( $args['carousel'] );
		}
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Prev/next buttons bound to a carousel id.
 *
 * @param string $id Carousel id.
 */
function nexora_carousel_nav( $id ) {
	$id = esc_attr( $id );
	echo '<div class="carousel-nav">';
	echo '<button type="button" class="carousel-nav__btn" data-carousel-prev="' . $id . '" aria-label="' . esc_attr__( 'Previous', 'nexora' ) . '">' . nexora_icon( 'chevron-right', 'xs', 'icon--flip-ltr' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<button type="button" class="carousel-nav__btn" data-carousel-next="' . $id . '" aria-label="' . esc_attr__( 'Next', 'nexora' ) . '">' . nexora_icon( 'chevron-left', 'xs', 'icon--flip-ltr' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';
}

/**
 * Button component from a link field.
 *
 * @param array  $link  Link value.
 * @param string $class Button classes.
 * @param bool   $arrow Append arrow icon.
 * @return string
 */
function nexora_button( $link, $class = 'btn btn--primary', $arrow = false ) {
	$link = nexora_link_value( $link );
	if ( '' === $link['url'] || '' === $link['text'] ) {
		return '';
	}
	$attrs = $link['target'] ? ' target="_blank" rel="noopener"' : '';
	return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $link['url'] ) . '"' . $attrs . '>' . esc_html( $link['text'] ) . ( $arrow ? nexora_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ) : '' ) . '</a>';
}

/**
 * Empty-state component.
 *
 * @param array $args icon, title, text, cta, href.
 */
function nexora_empty_state( array $args ) {
	$args = wp_parse_args( $args, array( 'icon' => 'magnifier', 'title' => '', 'text' => '', 'cta' => '', 'href' => '' ) );
	echo '<div class="empty"><span class="empty__icon">' . nexora_icon( $args['icon'], 'xl' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	if ( $args['title'] ) {
		echo '<h2 class="empty__title">' . esc_html( $args['title'] ) . '</h2>';
	}
	if ( $args['text'] ) {
		echo '<p class="empty__text">' . esc_html( $args['text'] ) . '</p>';
	}
	if ( $args['cta'] && $args['href'] ) {
		echo '<a class="btn btn--primary" href="' . esc_url( $args['href'] ) . '">' . esc_html( $args['cta'] ) . '</a>';
	}
	echo '</div>';
}

/**
 * Badge component.
 *
 * @param string $text  Text.
 * @param string $style discount|new|hot|out|soft|sale|success.
 * @return string
 */
function nexora_badge( $text, $style = 'soft' ) {
	return '<span class="badge badge--' . esc_attr( $style ) . '">' . esc_html( $text ) . '</span>';
}

/**
 * Post reading time in minutes.
 *
 * @param int|WP_Post $post Post.
 * @return int
 */
function nexora_reading_time( $post = null ) {
	$post  = get_post( $post );
	$words = str_word_count( wp_strip_all_tags( $post ? $post->post_content : '' ) );
	// Persian text isn't counted by str_word_count; fall back to whitespace split.
	if ( $words < 20 && $post ) {
		$words = count( preg_split( '/\s+/u', wp_strip_all_tags( $post->post_content ), -1, PREG_SPLIT_NO_EMPTY ) );
	}
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Post meta row (date, reading time, comments).
 *
 * @param int|WP_Post $post Post.
 * @param bool        $comments Show comment count.
 */
function nexora_post_meta( $post = null, $comments = false ) {
	$post = get_post( $post );
	echo '<div class="post-card__meta">';
	if ( nexora_option( 'blog', 'show_date' ) ) {
		echo '<span>' . nexora_icon( 'calendar-full', 'xs' ) . '<time datetime="' . esc_attr( get_the_date( DATE_W3C, $post ) ) . '">' . esc_html( nexora_num( get_the_date( '', $post ) ) ) . '</time></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( nexora_option( 'blog', 'show_readtime' ) ) {
		/* translators: %s: minutes */
		echo '<span>' . nexora_icon( 'clock3', 'xs' ) . esc_html( sprintf( __( '%s min read', 'nexora' ), nexora_num( nexora_reading_time( $post ) ) ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( $comments && comments_open( $post ) ) {
		echo '<span>' . nexora_icon( 'bubble', 'xs' ) . esc_html( nexora_num( get_comments_number( $post ) ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div>';
}

/**
 * Category badge for a post (first category).
 *
 * @param int|WP_Post $post Post.
 * @param string      $class Extra class.
 */
function nexora_post_category_badge( $post = null, $class = 'post-card__cat' ) {
	$cats = get_the_category( $post );
	if ( empty( $cats ) ) {
		return;
	}
	echo '<span class="badge badge--discount ' . esc_attr( $class ) . '">' . esc_html( $cats[0]->name ) . '</span>';
}

/**
 * Avatar initial circle.
 *
 * @param string $name Name.
 * @param string $class Extra class.
 */
function nexora_avatar_initial( $name, $class = '' ) {
	$initial = function_exists( 'mb_substr' ) ? mb_substr( trim( (string) $name ), 0, 1 ) : substr( trim( (string) $name ), 0, 1 );
	echo '<span class="avatar avatar--initial ' . esc_attr( $class ) . '" aria-hidden="true">' . esc_html( $initial ) . '</span>';
}

/**
 * Pagination with the theme's markup.
 *
 * @param WP_Query|null $query Query.
 */
function nexora_pagination( $query = null ) {
	global $wp_query;
	$query = $query ?: $wp_query;
	if ( $query->max_num_pages < 2 ) {
		return;
	}
	$links = paginate_links(
		array(
			'total'     => $query->max_num_pages,
			'current'   => max( 1, get_query_var( 'paged' ) ),
			'type'      => 'array',
			'prev_text' => nexora_icon( 'chevron-right', 'xs', 'icon--flip-ltr' ) . '<span class="visually-hidden">' . esc_html__( 'Previous', 'nexora' ) . '</span>',
			'next_text' => nexora_icon( 'chevron-left', 'xs', 'icon--flip-ltr' ) . '<span class="visually-hidden">' . esc_html__( 'Next', 'nexora' ) . '</span>',
			'mid_size'  => 1,
		)
	);
	if ( ! $links ) {
		return;
	}
	echo '<nav class="pagination" aria-label="' . esc_attr__( 'Pagination', 'nexora' ) . '">';
	foreach ( $links as $link ) {
		$link = str_replace( array( 'page-numbers', 'current' ), array( 'pagination__link', 'is-current' ), $link );
		$link = str_replace( 'dots', 'pagination__ellipsis', $link );
		$link = preg_replace_callback( '/>(\d+)</', static function ( $m ) { return '>' . nexora_num( $m[1] ) . '<'; }, $link );
		echo wp_kses( $link, array_merge( wp_kses_allowed_html( 'post' ), array( 'span' => array( 'class' => true, 'aria-current' => true ), 'a' => array( 'class' => true, 'href' => true, 'aria-current' => true, 'aria-label' => true ) ) ) );
	}
	echo '</nav>';
}

/**
 * Comment callback with the theme's markup.
 *
 * @param WP_Comment $comment Comment.
 * @param array      $args    Args.
 * @param int        $depth   Depth.
 */
function nexora_comment( $comment, $args, $depth ) {
	$tag = 'div' === $args['style'] ? 'div' : 'li';
	?>
	<<?php echo $tag; // phpcs:ignore ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $depth > 1 ? 'comment comment--reply' : 'comment' ); ?>>
		<div class="comment__head">
			<?php nexora_avatar_initial( get_comment_author( $comment ) ); ?>
			<div>
				<div class="comment__name"><?php comment_author( $comment ); ?></div>
				<div class="comment__date"><?php echo esc_html( nexora_num( get_comment_date( '', $comment ) ) ); ?></div>
			</div>
			<?php
			comment_reply_link(
				array_merge(
					$args,
					array(
						'depth'     => $depth,
						'max_depth' => $args['max_depth'],
						'before'    => '<span class="comment__reply">',
						'after'     => '</span>',
					)
				)
			);
			?>
		</div>
		<div class="comment__text">
			<?php if ( '0' === $comment->comment_approved ) : ?>
				<p class="text-muted small"><?php esc_html_e( 'Your comment is awaiting moderation.', 'nexora' ); ?></p>
			<?php endif; ?>
			<?php comment_text(); ?>
		</div>
	<?php
	// Closing tag is output by WordPress (end-callback).
}

/**
 * Print `{year}` / `{site}` placeholders in the copyright line.
 *
 * @return string
 */
function nexora_copyright() {
	$text = nexora_option( 'footer', 'copyright' );
	$text = str_replace( array( '{year}', '{site}' ), array( nexora_num( wp_date( 'Y' ) ), get_bloginfo( 'name' ) ), $text );
	return wp_kses( $text, nexora_kses_inline() );
}

/**
 * Social networks with URL set.
 *
 * @return array id => [label, url]
 */
function nexora_social_links() {
	$labels = array(
		'instagram' => 'Instagram',
		'telegram'  => 'Telegram',
		'whatsapp'  => 'WhatsApp',
		'x'         => 'X',
		'linkedin'  => 'LinkedIn',
		'youtube'   => 'YouTube',
		'aparat'    => 'Aparat',
	);
	$out = array();
	foreach ( $labels as $id => $label ) {
		$url = nexora_option( 'social', $id );
		if ( $url ) {
			$out[ $id ] = array( $label, $url );
		}
	}
	return $out;
}

/**
 * Render the social icon list component.
 *
 * @param string $class UL classes.
 */
function nexora_social_list( $class = 'social' ) {
	$links = nexora_social_links();
	if ( ! $links ) {
		return;
	}
	echo '<ul class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Social networks', 'nexora' ) . '">';
	foreach ( $links as $id => $s ) {
		echo '<li><a class="social__link" href="' . esc_url( $s[1] ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $s[0] ) . '">' . nexora_svg( $id ) . '</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</ul>';
}

/**
 * Body classes.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function nexora_body_classes( $classes ) {
	$classes[] = 'nexora';
	$classes[] = 'preset-' . nexora_active_preset_id();
	if ( ! nexora_option( 'header', 'mobile_bar' ) ) {
		$classes[] = 'no-mobile-bar';
	}
	if ( is_page_template( 'page-templates/minimal.php' ) || ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) ) {
		$classes[] = 'is-minimal';
	}
	return $classes;
}
add_filter( 'body_class', 'nexora_body_classes' );

/**
 * Does the current page use the minimal chrome (auth, checkout)?
 *
 * @return bool
 */
function nexora_is_minimal_layout() {
	if ( is_page_template( 'page-templates/minimal.php' ) ) {
		return true;
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
		return (bool) apply_filters( 'nexora_minimal_checkout', nexora_option( 'shop', 'checkout_minimal', true ) );
	}
	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() ) {
		return true;
	}
	return false;
}
