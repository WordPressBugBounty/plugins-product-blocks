<?php
/**
 * Deactivation Action.
 * 
 * @package WOPB\Notice
 * @since v.1.1.0
 */
namespace WOPB;

defined('ABSPATH') || exit;

/**
 * Deactive class.
 */
class Deactive {

	private $PLUGIN_NAME = 'WowStore';
	private $PLUGIN_SLUG = 'product-blocks';
	private $PLUGIN_VERSION = WOPB_VER;
    private $API_ENDPOINT = 'https://inside.wpxpo.com';
    
    public function __construct() {
		global $pagenow;
        if ( $pagenow == 'plugins.php' ) {
        	add_action( 'admin_footer', array( $this, 'get_source_data_callback' ) );
		}
		add_action( 'wp_ajax_wopb_deactive_plugin',  array( $this, 'send_plugin_data' ) );
        // add_action( 'admin_notices', array( $this, 'data_collect_notice' ) );
		// add_action( 'admin_init', array( $this, 'wopb_tracking_callback' ) );
	}

	/**
	 * Set Flug Data Collect Notice
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
	public function wopb_tracking_callback() {
		$tracking = wopb_function()->get_screen( 'productx_tracking' );
		if ( $tracking ) {
			if ( $tracking == 'yes' ) {
				set_transient( 'wpxpo_data_collect_productx', 'yes', 157680000 ); // 5 years notice
				$this->send_plugin_data( 'allow' );
			} else {
				set_transient( 'wpxpo_data_collect_productx', 'no', 31536000 ); // 30 days notice
			}
		}
	}

	/**
	 * Data Collection Notice
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
	public function data_collect_notice() {
		if ( ! wopb_function()->get_screen( 'productx_tracking' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$is_collected = get_transient( 'wpxpo_data_collect_productx' );
			if ( $is_collected != 'yes' && $is_collected != 'no' ) {
				echo '<div class="notice notice-success">';
					echo '<div class="wpxpo-btn-tracking-notice">';
						printf( __( 'Want to help make <strong style="padding:0 5px;"> %1$s </strong> even more awesome? Allow %1$s to collect <a style="padding:0 5px;" href="https://www.wpxpo.com/data-collection-policy/" target="_blank">non-sensitive</a> diagnostic data and usage information.', 'product-blocks' ), $this->PLUGIN_NAME ); //phpcs:ignore
						echo '<a href="'.esc_url( add_query_arg( 'productx_tracking', 'yes' ) ).'" class="button button-primary" style="margin: 0 5px;">' . esc_html__( "Allow", "product-blocks" ) . '</a> ';
						echo '<a href="'.esc_url( add_query_arg( 'productx_tracking', 'no' ) ).'" class="button button-secondary">' . esc_html__( "No Thanks", "product-blocks" ) . '</a>';
					echo '</div>';
				echo '</div>';
			}
		}
	}

	/**
	 * Plugin Data request
     * 
     * @since v.1.0.0
	 * @return ARRAY | Return From The Server
	 */
	public function send_plugin_data( $type = 'deactive' , $site = '' ) {
		if ( current_user_can( 'administrator' ) ) {
			$data = $this->get_data();
			$data['site_type'] = $site ? $site : get_option( '__wopb_site_type' );
			
			$data['type'] = $type ? $type : 'deactive';
			$form_data = isset($_POST) ? $_POST : array(); //phpcs:ignore
		
			if ( isset( $form_data['action'] ) ) {
				unset( $form_data['action'] );
			}

			$response = wp_remote_post( $this->API_ENDPOINT, array(
				'method'      => 'POST',
				'timeout'     => 30,
				'redirection' => 5,
				'headers'     => array(
					'user-agent' => 'wpxpo/' . md5( esc_url( home_url() ) ) . ';',
					'Accept'     => 'application/json',
				),
				'blocking'    => true,
				'httpversion' => '1.0',
				'body'        => array_merge( $data, $form_data ),
			) );

			return $response;
		}		
	}
	

