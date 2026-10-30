<?php
/**
 * Social Share Addons Core.
 *
 * @package WOPB\SocialShare
 * @since v.3.2.0
 */

namespace WOPB;

defined( 'ABSPATH' ) || exit;

/**
 * SocialShare class.
 */
class SocialShare {

	/**
	 * Setup class.
	 *
	 * @since v.3.2.0
	 */
	public function __construct() {
		$position_type   = wopb_function()->get_setting( 'social_share_position_type' );
		$position_inside = wopb_function()->get_setting( 'social_share_inside_position' );

		if ( 'inside' === $position_type ) {
			if ( 'before_meta' === $position_inside ) {
				add_action( 'woocommerce_product_meta_start', array( $this, 'social_share_content' ) );
			} elseif ( 'after_meta' === $position_inside ) {
				add_action( 'woocommerce_product_meta_end', array( $this, 'social_share_content' ) );
			}
		} elseif ( 'sticky' === $position_type ) {
			add_action( 'wp_footer', array( $this, 'social_share_content' ) );
		}

		// CSS Generator
		add_action( 'wopb_save_settings', array( $this, 'generate_css' ), 10, 1 );
	}

	/**
	 * CSS Generator
	 *
	 * @param $key
	 * @return void
	 * @since v.3.2.0
	 */
	public function generate_css( $key ) {
		if ( 'wopb_social_share' === $key && method_exists( wopb_function(), 'convert_css' ) ) {
			$settings   = wopb_function()->get_setting();
			$item_style = $settings['social_share_typo'];
			if ( 'yes' !== $settings['social_share_brand_show'] ) {
				$item_style = array_merge( $settings['social_share_typo'], $settings['social_share_bg'] );
			}

			$css      = '.wopb-social-share-wrapper .wopb-share-items{gap: ' . wopb_function()->sanitize_css_number( $settings['social_share_gap'] ) . 'px;}';
			$css     .= ' ' . ( 'inside' === $settings['social_share_position_type'] ? '.product-template-default' : '' ) . ' .wopb-social-share-wrapper .wopb-share-item a{';
				$css .= wopb_function()->convert_css( 'general', $item_style );
				$css .= 'padding: ' . wopb_function()->convert_css( 'dimension', $settings['social_share_padding'] ) . ';';
				$css .= wopb_function()->convert_css(
					'border',
					array(
						'border' => $settings['social_share_border']['border'] ?? 0,
						'color'  => $settings['social_share_border']['color'] ?? '',
					)
				);
				$css .= wopb_function()->convert_css( 'radius', $settings['social_share_radius'] );
			$css     .= '}';
			$css     .= ' ' . ( 'inside' === $settings['social_share_position_type'] ? '.product-template-default' : '' ) . ' .wopb-social-share-wrapper .wopb-share-item a:hover{';
				$css .= wopb_function()->convert_css( 'hover', $item_style );
			$css     .= '}';
			$css     .= '.wopb-social-share-wrapper .wopb-share-item svg{';
				$icon_size = wopb_function()->sanitize_css_number( $settings['social_share_icon_size'] );
				$css      .= 'width : ' . ( ! empty( $icon_size ) ? $icon_size : 20 ) . 'px;';
				$css      .= 'height : ' . ( ! empty( $icon_size ) ? $icon_size : 20 ) . 'px;';
			$css     .= '}';

			if ( 'yes' !== $settings['social_share_brand_show'] ) {
				$css     .= '.wopb-social-share-wrapper .wopb-share-item svg, .wopb-social-share-wrapper .wopb-share-item svg path{';
					$css .= isset( $settings['social_share_typo']['color'] ) ? 'fill: ' . wopb_function()->sanitize_css_color( $settings['social_share_typo']['color'] ) . ';' : '';
				$css     .= '}';
				$css     .= '.wopb-social-share-wrapper .wopb-share-item svg:hover, .wopb-social-share-wrapper .wopb-share-item svg:hover path{';
					$css .= isset( $settings['social_share_typo']['hover_color'] ) ? 'fill: ' . wopb_function()->sanitize_css_color( $settings['social_share_typo']['hover_color'] ) . ';' : '';
				$css     .= '}';
			}
			wopb_function()->update_css( $key, 'add', $css );
		}
	}

	/**
	 * Social Share Content
	 *
	 * @return NULL
	 * @since v.4.0.0
	 */
	public function social_share_content() {
		if ( is_product() ) {
			global $product;
			$position   = wopb_function()->get_setting( 'social_share_position_type' );
			$label_show = wopb_function()->get_setting( 'social_share_label_show' );

			$wrapper_class = ' wopb-' . $position;
			if ( 'sticky' === $position ) {
				$wrapper_class .= ' wopb-' . wopb_function()->get_setting( 'social_share_sticky' );
			}
			$total_share = get_post_meta( $product->get_id(), 'wopb_share_count', true );
			$total_share = absint( $total_share );

			echo '<div class="wopb-social-share-wrapper ' . esc_attr( $wrapper_class ) . '">';
				echo '<div class="wopb-share-items">';
			if ( wopb_function()->get_setting( 'social_share_count_show' ) === 'yes' ) {
				echo '<span class="wopb-share-count-section">';
					echo '<span class="wopb-share-count-inside">';
						echo '<span class="wopb-share-count">' . esc_html( $total_share ) . '</span>';
				if ( 'yes' === $label_show ) {
					echo esc_html( wopb_function()->get_setting( 'social_share_count_lvl' ) );
				}
					echo '</span>';
						echo '</span>';
			}
			foreach ( wopb_function()->get_setting( 'social_share_media' ) as $share_item ) {
				echo '<span class="wopb-share-item' . ( wopb_function()->get_setting( 'social_share_brand_show' ) === 'yes' ? ' wopb-' . esc_attr( sanitize_html_class( $share_item['key'] ) ) : '' ) . '" postId="' . absint( $product->get_id() ) . '" count="' . esc_attr( $total_share ) . '">';
					echo '<a href="javascript:" data-url="' . esc_url( $this->share_link( $share_item['key'], get_permalink( $product->get_id() ) ) ) . '">';
				if ( wopb_function()->get_setting( 'social_share_icon_show' ) === 'yes' ) {
					echo wp_kses( apply_filters( 'wopb_social_share_icon', wopb_function()->svg_icon( $share_item['key'] ), $share_item['key'] ), wopb_function()->allowed_html_tags() );
				}
				if ( 'yes' === $label_show ) {
					echo esc_html( $share_item['label'] );
				}
					echo '</a>';
						echo '</span>';
			}
				echo '</div>';
			echo '</div>';
		}
	}

	/**
	 * Social Share Link
	 *
	 * @param $key
	 * @param $post_link
	 * @return string
	 * @since v.4.0.0
	 */
	public function share_link( $key = 'facebook', $post_link = '' ) {
		$share_links = apply_filters(
			'wopb_social_share_networks',
			array(
				'facebook'  => 'https://www.facebook.com/sharer.php?u=' . $post_link,
				'twitter'   => 'https://twitter.com/share?url=' . $post_link,
				'linkedin'  => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $post_link,
				'pinterest' => 'https://pinterest.com/pin/create/link/?url=' . $post_link,
				'reddit'    => 'https://www.reddit.com/submit?url=' . $post_link,
				'skype'     => 'https://web.skype.com/share?url=' . $post_link,
			),
			$post_link
		);
		return isset( $share_links[ $key ] ) ? $share_links[ $key ] : '';
	}
}
