<?php
/**
 * Compatibility Action.
 * 
 * @package WOPB\Notice
 * @since v.1.1.0
 */
namespace WOPB;

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility class.
 */
class Compatibility {

    /**
	 * Setup class.
	 *
	 * @since v.1.1.0
	 */
    public function __construct() {
        add_action( 'upgrader_process_complete', array( $this, 'plugin_upgrade_completed' ), 10, 2 );
    }

    /**
	 * Compatibility Class Run after Plugin Upgrade
	 *
	 * @since v.1.1.0
	 */
    public function plugin_upgrade_completed( $upgrader_object, $options ) {
        if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
            foreach ( $options['plugins'] as $plugin ) {
                if ( $plugin == WOPB_BASE ) {
                    // License Check And Active
                    if ( defined('WOPB_PRO_VER' ) ) { // for Pro Plugins
                        $license = get_option( 'edd_wopb_license_key' );
                        $response = wp_remote_post( 
                            'https://account.wpxpo.com',
                            array(
                                'timeout' => 15,
                                'sslverify' => false,
                                'body' => array(
                                    'edd_action' => 'activate_license',
                                    'license'    => $license,
                                    'item_id'    => 1263,
                                    'url'        => home_url()
                                )
                            )
                        );
                        if ( ! is_wp_error( $response ) && 200 == wp_remote_retrieve_response_code( $response ) ) {
                            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                            update_option( 'edd_wopb_license_status', $license_data->license );    
                        }
                    }
                    // Set Metabox Position in Product Edit
                    wopb_function()->builder_metabox_position();
                }
            }
        }
    }
}