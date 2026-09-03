<?php
/**
 * Newsletter form (AJAX to nexora_newsletter, or external action URL).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_id     = $args['id'] ?? 'newsletter-email';
$nexora_class  = $args['class'] ?? 'newsletter__form';
$nexora_text   = $args['text'] ?? '';
$nexora_action = nexora_option( 'footer', 'newsletter_action' );
$nexora_source = $args['source'] ?? 'footer';
?>
<form class="<?php echo esc_attr( $nexora_class ); ?>" data-newsletter novalidate<?php echo $nexora_action ? ' action="' . esc_url( $nexora_action ) . '" method="post" target="_blank" data-newsletter-external' : ''; ?>>
	<?php if ( $nexora_text ) : ?>
		<p class="small newsletter__text"><?php echo esc_html( $nexora_text ); ?></p>
	<?php endif; ?>
	<div class="form-group">
		<label class="visually-hidden" for="<?php echo esc_attr( $nexora_id ); ?>"><?php esc_html_e( 'Your email address', 'nexora' ); ?></label>
		<div class="input-group input-group--attached">
			<input id="<?php echo esc_attr( $nexora_id ); ?>" class="form-control" type="email" name="email" placeholder="<?php esc_attr_e( 'Your email address', 'nexora' ); ?>" autocomplete="email" required dir="ltr">
			<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Subscribe', 'nexora' ); ?></button>
		</div>
		<input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="visually-hidden" aria-hidden="true">
		<input type="hidden" name="source" value="<?php echo esc_attr( $nexora_source ); ?>">
		<span class="form-error" role="alert"></span>
	</div>
</form>