	/**
	 * Settings Arguments
     * 
     * @since v.1.0.0
	 * @return ARRAY
	 */
	public function get_settings() {
		$attr = array(
			array(
				'id'          	=> 'no-need',
				'input' 		=> false,
				'text'        	=> __( "I no longer need the plugin.", "product-blocks" )
			),
			array(
				'id'          	=> 'better-plugin',
				'input' 		=> true,
				'text'        	=> __( "I found a better plugin.", "product-blocks" ),
				'placeholder' 	=> __( "Please share which plugin.", "product-blocks" ),
			),
			array(
				'id'          	=> 'stop-working',
				'input' 		=> true,
				'text'        	=> __( "The plugin suddenly stopped working.", "product-blocks" ),
				'placeholder' 	=> __( "Please share more details.", "product-blocks" ),
			),
			array(
				'id'          	=> 'not-working',
				'input' 		=> false,
				'text'        	=> __( "I could not get the plugin to work.", "product-blocks" )
			),
			array(
				'id'          	=> 'temporary-deactivation',
				'input' 		=> false,
				'text'        	=> __( "It's a temporary deactivation.", "product-blocks" )
			),
			array(
				'id'          	=> 'other',
				'input' 		=> true,
				'text'        	=> __( "Other.", "product-blocks" ),
				'placeholder' 	=> __( "Please share the reason.", "product-blocks" ),
			),
		);
		return $attr;
	}

	/**
	 * Popup Module of Action
     * 
     * @since v.1.0.0
	 * @return ARRAY
	 */
    public function get_source_data_callback() {
        $this->deactive_html();
		$this->deactive_css();
		$this->deactive_js();
	}

	public function deactive_html() { ?>
    	<div class="wopb-modal" id="wopb-deactive-modal">
            <div class="wopb-modal-wrap">
			
                <div class="wopb-modal-header">
                    <h2><?php esc_html_e( "Quick Feedback", "product-blocks" ); ?></h2>
                    <button class="wopb-modal-cancel"><span class="dashicons dashicons-no-alt"></span></button>
                </div>

                <div class="wopb-modal-body">
                    <h3><?php esc_html_e( "If you have a moment, please let us know why you are deactivating WowStore:", "product-blocks" ); ?></h3>
                    <ul class="wopb-modal-input">
						<?php foreach ( $this->get_settings() as $key => $setting ) { ?>
							<li>
								<label>
									<input type="radio" <?php echo ( $key == 0 ? 'checked="checked"' : '' ); ?> id="<?php echo esc_attr( $setting['id'] ); ?>" name="<?php echo esc_attr( $this->PLUGIN_SLUG ); ?>" value="<?php echo esc_attr( $setting['text'] ); ?>">
									<div class="wopb-reason-text"><?php echo esc_html($setting['text']); ?></div>
									<?php if ( isset( $setting['input'] ) && $setting['input'] ) { ?>
										<textarea placeholder="<?php echo esc_attr($setting['placeholder']); ?>" class="wopb-reason-input <?php echo ($key == 0 ? 'wopb-active' : ''); ?> <?php echo esc_html( $setting['id'] ); ?>"></textarea>
									<?php } ?>
								</label>
							</li>
						<?php } ?>
                    </ul>
                </div>

                <div class="wopb-modal-footer">
                    <a class="wopb-modal-submit wopb-btn wopb-btn-primary" href="#"><?php esc_html_e( "Submit & Deactivate", "product-blocks" ); ?><span class="dashicons dashicons-update rotate"></span></a>
                    <a class="wopb-modal-deactive" href="#"><?php esc_html_e( "Skip & Deactivate", "product-blocks" ); ?></a>
				</div>
				
            </div>
        </div>
	<?php }

