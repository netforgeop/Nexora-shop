<?php
/**
 * 10-step Setup Wizard.
 *
 * Every step is optional and can be skipped; nothing destructive happens.
 * Secrets (API keys, gateway credentials) are never asked for — payment and
 * shipping steps link to WooCommerce's own screens.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Steps.
 *
 * @return array id => [title, description]
 */
function nexora_wizard_steps() {
	return array(
		'welcome'   => array( __( 'Welcome', 'nexora' ), __( 'What the wizard does and how long it takes.', 'nexora' ) ),
		'plugins'   => array( __( 'Plugins', 'nexora' ), __( 'WooCommerce is required; the rest is optional.', 'nexora' ) ),
		'store'     => array( __( 'Store details', 'nexora' ), __( 'Name, tagline, contact info.', 'nexora' ) ),
		'logo'      => array( __( 'Logo', 'nexora' ), __( 'Upload the logo and site icon.', 'nexora' ) ),
		'colors'    => array( __( 'Colours', 'nexora' ), __( 'Pick a preset — change it any time.', 'nexora' ) ),
		'commerce'  => array( __( 'WooCommerce', 'nexora' ), __( 'Currency, address, pages.', 'nexora' ) ),
		'demo'      => array( __( 'Demo content', 'nexora' ), __( 'Start from a full store or an empty one.', 'nexora' ) ),
		'homepage'  => array( __( 'Homepage', 'nexora' ), __( 'Choose which sections to show.', 'nexora' ) ),
		'pages'     => array( __( 'Pages & menus', 'nexora' ), __( 'Create essential pages and the main menu.', 'nexora' ) ),
		'finish'    => array( __( 'Done', 'nexora' ), __( 'Next steps and useful links.', 'nexora' ) ),
	);
}

/**
 * Current step.
 *
 * @return string
 */
function nexora_wizard_current() {
	$steps = array_keys( nexora_wizard_steps() );
	$step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return in_array( $step, $steps, true ) ? $step : $steps[0];
}

/**
 * Next step id.
 *
 * @param string $step Current.
 * @return string
 */
function nexora_wizard_next( $step ) {
	$steps = array_keys( nexora_wizard_steps() );
	$i     = array_search( $step, $steps, true );
	return $steps[ min( $i + 1, count( $steps ) - 1 ) ];
}

/**
 * URL for a step.
 *
 * @param string $step Step.
 * @return string
 */
function nexora_wizard_url( $step ) {
	return admin_url( 'admin.php?page=nexora-wizard&step=' . $step );
}

/**
 * Handle step submissions.
 */
