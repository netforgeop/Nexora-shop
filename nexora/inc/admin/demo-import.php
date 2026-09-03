<?php
/**
 * Demo import — three stores built from bundled JSON + local images.
 *
 * - Administrators only (manage_options + install-style nonce).
 * - Runs in AJAX batches (categories → brands → products → reviews → posts → settings).
 * - Every created object is tagged with `_nexora_demo` meta so "Remove demo
 *   content" deletes exactly what was imported and nothing else.
 * - No remote downloads: images are copied from the theme's assets folder into
 *   the media library through the WordPress upload API.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Demo definitions.
 *
 * @return array
 */
function nexora_demos() {
	return array(
		'fashion'     => array(
			'name'       => __( 'Modern Fashion', 'nexora' ),
			'desc'       => __( 'Apparel, shoes, accessories and beauty. Editorial hero, lookbook tiles, Luxury Green preset.', 'nexora' ),
			'categories' => array( 'fashion', 'beauty' ),
			'preset'     => 'luxury-green',
			'preview'    => 'assets/img/banners/promo-fashion.webp',
			'hero'       => array( 'hero-2', 'hero-3' ),
		),
		'electronics' => array(
			'name'       => __( 'Electronics', 'nexora' ),
			'desc'       => __( 'Phones, laptops, audio and cameras. Spec-heavy product pages, compare table, Modern Blue preset.', 'nexora' ),
			'categories' => array( 'digital' ),
			'preset'     => 'modern-blue',
			'preview'    => 'assets/img/banners/promo-digital.webp',
			'hero'       => array( 'hero-1', 'hero-2' ),
		),
		'general'     => array(
			'name'       => __( 'General Store', 'nexora' ),
			'desc'       => __( 'Everything: electronics, fashion, home, beauty and sport. The full homepage with all 15 sections, Classic Red preset.', 'nexora' ),
			'categories' => array( 'digital', 'fashion', 'home', 'beauty', 'sport' ),
			'preset'     => 'classic-red',
			'preview'    => 'assets/img/banners/promo-home.webp',
			'hero'       => array( 'hero-1', 'hero-2', 'hero-3' ),
		),
	);
}

/**
 * Load a JSON data file.
 *
 * @param string $name File name without extension.
 * @return array
 */
function nexora_demo_data( $name ) {
	static $cache = array();
	if ( ! isset( $cache[ $name ] ) ) {
		$file = NEXORA_DIR . 'inc/demo/data/' . sanitize_file_name( $name ) . '.json';
		$json = file_exists( $file ) ? file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
		$data = json_decode( (string) $json, true );
		$cache[ $name ] = is_array( $data ) ? $data : array();
	}
	return $cache[ $name ];
}

/**
 * Localised string from {fa,en} pair.
 *
 * @param mixed $v Value.
 * @return string
 */
function nexora_demo_str( $v ) {
	if ( is_array( $v ) ) {
		$lang = nexora_is_fa() ? 'fa' : 'en';
		return (string) ( $v[ $lang ] ?? reset( $v ) );
	}
	return (string) $v;
}

/**
 * Sideload a theme image into the media library (cached per path).
 *
 * @param string $rel   Path relative to assets/img.
 * @param string $title Attachment title.
 * @param string $demo  Demo id.
 * @return int Attachment id or 0.
 */
function nexora_demo_image( $rel, $title, $demo ) {
	$rel  = ltrim( str_replace( '..', '', $rel ), '/' );
	$path = NEXORA_DIR . 'assets/img/' . $rel;
	if ( ! file_exists( $path ) ) {
		return 0;
	}
	$existing = get_posts( array( 'post_type' => 'attachment', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_nexora_demo_src', 'meta_value' => $rel ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( $existing ) {
		return (int) $existing[0];
	}
	$type = wp_check_filetype( basename( $path ) );
	if ( empty( $type['type'] ) || strpos( $type['type'], 'image/' ) !== 0 ) {
		return 0;
	}
	$upload = wp_upload_bits( basename( $path ), null, file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => $type['type'],
			'post_title'     => sanitize_text_field( $title ),
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $title ) );
	update_post_meta( $id, '_nexora_demo', $demo );
	update_post_meta( $id, '_nexora_demo_src', $rel );
	return (int) $id;
}

