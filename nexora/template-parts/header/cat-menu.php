<?php
/**
 * "All categories" mega menu.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_header = $args['header'] ?? nexora_options( 'header' );
$nexora_cats   = nexora_top_categories( (int) $nexora_header['cat_menu_count'] );
if ( ! $nexora_cats ) {
	return;
}
$nexora_promo_img  = (int) nexora_option( 'pages', 'megamenu_image' );
$nexora_promo_link = nexora_link_value( nexora_option( 'pages', 'megamenu_link' ) );
?>
<div class="cat-menu" data-cat-menu>
	<button type="button" class="cat-menu__trigger" aria-expanded="false" aria-controls="cat-menu-panel" data-cat-menu-trigger>
		<?php nexora_the_icon( 'menu', 'sm' ); ?><span><?php echo esc_html( $nexora_header['cat_menu_label'] ?: __( 'Categories', 'nexora' ) ); ?></span><?php nexora_the_icon( 'chevron-down', 'xs', 'nav__caret' ); ?>
	</button>
	<div class="cat-menu__panel" id="cat-menu-panel">
		<ul class="cat-menu__list">
			<?php
			foreach ( $nexora_cats as $nexora_cat ) :
				$nexora_url  = get_term_link( $nexora_cat );
				$nexora_kids = nexora_category_children( $nexora_cat, 8 );
				$nexora_brs  = nexora_brands( 5, $nexora_cat->term_id );
				$nexora_top  = $nexora_header['cat_menu_products'] ? nexora_query_products( 'category', 5, array( 'category' => $nexora_cat->term_id ) ) : array();
				?>
				<li class="cat-menu__item">
					<a class="cat-menu__link" href="<?php echo esc_url( $nexora_url ); ?>"><?php nexora_the_icon( nexora_category_icon( $nexora_cat ), 'sm' ); ?><span><?php echo esc_html( $nexora_cat->name ); ?></span><?php nexora_the_icon( 'chevron-down', 'xs', 'nav__caret' ); ?></a>
					<div class="megamenu">
						<div class="megamenu__col">
							<div class="megamenu__heading"><?php echo esc_html( $nexora_cat->name ); ?></div>
							<?php foreach ( $nexora_kids as $nexora_kid ) : ?>
								<a class="megamenu__link" href="<?php echo esc_url( get_term_link( $nexora_kid ) ); ?>"><?php echo esc_html( $nexora_kid->name ); ?></a>
							<?php endforeach; ?>
							<a class="megamenu__link fw-medium text-primary" href="<?php echo esc_url( $nexora_url ); ?>"><?php echo esc_html( sprintf( /* translators: %s: category */ __( 'View all in %s', 'nexora' ), $nexora_cat->name ) ); ?></a>
						</div>
						<div class="megamenu__col">
							<?php if ( $nexora_brs ) : ?>
								<div class="megamenu__heading"><?php esc_html_e( 'Brands', 'nexora' ); ?></div>
								<?php foreach ( $nexora_brs as $nexora_br ) : ?>
									<a class="megamenu__link" href="<?php echo esc_url( nexora_brand_url( $nexora_br ) ); ?>"><?php echo esc_html( $nexora_br->name ); ?></a>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="megamenu__heading"><?php esc_html_e( 'Quick links', 'nexora' ); ?></div>
							<?php endif; ?>
							<a class="megamenu__link" href="<?php echo esc_url( add_query_arg( 'on_sale', 1, $nexora_url ) ); ?>"><?php esc_html_e( 'Hot deals', 'nexora' ); ?></a>
							<a class="megamenu__link" href="<?php echo esc_url( add_query_arg( 'orderby', 'date', $nexora_url ) ); ?>"><?php esc_html_e( 'New arrivals', 'nexora' ); ?></a>
							<a class="megamenu__link" href="<?php echo esc_url( add_query_arg( 'orderby', 'popularity', $nexora_url ) ); ?>"><?php esc_html_e( 'Best sellers', 'nexora' ); ?></a>
						</div>
						<?php if ( $nexora_top ) : ?>
							<div class="megamenu__col">
								<div class="megamenu__heading"><?php esc_html_e( 'Best sellers', 'nexora' ); ?></div>
								<?php foreach ( $nexora_top as $nexora_pid ) : ?>
									<a class="megamenu__link truncate" href="<?php echo esc_url( get_permalink( $nexora_pid ) ); ?>"><?php echo esc_html( get_the_title( $nexora_pid ) ); ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( $nexora_promo_img ) : ?>
							<a class="megamenu__promo" href="<?php echo esc_url( $nexora_promo_link['url'] ?: nexora_shop_url( array( 'on_sale' => 1 ) ) ); ?>">
								<?php echo wp_get_attachment_image( $nexora_promo_img, 'nexora-square', false, array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
								<span class="megamenu__promo-body"><span class="badge badge--discount"><?php esc_html_e( 'Special offer', 'nexora' ); ?></span><span class="megamenu__promo-title"><?php echo esc_html( $nexora_promo_link['text'] ?: __( 'View offers', 'nexora' ) ); ?></span></span>
							</a>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
			<li class="cat-menu__item"><a class="cat-menu__link cat-menu__link--all" href="<?php echo esc_url( nexora_shop_url() ); ?>"><?php nexora_the_icon( 'grid', 'sm' ); ?><span><?php esc_html_e( 'All categories', 'nexora' ); ?></span></a></li>
		</ul>
	</div>
</div>
