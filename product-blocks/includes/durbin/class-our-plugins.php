<?php

namespace WOPB\Includes\Durbin;

defined( 'ABSPATH' ) || exit;
use WOPB\Includes\Durbin\Xpo;
class OurPlugins {


	/**
	 * Constructor. Hooks into various WordPress actions.
	 */
	public function __construct() {
		// Our Plugin Activation Hooks.
		add_action( 'wp_ajax_wopb_install_plugin', array( $this, 'wopb_install_plugin_callback' ) );
	}

	/**
	 * Handles plugin installation and activation via AJAX.
	 *
	 * @return void
	 */
	public function wopb_install_plugin_callback() {

		$nonce  = isset( $_POST['wpnonce'] ) ? sanitize_key( wp_unslash( $_POST['wpnonce'] ) ) : '';
		$plugin = isset( $_POST['plugin'] ) ? $_POST['plugin'] : '';

		if ( ! wp_verify_nonce( $nonce, 'wopb-nonce' ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'No plugin specified' ) );

		}

		$res = array( 'message' => 'false' );

		if ( $plugin ) {
			$res = Xpo::install_and_active_plugin( $plugin );
		}

		wp_send_json_success( array( 'message' => $res ) );

		die();
	}
}