/**
 * Ensure a term exists (tagged as demo when created).
 *
 * @param string $tax    Taxonomy.
 * @param string $name   Name.
 * @param string $slug   Slug.
 * @param string $demo   Demo id.
 * @param int    $parent Parent.
 * @return int
 */
function nexora_demo_term( $tax, $name, $slug, $demo, $parent = 0 ) {
	$term = get_term_by( 'slug', $slug, $tax );
	if ( $term ) {
		return (int) $term->term_id;
	}
	$res = wp_insert_term( $name, $tax, array( 'slug' => $slug, 'parent' => $parent ) );
	if ( is_wp_error( $res ) ) {
		return 0;
	}
	update_term_meta( $res['term_id'], '_nexora_demo', $demo );
	return (int) $res['term_id'];
}

/**
 * Import steps for a demo.
 *
 * @return array step => label
 */
function nexora_demo_steps() {
	return array(
		'categories' => __( 'Product categories', 'nexora' ),
		'brands'     => __( 'Brands & attributes', 'nexora' ),
		'products'   => __( 'Products & images', 'nexora' ),
		'reviews'    => __( 'Reviews', 'nexora' ),
		'posts'      => __( 'Blog posts', 'nexora' ),
		'settings'   => __( 'Homepage & theme settings', 'nexora' ),
	);
}

/**
 * Run one step (batched for products).
 *
 * @param string $demo  Demo id.
 * @param string $step  Step.
 * @param int    $offset Offset for batched steps.
 * @return array [done(bool), next_offset, message]
 */
