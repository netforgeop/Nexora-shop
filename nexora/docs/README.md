# Nexora — WooCommerce Theme by AVIRAD

**Version:** 1.0.0 · **Author:** [AVIRAD](https://avirad.ir/) · **Text domain:** `nexora`

Nexora is an RTL-first, multi-category WooCommerce theme. Everything on the storefront is
driven by WooCommerce data and the native theme options panel — no page builder, no custom
product post type, no custom payment code.

## Requirements
| | Minimum | Tested |
|---|---|---|
| WordPress | 6.4 | 6.8 |
| PHP | 8.0 | 8.3 |
| WooCommerce (required) | 8.0 | 9.9 |
| ACF / Secure Custom Fields | optional | — |

The theme never fatals when WooCommerce or ACF are missing: shop features are hidden and
the dashboard explains what to install.

## Installation
1. Appearance → Themes → Add New → Upload `nexora.zip` → Activate.
2. The **Setup Wizard** opens automatically (10 steps: welcome, plugins, branding, colours,
   contact, WooCommerce basics, demo content, homepage sections, pages, finish). Every step
   can be skipped and re-run later from *Nexora → Setup Wizard*.
3. Install/activate **WooCommerce** from the Plugin Manager tab (WordPress.org repository only).
4. Optional: import one of the three demo stores (*Modern Fashion*, *Electronics*, *General Store*).

Activation automatically (and safely) creates: menus for all locations, Shop/Cart/Checkout/
My Account/Wishlist/Compare/Contact/FAQ pages (if missing), default options, a static front
page, and flushes rewrite rules. It never stores API keys, gateway credentials or secrets.

## Admin panel (WordPress menu **Nexora**)
| Tab | What it does |
|---|---|
| Dashboard | Store health strip, KPIs (orders/products/revenue), quick links, environment facts, checklist, tutorials, tools (clear caches, export subscribers CSV, export options JSON, replay tour) |
| Theme Settings | General, Header, Footer, Homepage, Shop, Product, Blog, Typography, Pages, Performance. Conditional fields, help text, repeaters, sortable section order |
| Colors & Presets | 4 built-in presets (Nexora Gold, Classic Red, Modern Blue, Luxury Green) + unlimited user presets: create, edit, duplicate, activate, delete, import/export JSON, live preview with WCAG contrast check |
| Plugins | Required (WooCommerce, ACF/SCF) and recommended (Rank Math, ZarinPal gateway, security, cache) with installed/active/version status and one-click install from WordPress.org |
| Demo Import | Three deletable demo stores, batched AJAX import, admin-only |
| Tutorials | Categorised step-by-step guides + onboarding tour with Skip |
| System Status | PHP/WP/WC checks, WooCommerce configuration checker (currency, address, shipping zones, payment gateways, pages), copyable support report |

## Colour system
All colours are CSS custom properties (`--theme-primary`, `--theme-secondary`, `--theme-accent`,
`--theme-text`, `--theme-border`, `--theme-button`, `--theme-header-bg`, `--theme-footer-bg` …)
printed inline from the active preset (`nexora_preset_css()`). Legacy template tokens
(`--color-primary` etc.) alias the theme variables, so the original design is preserved 1:1.

## Homepage components
`template-parts/home/*.php` — hero, trust bar, categories, featured, flash sale, newest, promo
banners, best sellers, collections, category tiles, brands, stats, testimonials, blog, newsletter.
Each can be enabled/disabled and re-ordered (Theme Settings → Homepage → Section order).

## WooCommerce
Overrides live in `woocommerce/` (archive, loop, single product, cart, checkout, my-account,
auth forms, notices). Wishlist & compare are theme features stored in user meta
(`_nexora_wishlist`, `_nexora_compare`) with localStorage fallback for guests. Payment is
exclusively via WooCommerce gateways (e.g. ZarinPal plugin).

## Hooks
Filters: `nexora_option`, `nexora_preset_css`, `nexora_js_config`, `nexora_plugin_list`,
`nexora_tutorials`, `nexora_admin_pages`, `nexora_admin_cap`, `nexora_breadcrumb_items`,
`nexora_product_specs`, `nexora_schema`, `nexora_wc_checks`, `nexora_theme_checks`,
`nexora_minimal_checkout`, `nexora_map_hosts`, `nexora_force_swiper`, `nexora_account_icon`.
Actions: `nexora_activated`, `nexora_upgraded`, `nexora_settings_saved`,
`nexora_newsletter_subscribed`, `nexora_contact_message`.

## Child themes
Create `nexora-child/style.css` with `Template: nexora`. Override any file in
`template-parts/` or `woocommerce/` by copying it to the child theme.

## Translation
`languages/nexora.pot` (source) and a complete Persian translation `fa_IR.po/.mo` are bundled.
RTL is handled with logical CSS properties — no separate rtl.css is needed.

## Security
Every admin form and AJAX endpoint checks a nonce and `manage_options` (filterable); all input
is sanitised via `inc/security/sanitize.php`, all output escaped, SQL uses `$wpdb->prepare`,
uploads go through `media_handle_sideload` with MIME validation, demo import is admin-only,
front-end forms have honeypots and rate limiting.

## Support
AVIRAD — https://avirad.ir/
