<?php
namespace WOPB\blocks;

defined( 'ABSPATH' ) || exit;

class Product_Breadcrumb {

	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function get_attributes() {
		return array(
			'breadcrumbSeparator' => '/',
			'currentPostId'       => '',
		);
	}

	public function register() {
		register_block_type(
			'product-blocks/product-breadcrumb',
			array(
				'editor_script'   => 'wopb-blocks-builder-script',
				'editor_style'    => 'wopb-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
			)
		);
	}

	public function content( $attr ) {
		global $product;
		$product       = wc_get_product(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WooCommerce's own $product global, not plugin-defined.
		$block_name    = 'product-breadcrumb';
		$wraper_before = $wraper_after = $content = '';
		$attr          = wp_parse_args( $attr, $this->get_attributes() );

		if ( ! empty( $product ) ) {
			$attr['className'] = ! empty( $attr['className'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';

			$wraper_before .= '<div ' . ( isset( $attr['advanceId'] ) ? 'id="' . esc_attr( sanitize_html_class( $attr['advanceId'] ) ) . '" ' : '' ) . ' class="wp-block-product-blocks-' . esc_attr( $block_name ) . ' wopb-block-' . esc_attr( sanitize_html_class( $attr['blockId'] ) ) . ' ' . esc_attr( $attr['className'] ) . '">';
			$wraper_before .= '<div class="wopb-product-wrapper">';

			$wopb_buffer_level_1 = ob_get_level();
			ob_start();
			try {
				$settings = isset( $attr['breadcrumbSeparator'] ) ? array( 'delimiter' => '<span class="breadcrumb-separator" >' . esc_html( $attr['breadcrumbSeparator'] ) . '</span>' ) : array();
				woocommerce_breadcrumb( $settings );
				$content .= wp_kses( ob_get_clean(), wopb_function()->allowed_form_html() );
			} finally {
				while ( ob_get_level() > $wopb_buffer_level_1 ) {
					if ( ! ob_end_clean() ) {
						break;
					}
				}
			}

			$wraper_after .= '</div>';
			$wraper_after .= '</div>';
		}

		return $wraper_before . $content . $wraper_after;
	}
}
