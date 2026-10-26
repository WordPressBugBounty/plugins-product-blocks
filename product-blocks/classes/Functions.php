<?php
/**
 * Common Functions.
 *
 * @package WOPB\Functions
 * @since v.1.0.0
 */

namespace WOPB;

use WOPB\Includes\Xpo;

defined( 'ABSPATH' ) || exit;

/**
 * Functions class.
 */
class Functions {
	private $PLUGIN_NAME     = 'WowStore';
	private $PLUGIN_SLUG     = 'product-blocks';
	private $PLUGIN_VERSION  = WOPB_VER;
	private static $instance = null;
	/**
	 * Setup class.
	 *
	 * @since v.1.0.0
	 */
	public function __construct() {
		if ( ! isset( $GLOBALS['wopb_settings'] ) || ! is_array( $GLOBALS['wopb_settings'] ) ) { // Deprecated: Automatic conversion of false to array is deprecated.
			$GLOBALS['wopb_settings'] = array();
		}
		$GLOBALS['wopb_settings']['is_wc_ready'] = $this->is_wc_ready();
	}

	/**
	 * Gets the Functions class instance
	 *
	 * @since v.4.0.0
	 * @returns Functions
	 */
	public static function get_instance() {
		if ( ! isset( $GLOBALS['wopb_settings'] ) ) {
			$GLOBALS['wopb_settings'] = get_option( 'wopb_options' );
		}

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function get_screen( $type = 'page' ) {
        return isset( $_GET[$type] ) ? sanitize_text_field( wp_unslash( $_GET[ $type ] ) ) : ''; // phpcs:ignore
	}


	public function builder_metabox_position() {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$user_meta = get_user_meta( $user_id, 'meta-box-order_product', true );
			if ( ! empty( $user_meta ) ) {
				$meta_list = explode( ',', $user_meta['side'] );
				$meta_list = array_diff( $meta_list, array( 'wopb-single-product-meta-box' ) );
				array_splice( $meta_list, 1, 0, 'wopb-single-product-meta-box' );
				$user_meta['side'] = implode( ',', $meta_list );
				update_user_meta(
					$user_id,
					'meta-box-order_product',
					$user_meta
				);
			}
		}
	}

	/**
	 * Deal HTML of the Single Products
	 *
	 * @since v.1.1.0
	 * @return STRING | Deal HTML
	 */
	public function get_deals( $product, $dealText = '' ) {
		$html    = '';
		$arr     = explode( '|', $dealText );
		$time    = current_time( 'timestamp' );
		$sales   = $product->get_sale_price() ? $product->get_sale_price() : ( $product->get_regular_price() ? $product->get_regular_price() : '' );
		$time_to = $product->get_date_on_sale_to() ? strtotime( $product->get_date_on_sale_to() ) : '';

		if ( $sales && $time_to > $time ) {
			$html         .= '<div class="wopb-product-deals" data-date="' . esc_attr( gmdate( 'Y/m/d', $time_to ) ) . '">';
				$html     .= '<div class="wopb-deals-date">';
					$html .= '<strong class="wopb-deals-counter-days">00</strong>';
					$html .= '<span class="wopb-deals-periods">' . ( isset( $arr[0] ) ? esc_html( $arr[0] ) : esc_html__( 'Days', 'product-blocks' ) ) . '</span>';
				$html     .= '</div>';
				$html     .= '<div class="wopb-deals-hour">';
					$html .= '<strong class="wopb-deals-counter-hours">00</strong>';
					$html .= '<span class="wopb-deals-periods">' . ( isset( $arr[1] ) ? esc_html( $arr[1] ) : esc_html__( 'Hours', 'product-blocks' ) ) . '</span>';
				$html     .= '</div>';
				$html     .= '<div class="wopb-deals-minute">';
					$html .= '<strong class="wopb-deals-counter-minutes">00</strong>';
					$html .= '<span class="wopb-deals-periods">' . ( isset( $arr[2] ) ? esc_html( $arr[2] ) : esc_html__( 'Minutes', 'product-blocks' ) ) . '</span>';
				$html     .= '</div>';
				$html     .= '<div class="wopb-deals-seconds">';
					$html .= '<strong class="wopb-deals-counter-seconds">00</strong>';
					$html .= '<span class="wopb-deals-periods">' . ( isset( $arr[3] ) ? esc_html( $arr[3] ) : esc_html__( 'Seconds', 'product-blocks' ) ) . '</span>';
				$html     .= '</div>';
			$html         .= '</div>';
		}

		return $html;
	}


	/**
	 * Pro Link with Parameters
	 *
	 * @since v.1.1.0
	 * @return STRING | Premium Link With Parameters
	 */
	public function get_premium_link( $url = '', $tag = 'go_premium' ) {
		return Xpo::generate_utm_link(
			array(
				'utmKey' => $tag,
				'url'    => $url,
			)
		);
	}

	/**
	 * Whether to show Pro upsells (notices, badges, "Upgrade to Pro" links).
	 *
	 * Pro returns false while its license is valid. Free doesn't read the license itself.
	 *
	 * @since v.4.6.0
	 * @return bool
	 */
	public function show_upsell() {
		return (bool) apply_filters( 'wopb_show_upsell', true );
	}

	/**
	 * Whether the template library lets the site insert the designs the catalog marks as Pro.
	 *
	 * Defaults to the upsell flag, which is what older Pro versions drive. The Pro plugin answers the
	 * filter itself, from its license, so the library doesn't depend on a display flag.
	 *
	 * @since v.4.6.0
	 * @return bool
	 */
	public function templates_unlocked() {
		/**
		 * Filters whether Pro-marked designs in the template library can be inserted.
		 *
		 * @since 4.6.0
		 * @param bool $unlocked Whether they can; true when upsells are hidden.
		 */
		return (bool) apply_filters( 'wopb_template_library_unlocked', ! $this->show_upsell() );
	}

	/**
	 * Get ID from Post Objects
	 *
	 * @since v.1.1.0
	 * @return ARRAY
	 */
	public function get_ids( $obj ) {
		$id = array();
		foreach ( $obj->posts as $val ) {
			$id[] = $val->ID;
		}
		return $id;
	}

	/**
	 * Get Option Settings
	 *
	 * @since v.1.0.0
	 * @param string $key Key of the option.
	 * @return mixed Option value if $key is provided, otherwise all settings.
	 */
	public function get_setting( $key = '' ) {
		$data = $GLOBALS['wopb_settings'];
		if ( '' != $key ) {
			if ( isset( $data[ $key ] ) ) {
				return $data[ $key ];
			} else {
				return '';
			}
		} else {
			return $data;
		}
	}

