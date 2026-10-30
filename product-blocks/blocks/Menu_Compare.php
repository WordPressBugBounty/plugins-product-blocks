<?php
namespace WOPB\blocks;

defined( 'ABSPATH' ) || exit;

class Menu_Compare {
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function get_attributes() {
		return array(
			'iconType'      => 'icon',
			'iconSvg'       => 'compare_1',
			'showCount'     => true,
			'showLabel'     => false,
			'labelText'     => 'Compare',
			'labelPosition' => 'right',
			'currentPostId' => '',
		);
	}

	public function register() {
		register_block_type(
			'product-blocks/menu-compare',
			array(
				'editor_script'   => 'wopb-blocks-editor-script',
				'editor_style'    => 'wopb-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
			)
		);
	}

	/**
	 * This
	 *
	 * @return string
	 */
	public function content( $attr, $noAjax = false ) {
		$attr = wp_parse_args( $attr, $this->get_attributes() );

		$wrapper_before    = '';
		$block_name        = 'menu-compare';
		$attr['className'] = ! empty( $attr['className'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';
		$attr['align']     = ! empty( $attr['align'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['align'] ) : '';
		$attr['iconType']  = ! empty( $attr['iconType'] ) ? $attr['iconType'] : 'icon';
		$attr['labelText'] = wp_kses( $attr['labelText'], wopb_function()->allowed_html_tags() );
		$action_added      = wopb_function()->get_setting( 'compare_action_added' );
		$compare_page      = wopb_function()->get_setting( 'compare_page' );
		$compare_data      = apply_filters( 'wopb_menu_compare_data', array( 'action' => 'menu_block' ) );

		$btn_class = 'wopb-menu-compare-wrapper wopb-label-' . sanitize_html_class( $attr['labelPosition'] );

		$href_link = '';
		$page      = get_post( $compare_page );
		if ( 'redirect' === $action_added ) {
			if ( $compare_page && $page && 'page' === $page->post_type && 'publish' === $page->post_status ) {
				$href_link .= 'href="' . esc_url( get_permalink( $compare_page ) ) . '"';
			} elseif ( $page = get_post( 'compare' ) && 'publish' === $page->post_status && $link = get_permalink( get_page_by_path( 'compare' ) ) ) {
				$href_link .= 'href="' . esc_url( $link ) . '"';
			}
		} else {
			$btn_class .= ' wopb-menu-compare-btn';
		}

		$wrapper_before         .= '<div ' . ( isset( $attr['advanceId'] ) ? 'id="' . esc_attr( sanitize_html_class( $attr['advanceId'] ) ) . '" ' : '' ) . ' class="wp-block-product-blocks-' . esc_attr( $block_name ) . ' wopb-block-' . esc_attr( $attr['blockId'] ) . ' ' . esc_attr( $attr['className'] ) . ( $attr['align'] ? ' align' . esc_attr( $attr['align'] ) : '' ) . '">';
			$wrapper_before     .= '<a ';
				$wrapper_before .= $href_link;
				$wrapper_before .= 'class="' . esc_attr( $btn_class ) . '"';
				$wrapper_before .= $this->sanitize_button_attributes( $compare_data['button_attr'] ?? '' );
			$wrapper_before     .= '>';
				$wrapper_before .= '<div class="wopb-menu-compare-icon">';
		if ( 'icon' === $attr['iconType'] ) {
			$wrapper_before .= wp_kses( wopb_function()->svg_icon( $attr['iconSvg'] ), $this->allowed_icon_html() );
		} else {
			$wrapper_before .= '<img src="' . esc_url( isset( $attr['iconImage'] ) && $attr['iconImage']['url'] ? $attr['iconImage']['url'] : WOPB_URL . 'assets/img/wopb_fallback.jpg' ) . '" />';
		}
		if ( $attr['showCount'] ) {
			$wrapper_before     .= '<div class="wopb-menu-compare-count">';
				$wrapper_before .= esc_html( ( isset( $compare_data['c_items'] ) && is_array( $compare_data['c_items'] ) ) ? count( $compare_data['c_items'] ) : 0 );
			$wrapper_before     .= '</div>';
		}
				$wrapper_before .= '</div>';
		if ( $attr['showLabel'] && $attr['labelText'] ) {
			$wrapper_before .= '<div class="wopb-menu-compare-label">' . esc_html( $attr['labelText'] );
			$wrapper_before .= '</div>';
		}

			$wrapper_before .= '</a>';
		$wrapper_before     .= '</div>';

		return $wrapper_before;
	}

	/**
	 * Allowed HTML for the bundled SVG icons: the shared list plus the SVG attributes some icons use.
	 *
	 * @return array
	 */
	private function allowed_icon_html() {
		return wopb_function()->allowed_html_tags(
			array(
				'svg'    => array(
					'id'        => true,
					'data-name' => true,
				),
				'g'      => array( 'data-name' => true ),
				'path'   => array(
					'id'        => true,
					'data-name' => true,
					'fill-rule' => true,
					'transform' => true,
				),
				'rect'   => array(
					'x' => true,
					'y' => true,
				),
				'circle' => array(
					'stroke'       => true,
					'stroke-width' => true,
				),
			)
		);
	}

	/**
	 * Escape the button attributes returned by the menu data filter, including URLs read by JavaScript.
	 *
	 * @param mixed $attributes Button attribute markup.
	 * @return string
	 */
	private function sanitize_button_attributes( $attributes ) {
		if ( ! is_string( $attributes ) ) {
			return '';
		}
		$output = '';
		foreach ( wp_kses_hair( $attributes, wp_allowed_protocols() ) as $attribute ) {
			$name = strtolower( $attribute['name'] );
			if ( ! preg_match( '/^(?:data-[a-z0-9_-]+|aria-[a-z-]+|title|role|tabindex|href)$/D', $name ) ) {
				continue;
			}
			$value   = wp_specialchars_decode( $attribute['value'], ENT_QUOTES );
			$value   = in_array( $name, array( 'href', 'data-redirect', 'data-login-redirect' ), true ) ? esc_url( $value ) : esc_attr( $value );
			$output .= ' ' . $name . '="' . $value . '"';
		}
		return $output;
	}
}