function nexora_demo_run_step( $demo, $step, $offset = 0 ) {
	$def = nexora_demos()[ $demo ];
	switch ( $step ) {
		case 'categories':
			foreach ( nexora_demo_data( 'categories' ) as $cat ) {
				if ( ! in_array( $cat['slug'], $def['categories'], true ) ) {
					continue;
				}
				$id = nexora_demo_term( 'product_cat', nexora_demo_str( $cat['name'] ), $cat['slug'], $demo );
				if ( $id && ! empty( $cat['image'] ) ) {
					$img = nexora_demo_image( $cat['image'], nexora_demo_str( $cat['name'] ), $demo );
					if ( $img ) {
						update_term_meta( $id, 'thumbnail_id', $img );
					}
				}
				foreach ( (array) ( $cat['children'] ?? array() ) as $child ) {
					nexora_demo_term( 'product_cat', nexora_demo_str( $child['name'] ), $child['slug'], $demo, $id );
				}
			}
			return array( true, 0, __( 'Categories created.', 'nexora' ) );

		case 'brands':
			$brand_tax = taxonomy_exists( 'product_brand' ) ? 'product_brand' : '';
			if ( ! $brand_tax ) {
				$brand_tax = nexora_demo_attribute( 'brand', __( 'Brand', 'nexora' ) );
			}
			foreach ( nexora_demo_data( 'brands' ) as $b ) {
				if ( ! in_array( $b['category'], $def['categories'], true ) ) {
					continue;
				}
				nexora_demo_term( $brand_tax, $b['name'], $b['slug'], $demo );
			}
			nexora_demo_attribute( 'color', __( 'Colour', 'nexora' ) );
			nexora_demo_attribute( 'size', __( 'Size', 'nexora' ) );
			nexora_set_state( 'demo_brand_tax', $brand_tax );
			return array( true, 0, __( 'Brands and attributes ready.', 'nexora' ) );

		case 'products':
			$all   = array_values( array_filter( nexora_demo_data( 'products' ), static function ( $p ) use ( $def ) { return in_array( $p['category'], $def['categories'], true ); } ) );
			$batch = array_slice( $all, $offset, 4 );
			foreach ( $batch as $p ) {
				nexora_demo_import_product( $p, $demo );
			}
			$next = $offset + 4;
			/* translators: 1: done 2: total */
			return array( $next >= count( $all ), $next, sprintf( __( 'Products %1$d / %2$d', 'nexora' ), min( $next, count( $all ) ), count( $all ) ) );

		case 'reviews':
			$reviews = nexora_demo_data( 'reviews' );
			$map     = nexora_get_state( 'demo_product_map', array() );
			foreach ( $map as $src_id => $pid ) {
				$list = $reviews['byProduct'][ (string) $src_id ] ?? array_slice( $reviews['generic'] ?? array(), 0, 3 );
				foreach ( $list as $r ) {
					$cid = wp_insert_comment(
						array(
							'comment_post_ID'      => $pid,
							'comment_author'       => nexora_demo_str( $r['author'] ),
							'comment_author_email' => sanitize_title( nexora_demo_str( $r['author'] ) ) . '@example.com',
							'comment_content'      => nexora_demo_str( $r['text'] ),
							'comment_type'         => 'review',
							'comment_approved'     => 1,
							'comment_date'         => $r['date'] . ' 10:00:00',
						)
					);
					if ( $cid ) {
						update_comment_meta( $cid, 'rating', (int) $r['rating'] );
						update_comment_meta( $cid, 'verified', ! empty( $r['verified'] ) ? 1 : 0 );
						update_comment_meta( $cid, '_nexora_demo', $demo );
					}
				}
				if ( class_exists( 'WC_Comments' ) ) {
					WC_Comments::clear_transients( $pid );
				}
			}
			return array( true, 0, __( 'Reviews added.', 'nexora' ) );

		case 'posts':
			foreach ( nexora_demo_data( 'posts' ) as $post ) {
				if ( get_page_by_path( $post['slug'], OBJECT, 'post' ) ) {
					continue;
				}
				$cat_id = nexora_demo_term( 'category', nexora_demo_str( $post['category']['name'] ), $post['category']['slug'], $demo );
				$pid    = wp_insert_post(
					array(
						'post_title'    => nexora_demo_str( $post['title'] ),
						'post_name'     => $post['slug'],
						'post_excerpt'  => nexora_demo_str( $post['excerpt'] ),
						'post_content'  => wp_kses_post( nexora_demo_str( $post['body'] ) ),
						'post_status'   => 'publish',
						'post_type'     => 'post',
						'post_date'     => $post['date'] . ' 09:00:00',
						'post_category' => $cat_id ? array( $cat_id ) : array(),
						'tags_input'    => array_map( 'nexora_demo_str', array( $post['tags'] ) )[0] ?? array(),
						'meta_input'    => array( '_nexora_demo' => $demo, 'nexora_featured' => ! empty( $post['featured'] ) ? 1 : 0 ),
					)
				);
				if ( $pid && ! is_wp_error( $pid ) && ! empty( $post['image'] ) ) {
					$img = nexora_demo_image( $post['image'], nexora_demo_str( $post['title'] ), $demo );
					if ( $img ) {
						set_post_thumbnail( $pid, $img );
					}
				}
			}
			return array( true, 0, __( 'Blog posts published.', 'nexora' ) );

		case 'settings':
			nexora_demo_apply_settings( $demo );
			return array( true, 0, __( 'Homepage configured.', 'nexora' ) );
	}
	return array( true, 0, '' );
}

/**
 * Ensure a global attribute taxonomy exists; returns taxonomy name.
 *
 * @param string $slug  Attribute slug.
 * @param string $label Label.
 * @return string
 */
function nexora_demo_attribute( $slug, $label ) {
	$tax = wc_attribute_taxonomy_name( $slug );
	if ( taxonomy_exists( $tax ) ) {
		return $tax;
	}
	$id = wc_create_attribute( array( 'name' => $label, 'slug' => $slug, 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => false ) );
	if ( is_wp_error( $id ) ) {
		return $tax;
	}
	register_taxonomy( $tax, array( 'product' ), array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false ) );
	delete_transient( 'wc_attribute_taxonomies' );
	if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
		wc_get_attribute_taxonomies();
	}
	return $tax;
}

