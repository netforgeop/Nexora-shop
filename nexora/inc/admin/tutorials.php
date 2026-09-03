<?php
/**
 * In-admin tutorial system (categorised, searchable, step-based).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Categories.
 *
 * @return array
 */
function nexora_tutorial_categories() {
	return array(
		'start'    => __( 'Getting started', 'nexora' ),
		'products' => __( 'Products & categories', 'nexora' ),
		'design'   => __( 'Design & homepage', 'nexora' ),
		'payments' => __( 'Payments & shipping', 'nexora' ),
		'content'  => __( 'Blog & pages', 'nexora' ),
		'seo'      => __( 'SEO & performance', 'nexora' ),
		'security' => __( 'Security & maintenance', 'nexora' ),
	);
}

/**
 * Tutorials. Each: id, title, category, minutes, summary, steps[], links[].
 *
 * @return array
 */
function nexora_tutorials() {
	$s   = admin_url( 'admin.php?page=nexora-settings&tab=' );
	$wcs = admin_url( 'admin.php?page=wc-settings&tab=' );
	$t   = array(
		array(
			'id'       => 'first-steps',
			'title'    => __( 'Your first 10 minutes with Nexora', 'nexora' ),
			'category' => 'start',
			'minutes'  => 10,
			'summary'  => __( 'Activate WooCommerce, run the wizard, choose a colour preset and import a demo — in that order.', 'nexora' ),
			'steps'    => array(
				__( 'Open Plugins in the theme menu and make sure WooCommerce is active. Without it, the shop, cart and checkout do not exist.', 'nexora' ),
				__( 'Run the Setup Wizard. It asks for logo, store details, colours and whether to import demo content.', 'nexora' ),
				__( 'Visit the front end. Everything you see is editable in Theme Settings — hover a section on the homepage to find its tab name.', 'nexora' ),
				__( 'Replace demo products with your own (Products → All products). Demo items are tagged "nexora-demo" so you can delete them in one click from Demo Import.', 'nexora' ),
				__( 'Check System Status until everything is green.', 'nexora' ),
			),
			'links'    => array( array( __( 'Setup Wizard', 'nexora' ), admin_url( 'admin.php?page=nexora-wizard' ) ), array( __( 'System Status', 'nexora' ), admin_url( 'admin.php?page=nexora-status' ) ) ),
		),
		array(
			'id'       => 'logo-identity',
			'title'    => __( 'Logo, favicon and site name', 'nexora' ),
			'category' => 'start',
			'minutes'  => 3,
			'summary'  => __( 'Where to upload the logo and how the text fallback works.', 'nexora' ),
			'steps'    => array(
				__( 'Go to Appearance → Customize → Site Identity and upload a logo (SVG or PNG, at least 160 px tall for retina).', 'nexora' ),
				__( 'Upload a Site Icon (512×512 px) — it becomes the favicon and the icon on phone home screens.', 'nexora' ),
				__( 'If you have a light version for the dark footer, add it in Theme Settings → General → Branding.', 'nexora' ),
				__( 'No logo? The theme shows the "Logo text" from Theme Settings → General in the brand font.', 'nexora' ),
			),
			'links'    => array( array( __( 'Customizer', 'nexora' ), admin_url( 'customize.php?autofocus[section]=title_tagline' ) ), array( __( 'General settings', 'nexora' ), $s . 'general' ) ),
		),
		array(
			'id'       => 'menus',
			'title'    => __( 'Menus: main navigation, top bar and footer columns', 'nexora' ),
			'category' => 'start',
			'minutes'  => 5,
			'summary'  => __( 'The theme has eight menu locations. Here is what each one does.', 'nexora' ),
			'steps'    => array(
				__( 'Appearance → Menus → create a menu and tick a location under "Display location".', 'nexora' ),
				__( 'Primary: the main bar under the header. Two levels deep; add product categories for a mega-menu feel.', 'nexora' ),
				__( 'Mobile: shown in the off-canvas drawer. Leave empty to reuse Primary.', 'nexora' ),
				__( 'Top bar (start/end): small utility links such as "Track order", "Sell with us".', 'nexora' ),
				__( 'Footer 1 / Footer 2 / Footer bottom: link columns and the legal row. Account: the dropdown under the user icon.', 'nexora' ),
			),
			'links'    => array( array( __( 'Menus', 'nexora' ), admin_url( 'nav-menus.php' ) ) ),
		),
		array(
			'id'       => 'add-product',
			'title'    => __( 'Adding a product correctly', 'nexora' ),
			'category' => 'products',
			'minutes'  => 6,
			'summary'  => __( 'Images, price, sale price, stock, attributes and highlights — what the theme uses from each.', 'nexora' ),
			'steps'    => array(
				__( 'Products → Add new. Title and long description go in the editor; the short description becomes the intro next to the gallery.', 'nexora' ),
				__( 'Set a featured image (square, 800×800 px or larger). Gallery images appear as thumbnails and in the zoom viewer.', 'nexora' ),
				__( 'Regular price + Sale price → the theme shows the discount percentage badge automatically.', 'nexora' ),
				__( 'Inventory: enable stock management to get "only 3 left" urgency labels and out-of-stock overlays.', 'nexora' ),
				__( 'Attributes: tick "Visible on the product page" — they populate the Specifications tab and the compare table. A "Colour" attribute with colour swatches shows dots on product cards.', 'nexora' ),
				__( 'With ACF/SCF active, the Highlights box gives you the bullet list beside the price and a size-guide modal.', 'nexora' ),
			),
			'links'    => array( array( __( 'Add product', 'nexora' ), admin_url( 'post-new.php?post_type=product' ) ), array( __( 'Shop settings', 'nexora' ), $s . 'shop' ) ),
		),
		array(
			'id'       => 'categories-brands',
			'title'    => __( 'Categories, brands and colour swatches', 'nexora' ),
			'category' => 'products',
			'minutes'  => 4,
			'summary'  => __( 'Category thumbnails drive the homepage tiles; brands come from WooCommerce Brands.', 'nexora' ),
			'steps'    => array(
				__( 'Products → Categories: upload a thumbnail for every top-level category (square). These images are used in the homepage category strip and tiles.', 'nexora' ),
				__( 'Products → Brands (WooCommerce 9.6+): add brands with logos. The homepage brands carousel and the shop filter pick them up automatically.', 'nexora' ),
				__( 'Products → Attributes → Colour → Configure terms: each term has a colour picker added by the theme. Used for swatches on cards and filters.', 'nexora' ),
			),
			'links'    => array( array( __( 'Categories', 'nexora' ), admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ) ) ),
		),
		array(
			'id'       => 'homepage',
			'title'    => __( 'Building the homepage section by section', 'nexora' ),
			'category' => 'design',
			'minutes'  => 8,
			'summary'  => __( 'Enable, disable and reorder 15 sections; pick product sources; upload banners.', 'nexora' ),
			'steps'    => array(
				__( 'Theme Settings → Homepage → Layout: toggle sections on/off and drag to reorder.', 'nexora' ),
				__( 'Hero slider: add up to 6 slides with image (1600×640 px), kicker, title, text and button. Side banners fill the two cards next to the slider.', 'nexora' ),
				__( 'Product sections (Featured, Flash sale, New arrivals, Best sellers): choose a source — automatic (featured / on sale / newest / best selling), a category, or hand-picked products.', 'nexora' ),
				__( 'Flash sale shows a countdown to the date you set; it hides itself when the date has passed.', 'nexora' ),
				__( 'Testimonials: choose real WooCommerce reviews (4+ stars) or write your own. Stats band: four numbers with icons.', 'nexora' ),
				__( 'Blog & newsletter: latest posts pull from your blog; newsletter subscribers are stored in the theme (export as CSV from the dashboard) or posted to a Mailchimp/Mailerlite form URL.', 'nexora' ),
			),
			'links'    => array( array( __( 'Homepage settings', 'nexora' ), $s . 'home' ) ),
		),
		array(
			'id'       => 'colors',
			'title'    => __( 'Colours: presets and your own palette', 'nexora' ),
			'category' => 'design',
			'minutes'  => 4,
			'summary'  => __( 'Switch between Classic Red, Modern Blue and Luxury Green, or save unlimited custom presets.', 'nexora' ),
			'steps'    => array(
				__( 'Colors & Presets → click Activate on a preset. The whole site, including WooCommerce pages, changes instantly.', 'nexora' ),
				__( 'Click Edit on any preset, adjust colours, watch the live preview and press Save — built-in presets are copied, custom ones are updated in place.', 'nexora' ),
				__( 'The contrast checker warns when text on primary buttons falls under WCAG 4.5:1.', 'nexora' ),
				__( 'Export JSON to move a palette to another site; import it on the same screen.', 'nexora' ),
			),
			'links'    => array( array( __( 'Colors & Presets', 'nexora' ), admin_url( 'admin.php?page=nexora-presets' ) ) ),
		),
		array(
			'id'       => 'header-footer',
			'title'    => __( 'Header, top bar and footer', 'nexora' ),
			'category' => 'design',
			'minutes'  => 5,
			'summary'  => __( 'Announcement bar, sticky header, search placeholders, footer columns, trust badges and payment icons.', 'nexora' ),
			'steps'    => array(
				__( 'Header tab: announcement text with optional link and dismiss button; toggle the top bar, sticky header, category menu and search suggestions.', 'nexora' ),
				__( 'Footer tab: about text, app badges, contact block, trust badges (paste the eNAMAD/Samandehi code — scripts are stripped for safety), payment icons and the copyright line.', 'nexora' ),
				__( 'Footer link columns come from the Footer 1 / Footer 2 menu locations; the column titles are set in the Footer tab.', 'nexora' ),
			),
			'links'    => array( array( __( 'Header', 'nexora' ), $s . 'header' ), array( __( 'Footer', 'nexora' ), $s . 'footer' ) ),
		),
		array(
			'id'       => 'payments',
			'title'    => __( 'Accepting payments (ZarinPal, bank gateways, cash on delivery)', 'nexora' ),
			'category' => 'payments',
			'minutes'  => 6,
			'summary'  => __( 'The theme never touches money — WooCommerce and your gateway plugin do. Here is the safe setup.', 'nexora' ),
			'steps'    => array(
				__( 'Get the official plugin from your provider (for ZarinPal: zarinpal.com → Lab → WooCommerce). Never use a plugin from a forum or Telegram channel.', 'nexora' ),
				__( 'Plugins → Add new → Upload plugin → activate.', 'nexora' ),
				__( 'WooCommerce → Settings → Payments → enable the gateway and enter the merchant ID / API key given by the provider. These keys are stored by WooCommerce only.', 'nexora' ),
				__( 'Place a test order. Providers offer a sandbox mode — switch it off before launch.', 'nexora' ),
				__( 'Make sure the site runs on HTTPS (green lock). System Status warns if it does not.', 'nexora' ),
			),
			'links'    => array( array( __( 'Payment settings', 'nexora' ), $wcs . 'checkout' ) ),
		),
		array(
			'id'       => 'shipping',
			'title'    => __( 'Shipping zones, free-shipping threshold and Persian provinces', 'nexora' ),
			'category' => 'payments',
			'minutes'  => 5,
			'summary'  => __( 'Create zones, add flat-rate / free shipping, and show the free-shipping progress bar in the cart.', 'nexora' ),
			'steps'    => array(
				__( 'WooCommerce → Settings → Shipping → Add zone (e.g. "Tehran", "Other provinces"). Install "Persian WooCommerce" to get province lists.', 'nexora' ),
				__( 'Add a Flat rate and a Free shipping method with a minimum order amount.', 'nexora' ),
				__( 'Theme Settings → Shop → Cart: enter the same threshold to display the "X left for free shipping" progress bar in the cart drawer.', 'nexora' ),
			),
			'links'    => array( array( __( 'Shipping settings', 'nexora' ), $wcs . 'shipping' ) ),
		),
		array(
			'id'       => 'blog',
			'title'    => __( 'Blog posts, categories and the magazine layout', 'nexora' ),
			'category' => 'content',
			'minutes'  => 4,
			'summary'  => __( 'Featured images, reading time, related posts and the sidebar.', 'nexora' ),
			'steps'    => array(
				__( 'Posts → Add new. Set a featured image (1200×675 px). The first category becomes the coloured badge on the card.', 'nexora' ),
				__( 'Settings → Reading: the "Posts page" is your blog index (the wizard creates "Blog").', 'nexora' ),
				__( 'Theme Settings → Blog: layout (grid / list), sidebar position, reading time, author box, related posts and the newsletter box.', 'nexora' ),
				__( 'Appearance → Widgets → Blog sidebar: add Search, Categories, Recent posts, Tag cloud.', 'nexora' ),
			),
			'links'    => array( array( __( 'Blog settings', 'nexora' ), $s . 'blog' ) ),
		),
		array(
			'id'       => 'pages',
			'title'    => __( 'Contact, FAQ, About and legal pages', 'nexora' ),
			'category' => 'content',
			'minutes'  => 4,
			'summary'  => __( 'Page templates included with the theme and how to fill them.', 'nexora' ),
			'steps'    => array(
				__( 'Pages → edit "Contact us": the Contact template shows your address, phone, email, hours (from General settings), a map embed and a form. Paste a Contact Form 7 / WPForms shortcode in the Contact tab, or use the built-in simple form.', 'nexora' ),
				__( 'The FAQ template renders the questions you enter in Theme Settings → Shop → FAQ as an accordion with search.', 'nexora' ),
				__( 'For About/Terms/Privacy: create a normal page; choose "Full width" or "Minimal" template from the page sidebar when you do not want the sidebar.', 'nexora' ),
				__( 'Set the Privacy Policy page in Settings → Privacy and the Terms page in WooCommerce → Settings → Advanced — checkout links to both.', 'nexora' ),
			),
			'links'    => array( array( __( 'Pages', 'nexora' ), admin_url( 'edit.php?post_type=page' ) ) ),
		),
		array(
			'id'       => 'seo',
			'title'    => __( 'SEO basics and Rank Math', 'nexora' ),
			'category' => 'seo',
			'minutes'  => 5,
			'summary'  => __( 'What the theme does out of the box and when to add an SEO plugin.', 'nexora' ),
			'steps'    => array(
				__( 'Built in: semantic HTML, breadcrumb schema, product/organisation schema, Open Graph tags, clean titles. It all switches off automatically when Rank Math, Yoast, SEOPress or AIOSEO is active — no duplicates.', 'nexora' ),
				__( 'Install Rank Math for sitemaps, per-page titles and Search Console integration.', 'nexora' ),
				__( 'Settings → Permalinks → "Post name". Never use plain permalinks on a store.', 'nexora' ),
				__( 'Write unique short descriptions for products and alt text for images.', 'nexora' ),
			),
			'links'    => array( array( __( 'Permalinks', 'nexora' ), admin_url( 'options-permalink.php' ) ) ),
		),
		array(
			'id'       => 'performance',
			'title'    => __( 'Speed: caching, images and fonts', 'nexora' ),
			'category' => 'seo',
			'minutes'  => 5,
			'summary'  => __( 'The theme is light by design; these switches make it lighter.', 'nexora' ),
			'steps'    => array(
				__( 'Theme Settings → Performance: lazy images, defer scripts, disable emoji script, disable block CSS, load WooCommerce assets only on shop pages, preload fonts.', 'nexora' ),
				__( 'Install a caching plugin (LiteSpeed Cache if your host runs LiteSpeed, otherwise WP Super Cache). Exclude cart, checkout and my-account pages — most plugins do this automatically for WooCommerce.', 'nexora' ),
				__( 'Upload images at sensible sizes (products 800–1200 px). WordPress generates WebP-friendly sizes automatically on modern hosts.', 'nexora' ),
				__( 'After changing options, purge the cache plugin once.', 'nexora' ),
			),
			'links'    => array( array( __( 'Performance settings', 'nexora' ), $s . 'performance' ) ),
		),
		array(
			'id'       => 'security',
			'title'    => __( 'Keeping the store safe', 'nexora' ),
			'category' => 'security',
			'minutes'  => 5,
			'summary'  => __( 'Updates, backups, admin accounts and what the theme already protects.', 'nexora' ),
			'steps'    => array(
				__( 'Update WordPress, WooCommerce, plugins and the theme when notified. Use a child theme for code customisations so updates never overwrite them.', 'nexora' ),
				__( 'Install a security plugin (Wordfence) and enable two-factor authentication for administrators.', 'nexora' ),
				__( 'Daily backups via your host or UpdraftPlus — test a restore once.', 'nexora' ),
				__( 'Every theme form and AJAX action is protected with nonces and capability checks; uploads go through the WordPress media library; demo import is administrator-only.', 'nexora' ),
				__( 'Turn WP_DEBUG off in production (System Status warns you).', 'nexora' ),
			),
			'links'    => array( array( __( 'Plugin manager', 'nexora' ), admin_url( 'admin.php?page=nexora-plugins' ) ) ),
		),
		array(
			'id'       => 'child-theme',
			'title'    => __( 'Customising safely with a child theme', 'nexora' ),
			'category' => 'security',
			'minutes'  => 4,
			'summary'  => __( 'Override templates and add CSS without losing changes on update.', 'nexora' ),
			'steps'    => array(
				__( 'Download the ready-made child theme from the docs folder (nexora-child.zip) and install it via Appearance → Themes → Add new → Upload.', 'nexora' ),
				__( 'Copy any file from nexora/template-parts or nexora/woocommerce into the same path inside the child theme and edit it there.', 'nexora' ),
				__( 'Small CSS tweaks: Appearance → Customize → Additional CSS. Colours: use the preset editor instead of CSS.', 'nexora' ),
				__( 'Hooks: the theme fires nexora_before_*/nexora_after_* actions around every homepage section — see docs/hooks.md.', 'nexora' ),
			),
			'links'    => array(),
		),
	);
	return apply_filters( 'nexora_tutorials', $t );
}