	public function deactive_css() { ?>
		<style type="text/css">
			.wopb-modal {
                position: fixed;
                z-index: 99999;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                background: rgba(0,0,0,0.5);
                display: none;
                box-sizing: border-box;
                overflow: scroll;
            }
            .wopb-modal * {
                box-sizing: border-box;
            }
            .wopb-modal.modal-active {
                display: block;
            }
			.wopb-modal-wrap {
                max-width: 870px;
                width: 100%;
                position: relative;
                margin: 10% auto;
                background: #fff;
            }
			.wopb-reason-input{
				display: none;
			}
			.wopb-reason-input.wopb-active{
				display: block;
			}
			.rotate{
				animation: rotate 1.5s linear infinite; 
			}
			@keyframes rotate{
				to{ transform: rotate(360deg); }
			}
			.wopb-popup-rotate{
				animation: popupRotate 1s linear infinite; 
			}
			@keyframes popupRotate{
				to{ transform: rotate(360deg); }
			}
			#wopb-deactive-modal {
				background: rgb(0 0 0 / 85%);
				overflow: hidden;
			}
			#wopb-deactive-modal .wopb-modal-wrap {
				max-width: 570px;
				border-radius: 5px;
				margin: 5% auto;
				overflow: hidden
			}
			#wopb-deactive-modal .wopb-modal-header {
				padding: 17px 30px;
				border-bottom: 1px solid #ececec;
				display: flex;
				align-items: center;
				background: #f5f5f5;
			}
			#wopb-deactive-modal .wopb-modal-header .wopb-modal-cancel {
				padding: 0;
				border-radius: 100px;
				border: 1px solid #b9b9b9;
				background: none;
				color: #b9b9b9;
				cursor: pointer;
				transition: 400ms;
				right: 15px;
    			position: absolute;
			}
			#wopb-deactive-modal .wopb-modal-header .wopb-modal-cancel:focus {
				color: red;
				border: 1px solid red;
				outline: 0;
			}
			#wopb-deactive-modal .wopb-modal-header .wopb-modal-cancel:hover {
				color: red;
				border: 1px solid red;
			}
			#wopb-deactive-modal .wopb-modal-header h2 {
				margin: 0;
				padding: 0;
				flex: 1;
				line-height: 1;
				font-size: 20px;
				text-transform: uppercase;
				color: #8e8d8d;
			}
			#wopb-deactive-modal .wopb-modal-body {
				padding: 25px 30px;
			}
			#wopb-deactive-modal .wopb-modal-body h3{
				padding: 0;
				margin: 0;
				line-height: 1.4;
				font-size: 15px;
			}
			#wopb-deactive-modal .wopb-modal-body ul {
				margin: 25px 0 10px;
			}
			#wopb-deactive-modal .wopb-modal-body ul li {
				display: flex;
				margin-bottom: 10px;
				color: #807d7d;
			}
			#wopb-deactive-modal .wopb-modal-body ul li:last-child {
				margin-bottom: 0;
			}
			#wopb-deactive-modal .wopb-modal-body ul li label {
				align-items: center;
				width: 100%;
			}
			#wopb-deactive-modal .wopb-modal-body ul li label input {
				padding: 0 !important;
				margin: 0;
				display: inline-block;
			}
			#wopb-deactive-modal .wopb-modal-body ul li label textarea {
				margin-top: 8px;
				width: 350px;
			}
			#wopb-deactive-modal .wopb-modal-body ul li label .wopb-reason-text {
				margin-left: 8px;
				display: inline-block;
			}
			#wopb-deactive-modal .wopb-modal-footer {
				padding: 0 30px 30px 30px;
				display: flex;
				align-items: center;
			}
			#wopb-deactive-modal .wopb-modal-footer .wopb-modal-submit {
				display: flex;
				align-items: center;
			}
			#wopb-deactive-modal .wopb-modal-footer .wopb-modal-submit span {
				margin-left: 4px;
				display: none;
			}
			#wopb-deactive-modal .wopb-modal-footer .wopb-modal-submit.loading span {
				display: block;
			}
			#wopb-deactive-modal .wopb-modal-footer .wopb-modal-deactive {
				margin-left: auto;
				color: #c5c5c5;
				text-decoration: none;
			}
			.wpxpo-btn-tracking-notice {
				display: flex;
                align-items: center;
                flex-wrap: wrap;
                padding: 5px 0;
			}
			.wpxpo-btn-tracking-notice .wpxpo-btn-tracking {
				margin: 0 5px;
				text-decoration: none;
			}
		</style>
    <?php }

	public function deactive_js() { ?>
        <script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				'use strict';

				// Modal Radio Input Click Action
				$('.wopb-modal-input input[type=radio]').on( 'change', function(e) {
					$('.wopb-reason-input').removeClass('wopb-active');
					$('.wopb-modal-input').find( '.'+$(this).attr('id') ).addClass('wopb-active');
				});

				// Modal Cancel Click Action
				$( document ).on( 'click', '.wopb-modal-cancel', function(e) {
					$( '#wopb-deactive-modal' ).removeClass( 'modal-active' );
				});

				// Deactivate Button Click Action
				$( document ).on( 'click', '#deactivate-product-blocks', function(e) {
					e.preventDefault();
					$( '#wopb-deactive-modal' ).addClass( 'modal-active' );
					$( '.wopb-modal-deactive, .wopb-modal-submit' ).attr( 'href', $(this).attr('href') );
				});

				// Submit to Remote Server
				$( document ).on( 'click', '.wopb-modal-submit', function(e) {
					e.preventDefault();
					$(this).addClass('loading');
					const url = $(this).attr('href')
					$.ajax({
						url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
						type: 'POST',
						data: { 
							action: 'wopb_deactive_plugin',
							cause_id: $('input[type=radio]:checked').attr('id'),
							cause_title: $('.wopb-modal-input input[type=radio]:checked').val(),
							cause_details: $('.wopb-reason-input.wopb-active').val(),
						},
						success: function (data) {
							$( '#wopb-deactive-modal' ).removeClass( 'modal-active' );
							window.location.href = url;
						},
						error: function(xhr) {
							console.log( 'Error occured. Please try again' + xhr.statusText + xhr.responseText );
						},
					});
				});
			});
		</script>
    <?php }


	/**
	 * Plugin Data Callback
     * 
     * @since v.1.0.0
	 * @return ARRAY | Plugins Information
	 */
	public function get_all_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
            include ABSPATH . '/wp-admin/includes/plugin.php';
        }

		$active = array();
        $inactive = array();
        $all_plugins = get_plugins();
        $active_plugins = get_option( 'active_plugins', array() );

        foreach ( $all_plugins as $key => $plugin ) {
			$arr = array();
			
			$arr['name'] 	= isset( $plugin['Name'] ) ? $plugin['Name'] : '';
			$arr['url'] 	= isset( $plugin['PluginURI'] ) ? $plugin['PluginURI'] : '';
			$arr['author'] 	= isset( $plugin['Author'] ) ? $plugin['Author'] : '';
			$arr['version'] = isset( $plugin['Version'] ) ? $plugin['Version'] : '';

			if ( in_array( $key, $active_plugins ) ){
				$active[$key] = $arr;
			} else {
				$inactive[$key] = $arr;
			}
		}

		return array( 'active' => $active, 'inactive' => $inactive );		
	}


	/**
	 * Get Theme Data Callback
     * 
     * @since v.1.0.0
	 * @return ARRAY | Theme Information
	 */
	public function get_themes() {
		$theme_data = array();
		$all_themes = wp_get_themes();	
		if ( is_array( $all_themes ) ){
			foreach ( $all_themes as $key => $theme ) {
				$attr = array();
				$attr['name'] 		= $theme->Name;
				$attr['url'] 		= $theme->ThemeURI;
				$attr['author'] 	= $theme->Author;
				$attr['version'] 	= $theme->Version;
				$theme_data[$key] 	= $attr;
			}
		}
		return $theme_data;
	}

	/**
	 * Get Current Users IP Address
     * 
     * @since v.1.0.0
	 * @return STRING | IP Address
	 */
	public function get_user_ip() {
		$response = wp_remote_get( 'https://icanhazip.com/' );
		
        if ( is_wp_error( $response ) ) {
            return '';
        } else {
			$user_ip = trim( wp_remote_retrieve_body( $response ) );
			return filter_var( $user_ip, FILTER_VALIDATE_IP ) ? $user_ip : '';
		}
    }

	/**
	 * All the Valid Information of The Users
     * 
     * @since v.1.0.0
	 * @return STRING | IP Address
	 */
	public function get_data() {
		global $wpdb;
		$user = wp_get_current_user();
		$user_count = count_users();
		$plugins_data = $this->get_all_plugins();

		$data = array(
			'name' => get_bloginfo( 'name' ),
			'home' => esc_url( home_url() ),
			'admin_email' => $user->user_email,
			'first_name' => isset($user->user_firstname) ? $user->user_firstname : '',
			'last_name' => isset($user->user_lastname) ? $user->user_lastname : '',
			'display_name' => $user->display_name,
			'wordpress' => get_bloginfo( 'version' ),
			'memory_limit' => WP_MEMORY_LIMIT,
			'debug_mode' => ( defined('WP_DEBUG') && WP_DEBUG ) ? 'Yes' : 'No',
			'locale' => get_locale(),
			'multisite' => is_multisite() ? 'Yes' : 'No',

			'themes' => $this->get_themes(),
			'active_theme' => get_stylesheet(),
			'users' => isset($user_count['total_users']) ? $user_count['total_users'] : 0,
			'active_plugins' => $plugins_data['active'],
			'inactive_plugins' => $plugins_data['inactive'],
			'server' => isset( $_SERVER['SERVER_SOFTWARE'] ) ?  $_SERVER['SERVER_SOFTWARE'] : '', //phpcs:ignore
			
			'timezone' => date_default_timezone_get(),
			'php_curl' => function_exists( 'curl_init' ) ? 'Yes' : 'No',
			'php_version' => function_exists('phpversion') ? phpversion() : '',
			'upload_size' => size_format( wp_max_upload_size() ),
			'mysql_version' => $wpdb->db_version(),
			'php_fsockopen' => function_exists( 'fsockopen' ) ? 'Yes' : 'No',

			'ip' => $this->get_user_ip(),
			'plugin_name' => $this->PLUGIN_NAME,
			'plugin_version' => $this->PLUGIN_VERSION,
			'plugin_slug' => $this->PLUGIN_SLUG
		);

		return $data;
	}
}