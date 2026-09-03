<?php
/**
 * Default blog sidebar (no widgets configured).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_cats = get_categories( array( 'hide_empty' => true, 'number' => 10 ) );
if ( $nexora_cats ) :
	?>
	<div class="widget">
		<h2 class="widget__title"><?php esc_html_e( 'Categories', 'nexora' ); ?></h2>
		<ul class="widget__list">
			<?php foreach ( $nexora_cats as $nexora_c ) : ?>
				<li><a href="<?php echo esc_url( get_category_link( $nexora_c ) ); ?>"><span><?php echo esc_html( $nexora_c->name ); ?></span><span class="count"><?php echo esc_html( nexora_num( $nexora_c->count ) ); ?></span></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
endif;

$nexora_recent = new WP_Query( array( 'posts_per_page' => 4, 'ignore_sticky_posts' => true, 'no_found_rows' => true, 'post__not_in' => is_singular( 'post' ) ? array( get_the_ID() ) : array() ) ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
if ( $nexora_recent->have_posts() ) :
	?>
	<div class="widget">
		<h2 class="widget__title"><?php esc_html_e( 'Recent posts', 'nexora' ); ?></h2>
		<div class="widget__posts">
			<?php
			while ( $nexora_recent->have_posts() ) {
				$nexora_recent->the_post();
				get_template_part( 'template-parts/blog/post-inline' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
	<?php
endif;

$nexora_tags = get_tags( array( 'number' => 20, 'orderby' => 'count', 'order' => 'DESC' ) );
if ( $nexora_tags ) :
	?>
	<div class="widget">
		<h2 class="widget__title"><?php esc_html_e( 'Tags', 'nexora' ); ?></h2>
		<div class="tag-cloud">
			<?php foreach ( $nexora_tags as $nexora_t ) : ?>
				<a href="<?php echo esc_url( get_tag_link( $nexora_t ) ); ?>"><?php echo esc_html( $nexora_t->name ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
endif;

if ( nexora_option( 'footer', 'newsletter_enable' ) ) :
	?>
	<div class="widget widget--dark">
		<h2 class="widget__title"><?php esc_html_e( 'Get new articles by email', 'nexora' ); ?></h2>
		<?php get_template_part( 'template-parts/components/newsletter-form', null, array( 'id' => 'sidebar-newsletter', 'class' => 'footer-newsletter', 'source' => 'sidebar' ) ); ?>
	</div>
	<?php
endif;

if ( class_exists( 'WooCommerce' ) && nexora_option( 'home', 'flash_enable' ) ) :
	$nexora_img = (int) nexora_option( 'pages', 'megamenu_image' );
	?>
	<div class="widget widget--flush">
		<a class="promo promo--dark promo--widget" href="<?php echo esc_url( nexora_shop_url( array( 'on_sale' => 1 ) ) ); ?>">
			<?php if ( $nexora_img ) { echo wp_get_attachment_image( $nexora_img, 'medium_large', false, array( 'alt' => '', 'loading' => 'lazy' ) ); } ?>
			<span class="promo__body promo__body--full"><span class="promo__kicker"><?php echo esc_html( nexora_option( 'home', 'flash_kicker' ) ); ?></span><span class="promo__title promo__title--sm"><?php echo esc_html( nexora_option( 'home', 'flash_headline' ) ); ?></span><span class="btn btn--primary btn--sm"><?php esc_html_e( 'All offers', 'nexora' ); ?></span></span>
		</a>
	</div>
	<?php
endif;
