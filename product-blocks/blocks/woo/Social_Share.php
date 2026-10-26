<?php
namespace WOPB\blocks;

defined( 'ABSPATH' ) || exit;

class Social_Share {
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}
	public function get_attributes() {
		return array(
			'repeatableSocialShare' => array(
				0 => array(
					'type'           => 'facebook',
					'enableLabel'    => true,
					'label'          => 'Facebook',
					'iconColor'      => '#fff',
					'iconColorHover' => '#d2d2d2',
					'shareBg'        => '#4267B2',
					'bgHoverColor'   => '#f5f5f5',
				),
				1 => array(
					'type'           => 'twitter',
					'enableLabel'    => true,
					'label'          => 'Twitter',
					'iconColor'      => '#fff',
					'iconColorHover' => '#d2d2d2',
					'shareBg'        => '#1DA1F2',
					'bgHoverColor'   => '#f5f5f5',
				),
				2 => array(
					'type'           => 'pinterest',
					'enableLabel'    => true,
					'label'          => 'Pinterest',
					'iconColor'      => '#fff',
					'iconColorHover' => '#d2d2d2',
					'shareBg'        => '#E60023',
					'bgHoverColor'   => '#f5f5f5',
				),
				3 => array(
					'type'           => 'linkedin',
					'enableLabel'    => true,
					'label'          => 'Linkedin',
					'iconColor'      => '#fff',
					'iconColorHover' => '#d2d2d2',
					'shareBg'        => '#0A66C2',
					'bgHoverColor'   => '#f5f5f5',
				),
				4 => array(
					'type'           => 'mail',
					'enableLabel'    => true,
					'label'          => 'Mail',
					'iconColor'      => '#fff',
					'iconColorHover' => '#d2d2d2',
					'shareBg'        => '#EA4335',
					'bgHoverColor'   => '#f5f5f5',
				),
			// 5 => array('type' => 'whatsapp','enableLabel' => true,'label' => 'WhatsApp','iconColor' => '#fff','iconColorHover' => '#d2d2d2','shareBg' => '#009edc','bgHoverColor' => '#f5f5f5'),
			// 6 => array('type' => 'skype','enableLabel' => true,'label' => 'Skype','iconColor' => '#fff','iconColorHover' => '#d2d2d2','shareBg' => '#25d366','bgHoverColor' => '#f5f5f5'),
			),
			'disInline'             => true,
			'shareLabelShow'        => true,
			'shareLabelStyle'       => 'style2',
			'shareCountShow'        => false,
			'shareCountLabel'       => 'Shares',
			'enableLabel'           => false,
			'currentPostId'         => '',
		);
	}

	public function register() {
		register_block_type(
			'product-blocks/social-share',
			array(
				'editor_script'   => 'wopb-blocks-builder-script',
				'editor_style'    => 'wopb-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
			)
		);
	}

	public function share_link( $key = 'facebook', $post_link = '' ) {
		$share_links = array(
			'facebook'  => 'https://www.facebook.com/sharer.php?u=' . $post_link,
			'twitter'   => 'https://twitter.com/share?url=' . $post_link,
			'linkedin'  => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $post_link,
			'pinterest' => 'https://pinterest.com/pin/create/link/?url=' . $post_link,
			'whatsapp'  => 'https://api.whatsapp.com/send?text=' . $post_link,
			'messenger' => 'https://www.facebook.com/dialog/send?app_id=1904103319867886&amp;link=' . $post_link . '&amp;redirect_uri=' . $post_link,
			'mail'      => 'mailto:?body=' . $post_link,
			'reddit'    => 'https://www.reddit.com/submit?url=' . $post_link,
			'skype'     => 'https://web.skype.com/share?url=' . $post_link,
		);
		return isset( $share_links[ $key ] ) ? $share_links[ $key ] : '';
	}

	public function content( $attr, $noAjax = false ) {
		$block_name    = 'social-share';
		$wraper_before = $wraper_after = $wrapper_content = '';
		$attr          = wp_parse_args( $attr, $this->get_attributes() );
		$post_id       = get_the_ID();
		$post_link     = isset( $_SERVER['REQUEST_URI'] ) ? home_url( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '';

		$total_share = get_post_meta( $post_id, 'wopb_share_count', true );
		$total_share = absint( $total_share );

		if ( is_product() ) {
			$attr['className'] = ! empty( $attr['className'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';

			$wraper_before     .= '<div ' . ( isset( $attr['advanceId'] ) ? 'id="' . sanitize_html_class( $attr['advanceId'] ) . '" ' : '' ) . ' class="wp-block-product-blocks-' . $block_name . ' wopb-block-' . sanitize_html_class( $attr['blockId'] ) . ' ' . $attr['className'] . '">';
				$wraper_before .= '<div class="wopb-product-wrapper">';

					$wrapper_content     .= '<div class="wopb-share">';
						$wrapper_content .= '<div class="wopb-share-layout wopb-inline-' . ( $attr['disInline'] ? 'true' : 'false' ) . '">';
			if ( $attr['shareLabelShow'] ) {
				$wrapper_content .= '<div class="wopb-share-count-section wopb-share-count-section-' . sanitize_html_class( $attr['shareLabelStyle'] ) . '">';
				if ( 'style2' !== $attr['shareLabelStyle'] && $attr['shareCountShow'] ) {
								$wrapper_content .= '<span class="wopb-share-count">' . $total_share . '</span>';
				}
				if ( 'style2' === $attr['shareLabelStyle'] ) {
									$wrapper_content .= '<span class="wopb-share-icon-section">' . wopb_function()->svg_icon( 'share' ) . '</span>';
				}
				if ( 'style2' !== $attr['shareLabelStyle'] && $attr['shareCountLabel'] ) {
					$wrapper_content .= '<span class="wopb-share-label">' . esc_html( $attr['shareCountLabel'] ) . '</span>';
				}
												$wrapper_content .= '</div>';
			}
							$wrapper_content .= '<div class="wopb-share-item-inner-block">';

			foreach ( $attr['repeatableSocialShare'] as $key => $value ) {
				$share_type               = isset( $value['type'] ) ? sanitize_html_class( $value['type'] ) : '';
				$wrapper_content         .= '<div class="wopb-share-item wopb-repeat-' . sanitize_html_class( $key ) . ' wopb-social-' . $share_type . '" postId="' . absint( $post_id ) . '" count="' . $total_share . '">';
					$wrapper_content     .= '<a href="javascript:" class="wopb-share-item-' . $share_type . '" data-url="' . esc_url( $this->share_link( $share_type, $post_link ) ) . '">';
						$wrapper_content .= '<span class="wopb-share-item-icon">' . wopb_function()->svg_icon( $share_type ) . '</span>';
						$wrapper_content .= '' . $value['enableLabel'] && $attr['enableLabel'] ? '<span class="wopb-share-item-label">' . esc_html( $value['label'] ) . '</span>' : ' ';
					$wrapper_content     .= '</a>';
				$wrapper_content         .= '</div>';
			}
							$wrapper_content .= '</div>';
						$wrapper_content     .= '</div>';
					$wrapper_content         .= '</div>';

				$wraper_after .= '</div>';
			$wraper_after     .= '</div> ';
		}

		return $wraper_before . $wrapper_content . $wraper_after;
	}
}
