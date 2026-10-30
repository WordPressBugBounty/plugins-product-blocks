<?php
namespace WOPB\blocks;

defined( 'ABSPATH' ) || exit;

class Product_Grid_2 {

	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function get_attributes() {
		return array(
			'productView'             => 'grid',
			'columns'                 => array(
				'lg' => '3',
				'sm' => '2',
				'xs' => '1',
			),
			'slidesToShow'            => (object) array(
				'lg' => '3',
				'sm' => '2',
				'xs' => '1',
			),
			'autoPlay'                => true,
			'showDots'                => true,
			'showArrows'              => true,
			'slideSpeed'              => '3000',
			'showPrice'               => true,
			'showReview'              => true,
			'showCart'                => true,
			'showOutStock'            => true,
			'showInStock'             => false,
			'showShortDesc'           => true,
			'showSale'                => true,
			'showHot'                 => false,
			'showDeal'                => false,
			'filterShow'              => false,
			'headingShow'             => false,
			'paginationShow'          => false,
			'catShow'                 => true,
			'titleShow'               => true,
			'showImage'               => true,
			'disableFlip'             => false,
			'showVideo'               => true,
			'showVariationSwitch'     => true,
			'variationSwitchPosition' => 'before_title',
			'queryTax'                => 'product_cat',
			'arrowStyle'              => 'leftAngle2#rightAngle2',
			'headingText'             => 'Product Grid #2',
			'headingURL'              => '',
			'headingBtnText'          => 'View More',
			'headingStyle'            => 'style1',
			'headingTag'              => 'h2',
			'headingAlign'            => 'left',
			'subHeadingShow'          => false,
			'subHeadingText'          => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer ut sem augue. Sed at felis ut enim dignissim sodales.',
			'saleText'                => 'Sale!',
			'saleDesign'              => 'text',
			'saleStyle'               => 'classic',
			'hotText'                 => 'Hot',
			'dealText'                => 'Days|Hours|Minutes|Seconds',
			'shortDescLimit'          => 7,
			'titleTag'                => 'h3',
			'titleLength'             => 0,
			'cartText'                => '',
			'cartWidth'               => 'content',
			'cartActive'              => 'View Cart',
			'enableCatLink'           => true,
			'catPosition'             => 'none',
			'brandShow'               => false,
			'enableBrandLink'         => true,
			'brandPosition'           => 'none',
			'imgCrop'                 => 'full',
			'imgAnimation'            => 'none',
			'filterType'              => 'product_cat',
			'filterText'              => 'all',
			'filterCat'               => '[]',
			'filterTag'               => '["all"]',
			'filterAction'            => '[]',
			'filterActionText'        => 'Top Sale|Popular|On Sale|Most Rated|Top Rated|Featured|New Arrival',
			'filterMobile'            => true,
			'filterMobileText'        => 'More',
			'paginationType'          => 'pagination',
			'loadMoreText'            => 'Load More',
			'paginationText'          => 'Previous|Next',
			'paginationNav'           => 'textArrow',
			'paginationAjax'          => true,
			'queryNumber'             => 6,
			'overlayMetaList'         => '["_compare","_wishlist","_qview"]',
			'tooltipPosition'         => 'left',
			'ovrMetaInline'           => false,
			'currentPostId'           => '',
			'hoverMeta'               => true,
			'cartNoFollow'            => true,
		);
	}

	public function register() {
		register_block_type(
			'product-blocks/product-grid-2',
			array(
				'editor_script'   => 'wopb-blocks-editor-script',
				'editor_style'    => 'wopb-blocks-editor-css',
				'render_callback' => array( $this, 'content' ),
			)
		);
	}

