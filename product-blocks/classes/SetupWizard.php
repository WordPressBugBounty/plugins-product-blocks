<?php
/**
 * Initial Setup.
 *
 * @package WOPB\Notice
 * @since v.2.4.4
 */
namespace WOPB;

defined( 'ABSPATH' ) || exit;

class SetupWizard {

	public function __construct() {
		add_action( 'wowstore_menu', array( $this, 'menu_page_callback' ) );
		add_action( 'rest_api_init', array( $this, 'wopb_register_route' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'script_wizard_callback' ) ); // Option Panel

        add_action('wp_ajax_wopb_revenue_install', [$this,'revenue_install']);
	}
	
    public function revenue_install() {
        if ( ! ( isset( $_REQUEST['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['wpnonce'] ) ), 'wopb-nonce' ) ) ) {
			return;
		}
        
        $data = $this->install_and_active_plugin( 'revenue' );

        wp_send_json_success($data);
    }
	/**
     * Setup Wizard Function
     *
     * @since v.4.0.0
     * @return NULL
     */
	public function script_wizard_callback() {
		if ( wopb_function()->get_screen() == 'wopb-initial-setup-wizard' ) {
			wp_enqueue_script( 'wopb-setup-wizard', WOPB_URL . 'assets/js/setup.wizard.js', array( 'wp-i18n', 'wp-api-fetch', 'wp-api-request', 'wp-components', 'wp-blocks' ), WOPB_VER, true );
			wp_localize_script( 'wopb-setup-wizard', 'setup_wizard', array(
				'url' => WOPB_URL,
				'version' => WOPB_VER,
				'security' => wp_create_nonce('wopb-nonce'),
				'redirect' => admin_url('admin.php?page=wopb-settings#home')
			) );
			wp_set_script_translations( 'wopb-setup-wizard', 'product-blocks', WOPB_URL . 'languages/' );
		}
	}
	
	/**
     * Plugins Menu Page Added
     *
     * @since v.1.0.0
     * @return NULL
     */
    public function menu_page_callback() {
		add_submenu_page(
			'wopb-settings',
			esc_html__( 'Setup Wizard', 'product-blocks' ),
			esc_html__( 'Setup Wizard', 'product-blocks' ),
			'manage_options',
			'wopb-initial-setup-wizard',
			array( $this , 'initial_setup' )
		);
	}

	 /**
     * Initial Plugin Setting
     *
     * * @since 3.0.0
     * @return STRING
     */
    public function initial_setup() { ?>
        <div class="wopb-initial-setting-wrap" id="wopb-initial-setting"></div>
    <?php }

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

        register_rest_route(
            'wopb/v2',
            '/install-extra-plugin/',
            array(
                array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'install_extra_plugin' ),
                    'permission_callback' => function () {
                        return current_user_can( 'manage_options' );
                    },
                    'args' => array()
                )
            )
        );
	}

