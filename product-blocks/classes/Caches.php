<?php
/**
 * Plugin Cache.
 *
 * @package WOPB\Caches
 * @since v.1.0.0
 */
namespace WOPB;

defined( 'ABSPATH' ) || exit;

/**
 * Caches class.
 */
class Caches {

	/**
	 * API Endpoint
	 *
	 * @since v.1.0.0
	 */
	private $api_endpoint = 'https://wopb.wpxpo.com/wp-json/restapi/v2/';

	private $files = array( 'design', 'premade', 'template' );
	/**
	 * Setup class.
	 *
	 * @since v.1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'template_data_callback' ) );
	}

	/**
	 * Get Template or Desing from the API Action
	 *
	 * @since v.1.0.0
	 * @return NULL
	 */
	public function template_data_callback() {
		register_rest_route(
			'wopb/v2',
			'/get_preset_data/',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'get_file_callback' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				),
			)
		);
	}

	/**
	 * ResetData from API
	 *
	 * @since v.2.0.7
	 * @param ARRAY
	 * @return ARRAY | Data of the Design
	 */
	public function reset_all_callback() {
		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'] . '/wopb';
		foreach ( $this->files as $key => $file ) {
			if ( file_exists( $upload_dir . '/' . $file . '.json' ) ) {
				wp_delete_file( $upload_dir . '/' . $file . '.json' );
			}
		}
		foreach ( $this->files as $key => $download ) {
			$this->download_source( $download, false );
		}
		return array(
			'success' => true,
			'data'    => __( 'Data Fetched!!!', 'product-blocks' ),
		);
	}

	/**
	 * Get Data Form File
	 *
	 * @since v.2.3.9
	 * @param ARRAY
	 * @return array | Data of the Premade
	 */
	public function get_file_callback( $request ) {
		$type = $request->get_param( 'type' );
		if ( ! is_string( $type ) || ! in_array( $type, array_merge( $this->files, array( 'all', 'reset' ) ), true ) ) {
			return new \WP_Error( 'wopb_invalid_type', __( 'Invalid library type.', 'product-blocks' ), array( 'status' => 400 ) );
		}
		if ( 'reset' === $type ) {
			return $this->reset_all_callback();
		} else {
			try {
				return $this->get_preset_list( $type );
			} catch ( Exception $e ) {
				return array(
					'success' => false,
					'message' => $e->getMessage(),
				);
			}
		}
	}

	/**
	 * Get Preset Data List Form File
	 *
	 * @since v.2.3.9
	 * @param $type
	 * @return array | Data of the Premade
	 */
	public function get_preset_list( $type ) {
		$res = array();
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$upload_url = wp_upload_dir();
		$dir        = trailingslashit( $upload_url['basedir'] ) . 'wopb/';
		$downloads  = 'all' === $type ? $this->files : array( $type );
		foreach ( $downloads as $key => $download ) {
			$file_path = $dir . $download . '.json';
			if ( file_exists( $file_path ) && ( current_time( 'timestamp' ) - filemtime( $file_path ) ) <= 432000 ) { // 5 Days in Seconds
				$content = $wp_filesystem->get_contents( $file_path );
			} else {
				$content = $this->download_source( $download );
			}

			$items = $this->get_listed_items( $content, $download );
			if ( 'all' === $type ) {
				$res[ $download ] = $items;
			} else {
				return array(
					'success' => true,
					'data'    => null === $items ? $content : wp_json_encode( $items ),
				);
			}
		}
		return array(
			'success' => true,
			'data'    => wp_json_encode( $res ),
		);
	}

	/**
	 * Designs of a library catalog that the library lists
	 *
	 * Every design of the catalog is listed. The ones the catalog marks as Pro show a Pro badge
	 * and a link to the WowStore Pro page until Pro is licensed.
	 *
	 * @since 4.6.0
	 * @param string $content Catalog as JSON, a list of designs.
	 * @param string $type    Catalog: 'design', 'premade' or 'template'.
	 * @return array|null Null when the catalog isn't a list.
	 */
	private function get_listed_items( $content, $type ) {
		$all = json_decode( (string) $content );
		if ( ! is_array( $all ) ) {
			return null;
		}

		/**
		 * Filters the designs the template library lists.
		 *
		 * @since 4.6.0
		 * @param array  $items Designs the library lists, the whole catalog.
		 * @param array  $all   Every design in the catalog.
		 * @param string $type  Catalog: 'design', 'premade' or 'template'.
		 */
		$items = apply_filters( 'wopb_template_library_items', $all, $all, $type );
		return is_array( $items ) ? array_values( $items ) : array();
	}

	/**
	 * Create a Directory in Upload Folder
	 *
	 * @since v.1.0.0
	 * @param NULL
	 * @return STRING | Directory Path
	 */
	public function create_directory( $type = 'template' ) {
		try {
			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
			$upload_dir_url = wp_upload_dir();
			$dir            = trailingslashit( $upload_dir_url['basedir'] ) . 'wopb/';
			WP_Filesystem( false, $upload_dir_url['basedir'], true );
			if ( ! $wp_filesystem->is_dir( $dir ) ) {
				$wp_filesystem->mkdir( $dir );
			}
			if ( ! file_exists( $dir . $type . '.json' ) ) {
				$wp_filesystem->put_contents( $dir . $type . '.json', '', FS_CHMOD_FILE );
			}
			return $dir;
		} catch ( Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Download Source from the Server API
	 *
	 * @since v.1.0.0
	 * @param STRING | Type (STRING)
	 * @return ARRAY | Message from the API
	 */
	public function download_source( $type = '', $output = true ) {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$response = wp_remote_post(
			$this->api_endpoint . 'wopbdata',
			array(
				'method'  => 'POST',
				'timeout' => 120,
				'body'    => array( 'type' => $type ),
			)
		);
		if ( ! is_wp_error( $response ) ) {
			// Cache only a catalog the server sent successfully, so an error page isn't kept for 5 days.
			if ( 200 === wp_remote_retrieve_response_code( $response ) && is_array( json_decode( $response['body'] ) ) ) {
				$path_url = $this->create_directory( $type );
				$wp_filesystem->put_contents( $path_url . $type . '.json', $response['body'] );
			}
			if ( $output ) {
				return $response['body'];
			}
		}
	}
}