/**
 * Import one product.
 *
 * @param array  $p    Source.
 * @param string $demo Demo id.
 */
function nexora_demo_import_product( array $p, $demo ) {
	if ( wc_get_product_id_by_sku( $p['sku'] ) ) {
		return;
	}
	$has_variations = ! empty( $p['sizes'] );
	$product        = $has_variations ? new WC_Product_Variable() : new WC_Product_Simple();
	$product->set_name( nexora_demo_str( $p['name'] ) );
	$product->set_slug( $p['slug'] );
	$product->set_sku( $p['sku'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_short_description( nexora_demo_str( $p['short'] ) );
	$desc = '<p>' . nexora_demo_str( $p['description'] ) . '</p>';
	$product->set_description( $desc );
	$product->set_featured( ! empty( $p['flags']['featured'] ) );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( (int) $p['stock'] );
	$product->set_reviews_allowed( true );
	if ( ! $has_variations ) {
		$product->set_regular_price( (string) ( $p['oldPrice'] ?: $p['price'] ) );
		if ( ! empty( $p['oldPrice'] ) && $p['oldPrice'] > $p['price'] ) {
			$product->set_sale_price( (string) $p['price'] );
			if ( ! empty( $p['flags']['flash'] ) ) {
				$product->set_date_on_sale_to( time() + 7 * DAY_IN_SECONDS );
			}
		}
	}
	$product->set_date_created( $p['createdAt'] . ' 12:00:00' );

	// Categories.
	$cats = array();
	foreach ( array( $p['category'], $p['subcategory'] ?? '' ) as $slug ) {
		$t = $slug ? get_term_by( 'slug', $slug, 'product_cat' ) : null;
		if ( $t ) {
			$cats[] = $t->term_id;
		}
	}
	$product->set_category_ids( $cats );

	// Tags.
	$tag_ids = array();
	foreach ( (array) ( nexora_is_fa() ? $p['tags']['fa'] : $p['tags']['en'] ) as $tag ) {
		$tid = nexora_demo_term( 'product_tag', $tag, sanitize_title( $tag ), $demo );
		if ( $tid ) {
			$tag_ids[] = $tid;
		}
	}
	$tid = nexora_demo_term( 'product_tag', 'nexora-demo', 'nexora-demo', $demo );
	if ( $tid ) {
		$tag_ids[] = $tid;
	}
	$product->set_tag_ids( $tag_ids );

	// Attributes: brand (if attribute-based), colour, size.
	$attributes = array();
	$brand_tax  = nexora_get_state( 'demo_brand_tax', 'product_brand' );
	$brand_term = get_term_by( 'slug', $p['brand'], $brand_tax );
	if ( $brand_term && 0 === strpos( $brand_tax, 'pa_' ) ) {
		$attributes[] = nexora_demo_tax_attribute( $brand_tax, array( $brand_term->term_id ), 0, false );
	}
	if ( ! empty( $p['colors'] ) ) {
		$ids = array();
		foreach ( $p['colors'] as $c ) {
			$cid = nexora_demo_term( 'pa_color', nexora_demo_str( $c['name'] ), sanitize_title( $c['name']['en'] ), $demo );
			if ( $cid ) {
				update_term_meta( $cid, 'nexora_color', sanitize_hex_color( $c['hex'] ) );
				$ids[] = $cid;
			}
		}
		$attributes[] = nexora_demo_tax_attribute( 'pa_color', $ids, 1, false );
	}
	$size_ids = array();
	if ( $has_variations ) {
		foreach ( $p['sizes'] as $s ) {
			$sid = nexora_demo_term( 'pa_size', (string) $s, sanitize_title( (string) $s ), $demo );
			if ( $sid ) {
				$size_ids[] = $sid;
			}
		}
		$attributes[] = nexora_demo_tax_attribute( 'pa_size', $size_ids, 2, true );
	}
	$product->set_attributes( $attributes );

	// Images.
	$gallery = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$img = nexora_demo_image( 'products/' . $p['slug'] . '-' . $i . '.webp', nexora_demo_str( $p['name'] ), $demo );
		if ( $img ) {
			$gallery[] = $img;
		}
	}
	if ( $gallery ) {
		$product->set_image_id( array_shift( $gallery ) );
		$product->set_gallery_image_ids( $gallery );
	}
	$product->update_meta_data( '_nexora_demo', $demo );
	$product->update_meta_data( 'total_sales', (int) $p['sold'] );
	$product->update_meta_data( 'nexora_highlights', implode( "\n", (array) ( nexora_is_fa() ? $p['highlights']['fa'] : $p['highlights']['en'] ) ) );
	$pid = $product->save();

	// Size variations.
	if ( $has_variations && $pid ) {
		foreach ( $p['sizes'] as $s ) {
			$v = new WC_Product_Variation();
			$v->set_parent_id( $pid );
			$v->set_attributes( array( 'pa_size' => sanitize_title( (string) $s ) ) );
			$v->set_regular_price( (string) ( $p['oldPrice'] ?: $p['price'] ) );
			if ( ! empty( $p['oldPrice'] ) && $p['oldPrice'] > $p['price'] ) {
				$v->set_sale_price( (string) $p['price'] );
			}
			$v->set_manage_stock( true );
			$v->set_stock_quantity( max( 1, (int) floor( $p['stock'] / count( $p['sizes'] ) ) ) );
			$v->set_status( 'publish' );
			$v->update_meta_data( '_nexora_demo', $demo );
			$v->save();
		}
		WC_Product_Variable::sync( $pid );
	}
	// Specs (visible custom attributes) for compare/spec tab when ACF is absent.
	$specs = nexora_is_fa() ? ( $p['specs']['fa'] ?? array() ) : ( $p['specs']['en'] ?? array() );
	if ( $specs && $pid ) {
		$product = wc_get_product( $pid );
		$attrs   = $product->get_attributes();
		$pos     = count( $attrs ) + 1;
		foreach ( array_slice( $specs, 0, 2 ) as $group ) {
			foreach ( array_slice( $group['rows'], 0, 4 ) as $row ) {
				$a = new WC_Product_Attribute();
				$a->set_name( $row[0] );
				$a->set_options( array( $row[1] ) );
				$a->set_position( $pos++ );
				$a->set_visible( true );
				$a->set_variation( false );
				$attrs[ sanitize_title( $row[0] ) ] = $a;
			}
		}
		$product->set_attributes( $attrs );
		$product->save();
	}
	$map               = nexora_get_state( 'demo_product_map', array() );
	$map[ $p['id'] ]   = $pid;
	nexora_set_state( 'demo_product_map', $map );
	// Rating cache from source data (real reviews are added next step).
	update_post_meta( $pid, '_nexora_demo_rating', $p['rating'] );
}

/**
 * Build a taxonomy attribute object.
 *
 * @param string $tax       Taxonomy.
 * @param int[]  $ids       Term ids.
 * @param int    $position  Position.
 * @param bool   $variation Used for variations.
 * @return WC_Product_Attribute
 */
function nexora_demo_tax_attribute( $tax, array $ids, $position, $variation ) {
	$a = new WC_Product_Attribute();
	$a->set_id( wc_attribute_taxonomy_id_by_name( $tax ) );
	$a->set_name( $tax );
	$a->set_options( $ids );
	$a->set_position( $position );
	$a->set_visible( true );
	$a->set_variation( $variation );
	return $a;
}

/**
 * Apply homepage / theme settings for a demo (merged over current values).
 *
 * @param string $demo Demo id.
 */
function nexora_demo_apply_settings( $demo ) {
	$def   = nexora_demos()[ $demo ];
	$cats  = nexora_demo_data( 'categories' );
	$home  = nexora_options( 'home' );
	$img   = static function ( $rel, $title ) use ( $demo ) { return nexora_demo_image( $rel, $title, $demo ); };

	$slides = array();
	$titles = array(
		'hero-1' => array( __( 'Season opening', 'nexora' ), __( 'Up to 40% off the newest tech', 'nexora' ), __( 'Headphones, wearables and laptops from top brands — delivered in 24 hours.', 'nexora' ) ),
		'hero-2' => array( __( 'New collection', 'nexora' ), __( 'Dress for the season', 'nexora' ), __( 'Coats, sneakers and accessories curated by our stylists.', 'nexora' ) ),
		'hero-3' => array( __( 'Home & living', 'nexora' ), __( 'Make every room feel new', 'nexora' ), __( 'Furniture, lighting and decor with free assembly.', 'nexora' ) ),
	);
	foreach ( $def['hero'] as $h ) {
		$slides[] = array(
			'image'   => $img( 'hero/' . $h . '.webp', $titles[ $h ][1] ),
			'eyebrow' => $titles[ $h ][0],
			'title'   => $titles[ $h ][1],
			'text'    => $titles[ $h ][2],
			'button1' => array( 'text' => __( 'Shop now', 'nexora' ), 'url' => nexora_shop_url(), 'target' => '' ),
			'button2' => array( 'text' => __( 'Offers', 'nexora' ), 'url' => nexora_shop_url( array( 'on_sale' => 1 ) ), 'target' => '' ),
		);
	}
	$home['hero_slides'] = $slides;

	$promos = array();
	$tiles  = array();
	foreach ( $cats as $i => $cat ) {
		if ( ! in_array( $cat['slug'], $def['categories'], true ) ) {
			continue;
		}
		$term = get_term_by( 'slug', $cat['slug'], 'product_cat' );
		$url  = $term ? get_term_link( $term ) : nexora_shop_url();
		if ( ! empty( $cat['promo'] ) ) {
			$promos[] = array(
				'image'  => $img( $cat['promo']['image'], nexora_demo_str( $cat['promo']['title'] ) ),
				'kicker' => nexora_demo_str( $cat['promo']['badge'] ),
				'title'  => nexora_demo_str( $cat['promo']['title'] ),
				'text'   => '',
				'link'   => array( 'text' => __( 'Shop', 'nexora' ), 'url' => $url, 'target' => '' ),
				'style'  => $i % 2 ? 'light' : 'dark',
			);
		}
		if ( ! empty( $cat['tile'] ) ) {
			$tiles[] = array( 'category' => $term ? $term->term_id : 0, 'image' => $img( $cat['tile'], nexora_demo_str( $cat['name'] ) ) );
		}
	}
	$home['promo_banners'] = array_slice( $promos, 0, 4 );
	$home['tiles']         = array_slice( $tiles, 0, 4 );
	$home['hero_tiles']    = array();
	foreach ( array_slice( $promos, 0, 2 ) as $k => $pr ) {
		$home['hero_tiles'][] = array( 'image' => $pr['image'], 'kicker' => $pr['kicker'], 'title' => $pr['title'], 'link' => $pr['link'], 'dark' => 0 === $k );
	}
	$home['flash_end']      = gmdate( 'Y-m-d', time() + 7 * DAY_IN_SECONDS ) . ' 23:59';
	$home['brands_source']  = 'product_brand' === nexora_get_state( 'demo_brand_tax' ) ? 'taxonomy' : 'manual';
	if ( 'manual' === $home['brands_source'] ) {
		$home['brands_manual'] = array();
		foreach ( nexora_demo_data( 'brands' ) as $b ) {
			if ( in_array( $b['category'], $def['categories'], true ) ) {
				$home['brands_manual'][] = array( 'name' => $b['name'], 'logo' => 0, 'url' => nexora_shop_url( array( 'brand' => $b['slug'] ) ) );
			}
		}
	}
	$home['reviews_source'] = 'manual';
	$reviews                = nexora_demo_data( 'reviews' );
	$home['reviews_manual'] = array();
	foreach ( array_slice( $reviews['testimonials'] ?? array(), 0, 6 ) as $t ) {
		$home['reviews_manual'][] = array( 'name' => nexora_demo_str( $t['name'] ), 'role' => nexora_demo_str( $t['role'] ), 'rating' => (int) $t['rating'], 'text' => nexora_demo_str( $t['text'] ) );
	}
	foreach ( nexora_home_section_labels() as $key => $label ) {
		$home[ $key . '_enable' ] = true;
	}
	nexora_update_options( 'home', nexora_sanitize_group( 'home', $home ) );

	$pages = nexora_options( 'pages' );
	$pages['newsletter_bg']  = $img( 'banners/newsletter-bg.webp', __( 'Newsletter', 'nexora' ) );
	$pages['auth_image']     = $img( 'banners/auth-visual.webp', __( 'Sign in', 'nexora' ) );
	$pages['megamenu_image'] = $img( 'banners/megamenu-promo.webp', __( 'Offer', 'nexora' ) );
	nexora_update_options( 'pages', nexora_sanitize_group( 'pages', $pages ) );

	$shop = nexora_options( 'shop' );
	$shop['banner_img'] = $img( 'banners/shop-banner.webp', __( 'Shop', 'nexora' ) );
	nexora_update_options( 'shop', nexora_sanitize_group( 'shop', $shop ) );

	update_option( 'nexora_active_preset', $def['preset'] );
	nexora_set_state( 'demo_imported', $def['name'] );
	nexora_set_state( 'demo_id', $demo );
	delete_transient( 'nexora_dash_kpis' );
	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients();
	}
	flush_rewrite_rules();
}

