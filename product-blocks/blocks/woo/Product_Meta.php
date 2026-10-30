<?php
namespace WOPB\blocks;

defined( 'ABSPATH' ) || exit;

class Product_Meta {

	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function get_attributes() {
		return array(
			'metaSku'       => true,
			'metaCategory'  => true,
			'metaBrand'     => false,
			'metaTag'       => true,
			'metaLabelShow' => true,
			'labelSku'      => 'sku : ',
			'labelCat'      => 'category :  ',
			'labelBrand'    => 'brand : ',
			'labelTag'      => 'tag : ',
			'currentPostId' => '',
		);
	}

	public function register() {
		register_block_type(
			'product-blocks/product-meta',
			array(
				'editor_script'   => 'wopb-blocks-builder-script',
				'editor_style'    => 'wopb-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
			)
		);
	}

	public function list_items( $terms, $type ) {
		$inc     = 1;
		$content = '';
		foreach ( $terms as $term_id ) {
			$term = get_term_by( 'id', $term_id, $type );
			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}
			$term_link = get_term_link( $term, $type );
			if ( is_wp_error( $term_link ) ) {
				continue;
			}
			if ( $inc > 1 ) {
				$content .= ', ';
			}
			$content .= '<a class="wopb-meta-list-sku meta-block__value" href="' . esc_url( $term_link ) . '">' . esc_html( $term->name ) . '</a>';
			++$inc;
		}
		return $content;
	}

	public function content( $attr ) {
		global $product;
		$product       = wc_get_product(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WooCommerce's own $product global, not plugin-defined.
		$block_name    = 'product-meta';
		$wraper_before = $wraper_after = $content = '';
		$attr          = wp_parse_args( $attr, $this->get_attributes() );

		if ( ! empty( $product ) ) {
			$attr['className'] = ! empty( $attr['className'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';

			$wraper_before              .= '<div ' . ( isset( $attr['advanceId'] ) ? 'id="' . esc_attr( sanitize_html_class( $attr['advanceId'] ) ) . '" ' : '' ) . ' class="wp-block-product-blocks-' . esc_attr( $block_name ) . ' wopb-block-' . esc_attr( sanitize_html_class( $attr['blockId'] ) ) . ' ' . esc_attr( $attr['className'] ) . '">';
				$content                .= '<div class="wopb-product-wrapper">';
					$wopb_buffer_level_1 = ob_get_level();
					ob_start();
			try {
				do_action( 'woocommerce_product_meta_start' );
				$content .= wp_kses( ob_get_clean(), wopb_function()->allowed_form_html() );
			} finally {
				while ( ob_get_level() > $wopb_buffer_level_1 ) {
					if ( ! ob_end_clean() ) {
						break;
					}
				}
			}
			if ( $attr['metaSku'] ) {
				$content .= '<div class="wopb-meta-sku"><div class="product_meta" style="display: contents !important;">';
				if ( $attr['metaLabelShow'] ) {
						$content .= '<span class="wopb-meta-label-sku meta-block__label">' . esc_html( $attr['labelSku'] ) . '</span>';
				}
					$content     .= '<span class="wopb-meta-list-sku meta-block__value sku">' . esc_html( $product->get_sku() ) . '</span>';
						$content .= '</div></div>';
			}
			if ( $attr['metaCategory'] ) {
				$terms = $product->get_category_ids();
				if ( count( $terms ) ) {
					$content .= '<div class="wopb-meta-cat">';
					if ( $attr['metaLabelShow'] ) {
							$content .= '<div class="wopb-meta-label-cat meta-block__label">' . esc_html( $attr['labelCat'] ) . '</div>';
					}
						$content     .= '<div class="wopb-meta-list-cat meta-block__value">';
							$content .= wp_kses( $this->list_items( $terms, 'product_cat' ), wopb_function()->allowed_html_tags() );
						$content     .= '</div>';
							$content .= '</div>';
				}
			}
			if ( $attr['metaBrand'] && taxonomy_exists( 'product_brand' ) ) {
				// intentionally did not use $product->get_brand_ids
				// because it is a recent function of woocommerce 10.3
				// many users are still using older versions.
				$brand_terms = get_the_terms( $product->get_id(), 'product_brand' );
				$brand_terms = ( ! empty( $brand_terms ) && ! is_wp_error( $brand_terms ) ) ? wp_list_pluck( $brand_terms, 'term_id' ) : array();
				if ( count( $brand_terms ) ) {
					$content .= '<div class="wopb-meta-brand">';
					if ( $attr['metaLabelShow'] ) {
							$content .= '<div class="wopb-meta-label-brand meta-block__label">' . esc_html( $attr['labelBrand'] ) . '</div>';
					}
						$content     .= '<div class="wopb-meta-list-brand meta-block__value">';
							$content .= wp_kses( $this->list_items( $brand_terms, 'product_brand' ), wopb_function()->allowed_html_tags() );
						$content     .= '</div>';
							$content .= '</div>';
				}
			}
			if ( $attr['metaTag'] ) {
				$tag_terms = $product->get_tag_ids();
				if ( count( $tag_terms ) ) {
					$content .= '<div class="wopb-meta-tag">';
					if ( $attr['metaLabelShow'] ) {
							$content .= '<span class="wopb-meta-label-tag meta-block__label">' . esc_html( $attr['labelTag'] ) . '</span>';
					}
						$content     .= '<div class="wopb-meta-list-tag">';
							$content .= wp_kses( $this->list_items( $tag_terms, 'product_tag' ), wopb_function()->allowed_html_tags() );
						$content     .= '</div>';
							$content .= '</div>';
				}
			}
					$content                .= '<div class="wopb-product-meta-end">';
						$wopb_buffer_level_2 = ob_get_level();
						ob_start();
			try {
				do_action( 'woocommerce_product_meta_end' );
				do_action( 'wopb_product_meta_end' );
				$content .= wp_kses( ob_get_clean(), wopb_function()->allowed_form_html() );
			} finally {
				while ( ob_get_level() > $wopb_buffer_level_2 ) {
					if ( ! ob_end_clean() ) {
						break;
					}
				}
			}
				$content  .= '</div>';
				$content  .= '</div>';
			$wraper_after .= '</div>';
		}

		return $wraper_before . $content . $wraper_after;
	}
}