/**
 * Get a tutorial by id.
 *
 * @param string $id Id.
 * @return array|null
 */
function nexora_tutorial( $id ) {
	foreach ( nexora_tutorials() as $t ) {
		if ( $t['id'] === $id ) {
			return $t;
		}
	}
	return null;
}

/**
 * Render page.
 */
function nexora_render_tutorials() {
	$cats    = nexora_tutorial_categories();
	$current = isset( $_GET['tutorial'] ) ? sanitize_key( $_GET['tutorial'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$done    = (array) get_user_meta( get_current_user_id(), 'nexora_tutorials_done', true );
	nexora_admin_header( 'nexora-tutorials', __( 'Tutorials', 'nexora' ), __( 'Short, practical guides written for store owners — no coding required. Tick a guide when you are done to keep track.', 'nexora' ) );

	if ( $current && nexora_tutorial( $current ) ) {
		$t = nexora_tutorial( $current );
		echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=nexora-tutorials' ) ) . '">&larr; ' . esc_html__( 'All tutorials', 'nexora' ) . '</a></p>';
		echo '<article class="nx-card nx-tutorial"><header class="nx-card__head"><h2>' . esc_html( $t['title'] ) . '</h2><span class="nx-muted">' . esc_html( $cats[ $t['category'] ] ) . ' · ' . esc_html( sprintf( /* translators: %d: minutes */ __( '%d min', 'nexora' ), $t['minutes'] ) ) . '</span></header><div class="nx-card__body">';
		echo '<p class="nx-tutorial__summary">' . esc_html( $t['summary'] ) . '</p><ol class="nx-steps">';
		foreach ( $t['steps'] as $step ) {
			echo '<li>' . wp_kses_post( $step ) . '</li>';
		}
		echo '</ol>';
		if ( $t['links'] ) {
			echo '<p class="nx-tutorial__links">';
			foreach ( $t['links'] as $l ) {
				echo '<a class="button" href="' . esc_url( $l[1] ) . '">' . esc_html( $l[0] ) . ' &rarr;</a> ';
			}
			echo '</p>';
		}
		echo '<form method="post">';
		wp_nonce_field( 'nexora_tutorial_done', '_nexora_nonce' );
		echo '<input type="hidden" name="tutorial_id" value="' . esc_attr( $t['id'] ) . '">';
		echo '<button class="button ' . ( in_array( $t['id'], $done, true ) ? '' : 'button-primary' ) . '" name="nexora_tutorial_toggle" value="1">' . ( in_array( $t['id'], $done, true ) ? esc_html__( 'Mark as not done', 'nexora' ) : esc_html__( 'Mark as done', 'nexora' ) ) . '</button></form>';
		echo '</div></article>';
		nexora_admin_footer();
		return;
	}

	echo '<div class="nx-tutorials-head"><input type="search" class="regular-text" placeholder="' . esc_attr__( 'Search tutorials…', 'nexora' ) . '" data-filter-list="[data-tutorial]"><span class="nx-muted">' . esc_html( sprintf( /* translators: 1: done 2: total */ __( '%1$d of %2$d completed', 'nexora' ), count( array_filter( $done ) ), count( nexora_tutorials() ) ) ) . '</span></div>';
	foreach ( $cats as $cid => $label ) {
		$items = array_filter( nexora_tutorials(), static function ( $t ) use ( $cid ) { return $t['category'] === $cid; } );
		if ( ! $items ) {
			continue;
		}
		echo '<h2 class="nx-section-title">' . esc_html( $label ) . '</h2><div class="nx-grid nx-grid--3">';
		foreach ( $items as $t ) {
			$is_done = in_array( $t['id'], $done, true );
			printf(
				'<a class="nx-card nx-tutcard%1$s" href="%2$s" data-tutorial data-text="%3$s"><div class="nx-card__body">%4$s<h3>%5$s</h3><p>%6$s</p><span class="nx-muted">%7$s</span></div></a>',
				$is_done ? ' is-done' : '',
				esc_url( admin_url( 'admin.php?page=nexora-tutorials&tutorial=' . $t['id'] ) ),
				esc_attr( strtolower( $t['title'] . ' ' . $t['summary'] ) ),
				nexora_admin_icon( $is_done ? 'check' : 'book' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html( $t['title'] ),
				esc_html( $t['summary'] ),
				esc_html( sprintf( /* translators: %d: minutes */ __( '%d min read', 'nexora' ), $t['minutes'] ) )
			);
		}
		echo '</div>';
	}
	nexora_admin_footer();
}

/**
 * Toggle done state.
 */
add_action(
	'admin_init',
	static function () {
		if ( ! isset( $_POST['nexora_tutorial_toggle'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		nexora_admin_post_check( 'nexora_tutorial_done' );
		$id   = isset( $_POST['tutorial_id'] ) ? sanitize_key( $_POST['tutorial_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$done = array_filter( (array) get_user_meta( get_current_user_id(), 'nexora_tutorials_done', true ) );
		if ( in_array( $id, $done, true ) ) {
			$done = array_diff( $done, array( $id ) );
		} elseif ( nexora_tutorial( $id ) ) {
			$done[] = $id;
		}
		update_user_meta( get_current_user_id(), 'nexora_tutorials_done', array_values( $done ) );
		wp_safe_redirect( admin_url( 'admin.php?page=nexora-tutorials&tutorial=' . $id ) );
		exit;
	}
);