/**
 * Remove every object tagged as demo.
 *
 * @return int Number of removed objects.
 */
function nexora_demo_remove() {
	$n = 0;
	$posts = get_posts( array( 'post_type' => array( 'product', 'product_variation', 'post', 'attachment' ), 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_nexora_demo' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	foreach ( $posts as $id ) {
		if ( 'attachment' === get_post_type( $id ) ) {
			wp_delete_attachment( $id, true );
		} else {
			wp_delete_post( $id, true );
		}
		$n++;
	}
	$comments = get_comments( array( 'meta_key' => '_nexora_demo', 'fields' => 'ids' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	foreach ( $comments as $cid ) {
		wp_delete_comment( $cid, true );
		$n++;
	}
	$taxes = array_filter( array( 'product_cat', 'product_tag', 'category', 'post_tag', 'pa_color', 'pa_size', 'pa_brand', taxonomy_exists( 'product_brand' ) ? 'product_brand' : '' ) );
	foreach ( $taxes as $tax ) {
		if ( ! taxonomy_exists( $tax ) ) {
			continue;
		}
		$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false, 'meta_key' => '_nexora_demo', 'fields' => 'ids' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( ! is_wp_error( $terms ) ) {
			// Children first.
			foreach ( array_reverse( $terms ) as $tid ) {
				wp_delete_term( $tid, $tax );
				$n++;
			}
		}
	}
	nexora_set_state( 'demo_imported', '' );
	nexora_set_state( 'demo_id', '' );
	nexora_set_state( 'demo_product_map', array() );
	delete_transient( 'nexora_dash_kpis' );
	return $n;
}

/**
 * AJAX: run a step.
 */
add_action(
	'wp_ajax_nexora_demo_step',
	static function () {
		nexora_admin_ajax_check();
		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'WooCommerce must be active before importing a demo.', 'nexora' ) ) );
		}
		$demo   = isset( $_POST['demo'] ) ? sanitize_key( $_POST['demo'] ) : '';
		$step   = isset( $_POST['step'] ) ? sanitize_key( $_POST['step'] ) : '';
		$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
		if ( ! isset( nexora_demos()[ $demo ] ) || ! isset( nexora_demo_steps()[ $step ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'nexora' ) ) );
		}
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}
		wp_raise_memory_limit( 'admin' );
		wp_suspend_cache_addition( true );
		if ( 'categories' === $step && 0 === $offset ) {
			nexora_set_state( 'demo_product_map', array() );
		}
		list( $done, $next, $msg ) = nexora_demo_run_step( $demo, $step, $offset );
		wp_send_json_success( array( 'done' => $done, 'offset' => $next, 'message' => $msg ) );
	}
);

