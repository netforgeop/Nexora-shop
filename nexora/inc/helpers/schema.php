<?php
/**
 * Theme options schema — single source of truth.
 *
 * The admin UI (inc/admin/settings.php) renders this schema, the sanitizer
 * (inc/security/sanitize.php) validates against it and the front-end reads
 * values through nexora_option(). Adding a setting = adding one array entry.
 *
 * Field types: text, textarea, richtext, url, email, tel, number, toggle,
 * select, color, image, icon, link, menu, term, products, repeater, sortable,
 * notice.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * All option groups. Cached per request.
 *
 * @return array
 */
function nexora_schema() {
	static $schema = null;
	if ( null !== $schema ) {
		return $schema;
	}

	$link = static function ( $label, $text = '', $url = '' ) {
		return array(
			'type'    => 'link',
			'label'   => $label,
			'default' => array(
				'text'   => $text,
				'url'    => $url,
				'target' => '',
			),
		);
	};

	$feature_repeater = static function ( $label, $desc, $defaults ) {
		return array(
			'type'        => 'repeater',
			'label'       => $label,
			'description' => $desc,
			'max'         => 6,
			'fields'      => array(
				'icon'  => array(
					'type'    => 'icon',
					'label'   => __( 'Icon', 'nexora' ),
					'default' => 'truck',
				),
				'title' => array(
					'type'  => 'text',
					'label' => __( 'Title', 'nexora' ),
				),
				'text'  => array(
					'type'  => 'text',
					'label' => __( 'Text', 'nexora' ),
				),
			),
			'default'     => $defaults,
		);
	};

	$schema = array(

		/* ------------------------------------------------------------------ */
		'general' => array(
			'title'       => __( 'General', 'nexora' ),
			'icon'        => 'cog',
			'description' => __( 'Branding and contact details used across the whole site (header, footer, contact page, schema.org data).', 'nexora' ),
			'sections'    => array(
				'branding' => array(
					'title'       => __( 'Branding', 'nexora' ),
					'description' => __( 'The main logo is managed in Appearance → Customize → Site Identity (or the Setup Wizard). Here you can add a light version for dark backgrounds and a short tagline shown next to the logo.', 'nexora' ),
					'fields'      => array(
						'logo_light'      => array(
							'type'        => 'image',
							'label'       => __( 'Light logo (footer / dark areas)', 'nexora' ),
							'description' => __( 'Optional. Falls back to the main logo.', 'nexora' ),
						),
						'logo_text'       => array(
							'type'        => 'text',
							'label'       => __( 'Logo text', 'nexora' ),
							'description' => __( 'Shown when no image logo is uploaded.', 'nexora' ),
							'default'     => 'Nexora',
						),
						'tagline'         => array(
							'type'        => 'text',
							'label'       => __( 'Tagline under the logo', 'nexora' ),
							'default'     => __( 'Online store', 'nexora' ),
						),
						'show_brand_text' => array(
							'type'    => 'toggle',
							'label'   => __( 'Show text next to logo mark', 'nexora' ),
							'default' => true,
						),
					),
				),
				'contact'  => array(
					'title'       => __( 'Contact information', 'nexora' ),
					'description' => __( 'Used in the top bar, footer, contact page and mobile menu.', 'nexora' ),
					'fields'      => array(
						'phone'    => array(
							'type'    => 'tel',
							'label'   => __( 'Support phone', 'nexora' ),
							'default' => '021-91008800',
						),
						'email'    => array(
							'type'    => 'email',
							'label'   => __( 'Support email', 'nexora' ),
							'default' => 'support@example.com',
						),
						'address'  => array(
							'type'  => 'textarea',
							'label' => __( 'Address', 'nexora' ),
						),
						'hours'    => array(
							'type'    => 'text',
							'label'   => __( 'Working hours', 'nexora' ),
							'default' => __( 'Saturday to Thursday, 9:00 – 21:00', 'nexora' ),
						),
						'whatsapp' => array(
							'type'        => 'tel',
							'label'       => __( 'WhatsApp number', 'nexora' ),
							'description' => __( 'International format, e.g. 989120000000', 'nexora' ),
						),
						'map_embed' => array(
							'type'        => 'url',
							'label'       => __( 'Map embed URL', 'nexora' ),
							'description' => __( 'Google Maps / OpenStreetMap iframe URL for the contact page.', 'nexora' ),
						),
					),
				),
			),
		),

		/* ------------------------------------------------------------------ */
		'social'  => array(
			'title'       => __( 'Social networks', 'nexora' ),
			'icon'        => 'share2',
			'description' => __( 'Leave a field empty to hide that network. Icons are rendered from the theme SVG sprite.', 'nexora' ),
			'sections'    => array(
				'links' => array(
					'title'  => __( 'Profiles', 'nexora' ),
					'fields' => array(
						'instagram' => array( 'type' => 'url', 'label' => 'Instagram' ),
						'telegram'  => array( 'type' => 'url', 'label' => 'Telegram' ),
						'whatsapp'  => array( 'type' => 'url', 'label' => 'WhatsApp' ),
						'x'         => array( 'type' => 'url', 'label' => 'X (Twitter)' ),
						'linkedin'  => array( 'type' => 'url', 'label' => 'LinkedIn' ),
						'youtube'   => array( 'type' => 'url', 'label' => 'YouTube' ),
						'aparat'    => array( 'type' => 'url', 'label' => 'Aparat' ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------ */
		'header'  => array(
			'title'       => __( 'Header', 'nexora' ),
			'icon'        => 'layers',
			'description' => __( 'Announcement bar, top bar, main bar actions and the navigation row. Menus themselves are managed in Appearance → Menus.', 'nexora' ),
			'sections'    => array(
				'announcement' => array(
					'title'       => __( 'Announcement bar', 'nexora' ),
					'description' => __( 'A slim promotional strip above the header. Visitors can dismiss it; the choice is remembered in their browser.', 'nexora' ),
					'fields'      => array(
						'announcement_enable' => array(
							'type'    => 'toggle',
							'label'   => __( 'Enable announcement bar', 'nexora' ),
							'default' => true,
						),
						'announcement_icon'   => array(
							'type'    => 'icon',
							'label'   => __( 'Icon', 'nexora' ),
							'default' => 'truck',
						),
						'announcement_text'   => array(
							'type'    => 'text',
							'label'   => __( 'Text', 'nexora' ),
							'default' => __( 'Free shipping on orders over 2,000,000 Toman', 'nexora' ),
						),
						'announcement_link'   => $link( __( 'Link', 'nexora' ), __( 'Learn more', 'nexora' ), '' ),
					),
				),
				'topbar'       => array(
					'title'  => __( 'Top bar', 'nexora' ),
					'fields' => array(
						'topbar_enable'       => array(
							'type'    => 'toggle',
							'label'   => __( 'Enable top bar', 'nexora' ),
							'default' => true,
						),
						'topbar_support_text' => array(
							'type'    => 'text',
							'label'   => __( 'Support label', 'nexora' ),
							'default' => __( 'Support:', 'nexora' ),
						),
						'topbar_menu_note'    => array(
							'type'    => 'notice',
							'label'   => '',
							'content' => __( 'Links in the top bar come from the "Top bar (start)" and "Top bar (end)" menu locations in Appearance → Menus.', 'nexora' ),
						),
					),
				),
				'main'         => array(
					'title'  => __( 'Main bar', 'nexora' ),
					'fields' => array(
						'show_search'      => array( 'type' => 'toggle', 'label' => __( 'Show search box', 'nexora' ), 'default' => true ),
						'search_category'  => array( 'type' => 'toggle', 'label' => __( 'Category dropdown inside search', 'nexora' ), 'default' => true ),
						'show_account'     => array( 'type' => 'toggle', 'label' => __( 'Show account link', 'nexora' ), 'default' => true ),
						'show_compare'     => array( 'type' => 'toggle', 'label' => __( 'Show compare icon', 'nexora' ), 'default' => true ),
						'show_wishlist'    => array( 'type' => 'toggle', 'label' => __( 'Show wishlist icon', 'nexora' ), 'default' => true ),
						'show_cart'        => array( 'type' => 'toggle', 'label' => __( 'Show cart with mini-cart', 'nexora' ), 'default' => true ),
						'sticky'           => array( 'type' => 'toggle', 'label' => __( 'Sticky header on scroll', 'nexora' ), 'default' => true ),
						'header_button'    => $link( __( 'Extra header button (optional)', 'nexora' ) ),
					),
				),
				'nav'          => array(
					'title'       => __( 'Navigation row', 'nexora' ),
					'description' => __( 'The dark "Categories" button opens a mega menu built from your WooCommerce product categories.', 'nexora' ),
					'fields'      => array(
						'show_nav'          => array( 'type' => 'toggle', 'label' => __( 'Show navigation row', 'nexora' ), 'default' => true ),
						'show_cat_menu'     => array( 'type' => 'toggle', 'label' => __( 'Show categories mega menu', 'nexora' ), 'default' => true ),
						'cat_menu_label'    => array( 'type' => 'text', 'label' => __( 'Categories button label', 'nexora' ), 'default' => __( 'Product categories', 'nexora' ) ),
						'cat_menu_count'    => array( 'type' => 'number', 'label' => __( 'Number of top-level categories', 'nexora' ), 'default' => 8, 'min' => 1, 'max' => 20 ),
						'cat_menu_products' => array( 'type' => 'toggle', 'label' => __( 'Show best sellers column in mega menu', 'nexora' ), 'default' => true ),
						'nav_aside'         => array(
							'type'    => 'repeater',
							'label'   => __( 'Right-side highlights', 'nexora' ),
							'max'     => 3,
							'fields'  => array(
								'icon' => array( 'type' => 'icon', 'label' => __( 'Icon', 'nexora' ), 'default' => 'truck' ),
								'text' => array( 'type' => 'text', 'label' => __( 'Text', 'nexora' ) ),
								'url'  => array( 'type' => 'url', 'label' => __( 'URL', 'nexora' ) ),
							),
							'default' => array(
								array( 'icon' => 'truck', 'text' => __( 'Free shipping', 'nexora' ), 'url' => '' ),
								array( 'icon' => 'shield-check', 'text' => __( 'Authenticity guarantee', 'nexora' ), 'url' => '' ),
							),
						),
					),
				),
				'mobile'       => array(
					'title'  => __( 'Mobile', 'nexora' ),
					'fields' => array(
						'mobile_bar' => array( 'type' => 'toggle', 'label' => __( 'Show bottom tab bar on mobile', 'nexora' ), 'default' => true ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------ */
		'footer'  => array(
			'title'       => __( 'Footer', 'nexora' ),
			'icon'        => 'apartment',
			'description' => __( 'Feature strip, footer columns, newsletter, trust badges and the copyright row. Link columns use the footer menu locations.', 'nexora' ),
			'sections'    => array(
				'features'   => array(
					'title'  => __( 'Feature strip', 'nexora' ),
					'fields' => array(
						'features_enable' => array( 'type' => 'toggle', 'label' => __( 'Show feature strip', 'nexora' ), 'default' => true ),
						'features'        => $feature_repeater(
							__( 'Features', 'nexora' ),
							__( 'Four short trust signals shown above the footer.', 'nexora' ),
							array(
								array( 'icon' => 'truck', 'title' => __( 'Fast & free shipping', 'nexora' ), 'text' => __( 'On orders over the free-shipping threshold', 'nexora' ) ),
								array( 'icon' => 'undo', 'title' => __( '7-day returns', 'nexora' ), 'text' => __( 'No questions asked', 'nexora' ) ),
								array( 'icon' => 'shield-check', 'title' => __( 'Authenticity guarantee', 'nexora' ), 'text' => __( 'Original products with warranty', 'nexora' ) ),
								array( 'icon' => 'lock', 'title' => __( 'Secure payment', 'nexora' ), 'text' => __( 'Trusted bank gateways', 'nexora' ) ),
							)
						),
					),
				),
				'about'      => array(
					'title'  => __( 'About column', 'nexora' ),
					'fields' => array(
						'about'        => array(
							'type'    => 'textarea',
							'label'   => __( 'About text', 'nexora' ),
							'default' => __( 'A multi-category online store built for a simple, fast and trustworthy shopping experience.', 'nexora' ),
						),
						'show_contact' => array( 'type' => 'toggle', 'label' => __( 'Show contact details', 'nexora' ), 'default' => true ),
					),
				),
				'columns'    => array(
					'title'       => __( 'Link columns', 'nexora' ),
					'description' => __( 'Assign menus to "Footer column 1" and "Footer column 2" in Appearance → Menus. The categories column is generated from WooCommerce.', 'nexora' ),
					'fields'      => array(
						'col1_title'      => array( 'type' => 'text', 'label' => __( 'Column 1 title', 'nexora' ), 'default' => __( 'Quick links', 'nexora' ) ),
						'col2_title'      => array( 'type' => 'text', 'label' => __( 'Column 2 title', 'nexora' ), 'default' => __( 'Customer service', 'nexora' ) ),
						'cats_enable'     => array( 'type' => 'toggle', 'label' => __( 'Show categories column', 'nexora' ), 'default' => true ),
						'cats_title'      => array( 'type' => 'text', 'label' => __( 'Categories column title', 'nexora' ), 'default' => __( 'Categories', 'nexora' ) ),
						'cats_count'      => array( 'type' => 'number', 'label' => __( 'Categories to list', 'nexora' ), 'default' => 8, 'min' => 1, 'max' => 20 ),
					),
				),
				'newsletter' => array(
					'title'  => __( 'Newsletter', 'nexora' ),
					'fields' => array(
						'newsletter_enable'   => array( 'type' => 'toggle', 'label' => __( 'Show newsletter form', 'nexora' ), 'default' => true ),
						'newsletter_title'    => array( 'type' => 'text', 'label' => __( 'Title', 'nexora' ), 'default' => __( 'Join our newsletter', 'nexora' ) ),
						'newsletter_text'     => array( 'type' => 'text', 'label' => __( 'Text', 'nexora' ), 'default' => __( 'Be the first to hear about special offers and new arrivals.', 'nexora' ) ),
						'newsletter_action'   => array(
							'type'        => 'url',
							'label'       => __( 'Form action URL (optional)', 'nexora' ),
							'description' => __( 'Leave empty to store subscribers in the theme (Nexora → Dashboard shows the count and lets you export CSV). Enter a Mailchimp / Mailerlite form URL to post there instead.', 'nexora' ),
						),
						'apps_enable'         => array( 'type' => 'toggle', 'label' => __( 'Show app badges', 'nexora' ), 'default' => false ),
						'appstore_url'        => array( 'type' => 'url', 'label' => __( 'App Store URL', 'nexora' ) ),
						'googleplay_url'      => array( 'type' => 'url', 'label' => __( 'Google Play URL', 'nexora' ) ),
						'trust_html'          => array(
							'type'        => 'richtext',
							'label'       => __( 'Trust badges HTML', 'nexora' ),
							'description' => __( 'Paste the eNAMAD / Samandehi badge code here. Scripts are stripped for security; image + link badges work.', 'nexora' ),
						),
					),
				),
				'bottom'     => array(
					'title'  => __( 'Bottom row', 'nexora' ),
					'fields' => array(
						'copyright'     => array(
							'type'        => 'text',
							'label'       => __( 'Copyright text', 'nexora' ),
							'description' => __( 'Use {year} for the current year and {site} for the site name.', 'nexora' ),
							'default'     => __( '© {year} {site}. All rights reserved.', 'nexora' ),
						),
						'show_social'   => array( 'type' => 'toggle', 'label' => __( 'Show social icons', 'nexora' ), 'default' => true ),
						'show_payments' => array( 'type' => 'toggle', 'label' => __( 'Show payment icons', 'nexora' ), 'default' => true ),
						'payments'      => array(
							'type'    => 'select',
							'multiple' => true,
							'label'   => __( 'Payment icons', 'nexora' ),
							'options' => array(
								'shaparak'   => 'Shaparak',
								'visa'       => 'Visa',
								'mastercard' => 'Mastercard',
							),
							'default' => array( 'shaparak', 'visa', 'mastercard' ),
						),
					),
				),
			),
		),

		/* ------------------------------------------------------------------ */
		'home'    => array(
			'title'       => __( 'Homepage', 'nexora' ),
			'icon'        => 'home',
			'description' => __( 'Every homepage section can be switched on/off, re-ordered and edited. Products always come from WooCommerce; posts from the blog. Set a static front page in Settings → Reading (the Setup Wizard does this for you).', 'nexora' ),
			'sections'    => array(
				'order'       => array(
					'title'       => __( 'Section order', 'nexora' ),
					'description' => __( 'Drag to reorder. Disabled sections are skipped automatically.', 'nexora' ),
					'fields'      => array(
						'sections_order' => array(
							'type'    => 'sortable',
							'label'   => __( 'Order', 'nexora' ),
							'options' => nexora_home_section_labels(),
							'default' => array_keys( nexora_home_section_labels() ),
						),
					),
				),
				'hero'        => array(
					'title'  => __( 'Hero slider', 'nexora' ),
					'fields' => array(
						'hero_enable'   => array( 'type' => 'toggle', 'label' => __( 'Enable hero', 'nexora' ), 'default' => true ),
						'hero_autoplay' => array( 'type' => 'number', 'label' => __( 'Autoplay delay (seconds, 0 = off)', 'nexora' ), 'default' => 6, 'min' => 0, 'max' => 30 ),
						'hero_slides'   => array(
							'type'        => 'repeater',
							'label'       => __( 'Slides', 'nexora' ),
							'max'         => 8,
							'description' => __( 'Recommended image size 1600×680. Wrap a word in [mark]…[/mark] to highlight it.', 'nexora' ),
							'fields'      => array(
								'image'    => array( 'type' => 'image', 'label' => __( 'Image', 'nexora' ) ),
								'eyebrow'  => array( 'type' => 'text', 'label' => __( 'Eyebrow', 'nexora' ) ),
								'title'    => array( 'type' => 'text', 'label' => __( 'Title', 'nexora' ) ),
								'text'     => array( 'type' => 'textarea', 'label' => __( 'Text', 'nexora' ) ),
								'button1'  => $link( __( 'Primary button', 'nexora' ) ),
								'button2'  => $link( __( 'Secondary button', 'nexora' ) ),
							),
							'default'     => array(),
						),
						'hero_tiles'    => array(
							'type'   => 'repeater',
							'label'  => __( 'Side tiles', 'nexora' ),
							'max'    => 2,
							'fields' => array(
								'image'  => array( 'type' => 'image', 'label' => __( 'Image', 'nexora' ) ),
								'kicker' => array( 'type' => 'text', 'label' => __( 'Kicker', 'nexora' ) ),
								'title'  => array( 'type' => 'text', 'label' => __( 'Title', 'nexora' ) ),
								'link'   => $link( __( 'Link', 'nexora' ), __( 'Shop now', 'nexora' ) ),
								'dark'   => array( 'type' => 'toggle', 'label' => __( 'Dark style', 'nexora' ), 'default' => false ),
							),
							'default' => array(),
						),
					),
				),
				'trust'       => array(
					'title'  => __( 'Trust bar', 'nexora' ),
					'fields' => array(
						'trust_enable' => array( 'type' => 'toggle', 'label' => __( 'Enable trust bar', 'nexora' ), 'default' => true ),
						'trust_items'  => $feature_repeater(
							__( 'Items', 'nexora' ),
							'',
							array(
								array( 'icon' => 'truck', 'title' => __( 'Fast delivery', 'nexora' ), 'text' => __( '24 to 72 hours', 'nexora' ) ),
								array( 'icon' => 'credit-card', 'title' => __( 'Secure payment', 'nexora' ), 'text' => __( 'Trusted gateways', 'nexora' ) ),
								array( 'icon' => 'shield-check', 'title' => __( 'Authenticity', 'nexora' ), 'text' => __( 'Original with warranty', 'nexora' ) ),
								array( 'icon' => 'undo', 'title' => __( '7-day returns', 'nexora' ), 'text' => __( 'Unconditional', 'nexora' ) ),
							)
						),
					),
				),
				'categories'  => array(
					'title'  => __( 'Categories', 'nexora' ),
					'fields' => array(
						'categories_enable' => array( 'type' => 'toggle', 'label' => __( 'Enable', 'nexora' ), 'default' => true ),
						'categories_title'  => array( 'type' => 'text', 'label' => __( 'Title', 'nexora' ), 'default' => __( 'Shop by category', 'nexora' ) ),
						'categories_sub'    => array( 'type' => 'text', 'label' => __( 'Subtitle', 'nexora' ), 'default' => __( 'Everything you need, at a glance', 'nexora' ) ),
						'categories_count'  => array( 'type' => 'number', 'label' => __( 'Number of categories', 'nexora' ), 'default' => 8, 'min' => 2, 'max' => 16 ),
						'categories_ids'    => array( 'type' => 'term', 'taxonomy' => 'product_cat', 'multiple' => true, 'label' => __( 'Specific categories (optional)', 'nexora' ), 'description' => __( 'Leave empty to show the most populated top-level categories.', 'nexora' ) ),
					),
				),
				'featured'    => array(
					'title'  => __( 'Featured products', 'nexora' ),
					'fields' => nexora_schema_product_section( __( 'Featured products', 'nexora' ), __( 'Hand-picked by our team this week', 'nexora' ), 'featured' ),
				),
				'flash'       => array(
					'title'  => __( 'Flash sale', 'nexora' ),
					'fields' => array_merge(
						nexora_schema_product_section( __( 'Amazing offer', 'nexora' ), __( 'Do not miss out before time runs out', 'nexora' ), 'sale', 'flash' ),
						array(
							'flash_kicker'   => array( 'type' => 'text', 'label' => __( 'Card kicker', 'nexora' ), 'default' => __( 'Today only', 'nexora' ) ),
							'flash_headline' => array( 'type' => 'text', 'label' => __( 'Card headline', 'nexora' ), 'default' => __( 'Up to 40% off', 'nexora' ) ),
							'flash_text'     => array( 'type' => 'text', 'label' => __( 'Card text', 'nexora' ), 'default' => __( 'On selected electronics, fashion and home', 'nexora' ) ),
							'flash_end'      => array( 'type' => 'text', 'label' => __( 'Countdown end (YYYY-MM-DD HH:MM)', 'nexora' ), 'description' => __( 'Leave empty for a rolling daily countdown ending at midnight.', 'nexora' ) ),
							'flash_cta'      => $link( __( 'Card button', 'nexora' ), __( 'All offers', 'nexora' ) ),
						)
					),
				),
				'newest'      => array(
					'title'  => __( 'New arrivals', 'nexora' ),
					'fields' => nexora_schema_product_section( __( 'New arrivals', 'nexora' ), __( 'Fresh additions from this week', 'nexora' ), 'newest', 'newest', 'grid' ),
				),
				'promo'       => array(
					'title'  => __( 'Promo banners', 'nexora' ),
					'fields' => array(
						'promo_enable'  => array( 'type' => 'toggle', 'label' => __( 'Enable', 'nexora' ), 'default' => true ),
						'promo_banners' => array(
							'type'   => 'repeater',
							'label'  => __( 'Banners', 'nexora' ),
							'max'    => 4,
							'fields' => array(
								'image'  => array( 'type' => 'image', 'label' => __( 'Image (1200×600)', 'nexora' ) ),
								'kicker' => array( 'type' => 'text', 'label' => __( 'Kicker', 'nexora' ) ),
								'title'  => array( 'type' => 'text', 'label' => __( 'Title', 'nexora' ) ),
								'text'   => array( 'type' => 'text', 'label' => __( 'Text', 'nexora' ) ),
								'link'   => $link( __( 'Button', 'nexora' ), __( 'Shop now', 'nexora' ) ),
								'style'  => array( 'type' => 'select', 'label' => __( 'Style', 'nexora' ), 'options' => array( 'dark' => __( 'Dark', 'nexora' ), 'light' => __( 'Light', 'nexora' ) ), 'default' => 'dark' ),
							),
							'default' => array(),
						),
					),
				),
				'best'        => array(
					'title'  => __( 'Best sellers', 'nexora' ),
					'fields' => nexora_schema_product_section( __( 'Best sellers', 'nexora' ), __( 'Our customers\' favourite picks', 'nexora' ), 'best', 'best' ),
				),
				'collections' => array(
					'title'  => __( 'Collections (ranked lists)', 'nexora' ),
					'fields' => array(
						'collections_enable' => array( 'type' => 'toggle', 'label' => __( 'Enable', 'nexora' ), 'default' => true ),
						'collections_title'  => array( 'type' => 'text', 'label' => __( 'Title', 'nexora' ), 'default' => __( 'Recommended collections', 'nexora' ) ),
						'collections_sub'    => array( 'type' => 'text', 'label' => __( 'Subtitle', 'nexora' ), 'default' => __( 'Popular picks this season', 'nexora' ) ),
						'collections_count'  => array( 'type' => 'number', 'label' => __( 'Products per list', 'nexora' ), 'default' => 3, 'min' => 2, 'max' => 6 ),
					),
				),
				'tiles'       => array(
					'title'  => __( 'Category tiles', 'nexora' ),
					'fields' => array(
						'tiles_enable' => array( 'type' => 'toggle', 'label' => __( 'Enable', 'nexora' ), 'default' => true ),
						'tiles'        => array(
							'type'   => 'repeater',
							'label'  => __( 'Tiles', 'nexora' ),
							'max'    => 4,
							'fields' => array(
								'category' => array( 'type' => 'term', 'taxonomy' => 'product_cat', 'label' => __( 'Category', 'nexora' ) ),
								'image'    => array( 'type' => 'image', 'label' => __( 'Image (800×600)', 'nexora' ), 'description' => __( 'Falls back to the category thumbnail.', 'nexora' ) ),
							),
							'default' => array(),
						),
					),
				),
				'brands'      => array(
					'title'  => __( 'Brands', 'nexora' ),
					'fields' => array(
						'brands_enable' => array( 'type' => 'toggle', 'label' => __( 'Enable', 'nexora' ), 'default' => true ),
						'brands_title'  => array( 'type' => 'text', 'label' => __( 'Title', 'nexora' ), 'default' => __( 'Partner brands', 'nexora' ) ),
						'brands_sub'    => array( 'type' => 'text', 'label' => __( 'Subtitle', 'nexora' ), 'default' => __( 'Original products from trusted brands', 'nexora' ) ),
						'brands_source' => array(
							'type'        => 'select',
							'label'       => __( 'Source', 'nexora' ),
							'options'     => array(
								'taxonomy' => __( 'WooCommerce Brands taxonomy (product_brand)', 'nexora' ),
								'manual'   => __( 'Manual list below', 'nexora' ),
							),
							'default'     => 'taxonomy',
							'description' => __( 'WooCommerce 9.6+ ships a Brands taxonomy under Products → Brands.', 'nexora' ),
						),
						'brands_manual' => array(
							'type'   => 'repeater',
							'label'  => __( 'Manual brands', 'nexora' ),
							'max'    => 24,
							'fields' => array(
								'name' => array( 'type' => 'text', 'label' => __( 'Name', 'nexora' ) ),
								'logo' => array( 'type' => 'image', 'label' => __( 'Logo (optional)', 'nexora' ) ),
								'url'  => array( 'type' => 'url', 'label' => __( 'URL', 'nexora' ) ),
							),
							'default' => array(),
						),
					),
				),
				'stats'       => array(
					'title'  => __( 'Stats band', 'nexora' ),
					'fields' => array(
						'stats_enable' => array( 'type' => 'toggle', 'label' => __( 'Enable', 'nexora' ), 'default' => true ),
						'stats'        => array(
							'type'   => 'repeater',
							'label'  => __( 'Numbers', 'nexora' ),
							'max'    => 4,
							'fields' => array(
								'value' => array( 'type' => 'text', 'label' => __( 'Value', 'nexora' ) ),
								'label' => array( 'type' => 'text', 'label' => __( 'Label', 'nexora' ) ),
							),
							'default' => array(
								array( 'value' => '120K+', 'label' => __( 'Happy customers', 'nexora' ) ),
								array( 'value' => '45K+', 'label' => __( 'Products', 'nexora' ) ),
								array( 'value' => '98%', 'label' => __( 'Delivery satisfaction', 'nexora' ) ),
								array( 'value' => '24/7', 'label' => __( 'Online support', 'nexora' ) ),
							),
						),
					),
				),
				'reviews'     => array(
					'title'  => __( 'Testimonials', 'nexora' ),
					'fields' => array(
						'reviews_enable' => array( 'type' => 'toggle', 'label' => __( 'Enable', 'nexora' ), 'default' => true ),
						'reviews_title'  => array( 'type' => 'text', 'label' => __( 'Title', 'nexora' ), 'default' => __( 'What customers say', 'nexora' ) ),
						'reviews_sub'    => array( 'type' => 'text', 'label' => __( 'Subtitle', 'nexora' ), 'default' => __( 'Thousands of successful orders', 'nexora' ) ),
						'reviews_source' => array(
							'type'    => 'select',
							'label'   => __( 'Source', 'nexora' ),
							'options' => array(
								'woocommerce' => __( 'Latest approved WooCommerce product reviews', 'nexora' ),
								'manual'      => __( 'Manual testimonials below', 'nexora' ),
							),
							'default' => 'woocommerce',
						),
						'reviews_count'  => array( 'type' => 'number', 'label' => __( 'Number of reviews', 'nexora' ), 'default' => 6, 'min' => 1, 'max' => 12 ),
						'reviews_manual' => array(
							'type'   => 'repeater',
							'label'  => __( 'Manual testimonials', 'nexora' ),
							'max'    => 12,
							'fields' => array(
								'name'   => array( 'type' => 'text', 'label' => __( 'Name', 'nexora' ) ),
								'role'   => array( 'type' => 'text', 'label' => __( 'Role / city', 'nexora' ) ),
								'rating' => array( 'type' => 'number', 'label' => __( 'Rating (1-5)', 'nexora' ), 'default' => 5, 'min' => 1, 'max' => 5 ),
								'text'   => array( 'type' => 'textarea', 'label' => __( 'Text', 'nexora' ) ),
							),
							'default' => array(),
						),
					),
				),
				'blog'        => array(
					'title'  => __( 'Blog', 'nexora' ),
					'fields' => array(
						'blog_enable' => array( 'type' => 'toggle', 'label' => __( 'Enable', 'nexora' ), 'default' => true ),
						'blog_title'  => array( 'type' => 'text', 'label' => __( 'Title', 'nexora' ), 'default' => __( 'From the blog', 'nexora' ) ),
						'blog_sub'    => array( 'type' => 'text', 'label' => __( 'Subtitle', 'nexora' ), 'default' => __( 'Buying guides, reviews and trends', 'nexora' ) ),
						'blog_count'  => array( 'type' => 'number', 'label' => __( 'Number of posts', 'nexora' ), 'default' => 3, 'min' => 1, 'max' => 6 ),
					),
				),
				'newsletter'  => array(
					'title'  => __( 'Newsletter band', 'nexora' ),
					'fields' => array(
						'newsletter_enable' => array( 'type' => 'toggle', 'label' => __( 'Enable', 'nexora' ), 'default' => true ),
						'newsletter_title'  => array( 'type' => 'text', 'label' => __( 'Title', 'nexora' ), 'default' => __( 'Join our newsletter', 'nexora' ) ),
						'newsletter_text'   => array( 'type' => 'text', 'label' => __( 'Text', 'nexora' ), 'default' => __( 'Special discounts and new products, before anyone else.', 'nexora' ) ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------ */
		'shop'    => array(
			'title'       => __( 'Shop', 'nexora' ),
			'icon'        => 'store',
			'description' => __( 'Product archive layout, filters and product-card behaviour. Products, categories and stock are managed in WooCommerce → Products.', 'nexora' ),
			'sections'    => array(
				'layout'  => array(
					'title'  => __( 'Layout', 'nexora' ),
					'fields' => array(
						'per_page'   => array( 'type' => 'number', 'label' => __( 'Products per page', 'nexora' ), 'default' => 12, 'min' => 4, 'max' => 48 ),
						'columns'    => array( 'type' => 'select', 'label' => __( 'Columns (desktop)', 'nexora' ), 'options' => array( '3' => '3', '4' => '4', '5' => '5' ), 'default' => '4' ),
						'sidebar'    => array( 'type' => 'select', 'label' => __( 'Sidebar', 'nexora' ), 'options' => array( 'start' => __( 'Start (right in RTL)', 'nexora' ), 'end' => __( 'End', 'nexora' ), 'none' => __( 'None', 'nexora' ) ), 'default' => 'start' ),
						'banner'     => array( 'type' => 'toggle', 'label' => __( 'Show shop banner', 'nexora' ), 'default' => true ),
						'banner_img' => array( 'type' => 'image', 'label' => __( 'Banner image (1600×360)', 'nexora' ) ),
						'banner_title' => array( 'type' => 'text', 'label' => __( 'Banner title', 'nexora' ), 'default' => __( 'Shop', 'nexora' ) ),
						'banner_text'  => array( 'type' => 'text', 'label' => __( 'Banner text', 'nexora' ), 'default' => __( 'Thousands of original products in one place', 'nexora' ) ),
						'chips'      => array( 'type' => 'toggle', 'label' => __( 'Show category chips row', 'nexora' ), 'default' => true ),
						'sort_tabs'  => array( 'type' => 'toggle', 'label' => __( 'Show sort tabs (desktop)', 'nexora' ), 'default' => true ),
						'view_switch' => array( 'type' => 'toggle', 'label' => __( 'Show grid / list switch', 'nexora' ), 'default' => true ),
					),
				),
				'filters' => array(
					'title'       => __( 'Filters', 'nexora' ),
					'description' => __( 'The sidebar is a widget area (Appearance → Widgets → Shop sidebar). WooCommerce\'s own filter widgets/blocks work out of the box; the toggles below add the theme\'s native filters.', 'nexora' ),
					'fields'      => array(
						'filter_categories'   => array( 'type' => 'toggle', 'label' => __( 'Categories', 'nexora' ), 'default' => true ),
						'filter_price'        => array( 'type' => 'toggle', 'label' => __( 'Price range', 'nexora' ), 'default' => true ),
						'filter_brand'        => array( 'type' => 'toggle', 'label' => __( 'Brand', 'nexora' ), 'default' => true ),
						'filter_brand_attr'   => array( 'type' => 'text', 'label' => __( 'Brand attribute slug', 'nexora' ), 'default' => 'brand', 'description' => __( 'Uses the WooCommerce Brands taxonomy when available, otherwise the product attribute with this slug (pa_brand).', 'nexora' ) ),
						'filter_rating'       => array( 'type' => 'toggle', 'label' => __( 'Rating', 'nexora' ), 'default' => true ),
						'filter_color'        => array( 'type' => 'toggle', 'label' => __( 'Colour swatches', 'nexora' ), 'default' => true ),
						'filter_color_attr'   => array( 'type' => 'text', 'label' => __( 'Colour attribute slug', 'nexora' ), 'default' => 'color' ),
						'filter_stock'        => array( 'type' => 'toggle', 'label' => __( 'In stock / on sale switches', 'nexora' ), 'default' => true ),
					),
				),
				'cart'    => array(
					'title'  => __( 'Cart & checkout', 'nexora' ),
					'fields' => array(
						'free_shipping_min' => array( 'type' => 'number', 'label' => __( 'Free-shipping threshold (store currency)', 'nexora' ), 'description' => __( 'Shows a progress bar in the mini cart and cart page. Set 0 to hide. Configure the actual free-shipping rule in WooCommerce → Shipping.', 'nexora' ), 'default' => 0, 'min' => 0 ),
						'cart_coupon'       => array( 'type' => 'toggle', 'label' => __( 'Show coupon field in cart', 'nexora' ), 'default' => true ),
						'cart_suggestions'  => array( 'type' => 'toggle', 'label' => __( 'Show "You may also like" under the cart', 'nexora' ), 'default' => true ),
						'checkout_minimal'  => array( 'type' => 'toggle', 'label' => __( 'Minimal header/footer on checkout', 'nexora' ), 'default' => true ),
						'checkout_trust'    => array( 'type' => 'toggle', 'label' => __( 'Show trust notes beside the order summary', 'nexora' ), 'default' => true ),
					),
				),
				'card'    => array(
					'title'  => __( 'Product card', 'nexora' ),
					'fields' => array(
						'card_category'  => array( 'type' => 'toggle', 'label' => __( 'Show category', 'nexora' ), 'default' => true ),
						'card_rating'    => array( 'type' => 'toggle', 'label' => __( 'Show rating', 'nexora' ), 'default' => true ),
						'card_swatches'  => array( 'type' => 'toggle', 'label' => __( 'Show colour swatches (variable products)', 'nexora' ), 'default' => true ),
						'card_hover'     => array( 'type' => 'toggle', 'label' => __( 'Second image on hover', 'nexora' ), 'default' => true ),
						'card_quickview' => array( 'type' => 'toggle', 'label' => __( 'Quick view button', 'nexora' ), 'default' => true ),
						'card_wishlist'  => array( 'type' => 'toggle', 'label' => __( 'Wishlist button', 'nexora' ), 'default' => true ),
						'card_compare'   => array( 'type' => 'toggle', 'label' => __( 'Compare button', 'nexora' ), 'default' => true ),
						'card_badges'    => array( 'type' => 'toggle', 'label' => __( 'Discount / new / out-of-stock badges', 'nexora' ), 'default' => true ),
						'new_days'       => array( 'type' => 'number', 'label' => __( '"New" badge for products younger than (days)', 'nexora' ), 'default' => 14, 'min' => 0, 'max' => 90 ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------ */
		'product' => array(
			'title'       => __( 'Product page', 'nexora' ),
			'icon'        => 'tag',
			'description' => __( 'Single product layout. Specifications come from product attributes; reviews and Q&A use native WooCommerce comments.', 'nexora' ),
			'sections'    => array(
				'layout'    => array(
					'title'  => __( 'Layout', 'nexora' ),
					'fields' => array(
						'sticky_aside'  => array( 'type' => 'toggle', 'label' => __( 'Sticky buy box on very wide screens', 'nexora' ), 'default' => true ),
						'sticky_mobile' => array( 'type' => 'toggle', 'label' => __( 'Sticky buy bar on mobile', 'nexora' ), 'default' => true ),
						'show_brand'    => array( 'type' => 'toggle', 'label' => __( 'Show brand line', 'nexora' ), 'default' => true ),
						'show_sku'      => array( 'type' => 'toggle', 'label' => __( 'Show SKU', 'nexora' ), 'default' => true ),
						'show_share'    => array( 'type' => 'toggle', 'label' => __( 'Share buttons', 'nexora' ), 'default' => true ),
						'related_count' => array( 'type' => 'number', 'label' => __( 'Related products', 'nexora' ), 'default' => 8, 'min' => 0, 'max' => 12 ),
						'upsells_count' => array( 'type' => 'number', 'label' => __( 'Up-sells', 'nexora' ), 'default' => 4, 'min' => 0, 'max' => 12 ),
					),
				),
				'assurance' => array(
					'title'  => __( 'Assurance box', 'nexora' ),
					'fields' => array(
						'assurance_enable' => array( 'type' => 'toggle', 'label' => __( 'Show assurance items', 'nexora' ), 'default' => true ),
						'assurance'        => $feature_repeater(
							__( 'Items', 'nexora' ),
							'',
							array(
								array( 'icon' => 'shield-check', 'title' => __( 'Warranty', 'nexora' ), 'text' => __( '18-month official warranty', 'nexora' ) ),
								array( 'icon' => 'truck', 'title' => __( 'Shipping', 'nexora' ), 'text' => __( 'Delivery in 1-3 business days', 'nexora' ) ),
								array( 'icon' => 'undo', 'title' => __( 'Returns', 'nexora' ), 'text' => __( '7 days, no questions asked', 'nexora' ) ),
								array( 'icon' => 'lock', 'title' => __( 'Secure payment', 'nexora' ), 'text' => __( 'Trusted bank gateways', 'nexora' ) ),
							)
						),
					),
				),
				'seller'    => array(
					'title'  => __( 'Seller box', 'nexora' ),
					'fields' => array(
						'seller_enable' => array( 'type' => 'toggle', 'label' => __( 'Show seller box', 'nexora' ), 'default' => true ),
						'seller_name'   => array( 'type' => 'text', 'label' => __( 'Seller name', 'nexora' ), 'default' => '' ),
						'seller_meta'   => array( 'type' => 'text', 'label' => __( 'Seller meta', 'nexora' ), 'default' => __( 'Official seller · 98% satisfaction', 'nexora' ) ),
						'seller_link'   => $link( __( 'Details link', 'nexora' ), __( 'Details', 'nexora' ) ),
					),
				),
				'tabs'      => array(
					'title'  => __( 'Tabs', 'nexora' ),
					'fields' => array(
						'tab_specs'    => array( 'type' => 'toggle', 'label' => __( 'Specifications tab (from attributes)', 'nexora' ), 'default' => true ),
						'tab_shipping' => array( 'type' => 'toggle', 'label' => __( 'Shipping & returns tab', 'nexora' ), 'default' => true ),
						'tab_shipping_content' => array( 'type' => 'richtext', 'label' => __( 'Shipping & returns content', 'nexora' ) ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------ */
		'blog'    => array(
			'title'       => __( 'Blog', 'nexora' ),
			'icon'        => 'book',
			'description' => __( 'Posts archive and single post options. Content is managed in Posts.', 'nexora' ),
			'sections'    => array(
				'archive' => array(
					'title'  => __( 'Archive', 'nexora' ),
					'fields' => array(
						'heading'       => array( 'type' => 'text', 'label' => __( 'Heading', 'nexora' ), 'default' => __( 'Blog', 'nexora' ) ),
						'sub'           => array( 'type' => 'text', 'label' => __( 'Subheading', 'nexora' ), 'default' => __( 'Buying guides, reviews and trends', 'nexora' ) ),
						'sidebar'       => array( 'type' => 'select', 'label' => __( 'Sidebar', 'nexora' ), 'options' => array( 'end' => __( 'End', 'nexora' ), 'start' => __( 'Start', 'nexora' ), 'none' => __( 'None', 'nexora' ) ), 'default' => 'end' ),
						'featured'      => array( 'type' => 'toggle', 'label' => __( 'Show first sticky post as featured', 'nexora' ), 'default' => true ),
						'show_cats'     => array( 'type' => 'toggle', 'label' => __( 'Category pills', 'nexora' ), 'default' => true ),
						'show_author'   => array( 'type' => 'toggle', 'label' => __( 'Show author', 'nexora' ), 'default' => true ),
						'show_date'     => array( 'type' => 'toggle', 'label' => __( 'Show date', 'nexora' ), 'default' => true ),
						'show_readtime' => array( 'type' => 'toggle', 'label' => __( 'Show reading time', 'nexora' ), 'default' => true ),
					),
				),
				'single'  => array(
					'title'  => __( 'Single post', 'nexora' ),
					'fields' => array(
						'toc'            => array( 'type' => 'toggle', 'label' => __( 'Auto table of contents (from H2)', 'nexora' ), 'default' => true ),
						'author_box'     => array( 'type' => 'toggle', 'label' => __( 'Author box', 'nexora' ), 'default' => true ),
						'share'          => array( 'type' => 'toggle', 'label' => __( 'Share buttons', 'nexora' ), 'default' => true ),
						'prev_next'      => array( 'type' => 'toggle', 'label' => __( 'Previous / next navigation', 'nexora' ), 'default' => true ),
						'related_count'  => array( 'type' => 'number', 'label' => __( 'Related posts', 'nexora' ), 'default' => 3, 'min' => 0, 'max' => 6 ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------ */
		'typography' => array(
			'title'       => __( 'Typography', 'nexora' ),
			'icon'        => 'pencil',
			'description' => __( 'Fonts are bundled locally (no external requests). Colours are managed under Nexora → Colour presets.', 'nexora' ),
			'sections'    => array(
				'fonts' => array(
					'title'  => __( 'Fonts', 'nexora' ),
					'fields' => array(
						'body_font'    => array( 'type' => 'select', 'label' => __( 'Body font', 'nexora' ), 'options' => array( 'iranyekan' => 'IRANYekan', 'pinar' => 'Pinar', 'inter' => 'Inter (Latin)', 'system' => __( 'System UI', 'nexora' ) ), 'default' => 'iranyekan' ),
						'heading_font' => array( 'type' => 'select', 'label' => __( 'Display / heading font', 'nexora' ), 'options' => array( 'pinar' => 'Pinar', 'iranyekan' => 'IRANYekan', 'inter' => 'Inter (Latin)', 'system' => __( 'System UI', 'nexora' ) ), 'default' => 'pinar' ),
						'base_size'    => array( 'type' => 'number', 'label' => __( 'Base font size (px)', 'nexora' ), 'default' => 14, 'min' => 12, 'max' => 18 ),
						'radius'       => array( 'type' => 'select', 'label' => __( 'Corner radius', 'nexora' ), 'options' => array( 'sharp' => __( 'Sharp (default)', 'nexora' ), 'soft' => __( 'Soft', 'nexora' ), 'round' => __( 'Rounded', 'nexora' ) ), 'default' => 'sharp' ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------ */
		'pages' => array(
			'title'       => __( 'Pages', 'nexora' ),
			'icon'        => 'file-empty',
			'description' => __( 'Content for the special page templates (Contact, FAQ, Login) and the shared newsletter / auth visuals.', 'nexora' ),
			'sections'    => array(
				'contact' => array(
					'title'       => __( 'Contact page', 'nexora' ),
					'description' => __( 'Address, phone and hours come from General → Contact information.', 'nexora' ),
					'fields'      => array(
						'contact_intro'     => array( 'type' => 'textarea', 'label' => __( 'Intro text', 'nexora' ), 'default' => __( 'Our support team answers every day from 9 to 21. Send a message or call us.', 'nexora' ) ),
						'contact_shortcode' => array( 'type' => 'text', 'label' => __( 'Form shortcode (optional)', 'nexora' ), 'description' => __( 'Paste a Contact Form 7 / WPForms / Gravity Forms shortcode. Leave empty to use the built-in form (messages are emailed to the admin address).', 'nexora' ) ),
						'contact_map'       => array( 'type' => 'toggle', 'label' => __( 'Show map', 'nexora' ), 'default' => true ),
						'contact_faq'       => array( 'type' => 'toggle', 'label' => __( 'Show FAQ teaser', 'nexora' ), 'default' => true ),
					),
				),
				'faq'     => array(
					'title'  => __( 'FAQ page', 'nexora' ),
					'fields' => array(
						'faq_intro' => array( 'type' => 'text', 'label' => __( 'Intro text', 'nexora' ), 'default' => __( 'Answers to the most common questions about ordering, shipping and returns.', 'nexora' ) ),
						'faq_items' => array(
							'type'    => 'repeater',
							'label'   => __( 'Questions', 'nexora' ),
							'max'     => 40,
							'fields'  => array(
								'group'    => array( 'type' => 'text', 'label' => __( 'Group', 'nexora' ), 'default' => __( 'Orders', 'nexora' ) ),
								'question' => array( 'type' => 'text', 'label' => __( 'Question', 'nexora' ) ),
								'answer'   => array( 'type' => 'textarea', 'label' => __( 'Answer', 'nexora' ) ),
							),
							'default' => array(
								array( 'group' => __( 'Orders', 'nexora' ), 'question' => __( 'How can I track my order?', 'nexora' ), 'answer' => __( 'Go to My Account → Orders. Every order shows its status and the tracking code once shipped.', 'nexora' ) ),
								array( 'group' => __( 'Orders', 'nexora' ), 'question' => __( 'Can I change or cancel an order?', 'nexora' ), 'answer' => __( 'Orders can be cancelled from My Account until they are marked as processing. After that, contact support.', 'nexora' ) ),
								array( 'group' => __( 'Shipping', 'nexora' ), 'question' => __( 'How long does delivery take?', 'nexora' ), 'answer' => __( 'Tehran: 24 hours. Other cities: 2 to 4 working days.', 'nexora' ) ),
								array( 'group' => __( 'Returns', 'nexora' ), 'question' => __( 'What is the return policy?', 'nexora' ), 'answer' => __( 'Unused items in original packaging can be returned within 7 days for a full refund.', 'nexora' ) ),
								array( 'group' => __( 'Payment', 'nexora' ), 'question' => __( 'Which payment methods are accepted?', 'nexora' ), 'answer' => __( 'All Shaparak bank cards through the secure gateway, plus cash on delivery in selected cities.', 'nexora' ) ),
							),
						),
					),
				),
				'visuals' => array(
					'title'  => __( 'Shared visuals', 'nexora' ),
					'fields' => array(
						'auth_image'     => array( 'type' => 'image', 'label' => __( 'Login / register side image', 'nexora' ), 'description' => __( 'Portrait 900×1200 px. Falls back to a branded gradient.', 'nexora' ) ),
						'newsletter_bg'  => array( 'type' => 'image', 'label' => __( 'Newsletter band background', 'nexora' ) ),
						'megamenu_image' => array( 'type' => 'image', 'label' => __( 'Category menu promo image', 'nexora' ) ),
						'megamenu_link'  => $link( __( 'Category menu promo link', 'nexora' ), __( 'View offers', 'nexora' ) ),
						'notfound_text'  => array( 'type' => 'text', 'label' => __( '404 page text', 'nexora' ), 'default' => __( 'The page you are looking for has moved or never existed.', 'nexora' ) ),
					),
				),
			),
		),

		/* ------------------------------------------------------------------ */
		'performance' => array(
			'title'       => __( 'Performance', 'nexora' ),
			'icon'        => 'rocket',
			'description' => __( 'Safe front-end optimisations. All are reversible.', 'nexora' ),
			'sections'    => array(
				'assets' => array(
					'title'  => __( 'Assets', 'nexora' ),
					'fields' => array(
						'disable_emoji'      => array( 'type' => 'toggle', 'label' => __( 'Remove WordPress emoji script', 'nexora' ), 'default' => true ),
						'disable_block_css'  => array( 'type' => 'toggle', 'label' => __( 'Skip Gutenberg block CSS on pages without blocks', 'nexora' ), 'default' => true ),
						'woo_assets_scoped'  => array( 'type' => 'toggle', 'label' => __( 'Load WooCommerce scripts only on shop pages', 'nexora' ), 'default' => true ),
						'preload_fonts'      => array( 'type' => 'toggle', 'label' => __( 'Preload primary font files', 'nexora' ), 'default' => true ),
						'lazy_images'        => array( 'type' => 'toggle', 'label' => __( 'Native lazy loading for images', 'nexora' ), 'default' => true ),
						'defer_js'           => array( 'type' => 'toggle', 'label' => __( 'Defer theme JavaScript', 'nexora' ), 'default' => true ),
					),
				),
			),
		),
	);

	return apply_filters( 'nexora_schema', $schema );
}

/**
 * Labels for reorderable homepage sections.
 *
 * @return array
 */
function nexora_home_section_labels() {
	return array(
		'hero'        => __( 'Hero slider', 'nexora' ),
		'trust'       => __( 'Trust bar', 'nexora' ),
		'categories'  => __( 'Categories', 'nexora' ),
		'featured'    => __( 'Featured products', 'nexora' ),
		'flash'       => __( 'Flash sale', 'nexora' ),
		'newest'      => __( 'New arrivals', 'nexora' ),
		'promo'       => __( 'Promo banners', 'nexora' ),
		'best'        => __( 'Best sellers', 'nexora' ),
		'collections' => __( 'Collections', 'nexora' ),
		'tiles'       => __( 'Category tiles', 'nexora' ),
		'brands'      => __( 'Brands', 'nexora' ),
		'stats'       => __( 'Stats band', 'nexora' ),
		'reviews'     => __( 'Testimonials', 'nexora' ),
		'blog'        => __( 'Blog', 'nexora' ),
		'newsletter'  => __( 'Newsletter band', 'nexora' ),
	);
}

/**
 * Shared fields for a product-list homepage section.
 *
 * @param string $title   Default title.
 * @param string $sub     Default subtitle.
 * @param string $source  Default source.
 * @param string $prefix  Field prefix.
 * @param string $layout  Default layout.
 * @return array
 */
function nexora_schema_product_section( $title, $sub, $source, $prefix = 'featured', $layout = 'carousel' ) {
	return array(
		$prefix . '_enable' => array( 'type' => 'toggle', 'label' => __( 'Enable', 'nexora' ), 'default' => true ),
		$prefix . '_title'  => array( 'type' => 'text', 'label' => __( 'Title', 'nexora' ), 'default' => $title ),
		$prefix . '_sub'    => array( 'type' => 'text', 'label' => __( 'Subtitle', 'nexora' ), 'default' => $sub ),
		$prefix . '_source' => array(
			'type'    => 'select',
			'label'   => __( 'Product source', 'nexora' ),
			'options' => array(
				'featured' => __( 'Featured products (star in product list)', 'nexora' ),
				'sale'     => __( 'On sale', 'nexora' ),
				'newest'   => __( 'Newest', 'nexora' ),
				'best'     => __( 'Best selling', 'nexora' ),
				'rating'   => __( 'Top rated', 'nexora' ),
				'category' => __( 'From a category', 'nexora' ),
				'manual'   => __( 'Hand-picked products', 'nexora' ),
			),
			'default' => $source,
		),
		$prefix . '_category' => array( 'type' => 'term', 'taxonomy' => 'product_cat', 'label' => __( 'Category', 'nexora' ), 'show_if' => array( $prefix . '_source', 'category' ) ),
		$prefix . '_products' => array( 'type' => 'products', 'label' => __( 'Products', 'nexora' ), 'show_if' => array( $prefix . '_source', 'manual' ) ),
		$prefix . '_count'    => array( 'type' => 'number', 'label' => __( 'Number of products', 'nexora' ), 'default' => 8, 'min' => 2, 'max' => 24 ),
		$prefix . '_layout'   => array( 'type' => 'select', 'label' => __( 'Layout', 'nexora' ), 'options' => array( 'carousel' => __( 'Carousel', 'nexora' ), 'grid' => __( 'Grid', 'nexora' ) ), 'default' => $layout ),
		$prefix . '_columns'  => array( 'type' => 'select', 'label' => __( 'Columns', 'nexora' ), 'options' => array( '3' => '3', '4' => '4', '5' => '5' ), 'default' => '4', 'show_if' => array( $prefix . '_layout', 'grid' ) ),
		$prefix . '_link'     => array( 'type' => 'url', 'label' => __( '"View all" URL (optional)', 'nexora' ), 'description' => __( 'Defaults to the shop page with a matching sort/filter.', 'nexora' ) ),
	);
}
