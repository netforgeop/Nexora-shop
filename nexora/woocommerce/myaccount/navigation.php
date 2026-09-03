<?php
/**
 * Account navigation.
 *
 * @package Nexora
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_navigation' );
$nexora_user   = wp_get_current_user();
$nexora_counts = array(
	'orders'          => wc_get_customer_order_count( $nexora_user->ID ),
	'nexora-wishlist' => count( nexora_user_list( 'wishlist' ) ),
);
?>
<nav class="account-nav woocommerce-MyAccount-navigation" aria-label="<?php esc_attr_e( 'Account', 'nexora' ); ?>">
	<div class="account-nav__user">
		<span class="avatar avatar--initial" aria-hidden="true"><?php echo esc_html( nexora_avatar_initial( $nexora_user->display_name ) ); ?></span>
		<div class="account-nav__meta"><div class="account-nav__name"><?php echo esc_html( $nexora_user->display_name ); ?></div><div class="account-nav__email truncate ltr"><?php echo esc_html( $nexora_user->user_email ); ?></div></div>
	</div>
	<ul class="account-nav__list">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<?php
			$classes = wc_get_account_menu_item_classes( $endpoint );
			$active  = false !== strpos( $classes, 'is-active' );
			$danger  = 'customer-logout' === $endpoint;
			?>
			<li class="<?php echo esc_attr( $classes ); ?>">
				<a class="account-nav__link<?php echo $active ? ' is-active' : ''; ?><?php echo $danger ? ' account-nav__link--danger' : ''; ?>" href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo $active ? 'aria-current="page"' : ''; ?>>
					<?php nexora_the_icon( nexora_account_icon( $endpoint ), 'sm' ); ?><?php echo esc_html( $label ); ?>
					<?php if ( ! empty( $nexora_counts[ $endpoint ] ) ) : ?><span class="badge badge--soft"><?php echo esc_html( nexora_num( $nexora_counts[ $endpoint ] ) ); ?></span><?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<?php do_action( 'woocommerce_after_account_navigation' ); ?>