/**
 * AJAX: remove.
 */
add_action(
	'wp_ajax_nexora_demo_remove',
	static function () {
		nexora_admin_ajax_check();
		$n = nexora_demo_remove();
		/* translators: %d: count */
		wp_send_json_success( array( 'message' => sprintf( __( 'Removed %d demo objects.', 'nexora' ), $n ) ) );
	}
);

/**
 * Render page.
 */
function nexora_render_demo_import() {
	$current = nexora_get_state( 'demo_id' );
	nexora_admin_header( 'nexora-demo', __( 'Demo Import', 'nexora' ), __( 'Start from a complete store and replace the content with yours. Demo products, categories, brands, reviews, posts and homepage settings are imported; your existing content is never changed. Everything can be removed with one click.', 'nexora' ) );
	if ( ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="nx-notice nx-notice--error">' . esc_html__( 'WooCommerce is not active. Activate it first from the Plugins tab.', 'nexora' ) . '</div>';
	}
	if ( $current ) {
		echo '<div class="nx-strip nx-strip--ok">' . nexora_admin_icon( 'check' ) . '<div><strong>' . esc_html( sprintf( /* translators: %s: demo */ __( 'Demo "%s" is installed.', 'nexora' ), nexora_get_state( 'demo_imported' ) ) ) . '</strong> <span class="nx-muted">' . esc_html__( 'Importing another demo adds its content alongside. Remove first for a clean switch.', 'nexora' ) . '</span></div><button class="button-link-delete" data-demo-remove data-confirm>' . esc_html__( 'Remove all demo content', 'nexora' ) . '</button></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '<div class="nx-grid nx-grid--3">';
	foreach ( nexora_demos() as $id => $d ) {
		echo '<article class="nx-card nx-demo' . ( $id === $current ? ' is-installed' : '' ) . '" data-demo-card="' . esc_attr( $id ) . '">';
		echo '<img class="nx-demo__img" src="' . esc_url( NEXORA_URI . $d['preview'] ) . '" alt="" loading="lazy">';
		echo '<div class="nx-card__body"><h3>' . esc_html( $d['name'] ) . '</h3><p>' . esc_html( $d['desc'] ) . '</p>';
		echo '<ul class="nx-demo__includes">';
		foreach ( nexora_demo_steps() as $step => $label ) {
			echo '<li data-step="' . esc_attr( $step ) . '">' . nexora_admin_icon( 'check' ) . esc_html( $label ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</ul>';
		echo '<div class="nx-demo__progress" hidden><div class="nx-progress"><span style="width:0%"></span></div><p class="nx-demo__status"></p></div>';
		echo '<button class="button button-primary" data-demo-import="' . esc_attr( $id ) . '"' . disabled( ! class_exists( 'WooCommerce' ), true, false ) . '>' . nexora_admin_icon( 'download' ) . esc_html__( 'Import this demo', 'nexora' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div></article>';
	}
	echo '</div>';
	nexora_admin_card( __( 'Good to know', 'nexora' ), '<ul class="nx-list"><li>' . esc_html__( 'Images are copied from the theme into your media library — no external downloads.', 'nexora' ) . '</li><li>' . esc_html__( 'Products get the tag "nexora-demo"; the whole set is removable from this page.', 'nexora' ) . '</li><li>' . esc_html__( 'Prices are in the store currency without conversion. Adjust WooCommerce → Settings → General if needed.', 'nexora' ) . '</li><li>' . esc_html__( 'The import changes the active colour preset and homepage settings; your previous settings are kept in the theme options and can be restored via the settings tabs.', 'nexora' ) . '</li></ul>', array( 'icon' => 'info' ) );
	nexora_admin_footer();
}