	public function content( $attr, $noAjax = false ) {
		$attr = wp_parse_args( $attr, $this->get_attributes() );

		if ( ! $noAjax ) {
			$paged         = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
			$attr['paged'] = $paged ? $paged : 1;

			/**
			 * Filters the attributes of a block on its first render, before the products are queried.
			 *
			 * The block's own settings are in `$attr`. Add-ons use it to bring in state kept outside the
			 * block, such as a visitor's current filter choices.
			 *
			 * @since 4.6.0
			 * @param array  $attr       Block attributes.
			 * @param string $block_name Block slug, for example 'product-grid-1'.
			 */
			$attr = apply_filters( 'wopb_block_render_attributes', $attr, 'product-grid-2' );
		}

		$block_name            = 'product-grid-2';
		$wraper_before         = $wraper_after = $post_loop = '';
		$wrapper_main_content  = '';
		$recent_posts          = new \WP_Query( wopb_function()->get_query( $attr ) );
		$pageNum               = wopb_function()->get_page_number( $attr, $recent_posts->found_posts );
		$parsedOverlayMetaList = json_decode( $attr['overlayMetaList'], true );
		$item_calss            = wopb_function()->loop_item_classes();
		$allowed_html          = $this->allowed_loop_html();

		$slider_attr = wc_implode_html_attributes(
			array(
				'data-slidestoshow' => wopb_function()->slider_responsive_split( $attr['slidesToShow'] ),
				'data-autoplay'     => esc_attr( $attr['autoPlay'] ),
				'data-slidespeed'   => esc_attr( $attr['slideSpeed'] ),
				'data-showdots'     => esc_attr( $attr['showDots'] ),
				'data-showarrows'   => esc_attr( $attr['showArrows'] ),
			)
		);

		if ( $recent_posts->have_posts() ) {
			$attr['className'] = ! empty( $attr['className'] ) ? preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['className'] ) : '';
			$attr['align']     = ! empty( $attr['align'] ) ? 'align' . preg_replace( '/[^A-Za-z0-9_ -]/', '', $attr['align'] ) : '';
			$cart_params       = array(
				'cartText'        => $attr['cartText'],
				'cartActive'      => $attr['cartActive'],
				'tooltipPosition' => $attr['tooltipPosition'],
				'cartNoFollow'    => $attr['cartNoFollow'],
				'cartWidth'       => $attr['cartWidth'],
			);

			$wraper_before     .= '<div ' . ( isset( $attr['advanceId'] ) ? 'id="' . esc_attr( sanitize_html_class( $attr['advanceId'] ) ) . '" ' : '' ) . ' class="wp-block-product-blocks-' . esc_attr( $block_name ) . ' wopb-block-' . esc_attr( sanitize_html_class( $attr['blockId'] ) ) . ' ' . esc_attr( $attr['className'] ) . esc_attr( $attr['align'] ) . '">';
				$wraper_before .= '<div class="wopb-block-wrapper' . ( isset( $attr['layout'] ) ? ' wopb-has-gridlay' : '' ) . '">';

			if ( $attr['headingShow'] || $attr['filterShow'] ) {
				$wraper_before     .= '<div class="wopb-heading-filter">';
					$wraper_before .= '<div class="wopb-heading-filter-in">';

						// Heading
						include WOPB_PATH . 'blocks/template/heading.php';

				if ( ( $attr['filterShow'] ) && 'grid' === $attr['productView'] ) {
					$wraper_before .= '<div class="wopb-filter-navigation">';
					if ( $attr['filterShow'] ) {
						include WOPB_PATH . 'blocks/template/filter.php';
					}
							$wraper_before .= '</div>';
				}

					$wraper_before     .= '</div>';
						$wraper_before .= '</div>';
			}

					$wraper_before .= '<div class="wopb-wrapper-main-content">';
			if ( 'slide' === $attr['productView'] ) {
				$wrapper_main_content .= '<div class="wopb-product-blocks-slide" ' . wp_kses_post( $slider_attr ) . '>';
			} else {
				$wrapper_main_content .= '<div class="wopb-block-items-wrap wopb-block-row wopb-block-column-' . ( ! empty( $attr['columns']['lg'] ) ? intval( $attr['columns']['lg'] ) : 3 ) . '">';
			}

							$idx = $noAjax ? 1 : 0;
			while ( $recent_posts->have_posts() ) :
				$recent_posts->the_post();

				$image_data = $category_data = $title_data = $price_data = $review_data = $cart_data = $content_data  = $variationSwitcher_data = '';

				include WOPB_PATH . 'blocks/template/data.php';
				include WOPB_PATH . 'blocks/template/category.php';
				include WOPB_PATH . 'blocks/template/brand.php';

				if ( $product ) {
					$post_loop         .= '<div class="wopb-block-item ' . ( ! empty( $item_calss['wrapper'] ) ? esc_attr( $item_calss['wrapper'] ) : '' ) . '">';
						$post_loop     .= '<div class="wopb-block-content-wrap">';
							$post_loop .= '<div class="wopb-product-grid3-overlay"></div>';

							// Variation Switcher
					if ( $attr['showVariationSwitch'] ) {
								$variationSwitcher_data = wp_kses( (string) apply_filters( 'wopb_variation_switcher', '', $product ), $allowed_html );
					}

							// Image
					if ( $attr['showImage'] ) {
										$image_data .= '<div class="wopb-block-image wopb-block-image-' . esc_attr( $attr['imgAnimation'] ) . '">';

						if ( $attr['showSale'] || $attr['showHot'] ) {
							$image_data .= '<div class="wopb-onsale-hot">';
							if ( $attr['showSale'] && $product->is_on_sale() ) {
										$image_data .= '<span class="wopb-onsale wopb-onsale-' . esc_attr( $attr['saleStyle'] ) . '">';
								if ( 'digit' === $attr['saleDesign'] ) {
									$image_data .= '-' . esc_html( $_discount ); }
								if ( 'text' === $attr['saleDesign'] ) {
									$image_data .= isset( $attr['saleText'] ) ? esc_html( $attr['saleText'] ) : esc_html__( 'Sale!', 'product-blocks' ); }
								if ( 'textDigit' === $attr['saleDesign'] ) {
									$image_data .= '-' . esc_html( $_discount ) . esc_html__( ' Off', 'product-blocks' ); }
															$image_data .= '</span>';
							}
							if ( $attr['showHot'] && $product->is_featured() ) {
														$image_data     .= '<span class="wopb-hot">';
															$image_data .= isset( $attr['hotText'] ) ? esc_html( $attr['hotText'] ) : esc_html__( 'Hot', 'product-blocks' );
														$image_data     .= '</span>';
							}
													$image_data .= '</div>';
						}
						if ( ! empty( $parsedOverlayMetaList ) ) {
							$meta_class  = $attr['ovrMetaInline'] ? ' wopb_f_inline' : '';
							$meta_class .= ! $attr['hoverMeta'] ? ' wopb-is-visible' : '';
							$image_data .= '<div class="wopb-product-new-meta' . $meta_class . '">';
							foreach ( $parsedOverlayMetaList as $meta_val ) {
								if ( '_wishlist' === $meta_val ) {
									$image_data .= wp_kses( (string) apply_filters( 'wopb_grid_wishlist', '', $post_id, $attr['tooltipPosition'] ), $allowed_html );
								}
								if ( '_qview' === $meta_val ) {
									$image_data .= wp_kses( (string) apply_filters( 'wopb_grid_quickview', '', $recent_posts, $post_id, $attr['tooltipPosition'] ), $allowed_html );
								}
								if ( '_compare' === $meta_val ) {
									$image_data .= wp_kses( (string) apply_filters( 'wopb_grid_compare', '', $post_id, $attr['tooltipPosition'] ), $allowed_html );
								}
								if ( '_cart' === $meta_val ) {
									$cart_params['isIcon'] = true;
									$image_data           .= wp_kses( wopb_function()->get_add_to_cart( $product, $cart_params ), $allowed_html );
								}
							}
							$image_data .= '</div>';
						}

						if ( $attr['showDeal'] ) {
							$image_data .= wp_kses( wopb_function()->get_deals( $product, $attr['dealText'] ), $allowed_html );
						}

						if ( $attr['catShow'] && 'none' !== $attr['catPosition'] && 'beforeTitle' !== $attr['catPosition'] ) {
							$image_data .= wp_kses_post( $category );
						}

						if ( $attr['brandShow'] && 'none' !== $attr['brandPosition'] && 'beforeTitle' !== $attr['brandPosition'] ) {
							$image_data .= wp_kses_post( $brand );
						}

						if ( $product->get_stock_status() === 'outofstock' && $attr['showOutStock'] ) {
							$image_data     .= '<div class="wopb-product-outofstock">';
								$image_data .= '<span>' . esc_html__( 'Out of stock', 'product-blocks' ) . '</span>';
							$image_data     .= '</div>';
						} elseif ( $product->get_stock_status() === 'instock' && $attr['showInStock'] ) {
							$image_data     .= '<div class="wopb-product-instock">';
								$image_data .= '<span>' . esc_html__( 'In Stock', 'product-blocks' ) . '</span>';
							$image_data     .= '</div>';
						}

											// Image
											$empty_image = false;
						if ( has_post_thumbnail() ) {
							$image_url = wp_get_attachment_image_url( $post_thumb_id, ( $attr['imgCrop'] ? $attr['imgCrop'] : 'full' ) );
						} else {
							$empty_image = true;
							$image_url   = wc_placeholder_img_src( ( $attr['imgCrop'] ? $attr['imgCrop'] : 'full' ) );
						}
											$image_data             .= $empty_image ? '<div class="empty-image">' : '';
												$image_data         .= '<a href="' . esc_url( $titlelink ) . '">';
													$image_data     .= '<img ';
														$image_data .= 'src="' . esc_url( $image_url ) . '" ';
														$image_data .= 'alt="' . esc_attr( $title ) . '" ';
						if ( ! empty( $item_calss['image'] ) ) {
							$image_data .= 'class=" ' . esc_attr( $item_calss['image'] ) . '" ';
							$image_data .= 'srcset="' . esc_attr( wp_get_attachment_image_srcset( $post_thumb_id ) ) . '"';
						}
													$image_data .= '/>';
													$image_data .= wp_kses( (string) apply_filters( 'wopb_after_loop_image', '', $product, $post_thumb_id ), $allowed_html );
						if ( $attr['showVideo'] && ! $empty_image ) {
							$image_data .= wp_kses( (string) apply_filters( 'wopb_product_video', '', $product, $post_thumb_id ), $allowed_html );
						}
						if ( ! $attr['disableFlip'] && ! $empty_image ) {
							$image_data .= wp_kses( (string) apply_filters( 'wopb_flip_image', '', $product, $attr['imgCrop'] ), $allowed_html );
						}
												$image_data .= '</a>';
											$image_data     .= $empty_image ? '</div>' : '';

										$image_data .= '</div>';
					}

													$content_data .= '<div class="wopb-product-grid3-content">';

													// Category
					if ( $attr['catShow'] && 'beforeTitle' === $attr['catPosition'] ) {
						$content_data .= wp_kses_post( $category );
					}
													// Brand
					if ( $attr['brandShow'] && 'beforeTitle' === $attr['brandPosition'] ) {
						$content_data .= wp_kses_post( $brand );
					}
													// Title
					if ( $attr['titleShow'] ) {
						if ( 'before_title' === $attr['variationSwitchPosition'] ) {
							$content_data .= $variationSwitcher_data;
						}

						include WOPB_PATH . 'blocks/template/title.php';
						$content_data .= $title_data;

						if ( 'after_title' === $attr['variationSwitchPosition'] ) {
							$content_data .= $variationSwitcher_data;
						}
					}
													// Category
					if ( $attr['catShow'] && 'none' === $attr['catPosition'] ) {
						$content_data .= wp_kses_post( $category );
					}
													// Brand
					if ( $attr['brandShow'] && 'none' === $attr['brandPosition'] ) {
						$content_data .= wp_kses_post( $brand );
					}
													// Price
					if ( $attr['showPrice'] ) {
						if ( 'before_price' === $attr['variationSwitchPosition'] ) {
							$content_data .= $variationSwitcher_data;
						}

						$content_data .= '<div class="wopb-product-price">' . wp_kses( $product->get_price_html(), wopb_function()->allowed_html_tags() ) . '</div>';

						if ( 'after_price' === $attr['variationSwitchPosition'] ) {
							$content_data .= $variationSwitcher_data;
						}
					}
													$content_data .= '<div class="wopb-fade-in-block">';
													// Review
					if ( $attr['showReview'] ) {
						include WOPB_PATH . 'blocks/template/review.php';
						$content_data .= $review_data;
					}
					if ( $attr['showShortDesc'] ) {
						$content_data .= '<div class="wopb-short-description">' . wp_kses( wopb_function()->excerpt( $post_id, $attr['shortDescLimit'] ), wopb_function()->allowed_html_tags() ) . '</div>';
					}

					if ( 'before_cart' === $attr['variationSwitchPosition'] ) {
						$content_data .= $variationSwitcher_data;
					}

													$content_data .= '<div class="wopb-quick-cart wopb-grid3-quick-cart">';
										// Add to Cart URL
					if ( $attr['showCart'] ) {
										$cart_params['isIcon'] = false;
										$content_data         .= wp_kses( wopb_function()->get_add_to_cart( $product, $cart_params ), $allowed_html );
					}
																	$content_data .= '</div>';
					if ( 'after_cart' === $attr['variationSwitchPosition'] ) {
						$content_data .= $variationSwitcher_data;
					}
					if ( $after_loop = apply_filters( 'wopb_after_loop_item', $content = '' ) ) {
						$content_data .= '<div class="wopb-after-loop-item">' . wp_kses( $after_loop, wopb_function()->allowed_html_tags() ) . '</div>';
					}
																	$content_data .= '</div>';
																	$content_data .= '</div>';

																	$post_loop .= $image_data . $content_data;

																	$post_loop .= '</div>';
																	$post_loop .= '</div>';
				}
				++$idx;
							endwhile;

							$wrapper_main_content .= $post_loop;

			if ( $attr['paginationShow'] && 'grid' === $attr['productView'] && 'loadMore' === $attr['paginationType'] ) {
				$wrapper_main_content .= '<span class="wopb-loadmore-insert-before"></span>';
			}
						$wrapper_main_content .= '</div>';// wopb-block-items-wrap

						// Load More
			if ( $attr['paginationShow'] && 'grid' === $attr['productView'] && 'loadMore' === $attr['paginationType'] ) {
				include WOPB_PATH . 'blocks/template/loadmore.php';
			}

						// Pagination
			if ( $attr['paginationShow'] && 'grid' === $attr['productView'] && 'pagination' === $attr['paginationType'] ) {
				include WOPB_PATH . 'blocks/template/pagination.php';
			}

			if ( 'slide' === $attr['productView'] && $attr['showArrows'] ) {
				include WOPB_PATH . 'blocks/template/arrow.php';
			}
					$wraper_after .= '</div>';// wopb-wrapper-main-content
				$wraper_after     .= '</div>';
			$wraper_after         .= '</div>';

			wp_reset_postdata();
		}

