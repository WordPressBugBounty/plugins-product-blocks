<?php
namespace WOPB\blocks;

defined( 'ABSPATH' ) || exit;

class Thankyou_Address {
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function get_attributes() {
		return array(
			'billingText'   => 'Billing Address',
			'shippingText'  => 'Shipping Address',
			'currentPostId' => '',
		);
	}

	public function register() {
		register_block_type(
			'product-blocks/thankyou-address',
			array(
				'editor_script'   => 'wopb-blocks-builder-script',
				'editor_style'    => 'wopb-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
			)
		);
	}

	public function content( $attr, $noAjax = false ) {
		if ( function_exists( 'WC' ) && is_checkout() && is_wc_endpoint_url( 'order-received' ) ) {
			$block_name        = 'thankyou-address';
			$wraper_before     = $wraper_after = $content = '';
			$attr              = wp_parse_args( $attr, $this->get_attributes() );
			$attr['className'] = ! empty( $attr['className'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';

			$wraper_before     .= '<div ' . ( isset( $attr['advanceId'] ) ? 'id="' . esc_attr( sanitize_html_class( $attr['advanceId'] ) ) . '" ' : '' ) . ' class="wp-block-product-blocks-' . esc_attr( $block_name ) . ' wopb-block-' . esc_attr( sanitize_html_class( $attr['blockId'] ) ) . ' ' . esc_attr( $attr['className'] ) . '">';
				$wraper_before .= '<div class="wopb-product-wrapper">';
			if ( ! is_admin() ) {
				if ( isset( WC()->customer ) ) {
					$wopb_buffer_level_1 = ob_get_level();
					ob_start();
					try {
						require_once WOPB_PATH . 'blocks/woo/thank_you/address/Template.php';
						$content .= wp_kses(
							ob_get_clean(),
							wopb_function()->allowed_form_html(
								array(
									'noscript' => array(),
									'wc-order-attribution-inputs' => array(),
									'span'     => array( 'translate' => true ),
								)
							)
						);
					} finally {
						while ( ob_get_level() > $wopb_buffer_level_1 ) {
							if ( ! ob_end_clean() ) {
								break;
							}
						}
					}
				}
			}
				$wraper_after .= '</div>';
			$wraper_after     .= '</div>';

			return $wraper_before . $content . $wraper_after;
		}
	}
}