add_action(
	'admin_init',
	static function () {
		if ( ! isset( $_POST['nexora_wizard_step'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		$step = sanitize_key( $_POST['nexora_wizard_step'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		nexora_admin_post_check( 'nexora_wizard_' . $step );
		$skip = isset( $_POST['skip'] );
		$post = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( ! $skip ) {
			switch ( $step ) {
				case 'store':
					if ( isset( $post['blogname'] ) ) {
						update_option( 'blogname', sanitize_text_field( $post['blogname'] ) );
					}
					if ( isset( $post['blogdescription'] ) ) {
						update_option( 'blogdescription', sanitize_text_field( $post['blogdescription'] ) );
					}
					$general = nexora_options( 'general' );
					foreach ( array( 'phone', 'email', 'address', 'hours', 'logo_text' ) as $k ) {
						if ( isset( $post[ $k ] ) ) {
							$general[ $k ] = $post[ $k ];
						}
					}
					nexora_update_options( 'general', nexora_sanitize_group( 'general', $general ) );
					$social = nexora_options( 'social' );
					foreach ( array( 'instagram', 'telegram', 'whatsapp' ) as $k ) {
						if ( isset( $post[ 'social_' . $k ] ) ) {
							$social[ $k ] = $post[ 'social_' . $k ];
						}
					}
					nexora_update_options( 'social', nexora_sanitize_group( 'social', $social ) );
					break;

				case 'logo':
					if ( isset( $post['custom_logo'] ) ) {
						set_theme_mod( 'custom_logo', absint( $post['custom_logo'] ) );
					}
					if ( isset( $post['site_icon'] ) ) {
						update_option( 'site_icon', absint( $post['site_icon'] ) );
					}
					break;

				case 'colors':
					$preset = isset( $post['preset'] ) ? sanitize_key( $post['preset'] ) : '';
					if ( isset( nexora_presets()[ $preset ] ) ) {
						update_option( 'nexora_active_preset', $preset );
					}
					break;

				case 'commerce':
					if ( class_exists( 'WooCommerce' ) ) {
						if ( ! empty( $post['currency'] ) && array_key_exists( $post['currency'], get_woocommerce_currencies() ) ) {
							update_option( 'woocommerce_currency', sanitize_text_field( $post['currency'] ) );
						}
						if ( ! empty( $post['country'] ) ) {
							update_option( 'woocommerce_default_country', sanitize_text_field( $post['country'] ) );
						}
						if ( isset( $post['city'] ) ) {
							update_option( 'woocommerce_store_city', sanitize_text_field( $post['city'] ) );
						}
						if ( isset( $post['store_address'] ) ) {
							update_option( 'woocommerce_store_address', sanitize_text_field( $post['store_address'] ) );
						}
						if ( ! empty( $post['create_wc_pages'] ) && class_exists( 'WC_Install' ) ) {
							WC_Install::create_pages();
						}
						if ( isset( $post['guest_checkout'] ) ) {
							update_option( 'woocommerce_enable_guest_checkout', 'yes' );
						} else {
							update_option( 'woocommerce_enable_guest_checkout', 'no' );
						}
					}
					break;

				case 'homepage':
					$home = nexora_options( 'home' );
					foreach ( array_keys( nexora_home_section_labels() ) as $key ) {
						$home[ $key . '_enable' ] = ! empty( $post['sections'][ $key ] );
					}
					nexora_update_options( 'home', nexora_sanitize_group( 'home', $home ) );
					break;

				case 'pages':
					nexora_ensure_pages();
					if ( ! empty( $post['create_menu'] ) ) {
						nexora_ensure_menus();
					}
					flush_rewrite_rules();
					break;

				case 'finish':
					nexora_set_state( 'wizard_done', time() );
					wp_safe_redirect( admin_url( 'admin.php?page=nexora' ) );
					exit;
			}
		}
		if ( 'finish' === $step ) {
			nexora_set_state( 'wizard_done', time() );
			wp_safe_redirect( admin_url( 'admin.php?page=nexora' ) );
			exit;
		}
		wp_safe_redirect( nexora_wizard_url( nexora_wizard_next( $step ) ) );
		exit;
	}
);

/**
 * Render wizard (full-screen-ish layout inside admin).
 */
function nexora_render_wizard() {
	$steps   = nexora_wizard_steps();
	$current = nexora_wizard_current();
	$index   = array_search( $current, array_keys( $steps ), true );
	$total   = count( $steps );
	echo '<div class="wrap nx-wrap nx-wizard" id="nexora-admin" data-page="nexora-wizard">';
	echo '<div class="nx-wizard__side"><div class="nx-wizard__brand"><img src="' . esc_url( NEXORA_URI . 'assets/img/brand/avirad-mark.svg' ) . '" alt="AVIRAD" width="28" height="28"><strong>Nexora</strong><span>' . esc_html__( 'Setup wizard', 'nexora' ) . '</span></div><ol class="nx-wizard__steps">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	foreach ( array_keys( $steps ) as $i => $id ) {
		$cls = $i < $index ? 'is-done' : ( $i === $index ? 'is-current' : '' );
		printf( '<li class="%1$s"><a href="%2$s"><span class="nx-wizard__num">%3$s</span><span><strong>%4$s</strong><small>%5$s</small></span></a></li>', esc_attr( $cls ), esc_url( nexora_wizard_url( $id ) ), $i < $index ? nexora_admin_icon( 'check' ) : (int) ( $i + 1 ), esc_html( $steps[ $id ][0] ), esc_html( $steps[ $id ][1] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</ol><a class="nx-wizard__exit" href="' . esc_url( admin_url( 'admin.php?page=nexora' ) ) . '">' . esc_html__( 'Exit to dashboard', 'nexora' ) . '</a></div>';

	echo '<div class="nx-wizard__main"><div class="nx-wizard__progress"><span style="width:' . esc_attr( round( ( $index + 1 ) / $total * 100 ) ) . '%"></span></div>';
	echo '<form method="post" class="nx-wizard__form">';
	wp_nonce_field( 'nexora_wizard_' . $current, '_nexora_nonce' );
	echo '<input type="hidden" name="nexora_wizard_step" value="' . esc_attr( $current ) . '">';
	echo '<h1>' . esc_html( $steps[ $current ][0] ) . '</h1><p class="nx-wizard__lead">' . esc_html( $steps[ $current ][1] ) . '</p>';
	nexora_admin_print_flash();
	call_user_func( 'nexora_wizard_step_' . $current );
	echo '<div class="nx-wizard__actions">';
	if ( $index > 0 ) {
		echo '<a class="button" href="' . esc_url( nexora_wizard_url( array_keys( $steps )[ $index - 1 ] ) ) . '">' . esc_html__( 'Back', 'nexora' ) . '</a>';
	}
	if ( 'finish' === $current ) {
		echo '<button class="button button-primary button-hero">' . esc_html__( 'Go to dashboard', 'nexora' ) . '</button>';
	} else {
		echo '<button class="button button-primary button-hero">' . esc_html__( 'Save & continue', 'nexora' ) . '</button> <button class="button-link" name="skip" value="1">' . esc_html__( 'Skip this step', 'nexora' ) . '</button>';
	}
	echo '</div></form></div></div>';
}

/* ---- Step bodies ---------------------------------------------------- */

function nexora_wizard_step_welcome() {
	echo '<div class="nx-wizard__card"><p>' . esc_html__( 'In about five minutes you will have a store with a logo, colours, contact details, WooCommerce basics, and — if you want — full demo content to edit instead of starting from a blank page.', 'nexora' ) . '</p><ul class="nx-list">';
	foreach ( array( __( 'Nothing is deleted and no account or API key is requested.', 'nexora' ), __( 'Every step can be skipped and every setting changed later in Theme Settings.', 'nexora' ), __( 'You can re-run this wizard from the theme menu.', 'nexora' ) ) as $li ) {
		echo '<li>' . esc_html( $li ) . '</li>';
	}
	echo '</ul></div>';
}

function nexora_wizard_step_plugins() {
	echo '<div class="nx-grid nx-grid--2">';
	foreach ( nexora_plugin_list() as $plugin ) {
		if ( ! empty( $plugin['locale'] ) && strpos( get_locale(), $plugin['locale'] ) !== 0 ) {
			continue;
		}
		$st = nexora_plugin_status( $plugin );
		$ac = nexora_plugin_action( $plugin, $st );
		echo '<div class="nx-wizard__card"><h3>' . esc_html( $plugin['name'] ) . ( $plugin['required'] ? ' <span class="nx-req">' . esc_html__( 'required', 'nexora' ) . '</span>' : '' ) . '</h3><p class="nx-muted">' . esc_html( $plugin['desc'] ) . '</p>';
		echo nexora_admin_pill( 'active' === $st['status'] ? 'ok' : ( 'installed' === $st['status'] ? 'warn' : ( $plugin['required'] ? 'bad' : 'muted' ) ), 'active' === $st['status'] ? __( 'Active', 'nexora' ) : ( 'installed' === $st['status'] ? __( 'Installed', 'nexora' ) : __( 'Not installed', 'nexora' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $ac ) {
			printf( ' <a class="button button-small" href="%1$s"%2$s>%3$s</a>', esc_url( $ac[1] ), $ac[2] ? '' : ' target="_blank" rel="noopener"', esc_html( $ac[0] ) );
		}
		echo '</div>';
	}
	echo '</div><p class="nx-muted">' . esc_html__( 'After installing, return to this page — the wizard remembers where you were.', 'nexora' ) . '</p>';
}

function nexora_wizard_step_store() {
	$g = nexora_options( 'general' );
	$s = nexora_options( 'social' );
	$f = static function ( $name, $label, $value, $type = 'text' ) {
		printf( '<div class="nx-field"><label class="nx-field__label" for="w-%1$s">%2$s</label><div class="nx-field__control"><input type="%4$s" id="w-%1$s" name="%1$s" value="%3$s" class="regular-text"></div></div>', esc_attr( $name ), esc_html( $label ), esc_attr( $value ), esc_attr( $type ) );
	};
	echo '<div class="nx-wizard__card">';
	$f( 'blogname', __( 'Store name', 'nexora' ), get_option( 'blogname' ) );
	$f( 'blogdescription', __( 'Tagline', 'nexora' ), get_option( 'blogdescription' ) );
	$f( 'logo_text', __( 'Text logo (used when no image logo)', 'nexora' ), $g['logo_text'] );
	$f( 'phone', __( 'Support phone', 'nexora' ), $g['phone'], 'tel' );
	$f( 'email', __( 'Support email', 'nexora' ), $g['email'], 'email' );
	$f( 'hours', __( 'Working hours', 'nexora' ), $g['hours'] );
	printf( '<div class="nx-field"><label class="nx-field__label" for="w-address">%1$s</label><div class="nx-field__control"><textarea id="w-address" name="address" rows="2" class="large-text">%2$s</textarea></div></div>', esc_html__( 'Address', 'nexora' ), esc_textarea( $g['address'] ) );
	echo '<h3>' . esc_html__( 'Social (optional)', 'nexora' ) . '</h3>';
	$f( 'social_instagram', 'Instagram', $s['instagram'], 'url' );
	$f( 'social_telegram', 'Telegram', $s['telegram'], 'url' );
	$f( 'social_whatsapp', 'WhatsApp', $s['whatsapp'], 'url' );
	echo '</div>';
}

function nexora_wizard_step_logo() {
	$logo = (int) get_theme_mod( 'custom_logo' );
	$icon = (int) get_option( 'site_icon' );
	echo '<div class="nx-grid nx-grid--2">';
	foreach ( array( array( 'custom_logo', __( 'Logo', 'nexora' ), __( 'SVG or PNG with transparent background, at least 160 px tall.', 'nexora' ), $logo ), array( 'site_icon', __( 'Site icon (favicon)', 'nexora' ), __( 'Square PNG, 512×512 px.', 'nexora' ), $icon ) ) as $item ) {
		$src = $item[3] ? wp_get_attachment_image_url( $item[3], 'medium' ) : '';
		echo '<div class="nx-wizard__card"><h3>' . esc_html( $item[1] ) . '</h3><p class="nx-muted">' . esc_html( $item[2] ) . '</p>';
		echo '<div class="nx-image" data-image><input type="hidden" name="' . esc_attr( $item[0] ) . '" value="' . esc_attr( $item[3] ) . '"><div class="nx-image__preview' . ( $src ? ' has-image' : '' ) . '">' . ( $src ? '<img src="' . esc_url( $src ) . '" alt="">' : nexora_admin_icon( 'image' ) ) . '</div><div class="nx-image__actions"><button type="button" class="button" data-image-select>' . esc_html__( 'Select image', 'nexora' ) . '</button> <button type="button" class="button-link-delete" data-image-remove' . ( $src ? '' : ' hidden' ) . '>' . esc_html__( 'Remove', 'nexora' ) . '</button></div></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div>';
}

function nexora_wizard_step_colors() {
	$active = nexora_active_preset_id();
	echo '<div class="nx-grid nx-grid--3 nx-presetpick">';
	foreach ( nexora_presets() as $id => $p ) {
		echo '<label class="nx-wizard__card nx-presetpick__item' . ( $id === $active ? ' is-active' : '' ) . '"><input type="radio" name="preset" value="' . esc_attr( $id ) . '"' . checked( $id, $active, false ) . '><span class="nx-preset__swatches">';
		foreach ( array( 'primary', 'accent', 'secondary', 'surface', 'text', 'danger' ) as $k ) {
			echo '<span style="background:' . esc_attr( $p['colors'][ $k ] ) . '"></span>';
		}
		echo '</span><strong>' . esc_html( $p['name'] ) . '</strong></label>';
	}
	echo '</div><p class="nx-muted">' . esc_html__( 'Fine-tune or create your own palette later under Colors & Presets.', 'nexora' ) . '</p>';
}

function nexora_wizard_step_commerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="nx-notice nx-notice--warning">' . esc_html__( 'WooCommerce is not active yet. Go back to the Plugins step, or skip and configure it later.', 'nexora' ) . '</div>';
		return;
	}
	$currencies = get_woocommerce_currencies();
	$cur        = get_woocommerce_currency();
	$country    = get_option( 'woocommerce_default_country', nexora_is_fa() ? 'IR' : 'US' );
	echo '<div class="nx-wizard__card">';
	echo '<div class="nx-field"><label class="nx-field__label" for="w-currency">' . esc_html__( 'Currency', 'nexora' ) . '</label><div class="nx-field__control"><select id="w-currency" name="currency">';
	foreach ( $currencies as $code => $label ) {
		printf( '<option value="%1$s"%2$s>%3$s (%1$s)</option>', esc_attr( $code ), selected( $code, $cur, false ), esc_html( $label ) );
	}
	echo '</select></div><p class="nx-field__desc">' . esc_html__( 'Iranian stores: choose IRT (Toman) or IRR (Rial). The theme formats prices with Persian digits automatically.', 'nexora' ) . '</p></div>';
	echo '<div class="nx-field"><label class="nx-field__label" for="w-country">' . esc_html__( 'Country / state', 'nexora' ) . '</label><div class="nx-field__control"><select id="w-country" name="country">';
	foreach ( WC()->countries->get_allowed_country_states() + WC()->countries->get_countries() as $code => $val ) {
		if ( is_array( $val ) ) {
			foreach ( $val as $sc => $sn ) {
				printf( '<option value="%1$s"%2$s>%3$s — %4$s</option>', esc_attr( $code . ':' . $sc ), selected( $code . ':' . $sc, $country, false ), esc_html( WC()->countries->get_countries()[ $code ] ?? $code ), esc_html( $sn ) );
			}
		} else {
			printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $code ), selected( $code, $country, false ), esc_html( $val ) );
		}
	}
	echo '</select></div></div>';
	printf( '<div class="nx-field"><label class="nx-field__label" for="w-city">%1$s</label><div class="nx-field__control"><input type="text" id="w-city" name="city" class="regular-text" value="%2$s"></div></div>', esc_html__( 'City', 'nexora' ), esc_attr( get_option( 'woocommerce_store_city' ) ) );
	printf( '<div class="nx-field"><label class="nx-field__label" for="w-addr">%1$s</label><div class="nx-field__control"><input type="text" id="w-addr" name="store_address" class="regular-text" value="%2$s"></div></div>', esc_html__( 'Street address', 'nexora' ), esc_attr( get_option( 'woocommerce_store_address' ) ) );
	echo '<div class="nx-field"><div class="nx-field__control"><label class="nx-check"><input type="checkbox" name="create_wc_pages" value="1" checked> ' . esc_html__( 'Create missing WooCommerce pages (Shop, Cart, Checkout, My Account)', 'nexora' ) . '</label></div></div>';
	echo '<div class="nx-field"><div class="nx-field__control"><label class="nx-check"><input type="checkbox" name="guest_checkout" value="1"' . checked( 'yes', get_option( 'woocommerce_enable_guest_checkout' ), false ) . '> ' . esc_html__( 'Allow guest checkout', 'nexora' ) . '</label></div></div>';
	echo '<div class="nx-notice nx-notice--info">' . nexora_admin_icon( 'lock' ) . esc_html__( 'Payment gateways and shipping rates are configured inside WooCommerce with your own credentials — the wizard never asks for them.', 'nexora' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) . '" target="_blank">' . esc_html__( 'Open payments', 'nexora' ) . '</a> · <a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ) . '" target="_blank">' . esc_html__( 'Open shipping', 'nexora' ) . '</a></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';
}

function nexora_wizard_step_demo() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="nx-notice nx-notice--warning">' . esc_html__( 'Demo content needs WooCommerce. Skip for now and import later from the Demo Import tab.', 'nexora' ) . '</div>';
		return;
	}
	$current = nexora_get_state( 'demo_id' );
	echo '<div class="nx-grid nx-grid--3">';
	foreach ( nexora_demos() as $id => $d ) {
		echo '<div class="nx-wizard__card nx-demo' . ( $id === $current ? ' is-installed' : '' ) . '" data-demo-card="' . esc_attr( $id ) . '"><img class="nx-demo__img" src="' . esc_url( NEXORA_URI . $d['preview'] ) . '" alt=""><h3>' . esc_html( $d['name'] ) . '</h3><p class="nx-muted">' . esc_html( $d['desc'] ) . '</p><div class="nx-demo__progress" hidden><div class="nx-progress"><span style="width:0%"></span></div><p class="nx-demo__status"></p></div>';
		echo '<button type="button" class="button' . ( $id === $current ? '' : ' button-primary' ) . '" data-demo-import="' . esc_attr( $id ) . '">' . ( $id === $current ? esc_html__( 'Installed', 'nexora' ) : esc_html__( 'Import', 'nexora' ) ) . '</button></div>';
	}
	echo '</div><p class="nx-muted">' . esc_html__( 'Import runs in the background on this page; wait for the green tick, then continue. Prefer an empty store? Just continue.', 'nexora' ) . '</p>';
}

function nexora_wizard_step_homepage() {
	$home = nexora_options( 'home' );
	echo '<div class="nx-wizard__card"><p>' . esc_html__( 'Tick the sections you want on the homepage. Order and content are edited later in Theme Settings → Homepage.', 'nexora' ) . '</p><div class="nx-checkgrid">';
	foreach ( nexora_home_section_labels() as $key => $label ) {
		printf( '<label class="nx-check"><input type="checkbox" name="sections[%1$s]" value="1"%2$s> %3$s</label>', esc_attr( $key ), checked( ! empty( $home[ $key . '_enable' ] ), true, false ), esc_html( $label ) );
	}
	echo '</div></div>';
}

function nexora_wizard_step_pages() {
	echo '<div class="nx-wizard__card"><p>' . esc_html__( 'The theme will create these pages if they do not exist yet, assign the right templates, and set the static homepage:', 'nexora' ) . '</p><ul class="nx-list">';
	foreach ( nexora_required_pages() as $key => $def ) {
		$id = (int) nexora_get_state( $key );
		echo '<li>' . esc_html( $def[0] ) . ' ' . ( $id && get_post( $id ) ? nexora_admin_pill( 'ok', __( 'exists', 'nexora' ) ) : nexora_admin_pill( 'muted', __( 'will be created', 'nexora' ) ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</ul><label class="nx-check"><input type="checkbox" name="create_menu" value="1" checked> ' . esc_html__( 'Also create a main menu with Home, Shop, Blog, FAQ and Contact (only if no menu is assigned)', 'nexora' ) . '</label></div>';
}

function nexora_wizard_step_finish() {
	$t = nexora_health_totals();
	echo '<div class="nx-wizard__card nx-wizard__finish">' . nexora_admin_icon( 'check', 'nx-icon--big' ) . '<h2>' . esc_html__( 'Your store is set up', 'nexora' ) . '</h2><p>' . esc_html( sprintf( /* translators: 1: ok 2: warn 3: bad */ __( 'Health check: %1$d passed, %2$d recommended, %3$d critical.', 'nexora' ), $t['ok'], $t['warn'], $t['bad'] ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="nx-quicklinks">';
	$links = array(
		array( 'external', __( 'View your site', 'nexora' ), home_url( '/' ) ),
		array( 'tag', __( 'Add a product', 'nexora' ), admin_url( 'post-new.php?post_type=product' ) ),
		array( 'credit', __( 'Set up payments', 'nexora' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ),
		array( 'truck', __( 'Set up shipping', 'nexora' ), admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ),
		array( 'home', __( 'Edit homepage', 'nexora' ), admin_url( 'admin.php?page=nexora-settings&tab=home' ) ),
		array( 'book', __( 'Read tutorials', 'nexora' ), admin_url( 'admin.php?page=nexora-tutorials' ) ),
	);
	foreach ( $links as $l ) {
		printf( '<a href="%1$s">%2$s<span>%3$s</span></a>', esc_url( $l[2] ), nexora_admin_icon( $l[0] ), esc_html( $l[1] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div></div>';
}
