<?php
namespace WOPB\blocks;

defined( 'ABSPATH' ) || exit;

class Product_Short {

	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register() {
		register_block_type(
			'product-blocks/product-short',
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
		$block_name    = 'product-short';
		$wraper_before = $wraper_after = $content = '';

		if ( ! empty( $product ) ) {
			$attr['className'] = ! empty( $attr['className'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';

			$wraper_before     .= '<div ' . ( isset( $attr['advanceId'] ) ? 'id="' . esc_attr( sanitize_html_class( $attr['advanceId'] ) ) . '" ' : '' ) . ' class="wp-block-product-blocks-' . esc_attr( $block_name ) . ' wopb-block-' . esc_attr( sanitize_html_class( $attr['blockId'] ) ) . ' ' . esc_attr( $attr['className'] ) . '">';
				$wraper_before .= '<div class="wopb-product-wrapper">';

				$wopb_buffer_level_1 = ob_get_level();
				ob_start();
			try {
				wc_get_template( 'single-product/short-description.php' );
				$content .= wp_kses( ob_get_clean(), wopb_function()->allowed_form_html() );
			} finally {
				while ( ob_get_level() > $wopb_buffer_level_1 ) {
					if ( ! ob_end_clean() ) {
						break;
					}
				}
			}

				$wraper_after .= '</div>';
			$wraper_after     .= '</div>';
		}

		return $wraper_before . $content . $wraper_after;
	}
}
