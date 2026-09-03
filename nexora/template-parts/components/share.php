<?php
/**
 * Share row.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_url   = $args['url'] ?? get_permalink();
$nexora_title = $args['title'] ?? get_the_title();
$nexora_enc   = rawurlencode( $nexora_url );
$nexora_tenc  = rawurlencode( $nexora_title );
?>
<div class="article-share">
	<span class="small text-muted"><?php esc_html_e( 'Share this', 'nexora' ); ?></span>
	<button type="button" class="icon-btn icon-btn--light icon-btn--circle" data-action="share" data-share-url="<?php echo esc_url( $nexora_url ); ?>" data-share-title="<?php echo esc_attr( $nexora_title ); ?>" aria-label="<?php esc_attr_e( 'Share', 'nexora' ); ?>"><?php nexora_the_icon( 'share2', 'sm' ); ?></button>
	<a class="icon-btn icon-btn--light icon-btn--circle" href="<?php echo esc_url( 'https://t.me/share/url?url=' . $nexora_enc . '&text=' . $nexora_tenc ); ?>" target="_blank" rel="noopener" aria-label="Telegram"><?php echo nexora_svg( 'telegram' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	<a class="icon-btn icon-btn--light icon-btn--circle" href="<?php echo esc_url( 'https://wa.me/?text=' . $nexora_tenc . '%20' . $nexora_enc ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?php echo nexora_svg( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	<a class="icon-btn icon-btn--light icon-btn--circle" href="<?php echo esc_url( 'https://x.com/intent/tweet?url=' . $nexora_enc . '&text=' . $nexora_tenc ); ?>" target="_blank" rel="noopener" aria-label="X"><?php echo nexora_svg( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	<button type="button" class="icon-btn icon-btn--light icon-btn--circle" data-action="copy-link" data-copy="<?php echo esc_url( $nexora_url ); ?>" aria-label="<?php esc_attr_e( 'Copy link', 'nexora' ); ?>"><?php nexora_the_icon( 'link', 'sm' ); ?></button>
</div>
