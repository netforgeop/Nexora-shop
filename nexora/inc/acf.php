<?php
/**
 * Optional ACF / Secure Custom Fields integration.
 *
 * Theme options live in the native schema-driven options screen (works with
 * or without ACF). When ACF/SCF is active we additionally register:
 *  - product extras (highlights, video, size guide, badge text)
 *  - page banner (title override, subtitle, background image)
 *  - post extras (featured video, reading time)
 * All fields have fallbacks in templates (get_post_meta based), so deactivating
 * ACF never breaks rendering — only the editing UI disappears.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

add_action(
	'acf/init',
	static function () {
		acf_add_local_field_group(
			array(
				'key'      => 'group_nexora_product',
				'title'    => __( 'Nexora — Product extras', 'nexora' ),
				'fields'   => array(
					array(
						'key'          => 'field_nexora_highlights',
						'label'        => __( 'Highlights', 'nexora' ),
						'name'         => 'nexora_highlights',
						'type'         => 'textarea',
						'rows'         => 5,
						'instructions' => __( 'One highlight per line. Shown as a bullet list beside the product gallery (max 6).', 'nexora' ),
					),
					array(
						'key'          => 'field_nexora_badge',
						'label'        => __( 'Custom badge', 'nexora' ),
						'name'         => 'nexora_badge',
						'type'         => 'text',
						'maxlength'    => 20,
						'instructions' => __( 'Short label such as "Best seller". Leave empty to hide.', 'nexora' ),
					),
					array(
						'key'          => 'field_nexora_video',
						'label'        => __( 'Video URL', 'nexora' ),
						'name'         => 'nexora_video',
						'type'         => 'url',
						'instructions' => __( 'YouTube / Aparat / MP4 link — appears as the last gallery slide.', 'nexora' ),
					),
					array(
						'key'          => 'field_nexora_size_guide',
						'label'        => __( 'Size guide', 'nexora' ),
						'name'         => 'nexora_size_guide',
						'type'         => 'wysiwyg',
						'tabs'         => 'visual',
						'media_upload' => 1,
						'instructions' => __( 'Optional table shown in a modal via the "Size guide" link.', 'nexora' ),
					),
				),
				'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ) ) ),
				'position' => 'normal',
			)
		);

		acf_add_local_field_group(
			array(
				'key'      => 'group_nexora_page',
				'title'    => __( 'Nexora — Page banner', 'nexora' ),
				'fields'   => array(
					array(
						'key'   => 'field_nexora_page_hide_title',
						'label' => __( 'Hide page title', 'nexora' ),
						'name'  => 'nexora_hide_title',
						'type'  => 'true_false',
						'ui'    => 1,
					),
					array(
						'key'   => 'field_nexora_page_subtitle',
						'label' => __( 'Subtitle', 'nexora' ),
						'name'  => 'nexora_subtitle',
						'type'  => 'text',
					),
					array(
						'key'           => 'field_nexora_page_bg',
						'label'         => __( 'Banner image', 'nexora' ),
						'name'          => 'nexora_banner',
						'type'          => 'image',
						'return_format' => 'id',
						'preview_size'  => 'medium',
					),
				),
				'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ) ),
				'position' => 'side',
			)
		);

		acf_add_local_field_group(
			array(
				'key'      => 'group_nexora_post',
				'title'    => __( 'Nexora — Post extras', 'nexora' ),
				'fields'   => array(
					array(
						'key'   => 'field_nexora_post_video',
						'label' => __( 'Featured video URL', 'nexora' ),
						'name'  => 'nexora_video',
						'type'  => 'url',
					),
					array(
						'key'   => 'field_nexora_post_featured',
						'label' => __( 'Mark as editor’s pick', 'nexora' ),
						'name'  => 'nexora_featured',
						'type'  => 'true_false',
						'ui'    => 1,
					),
				),
				'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ),
				'position' => 'side',
			)
		);
	}
);
