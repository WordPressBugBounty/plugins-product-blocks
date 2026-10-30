<?php
namespace WOPB\blocks;

defined( 'ABSPATH' ) || exit;

class Product_Title {

	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register() {
		register_block_type(
			'product-blocks/product-title',
			array(
				'editor_script'   => 'wopb-blocks-builder-script',
				'editor_style'    => 'wopb-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
				'currentPostId'   => '',
			)
		);
	}

	public function content( $attr ) {
		global $product;
		$block_name    = 'product-title';
		$wraper_before = $wraper_after = $content = '';

		$product = wc_get_product(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WooCommerce's own $product global, not plugin-defined.

		if ( ! empty( $product ) ) {
			$title_tag         = isset( $attr['titleTag'] ) && in_array( $attr['titleTag'], wopb_function()->allowed_block_tags(), true ) ? $attr['titleTag'] : 'h1';
			$attr['className'] = ! empty( $attr['className'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';

			$wraper_before              .= '<div ' . ( isset( $attr['advanceId'] ) ? 'id="' . esc_attr( sanitize_html_class( $attr['advanceId'] ) ) . '" ' : '' ) . ' class="wp-block-product-blocks-' . esc_attr( $block_name ) . ' wopb-block-' . esc_attr( sanitize_html_class( $attr['blockId'] ) ) . ' ' . esc_attr( $attr['className'] ) . '">';
				$wraper_before          .= '<div class="wopb-product-wrapper">';
					$content            .= '<' . esc_attr( $title_tag ) . ' class="product_title entry-title wopb-builder-product-title">' . wp_kses_post( $product->get_title() ) . '</' . esc_attr( $title_tag ) . '>';
					$wopb_buffer_level_1 = ob_get_level();
					ob_start();
			try {
				do_action( 'wopb_after_single_product_title' );
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
