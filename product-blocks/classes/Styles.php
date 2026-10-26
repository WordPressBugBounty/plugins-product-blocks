<?php
/**
 * Styles Add and Style REST API Action.
 *
 * @package WOPB\Styles
 * @since v.1.0.0
 */

namespace WOPB;

defined( 'ABSPATH' ) || exit;

/**
 * Styles class.
 */
class Styles {

	/**
	 * Setup class.
	 *
	 * @since v.1.0.0
	 */

	private $changed_wp_block = '';
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'save_block_css_callback' ) );

		add_action( 'after_delete_post', array( $this, 'wopb_delete_post_callback' ), 10, 2 ); // Delete Plugin Data CSS file delete Action
		add_action( 'enqueue_block_editor_assets', array( $this, 'productx_global_css' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_the_wowstore_block_css' ) );
		add_action( 'wopb_enqueue_wowstore_block_css', array( $this, 'wopb_enqueue_wowstore_block_css_callback' ), 10, 1 ); // action to enqueue the block css
		add_filter( 'render_block', array( $this, 'render_block_callback' ), 10, 2 ); // render block to enqueue corresponding css
	}

	/**
	 * Enqueue The Block Style based on block( wp_block, fse_template, wp_template, wp_template_part )
	 *
	 * @param $block_content
	 * @param $block
	 * @return NULL
	 * @since v.4.0.4
	 */
	public function render_block_callback( $block_content, $block ) {
		if ( isset( $block['blockName'] ) &&
			strpos( $block['blockName'], 'product-blocks/' ) === 0
			&& ! empty( $block['attrs']['currentPostId'] )
		) {
			do_action(
				'wopb_enqueue_wowstore_block_css',
				array(
					'post_id' => $block['attrs']['currentPostId'],
					'css'     => '',
				)
			);
		}
		return $block_content;
	}

	/**
	 * Enqueue The Block Style
	 *
	 * @since v.4.0.4
	 * @return NULL
	 */
	public function enqueue_the_wowstore_block_css() {
		$this->productx_global_css( 'front' );
		wopb_function()->front_common_script();
		if ( apply_filters('productx_common_script', false) || isset( $_GET['et_fb'] ) ) { //phpcs:ignore
			wopb_function()->register_scripts_common();
		}
		if ( is_admin() ) {
			return;
		}
		$css     = '';
		$post_id = wopb_function()->get_ID();
		if ( isset($_GET['preview_id']) && isset($_GET['preview_nonce']) ) {	// phpcs:ignore @codingStandardsIgnoreLine
			$css = get_transient( '_wopb_preview_' . $post_id, true );
			if ( ! $css ) {
				$css = get_post_meta( $post_id, '_wopb_css', true );
			}
		}
		do_action(
			'wopb_enqueue_wowstore_block_css',
			array(
				'post_id' => $post_id,
				'css'     => $css,
			)
		);
	}

	/**
	 * Enqueue The Block Style
	 *
	 * @param $data
	 * @return NULL
	 * @since v.4.0.4
	 */
	public function wopb_enqueue_wowstore_block_css_callback( $data ) {
		$post_id = isset( $data['post_id'] ) ? $data['post_id'] : '';
		$css     = isset( $data['css'] ) ? safecss_filter_attr( $data['css'] ) : '';
		if ( wp_style_is( "wopb-post-{$post_id}", 'enqueued' ) ) {
			return;
		}

		if ( $post_id ) {
			if ( '' == $css ) {
				global $wp_filesystem;
				if ( ! $wp_filesystem ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				WP_Filesystem();
				$upload_dir_url = wp_upload_dir();
				$_path          = trailingslashit( $upload_dir_url['basedir'] ) . "product-blocks/wopb-css-{$post_id}.css";
				$css            = '';
				if ( file_exists( $_path ) ) {
					$css = $wp_filesystem->get_contents( $_path );
				} elseif ( 'wopb-widget' === $post_id ) {
					$css = get_option( $post_id, true );
				} else {
					$css = get_post_meta( $post_id, '_wopb_css', true );
				}
			}
			if ( $css ) {
				wp_register_style( "wopb-post-{$post_id}", false, array(), WOPB_VER );
				wp_enqueue_style( "wopb-post-{$post_id}" );
				wp_add_inline_style( "wopb-post-{$post_id}", wp_strip_all_tags( $css ) );
				wopb_function()->register_scripts_common();
			}
		}

		$post_type = get_post_type( $post_id );
		if ( 'wopb_builder' === $post_type && class_exists( 'UAGB_Helper' ) ) {
			do_action( 'wopb_enqueue_plugin_css', $post_id );
		}
	}

	/**
	 * Delete Plugin Data CSS file delete Action
	 *
	 * @param $post_id
	 * @param $post
	 * @return STRING
	 * @since v.4.0.4
	 */
	public function wopb_delete_post_callback( $post_id, $post ) {
		$upload          = wp_upload_dir();
		$upload_dir      = $upload['basedir'];
		$upload_dir_path = $upload_dir . "/product-blocks/wopb-css-{$post_id}.css";
		if ( file_exists( $upload_dir_path ) ) {
			wp_delete_file( $upload_dir_path );
		}
	}

	/**
	 * REST API Action
	 *
	 * @since v.1.0.0
	 * @return NULL
	 */
	public function save_block_css_callback() {
		register_rest_route(
			'wopb/v1',
			'/save_block_css/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'save_block_content_css' ),
					'permission_callback' => array( $this, 'can_save_block_css' ),
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'wopb/v1',
			'/get_other_post_content/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'get_other_post_content_callback' ),
					'permission_callback' => array( $this, 'can_get_post_content' ),
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			'wopb/v1',
			'/action_option/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'global_settings_action' ),
					'permission_callback' => array( $this, 'can_access_global_settings' ),
					'args'                => array(),
				),
			)
		);
	}

	/**
	 * Authorize CSS changes for the requested post or shared widget styles.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return bool
	 */
	public function can_save_block_css( $request ) {
		$post_id = $request->get_param( 'post_id' );
		if ( 'wopb-widget' === $post_id ) {
			return current_user_can( 'manage_options' );
		}
		if ( ! is_scalar( $post_id ) || ! ctype_digit( (string) $post_id ) || ! get_post( (int) $post_id ) ) {
			return false;
		}
		if ( in_array( get_post_type( (int) $post_id ), array( 'wp_template', 'wp_template_part' ), true ) && ! current_user_can( 'edit_theme_options' ) ) {
			return false;
		}
		return current_user_can( 'edit_post', (int) $post_id );
	}

	/**
	 * Authorize access to editable post content.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return bool
	 */
	public function can_get_post_content( $request ) {
		$post_id = $request->get_param( 'postId' );
		return is_scalar( $post_id ) && ctype_digit( (string) $post_id ) && get_post( (int) $post_id ) && current_user_can( 'edit_post', (int) $post_id );
	}

	/**
	 * Allow editors to read defaults and administrators to change global settings.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return bool
	 */
	public function can_access_global_settings( $request ) {
		$type = $request->get_param( 'type' );
		if ( ! is_string( $type ) ) {
			return false;
		}
		$type = wopb_function()->rest_sanitize_params( $type );
		return current_user_can( in_array( $type, array( 'set', 'regenerate_font' ), true ) ? 'manage_options' : 'edit_posts' );
	}

	/**
	 * Disable Google Font Callback
	 *
	 * * @since v.2.5.5
	 *
	 * @return STRING
	 */
	public function disable_google_font_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$upload_dir_url = wp_upload_dir();
		$dir            = trailingslashit( $upload_dir_url['basedir'] ) . 'product-blocks/';
		$css_dir        = glob( $dir . '*.css' );
		// Custom Font
		$custom_fonts = array();
		if ( wopb_function()->get_setting( 'wopb_custom_font' ) === 'true' ) {
			$args  = array(
				'post_type'   => 'wopb_custom_font',
				'post_status' => 'publish',
				'numberposts' => -1,
				'order'       => 'ASC',
			);
			$posts = get_posts( $args );
			if ( $posts ) {
				foreach ( $posts as $post ) {
					if ( ! empty( $post->post_title ) ) {
						$custom_fonts[] = $post->post_title;
					}
				}
			}
		}
		wp_reset_postdata();
		$custom_fonts = implode( '|', $custom_fonts );
		// system font
		$exclude_typo = array( 'Arial', 'Tahoma', 'Verdana', 'Helvetica', 'Times New Roman', 'Trebuchet MS', 'Georgia' );

		// Custom Font Exclude
		$fonts = get_posts(
			array(
				'post_type'   => 'wopb_custom_font',
				'post_status' => 'publish',
				'numberposts' => 10,
			)
		);
		if ( count( $fonts ) > 0 ) {
			foreach ( $fonts as $font ) {
				$exclude_typo[] = $font->post_title;
			}
		}

		$exclude_typo = implode( '|', $exclude_typo );

		if ( count( $css_dir ) > 0 ) {
			foreach ( $css_dir as $key => $value ) {
				$css        = $wp_filesystem->get_contents( $value );
				$filter_css = preg_replace( '/(@import)[\w\s:\/?=,;.\'()+]*;/m', '', $css ); // Remove Import Font
				$final_css  = preg_replace( '/(font-family:)((?!' . $custom_fonts . $exclude_typo . ')[\w\s:,\\\'-])*;/mi', '', $filter_css ); // Font Replace Except Default Font
				$wp_filesystem->put_contents( $value, $final_css ); // Update CSS File
			}
		}

		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE `meta_key`=%s", '_wopb_css' ) ); //phpcs:ignore
		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $value ) {
				$filter_css = preg_replace( '/(@import)[\w\s:\/?=,;.\'()+]*;/m', '', $value->meta_value ); // Remove Import Font
				$final_css  = preg_replace( '/(font-family:)((?!' . $custom_fonts . $exclude_typo . ')[\w\s:,\\\'-])*;/mi', '', $filter_css ); // Font Replace Except Default Font
				update_post_meta( $value->post_id, '_wopb_css', $final_css );
			}
		}

		return array(
			'success' => true,
			'data'    => __( 'CSS Updated!', 'product-blocks' ),
		);
	}

	/**
	 * Get and Set WowStore Global Settings
	 *
	 * @since v.2.4.24
	 * @param OBJECT | Request Param of the REST API
	 * @return ARRAY | Array of the Custom Message
	 */
	public function global_settings_action( $server ) {
		$post = $server->get_params();
		if ( ! isset( $post['type'] ) || ! is_string( $post['type'] ) || ! in_array( $post['type'], array( 'get', 'set', 'regenerate_font' ), true ) ) {
			return new \WP_Error( 'wopb_invalid_style_action', __( 'Invalid global style action.', 'product-blocks' ), array( 'status' => 400 ) );
		}
		if ( ! $this->can_access_global_settings( $server ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot change global styles.', 'product-blocks' ), array( 'status' => rest_authorization_required_code() ) );
		}
		$post = $server->get_params();
		if ( isset( $post['type'] ) ) {
			$type = wopb_function()->rest_sanitize_params( $post['type'] );
			if ( 'set' === $type ) {
				$data = $this->sanitize_global_settings( isset( $post['data'] ) ? $post['data'] : null );
				if ( is_wp_error( $data ) ) {
					return $data;
				}
				update_option( 'productx_global', $data );
				return array( 'success' => true );
			} elseif ( 'regenerate_font' === $type ) {
				return $this->disable_google_font_callback();
			} else {
				return array(
					'success' => true,
					'data'    => get_option( 'productx_global', array() ),
				);
			}
		} else {
			return array( 'success' => false );
		}
	}

	/**
	 * Accept the palette, editor width and breakpoint fields used by the editor.
	 *
	 * @param mixed $data Global settings input.
	 * @return array|\WP_Error
	 */
	private function sanitize_global_settings( $data ) {
		$error = new \WP_Error( 'wopb_invalid_global_styles', __( 'Invalid global style settings.', 'product-blocks' ), array( 'status' => 400 ) );
		if ( ! is_array( $data ) || count( $data ) > 14 ) {
			return $error;
		}
		$dimensions = array(
			'editorWidth'  => array( 500, 3000 ),
			'breakpointSm' => array( 600, 1200 ),
			'breakpointXs' => array( 300, 1000 ),
		);
		foreach ( $data as $key => $value ) {
			if ( preg_match( '/^presetColor[1-9]$/D', (string) $key ) ) {
				if ( ! is_string( $value ) || strlen( $value ) > 100 ) {
					return $error;
				}
				$value = trim( $value );
				if ( sanitize_hex_color( $value ) || 'transparent' === $value ) {
					$data[ $key ] = sanitize_text_field( $value );
				} elseif ( preg_match( '/^rgba?\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})(?:\s*,\s*(0(?:\.[0-9]+)?|1(?:\.0+)?|\.[0-9]+))?\s*\)$/D', $value, $matches ) && (int) $matches[1] <= 255 && (int) $matches[2] <= 255 && (int) $matches[3] <= 255 && ( ( 0 === strpos( $value, 'rgba(' ) ) === isset( $matches[4] ) ) ) {
					$data[ $key ] = sanitize_text_field( $value );
				} else {
					return $error;
				}
			} elseif ( isset( $dimensions[ $key ] ) ) {
				if ( ! ( is_int( $value ) || is_string( $value ) ) || ! ctype_digit( (string) $value ) || (int) $value < $dimensions[ $key ][0] || (int) $value > $dimensions[ $key ][1] ) {
					return $error;
				}
				$data[ $key ] = (int) $value;
			} elseif ( 'editorType' === $key ) {
				if ( ! is_string( $value ) || ! in_array( $value, array( 'default', 'fullscreen', 'custom' ), true ) ) {
					return $error;
				}
			} elseif ( 'productxColorOnly' !== $key || ! is_bool( $value ) ) {
				return $error;
			}
		}
		return $data;
	}


	/**
	 * Save Import CSS in the top of the File
	 *
	 * @since v.1.0.0
	 * @param OBJECT | Request Param of the REST API
	 * @return ARRAY/Exception | Array of the Custom Message
	 */
	public function save_block_content_css( $request ) {

		$params     = $request->get_params();
		$boolean_values = array( true, false, 0, 1, '0', '1', 'true', 'false' );
		if ( ! isset( $params['post_id'], $params['has_block'] ) || ! ( is_int( $params['post_id'] ) || is_string( $params['post_id'] ) ) || ! in_array( $params['has_block'], $boolean_values, true ) || ( array_key_exists( 'preview', $params ) && ! in_array( $params['preview'], $boolean_values, true ) ) || ( rest_sanitize_boolean( $params['has_block'] ) && ! isset( $params['block_css'] ) ) || ( array_key_exists( 'block_css', $params ) && ( ! is_string( $params['block_css'] ) || strlen( $params['block_css'] ) > 1048576 || false !== strpos( $params['block_css'], "\0" ) ) ) ) {
			return new \WP_Error( 'wopb_invalid_css', __( 'Provide valid block CSS and save options.', 'product-blocks' ), array( 'status' => 400 ) );
		}
		if ( isset( $params['block_css'] ) ) {
			$params['block_css'] = wp_strip_all_tags( $params['block_css'] );
		}
		$post_id    = isset( $params['post_id'] ) ? sanitize_text_field( $params['post_id'] ) : '';
		$has_block  = isset( $params['has_block'] ) ? rest_sanitize_boolean( $params['has_block'] ) : '';
		$is_preview = isset( $params['preview'] ) ? rest_sanitize_boolean( $params['preview'] ) : '';
		$is_widget  = 'wopb-widget' === $post_id;

		if ( ! $this->can_save_block_css( $request ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot edit these styles.', 'product-blocks' ), array( 'status' => rest_authorization_required_code() ) );
		}
		try {
			$upload_dir_url = wp_upload_dir();
			$dir            = trailingslashit( $upload_dir_url['basedir'] ) . 'product-blocks/';
			if ( $has_block ) {
				$wopb_block_css = $this->set_top_css( $params['block_css'] );
				// Preview Check
				if ( $is_preview ) {
					set_transient( '_wopb_preview_' . $post_id, $wopb_block_css, 3600 );
					return array(
						'success' => true,
						'preview' => true,
					);
				}

				global $wp_filesystem;
				if ( ! $wp_filesystem ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				if ( $is_widget ) {
					update_option( $post_id, $params['block_css'] );
				} else {
					$post_id = (int) $post_id;
					update_post_meta( $post_id, '_wopb_active', 'yes' );
					update_post_meta( $post_id, '_wopb_css', $wopb_block_css );
				}
				wopb_function()->set_setting( 'save_version', wp_rand( 1, 1000 ) );
				$filename = "wopb-css-{$post_id}.css";

				WP_Filesystem( false, $upload_dir_url['basedir'], true );
				if ( ! $wp_filesystem->is_dir( $dir ) ) {
					$wp_filesystem->mkdir( $dir );
				}
				if ( ! $wp_filesystem->put_contents( $dir . $filename, $wopb_block_css ) ) {
					throw new \Exception(__('CSS can not be saved due to permission!!!', 'product-blocks')); //phpcs:ignore
				}
				return array(
					'success' => true,
					'message' => __( 'WowStore css file has been updated.', 'product-blocks' ),
				);

			} else {
				if ( $is_widget ) {
					update_option( $post_id, '' );
				} else {
					$post_id = (int) $post_id;
					delete_post_meta( $post_id, '_wopb_active' );
					delete_post_meta( $post_id, '_wopb_css' );
				}
				$filename = "wopb-css-{$post_id}.css";
				if ( file_exists( $dir . $filename ) ) {
					wp_delete_file( $dir . $filename );
				}
				return array(
					'success' => true,
					'message' => __( 'Data Delete Done', 'product-blocks' ),
				);
			}
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Get Post Content for other Posts while performing css save
	 *
	 * @since v.1.0.0
	 * @param OBJECT | Request Param of the REST API
	 * @return ARRAY/Exception | Array of the Custom Message
	 */
	public function get_other_post_content_callback( $server ) {
		$post        = $server->get_params();
		$post_id     = isset( $post['postId'] ) ? sanitize_text_field( $post['postId'] ) : '';
		$c_post_type = get_post_type( $post_id );
		if ( $this->can_get_post_content( $server ) ) {
			if ( 'wp_block' === $c_post_type ) {
				$this->handle_wpblock_current_id( $post_id );
			}
			return array(
				'success' => true,
				'data'    => get_post( $post_id )->post_content,
				'message' => __( 'Data retrive done', 'product-blocks' ),
			);
		} else {
			return array(
				'success' => false,
				'message' => __( 'Data not found!!', 'product-blocks' ),
			);
		}
	}
	/**
	 * Handle WP Block postid
	 *
	 * @since v.4.0.4
	 * @param OBJECT | Request Param of the REST API
	 * @return ARRAY/Exception | Array of the Custom Message
	 */
	public function handle_wpblock_current_id( $post_id ) {
		$this->changed_wp_block = '';
		$post                   = get_post( $post_id );
		$post_content           = $post->post_content;

		// Parse the blocks
		$blocks         = parse_blocks( $post_content );
		$updated_blocks = $this->update_block_attributes_func( $blocks, $post_id );
		if ( $this->changed_wp_block ) {
			wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => serialize_blocks( $updated_blocks ),
				)
			);
		}
	}
	/**
	 * Handle WP Block postid save
	 *
	 * @since v.4.0.4
	 * @param OBJECT | Request Param of the REST API
	 * @return ARRAY/Exception | Array of the Custom Message
	 */
	public function update_block_attributes_func( $blocks, $post_id ) {
		foreach ( $blocks as &$block ) {
			if ( strpos( $block['blockName'], 'product-blocks/' ) > -1 && isset( $block['attrs']['currentPostId'] ) && $post_id != $block['attrs']['currentPostId'] ) {
				$this->changed_wp_block = true;
				$block['attrs']         = array_merge( $block['attrs'], array( 'currentPostId' => $post_id ) );
			}
			// Recursively update inner blocks
			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = $this->update_block_attributes_func( $block['innerBlocks'], $post_id );
			}
		}
		return $blocks;
	}


	/**
	 * Save Import CSS in the top of the File
	 *
	 * @since v.1.0.0
	 * @param STRING | CSS (STRING)
	 * @return STRING | Generated CSS
	 */
	public function set_top_css( $get_css = '' ) {
		$disable_google_font = wopb_function()->get_setting( 'disable_google_font' );
		if ( 'yes' !== $disable_google_font ) {
			$css_url     = "@import url('https://fonts.googleapis.com/css?family=";
			$font_exists = substr_count( $get_css, $css_url );
			if ( $font_exists ) {
				$pattern = sprintf( '/%s(.+?)%s/ims', preg_quote( $css_url, '/' ), preg_quote( "');", '/' ) );
				if ( preg_match_all( $pattern, $get_css, $matches ) ) {
					$fonts   = $matches[0];
					$get_css = str_replace( $fonts, '', $get_css );
					if ( preg_match_all( '/font-weight[ ]?:[ ]?[\d]{3}[ ]?;/', $get_css, $matche_weight ) ) {
						$weight = array_map(
							function ( $val ) {
								$process = trim( str_replace( array( 'font-weight', ':', ';' ), '', $val ) );
								if ( is_numeric( $process ) ) {
										return $process;
								}
							},
							$matche_weight[0]
						);
						foreach ( $fonts as $key => $val ) {
							$fonts[ $key ] = str_replace( "');", '', $val ) . ':' . implode( ',', $weight ) . "');";
						}
					}
					$fonts   = array_unique( $fonts );
					$get_css = implode( '', $fonts ) . $get_css;
				}
			}
		}
		return $get_css;
	}

	/**
	 * Set Global Color Codes
	 *
	 * @since v.2.3.7
	 * @return NULL
	 */
	public function productx_global_css( $src = '' ) {
		// Preset CSS
		$global     = get_option( 'productx_global', array() );
		$custom_css = ':root {
			--productx-color1: ' . ( isset( $global['presetColor1'] ) ? esc_attr( $global['presetColor1'] ) : '#037fff' ) . ';
			--productx-color2: ' . ( isset( $global['presetColor2'] ) ? esc_attr( $global['presetColor2'] ) : '#026fe0' ) . ';
			--productx-color3: ' . ( isset( $global['presetColor3'] ) ? esc_attr( $global['presetColor3'] ) : '#071323' ) . ';
			--productx-color4: ' . ( isset( $global['presetColor4'] ) ? esc_attr( $global['presetColor4'] ) : '#132133' ) . ';
			--productx-color5: ' . ( isset( $global['presetColor5'] ) ? esc_attr( $global['presetColor5'] ) : '#34495e' ) . ';
			--productx-color6: ' . ( isset( $global['presetColor6'] ) ? esc_attr( $global['presetColor6'] ) : '#787676' ) . ';
			--productx-color7: ' . ( isset( $global['presetColor7'] ) ? esc_attr( $global['presetColor7'] ) : '#f0f2f3' ) . ';
			--productx-color8: ' . ( isset( $global['presetColor8'] ) ? esc_attr( $global['presetColor8'] ) : '#f8f9fa' ) . ';
			--productx-color9: ' . ( isset( $global['presetColor9'] ) ? esc_attr( $global['presetColor9'] ) : '#ffffff' ) . ';
			}';

		// Addon Genetarate CSS Added
		if ( 'front' === $src ) {
			$addon_css = get_option( 'wopb_generated_css' );
			if ( $addon_css ) {
				$custom_css .= $addon_css;
			}
		}

		wp_register_style( 'productx-global-style', false, array(), WOPB_VER );
		wp_enqueue_style( 'productx-global-style' );
		wp_add_inline_style( 'productx-global-style', wp_strip_all_tags( wp_unslash( $custom_css ) ) );
	}
}