		if ( ( $noAjax && isset( $attr['ajax_source'] ) && 'filter' === $attr['ajax_source'] ) || ! ( $recent_posts->have_posts() ) ) {
			if ( '' === $post_loop ) {
				// note: dont remove without proper check. - Shihab.
				// these before after are necessary for jquery use in filter.js .
				$wraper_before .= '<div '
									. ( isset( $attr['advanceId'] ) ? 'id="' . esc_attr( sanitize_html_class( $attr['advanceId'] ?? '' ) ) . '" ' : '' )
									. ' class="wp-block-product-blocks-' . esc_attr( $block_name ?? '' )
									. ' wopb-block-' . esc_attr( sanitize_html_class( $attr['blockId'] ?? '' ) )
									. ' ' . esc_attr( $attr['className'] ?? '' )
									. ' ' . esc_attr( $attr['align'] ?? '' )
									. '">';
				$wraper_before .= '<div class="wopb-block-wrapper'
									. ( isset( $attr['layout'] ) ? ' wopb-has-gridlay' : '' )
									. '">';
				$wraper_before .= '<div class="wopb-wrapper-main-content">';

				$wrapper_main_content .= '<span class="wopb-no-product-found">' . esc_html__( 'No products were found of your matching selection', 'product-blocks' ) . '</span>';

				$wraper_after .= '</div>'; // wopb-wrapper-main-content.
				$wraper_after .= '</div>'; // wopb-block-wrapper.
				$wraper_after .= '</div>'; // wopb-block.

				if ( ! $noAjax ) {
					return $wraper_before . $wrapper_main_content . $wraper_after;
				}
			}
			// before after needed when no products found when it is rendering without ajax/api.
			return $wrapper_main_content;
		}

		return $noAjax ? $post_loop : $wraper_before . $wrapper_main_content . $wraper_after;
	}

	/**
	 * Allowed HTML for the markup that add-ons and WooCommerce hook into the product loop.
	 *
	 * @return array
	 */
	private function allowed_loop_html() {
		return wopb_function()->allowed_form_html(
			array(
				'svg'     => array(
					'id'        => true,
					'data-name' => true,
				),
				'g'       => array(
					'transform' => true,
					'data-name' => true,
				),
				'path'    => array(
					'id'        => true,
					'class'     => true,
					'data-name' => true,
					'fill-rule' => true,
					'transform' => true,
				),
				'rect'    => array(
					'x'         => true,
					'y'         => true,
					'transform' => true,
				),
				'circle'  => array(
					'stroke'       => true,
					'stroke-width' => true,
				),
				'polygon' => array(
					'points' => true,
					'fill'   => true,
				),
			)
		);
	}
}
