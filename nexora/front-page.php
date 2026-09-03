<?php
/**
 * Front page: component-based homepage driven by Theme Settings → Homepage.
 * Falls back to page content when "Your homepage displays" is a page with
 * the default template and the homepage builder is disabled.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

if ( 'posts' === get_option( 'show_on_front' ) ) {
	get_template_part( 'index' );
	return;
}

get_header();

$nexora_home  = nexora_options( 'home' );
$nexora_order = (array) $nexora_home['sections_order'];
$nexora_woo   = class_exists( 'WooCommerce' );
$nexora_needs = array( 'categories', 'featured', 'flash', 'newest', 'best', 'collections', 'tiles', 'brands' );
?>
<h1 class="visually-hidden"><?php bloginfo( 'name' ); ?> – <?php echo esc_html( get_bloginfo( 'description' ) ?: nexora_option( 'general', 'tagline' ) ); ?></h1>
<?php
foreach ( $nexora_order as $nexora_section ) {
	if ( empty( $nexora_home[ $nexora_section . '_enable' ] ) ) {
		continue;
	}
	if ( ! $nexora_woo && in_array( $nexora_section, $nexora_needs, true ) ) {
		continue;
	}
	do_action( 'nexora_before_home_section', $nexora_section );
	get_template_part( 'template-parts/home/' . $nexora_section, null, array( 'home' => $nexora_home ) );
	do_action( 'nexora_after_home_section', $nexora_section );
}

// Optional page content (e.g. SEO text) below the sections.
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		if ( '' !== trim( get_the_content() ) ) {
			echo '<section class="section"><div class="container"><div class="prose">';
			the_content();
			echo '</div></div></section>';
		}
	}
}

get_footer();
