<?php
/**
 * REST API Action.
 *
 * @package WOPB\REST_API
 * @since v.1.0.0
 */
namespace WOPB;

defined( 'ABSPATH' ) || exit;

/**
 * Styles class.
 */
class REST_API {

	/**
	 * Setup class.
	 *
	 * @since v.1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'wopb_register_route' ) );
	}


	/**
	 * REST API Action
	 *
	 * @since v.1.0.0
	 * @return NULL
	 */
	public function wopb_register_route() {
		register_rest_route(
			'wopb',
			'posts',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'args'                => array(),
				'callback'            => array( $this, 'wopb_route_post_data' ),
				'permission_callback' => array( $this, 'can_access_editor_data' ),
			)
		);
		register_rest_route(
			'wopb',
			'category',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'args'                => array(),
				'callback'            => array( $this, 'wopb_route_category_data' ),
				'permission_callback' => array( $this, 'can_access_editor_data' ),
			)
		);
		register_rest_route(
			'wopb',
			'common',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'args'                => array(),
				'callback'            => array( $this, 'wopb_route_common_data' ),
				'permission_callback' => array( $this, 'can_access_editor_data' ),
			)
		);
		register_rest_route(
			'wopb',
			'preview',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'args'                => array(),
				'callback'            => array( $this, 'wopb_route_preview_data' ),
				'permission_callback' => array( $this, 'can_preview_product' ),
			)
		);
		register_rest_route(
			'wopb/v2',
			'/template_page_insert/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'template_page_insert' ),
					'permission_callback' => function () {
						return apply_filters( 'wopb_rest_api_capability', current_user_can( 'manage_options' ) );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'wopb/v1',
			'/search/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'search_settings_action' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'wopb',
			'views',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'popular_posts_tracker_callback' ),
				'permission_callback' => array( $this, 'can_track_product_view' ),
			)
		);
	}


	/**
	 * The request's parameters, which must all be single values (the editor sends JSON as strings).
	 *
	 * Values are not run through sanitize_text_field(): that would strip the %xx octets of
	 * non-Latin term slugs. WP_Query and the term functions handle them as before.
	 *
	 * @param \WP_REST_Request $request The request.
	 * @return array|\WP_Error Parameters, or a 400 error naming the first invalid field.
	 */
	private function get_scalar_params( $request ) {
		$params = $request->get_params();
		foreach ( $params as $key => $value ) {
			if ( null !== $value && ! is_scalar( $value ) ) {
				return $this->invalid_param_error( $key );
			}
		}

		// These attributes are built by server-side renderers, never by editor REST requests.
		foreach ( array( 'productTaxonomy', 'queryCatAction', 'queryTagAction', 'custom_action', 'post__not_in' ) as $key ) {
			if ( array_key_exists( $key, $params ) ) {
				return $this->invalid_param_error( $key );
			}
		}

		$json_keys = array( 'queryCat', 'queryTaxValue', 'filterCat', 'filterTag', 'filterAction' );
		foreach ( $json_keys as $key ) {
			if ( ! array_key_exists( $key, $params ) ) {
				continue;
			}
			// Older blocks may store an empty selection as an empty string.
			if ( '' === $params[ $key ] ) {
				$params[ $key ] = '[]';
			}
			if ( ! $this->is_selection_json( $params[ $key ], 'filterAction' !== $key ) ) {
				return $this->invalid_param_error( $key );
			}
		}

		// Product include/exclude controls also accept their legacy comma-separated IDs.
		foreach ( array( 'queryInclude', 'queryExclude' ) as $key ) {
			if ( isset( $params[ $key ] ) && is_string( $params[ $key ] ) && '[' === substr( $params[ $key ], 0, 1 ) && ! $this->is_selection_json( $params[ $key ], true, false ) ) {
				return $this->invalid_param_error( $key );
			}
		}

		if ( ! empty( $params['filterShow'] ) ) {
			$params += array(
				'productView'  => 'grid',
				'filterType'   => 'product_cat',
				'filterCat'    => '[]',
				'filterTag'    => '[]',
				'filterAction' => '[]',
			);
		}

		return $params;
	}

	/**
	 * Validate JSON selections without changing the strings passed to query filters.
	 *
	 * Select controls send either scalar values or objects with a value property.
	 * Keep each list homogeneous: get_value() consumes the whole list as objects.
	 *
	 * @param mixed $json          Encoded selection.
	 * @param bool  $allow_objects Whether selection objects are supported by the consumer.
	 * @param bool  $allow_scalars Whether plain scalar list entries are supported.
	 * @return bool
	 */
	private function is_selection_json( $json, $allow_objects, $allow_scalars = true ) {
		if ( ! is_string( $json ) ) {
			return false;
		}
		$items = json_decode( $json );
		if ( ! is_array( $items ) ) {
			return false;
		}
		$object_list = isset( $items[0] ) && is_object( $items[0] );
		foreach ( $items as $item ) {
			if ( is_object( $item ) !== $object_list ) {
				return false;
			}
			if ( $object_list ) {
				if ( ! $allow_objects || ! isset( $item->value ) ) {
					return false;
				}
				$item = $item->value;
			} elseif ( ! $allow_scalars ) {
				return false;
			}
			if ( ! is_string( $item ) && ! is_int( $item ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * A 400 error for a parameter of the wrong shape.
	 *
	 * @param string $key Parameter name.
	 * @return \WP_Error
	 */
	private function invalid_param_error( $key ) {
		return new \WP_Error(
			'rest_invalid_param',
			/* translators: %s: Parameter name. */
			sprintf( __( 'Invalid parameter: %s', 'product-blocks' ), $key ),
			array( 'status' => 400 )
		);
	}

	/**
	 * Editor data is available only to users who can edit content.
	 *
	 * @return bool
	 */
	public function can_access_editor_data() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Authorize access to the individual product used in a builder preview.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return bool
	 */
	public function can_preview_product( $request ) {
		if ( ! $this->can_access_editor_data() ) {
			return false;
		}
		$post_id = $request->get_param( 'previews' );
		if ( null === $post_id || '' === $post_id ) {
			return true;
		}
		return is_scalar( $post_id ) && ctype_digit( (string) $post_id )
			&& 'product' === get_post_type( (int) $post_id )
			&& current_user_can( 'read_post', (int) $post_id );
	}

	/**
	 * Public view tracking is limited to visible, unprotected catalog products.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return bool
	 */
	public function can_track_product_view( $request ) {
		$post_id = $request->get_param( 'postID' );
		if ( ! is_scalar( $post_id ) || ! ctype_digit( (string) $post_id ) || 'product' !== get_post_type( (int) $post_id ) ) {
			return false;
		}
		$product = wc_get_product( (int) $post_id );
		return $product && 'publish' === $product->get_status() && $product->is_visible() && ! post_password_required( (int) $post_id );
	}


	/**
	 * Post View Counter for Every Post
	 *
	 * @since v.1.0.0
	 * @return NULL
	 */
	public function popular_posts_tracker_callback( $server ) {
		if ( ! $this->can_track_product_view( $server ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot access this resource.', 'product-blocks' ), array( 'status' => rest_authorization_required_code() ) );
		}

		$params = $server->get_params();
		if ( ! isset( $params['wpnonce'] ) || ! is_string( $params['wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $params['wpnonce'] ) ), 'wopb-nonce' ) ) {
			die();
		}
		$post_id = absint( $params['postID'] );
		if ( $post_id ) {
			// View Product Count
			$count = (int) get_post_meta( $post_id, '__product_views_count', true );
			update_post_meta( $post_id, '__product_views_count', $count ? (int) $count + 1 : 1 );

			// Recently View Products
			$viewed_products = empty( $_COOKIE['__wopb_recently_viewed'] ) ? array() : (array) explode( '|', sanitize_text_field( wp_unslash( $_COOKIE['__wopb_recently_viewed'] ) ) );
			if ( ! in_array( $post_id, $viewed_products ) ) {
				$viewed_products[] = $post_id;
			}
			if ( count( $viewed_products ) > 30 ) {
				array_shift( $viewed_products );
			}
			wc_setcookie( '__wopb_recently_viewed', implode( '|', $viewed_products ) );

			return rest_ensure_response( array() );
		}
	}


	/**
	 * Taxonomy Data Response of REST API
	 *
	 * @since v.2.3.7
	 * @return ARRAY | \WP_REST_Response Taxonomy List as Array
	 */
	public function wopb_route_common_data() {
		if ( ! $this->can_access_editor_data() ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot access this resource.', 'product-blocks' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! isset( $_REQUEST['wpnonce'] ) || ! is_string( $_REQUEST['wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['wpnonce'] ) ), 'wopb-nonce' ) ) {
			return rest_ensure_response( array() );
		}

		// Stock Status
		$stock_status = array();
		foreach ( wc_get_product_stock_status_options() as $key => $status ) {
			$stock_status[] = array(
				'value' => $key,
				'label' => $status,
			);
		}

		// Global Customizer
		$global = get_option( 'productx_global', array() );
		// Image Size
		$image_sizes = wopb_function()->get_image_size();

		$post_type_data = array();
		$post_types     = wopb_function()->get_post_type();
		foreach ( $post_types as $post_type_slug => $post_type ) {
			$taxonomies     = 'product' === $post_type_slug ? array_diff( get_object_taxonomies( $post_type_slug ), array( 'product_type', 'product_visibility', 'product_shipping_class' ) ) : get_object_taxonomies( $post_type_slug );
			$taxonomy_array = array();
			foreach ( $taxonomies as $key => $taxonomy_slug ) {
				$taxonomy   = get_taxonomy( $taxonomy_slug );
				$terms      = get_terms(
					array(
						'taxonomy'   => $taxonomy_slug,
						'hide_empty' => false,
					)
				);
				$term_array = array();
				if ( ! is_wp_error( $terms ) ) {
					foreach ( $terms as $k => $term ) {
						$term_array[ urldecode_deep( $term->slug ) ] = array(
							'id'   => $term->term_id,
							'name' => $term->name,
						);
					}
				}
				$taxonomy_array[] = array(
					'name'  => $taxonomy->name,
					'label' => $taxonomy->label,
					'terms' => $term_array,
				);
			}
			$post_type_data[ $post_type_slug ] = $taxonomy_array;
		}

		return rest_ensure_response(
			array(
				'tag'                => wopb_function()->taxonomy( 'product_tag' ),
				'cat'                => wopb_function()->taxonomy( 'product_cat' ),
				'global'             => $global,
				'image'              => $image_sizes,
				'post_type_taxonomy' => $post_type_data,
				'post_type'          => wp_json_encode( $post_types ),
				'stock_status'       => $stock_status,
			)
		);
	}

	/**
	 * Builder Preview Data
	 *
	 * @since v.2.3.7
	 * @param ARRAY | Parameters of the REST API
	 * @return ARRAY | Response as Array
	 */
	public function wopb_route_preview_data( $prams ) {
		if ( ! $this->can_preview_product( $prams ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot access this resource.', 'product-blocks' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! isset( $_REQUEST['wpnonce'] ) || ! is_string( $_REQUEST['wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['wpnonce'] ) ), 'wopb-nonce' ) ) {
			return rest_ensure_response( array() );
		}

		global $product;
		$post_id = isset( $prams['previews'] ) ? $prams['previews'] : '';
		if ( $post_id ) {
			$post_data = array();
			$products  = wc_get_product( $post_id );
			if ( ! $products ) {
				return new \WP_Error( 'rest_not_found', __( 'Product not found.', 'product-blocks' ), array( 'status' => 404 ) );
			}
			switch ( $prams['type'] ) {
				case 'title':
					$post_data['title'] = wp_kses_post( $products->get_title() );
					break;
				case 'description':
					$post_data['description'] = wp_kses_post( $products->get_description() );
					break;
				case 'short':
					$post_data['short'] = wp_kses_post( $products->get_short_description() );
					break;
				case 'image':
					$images        = array();
					$feature_image = $products->get_image_id();
					if ( ! empty( $feature_image ) ) {
						$images[] = esc_url( wp_get_attachment_image_url( $feature_image, 'full' ) );
					}
					$galleries = $products->get_gallery_image_ids();
					if ( ! empty( $galleries ) ) {
						foreach ( $galleries as $key => $val ) {
							$images[] = esc_url( wp_get_attachment_image_url( $val, 'full' ) );
						}
					}
					$sales                     = $products->get_sale_price();
					$regular                   = $products->get_regular_price();
					$post_data['images']       = $images;
					$post_data['images_thumb'] = $images;
					$post_data['percentage']   = ( $sales && (float) $regular > 0 ) ? round( ( ( $regular - $sales ) / $regular ) * 100 ) : 0;
					break;
				case 'meta':
					$post_data['sku']      = $products->get_sku();
					$post_data['category'] = '<div className="meta-block__cat">' . wp_kses_post( $this->list_items( $products->get_category_ids(), 'product_cat' ) ) . '</div>';
					$brand_terms           = taxonomy_exists( 'product_brand' ) ? get_the_terms( $products->get_id(), 'product_brand' ) : array();
					$brand_terms           = ( ! empty( $brand_terms ) && ! is_wp_error( $brand_terms ) ) ? wp_list_pluck( $brand_terms, 'term_id' ) : array();
					$post_data['brand']    = '<div className="meta-block__brand">' . wp_kses_post( $this->list_items( $brand_terms, 'product_brand' ) ) . '</div>';
					$post_data['tag']      = '<div className="meta-block__tag">' . wp_kses_post( $this->list_items( $products->get_tag_ids(), 'product_tag' ) ) . '</div>';
					break;
				case 'price':
					$sales                      = $products->get_sale_price();
					$regular                    = $products->get_regular_price();
					$post_data['sales_price']   = $sales;
					$post_data['regular_price'] = $regular;
					$post_data['range_price']   = ! $regular && ! $sales ? wp_kses( (string) $products->get_price_html(), wopb_function()->allowed_html_tags() ) : '';
					$post_data['percentage']    = ( $sales && (float) $regular > 0 ) ? round( ( ( $regular - $sales ) / $regular ) * 100 ) : 0;
					$post_data['symbol']        = get_woocommerce_currency_symbol();
					break;
				case 'rating':
					$post_data['sales']   = $products->get_total_sales();
					$post_data['rating']  = $products->get_review_count();
					$post_data['average'] = ( ( $products->get_average_rating() / 5 ) * 100 ) . '%';
					break;
			}
			return array(
				'type' => 'data',
				'data' => $post_data,
			);
		}

		$data     = array(
			array(
				'value' => '',
				'label' => '-- Select Product --',
			),
		);
		$products = wc_get_products(
			array(
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);
		foreach ( $products as $key => $val ) {
			$data[] = array(
				'value' => $val->get_id(),
				'label' => $val->get_title(),
			);
		}
		return array(
			'type' => 'list',
			'list' => $data,
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
			if ( $inc > 1 ) {
				$content .= ', ';
			}
			$content .= '<a class="meta-block__value" href="#">' . esc_html( $term->name ) . '</a>';
			++$inc;
		}
		return $content;
	}


	/**
	 * REST API Action
	 *
	 * @since v.1.0.0
	 * @param ARRAY | Parameters of the REST API
	 * @return ARRAY | Response as Array
	 */
	public function wopb_route_category_data( $prams ) {
		if ( ! $this->can_access_editor_data() ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot access this resource.', 'product-blocks' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! isset( $_REQUEST['wpnonce'] ) || ! is_string( $_REQUEST['wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['wpnonce'] ) ), 'wopb-nonce' ) ) {
			return rest_ensure_response( array() );
		}

		$params = $this->get_scalar_params( $prams );
		if ( is_wp_error( $params ) ) {
			return $params;
		}

		$queryCat = isset( $params['queryCat'] ) && '' !== $params['queryCat'] ? json_decode( $params['queryCat'] ) : array();
		if ( ! is_array( $queryCat ) ) {
			return $this->invalid_param_error( 'queryCat' );
		}

		$queryNumber = isset( $params['queryNumber'] ) ? absint( $params['queryNumber'] ) : '';
		$queryType   = isset( $params['queryType'] ) ? sanitize_key( $params['queryType'] ) : '';
		$data        = wopb_function()->get_category_data( $queryCat, $queryNumber, $queryType );
		return rest_ensure_response( $data );
	}


	/**
	 * Post Data Response of REST API
	 *
	 * @since v.1.0.0
	 * @param MIXED | Pram (ARRAY), Local (BOOLEAN)
	 * @return ARRAY | Response Image Size as Array
	 */
	public function wopb_route_post_data( $prams ) {
		if ( ! $this->can_access_editor_data() ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot access this resource.', 'product-blocks' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! isset( $_REQUEST['wpnonce'] ) || ! is_string( $_REQUEST['wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['wpnonce'] ) ), 'wopb-nonce' ) ) {
			return;
		}

		$prams = $this->get_scalar_params( $prams );
		if ( is_wp_error( $prams ) ) {
			return $prams;
		}

		$data = array();
		$loop = new \WP_Query( wopb_function()->get_query( $prams ) );

		if ( $loop->have_posts() ) {
			while ( $loop->have_posts() ) {
				$loop->the_post();
				$var                   = array();
				$post_id               = get_the_ID();
				$product               = wc_get_product( $post_id );
				$user_id               = get_the_author_meta( 'ID' );
				$var['post_id']        = $post_id;
				$var['title']          = wp_kses_post( get_the_title() );
				$var['permalink']      = get_permalink();
				$var['excerpt']        = wp_kses_post( $product->get_short_description() );
				$var['time']           = get_the_date();
				$var['price_sale']     = $product->get_sale_price();
				$var['price_regular']  = $product->get_regular_price();
				$var['discount']       = ( $var['price_sale'] && (float) $var['price_regular'] > 0 ) ? round( ( $var['price_regular'] - $var['price_sale'] ) / $var['price_regular'] * 100 ) . '%' : '';
				$var['sale']           = $product->is_on_sale();
				$var['price_html']     = wp_kses( (string) $product->get_price_html(), wopb_function()->allowed_html_tags() );
				$var['stock']          = $product->get_stock_status();
				$var['featured']       = $product->is_featured();
				$var['rating_count']   = $product->get_rating_count();
				$var['rating_average'] = $product->get_average_rating();
				$var['type']           = $product->get_type();

				$time_to     = $product->get_date_on_sale_to() ? strtotime( $product->get_date_on_sale_to() ) : '';
				$var['deal'] = ( $var['price_sale'] && $time_to > current_time( 'timestamp' ) ) ? gmdate( 'Y/m/d', $time_to ) : '';

				// image
				if ( has_post_thumbnail() ) {
					$thumb_id    = get_post_thumbnail_id( $post_id );
					$image_sizes = wopb_function()->get_image_size();
					$image_src   = array();
					foreach ( $image_sizes as $key => $value ) {
						$image_src[ $key ] = esc_url( wp_get_attachment_image_src( $thumb_id, $key, false )[0] );
					}
					$var['image'] = $image_src;
				} else {
					$var['image']['full'] = esc_url( wc_placeholder_img_src( 'full' ) );
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
		return rest_ensure_response( $data );
	}

	/**
	 * Insert Post For Imported Template
	 *
	 * @since v.2.3.8
	 * @param STRING
	 * @return ARRAY | Inserted Post Url
	 */
	public function template_page_insert( $server ) {
		$post = $server->get_params();
		if ( ! isset( $post['title'], $post['blockCode'] ) || ! is_string( $post['title'] ) || '' === trim( $post['title'] ) || strlen( $post['title'] ) > 1000 || ! is_string( $post['blockCode'] ) || strlen( $post['blockCode'] ) > 2097152 ) {
			return new \WP_Error( 'wopb_invalid_template_content', __( 'Provide a valid title and block content.', 'product-blocks' ), array( 'status' => 400 ) );
		}
		$post_type = get_post_type_object( 'page' );
		if ( ! $post_type || ! current_user_can( $post_type->cap->create_posts ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot create pages.', 'product-blocks' ), array( 'status' => rest_authorization_required_code() ) );
		}
		$content  = current_user_can( 'unfiltered_html' ) ? $post['blockCode'] : wp_kses_post( $post['blockCode'] );
		$new_page = array(
			'post_title'   => sanitize_text_field( $post['title'] ),
			'post_type'    => 'page',
			'post_content' => wp_slash( $content ),
			'post_status'  => 'draft',
		);
		$post_id  = wp_insert_post( $new_page, true );
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return new \WP_Error( 'wopb_template_insert_failed', __( 'The page could not be created.', 'product-blocks' ), array( 'status' => 500 ) );
		}
		return array(
			'success' => true,
			'link'    => get_edit_post_link( $post_id ),
		);
	}

	/**
	 * Search from editor settings
	 *
	 * @since v.2.6.0
	 * @param STRING
	 * @return ARRAY | Inserted Post Url
	 */
	public function search_settings_action( $server ) {
		global $wpdb;
		$post           = $server->get_params();
		$type           = isset( $post['type'] ) ? wopb_function()->rest_sanitize_params( $post['type'] ) : '';
		$condition_type = isset( $post['condition'] ) ? wopb_function()->rest_sanitize_params( $post['condition'] ) : '';
		$term_type      = isset( $post['term'] ) ? wopb_function()->rest_sanitize_params( $post['term'] ) : '';
		switch ( $type ) {
			case 'posts':
			case 'allpost':
			case 'postExclude':
				$post_type = array( 'post' );
				if ( 'allpost' === $type ) {
					$post_type = array_keys( wopb_function()->get_post_type() );
				} elseif ( 'postExclude' === $type ) {
					$post_type = array( $condition_type );
				}
				$args = array(
					'post_type'      => $post_type,
					'post_status'    => 'publish',
					'posts_per_page' => 10,
				);
				if ( is_numeric( $term_type ) ) {
					$args['p'] = $term_type;
				} else {
					$args['s'] = $term_type;
				}

				$post_results = new \WP_Query( $args );
				$data         = array();
				if ( ! empty( $post_results ) ) {
					while ( $post_results->have_posts() ) {
						$post_results->the_post();
						$id     = get_the_ID();
						$title  = html_entity_decode( get_the_title() );
						$data[] = array(
							'value' => $id,
							'title' => ( $title ? '[ID: ' . $id . '] ' . $title : ( '[ID: ' . $id . ']' ) ),
						);
					}
					wp_reset_postdata();
				}
				return array(
					'success' => true,
					'data'    => $data,
				);
				break;

			case 'product':
			case 'allproduct':
			case 'productInclude':
			case 'productExclude':
				$post_type = array( 'product' );
				if ( 'allproduct' === $type ) {
					$post_type = array_keys( wopb_function()->get_post_type() );
				} elseif ( 'productInclude' === $type || 'productExclude' === $type ) {
					$post_type = array( $condition_type );
				}
				$args = array(
					'post_type'      => $post_type,
					'post_status'    => 'publish',
					'posts_per_page' => 10,
				);
				if ( is_numeric( $term_type ) ) {
					$args['p'] = $term_type;
				} else {
					$args['s'] = $term_type;
				}
				if ( $term_type ) {
					$args['search_key'] = $term_type;
					add_filter( 'posts_search', array( $this, 'searchProducts' ), 100, 2 );
					add_filter( 'posts_distinct', array( $this, 'searchProductsDistinct' ), 100, 2 );
					add_filter( 'posts_join', array( wopb_function(), 'custom_post_join' ), 100, 2 );
				}

				$post_results = new \WP_Query( $args );

				remove_filter( 'posts_search', array( $this, 'searchProducts' ), 100, 2 );
				remove_filter( 'posts_distinct', array( $this, 'searchProductsDistinct' ), 100, 2 );
				remove_filter( 'posts_join', array( wopb_function(), 'custom_post_join' ), 100, 2 );

				$data = array();
				if ( ! empty( $post_results ) ) {
					while ( $post_results->have_posts() ) {
						$post_results->the_post();
						$id     = get_the_ID();
						$title  = wp_strip_all_tags( html_entity_decode( get_the_title() ) );
						$data[] = array(
							'value' => $id,
							'title' => ( $title ? '[ID: ' . $id . '] ' . $title : ( '[ID: ' . $id . ']' ) ),
						);
					}
					wp_reset_postdata();
				}
				return array(
					'success' => true,
					'data'    => $data,
				);
				break;

			case 'author':
				$term = '%' . $wpdb->esc_like( $term_type ) . '%';
				// The block editor only needs names to pick an author. Matching on the email address is for
				// users who may list users, so a contributor can't probe which addresses exist.
				if ( current_user_can( 'list_users' ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- editor author search (LIMIT 10, prepared); WP_User_Query can't match ID and display name with LIKE in one query, and results must be live.
					$post_results = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT ID, display_name
							FROM $wpdb->users
							WHERE user_login LIKE %s OR ID LIKE %s OR user_nicename LIKE %s OR user_email LIKE %s OR display_name LIKE %s LIMIT 10",
							$term,
							$term,
							$term,
							$term,
							$term
						)
					);
				} else {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- editor author search (LIMIT 10, prepared); WP_User_Query can't match ID and display name with LIKE in one query, and results must be live.
					$post_results = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT ID, display_name
							FROM $wpdb->users
							WHERE user_nicename LIKE %s OR display_name LIKE %s LIMIT 10",
							$term,
							$term
						)
					);
				}
				$data = array();
				if ( ! empty( $post_results ) ) {
					foreach ( $post_results as $key => $val ) {
						$data[] = array(
							'value' => $val->ID,
							'title' => '[ID: ' . $val->ID . '] ' . $val->display_name,
						);
					}
				}
				return array(
					'success' => true,
					'data'    => $data,
				);
				break;

			case 'taxValue':
				$split = explode( '###', $condition_type );
				if ( isset( $split[1] ) && ! taxonomy_exists( $split[1] ) ) {
					/**
					 * Filters the term search results of a block whose taxonomy isn't a registered one.
					 *
					 * @since 4.6.0
					 * @param array  $data           Results, `array( 'value', 'title' )` rows; none by default.
					 * @param string $type           Search type: `taxValue`, `product_cat` or `product_tag`.
					 * @param string $condition_type The field's condition, `<post type>###<taxonomy>`.
					 * @param string $term_type      What the user typed: a name, or a term ID.
					 */
					return array(
						'success' => true,
						'data'    => apply_filters( 'wopb_rest_tax_value_data', array(), $type, $condition_type, $term_type ),
					);
				}
				$condition = isset( $split[1] ) ? array( $split[1] ) : get_object_taxonomies( $split[0] );
				$args      = array(
					'taxonomy'   => $condition,
					'fields'     => 'all',
					'orderby'    => 'id',
					'order'      => 'ASC',
					'name__like' => $term_type,
				);
				if ( is_numeric( $term_type ) ) {
					unset( $args['name__like'] );
					$args['include'] = array( $term_type );
				}

				$post_results = get_terms( $args );
				$data         = array();
				if ( ! empty( $post_results ) ) {
					foreach ( $post_results as $key => $val ) {
						$data[] = array(
							'value' => $val->slug,
							'title' => '[ID: ' . $val->term_id . '] ' . $val->name,
						);
					}
				}
				return array(
					'success' => true,
					'data'    => $data,
				);
				break;

			case 'taxExclude':
				$condition = get_object_taxonomies( $condition_type );
				$args      = array(
					'taxonomy'   => $condition,
					'fields'     => 'all',
					'orderby'    => 'id',
					'order'      => 'ASC',
					'name__like' => $term_type,
				);
				if ( is_numeric( $term_type ) ) {
					unset( $args['name__like'] );
					$args['include'] = array( $term_type );
				}
				$post_results = get_terms( $args );
				$data         = array();
				if ( ! empty( $post_results ) ) {
					foreach ( $post_results as $key => $val ) {
						$data[] = array(
							'value' => $val->taxonomy . '###' . $val->slug,
							'title' => '[ID: ' . $val->term_id . '] ' . $val->taxonomy . ': ' . $val->name,
						);
					}
				}
				return array(
					'success' => true,
					'data'    => $data,
				);
				break;

			case 'product_cat':
			case 'product_tag':
				$split = explode( '###', $condition_type );
				if ( isset( $split[1] ) && ! taxonomy_exists( $split[1] ) ) {
					/** This filter is documented in classes/REST_API.php */
					return array(
						'success' => true,
						'data'    => apply_filters( 'wopb_rest_tax_value_data', array(), $type, $condition_type, $term_type ),
					);
				}
				$condition = isset( $split[1] ) ? array( $split[1] ) : get_object_taxonomies( $split[0] );
				$args      = array(
					'taxonomy'   => $type,
					'fields'     => 'all',
					'orderby'    => 'id',
					'order'      => 'ASC',
					'name__like' => $term_type,
				);
				if ( is_numeric( $term_type ) ) {
					unset( $args['name__like'] );
					$args['include'] = array( $term_type );
				}

				$post_results = get_terms( $args );
				$data         = array();
				if ( ! empty( $post_results ) ) {
					foreach ( $post_results as $key => $val ) {
						$data[] = array(
							'value' => $val->slug,
							'title' => '[ID: ' . $val->term_id . '] ' . $val->name,
						);
					}
				}
				return array(
					'success' => true,
					'data'    => $data,
				);
				break;

			default:
				return array(
					'success' => true,
					'data'    => array(
						array(
							'value' => '',
							'title' => '- Select -',
						),
					),
				);
				break;
		}
	}

	/**
	 * Product Search Query
	 *
	 * @param $search
	 * @param $query
	 * @return STRING
	 * @since v.3.1.0
	 */
	public function searchProducts( $search, $query ) {
		global $wpdb;

		if ( ! empty( $query->query_vars['s'] ) ) {
			$search_term = $query->query_vars['s'];
			// Properly escape the search term for LIKE queries.
			$search_term_like = '%' . $wpdb->esc_like( $search_term ) . '%';

			// Use parameterized query with $wpdb->prepare().
			$search = $wpdb->prepare(
				" AND (
                {$wpdb->prefix}posts.post_title LIKE %s
                OR {$wpdb->prefix}posts.post_content LIKE %s
                OR {$wpdb->prefix}posts.post_excerpt LIKE %s
                OR (post_meta.meta_key='_sku' AND post_meta.meta_value LIKE %s)
            ) ",
				$search_term_like,
				$search_term_like,
				$search_term_like,
				$search_term_like
			);
		}

		return $search;
	}

	/**
	 * Product Distinct Query
	 *
	 * @param $where
	 * @return STRING
	 * @since v.3.1.0
	 */
	public function searchProductsDistinct( $where ) {
		return 'DISTINCT';
	}
}
