<?php
/**
 * Nav menu walkers that reproduce the template markup (dropdowns, icons, badges).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Desktop primary navigation.
 *
 * ul.nav > li.nav__item[data-nav-dropdown] > a.nav__link + div.dropdown > a.dropdown__link
 * Menu item CSS classes: "hot" (red highlight), "badge-new", "badge-sale", "icon-<name>".
 */
class Nexora_Walker_Nav extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div class="dropdown">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</div>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes  = (array) $item->classes;
		$has_kids = in_array( 'menu-item-has-children', $classes, true );
		$current  = in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true ) || in_array( 'current_page_parent', $classes, true );
		$icon     = '';
		$badge    = '';
		foreach ( $classes as $class ) {
			if ( 0 === strpos( $class, 'icon-' ) ) {
				$icon = substr( $class, 5 );
			}
			if ( 'badge-new' === $class ) {
				$badge = nexora_badge( __( 'New', 'nexora' ), 'new' );
			}
			if ( 'badge-sale' === $class ) {
				$badge = nexora_badge( '%', 'sale' );
			}
		}
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$atts  = ' href="' . esc_url( $item->url ?: '#' ) . '"';
		if ( $item->target ) {
			$atts .= ' target="' . esc_attr( $item->target ) . '" rel="noopener"';
		}
		if ( $current ) {
			$atts .= ' aria-current="page"';
		}

		if ( 0 === $depth ) {
			$li_class = 'nav__item' . ( $current ? ' is-active' : '' );
			$output  .= '<li class="' . $li_class . '"' . ( $has_kids ? ' data-nav-dropdown' : '' ) . '>';
			$a_class  = 'nav__link' . ( in_array( 'hot', $classes, true ) ? ' nav__link--hot' : '' );
			$output  .= '<a class="' . $a_class . '"' . $atts . ( $has_kids ? ' aria-haspopup="true" aria-expanded="false"' : '' ) . '>';
			$output  .= ( $icon ? nexora_icon( $icon, 'xs' ) : '' ) . esc_html( $title ) . $badge;
			$output  .= $has_kids ? nexora_icon( 'chevron-down', 'xs', 'nav__caret' ) : '';
			$output  .= '</a>';
		} else {
			$output .= '<a class="dropdown__link"' . $atts . '>' . ( $icon ? nexora_icon( $icon, 'xs' ) : '' ) . esc_html( $title ) . $badge . '</a>';
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</li>';
		}
	}
}

/**
 * Mobile drawer navigation with collapsible sub-menus.
 */
class Nexora_Walker_Mobile extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div class="mobile-nav__sub">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</div>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes  = (array) $item->classes;
		$has_kids = in_array( 'menu-item-has-children', $classes, true );
		$current  = in_array( 'current-menu-item', $classes, true );
		$icon     = 'chevron-left';
		foreach ( $classes as $class ) {
			if ( 0 === strpos( $class, 'icon-' ) ) {
				$icon = substr( $class, 5 );
			}
		}
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$url   = esc_url( $item->url ?: '#' );

		if ( 0 === $depth ) {
			$output .= '<li class="mobile-nav__item">';
			if ( $has_kids ) {
				$sub_id  = 'msub-' . (int) $item->ID;
				$output .= '<div class="mobile-nav__link' . ( $current ? ' is-active' : '' ) . '"><a href="' . $url . '" class="mobile-nav__link-inner">' . nexora_icon( $icon, 'sm' ) . esc_html( $title ) . '</a>';
				$output .= '<button type="button" class="mobile-nav__toggle" aria-expanded="false" aria-controls="' . esc_attr( $sub_id ) . '" aria-label="' . esc_attr( $title ) . '" data-mobile-sub-toggle>' . nexora_icon( 'chevron-down', 'xs' ) . '</button></div>';
				$output .= '<div class="mobile-nav__sub" id="' . esc_attr( $sub_id ) . '">';
				$this->open_sub = true;
			} else {
				$output .= '<a class="mobile-nav__link' . ( $current ? ' is-active' : '' ) . '" href="' . $url . '">' . nexora_icon( $icon, 'sm' ) . esc_html( $title ) . '</a>';
			}
		} else {
			$output .= '<a href="' . $url . '"' . ( $current ? ' aria-current="page"' : '' ) . '>' . esc_html( $title ) . '</a>';
		}
	}

	protected $open_sub = false;

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			if ( in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
				$output .= '</div>';
			}
			$output .= '</li>';
		}
	}

	// Sub-levels are rendered inside the wrapper opened in start_el, so skip the default lvl wrappers.
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( ! $element ) {
			return;
		}
		$id_field = $this->db_fields['id'];
		$id       = $element->$id_field;
		$this->start_el( $output, $element, $depth, ...array_values( $args ) );
		if ( ( 0 === $max_depth || $max_depth > $depth + 1 ) && isset( $children_elements[ $id ] ) ) {
			foreach ( $children_elements[ $id ] as $child ) {
				$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
			}
			unset( $children_elements[ $id ] );
		}
		$this->end_el( $output, $element, $depth, ...array_values( $args ) );
	}
}

/**
 * Simple flat list walker for top bar / footer columns (li > a with optional icon).
 */
class Nexora_Walker_Flat extends Walker_Nav_Menu {

	private $link_class;
	private $item_class;
	private $sep;

	public function __construct( $item_class = '', $link_class = '', $sep = false ) {
		$this->item_class = $item_class;
		$this->link_class = $link_class;
		$this->sep        = $sep;
	}

	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		if ( $depth > 0 ) {
			return;
		}
		$icon = '';
		foreach ( (array) $item->classes as $class ) {
			if ( 0 === strpos( $class, 'icon-' ) ) {
				$icon = substr( $class, 5 );
			}
		}
		$title   = apply_filters( 'the_title', $item->title, $item->ID );
		$target  = $item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '';
		$output .= '<li' . ( $this->item_class ? ' class="' . esc_attr( $this->item_class ) . '"' : '' ) . '>';
		$output .= '<a' . ( $this->link_class ? ' class="' . esc_attr( $this->link_class ) . '"' : '' ) . ' href="' . esc_url( $item->url ?: '#' ) . '"' . $target . '>' . ( $icon ? nexora_icon( $icon, 'xs' ) : '' ) . esc_html( $title ) . '</a></li>';
		if ( $this->sep ) {
			$output .= '<li class="topbar__sep" aria-hidden="true"></li>';
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/**
 * Add "icon-*" hint to the menu item class help text.
 */
function nexora_menu_item_help() {
	echo '<p class="description" style="margin-top:8px">' . esc_html__( 'Nexora tip: add a CSS class like icon-truck, hot, badge-new or badge-sale to a menu item to show an icon or a badge (enable "CSS Classes" in Screen Options).', 'nexora' ) . '</p>';
}
add_action( 'admin_footer-nav-menus.php', 'nexora_menu_item_help' );

/**
 * Trim trailing separators from top bar lists (rendered with Nexora_Walker_Flat sep=true).
 *
 * @param string $html Menu html.
 * @return string
 */
function nexora_trim_topbar_sep( $html ) {
	return preg_replace( '#<li class="topbar__sep" aria-hidden="true"></li>\s*</ul>#', '</ul>', $html );
}
