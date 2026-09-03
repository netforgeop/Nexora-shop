<?php
/**
 * Public AJAX endpoints (nonce + sanitisation on every request).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Verify the front-end nonce or die with 403.
 */
function nexora_ajax_check() {
	if ( ! check_ajax_referer( 'nexora_front', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please reload the page.', 'nexora' ) ), 403 );
	}
}

/**
 * Newsletter subscribe (stored locally; export from Nexora → Dashboard).
 */
function nexora_ajax_newsletter() {
	nexora_ajax_check();
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email.', 'nexora' ) ), 400 );
	}
	// Honeypot.
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success();
	}
	// Simple rate limit per IP: 5 / hour.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$key = 'nexora_nl_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 5 ) {
		wp_send_json_error( array( 'message' => __( 'Too many attempts, please try again later.', 'nexora' ) ), 429 );
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );

	$list = get_option( 'nexora_subscribers', array() );
	$list = is_array( $list ) ? $list : array();
	if ( ! isset( $list[ $email ] ) ) {
		$list[ $email ] = current_time( 'mysql' );
		update_option( 'nexora_subscribers', $list, false );
		do_action( 'nexora_newsletter_subscribed', $email );
	}
	wp_send_json_success( array( 'message' => __( 'You are subscribed. Thank you!', 'nexora' ) ) );
}
add_action( 'wp_ajax_nexora_newsletter', 'nexora_ajax_newsletter' );
add_action( 'wp_ajax_nopriv_nexora_newsletter', 'nexora_ajax_newsletter' );

/**
 * Live search suggestions (products first, then posts).
 */
function nexora_ajax_suggest() {
	nexora_ajax_check();
	$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$q = trim( $q );
	if ( mb_strlen( $q ) < 2 ) {
		wp_send_json_success( array( 'items' => array() ) );
	}
	$items = array();
	if ( class_exists( 'WooCommerce' ) ) {
		$cat  = isset( $_GET['cat'] ) ? sanitize_title( wp_unslash( $_GET['cat'] ) ) : '';
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			's'              => $q,
			'posts_per_page' => 6,
			'no_found_rows'  => true,
			'fields'         => 'ids',
		);
		if ( $cat ) {
			$args['tax_query'] = array( array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $cat ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}
		$ids = get_posts( $args );
		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}
			$cats    = wc_get_product_category_list( $id, ', ' );
			$items[] = array(
				'title'    => $product->get_name(),
				'url'      => $product->get_permalink(),
				'image'    => wp_get_attachment_image_url( $product->get_image_id(), 'nexora-thumb' ) ?: wc_placeholder_img_src( 'nexora-thumb' ),
				'price'    => $product->get_price_html(),
				'category' => wp_strip_all_tags( $cats ),
			);
		}
	}
	wp_send_json_success(
		array(
			'items' => $items,
			'more'  => add_query_arg( array( 's' => rawurlencode( $q ), 'post_type' => class_exists( 'WooCommerce' ) ? 'product' : '' ), home_url( '/' ) ),
		)
	);
}
add_action( 'wp_ajax_nexora_suggest', 'nexora_ajax_suggest' );
add_action( 'wp_ajax_nopriv_nexora_suggest', 'nexora_ajax_suggest' );

/**
 * Built-in contact form → email to the admin (or General → Email). Nonce, honeypot, rate limit.
 */
function nexora_ajax_contact() {
	if ( ! check_ajax_referer( 'nexora_contact', 'nexora_contact_nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please reload the page.', 'nexora' ) ), 403 );
	}
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success( array( 'message' => __( 'Thanks! Your message has been sent.', 'nexora' ) ) );
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$key = 'nexora_ct_' . md5( $ip );
	if ( (int) get_transient( $key ) >= 3 ) {
		wp_send_json_error( array( 'message' => __( 'Too many messages, please try again later.', 'nexora' ) ), 429 );
	}
	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$errors  = array();
	if ( mb_strlen( $name ) < 3 ) {
		$errors['name'] = __( 'Please enter your name.', 'nexora' );
	}
	if ( ! is_email( $email ) ) {
		$errors['email'] = __( 'Please enter a valid email.', 'nexora' );
	}
	if ( '' === $subject ) {
		$errors['subject'] = __( 'Please choose a subject.', 'nexora' );
	}
	if ( mb_strlen( $message ) < 20 ) {
		$errors['message'] = __( 'Please write at least 20 characters.', 'nexora' );
	}
	if ( $errors ) {
		wp_send_json_error( array( 'errors' => $errors, 'message' => __( 'Please fix the highlighted fields.', 'nexora' ) ), 400 );
	}
	set_transient( $key, (int) get_transient( $key ) + 1, HOUR_IN_SECONDS );

	$to   = nexora_option( 'general', 'email' ) ?: get_option( 'admin_email' );
	$body = sprintf( "%s: %s\n%s: %s\n%s: %s\n%s: %s\n\n%s\n\n--\n%s %s", __( 'Name', 'nexora' ), $name, __( 'Email', 'nexora' ), $email, __( 'Phone', 'nexora' ), $phone, __( 'Subject', 'nexora' ), $subject, $message, __( 'IP', 'nexora' ), $ip );
	$sent = wp_mail( $to, sprintf( '[%s] %s', wp_specialchars_decode( get_bloginfo( 'name' ) ), $subject ), $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
	do_action( 'nexora_contact_message', compact( 'name', 'email', 'phone', 'subject', 'message' ), $sent );
	if ( ! $sent ) {
		wp_send_json_error( array( 'message' => __( 'The message could not be sent right now. Please call us instead.', 'nexora' ) ), 500 );
	}
	wp_send_json_success( array( 'message' => __( 'Thanks! Your message has been sent.', 'nexora' ) ) );
}
add_action( 'wp_ajax_nexora_contact', 'nexora_ajax_contact' );
add_action( 'wp_ajax_nopriv_nexora_contact', 'nexora_ajax_contact' );
