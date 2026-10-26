<?php
/**
 * REST API Action.
 *
 * @package WOPB\Dashboard
 * @since 3.0.0
 */
namespace WOPB;

defined( 'ABSPATH' ) || exit;

/**
 * Dashboard class.
 */
class Dashboard {

	/**
	 * Setup class.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'wopb_register_route' ) );
	}

	/**
	 * REST API Action
	 *
	 * @return NULL
	 */
	public function wopb_register_route() {
		register_rest_route(
			'wopb/v2',
			'/dashborad/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'get_dashboard_callback' ),
					'permission_callback' => function () {
						return apply_filters( 'wopb_rest_api_capability', current_user_can( 'manage_options' ) );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'wopb/v2',
			'/get_all_settings/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'get_all_settings' ),
					'permission_callback' => function () {
						return apply_filters( 'wopb_rest_api_capability', current_user_can( 'manage_options' ) );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'wopb/v2',
			'/addon_block_action/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'addon_block_action' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'wopb/v2',
			'/save_plugin_settings/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'save_plugin_settings' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'wopb/v2',
			'/premade_wishlist_save/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'premade_wishlist_save' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => array(),
				),
			)
		);
	}

	/**
	 * Save and get premade_wishlist_save
	 *
	 * @since 3.0.0
	 * @param STRING
	 * @return ARRAY | Inserted Post Url
	 */
	public function premade_wishlist_save( $server ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot manage shared template preferences.', 'product-blocks' ), array( 'status' => rest_authorization_required_code() ) );
		}
		$post        = $server->get_params();
		if ( ! isset( $post['id'], $post['action'], $post['type'] ) || ! ( is_string( $post['id'] ) || is_int( $post['id'] ) ) || ! is_string( $post['action'] ) || ! in_array( $post['action'], array( '', 'add', 'remove' ), true ) || ! is_string( $post['type'] ) || ! in_array( $post['type'], array( '', 'fetchData' ), true ) || ( 'fetchData' !== $post['type'] && ! preg_match( '/^[a-zA-Z0-9_-]{1,100}$/D', (string) $post['id'] ) ) ) {
			return new \WP_Error( 'wopb_invalid_wishlist', __( 'Invalid template preference request.', 'product-blocks' ), array( 'status' => 400 ) );
		}
		$id          = sanitize_text_field( $post['id'] );
		$action      = sanitize_text_field( $post['action'] );
		$wishListArr = get_option( 'wopb_premade_wishlist', array() );
		$wishListArr = is_array( $wishListArr ) ? $wishListArr : array();