	/**
	 * Save General Settings Data.
	 *
	 * @return void
	 * @since 3.0.0
	 */
	public function wizard_site_action_callback( $server ) {
        $params = $server->get_params();
		if ( ! ( isset( $params['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $params['wpnonce'] ) ), 'wopb-nonce' ) ) ) {
			die();
		}

        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

		$action = sanitize_text_field( $params['action'] );
		
        $woocommerce_required = isset( $params['install_woocommerce'] ) && 'yes' === $params['install_woocommerce'];
        $revenue__required = isset( $params['install_revenue'] ) && 'yes' === $params['install_revenue'];

		if ( $action == 'install' ) {
			if ( isset( $params['siteType'] ) ) {
				$site_type = sanitize_text_field( $params['siteType'] );
				update_option( '__wopb_site_type', $site_type );
			}
            if($woocommerce_required && $revenue__required ) {
                if($this->install_and_active_plugin('woocommerce') && $this->install_and_active_plugin( 'revenue' )) {
                    $is_wc_active = is_plugin_active( 'revenue/revenue.php' );
                    if ( ! $is_wc_active ) {
                        $activate_status = activate_plugin( 'revenue/revenue.php', '', false, false );
                        if ( is_wp_error( $activate_status ) ) {
                            wp_send_json_error( array( 'message' => __( 'WowRevenue Activation Failed!', 'revenue' ) ) );
                        }
                    }
                    return rest_ensure_response( ['success' => true ] );
                }
            } 
            if($woocommerce_required && !$revenue__required) {
                if($this->install_and_active_plugin('woocommerce')) {
                    return rest_ensure_response( ['success' => true ] );
                }
            }
            if(!$woocommerce_required && $revenue__required) {
                if($this->install_and_active_plugin( 'revenue' )) {
                    $is_wc_active = is_plugin_active( 'revenue/revenue.php' );
                    if ( ! $is_wc_active ) {
                        $activate_status = activate_plugin( 'revenue/revenue.php', '', false, false );
                        if ( is_wp_error( $activate_status ) ) {
                            wp_send_json_error( array( 'message' => __( 'WowRevenue Activation Failed!', 'revenue' ) ) );
                        }
                    }
                    return rest_ensure_response( ['success' => true ] );
                }
            }

		} else if ( $action == 'send' ) {
			update_option('wopb_setup_wizard_data', 'yes');
			$site = isset( $post['site'] ) ? sanitize_text_field( $post['site'] ) : get_option('__wopb_site_type', '');
			require_once WOPB_PATH . 'classes/Deactive.php';
			$obj = new \WOPB\Deactive();
			$obj->send_plugin_data( 'productx_wizard' , $site);

			return rest_ensure_response([
				'success' => true,
			]);
		}
	}

    /**
     * Install Extra Plugin
     *
     * @param object $server get request.
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     * @since v.4.1.7
     */
    public function install_extra_plugin( $server ) {
        $params = $server->get_params();
        if ( ! ( isset( $params['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $params['wpnonce'] ) ), 'wopb-nonce' ) ) ) {
            die();
        }
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin = $this->install_and_active_plugin( $params['name'] );

        return rest_ensure_response( [
            'redirect' => ! empty( $plugin['redirect'] ) ? $plugin['redirect'] : '',
            'success' => true,
        ] );
    }

    /**
     * Install and Active Plugin
     *
     * @param string $name get plugin name.
     * @return array
     * @since v.4.1.7
     */
    public function install_and_active_plugin( $name ) {
        $redirect = '';
        switch ( $name ) {
            case 'woocommerce':
                $woocommerce_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
                if ( ! $woocommerce_installed ) {
                    include_once WOPB_PATH . 'classes/Notice.php';
                    $obj = new \WOPB\Notice();
                    $obj->plugin_install( 'woocommerce', 'setup_wizard', true );
                }
                $is_wc_active = is_plugin_active( 'woocommerce/woocommerce.php' );
                if ( ! $is_wc_active ) {
                    $activate_status = activate_plugin( 'woocommerce/woocommerce.php', '', false, false );
                    if ( is_wp_error( $activate_status ) ) {
                        wp_send_json_error( array( 'message' => __( 'WooCommerce Activation Failed!', 'revenue' ) ) );
                    }
                }
                break;
                case 'revenue':
                    $revenue_installed  = file_exists( WP_PLUGIN_DIR . '/revenue/revenue.php' );
                    if ( ! $revenue_installed ) {
                        include_once WOPB_PATH . 'classes/Notice.php';
                        $obj = new \WOPB\Notice();
                        $obj->plugin_install( 'revenue', 'setup_wizard', true );
                    }
                    $is_wc_active = is_plugin_active( 'revenue/revenue.php' );
                    if ( ! $is_wc_active ) {
                        $activate_status = activate_plugin( 'revenue/revenue.php', '', false, false );
                        if ( is_wp_error( $activate_status ) ) {
                            wp_send_json_error( array( 'message' => __( 'WowRevenue Activation Failed!', 'revenue' ) ) );
                        }
                    }
                    $redirect = admin_url('admin.php?page=revenue');
                    break;
                default:
                break;
        }
        return array( 'redirect' => $redirect, 'success' => true );
    }
}
