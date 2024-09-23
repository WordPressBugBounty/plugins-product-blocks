<?php
/**
 * Initial Setup.
 *
 * @package WOPB\Notice
 * @since v.2.4.4
 */
namespace WOPB;

use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;

defined( 'ABSPATH' ) || exit;

class InitialSetup {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'wopb_register_route' ) );
	}

	/**
	 * REST API Action
	 *  * @since 3.0.0
	 * @return NULL
	 */
	public function wopb_register_route() {
        register_rest_route(
			'wopb/v2', 
			'/wizard_action/',
			array(
				array(
					'methods'  => 'POST', 
					'callback' => array( $this, 'wizard_site_action_callback' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args' => array()
				)
			)
        );
	}


	/**
	 * Plugin Install
	 *
	 * @param string $plugin Plugin Slug.
	 * @return boolean
	 * @since 3.0.0
	 */
	public function plugin_install( $plugin ) {

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $plugin,
				'fields' => array(
					'sections' => false,
				),
			)
		);

		if ( is_wp_error( $api ) ) {
			return $api->get_error_message();
		}

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		return $result;
	}

	/**
	 * Save General Settings Data.
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function wizard_site_action_callback($server) {
        $params = $server->get_params();
		if ( ! ( isset( $params['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $params['wpnonce'] ) ), 'wopb-nonce' ) ) ) {
			die();
		}

		$action = sanitize_text_field( $params['action'] );
		
		if ( $action == 'install' ) {
			if ( isset( $params['siteType'] ) ) {
				$site_type = sanitize_text_field( $params['siteType'] );
				update_option( '__wopb_site_type', $site_type );
			}
	
			$woocommerce_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
			$wholesalex_installed  = file_exists( WP_PLUGIN_DIR . '/wholesalex/wholesalex.php' );
			if ( isset( $params['install_woocommerce'] ) && 'yes' === $params['install_woocommerce'] ) {
				if ( $woocommerce_installed ) {
					$is_wc_active = is_plugin_active( 'woocommerce/woocommerce.php' );
					if ( ! $is_wc_active ) {
						$activate_status = activate_plugin( 'woocommerce/woocommerce.php', '', false, true );
						if ( is_wp_error( $activate_status ) ) {
							wp_send_json_error( array( 'message' => __( 'WooCommerce Activation Failed!', 'wholesalex' ) ) );
						}
					}
				}
			}
			if ( isset( $params['install_wholesalex'] ) && 'yes' === $params['install_wholesalex'] ) {
				if ( ! $wholesalex_installed ) {
					include_once WOPB_PATH . 'classes/Notice.php';
					$obj = new \WOPB\Notice();
					$status = $obj->plugin_install( 'wholesalex' );
					if ( $status && ! is_wp_error( $status ) ) {
						$activate_status = activate_plugin( 'wholesalex/wholesalex.php', '', false, true );
						if ( is_wp_error( $activate_status ) ) {
							wp_send_json_error( array( 'message' => __( 'WholesaleX Activation Failed!', 'wholesalex' ) ) );
						}
					} else {
						wp_send_json_error( array( 'message' => __( 'WholesaleX Installation Failed!', 'wholesalex' ) ) );
					}
				} else {
					$is_wc_active = is_plugin_active( 'wholesalex/wholesalex.php' );
					if ( ! $is_wc_active ) {
						$activate_status = activate_plugin( 'wholesalex/wholesalex.php', '', false, true );
						if ( is_wp_error( $activate_status ) ) {
							wp_send_json_error( array( 'message' => __( 'WholesaleX Activation Failed!', 'wholesalex' ) ) );
						}
					}
				}
			}
			return rest_ensure_response( ['success' => true ] );
		} else if ( $action == 'send' ) {
			update_option('wopb_setup_wizard_data', 'yes');
			$site = isset($post['site']) ? sanitize_text_field( $post['site'] ) : '';
			require_once WOPB_PATH . 'classes/Deactive.php';
			$obj = new \WOPB\Deactive();
			$obj->send_plugin_data( 'productx_wizard' , $site);
		} else if ( $action == 'redirect' ) {
			update_option('wopb_setup_wizard_data', 'yes');
			require_once WOPB_PATH . 'classes/Deactive.php';
			$obj = new \WOPB\Deactive();
			$obj->send_plugin_data( 'productx_wizard' , get_option('__wopb_site_type', ''));
			return rest_ensure_response([
				'success' => true, 
				'redirect' => admin_url('admin.php?page=wopb-settings'),
			]);
		}
	}
}