	/**
	 * Resolve a thank-you order only after WooCommerce's key and shopper checks.
	 *
	 * @return \WC_Order|false Authorized order, or false without private details.
	 */
	public function get_thankyou_order() {
		$order_id = get_query_var( 'order-received' );
		if ( ! is_scalar( $order_id ) || ! ctype_digit( (string) $order_id ) ) {
			return false;
		}
		$order_id = apply_filters( 'woocommerce_thankyou_order_id', absint( $order_id ) );
		// Order keys authorize emailed links; a WordPress nonce cannot replace them.
		$order_key = isset( $_GET['key'] ) && is_string( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_key = apply_filters( 'woocommerce_thankyou_order_key', $order_key );
		$order     = $order_id > 0 ? wc_get_order( $order_id ) : false;
		if ( ! $order instanceof \WC_Order || ! is_string( $order_key ) || '' === $order_key || ! hash_equals( $order->get_order_key(), $order_key ) ) {
			return false;
		}

		$customer_id = (int) $order->get_customer_id();
		if ( $customer_id && apply_filters( 'woocommerce_order_received_verify_known_shoppers', true ) && get_current_user_id() !== $customer_id ) {
			return false;
		}

		$supplied_email = null;
		if ( isset( $_POST['check_submission'], $_POST['email'] ) && is_string( $_POST['check_submission'] ) && is_string( $_POST['email'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['check_submission'] ) ), 'wc_verify_email' ) ) {
			$supplied_email = sanitize_email( wp_unslash( $_POST['email'] ) );
		}
		if ( is_callable( array( '\Automattic\WooCommerce\Internal\Utilities\Users', 'should_user_verify_order_email' ) ) ) {
			return \Automattic\WooCommerce\Internal\Utilities\Users::should_user_verify_order_email( $order->get_id(), $supplied_email, 'order-received' ) ? false : $order;
		}

		// Preserve WooCommerce's policy where email verification is still private to its shortcode.
		$billing_email = $order->get_billing_email();
		if ( '' === $billing_email || ( $customer_id && get_current_user_id() === $customer_id ) ) {
			return $order;
		}
		$grace_period = (int) apply_filters( 'woocommerce_order_email_verification_grace_period', 10 * MINUTE_IN_SECONDS, $order, 'order-received' );
		$date_created = $order->get_date_created();
		if ( $date_created instanceof \WC_DateTime && time() - $date_created->getTimestamp() <= $grace_period ) {
			return $order;
		}
		$session       = WC()->session;
		$customer      = $session instanceof \WC_Session ? $session->get( 'customer' ) : array();
		$session_email = is_array( $customer ) && isset( $customer['email'] ) && is_string( $customer['email'] ) ? $customer['email'] : '';
		$session_match = '' !== $session_email && 0 === strcasecmp( $session_email, $billing_email );
		$email_match   = is_string( $supplied_email ) && '' !== $supplied_email && 0 === strcasecmp( $supplied_email, $billing_email );
		$verify_email  = ! $session_match && ! $email_match && ! current_user_can( 'read_private_shop_orders' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce registers this shop-order capability.
		return apply_filters( 'woocommerce_order_email_verification_required', $verify_email, $order, 'order-received' ) ? false : $order;
	}

	/**
	 * Validate the bounded, scalar-leaf arrays accepted by plugin settings.
	 *
	 * @param mixed $settings Settings input.
	 * @param int   $depth    Current nesting depth.
	 * @return bool
	 */
	public function is_valid_settings_input( $settings, $depth = 0 ) {
		if ( ! is_array( $settings ) || $depth > 10 || count( $settings ) > 1000 ) {
			return false;
		}
		foreach ( $settings as $key => $value ) {
			if ( is_string( $key ) && ( '' === $key || strlen( $key ) > 100 || ! preg_match( '/^[a-zA-Z0-9_-]+$/D', $key ) ) ) {
				return false;
			}
			if ( is_array( $value ) ) {
				if ( ! $this->is_valid_settings_input( $value, $depth + 1 ) ) {
					return false;
				}
			} elseif ( ! is_scalar( $value ) || ( is_string( $value ) && strlen( $value ) > 10000 ) || ( is_float( $value ) && ! is_finite( $value ) ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Set Option Settings
	 *
	 * @since v.1.0.0
	 * @param STRING | Key of the Option (STRING), Value (STRING)
	 * @return NULL
	 */
	public function set_setting( $key = '', $val = '' ) {
		if ( '' != $key ) {
			$data         = ! $GLOBALS['wopb_settings'] ? array() : $GLOBALS['wopb_settings'];
			$data[ $key ] = $val;
			update_option( 'wopb_options', $data );
			$GLOBALS['wopb_settings'] = $data;
		}
	}


	/**
	 * WooCommerce Activaton Check.
	 *
	 * @since v.1.0.0
	 * @param INTEGER | Product ID (INTEGER), Word Limit (INTEGER)
	 * @return BOOLEAN | Excerpt with Limit
	 */
	public function is_wc_ready() {
		// WooCommerce loads after this plugin, so it can't be told apart before `plugins_loaded`.
		return class_exists( 'WooCommerce' ) || ! did_action( 'plugins_loaded' );
	}


	/**
	 * Add to Cart HTML
	 *
	 * @since v.1.0.0
	 * @param INTEGER | Product ID (INTEGER), Word Limit (INTEGER)
	 * @return STRING | Excerpt with Limit
	 */
	public function excerpt( $post_id, $limit = 55 ) {
		global $product;

		$desc = $product->get_short_description();

		$desc = str_replace( '&nbsp;', ' ', $desc );

		$allowed_tags = array(
			'ul'     => array(),
			'ol'     => array(),
			'li'     => array(),
			'p'      => array(),
			'br'     => array(),
			'strong' => array(),
			'b'      => array(),
			'em'     => array(),
			'i'      => array(),
			// 'a'      => array(
			// 'href'   => true,
			// 'title'  => true,
			// 'target' => true,
			// 'rel'    => true,
			// ),
		);

		$desc = wp_kses( $desc, $allowed_tags );

		// 3. Split into words while keeping HTML tags
		preg_match_all( '/<[^>]+>|[^\s<]+/', $desc, $matches );
		$pieces = $matches[0];

		$words = array();
		$count = 0;

		foreach ( $pieces as $piece ) {
			if ( strpos( $piece, '<' ) === 0 ) {
				$words[] = $piece;
			} else {
				$sub_words = preg_split( '/\s+/', $piece );
				foreach ( $sub_words as $w ) {
					if ( $count < $limit ) {
						$words[] = $w;
						++$count;
					}
				}
			}
			if ( $count >= $limit ) {
				break;
			}
		}

		$excerpt = implode( ' ', $words ) . '...';

		return force_balance_tags( $excerpt );
	}

	/**
	 * Slider Responsive Split.
	 *
	 * @since v.1.0.0
	 * @param MIXED | Category Slug (STRING), Number (INTGER), Type (STRING)
	 * @return STRING | String of the responsive
	 */
	public function slider_responsive_split( $data ) {
		if ( is_string( $data ) ) {
			return $data . '-' . $data . '-2-1';
		} else {
			$data = (array) $data;
			return $data['lg'] . '-' . $data['sm'] . '-' . $data['xs'];
		}
	}


	/**
	 * Category Data of the Product.
	 *
	 * @since v.1.0.0
	 * @param MIXED | Category Slug (STRING), Number (INTGER), Type (STRING)
	 * @return ARRAY | Category Data as Array
	 */
	public function get_category_data( $catSlug, $number = 40, $type = '' ) {
		$data = array();

		if ( 'child' === $type ) {
			$image = '';
			if ( ! empty( $catSlug ) ) {
				foreach ( $catSlug as $cat ) {
					$category_slug = isset( $cat->value ) ? $cat->value : $cat;
					$parent_term   = get_term_by( 'slug', $category_slug, 'product_cat' );
					if ( ! $parent_term ) {
						continue;
					}
					$term_data = get_terms(
						array(
							'taxonomy'   => 'product_cat',
							'hide_empty' => true,
							'parent'     => $parent_term->term_id,
						)
					);
					if ( ! empty( $term_data ) ) {
						foreach ( $term_data as $terms ) {
							$temp            = array();
							$temp['url']     = get_term_link( $terms );
							$temp['term_id'] = $terms->term_id;
							$temp['name']    = $terms->name;
							$temp['slug']    = $terms->slug;
							$temp['desc']    = $terms->description;
							$temp['count']   = $terms->count;
							$temp['image']   = $this->get_cat_image( $terms );
							$temp['image2']  = $number;
							$data[]          = $temp;
						}
					}
				}
			}
			return $data;
		}

		if ( ! empty( $catSlug ) ) {
			foreach ( $catSlug as $cat ) {
					$category_slug = isset( $cat->value ) ? $cat->value : $cat;
				$terms             = get_term_by( 'slug', $category_slug, 'product_cat' );
				if ( ! empty( $terms ) ) {
					$image           = $this->get_cat_image( $terms );
					$temp['url']     = get_term_link( $terms );
					$temp['term_id'] = $terms->term_id;
					$temp['name']    = $terms->name;
					$temp['slug']    = $terms->slug;
					$temp['desc']    = $terms->description;
					$temp['count']   = $terms->count;
					$temp['image']   = $image;
					$temp['image1']  = $image;
					$data[]          = $temp;
				}
			}
		} else {
			$query = array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'number'     => $number,
			);
			if ( 'parent' === $type ) {
				$query['parent'] = 0;
			}
			$term_data = get_terms( $query );
			if ( ! empty( $term_data ) ) {
				foreach ( $term_data as $terms ) {
					$temp                   = array();
					$child_query            = array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => true,
						'parent'     => $terms->term_id,
					);
					$sub_categories         = get_terms( $child_query );
					$temp['url']            = get_term_link( $terms );
					$temp['term_id']        = $terms->term_id;
					$temp['name']           = $terms->name;
					$temp['slug']           = $terms->slug;
					$temp['desc']           = $terms->description;
					$temp['count']          = $terms->count;
					$temp['image']          = $this->get_cat_image( $terms );
					$temp['image2']         = $number;
					$temp['sub_categories'] = $sub_categories;
					$data[]                 = $temp;
				}
			}
		}
		return $data;
	}

	/**
	 * Get Category Image
	 *
	 * @param $terms
	 * @return array
	 * @since v.4.1.6
	 */
	public function get_cat_image( $terms ) {
		$thumbnail_id = get_term_meta( $terms->term_id, 'thumbnail_id', true );
		$image_src    = array();
		if ( $thumbnail_id ) {
			$image_sizes = $this->get_image_size();
			foreach ( $image_sizes as $key => $value ) {
				$data = wp_get_attachment_image_src( $thumbnail_id, $key, false );
				if ( ! empty( $data[0] ) ) {
					$image_src[ $key ] = $data[0];
				}
			}
		}
		return $image_src;
	}


	/**
	 * Quick Query Builder
	 *
	 * @since v.1.0.0
	 * @param ARRAY | Query Parameters
	 * @return ARRAY | Query
	 */
	public function get_query( $attr ) {
		global $wp_query;
		$query_vars = $wp_query->query_vars;
		$builder    = isset( $attr['builder'] ) ? $attr['builder'] : '';

		$query_args = array(
			'posts_per_page' => isset( $attr['queryNumber'] ) ? $attr['queryNumber'] : 3,
			'post_type'      => isset( $attr['queryType'] ) ? $attr['queryType'] : 'product',
			'orderby'        => isset( $attr['queryOrderBy'] ) ? $attr['queryOrderBy'] : 'date',
			'order'          => isset( $attr['queryOrder'] ) ? $attr['queryOrder'] : 'DESC',
			'post_status'    => 'publish',
			'paged'          => isset( $attr['paged'] ) ? $attr['paged'] : 1,
		);

        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		$query_args['tax_query'] = array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'slug',
				'terms'    => 'exclude-from-catalog',
				'operator' => 'NOT IN',
			),
		);

		$wopb_search_builder = ( isset( $attr['is_search'] ) && $attr['is_search'] );
		$archive             = isset( $attr['is_archive'] ) ? $attr['is_archive'] : ''; // double check because built in function not working after click on pagination

		if (
			! $this->is_specific_archive_product( $attr ) &&
			$this->is_builder( $builder ) &&
			( is_archive() || is_search() || is_product_taxonomy() || is_product_tag() || $wopb_search_builder ) && ! is_shop()
		) {
			if ( $builder ) {
				$str = explode( '###', $builder );
				if ( isset( $str[0] ) ) {
					if ( 'taxonomy' === $str[0] ) {
						if ( isset( $str[1] ) && isset( $str[2] ) ) {
                            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
							$query_args['tax_query'] = array(
								array(
									'taxonomy' => $str[1],
									'field'    => 'slug',
									'terms'    => $str[2],
								),
							);
						}
					} elseif ( 'search' === $str[0] ) {
						if ( isset( $str[1] ) ) {
							$query_args['s'] = $str[1];
						}
					}
				}
			} else {
				$query_args += $wp_query->query_vars;
			}
		}

		if ( isset( $attr['queryOrderBy'] ) ) {
			switch ( $attr['queryOrderBy'] ) {
				case 'new_old':
					unset( $query_args['orderby'] );
					break;

				case 'old_new':
					unset( $query_args['orderby'] );
					break;

				case 'title':
					$query_args['orderby'] = 'title';
					break;

				case 'title_reversed':
					$query_args['orderby'] = 'title';
					break;

				case 'price_low':
				case 'price_high':
					$query_args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					$query_args['orderby']  = 'meta_value_num';
					break;

				case 'date':
					unset( $query_args['orderby'] );
					break;

				case 'price':
					$query_args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					$query_args['orderby']  = 'meta_value_num';
					break;

				case 'sku':
					$query_args['meta_key'] = '_sku'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					break;

				case 'stock_status':
					$query_args['meta_key'] = '_stock_status'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					$query_args['orderby']  = 'meta_value';
					break;

				default:
					break;
			}
		}

		if ( isset( $attr['queryOffset'] ) && $attr['queryOffset'] && ! ( $query_args['paged'] > 1 ) ) {
			$query_args['offset'] = isset( $attr['queryOffset'] ) ? $attr['queryOffset'] : 0;
		}

		if ( isset( $attr['queryInclude'] ) && $attr['queryInclude'] ) {
			$query_include = ( substr( $attr['queryInclude'], 0, 1 ) === '[' ) ? $this->get_value( json_decode( $attr['queryInclude'] ) ) : explode( ',', $attr['queryInclude'] );
			if ( is_array( $query_include ) && count( $query_include ) ) {
				$query_args['post__in'] = $query_include;
				$query_args['orderby']  = 'post__in';
			}
		}

		if ( ! empty( $attr['queryExclude'] ) ) {
            // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			$query_exclude = ( substr( $attr['queryExclude'], 0, 1 ) === '[' ) ? $this->get_value( json_decode( $attr['queryExclude'] ) ) : explode( ',', $attr['queryExclude'] );
			if ( is_array( $query_exclude ) && count( $query_exclude ) ) {
				$query_args['post__not_in'] = $query_exclude; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			}
		}
		if ( isset( $query_vars['post__not_in'] ) && ! empty( $query_vars['post__not_in'] ) ) {
            // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			$query_args['post__not_in'] = isset( $query_args['post__not_in'] ) ? array_merge( $query_vars['post__not_in'], $query_args['post__not_in'] ) : $query_vars['post__not_in'];
		}

		if ( isset( $attr['queryStatus'] ) ) {
			switch ( $attr['queryStatus'] ) {
				case 'featured':
					$query_args['post__in'] = wc_get_featured_product_ids();
					break;

				case 'onsale':
					unset( $query_args['meta_key'] );
					$query_args['post__in'] = apply_filters( 'wopb_on_sale_ids', wc_get_product_ids_on_sale() );
					break;

				default:
					break;
			}
		}

		if ( isset( $attr['queryProductSort'] ) && 'null' !== $attr['queryProductSort'] ) {
			$query_args = $this->get_quick_query( $attr, $query_args, $attr['queryProductSort'] );
		} elseif ( isset( $attr['queryAdvanceProductSort'] ) && 'null' !== $attr['queryAdvanceProductSort'] ) {
			$query_args = $this->get_quick_query( $attr, $query_args, $attr['queryAdvanceProductSort'] );
		} elseif ( isset( $attr['queryQuick'] ) && '' != $attr['queryQuick'] ) {
			$query_args = $this->get_quick_query( $attr, $query_args );
		}
		if ( isset( $attr['productTaxonomy'] ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => $attr['productTaxonomy']['taxonomy'],
				'field'    => 'id',
				'terms'    => $attr['productTaxonomy']['term_ids'],
				'operator' => 'IN',
			);
		}

		if ( isset( $attr['queryTax'] ) ) {
			if ( isset( $attr['queryTaxValue'] ) ) {
				$tax_value = ( strlen( $attr['queryTaxValue'] ) > 2 ) ? $attr['queryTaxValue'] : array();
				$tax_value = is_array( $tax_value ) ? $tax_value : json_decode( $tax_value );

				$tax_value = ( isset( $tax_value[0] ) && is_object( $tax_value[0] ) ) ? $this->get_value( $tax_value ) : $tax_value;

				if ( is_array( $tax_value ) && count( $tax_value ) > 0 ) {
					$relation = isset( $attr['queryRelation'] ) ? $attr['queryRelation'] : 'OR';
					$var      = array( 'relation' => $relation );
					foreach ( $tax_value as $val ) {
						$var[] = array(
							'taxonomy' => $attr['queryTax'],
							'field'    => 'slug',
							'terms'    => $val,
						);
					}
					if ( count( $var ) > 1 ) {
						$query_args['tax_query'][] = $var;
					}
				} else {
					$queryCats = ! empty( $attr['queryCat'] ) ? json_decode( $attr['queryCat'] ) : '';
					$queryCats = isset( $queryCats[0] ) && is_object( $queryCats[0] ) ? $this->get_value( $queryCats ) : $queryCats;
					if ( ! empty( $queryCats ) ) {
						if ( ! isset( $query_args['tax_query']['relation'] ) ) {
							$query_args['tax_query']['relation'] = 'OR';
						}

						$query_args['tax_query'][] = array(
							'taxonomy' => 'product_cat',
							'field'    => 'slug',
							'terms'    => $queryCats,
							'operator' => 'IN',
						);
					}
				}
			}
		}

		// Filter Action Taxonomy
		if ( isset( $attr['custom_action'] ) ) {
			$query_args = $this->get_filter_query( $attr, $query_args );
		} elseif ( isset( $attr['filterShow'] ) && $attr['filterShow'] && 'grid' === $attr['productView'] ) {
				$showCat = json_decode( $attr['filterCat'] );
				$showTag = json_decode( $attr['filterTag'] );

				$flag = 'product_cat' === $attr['filterType'] ? ( empty( $showCat ) ? false : true ) : ( empty( $showTag ) ? false : true );

			$arr = json_decode( $attr['filterAction'] );
			if ( is_array( $arr ) && ! empty( $arr ) && false == $flag ) {
				$attr['custom_action'] = 'custom_action#' . $arr[0];
				$query_args            = $this->get_filter_query( $attr, $query_args );
			} elseif ( 'product_cat' === $attr['filterType'] ) {
					$var     = array( 'relation' => 'OR' );
					$showCat = isset( $attr['queryCatAction'] ) ? $attr['queryCatAction'] : $showCat;
				if ( count( $showCat ) ) {
					foreach ( $showCat as $cat ) {
						$var[] = array(
							'taxonomy' => 'product_cat',
							'field'    => 'slug',
							'terms'    => isset( $cat->value ) ? $cat->value : $cat,
							'operator' => 'IN',
						);
					}
				}
					$query_args['tax_query'] = $var; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			} elseif ( 'product_tag' === $attr['filterType'] ) {
					$var     = array( 'relation' => 'OR' );
					$showTag = isset( $attr['queryTagAction'] ) ? $attr['queryTagAction'] : $showTag;
				if ( count( $showTag ) ) {
					foreach ( $showTag as $tag ) {
						$var[] = array(
							'taxonomy' => 'product_tag',
							'field'    => 'slug',
							'terms'    => isset( $tag->value ) ? $tag->value : $tag,
							'operator' => 'IN',
						);
					}
				}
					$query_args['tax_query'] = $var; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

			}
		}

		if ( isset( $query_args['post__in'] ) && isset( $query_args['post__not_in'] ) ) {
			$had_ids                = ! empty( $query_args['post__in'] );
			$query_args['post__in'] = array_values( array_diff( $query_args['post__in'], $query_args['post__not_in'] ) ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			if ( $had_ids && empty( $query_args['post__in'] ) ) {
				// Every listed product is excluded; an empty post__in would list them all instead.
				$query_args['post__in'] = array( 0 );
			}
		}
		if ( isset( $attr['post__not_in'] ) && $attr['post__not_in'] ) {
			// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			$query_args['post__not_in'] = isset( $query_args['post__not_in'] ) ? array_merge( $attr['post__not_in'], $query_args['post__not_in'] ) : $attr['post__not_in'];
		}

		// Search Page Product Grid, List Result issue
		$search_query_val = get_search_query();
		if ( ( is_search() || $wopb_search_builder ) && isset( $attr['queryProductSort'] ) && 'product_search' === $attr['queryProductSort'] && $search_query_val ) {
			$query_args['filter_search_key'] = $search_query_val;

			add_filter( 'posts_join', array( wopb_function(), 'custom_post_join' ), 100, 2 );
			add_filter( 'posts_where', array( wopb_function(), 'custom_post_query' ), 1000, 2 );
			add_filter(
				'posts_distinct',
				array( $this, 'distinct_product_posts' )
			);
		}

		if ( ( is_search() || $wopb_search_builder ) && isset( $attr['queryProductSort'] ) && 'product_search' === $attr['queryProductSort'] ) {
			$query_args['search_columns'] = array( 'post_title' );
		}

		$query_args['wpnonce'] = wp_create_nonce( 'wopb-nonce' );

		return apply_filters( 'wopb_query_args', $query_args, $attr );
	}

	public function is_specific_archive_product( $attr ) {
		if (
			isset( $attr['queryProductSort'] ) &&
			'choose_specific' === $attr['queryProductSort'] &&
			isset( $attr['querySpecificProduct'] ) &&
			$attr['querySpecificProduct'] &&
			( is_archive() || ( ! is_archive() && ( is_product_taxonomy() || is_product_tag() ) ) )
		) {
			return true;
		}
	}

	/**
	 * Filter Query Builder
	 *
	 * @since v.1.1.0
	 * @return ARRAY | Return part of the filter query
	 */
	public function get_filter_query( $prams, $args ) {

		list($key, $value) = explode( '#', $prams['custom_action'] );

		unset( $args['tax_query'] );
		unset( $args['post__not_in'] );
		unset( $args['post__in'] );

		switch ( $value ) {
			case 'on_sale':
				unset( $args['meta_key'] );
				$args['orderby']  = 'date';
				$args['order']    = 'DESC';
				$args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
				break;
			case 'most_rated':
				$args['meta_key'] = '_wc_review_count'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['orderby']  = 'meta_value meta_value_num';
				$args['order']    = 'DESC';
				break;
			case 'featured':
				$args['post__in'] = wc_get_featured_product_ids();
				break;
			case 'arrival':
				$args['order'] = 'DESC';
				break;
			default:
				// code...
				break;
		}

		return $args;
	}


	/**
	 * Quick Query Builder Attribute Builder
	 *
	 * @param $prams
	 * @param $args
	 * @param null $sort_query
	 * @return ARRAY | Return part of the filter query
	 * @since v.1.1.0
	 */
	public function get_quick_query( $prams, $args, $sort_query = null ) {
		switch ( $sort_query ) {
			case 'sales_day_1':
				$args = $this->best_selling_query_args( $args, '-1 days' );
				break;
			case 'sales_day_7':
				$args = $this->best_selling_query_args( $args, '-7 days' );
				break;
			case 'sales_day_30':
				$args = $this->best_selling_query_args( $args, '-30 days' );
				break;
			case 'sales_day_90':
				$args = $this->best_selling_query_args( $args, '-90 days' );
				break;
			case 'sales_day_all':
				$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['orderby']  = 'meta_value_num';
				break;
			case 'view_day_all':
				$args['meta_key'] = '__product_views_count'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['orderby']  = 'meta_value meta_value_num';
				break;
			case 'most_rated':
				$args['meta_key'] = '_wc_review_count'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['orderby']  = 'meta_value meta_value_num';
				break;
			case 'top_rated':
				$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['orderby']  = 'meta_value meta_value_num';
				break;
			case 'random_post':
				$args['orderby'] = 'rand';
				break;
			case 'random_post_7_days':
				$args['orderby']    = 'rand';
				$args['order']      = 'ASC';
				$args['date_query'] = array( array( 'after' => '1 week ago' ) );
				break;
			case 'random_post_30_days':
				$args['orderby']    = 'rand';
				$args['order']      = 'ASC';
				$args['date_query'] = array( array( 'after' => '1 month ago' ) );
				break;
			case 'related_tag':
				global $post;
				if ( isset( $post->ID ) ) {
                    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					$args['tax_query']    = array(
						array(
							'taxonomy' => 'product_tag',
							'terms'    => $this->get_terms_id( $post->ID, 'product_tag' ),
							'field'    => 'term_id',
						),
					);
					$args['post__not_in'] = array( $post->ID ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
				}
				break;
			case 'related_category':
				global $post;
				if ( isset( $post->ID ) ) {
                    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					$args['tax_query']    = array(
						array(
							'taxonomy' => 'product_cat',
							'terms'    => $this->get_terms_id( $post->ID, 'product_cat' ),
							'field'    => 'term_id',
						),
					);
					$args['post__not_in'] = array( $post->ID ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
				}
				break;
			case 'related_cat_tag':
				global $post;
				if ( isset( $post->ID ) ) {
                    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					$args['tax_query']    = array(
						array(
							'taxonomy' => 'product_tag',
							'terms'    => $this->get_terms_id( $post->ID, 'product_tag' ),
							'field'    => 'term_id',
						),
						array(
							'taxonomy' => 'product_cat',
							'terms'    => $this->get_terms_id( $post->ID, 'product_cat' ),
							'field'    => 'term_id',
						),
					);
					$args['post__not_in'] = array( $post->ID ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
				}
				break;
			case 'upsells':
				global $post;
				global $product;
				$backend = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( 'edit' !== $backend && isset( $post->ID ) ) {
					if ( ! $product ) {
						$product = wc_get_product( $post->ID ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WooCommerce's own $product global, not plugin-defined.
					}
					if ( $product ) {
						$upsells                     = $product->get_upsell_ids();
						$args['ignore_sticky_posts'] = 1;
						if ( $upsells ) {
							$args['post__in']     = $upsells;
							$args['post__not_in'] = array( $post->ID ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
						} else {
							$args['post__in'] = array( 999999 );
						}
					}
				}
				break;
			case 'crosssell':
				global $post;
				global $product;
				$backend = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( 'edit' !== $backend && isset( $post->ID ) ) {
					if ( ! $product ) {
						$product = wc_get_product( $post->ID ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WooCommerce's own $product global, not plugin-defined.
					}

					if ( $this->is_builder() && is_cart() ) {
						if ( WC()->cart->get_cross_sells() ) {
							$args['post__in'] = WC()->cart->get_cross_sells();
						} else {
							$args['post_type'] = 'cart_cross_sell_empty';
						}
					} elseif ( $product ) {
						$crosssell                   = $product->get_cross_sell_ids();
						$args['ignore_sticky_posts'] = 1;
						if ( $crosssell ) {
							$args['post__in']     = $crosssell;
							$args['post__not_in'] = array( $post->ID ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
						} else {
							$args['post__in'] = array( 999999 );
						}
					}
				}
				break;
			case 'recent_view':
				global $post;
				$viewed_products             = ! empty( $_COOKIE['__wopb_recently_viewed'] ) ? (array) explode( '|', sanitize_text_field( wp_unslash( $_COOKIE['__wopb_recently_viewed'] ) ) ) : array();
				$args['ignore_sticky_posts'] = 1;
				if ( ! empty( $viewed_products ) ) {
					$args['post__in']     = $viewed_products;
					$args['post__not_in'] = array( $post->ID ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
				} else {
					$args['post__in'] = array( 999999 );
				}
				break;
		}
		return $args;
	}

	public function get_terms_id( $id, $type ) {
		$data = array();
		$arr  = get_the_terms( $id, $type );
		if ( is_array( $arr ) ) {
			foreach ( $arr as $key => $val ) {
				$data[] = $val->term_id;
			}
		}
		return $data;
	}


	/**
	 * Limit a product query to the best sellers of a period, in sales order.
	 *
	 * @since v.4.6.0
	 * @param array  $args   WP_Query arguments.
	 * @param string $period strtotime() offset, e.g. '-7 days'.
	 * @return array
	 */
	public function best_selling_query_args( $args, $period ) {
		$ids = $this->get_best_selling_products( gmdate( 'Y-m-d H:i:s', strtotime( $period ) ) );

		// [0] matches nothing: with no sales in the period the block shows no products, not the whole catalog.
		$args['post__in'] = $ids ? array_map( 'absint', $ids ) : array( 0 );
		$args['orderby']  = 'post__in';
		return $args;
	}

	/**
	 * IDs of the products sold since a date, most units sold first.
	 *
	 * @since v.1.1.0
	 * @param string $date Inclusive cutoff in the site's local time.
	 * @return ARRAY | Return Best Selling Products
	 */
	public function get_best_selling_products( $date ) {
		global $wpdb;

		$order_table         = $wpdb->posts;
		$order_id_column     = 'ID';
		$order_status_column = 'post_status';
		$order_date_column   = 'post_date';
		$order_type_column   = 'post_type';

		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$order_table         = $wpdb->prefix . 'wc_orders';
			$order_id_column     = 'id';
			$order_status_column = 'status';
			$order_date_column   = 'date_created_gmt';
			$order_type_column   = 'type';
			// Preserve the legacy local-time cutoff when reading HPOS UTC dates.
			$date = get_gmt_from_date( $date );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = (array) $wpdb->get_results(
			$wpdb->prepare(
				"
            SELECT post.ID as id, SUM(order_itemmeta2.meta_value + 0) as count
            FROM {$wpdb->posts} post
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta
                ON post.ID = order_itemmeta.meta_value
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta order_itemmeta2
                ON order_itemmeta.order_item_id = order_itemmeta2.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_items order_items
                ON order_itemmeta.order_item_id = order_items.order_item_id
            INNER JOIN %i as order_post
                ON order_post.%i = order_items.order_id
            WHERE post.post_type = 'product'
            AND post.post_status = 'publish'
            AND order_post.%i = 'shop_order'
            AND order_post.%i IN ('wc-processing','wc-completed')
            AND order_post.%i >= %s
            AND order_itemmeta.meta_key = '_product_id'
            AND order_itemmeta2.meta_key = '_qty'
            GROUP BY post.ID
            ORDER BY count DESC, post.ID DESC
        ",
				$order_table,
				$order_id_column,
				$order_type_column,
				$order_status_column,
				$order_date_column,
				$date
			)
		);
		return wp_list_pluck( $result, 'id' );
	}


	/**
	 * Get Number of the Page
	 *
	 * @since v.1.0.0
	 * @param MIXED | NUMBER of QUERY (ARRAY), NUMBER OF POST (INT)
	 * @return INTEGER | Number of Page
	 */
	public function get_page_number( $attr, $post_number ) {
		if ( $post_number > 0 ) {
			$post_per_page = isset( $attr['queryNumber'] ) ? $attr['queryNumber'] : 3;
			$pages         = ceil( $post_number / $post_per_page );
			return $pages ? $pages : 1;
		} else {
			return 1;
		}
	}


	/**
	 * List of Image Size
	 *
	 * @since v.1.0.0
	 * @return ARRAY | Image Size Name and Slug
	 */
	public function get_image_size() {
		$sizes  = get_intermediate_image_sizes();
		$filter = array( 'full' => 'Full' );
		foreach ( $sizes as $value ) {
			$filter[ $value ] = ucwords( str_replace( array( '_', '-' ), array( ' ', ' ' ), $value ) );
		}
		return $filter;
	}


	/**
	 * Pagination HTML Generator
	 *
	 * @since v.1.0.0
	 * @param STRING | PAGE, NAV TYPE, Pagination Text
	 * @return STRING | Pagination HTML as String
	 */
	public function pagination( $pages = '', $paginationNav = 'textArrow', $paginationText = '', $attr = array() ) {
		$html      = '';
		$showitems = 3;
		$paged     = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
		$paged     = $paged ? $paged : 1;
		if ( '' == $pages ) {
			global $wp_query;
			$pages = $wp_query->max_num_pages;
			if ( ! $pages ) {
				$pages = 1;
			}
		}

		$paginationText = explode( '|', $paginationText );
		$next_text      = isset( $paginationText[1] ) ? $paginationText[1] : esc_html__( 'Next', 'product-blocks' );
		$prev_text      = isset( $paginationText[0] ) ? $paginationText[0] : esc_html__( 'Previous', 'product-blocks' );

		$is_ajax = ! empty( $attr['paginationAjax'] );
		$data    = ( $paged >= 3 ) ? array( ( $paged - 1 ), $paged, $paged + 1 ) : array( 1, 2, 3 );

		if ( $pages > 1 ) {
			$html        .= '<ul class="wopb-pagination ' . ( $is_ajax ? 'wopb-pagination-ajax' : '' ) . '">';
			$display_none = 'style="display:none"';
			// Previous Button
			if ( $pages > 2 ) {
				$html .= '<li class="wopb-prev-page-numbers" ' . ( 1 == $paged ? $display_none : '' ) . '>
                            <a href="' . esc_url( $this->get_pagenum_link( $paged - 1, $attr ) ) . '">' .
							$this->svg_icon( 'leftAngle2' ) .
							( 'textArrow' === $paginationNav ? esc_html( $prev_text ) : '' ) .
							'</a>
                        </li>';
			}
			// First Page & Dots
			if ( $paged > 2 || ( $is_ajax && $pages > 3 ) ) {
				$html .= '<li class="wopb-first-pages" ' . ( $paged < 2 ? $display_none : '' ) . ' data-current="1">
                            <a href="' . esc_url( $this->get_pagenum_link( 1, $attr ) ) . '">1</a>
                        </li>';
			}

			if ( $paged > 3 || ( $is_ajax && $pages > 4 ) ) {
				$html .= '<li class="wopb-first-dot" ' . ( 1 == $paged ? $display_none : '' ) . '><a href="#">...</a></li>';
			}

			// Page Numbers
			foreach ( $data as $i ) {
				if ( $pages >= $i && ! ( $pages > 3 && $pages == $i ) ) {
					$active_class = ( $paged == $i ) ? ' pagination-active' : '';
					$html        .= '<li class="wopb-center-item' . $active_class . '" data-current="' . esc_attr( $i ) . '">
                                <a href="' . esc_url( $this->get_pagenum_link( $i, $attr ) ) . '">' . esc_html( $i ) . '</a>
                            </li>';
				}
			}

			// Last Dots & Last Page
			if ( $pages > 4 && $pages != ( $paged + 2 ) ) {
				$html .= '<li class="wopb-last-dot" ' . ( $pages <= $paged + 1 ? $display_none : '' ) . '><a href="#">...</a></li>';
			}

			if ( $pages > 3 ) {
				$last_class = ( $paged == $pages ) ? ' pagination-active' : '';
				$html      .= '<li class="wopb-last-pages' . $last_class . '" data-current="' . esc_attr( $pages ) . '">
                            <a href="' . esc_url( $this->get_pagenum_link( $pages, $attr ) ) . '">' . esc_html( $pages ) . '</a>
                        </li>';
			}

			// Next Button
			if ( $paged != $pages ) {
				$html .= '<li class="wopb-next-page-numbers">
                            <a href="' . esc_url( $this->get_pagenum_link( $paged + 1, $attr ) ) . '">
                                ' . ( $paginationNav === 'textArrow' ? esc_html( $next_text ) : '' ) . $this->svg_icon( 'rightAngle2' ) . '
                            </a>
                        </li>';
			}

			$html .= '</ul>';
		}
		return $html;
	}

	/**
	 * Get Page Post ID
	 *
	 * @param $page_post_id
	 * @param $blockId
	 * @return int
	 * @since v.4.0.4
	 */
	public function get_page_post_id( $page_post_id, $blockId ) {
		global $wpdb;
		$post_meta = $wpdb->get_row( $wpdb->prepare( 'SELECT post_id FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key=%s AND meta_value LIKE %s', '_wopb_css', '%.wopb-block-' . $blockId . '%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		// For FSE theme
		if ( ! $post_meta ) {
			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				$template = $wpdb->get_row( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->prefix . 'posts WHERE post_content LIKE %s', '%"blockId":"' . $blockId . '"%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				if ( isset( $template->ID ) ) {
					return $template->ID;
				}
			}
		}
		if ( $post_meta && isset( $post_meta->post_id ) && $post_meta->post_id != $page_post_id ) {
			return $post_meta->post_id;
		}
		return $page_post_id;
	}


	/**
	 * Taxonomoy Data List.
	 *
	 * @since v.1.0.0
	 * @param STRING | Taxonomy Name
	 * @return ARRAY | Taxonomy Slug and Name as a ARRAY
	 */
	public function taxonomy( $prams = 'product_cat' ) {
		$data  = array();
		$terms = get_terms(
			array(
				'taxonomy'   => $prams,
				'hide_empty' => true,
			)
		);
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $val ) {
				$data[ urldecode_deep( $val->slug ) ] = $val->name;

				// adding the undecoded slug as well,
				// because in case of when slug is encoded,
				// it prints out the encoded slug instead of name.
				// but it should print the name. - shihab
				$data[ $val->slug ] = $val->name;
			}
		}
		return $data;
	}

	// phpcs:disable

	/**
	 * Filter HTML Generator
	 *
	 * @since v.1.0.0
	 * @param STRING | TEXT, TYPE, CATEGORY, TAG
	 * @return STRING | Filter HTML as String
	 */
	public function filter(
		$filterText = '',
		$filterType = '',
		$filterCat = '[]',
		$filterTag = '[]',
		$action = '[]',
		$actionText = '',
		$noAjax = false,
		$filterMobileText = '...',
		$filterMobile = true
	) {
		$html = '';

		$filterData = array(
			'top_sale'   => 'Top Sale',
			'popular'    => 'Popular',
			'on_sale'    => 'On Sale',
			'most_rated' => 'Most Rated',
			'top_rated'  => 'Top Rated',
			'featured'   => 'Featured',
			'arrival'    => 'New Arrival',
		);

		$arr = explode( '|', is_string( $actionText ) ? $actionText : '' );
		if ( count( $arr ) == 7 ) {
			foreach ( array_keys( $filterData ) as $k => $v ) {
				$filterData[ $v ] = $arr[ $k ];
			}
		}
		$count = $noAjax ? 1 : 0;

		$actions = $this->filter_item_values( $action );

		$html .= '<ul ';
		$html .= $filterMobile ? 'class="wopb-flex-menu"' : '';
		$html .= ' data-name="' . ( $filterMobileText ? esc_attr( $filterMobileText ) : '&nbsp;' ) . '">';
		if ( $filterText && ! $actions ) {
			$class = '';
			if ( 0 == $count ) {
				$count = 1;
				$class = 'class="filter-active"';
			}
			$html .= '<li class="filter-item">';
			$html .= '<a ' . $class . ' data-taxonomy="" href="#">';
			$html .= esc_html( $filterText );
			$html .= '</a></li>';
		}
		if ( 'product_cat' === $filterType ) {
			$cat = $this->taxonomy( 'product_cat' );
			foreach ( $this->filter_item_values( $filterCat ) as $val ) {
				$class = '';
				if ( 0 == $count ) {
					$count = 1;
					$class = 'class="filter-active"';
				}
				$html .= '<li class="filter-item">';
				$html .= '<a ' . $class . ' data-taxonomy="' . esc_attr( $val === 'all' ? '' : $val ) . '" href="#">';
				$html .= esc_html( isset( $cat[ $val ] ) ? $cat[ $val ] : $val );
				$html .= '</a></li>';
			}
		} else {
			$tag = $this->taxonomy( 'product_tag' );
			foreach ( $this->filter_item_values( $filterTag ) as $val ) {
				$class = '';
				if ( 0 == $count ) {
					$count = 1;
					$class = 'class="filter-active"';
				}
				$html .= '<li class="filter-item">';
				$html .= '<a ' . $class . ' data-taxonomy="' . esc_attr( $val === 'all' ? '' : $val ) . '" href="#">';
				$html .= esc_html( isset( $tag[ $val ] ) ? $tag[ $val ] : $val );
				$html .= '</a></li>';
			}
		}

		foreach ( $actions as $val ) {
			$class = '';
			if ( 0 == $count ) {
				$count = 1;
				$class = 'class="filter-active"';
			}
			$html .= '<li class="filter-item">';
			$html .= '<a ' . $class . ' data-taxonomy="custom_action#' . esc_attr( $val ) . '" href="#">';
			$html .= esc_html( isset( $filterData[ $val ] ) ? $filterData[ $val ] : $val );
			$html .= '</a></li>';
		}

		$html .= '</ul>';
		return $html;
	}

	/**
	 * Normalize a filter attribute (JSON string, array or object) to a flat
	 * list of scalar values, so that malformed or unexpected input can never
	 * reach the output buffer as an object/array.
	 *
	 * @since v.4.5.0
	 * @param mixed $data Raw attribute value.
	 * @return array List of string values.
	 */
	private function filter_item_values( $data ) {
		if ( is_string( $data ) ) {
			$data = json_decode( $data );
		}
		if ( is_object( $data ) ) {
			$data = (array) $data;
		}
		if ( ! is_array( $data ) ) {
			return array();
		}

		$values = array();
		foreach ( $data as $val ) {
			if ( is_object( $val ) ) {
				$val = isset( $val->value ) ? $val->value : null;
			} elseif ( is_array( $val ) ) {
				$val = isset( $val['value'] ) ? $val['value'] : null;
			}
			if ( is_scalar( $val ) ) {
				$values[] = (string) $val;
			}
		}

		return $values;
	}


	/**
	 * Plugins Pro Version is Activated or not.
	 *
	 * @since v.1.0.0
	 * @param string  $icon_name Name of the icon
	 * @param boolean $is_full_path svg icon full path
	 * @return string SVG icon content
	 */
	public function svg_icon( $icon_name = '', $is_full_path = false ) {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			$init_filesystem = WP_Filesystem();
			if ( ! $init_filesystem ) {
				return '';
			}
		}

		$icon_path = $is_full_path ? WOPB_PATH . $icon_name : WOPB_PATH . 'assets/img/svg/' . $icon_name . '.svg';

		if ( ! $icon_name || ! $wp_filesystem->exists( $icon_path ) ) {
			return '';
		}

		$icon = $wp_filesystem->get_contents( $icon_path );
		return false !== $icon ? $icon : '';
	}

	/**
	 * All Pages as Array.
	 *
	 * @since v.1.1.0
	 * @param BOOLEAN | With empty return
	 * @return ARRAY | With Page Name and ID
	 */
	public function all_pages( $empty = false ) {
		$arr   = $empty ? array( '' => __( 'Select Page', 'product-blocks' ) ) : array();
		$pages = get_pages();
		foreach ( $pages as $page ) {
			$arr[ $page->ID ] = $page->post_title;
		}
		return $arr;
	}


	/**
	 * Get All Menu Laction
	 *
	 * @since v.3.1.1
	 * @return ARRAY
	 */
	public function wopb_nav_menu_location() {
		$array          = array( '' => __( 'Select Location', 'product-blocks' ) );
		$menu_locations = get_nav_menu_locations();
		foreach ( $menu_locations as $location => $ky ) {
			$array[ $location ] = ucfirst( str_replace( '_', ' ', $location ) );
		}
		return $array;
	}


	public function get_taxonomy_list( $default = false ) {
		$default_remove = $default ? array( 'product_cat', 'product_tag' ) : array( 'product_type', 'pa_color', 'pa_size' );
		$taxonomy       = get_taxonomies( array( 'object_type' => array( 'product' ) ) );
		foreach ( $taxonomy as $key => $val ) {
			if ( in_array( $key, $default_remove ) ) {
				unset( $taxonomy[ $key ] );
			}
		}
		return array_keys( $taxonomy );
	}

	public function in_string_part( $part, $data, $isValue = false ) {
		$return = false;
		foreach ( $data as $val ) {
			if ( strpos( $val, $part ) !== false ) {
				$return = $isValue ? $val : true;
				break;
			}
		}
		return $return;
	}

	// Template Conditions
	public function conditions( $type = 'return', $condition = '' ) {
		$page_id           = '';
		$conditions        = $condition ? $condition : get_option( 'wopb_builder_conditions', array() );
		$post_type         = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$not_header_footer = 'header' !== $type && 'footer' !== $type;
		$is_shop           = is_shop();

		/*
		 * Archive Builder
		 */
		if ( isset( $conditions['archive'] ) && $not_header_footer ) {
			if ( ! empty( $conditions['archive'] ) ) {
				foreach ( $conditions['archive'] as $key => $val ) {
					if (
						! $is_shop && ! is_category() && get_post_type( $key ) === 'wopb_builder' &&
						( is_archive() || ( ! is_archive() && ( is_product_taxonomy() || is_product_tag() ) ) )
					) {
						if ( in_array( 'include/archive', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
						if ( in_array( 'exclude/archive', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = '';
							}
						}
						if ( is_product_category() ) {
							$taxonomy = get_queried_object();
							if ( in_array( 'include/archive/product_cat', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = $key;
								}
							}
							if ( in_array( 'exclude/archive/product_cat', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = '';
								}
							}
							if ( isset( $taxonomy->term_id ) && $this->in_string_part( 'include/archive/product_cat/' . $taxonomy->term_id, $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = $key;
								}
							}
							if ( isset( $taxonomy->term_id ) && in_array( 'include/archive/product_cat/' . $taxonomy->term_id, $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = $key;
								}
							}
							if ( isset( $taxonomy->term_id ) && in_array( 'exclude/archive/product_cat/' . $taxonomy->term_id, $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = '';
								}
							}
							if ( $this->in_string_part( 'any_child_of', $val ) ) {
								if ( in_array( 'include/archive/any_child_of_product_cat', $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = $key;
									}
								}
								if ( in_array( 'exclude/archive/any_child_of_product_cat', $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = '';
									}
								}
								$data = $this->in_string_part( '/archive/any_child_of_product_cat/', $val, true );
								if ( $data ) {
									$data = explode( '/', $data );
									if ( isset( $data[3] ) && $data[3] ) {
										if ( isset( $taxonomy->term_id ) && term_is_ancestor_of( $data[3], $taxonomy->term_id, 'product_cat' ) ) {
											if ( 'publish' === get_post_status( $key ) ) {
												$page_id = 'exclude' === $data[0] ? '' : $key;
											}
										}
									}
								}
							} elseif ( $this->in_string_part( 'child_of', $val ) ) {
								if ( in_array( 'include/archive/child_of_product_cat', $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = $key;
									}
								}
								if ( in_array( 'exclude/archive/child_of_product_cat', $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = '';
									}
								}
								if ( in_array( 'include/archive/child_of_product_cat/' . $taxonomy->parent, $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = $key;
									}
								}
								if ( in_array( 'exclude/archive/child_of_product_cat/' . $taxonomy->parent, $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = '';
									}
								}
							}
						} elseif ( is_product_tag() ) {
							if ( in_array( 'include/archive/product_tag', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = $key;
								}
							}
							if ( in_array( 'exclude/archive/product_tag', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = '';
								}
							}
							$taxonomy = get_queried_object();
							if ( isset( $taxonomy->term_id ) && $this->in_string_part( 'include/archive/product_tag/' . $taxonomy->term_id, $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = $key;
								}
							}
							if ( isset( $taxonomy->term_id ) && in_array( 'exclude/archive/product_tag/' . $taxonomy->term_id, $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = '';
								}
							}
						} elseif ( $this->in_string_part( 'include/archive', $val ) ) {
							$taxonomy_list = $this->get_taxonomy_list( true );
							foreach ( $taxonomy_list as $value ) {
								if ( in_array( 'include/archive/' . $value, $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = $key;
									}
								}
								$taxonomy = get_queried_object();
								if ( isset( $taxonomy->term_id ) && $this->in_string_part( 'include/archive/' . $value . '/' . $taxonomy->term_id, $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = $key;
									}
								}
							}
						}
					} elseif ( is_cart() ) {
						if ( in_array( 'filter/cart', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
					} elseif ( is_checkout() && is_wc_endpoint_url( 'order-received' ) ) {
						if ( in_array( 'filter/thankyou', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
					} elseif ( $is_shop && ! is_search() ) {
						if ( in_array( 'filter/shop', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
					} elseif ( 'product' === $post_type && is_search() ) {
						if ( in_array( 'include/archive/search', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
					} elseif ( is_product() ) {
						if ( in_array( 'include/allsingle', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						} else {
							foreach ( $val as $value ) {
								$list = explode( '/', $value );
								if ( isset( $list[1] ) && 'single' === $list[1] ) {
									if ( isset( $list[3] ) ) {
										if ( 'product_cat' === $list[2] ) {
											if ( has_term( $list[3], 'product_cat' ) ) {
												if ( 'publish' === get_post_status( $key ) ) {
													$page_id = $key;
												}
											}
										} elseif ( 'product_tag' === $list[2] ) {
											if ( has_term( $list[3], 'product_tag' ) ) {
												if ( 'publish' === get_post_status( $key ) ) {
													$page_id = $key;
												}
											}
										} elseif ( 'single' === $list[1] && 'product' === $list[2] ) {
											if ( get_the_ID() == $list[3] ) {
												if ( 'publish' === get_post_status( $key ) ) {
													$page_id = $key;
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		/*
		 * Home Page Builder
		 */
		if ( isset( $conditions['home_page'] ) && $not_header_footer ) {
			if ( ! empty( $conditions['home_page'] ) ) {
				foreach ( $conditions['home_page'] as $key => $val ) {
					if ( ( is_front_page() || is_home() ) && ! is_search() ) {
						if ( is_array( $val ) && in_array( 'filter/home_page', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
					}
				}
			}
		}

		/*
		 * Singular Builder
		 */
		if ( isset( $conditions['single_product'] ) && is_product() && $not_header_footer ) {
			if ( ! empty( $conditions['single_product'] ) ) {
				$obj      = get_queried_object();
				$tax_list = $this->get_taxonomy_list();
				foreach ( $conditions['single_product'] as $key => $val ) {
					// All Taxonomy
					if ( $this->in_string_part( '/single_product/in_', $val ) ) {
						foreach ( $tax_list as $tax ) {
							if ( $this->in_string_part( 'single_product/in_' . $tax . '_children', $val ) ) {
								// In Taxonomy Children
								if ( in_array( 'include/single_product/in_' . $tax . '_children', $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = $key;
									}
								}
								if ( in_array( 'exclude/single_product/in_' . $tax . '_children', $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = '';
									}
								}
								$data = $this->in_string_part( '/single_product/in_' . $tax . '_children/', $val, true );
								if ( $data ) {
									$data = explode( '/', $data );
									if ( isset( $data[3] ) && $data[3] ) {
										$term_children  = get_term_children( $data[3], $tax );
										$terms_to_check = array_merge( $term_children, array( $data[3] ) );
										if ( is_object_in_term( $obj->ID, $tax, $terms_to_check ) ) {
											if ( 'publish' === get_post_status( $key ) ) {
												$page_id = 'exclude' === $data[0] ? '' : $key;
											}
										}
									}
								}
							} else {
								// IN Taxonomy
								if ( in_array( 'include/single_product/in_' . $tax, $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = $key;
									}
								}
								if ( in_array( 'exclude/single_product/in_' . $tax, $val ) ) {
									if ( 'publish' === get_post_status( $key ) ) {
										$page_id = '';
									}
								}
								foreach ( $val as $v ) {
									if ( strpos( $v, '/single_product/in_' . $tax . '/' ) !== false ) {
										if ( $v ) {
											$data = explode( '/', $v );
											if ( isset( $data[3] ) && $data[3] ) {
												if ( is_object_in_term( $obj->ID, $tax, $data[3] ) ) {
													if ( 'publish' === get_post_status( $key ) ) {
														$page_id = 'exclude' === $data[0] ? '' : $key;
													}
												}
											}
										}
									}
								}
							}
						}
					}

					// All Post Type
					if ( in_array( 'include/single_product/' . $obj->post_type, $val ) ) {
						if ( 'publish' === get_post_status( $key ) ) {
							$page_id = $key;
						}
					}
					if ( in_array( 'exclude/single_product/' . $obj->post_type, $val ) ) {
						if ( 'publish' === get_post_status( $key ) ) {
							$page_id = '';
						}
					}
					if ( $this->in_string_part( 'include/single_product/' . $obj->post_type . '/' . $obj->ID, $val ) ) {
						if ( 'publish' === get_post_status( $key ) ) {
							$page_id = $key;
						}
					}
					if ( $this->in_string_part( 'exclude/single_product/' . $obj->post_type . '/' . $obj->ID, $val ) ) {
						if ( 'publish' === get_post_status( $key ) ) {
							$page_id = '';
						}
					}
				}

				$builder_type = get_post_meta( $page_id, '_wopb_builder_type', true );
				if ( $builder_type && 'single_product' === $builder_type ) {
					add_theme_support( 'wc-product-gallery-zoom' );
					add_theme_support( 'wc-product-gallery-lightbox' );
				}
			}
		}

		/*
		 * Shop Builder
		 */
		if ( isset( $conditions['shop'] ) && $not_header_footer ) {
			if ( ! empty( $conditions['shop'] ) ) {
				foreach ( $conditions['shop'] as $key => $val ) {
					if ( $is_shop && ! is_search() ) {
						if ( is_array( $val ) && in_array( 'filter/shop', $val ) ) {
							if ( 'publish' === get_post_status( $key ) && get_post_type( $key ) === 'wopb_builder' ) {
								$page_id = $key;
							}
						}
					}
				}
			}
		}

		/*
		 * Cart Builder
		 */
		if ( isset( $conditions['cart'] ) && $not_header_footer ) {
			if ( ! empty( $conditions['cart'] ) ) {
				foreach ( $conditions['cart'] as $key => $val ) {
					if ( is_cart() && ! is_search() ) {
						if ( is_array( $val ) && in_array( 'filter/cart', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
					}
				}
			}
		}

		/*
		 * Checkout Builder
		 */
		if ( isset( $conditions['checkout'] ) && $not_header_footer ) {
			if ( ! empty( $conditions['checkout'] ) ) {
				foreach ( $conditions['checkout'] as $key => $val ) {
					if ( is_checkout() && ! ( is_wc_endpoint_url() || is_wc_endpoint_url( 'order-pay' ) || is_wc_endpoint_url( 'order-received' ) ) ) {
						if ( is_array( $val ) && in_array( 'filter/checkout', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
					}
				}
			}
		}

		/*
		 * My Account Builder
		 */
		if ( isset( $conditions['my_account'] ) && $not_header_footer ) {
			if ( ! empty( $conditions['my_account'] ) ) {
				foreach ( $conditions['my_account'] as $key => $val ) {
					if ( is_account_page() && is_array( $val ) && in_array( 'filter/my_account', $val ) ) {
						if ( 'publish' === get_post_status( $key ) ) {
							$page_id = $key;
						}
					}
				}
			}
		}

		/*
		 * Thank You Builder
		 */
		if ( isset( $conditions['thank_you'] ) && $not_header_footer ) {
			if ( ! empty( $conditions['thank_you'] ) ) {
				foreach ( $conditions['thank_you'] as $key => $val ) {
					if ( is_checkout() && is_wc_endpoint_url( 'order-received' ) ) {
						if ( is_array( $val ) && in_array( 'filter/thank_you', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
					}
				}
			}
		}

		/*
		 * Product Search Result Builder
		 */
		if ( isset( $conditions['product_search'] ) && $not_header_footer ) {
			if ( ! empty( $conditions['product_search'] ) ) {
				foreach ( $conditions['product_search'] as $key => $val ) {
					if ( is_search() ) {
						if ( is_array( $val ) && in_array( 'filter/product_search', $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
					}
				}
			}
		}

		/*
		 * 404 Page
		 * since v.3.0.0
		 */
		if ( isset( $conditions['404'] ) && $not_header_footer ) {
			if ( ! empty( $conditions['404'] ) && is_404() ) {
				foreach ( $conditions['404'] as $key => $val ) {
					if ( is_array( $val ) && in_array( 'filter/404', $val ) ) {
						if ( 'publish' === get_post_status( $key ) ) {
							$page_id = $key;
						}
					}
				}
			}
		}

		/*
		 * All or Particular Pages
		 * Only for Header/Footer Builder
		 * since v.3.0.0
		 */

		if ( isset( $conditions['singular_page'] ) && $not_header_footer && ( $is_shop || is_singular() ) ) {
			$obj       = get_queried_object();
			$shop_id   = $is_shop ? wc_get_page_id( 'shop' ) : '';
			$post_type = $is_shop ? 'page' : $obj->post_type;
			$post_id   = $is_shop && $shop_id
						? $shop_id
						: ( $is_shop ? get_the_ID() : $obj->ID );
			if ( is_object( $obj ) ) {
				foreach ( $conditions['singular_page'] as $key => $val ) {
					if ( get_post_status( $key ) ) {
						// All Post Type
						if ( in_array( 'include/single_product/' . $post_type, $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
						if ( $this->in_string_part( 'include/single_product/' . $post_type . '/' . $post_id, $val ) ) {
							if ( 'publish' === get_post_status( $key ) ) {
								$page_id = $key;
							}
						}
					}
				}
			}
		}

		/*
		 * Header
		 * since v.3.0.0
		 */
		if ( 'header' === $type ) {
			if ( isset( $conditions['header'] ) ) {
				if ( ! empty( $conditions['header'] ) ) {
					foreach ( $conditions['header'] as $key => $val ) {
						if ( ! empty( $val ) ) {
							if ( in_array( 'include/header/entire_site', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = $key;
								}
							}
							if ( in_array( 'exclude/header/entire_site', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = '';
								}
							}
							if ( $this->in_string_part( 'header/single_product', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									foreach ( $val as $k => $v ) {
										$tempKey = strpos( $v, 'header/single_product/page' ) !== false ? 'singular_page' : 'single_product';
										if ( $key && strpos( $v, 'include/header/single_product' ) !== false ) {
											$temp    = $this->conditions( 'return', array( $tempKey => array( $key => array( str_replace( 'header/', '', $v ) ) ) ) );
											$page_id = $temp ? $temp : $page_id;
										}
										if ( strpos( $v, 'exclude/header/single_product' ) !== false ) {
											$temp    = $this->conditions( 'return', array( $tempKey => array( $key => array( str_replace( 'exclude/header', 'include', $v ) ) ) ) );
											$page_id = $temp ? '' : $page_id;
										}
									}
								}
							}
							if ( $this->in_string_part( 'header/archive', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									foreach ( $val as $k => $v ) {
										if ( $key && strpos( $v, 'include/header/archive' ) !== false ) {
											$temp    = $this->conditions( 'return', array( 'archive' => array( $key => array( str_replace( 'header/', '', $v ) ) ) ) );
											$page_id = $temp ? $temp : $page_id;
										}
										if ( strpos( $v, 'exclude/header/archive' ) !== false ) {
											$temp    = $this->conditions( 'return', array( 'archive' => array( $key => array( str_replace( 'exclude/header', 'include', $v ) ) ) ) );
											$page_id = $temp ? '' : $page_id;
										}
									}
								}
							}
						}
					}
					return $page_id;
				}
			}
		}

		/*
		 * Footer
		 * since v.3.0.0
		 */
		if ( 'footer' === $type ) {
			if ( isset( $conditions['footer'] ) ) {
				if ( ! empty( $conditions['footer'] ) ) {
					foreach ( $conditions['footer'] as $key => $val ) {
						if ( ! empty( $val ) ) {
							if ( in_array( 'include/footer/entire_site', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = $key;
								}
							}
							if ( in_array( 'exclude/footer/entire_site', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									$page_id = '';
								}
							}
							if ( $this->in_string_part( 'footer/single_product', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									foreach ( $val as $k => $v ) {
										$tempKey = strpos( $v, 'footer/single_product/page' ) !== false ? 'singular_page' : 'single_product';
										if ( $key && strpos( $v, 'include/footer/single_product' ) !== false ) {
											$temp    = $this->conditions( 'return', array( $tempKey => array( $key => array( str_replace( 'footer/', '', $v ) ) ) ) );
											$page_id = $temp ? $temp : $page_id;
										}
										if ( strpos( $v, 'exclude/footer/single_product' ) !== false ) {
											$temp    = $this->conditions( 'return', array( $tempKey => array( $key => array( str_replace( 'exclude/footer', 'include', $v ) ) ) ) );
											$page_id = $temp ? '' : $page_id;
										}
									}
								}
							}
							if ( $this->in_string_part( 'footer/archive', $val ) ) {
								if ( 'publish' === get_post_status( $key ) ) {
									foreach ( $val as $k => $v ) {
										if ( $key && strpos( $v, 'include/footer/archive' ) !== false ) {
											$temp    = $this->conditions( 'return', array( 'archive' => array( $key => array( str_replace( 'footer/', '', $v ) ) ) ) );
											$page_id = $temp ? $temp : $page_id;
										}
										if ( strpos( $v, 'exclude/footer/archive' ) !== false ) {
											$temp    = $this->conditions( 'return', array( 'archive' => array( $key => array( str_replace( 'exclude/footer', 'include', $v ) ) ) ) );
											$page_id = $temp ? '' : $page_id;
										}
									}
								}
							}
						}
					}
					return $page_id;
				}
			}
		}

		if ( 'return' === $type ) {
			return $page_id;
		}
		if ( 'includes' === $type ) {
			return $page_id ? WOPB_PATH . 'addons/builder/templates/page.php' : '';
		}
	}

	/**
	 * ID for the Builder Post or Normal Post
	 *
	 * @since v.2.3.1
	 * @return NUMBER | is Builder or not
	 */
	public function get_ID() {
		$id = $this->is_builder();
		return $id ? $id : ( is_shop() ? wc_get_page_id( 'shop' ) : get_the_ID() );
	}

	public function is_builder( $builder = '' ) {
		$id = '';
		if ( $builder ) {
			return true;
		}
		$page_id = $this->conditions( 'return' );
		if ( $page_id && $this->get_setting( 'wopb_builder' ) === 'true' ) {
			$id = $page_id;
		}
		return $id;
	}

	/**
	 * Escaping and Set Inline CSS
	 *
	 * @since v.2.2.7
	 * @param STRING | CSS
	 * @return STRING | CSS with Style
	 */
	public function esc_inline( $css ) {
		return '<style type="text/css">' . wp_strip_all_tags( $css ) . '</style>';
	}

	/**
	 * Allowed HTML Tags
	 *
	 * @since v.4.3.2
	 */
	public function allowed_html_tags( $extras = array() ) {
		$allowed = array(
			'del'      => array(),
			'ins'      => array(),
			'source'   => array(
				'src'  => true,
				'type' => true,
			),
			'select'   => array(
				'multiple' => true,
				'name'     => true,
				'data-*'   => true,
				'class'    => true,
			),
			'option'   => array(
				'value'    => true,
				'selected' => true,
				'data-*'   => true,
			),
			'strong'   => array(),
			'b'        => array(),
			'input'    => array(
				'data-*'       => true,
				'area-*'       => true,
				'type'         => true,
				'value'        => true,
				'placeholder'  => true,
				'name'         => true,
				'id'           => true,
				'min'          => true,
				'max'          => true,
				'format'       => true,
				'class'        => true,
				'step'         => true,
				'disabled'     => true,
				'readonly'     => true,
				'required'     => true,
				'maxlength'    => true,
				'minlength'    => true,
				'pattern'      => true,
				'autocomplete' => true,
				'accept'       => true,
			),
			'textarea' => array(
				'data-*'       => true,
				'type'         => true,
				'value'        => true,
				'placeholder'  => true,
				'name'         => true,
				'id'           => true,
				'min'          => true,
				'max'          => true,
				'rows'         => true,
				'format'       => true,
				'class'        => true,
				'disabled'     => true,
				'readonly'     => true,
				'required'     => true,
				'maxlength'    => true,
				'minlength'    => true,
				'pattern'      => true,
				'autocomplete' => true,
				'accept'       => true,
			),
			'svg'      => array(
				'xmlns'        => true,
				'width'        => true,
				'height'       => true,
				'viewbox'      => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
				'class' => true,
			),
			'g'        => array(
				'fill'            => true,
				'stroke'          => true,
				'opacity'         => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'stroke-width'    => true,
				'clip-path'       => true,
			),
			'path'     => array(
				'd'               => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'stroke-width'    => true,
				'clip-rule'       => true,
			),
			'rect'     => array(
				'rx'           => true,
				'width'        => true,
				'height'       => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'defs'     => array(),
			'clipPath' => array(
				'id' => true,
			),
			'style'    => array(
				'id'     => true,
				'type'   => true,
				'media'  => true,
				'title'  => true,
				'scoped' => true,
				'data-*' => true,
			),
			'form'     => array(
				'id'            => true,
				'class'         => true,
				'action'        => true,
				'method'        => true,
				'enctyoe'       => true,
				'data-*'        => true,
				'current-image' => true,
			),
			'div'      => array(
				'style'  => true,
				'name'   => true,
				'id'     => true,
				'data-*' => true,
			),
			'span'     => array(
				'style'  => true,
				'id'     => true,
				'class'  => true,
				'data-*' => true,
			),
			'iframe'   => array(
				'title'           => true,
				'width'           => true,
				'height'          => true,
				'src'             => true,
				'frameborder'     => true,
				'allow'           => true,
				'referrerpolicy'  => true,
				'allowfullscreen' => true,
			),
			'circle'   => array(
				'cx'    => true,
				'cy'    => true,
				'r'     => true,
				'fill'  => true,
				'class' => true,
			),
			'li'       => array(
				'class'  => true,
				'style'  => true,
				'data-*' => true,
			),
			'img'      => array(
				'src'      => true,
				'alt'      => true,
				'width'    => true,
				'height'   => true,
				'srcset'   => true,
				'sizes'    => true,
				'decoding' => true,
				'loading'  => true,
			),
		);

		$default_allowed = wp_kses_allowed_html( 'post' );
		$allow_merged    = array_replace_recursive(
			$default_allowed,
			$allowed,
			(array) $extras
		);

		return $allow_merged;
	}


	public function get_builder_attr() {
		$builder_data = '';
		if ( is_archive() ) {
			$obj = get_queried_object();
			if ( isset( $obj->taxonomy ) ) {
				$builder_data = 'taxonomy###' . $obj->taxonomy . '###' . $obj->slug;
			}
		} elseif ( is_search() ) {
			$builder_data = 'search###' . get_search_query( true );
		}
		return $builder_data ? 'data-builder="' . esc_attr( $builder_data ) . '"' : '';
	}

	/**
	 * Product Join Query
	 *
	 * @param $join
	 * @param $query
	 * @return STRING
	 * @since v.4.1.6
	 */
	public function custom_post_join( $join, $query ) {
		global $wpdb;
		$join .= " INNER JOIN {$wpdb->postmeta} AS post_meta ON ( {$wpdb->posts}.ID = post_meta.post_id )";
		return $join;
	}

	/**
	 * Filter WHERE clause for product search query
	 *
	 * @param string $where WHERE clause.
	 * @return string Modified WHERE clause
	 * @since v.4.1.6
	 */
	public function custom_post_query( $where, $query ) {
		global $wpdb;
		if ( ! empty( $query->get( 'filter_search_key' ) ) ) {
			$search = $query->get( 'filter_search_key' );
			$like   = '%' . $wpdb->esc_like( $search ) . '%';

			$where .= $wpdb->prepare(
				" AND (
					{$wpdb->posts}.post_title LIKE %s
					OR (post_meta.meta_key = '_sku' AND post_meta.meta_value LIKE %s)
				)",
				$like,
				$like
			);
		}
		return $where;
	}

	public function get_pagenum_link( $page_num, $attr ) {
		global $wp_rewrite;
		if ( isset( $attr['current_url'] ) ) {
			$base = $attr['current_url'];
			if ( $page_num > 1 ) {
				$base = $base . user_trailingslashit( $wp_rewrite->pagination_base . '/' . $page_num, 'paged' );
			}
			return $base;
		} else {
			return get_pagenum_link( $page_num );
		}
	}

	/**
	 * Array Sanitize Function
	 *
	 * @param ARRAY
	 * @return ARRAY | Array of Sanitize
	 */
	public function recursive_sanitize_text_field( $array ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = $this->recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}
		return $array;
	}


	/**
	 * Sanitize params
	 *
	 * @param $params
	 * @return array|bool|mixed|string
	 * @since v.3.1.11
	 */
	public function rest_sanitize_params( $params ) {
		if ( is_array( $params ) ) {
			return array_map( array( $this, 'rest_sanitize_params' ), $params );
		} elseif ( is_bool( $params ) ) {
				return rest_sanitize_boolean( $params );
		} elseif ( is_object( $params ) ) {
			return $params;
		} else {
			return sanitize_text_field( $params );
		}
	}

	/**
	 * A price as the shopper sees it
	 *
	 * @param float  $value Price in the store's currency, or the shopper's price when `$type` is 'default'.
	 * @param string $type  'default' turns a shopper's price back into the store's currency.
	 * @return array `value`, and `rate` when the price was multiplied by one.
	 * @since v.2.4.8
	 */
	public function get_price_data( $value = 0, $type = '' ) {
		/**
		 * Filters a price before the plugin shows or compares it, for add-ons that change the shop's prices.
		 *
		 * @since 4.6.0
		 * @param array  $data  `value` (the price, unchanged by default) and optionally `rate`, the number the price was multiplied by.
		 * @param float  $value The price.
		 * @param string $type  'default' asks for the store's own price of a shopper's price.
		 */
		$data = apply_filters( 'wopb_price_data', array( 'value' => $value ), $value, $type );
		return is_array( $data ) && isset( $data['value'] ) ? $data : array( 'value' => $value );
	}


	/**
	 * Enqueue Common Script for Both Frontend and Backend
	 *
	 * @since v.1.0.0
	 * @return void
	 */
	public function register_scripts_common() {
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'wopb-slick-style', WOPB_URL . 'assets/css/slick.css', array(), WOPB_VER );
		wp_enqueue_style( 'wopb-slick-theme-style', WOPB_URL . 'assets/css/slick-theme.css', array(), WOPB_VER );
		if ( is_rtl() ) {
			wp_enqueue_style( 'wopb-blocks-rtl-css', WOPB_URL . 'assets/css/rtl.css', array(), WOPB_VER );
		}
		$this->register_main_scripts();
	}

	/**
	 * Enqueue Main Script
	 *
	 * @since v.4.0.0
	 * @return void
	 */
	public function register_main_scripts() {
		global $post;
		wp_enqueue_style( 'wopb-style', WOPB_URL . 'assets/css/blocks.style.css', array(), WOPB_VER );
		if ( has_block( 'product-blocks/cart-total', $post ) ) {
			wp_enqueue_script( 'wc-cart' );
		}
		if ( has_block( 'product-blocks/checkout-order-review', $post ) ) {
			wp_enqueue_script( 'wc-checkout' );
		}
		$this->front_common_script();
	}

	/**
	 * Enqueue Common Script for Frontend
	 *
	 * @since v.4.0.0
	 * @return void
	 */
	public function front_common_script() {
		$require_script = array( 'jquery', 'wopb-flexmenu-script', 'wp-api-fetch', 'wopb-slick-script', 'wp-data' );
		if ( wopb_function()->get_setting( 'wopb_variation_swatches' ) === 'true' && ! is_admin() ) {
			$require_script[] = 'wopb-variation-swatches';
		}

		wp_enqueue_style( 'wopb-css', WOPB_URL . 'assets/css/wopb.css', array(), WOPB_VER );
		wp_enqueue_script( 'wopb-slick-script', WOPB_URL . 'assets/js/slick.min.js', array( 'jquery' ), WOPB_VER, true );
		wp_enqueue_script( 'wopb-flexmenu-script', WOPB_URL . 'assets/js/flexmenu.min.js', array( 'jquery' ), WOPB_VER, true );
		wp_enqueue_script( 'wopb-script', WOPB_URL . 'assets/js/wopb.js', $require_script, WOPB_VER, true );
		// filter script moved to different file.
		wp_enqueue_script( 'wopb-filter-script', WOPB_URL . 'assets/js/filter.js', array( 'jquery', 'wopb-script' ), WOPB_VER, true );
		// Header show/hide behaviour on scroll.
		wp_enqueue_script( 'wopb-header-behavior', WOPB_URL . 'assets/js/header-behavior.js', array( 'jquery', 'wopb-script' ), WOPB_VER, true );
		$wopb_core_localize = array(
			'url'               => WOPB_URL,
			'ajax'              => admin_url( 'admin-ajax.php' ),
			'security'          => wp_create_nonce( 'wopb-nonce' ),
			'currency_symbol'   => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '',
			'currency_position' => get_option( 'woocommerce_currency_pos' ),
			'errorElementGroup' => array(
				'errorElement' => '<div class="wopb-error-element"></div>',
			),
			'rest'              => get_rest_url(),
			'page_id'           => $this->get_the_current_page(),
		);
		$wopb_core_localize = array_merge( $wopb_core_localize, $this->get_endpoint_urls() );
		wp_localize_script( 'wopb-script', 'wopb_core', $wopb_core_localize );
	}

	/**
	 * Get All PostType Registered
	 *
	 * @since v.2.5.6
	 * @param | Attribute of the Query(ARRAY) | Post Number(ARRAY)
	 * @return ARRAY
	 */
	public function get_post_type() {
		$filter    = apply_filters( 'wopb_public_post_type', true );
		$post_type = get_post_types( ( $filter ? array( 'public' => true ) : '' ), 'names' );
		return array_diff( $post_type, array( 'attachment' ) );
	}


	/**
	 * Get Raw Value from Objects
	 *
	 * @since v.2.6.0
	 * @param NULL
	 * @return STRING | Device Type
	 */
	public function get_value( $attr ) {
		$data = array();
		if ( is_array( $attr ) ) {
			foreach ( $attr as $val ) {
				$data[] = $val->value;
			}
		}
		return $data;
	}

	/**
	 * Check Specific Plugin Active or Not
	 *
	 * @since v.2.6.5
	 * @param $plugin_name
	 * @return BOOLEAN
	 */
	public function active_plugin( $plugin_name ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_file = $plugin_name . '/' . $plugin_name . '.php';
		$plugins = get_plugins();
		return isset( $plugins[ $plugin_file ] ) && in_array( $plugin_file, get_option( 'active_plugins', array() ), true );
	}

	/**
	 * Get all Post Lists as Array
	 *
	 * @since v.2.7.2
	 * @param $post_type
	 * @param $empty
	 * @return ARRAY
	 */
	public function get_all_lists( $post_type = 'post', $empty = '' ) {
		$args                         = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);
		$loop                         = new \WP_Query( $args );
		$data[ $empty ? $empty : '' ] = __( '- Select Template -', 'product-blocks' );
		while ( $loop->have_posts() ) :
			$loop->the_post();
			$data[ get_the_ID() ] = get_the_title();
		endwhile;
		wp_reset_postdata();
		return $data;
	}

	public function get_endpoint_urls() {
		if ( wopb_function()->get_setting( 'is_wc_ready' ) ) {
			$endpoints = array(
				'ajax_pagination'              => \WC_AJAX::get_endpoint( 'wopb_pagination' ),
				'ajax_load_more'               => \WC_AJAX::get_endpoint( 'wopb_load_more' ),
				'ajax_filter'                  => \WC_AJAX::get_endpoint( 'wopb_filter' ),
				'ajax_product_list'            => \WC_AJAX::get_endpoint( 'wopb_product_list' ),
				'ajax_variation_loop_add_cart' => \WC_AJAX::get_endpoint( 'wopb_variation_loop_add_cart' ),
				'ajax_quick_view'              => \WC_AJAX::get_endpoint( 'wopb_quickview' ),
			);
			return $endpoints;
		} else {
			return array();
		}
	}

	/**
	 * Content Print
	 *
	 * @since v.3.0.0
	 * @param NUMBER | Post ID
	 * @return STRING | Content of the Post
	 */
	public function content( $post_id ) {
		$content_post = get_post( $post_id );
		$content      = $content_post->post_content;
		$content      = do_blocks( $content );
		$content      = do_shortcode( $content );
		$content      = str_replace( ']]>', ']]&gt;', $content );
		$content      = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content );
		$content      = preg_replace( '/^(?:<br\s*\/?>\s*)+/', '', $content );
		return $content;
	}

	/**
	 * Retrieve an option using the shared WordPress-backed helper.
	 *
	 * @since v.3.1.0
	 * @param string $option        Option name.
	 * @param mixed  $default_value Default when the option does not exist.
	 * @return mixed
	 */
	public function get_option_without_cache( $option, $default_value = false ) {
		require_once WOPB_PATH . 'includes/class-xpo.php';
		return Xpo::get_option_without_cache( $option, $default_value );
	}

	/**
	 * Add an option using the shared WordPress-backed helper.
	 *
	 * @since v.3.1.0
	 * @param string      $option   Option name.
	 * @param mixed       $value    Option value.
	 * @param string|bool $autoload Whether WordPress should autoload the option.
	 * @return bool
	 */
	public function add_option_without_cache( $option, $value = '', $autoload = 'yes' ) {
		require_once WOPB_PATH . 'includes/class-xpo.php';
		return Xpo::add_option_without_cache( $option, $value, $autoload );
	}

	public function product_format( $params ) {
		$data = array();
		$loop = $params['products'];
		if ( $loop->have_posts() ) {
			while ( $loop->have_posts() ) {
				$loop->the_post();
				$var          = array();
				$post_id      = get_the_ID();
				$product      = wc_get_product( $post_id );
				$weight       = $product->get_weight();
				$weight       = $weight ? ( wc_format_localized_decimal( $weight ) . ' ' . get_option( 'woocommerce_weight_unit' ) ) : 'N/A';
				$stock_status = $product->is_purchasable() && $product->is_in_stock() ? 'in stock' : '';
				$wopb_buffer_level_1 = ob_get_level();
				ob_start();
				try {
					wc_display_product_attributes( $product );
				$additional               = ob_get_clean();
				} finally {
					while ( ob_get_level() > $wopb_buffer_level_1 ) {
						if ( ! ob_end_clean() ) {
							break;
						}
					}
				}
				$var['title']             = isset( $params['search'] ) ? $this->highlightSearchKey( get_the_title(), $params['search'] ) : $product->get_title();
				$var['type']              = $product->get_type();
				$var['permalink']         = $product->get_permalink();
				$var['price_html']        = $product->get_price_html();
				$var['rating_average']    = $product->get_average_rating();
				$var['rating_count']      = $product->get_rating_count();
				$var['short_description'] = $product->get_short_description();
				$var['stock_qty']         = $product->get_stock_quantity();
				$var['stock_status']      = $stock_status;
				$var['additional']        = $additional;
				$var['weight']            = $weight;
				$var['sku']               = $product->get_sku() ? $product->get_sku() : 'N/A';
				$var['dimensions']        = $product->get_dimensions( false ) ? wc_format_dimensions( $product->get_dimensions( false ) ) : 'N/A';

				if ( isset( $params['size'] ) ) {
					$var['image'] = $product->get_image( $params['size'] );
				} elseif ( has_post_thumbnail() ) {
					$var['image'] = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ), 'large' )[0];
				} else {
					$var['image'] = esc_url( WOPB_URL . 'assets/img/wopb-fallback-img.png' );
				}

				// tag
				$tag = get_the_terms( $post_id, ( isset( $prams['tag'] ) ? esc_attr( $prams['tag'] ) : 'product_tag' ) );
				if ( ! empty( $tag ) ) {
					$v = array();
					foreach ( $tag as $val ) {
						$v[] = array(
							'slug' => $val->slug,
							'name' => $val->name,
							'url'  => esc_url( get_term_link( $val->term_id ) ),
						);
					}
					$var['tag'] = $v;
				}

				// cat
				$cat = get_the_terms( $post_id, ( isset( $prams['cat'] ) ? esc_attr( $prams['cat'] ) : 'product_cat' ) );
				if ( ! empty( $cat ) ) {
					$v = array();
					foreach ( $cat as $val ) {
						$v[] = array(
							'slug' => $val->slug,
							'name' => $val->name,
							'url'  => esc_url( get_term_link( $val->term_id ) ),
						);
					}
					$var['category'] = $v;
				}

				// brand
				if ( taxonomy_exists( 'product_brand' ) ) {
					$brand = get_the_terms( $post_id, 'product_brand' );
					if ( ! empty( $brand ) && ! is_wp_error( $brand ) ) {
						$v = array();
						foreach ( $brand as $val ) {
							$v[] = array(
								'slug' => $val->slug,
								'name' => $val->name,
								'url'  => esc_url( get_term_link( $val->term_id ) ),
							);
						}
						$var['brand'] = $v;
					}
				}
				$data[] = $var;
			}
			wp_reset_postdata();
		}
		return $data;
	}

	/**
	 * All Loader When Modal Open
	 *
	 * @since v.3.1.1
	 * @return array
	 */
	public function modal_loaders() {
		return array(
			'loader_1' => $this->svg_icon( 'loader_1' ),
			'loader_2' => $this->svg_icon( 'loader_2' ),
			'loader_3' => $this->svg_icon( 'loader_3' ),
			'loader_4' => $this->svg_icon( 'loader_4' ),
			'loader_5' => $this->svg_icon( 'loader_5' ),
			'loader_6' => $this->svg_icon( 'loader_6' ),
			'loader_7' => $this->svg_icon( 'loader_7' ),
		);
	}

	/**
	 * Return content after highlight search key
	 *
	 * @param $content
	 * @param $search
	 * @return HTML
	 * @since v.2.6.8
	 */
	public function highlightSearchKey( $content, $search ) {
		if ( trim( (string) $search ) === '' ) {
			return $content;
		}

		// Create a new DOMDocument object and load the HTML
		$useErrors = libxml_use_internal_errors( true );
		$doc       = new \DOMDocument();
		$doc->loadHTML( '<?xml encoding="UTF-8">' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		// Use DOMXPath to select all text nodes
		$xpath     = new \DOMXPath( $doc );
		$textNodes = $xpath->query( '//text()' );

		$escapedSearch = preg_quote( htmlspecialchars( $search, ENT_QUOTES, 'UTF-8' ), '/' );

		foreach ( $textNodes as $node ) {
			$text            = $node->nodeValue;
			$escapedText     = htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
			$highlightedText = preg_replace( '/(' . $escapedSearch . ')/iu', '<strong class="wopb-highlight">$1</strong>', $escapedText );
			if ( null !== $highlightedText && $highlightedText !== $escapedText ) {
				$newNode = $doc->createDocumentFragment();
				$newNode->appendXML( $highlightedText );
				$node->parentNode->replaceChild( $newNode, $node );
			}
		}

		// Output the modified HTML
		$html = $doc->saveHTML();
		libxml_clear_errors();
		libxml_use_internal_errors( $useErrors );

		return $html;
	}

	/**
	 * Whether the current visitor (including anonymous users) is allowed to view a product.
	 *
	 * Front-end AJAX endpoints (Quick View, Compare, Wishlist) accept an arbitrary product
	 * ID from the request, so this gate keeps private, draft, and password protected
	 * products from being exposed to visitors who shouldn't be able to see them.
	 *
	 * @param int $post_id
	 * @return bool
	 * @since v.4.5.3
	 */
	public function can_view_product( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return false;
		}
		$post = get_post( $post_id );
		if ( ! $post || 'product' !== $post->post_type ) {
			return false;
		}
		if ( 'publish' !== $post->post_status && ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}
		if ( post_password_required( $post ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Allowed Block Tags
	 *
	 * @return array|boolean
	 * @since v.4.0.0
	 */
	public function allowed_block_tags( $search = '' ) {
		$array_lists = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p', 'div', 'section', 'article' );
		return $search ? in_array( $search, $array_lists, true ) : $array_lists;
	}

	/**
	 * Update CSS.
	 *
	 * @since v.4.0.0
	 */
	public function update_css( $key, $action = 'add', $css_change = '' ) {
		$css          = get_option( 'wopb_generated_css' );
		$final_change = "/*{$key}_start*/" . $css_change . "/*{$key}_stop*/";
		if ( strpos( $css, $key . '_start' ) !== false ) {
			$reg  = "/(\/\*{$key}_start\*\/)(.*?)(\/\*{$key}_stop\*\/)/s";
			$data = preg_replace( $reg, ( 'delete' === $action ? '' : $final_change ), $css );
			update_option( 'wopb_generated_css', $data );
		} elseif ( 'delete' !== $action ) {
			update_option( 'wopb_generated_css', $css . $final_change );
		}
	}

	/**
	 * Grid Add to Cart HTML
	 *
	 * @param $product
	 * @param $params
	 * @return STRING | Add to cart HTML as String
	 * @since v.1.0.0
	 */
	public function get_add_to_cart( $product, $params ) {
		$data             = '';
		$before           = '';
		$after            = '';
		$cart_text        = isset( $params['cartText'] ) ? $params['cartText'] : '';
		$cart_active      = isset( $params['cartActive'] ) ? $params['cartActive'] : '';
		$tooltip_position = isset( $params['tooltipPosition'] ) ? $params['tooltipPosition'] : '';
		$is_icon          = isset( $params['isIcon'] ) ? $params['isIcon'] : '';
		$no_follow        = ! empty( $params['cartNoFollow'] ) ? $params['cartNoFollow'] : '';
		$is_purchasable   = $product->is_purchasable();

		if ( $is_purchasable ) {
			/**
			 * Filters the add-to-cart text set in a grid block for a product that can be bought.
			 *
			 * @since 4.6.0
			 * @param string      $cart_text Text set in the block, '' for WooCommerce's own text.
			 * @param \WC_Product $product   Product.
			 * @param array       $params    The block's add-to-cart parameters.
			 */
			$cart_text = apply_filters( 'wopb_add_to_cart_text', $cart_text, $product, $params );
		}

		$attributes = array(
			'aria-label'       => $product->add_to_cart_description(),
			'data-quantity'    => '1',
			'data-product_id'  => $product->get_id(),
			'data-product_sku' => $product->get_sku(),
		);

		if ( $no_follow ) {
			$attributes['rel'] = 'nofollow';
		}

		if ( $product->is_type( 'external' ) ) {
			$attributes['target'] = '_blank';
		}

		$args = array(
			'quantity'   => '1',
			'attributes' => $attributes,
			'class'      => implode(
				' ',
				array_filter(
					array(
						'add_to_cart_button',
						( $product->is_type( 'simple' ) && $is_purchasable ? 'ajax_add_to_cart' : '' ),
						'wopb-cart-normal',
					)
				)
			),
		);

		$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( array(), $args ), $product );

		if ( $product->get_stock_status() === 'outofstock' || ! $is_purchasable ) {
			$cart_text = '';
		}

		$data            .= '<span class="wopb-cart-action ' . ( $is_icon ? 'wopb-tooltip-text' : '' ) . '" data-postid="' . esc_attr( $product->get_id() ) . '">';
			$inner_html   = '';
			$tooltip_html = '';
		if ( $is_icon ) {
			$inner_html   .= $this->svg_icon( 'cart' );
			$tooltip_html .= '<span class="wopb-cart-tooltip wopb-tooltip-text-' . $tooltip_position . '">' . esc_html( $cart_text && $product->is_type( 'simple' ) ? $cart_text : $product->add_to_cart_text() ) . '</span>';
		} else {
			$inner_html .= $cart_text && $product->is_type( 'simple' ) ? esc_html( $cart_text ) : esc_html( $product->add_to_cart_text() );
		}
		if ( $product->is_type( 'variable' ) ) {
			$data .= apply_filters(
				'woocommerce_loop_product_link', // WPCS: XSS ok.
				sprintf(
					'<a href="%s" class="%s" data-stock="%s" data-add-to-cart-text="%s" %s>%s</a>',
					esc_url( $product->add_to_cart_url() ),
					( ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '' ),
					esc_attr( $product->get_stock_quantity() ),
					$cart_text,
					wc_implode_html_attributes( $attributes ),
					$inner_html
				),
				$product,
				$args
			);
			$data .= $tooltip_html;

			if ( ! $is_icon && wopb_function()->active_plugin( 'woo-variation-swatches' ) ) {
				$swatch_position = woo_variation_swatches()->get_option( 'archive_swatches_position' );
				$wopb_buffer_level_2 = ob_get_level();
				ob_start();
				try {
					do_shortcode( '[wvs_show_archive_variation product_id="' . $product->get_id() . '"]' );
				$woo_swatch = ob_get_clean();
				} finally {
					while ( ob_get_level() > $wopb_buffer_level_2 ) {
						if ( ! ob_end_clean() ) {
							break;
						}
					}
				}
				if ( 'after' === $swatch_position ) {
					$after .= $woo_swatch;
				} else {
					$before .= $woo_swatch;
				}
			}
		} else {
			$data .= apply_filters(
				'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
				sprintf(
					'<a href="%s" class="%s" data-stock="%s" %s>%s</a>',
					esc_url( $product->add_to_cart_url() ),
					( ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '' ),
					esc_attr( $product->get_stock_quantity() ),
					wc_implode_html_attributes( $attributes ),
					$inner_html
				),
				$product,
				$args
			);
			$data .= $tooltip_html;
		}
			$data .= '<a href="' . esc_url( wc_get_cart_url() ) . '" class="wopb-cart-active">';
		if ( $is_icon ) {
			$data .= $this->svg_icon( 'viewCart' );
			$data .= '<span class="wopb-tooltip-text-' . $tooltip_position . '">' . ( $cart_active ? esc_html( $cart_active ) : esc_html__( 'View Cart', 'product-blocks' ) ) . '</span>';
		} else {
			$data .= $cart_active ? esc_html( $cart_active ) : esc_html__( 'View Cart', 'product-blocks' );
		}
			$data .= '</a>';
		$data     .= '</span>';

		return $before . '<div class="wopb-product-btn ' . ( $is_icon ? 'wopb_meta_svg_con' : '' ) . '">' . $data . '</div>' . $after;
	}


	/**
	 * Get Value And Convert to CSS
	 *
	 * @param $type
	 * @param $value
	 * @return string
	 * @since v.4.0.0
	 */
	public function convert_css( $type, $value ) {
		$css = '';
		if ( ! empty( $value ) ) {
			switch ( $type ) {
				case 'general':
					$css .= ! empty( $value['size'] ) ? 'font-size: ' . $value['size'] . 'px;' : '';
					$css .= 'font-weight: ' .
						( ! empty( $value['bold'] )
							? ( 1 == $value['bold'] ? 'bold' : $value['bold'] )
							: 'normal'
						) . ' !important;';
					$css .= ! empty( $value['italic'] ) ? 'font-style: ' . 'italic;' : '';
					$css .= 'text-decoration: ' . ( ! empty( $value['underline'] ) ? 'underline;' : 'none' ) . ';';
					if ( isset( $value['color'] ) ) {
						$css .= 'color: ' . (
							! empty( $value['color'] )
								? $value['color']
								: 'unset'
							) . ';';
					}
					if ( isset( $value['bg'] ) ) {
						$css .= 'background-color: ' . (
							! empty( $value['bg'] )
								? $value['bg']
								: 'unset'
							) . ';';
					}
					break;

				case 'hover':
					if ( isset( $value['hover_color'] ) ) {
						$css .= 'color: ' . (
							! empty( $value['hover_color'] )
								? $value['hover_color']
								: ( ! empty( $value['color'] ) ? $value['color'] : 'unset' )
							) . ';';
					}
					if ( isset( $value['hover_bg'] ) ) {
						$css .= 'background-color: ' . (
							! empty( $value['hover_bg'] )
								? $value['hover_bg']
								: ( ! empty( $value['bg'] ) ? $value['bg'] : 'unset' )
							) . ';';
					}
					break;

				case 'dimension':
					foreach ( array( 'top', 'right', 'bottom', 'left' ) as $direction ) {
						$css .= ( ! empty( $value[ $direction ] ) ? $value[ $direction ] : 0 ) . 'px ';
					}
					break;

				case 'border':
					$css .= 'border: ' .
						( ! empty( $value['border'] )
							? $value['border']
							: 0
						) . 'px solid ' .
						( ! empty( $value['color'] )
							? $value['color']
							: ''
						) . ';';
					break;

				case 'radius':
					$css .= 'border-radius: ' . $value . 'px;';
					break;

				default:
					break;
			}
		}
		return $css;
	}

	/**
	 * Get Theme name
	 *
	 * @return string
	 * @since v.4.0.0
	 */
	public function get_theme_name() {
		$current_theme_dir = '';
		$current_theme     = wp_get_theme();
		if ( $current_theme->exists() && $current_theme->parent() ) {
			$parent_theme = $current_theme->parent();

			if ( $parent_theme->exists() ) {
				$current_theme_dir = $parent_theme->get_stylesheet();
			}
		} elseif ( $current_theme->exists() ) {
			$current_theme_dir = $current_theme->get_stylesheet();
		}

		return $current_theme_dir;
	}

	/**
	 * Get All Reusable ID
	 *
	 * @param $post_id
	 * @return ARRAY | Query Arg
	 * @since v.4.0.4
	 */
	public function get_reusable_ids( $post_id ) {
		$reusable_id = array();
		if ( $post_id ) {
			$post = get_post( $post_id );
			if ( isset( $post->post_content ) ) {
				if ( has_blocks( $post->post_content ) &&
					strpos( $post->post_content, 'wp:block' ) &&
					strpos( $post->post_content, '"ref"' ) !== false
				) {
					$blocks = parse_blocks( $post->post_content );
					foreach ( $blocks as $key => $value ) {
						if ( isset( $value['attrs']['ref'] ) ) {
							$reusable_id[] = $value['attrs']['ref'];
						}
					}
				}
			}
		}
		return $reusable_id;
	}

	/**
	 * get directory file contents
	 *
	 * @param $path
	 * @return ARRAY | Query Arg
	 * @since v.4.0.4
	 */
	public function get_path_file_contents( $path ) {
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			$init_fs = WP_Filesystem();
			if ( ! $init_fs ) {
				return '';
			}
		}

		if ( $wp_filesystem->exists( $path ) ) {
			return $wp_filesystem->get_contents( $path );
		} else {
			return '';
		}
	}

	/**
	 * build css for inline printing // used for some builder
	 *
	 * @param $post_id
	 * @param bool $call_common
	 * @return ARRAY | Query Arg
	 * @since v.4.0.4
	 */
	public function build_css_for_inline_print( $post_id, $call_common = true ) {
		if ( $post_id ) {
			$upload_dir_url     = wp_get_upload_dir();
			$upload_css_dir_url = trailingslashit( $upload_dir_url['basedir'] );

			$css_dir_url = trailingslashit( $upload_dir_url['baseurl'] );
			if ( is_ssl() ) {
				$css_dir_url = str_replace( 'http://', 'https://', $css_dir_url );
			}
			$reusable_css = '';
			$reusable_id  = $this->get_reusable_ids( $post_id );
			if ( ! empty( $reusable_id ) ) {
				foreach ( $reusable_id as $id ) {
					$reusable_dir_path = $upload_css_dir_url . "product-blocks/wopb-css-{$id}.css";
					if ( file_exists( $reusable_dir_path ) ) {
						$reusable_css .= $this->get_path_file_contents( $reusable_dir_path );
					} else {
						$reusable_css .= get_post_meta( $id, '_wopb_css', true );
					}
				}
			}

			$css_dir_path = $upload_css_dir_url . "product-blocks/wopb-css-{$post_id}.css";
			$css          = '';
			if ( file_exists( $css_dir_path ) ) {
				$css = $this->get_path_file_contents( $css_dir_path );
			} else {
				$css = get_post_meta( $post_id, '_wopb_css', true );
			}
			if ( $reusable_css . $css ) {
				if ( $call_common ) {
					$this->register_scripts_common();
				}
				return '<style id="wopb-post-' . esc_attr( $post_id ) . '" type="text/css">' . wp_strip_all_tags( $reusable_css . $css ) . '</style>';
			}
		}
		return '';
	}

	public function loop_item_classes() {
		$elements = array();
		if (
			wopb_function()->active_plugin( 'woo-variation-swatches' ) &&
			wc_string_to_bool( woo_variation_swatches()->get_option( 'show_on_archive' ) )
		) {
			$elements['wrapper'] = ' wvs-archive-product-wrapper';
			$elements['image']   = ' wvs-archive-product-image';
		}
		return $elements;
	}

	public function wp_kses_safe( $content = '', $extra_params = array() ) {
		if ( empty( $content ) ) {
			return '';
		}
		$allowed = $this->allowed_html_tags(
			isset( $extra_params['allowed'] ) && is_array( $extra_params['allowed'] )
				? $extra_params['allowed']
				: array()
		);

		return wp_kses( $content, $allowed );
	}
	public function get_the_current_page() {
		// Prefer explicit homepage/posts page IDs when configured
		$front_id = (int) get_option( 'page_on_front' );
		if ( is_front_page() && $front_id > 0 ) {
			return $front_id;
		}
		// If front page but no static page set, return 'home' string key
		if ( is_front_page() && 0 === $front_id ) {
			return 'home';
		}

		$posts_page_id = (int) get_option( 'page_for_posts' );
		if ( is_home() && $posts_page_id > 0 ) {
			return $posts_page_id;
		}

		// If blog page but no static page set, return 'blog' string key
		if ( is_home() && 0 === $posts_page_id ) {
			return 'blog';
		}

		$page_id = get_the_ID();

		// Product / single contexts
		if ( is_product() || is_single() ) {
			return 'single_product';
		}

		// WooCommerce pages: return actual page ID when available, otherwise keep string key
		if ( function_exists( 'wc_get_page_id' ) ) {
			if ( is_cart() ) {
				$cart_id = wc_get_page_id( 'cart' );
				return $cart_id > 0 ? (int) $cart_id : 'cart';
			}
			if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
				$checkout_id = wc_get_page_id( 'checkout' );
				return $checkout_id > 0 ? (int) $checkout_id : 'checkout';
			}
			if ( is_account_page() ) {
				$acc_id = wc_get_page_id( 'myaccount' );
				return $acc_id > 0 ? (int) $acc_id : 'my_account';
			}
			if ( is_shop() && ! is_search() ) {
				$shop_id = wc_get_page_id( 'shop' );
				return $shop_id > 0 ? (int) $shop_id : 'shop';
			}
		}

		// Product category/tag/taxonomy archive - return 'product_archive' to match settings
		if ( is_product_category() || is_product_tag() || is_tax( get_object_taxonomies( 'product' ) ) ) {
			return 'product_archive';
		}

		// General product archive
		if ( is_archive() && is_post_type_archive( 'product' ) ) {
			return 'product_archive';
		} elseif ( is_search() ) {
			return 'product_search';
		} elseif ( is_404() ) {
			return '404';
		} elseif ( is_wc_endpoint_url( 'order-received' ) && is_checkout() ) {
			return 'thank_you';
		}
		return $page_id;
	}

	/** Named WordPress hook callback. */
	public function distinct_product_posts() {
		return 'DISTINCT'; // duplicate data remove
	}
}