		if ( $id && sanitize_text_field( $post['type'] ) !== 'fetchData' ) {
			if ( 'remove' === $action ) {
				$index = array_search( $id, $wishListArr );
				if ( false !== $index ) {
					unset( $wishListArr[ $index ] );
				}
			} elseif ( ! in_array( $id, $wishListArr ) ) {
					array_push( $wishListArr, $id );
			}
			update_option( 'wopb_premade_wishlist', $wishListArr );
		}
		return rest_ensure_response(
			array(
				'success'     => true,
				'message'     => $action === 'remove' ? __( 'Item has been removed from wishlist.', 'product-blocks' ) : __( 'Item added to wishlist.', 'product-blocks' ),
				'wishListArr' => wp_json_encode( $wishListArr ),
			)
		);
	}

	/**
	 * Save Settings of Option Panel
	 *
	 * @since 3.0.0
	 * @return NULL
	 */
	public function save_plugin_settings( $server ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot change plugin settings.', 'product-blocks' ), array( 'status' => rest_authorization_required_code() ) );
		}
		$post = $server->get_params();
		if ( ! isset( $post['type'], $post['settings'] ) || ! is_string( $post['type'] ) || ! in_array( $post['type'], array( 'add', 'delete' ), true ) || ! wopb_function()->is_valid_settings_input( $post['settings'] ) || ( array_key_exists( 'key', $post ) && ( ! is_string( $post['key'] ) || ( '' !== $post['key'] && ! preg_match( '/^[a-z0-9_-]{1,100}$/D', $post['key'] ) ) ) ) || ( 'delete' === $post['type'] && empty( $post['key'] ) ) ) {
			return new \WP_Error( 'wopb_invalid_settings', __( 'Invalid plugin settings request.', 'product-blocks' ), array( 'status' => 400 ) );
		}
		// General settings omit the add-on key; keep that existing request valid.
		$key  = isset( $post['key'] ) ? sanitize_key( $post['key'] ) : '';
		$type = sanitize_text_field( $post['type'] );
		$data = wopb_function()->recursive_sanitize_text_field( $post['settings'] );

		if ( count( $data ) > 0 ) {
			foreach ( $data as $k => $val ) {
				wopb_function()->set_setting( $k, $val );
			}
		}

		// Hook for Settings Save Data
		do_action( 'wopb_save_settings', $key );
		if ( 'delete' === $type ) {
			wopb_function()->update_css( $key, 'delete' );
		}

		return rest_ensure_response(
			array(
				'success'     => true,
				'message'     => __( 'You have successfully saved the settings data.', 'product-blocks' ),
				'wishListArr' => wp_json_encode( $data ),
				'settings'    => wopb_function()->get_setting(),
			)
		);
	}

	/**
	 * Save addon / blocks on/off data
	 *
	 * @since 3.0.0
	 * @param STRING
	 * @return array | Inserted Post Url
	 */
	public function addon_block_action( $server ) {
		$post        = $server->get_params();
		$addon_name  = sanitize_text_field( $post['key'] );
		$addon_value = sanitize_text_field( $post['value'] );

		if ( $addon_name ) {
			wopb_function()->set_setting( $addon_name, $addon_value );
		}

		return array(
			'success' => true,
			'message' => ( 'true' === $addon_value || 'false' === $addon_value ) ? ( 'true' === $addon_value ? __( 'The addon has been enabled.', 'product-blocks' ) : __( 'The addon has been disabled.', 'product-blocks' ) ) : ( 'yes' === $addon_value ? __( 'The block has been enabled.', 'product-blocks' ) : __( 'The block has been disabled.', 'product-blocks' ) ),
		);
	}

	/**
	 * All Settings
	 *
	 * @since 3.0.0
	 * @return ARRAy
	 */
	public function get_all_settings() {
		return rest_ensure_response(
			array(
				'success'  => true,
				'settings' => wopb_function()->get_setting(),
			)
		);
	}

	/**
	 * Post types the dashboard lists and bulk-edits.
	 *
	 * @since 4.6.0
	 * @return string[]
	 */
	private function get_dashboard_post_types() {
		/**
		 * Filters the post types the dashboard can list, publish, draft and delete.
		 *
		 * @since 4.6.0
		 * @param string[] $post_types Post type names.
		 */
		$post_types = apply_filters( 'wopb_dashboard_post_types', array( 'wopb_templates', 'wopb_custom_font', 'wopb-size-chart' ) );
		return is_array( $post_types ) ? $post_types : array();
	}

	/**
	 * The requested post type when the dashboard may list it, or an empty string.
	 *
	 * @since 4.6.0
	 * @param mixed $post_type Requested post type.
	 * @return string
	 */
	private function get_requested_post_type( $post_type ) {
		return is_string( $post_type ) && in_array( $post_type, $this->get_dashboard_post_types(), true ) ? $post_type : '';
	}

	/**
	 * Whether the current user may run a bulk action on a post.
	 *
	 * The post has to be one of the dashboard's post types, and the user needs the capability for
	 * that exact post.
	 *
	 * @since 4.6.0
	 * @param mixed  $post_id    Post ID from the request.
	 * @param string $capability Meta capability, `edit_post` or `delete_post`.
	 * @return bool
	 */
	private function can_bulk_edit_post( $post_id, $capability ) {
		$post_id = absint( $post_id );
		return $post_id && in_array( get_post_type( $post_id ), $this->get_dashboard_post_types(), true ) && current_user_can( $capability, $post_id );
	}

	/**
	 * Dashboard & Saved Template & Custom Font Actions
	 *
	 * @since 3.0.0
	 * @param STRING
	 * @return ARRAY | Inserted Post Url
	 */
	public function get_dashboard_callback( $server ) {
		$post = $server->get_params();
		if ( isset( $post['type'] ) ) {
			$type = sanitize_text_field( $post['type'] );

			switch ( $type ) {

				case 'saved_templates':
					$post_per_page = 10;
					$data          = array();
					$list_type     = $this->get_requested_post_type( isset( $post['pType'] ) ? $post['pType'] : '' );
					if ( ! $list_type ) {
						return new \WP_Error( 'wopb_invalid_post_type', __( 'Invalid post type.', 'product-blocks' ), array( 'status' => 400 ) );
					}
					$args          = array(
						'post_type'      => $list_type,
						'post_status'    => array( 'publish', 'draft' ),
						'posts_per_page' => $post_per_page,
						'paged'          => $post['pages'],
					);

					if ( isset( $post['search'] ) ) {
						$args['paged'] = 1;
						$args['s']     = $post['search'];
					}

					$the_query = new \WP_Query( $args );
					if ( $the_query->have_posts() ) {
						while ( $the_query->have_posts() ) {
							$the_query->the_post();
							$final = array(
								'id'     => get_the_ID(),
								'title'  => get_the_title(),
								'date'   => get_the_modified_date( 'Y/m/d h:i a' ),
								'status' => get_post_status(),
								'edit'   => get_edit_post_link(),
							);

							if ( 'wopb_custom_font' === $list_type ) {
								$final    = array_merge(
									$final,
									array(
										'woff'  => false,
										'woff2' => false,
										'ttf'   => false,
										'svg'   => false,
										'eot'   => false,
									)
								);
								$settings = get_post_meta( get_the_ID(), '__font_settings', true );
								foreach ( $settings as $key => $value ) {
									if ( $value['ttf'] ) {
										$final['ttf'] = true; }
									if ( $value['svg'] ) {
										$final['svg'] = true; }
									if ( $value['eot'] ) {
										$final['eot'] = true; }
									if ( $value['woff'] ) {
										$final['woff'] = true; }
									if ( $value['woff2'] ) {
										$final['woff2'] = true; }
								}
								$final['font_settings'] = $settings;
							}
							$data[] = $final;
						}
					}
					wp_reset_postdata();
					return array(
						'success' => true,
						'data'    => $data,
						'new'     => ( 'wopb_custom_font' === $list_type ? admin_url( 'post-new.php?post_type=wopb_custom_font' ) : admin_url( 'post-new.php?post_type=wopb_templates' ) ),
						'found'   => $the_query->found_posts,
						'pages'   => $the_query->max_num_pages,
					);
				break;

				case 'action_draft':
				case 'action_publish':
					if ( isset( $post['ids'] ) && is_array( $post['ids'] ) ) {
						foreach ( $post['ids'] as $id ) {
							if ( ! $this->can_bulk_edit_post( $id, 'edit_post' ) ) {
								continue;
							}
							wp_update_post(
								array(
									'ID'          => absint( $id ),
									'post_status' => str_replace( 'action_', '', $type ),
								)
							);
						}
						return array(
							'success' => true,
							'message' => __( 'Status changed for selected items.', 'product-blocks' ),
						);
					}
					break;

				case 'action_delete':
					if ( isset( $post['ids'] ) && is_array( $post['ids'] ) ) {
						foreach ( $post['ids'] as $id ) {
							if ( $this->can_bulk_edit_post( $id, 'delete_post' ) ) {
								wp_delete_post( absint( $id ), true );
							}
						}
					}
					return array(
						'success' => true,
						'message' => __( 'The selected item is deleted.', 'product-blocks' ),
					);
				break;
				case 'wopb-size-chart':
					$post_per_page = 10;
					$data          = array();
					$list_type     = $this->get_requested_post_type( isset( $post['pType'] ) ? $post['pType'] : '' );
					if ( ! $list_type ) {
						return new \WP_Error( 'wopb_invalid_post_type', __( 'Invalid post type.', 'product-blocks' ), array( 'status' => 400 ) );
					}
					$args          = array(
						'post_type'      => $list_type,
						'post_status'    => array( 'publish', 'draft' ),
						'posts_per_page' => $post_per_page,
						'paged'          => $post['pages'],
					);

					if ( isset( $post['search'] ) ) {
						$args['paged'] = 1;
						$args['s']     = $post['search'];
					}

					$the_query = new \WP_Query( $args );
					if ( $the_query->have_posts() ) {
						while ( $the_query->have_posts() ) {
							$the_query->the_post();
							$post_id      = get_the_ID();
							$category_ids = get_post_meta( $post_id, 'wopb_sc_category', true );

							$category = 'N/A';
							if ( ! empty( $category_ids ) ) {
								$categories = get_terms(
									array(
										'taxonomy'   => 'product_cat',
										'include'    => get_post_meta( $post_id, 'wopb_sc_category', true ),
										'hide_empty' => false,
									)
								);
								if ( ! empty( $categories ) ) {
									$category = implode( ', ', wp_list_pluck( $categories, 'name' ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
							}

							$include_ids      = get_post_meta( $post_id, 'wopb_sc_include_products', true );
							$include_products = 'N/A';
							if ( ! empty( $include_ids ) ) {
								$include_products = $this->product_names_by_ids( $include_ids ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}

							$exclude_ids      = get_post_meta( $post_id, 'wopb_sc_exclude_products', true );
							$exclude_products = 'N/A';
							if ( ! empty( $exclude_ids ) ) {
								$exclude_products = $this->product_names_by_ids( $exclude_ids ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							$final  = array(
								'id'               => $post_id,
								'title'            => get_the_title(),
								'categories'       => $category,
								'include_products' => $include_products,
								'exclude_products' => $exclude_products,
								'date'             => get_the_modified_date( 'Y/m/d h:i a' ),
								'status'           => get_post_status(),
								'edit'             => get_edit_post_link(),
							);
							$data[] = $final;
						}
					}
					wp_reset_postdata();
					return array(
						'success' => true,
						'data'    => $data,
						'new'     => admin_url( 'post-new.php?post_type=wopb-size-chart' ),
						'found'   => $the_query->found_posts,
						'pages'   => $the_query->max_num_pages,
					);
					break;

				default:
					// code...
					break;
			}
		}
	}

	/**
	 * Get Products by Product IDS
	 *
	 * @param array $product_ids
	 * @return string|void
	 * @since v.4.0.0
	 */
	public function product_names_by_ids( array $product_ids ) {
		$products      = wc_get_products(
			array(
				'limit'   => -1,
				'include' => $product_ids,
			)
		);
		$product_names = array();

		foreach ( $products as $product ) {
			$product_names[] = $product->get_name();
		}

		if ( ! empty( $product_names ) ) {
			return implode( ', ', $product_names );
		}
	}
}
