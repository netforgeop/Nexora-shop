<?php
/**
 * Customizer: a light bridge. The full option panel lives under Nexora → Settings;
 * here we expose the most common branding fields plus a link to the panel so
 * users who start in the Customizer are not lost.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

function nexora_customize_register( WP_Customize_Manager $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	$wp_customize->add_section(
		'nexora_quick',
		array(
			'title'       => __( 'Nexora quick settings', 'nexora' ),
			'priority'    => 5,
			'description' => sprintf(
				/* translators: %s: link to the theme panel */
				__( 'Colour presets, header, footer, homepage sections and shop options are all in %s.', 'nexora' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=nexora' ) ) . '">' . esc_html__( 'Nexora → Dashboard', 'nexora' ) . '</a>'
			),
		)
	);

	$wp_customize->add_setting(
		'nexora_active_preset_customizer',
		array(
			'default'           => nexora_active_preset_id(),
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_key',
		)
	);
	$choices = array();
	foreach ( nexora_presets() as $id => $preset ) {
		$choices[ $id ] = $preset['name'];
	}
	$wp_customize->add_control(
		'nexora_active_preset_customizer',
		array(
			'section' => 'nexora_quick',
			'label'   => __( 'Colour preset', 'nexora' ),
			'type'    => 'select',
			'choices' => $choices,
		)
	);

	$wp_customize->add_setting(
		'nexora_popular_searches',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'capability'        => 'edit_theme_options',
		)
	);
	$wp_customize->add_control(
		'nexora_popular_searches',
		array(
			'section'     => 'nexora_quick',
			'label'       => __( 'Popular search terms', 'nexora' ),
			'description' => __( 'Comma-separated. Shown as chips when the search box is focused.', 'nexora' ),
			'type'        => 'text',
		)
	);

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.brand__text > span:first-child',
				'render_callback' => static function () {
					bloginfo( 'name' );
				},
			)
		);
	}
}
add_action( 'customize_register', 'nexora_customize_register' );

/**
 * Sync the customizer preset select with the real active preset option.
 */
function nexora_customize_save_preset( $value ) {
	if ( $value && isset( nexora_presets()[ $value ] ) ) {
		update_option( 'nexora_active_preset', $value );
	}
}
add_action( 'update_option_nexora_active_preset_customizer', static function ( $old, $new ) { nexora_customize_save_preset( $new ); }, 10, 2 );
add_action( 'add_option_nexora_active_preset_customizer', static function ( $option, $new ) { nexora_customize_save_preset( $new ); }, 10, 2 );

/**
 * Live-preview JS (site title only, preset requires refresh).
 */
function nexora_customize_preview_js() {
	wp_enqueue_script( 'nexora-customizer', NEXORA_URI . 'assets/js/customizer.js', array( 'customize-preview' ), NEXORA_VERSION, true );
}
add_action( 'customize_preview_init', 'nexora_customize_preview_js' );
