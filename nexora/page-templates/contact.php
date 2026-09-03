<?php
/**
 * Template Name: Contact
 * Template Post Type: page
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();
$nexora_phone    = nexora_option( 'general', 'phone' );
$nexora_email    = nexora_option( 'general', 'email' );
$nexora_address  = nexora_option( 'general', 'address' );
$nexora_hours    = nexora_option( 'general', 'hours' );
$nexora_map      = nexora_option( 'general', 'map_embed' );
$nexora_short    = trim( (string) nexora_option( 'pages', 'contact_shortcode' ) );
$nexora_subjects = apply_filters( 'nexora_contact_subjects', array( __( 'Track my order', 'nexora' ), __( 'Returns', 'nexora' ), __( 'Payment', 'nexora' ), __( 'Sell on our store', 'nexora' ), __( 'Other', 'nexora' ) ) );
$nexora_faq_page = get_pages( array( 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/faq.php', 'number' => 1 ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
nexora_breadcrumb();
?>
<section class="section section--sm page-contact">
	<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<div class="page-head__inner page-head__inner--contact">
				<div><h1 class="h3"><?php the_title(); ?></h1><p class="text-muted"><?php echo esc_html( nexora_option( 'pages', 'contact_intro' ) ); ?></p></div>
			</div>
			<?php if ( '' !== trim( get_the_content() ) ) : ?><div class="prose page-contact__intro"><?php the_content(); ?></div><?php endif; ?>
		<?php endwhile; ?>
		<div class="row g-4">
			<div class="col-lg-4">
				<div class="stack">
					<?php if ( $nexora_phone ) : ?>
						<div class="card-surface card-surface--pad contact-card"><span class="trust-item__icon"><?php nexora_the_icon( 'telephone' ); ?></span><div><div class="fw-bold text-strong"><?php esc_html_e( 'Phone', 'nexora' ); ?></div><a class="ltr" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $nexora_phone ) ); ?>"><?php echo esc_html( nexora_num( $nexora_phone ) ); ?></a><?php if ( $nexora_hours ) : ?><div class="small text-muted"><?php echo esc_html( $nexora_hours ); ?></div><?php endif; ?></div></div>
					<?php endif; ?>
					<?php if ( $nexora_email ) : ?>
						<div class="card-surface card-surface--pad contact-card"><span class="trust-item__icon"><?php nexora_the_icon( 'envelope' ); ?></span><div><div class="fw-bold text-strong"><?php esc_html_e( 'Email', 'nexora' ); ?></div><a class="ltr" href="mailto:<?php echo esc_attr( $nexora_email ); ?>"><?php echo esc_html( $nexora_email ); ?></a></div></div>
					<?php endif; ?>
					<?php if ( $nexora_address ) : ?>
						<div class="card-surface card-surface--pad contact-card"><span class="trust-item__icon"><?php nexora_the_icon( 'map-marker' ); ?></span><div><div class="fw-bold text-strong"><?php esc_html_e( 'Address', 'nexora' ); ?></div><p class="small"><?php echo esc_html( $nexora_address ); ?></p></div></div>
					<?php endif; ?>
					<?php if ( nexora_social_links() ) : ?>
						<div class="card-surface card-surface--pad"><div class="fw-bold text-strong contact-card__label"><?php esc_html_e( 'Follow us', 'nexora' ); ?></div><?php nexora_social_list( 'social social--light' ); ?></div>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-lg-8">
				<?php if ( $nexora_short && has_shortcode( $nexora_short, explode( ' ', trim( $nexora_short, '[] ' ) )[0] ) ) : ?>
					<div class="card-surface card-surface--pad contact-form-wrap"><h2 class="h4 contact-form__title"><?php esc_html_e( 'Contact form', 'nexora' ); ?></h2><?php echo do_shortcode( $nexora_short ); ?></div>
				<?php else : ?>
					<form class="card-surface card-surface--pad contact-form" data-contact-form novalidate>
						<h2 class="h4 contact-form__title"><?php esc_html_e( 'Contact form', 'nexora' ); ?></h2>
						<?php wp_nonce_field( 'nexora_contact', 'nexora_contact_nonce' ); ?>
						<input type="text" name="website" class="visually-hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
						<div class="form-row">
							<div class="form-group"><label class="form-label" for="ct-name"><?php esc_html_e( 'Name', 'nexora' ); ?> <span class="req">*</span></label><input id="ct-name" class="form-control" name="name" required minlength="3" autocomplete="name"><span class="form-error" role="alert"></span></div>
							<div class="form-group"><label class="form-label" for="ct-email"><?php esc_html_e( 'Email', 'nexora' ); ?> <span class="req">*</span></label><input id="ct-email" class="form-control" name="email" type="email" required dir="ltr" autocomplete="email"><span class="form-error" role="alert"></span></div>
						</div>
						<div class="form-row">
							<div class="form-group"><label class="form-label" for="ct-phone"><?php esc_html_e( 'Phone', 'nexora' ); ?></label><input id="ct-phone" class="form-control" name="phone" type="tel" dir="ltr" autocomplete="tel"><span class="form-error" role="alert"></span></div>
							<div class="form-group"><label class="form-label" for="ct-subject"><?php esc_html_e( 'Subject', 'nexora' ); ?> <span class="req">*</span></label><select id="ct-subject" class="form-control" name="subject" required><option value=""><?php esc_html_e( 'Select…', 'nexora' ); ?></option><?php foreach ( $nexora_subjects as $nexora_s ) : ?><option><?php echo esc_html( $nexora_s ); ?></option><?php endforeach; ?></select><span class="form-error" role="alert"></span></div>
						</div>
						<div class="form-group"><label class="form-label" for="ct-msg"><?php esc_html_e( 'Your message', 'nexora' ); ?> <span class="req">*</span></label><textarea id="ct-msg" class="form-control" name="message" rows="5" required minlength="20"></textarea><span class="form-error" role="alert"></span></div>
						<div class="cluster contact-form__actions"><button type="submit" class="btn btn--primary"><?php nexora_the_icon( 'paper-plane', 'xs' ); ?><?php esc_html_e( 'Send message', 'nexora' ); ?></button><span class="form-status" role="status" aria-live="polite" data-form-status></span></div>
					</form>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( nexora_option( 'pages', 'contact_map' ) ) : ?>
			<div class="card-surface contact-map" aria-label="<?php esc_attr_e( 'Map', 'nexora' ); ?>">
				<?php if ( $nexora_map ) : ?>
					<?php echo nexora_kses_iframe( $nexora_map ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<div class="contact-map__placeholder"><?php nexora_the_icon( 'map-marker', 'xl' ); ?><span><?php echo esc_html( $nexora_address ); ?></span></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( nexora_option( 'pages', 'contact_faq' ) && $nexora_faq_page ) : ?>
			<div class="cta-band cta-band--rounded contact-faq"><div class="cta-band__inner"><div><div class="cta-band__title"><?php nexora_the_icon( 'question-circle' ); ?><?php esc_html_e( 'Have a quick question?', 'nexora' ); ?></div><div class="cta-band__text"><?php esc_html_e( 'Most answers about shipping, payment and returns are in our FAQ.', 'nexora' ); ?></div></div><a class="btn btn--dark" href="<?php echo esc_url( get_permalink( $nexora_faq_page[0] ) ); ?>"><?php esc_html_e( 'Read the FAQ', 'nexora' ); ?></a></div></div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
