<?php //phpcs:ignore
namespace WOPB\Includes\Notice;

use WOPB\Includes\Durbin\DurbinClient;
use WOPB\Includes\Durbin\Xpo;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Notice
 */
class Notice {


	/**
	 * Notice version
	 *
	 * @var string
	 */
	private $notice_version = 'v4132';

	/**
	 * Notice JS/CSS applied
	 *
	 * @var boolean
	 */
	private $notice_js_css_applied = false;


	/**
	 * Notice Constructor
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices_callback' ) );
		add_action( 'admin_init', array( $this, 'set_dismiss_notice_callback' ) );

		// REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );

		// Woocommerce Install Action
		add_action( 'wp_ajax_wopb_install', array( $this, 'install_activate_plugin' ) );
	}


	/**
	 * Registers REST API endpoints.
	 *
	 * @return void
	 */
	public function register_rest_route() {
		$routes = array(
			// Hello Bar.
			array(
				'endpoint'            => 'hello_bar',
				'methods'             => 'POST',
				'callback'            => array( $this, 'hello_bar_callback' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			),
		);

		foreach ( $routes as $route ) {
			register_rest_route(
				'wopb',
				$route['endpoint'],
				array(
					array(
						'methods'             => $route['methods'],
						'callback'            => $route['callback'],
						'permission_callback' => $route['permission_callback'],
					),
				)
			);
		}
	}

	/**
	 * Handles Hello Bar dismissal action via REST API .
	 *
	 * @param \WP_REST_Request $request REST request object .
	 * @return \WP_REST_Response
	 */
	public function hello_bar_callback( \WP_REST_Request $request ) {
		$request_params = $request->get_params();
		$type           = isset( $request_params['type'] ) ? $request_params['type'] : '';

		if ( 'hello_bar' === $type ) {
			Xpo::handle_hellobar_action( 'set' );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Hello Bar Action performed', 'product-blocks' ),
			),
			200
		);
	}

	/**
	 * Set Notice Dismiss Callback
	 *
	 * @return void
	 */
	public function set_dismiss_notice_callback() {

		// Durbin notice dismiss.
		if ( isset( $_GET['wopb_durbin_key'] ) && $_GET['wopb_durbin_key'] ) {
			$durbin_key = sanitize_text_field( $_GET['wopb_durbin_key'] );
			Xpo::set_transient_without_cache( 'wopb_durbin_notice_' . $durbin_key, 'off' );

			if ( isset( $_GET['wopb_get_durbin'] ) && 'get' === $_GET['wopb_get_durbin'] ) {
				DurbinClient::send( DurbinClient::ACTIVATE_ACTION );
			}
		}

		// Install notice dismiss
		if ( isset( $_GET['wopb_install_key'] ) && $_GET['wopb_install_key'] ) {
			$install_key = sanitize_text_field( $_GET['wopb_install_key'] );
			Xpo::set_transient_without_cache( 'wopb_install_notice_' . $install_key, 'off' );
		}

		if ( isset( $_GET['disable_wopb_notice'] ) ) {
			$notice_key = sanitize_text_field( $_GET['disable_wopb_notice'] );
			if ( isset( $_GET['wopb_interval'] ) && '' != $_GET['wopb_interval'] ) {
				$interval = (int) $_GET['wopb_interval'];
				Xpo::set_transient_without_cache( 'wopb_get_pro_notice_' . $notice_key, 'off', $interval );
			} else {
				Xpo::set_transient_without_cache( 'wopb_get_pro_notice_' . $notice_key, 'off' );
			}
		}
	}

	/**
	 * Admin Notices Callback
	 *
	 * @return void
	 */
	public function admin_notices_callback() {
		$this->other_plugin_install_notice_callback( 'required' );
		$this->wopb_dashboard_notice_callback();
		$this->wopb_dashboard_durbin_notice_callback();
		$this->our_plugin_install_notice_callback();
	}

	/**
	 * Admin Dashboard Notice Callback
	 *
	 * @return void
	 */
	public function wopb_dashboard_notice_callback() {
		$this->wopb_dashboard_content_notice();
		$this->wopb_dashboard_banner_notice();
	}

	/**
	 * Dashboard Banner Notice
	 *
	 * @return void
	 */
	public function wopb_dashboard_banner_notice() {
		$wopb_db_nonce  = wp_create_nonce( 'wopb-dashboard-nonce' );
		$banner_notices = array(
			array(
				'key'        => 'wopb_summer_sale_2522',
				'start'      => '2025-06-23 00:00 Asia/Dhaka', // format YY-MM-DD always set time 00:00 and zone Asia/Dhaka
				'end'        => '2025-07-05 23:59 Asia/Dhaka', // format YY-MM-DD always set time 23:59 and zone Asia/Dhaka
				'banner_src' => WOPB_URL . 'assets/img/dashboard_banner/summer_sale_25.png',
				'url'        => Xpo::generate_utm_link(
					array(
						'utmKey' => 'summer_db',
					)
				),
				'visibility' => ! Xpo::is_lc_active(),
			),
			array(
				'key'        => 'wopb_summer_25_banner_v2',
				'start'      => '2025-07-06 00:00 Asia/Dhaka', // format YY-MM-DD always set time 00:00 and zone Asia/Dhaka
				'end'        => '2025-07-09 23:59 Asia/Dhaka', // format YY-MM-DD always set time 23:59 and zone Asia/Dhaka
				'banner_src' => WOPB_URL . 'assets/img/dashboard_banner/summer_sale_v2_2025.jpg',
				'url'        => Xpo::generate_utm_link(
					array(
						'utmKey' => 'summer_db',
					)
				),
				'visibility' => ! Xpo::is_lc_active(),
			),
		);

		foreach ( $banner_notices as $key => $notice ) {
			$notice_key = isset( $notice['key'] ) ? $notice['key'] : $this->notice_version;
			if ( isset( $_GET['disable_wopb_notice'] ) && $notice_key === $_GET['disable_wopb_notice'] ) {
				return;
			}

			$current_time = gmdate( 'U' );
			$notice_start = gmdate('U', strtotime($notice['start']));
			$notice_end = gmdate('U', strtotime($notice['end']));
			if ( $current_time >= $notice_start && $current_time <= $notice_end && $notice['visibility'] ) {

				$notice_transient = Xpo::get_transient_without_cache( 'wopb_get_pro_notice_' . $notice_key );

				if ( 'off' !== $notice_transient ) {
					if ( ! $this->notice_js_css_applied ) {
						$this->wopb_banner_notice_css();
						$this->notice_js_css_applied = true;
					}
					$query_args = array(
						'disable_wopb_notice' => $notice_key,
						'wopb_db_nonce'       => $wopb_db_nonce,
					);
					if ( isset( $notice['repeat_interval'] ) && $notice['repeat_interval'] ) {
						$query_args['wopb_interval'] = $notice['repeat_interval'];
					}
					?>
					<div class="wopb-notice-wrapper notice wc-install wopb-free-notice">
						<div class="wc-install-body wopb-image-banner">
							<a class="wc-dismiss-notice" href="
							<?php
							echo esc_url(
								add_query_arg(
									$query_args
								)
							);
							?>
							"><?php esc_html_e( 'Dismiss', 'product-blocks' ); ?></a>
							<a class="wopb-btn-image" target="_blank" href="<?php echo esc_url( $notice['url'] ); ?>">
								<img loading="lazy" src="<?php echo esc_url( $notice['banner_src'] ); ?>" alt="Discount Banner"/>
							</a>
						</div>
					</div>
					<?php
				}
			}
		}
	}

	/**
	 * Dashboard Content Notice
	 *
	 * @return void
	 */
	public function wopb_dashboard_content_notice() {

		$content_notices = array(
			array(
				'key'        => 'wopb_dashboard_content_notice2',
				'start'      => '2024-06-23 00:00 Asia/Dhaka', // format YY-MM-DD always set time 00:00 and zone Asia/Dhaka
				'end'        => '2024-07-05 23:59 Asia/Dhaka', // format YY-MM-DD always set time 23:59 and zone Asia/Dhaka
				'url'        => Xpo::generate_utm_link(
					array(
						'utmKey' => 'summer_db',
					)
				),
				'visibility' => ! Xpo::is_lc_active(),
			),
		);

		$icon          = WOPB_URL . 'assets/img/logo-sm.svg';
		$wopb_db_nonce = wp_create_nonce( 'wopb-dashboard-nonce' );

		foreach ( $content_notices as $key => $notice ) {
			$notice_key = isset( $notice['key'] ) ? $notice['key'] : $this->notice_version;
			if ( isset( $_GET['disable_wopb_notice'] ) && $notice_key === $_GET['disable_wopb_notice'] ) {
				return;
			}

			$current_time = gmdate( 'U' );
			$notice_start = gmdate('U', strtotime($notice['start']));
			$notice_end = gmdate('U', strtotime($notice['end']));
			if ( $current_time >= $notice_start && $current_time <= $notice_end && $notice['visibility'] ) {

				$notice_transient = Xpo::get_transient_without_cache( 'wopb_get_pro_notice_' . $notice_key );

				if ( 'off' !== $notice_transient ) {
					if ( ! $this->notice_js_css_applied ) {
						$this->wopb_banner_notice_css();
						$this->notice_js_css_applied = true;
					}
					$query_args = array(
						'disable_wopb_notice' => $notice_key,
						'wopb_db_nonce'       => $wopb_db_nonce,
					);
					if ( isset( $notice['repeat_interval'] ) && $notice['repeat_interval'] ) {
						$query_args['wopb_interval'] = $notice['repeat_interval'];
					}

					$url = isset( $notice['url'] ) ? $notice['url'] : Xpo::generate_utm_link(
						array(
							'utmKey' => 'summer_db',
						)
					);

					?>
					<div class="wopb-notice-wrapper notice data_collection_notice"> 
						<?php
						if ( isset( $icon ) ) {
							?>
								<div class="wopb-notice-icon"> <img src="<?php echo esc_url( $icon ); ?>"/>  </div>
							<?php
						}
						?>
						
						<div class="wopb-notice-content-wrapper">
							<div class="">
								<strong><?php esc_html_e( 'Black Friday Deal Alert:', 'product-blocks' ); ?> </strong>
								<?php
								printf(
									/* translators: %s: Discount percentage */
									esc_html__( 'WowStore on Sale - Enjoy %s on the powerful site builder.', 'product-blocks' ),
									'<strong>' . esc_html__( 'up to 55% OFF', 'product-blocks' ) . '</strong>'
								);
								?>
							</div>
							<div class="wopb-notice-buttons"> 
								<a class="wopb-notice-btn button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank">
									<?php esc_html_e( 'Upgrade to Pro', 'product-blocks' ); ?>
								</a>
								<a href=
								<?php
								echo esc_url(
									add_query_arg(
										$query_args
									)
								);
								?>
								class="wopb-notice-dont-save-money">
									<?php esc_html_e( 'I don’t want to save money', 'product-blocks' ); ?>
								</a>
							</div>
						</div>
						<a href=
						<?php
						echo esc_url(
							add_query_arg(
								$query_args
							)
						);
						?>
						class="wopb-notice-close"><span class="wopb-notice-close-icon dashicons dashicons-dismiss"> </span></a>
					</div>
					<?php
				}
			}
		}
	}

	/**
	 * Dashboard Content Notice
	 *
	 * @return void
	 */
	public function our_plugin_install_notice_callback() {
		$notice_content = array(
			array(
				'type'	 	 => 'wow_revenue',
				'url'        => Xpo::generate_utm_link(
					array(
						'utmKey' => 'summer_db',
					)
				),
				'visibility' => ! Xpo::is_lc_active(),
			),
		);

		foreach ( $notice_content as $key => $notice ) {
				if ( ( isset( $_GET['wopb_install_key'] ) && $_GET['wopb_install_key'] === $notice['type'] ) ||
					'off' === Xpo::get_transient_without_cache( 'wopb_install_notice_' . $notice['type'], )
				) {
					return;
				}

				$this->install_notice_css();
				$this->install_notice_js();

				switch ( $notice['type'] ) { 
					case 'wow_revenue':
						$revenue_installed = file_exists( WP_PLUGIN_DIR . '/revenue/revenue.php' );
						$campaign_url      = esc_url( admin_url( 'admin.php?page=revenue#/campaigns' ) );
						$is_revenue_active = is_plugin_active( 'revenue/revenue.php' );
						$cmp_count         = 0;
						if ( $is_revenue_active ) {
							$cmp_count = $this->get_revenue_campaign_count(); // $cmp_count

							if ( $cmp_count && is_object( $cmp_count ) ) {
								$cmp_count = $cmp_count->total_campaigns;
							}
						}

						if ( $cmp_count ) {
							return;
						}

						ob_start();
							?>
							<div class="wopb-pro-notice wopb-wc-install wc-install wopb-wowrev-notice">
								<div class="wopb-wowrev-notice__wrapper">
									<div class="wopb-wowrev-notice__title">
										🚀 Offer Discounts, Boost Sales, and Increase Revenue
									</div>
									<div class="wopb-wowrev-notice__desc">Looking to maximize profits? WowRevenue can help send your store’s sales through the roof - It's a discount builder that ignites growth with flexible upselling, cross-selling, and downselling campaigns.</div>
									<div class="wopb-wowrev-notice__tag">
										<div>Bundle Discount</div>
										<span></span>
										<div>Quantity Discount</div>
										<span></span>
										<div>Frequently Bought Together</div>
										<span></span>
										<div>Buy X Get Y</div>
									</div>
									<?php
									if ( is_plugin_active( 'revenue/revenue.php' ) ) {
										?>
										<a  href="<?php echo $campaign_url; ?>" class="wopb-wowrev-notice__button">Create Discount WowRevenue<span></span></a>
										<?php
									} elseif ( $revenue_installed && !is_plugin_active( 'revenue/revenue.php' ) ) {
										?>
										<a href="#" data-plugin-slug="<?php echo esc_attr( "wow_revenue" ); ?>" class="wopb-wowrev-btn wopb-wowrev-notice__button wopb-revx-active wopb-revx-activate wc-install-btn wopb-install-btn" data-link="<?php echo $campaign_url; ?>" data-api-url="<?php echo esc_url( rest_url( '/wopb/v2/install-extra-plugin' ) ); ?>"><span class="dashicons dashicons-image-rotate"></span>Active WowRevenue <span></span></a>
										<?php
									} elseif ( ! $revenue_installed ) {
										?>
										<a href="#" data-plugin-slug="<?php echo esc_attr( "wow_revenue" ); ?>" class="wopb-wowrev-btn wopb-wowrev-notice__button wopb-revx-install wc-install-btn wopb-install-btn" data-link="<?php echo $campaign_url; ?>" data-api-url="<?php echo esc_url( rest_url( '/wopb/v2/install-extra-plugin' ) ); ?>"><span class="dashicons dashicons-image-rotate"></span>Free Install WowRevenue<span></span></a>
										<?php
									}
									?>
									<div class="wopb-notice-close wopb-wowrev-notice__notice-close">
										<a href="<?php echo esc_url( add_query_arg( array( 'wopb_install_key' => $notice['type'] ) ) ); ?>" ><span class="dashicons dashicons-no-alt"></span></a>
									</div>
								</div>
							</div>
							<?php
							echo ob_get_clean();
						break;
					default:
					// code...
					break;
				}
				return '';
		}
	}

	/**
	 * Admin Banner CSS File
	 *
	 * @since v.1.0.7
	 * @param NULL
	 * @return STRING
	 */
	public function wopb_banner_notice_css() {
		?>
		<style id="wopb-notice-css" type="text/css">
			.wopb-notice-wrapper {
				border: 1px solid #c3c4c7;
				border-left: 3px solid #037fff;
				margin: 15px 0px !important;
				display: flex;
				align-items: center;
				background: #F7F9FF;
				width: 100%;
				padding: 10px 0px;
				position: relative;
				box-sizing: border-box;
			}
			.wopb-notice-wrapper.notice, .wopb-free-notice.wc-install.notice {
				margin: 10px 0px;
				width: calc( 100% - 20px );
			}
			.wrap .wopb-notice-wrapper.notice, .wrap .wopb-free-notice.wc-install {
				width: 100%;
			}
			.wopb-notice-icon {
				margin-left: 15px;
			}
			.wopb-notice-icon img {
				max-width: 42px;
				width: 100%;
			}
			.wopb-notice-content-wrapper {
				display: flex;
				flex-direction: column;
				gap: 8px;
				font-size: 14px;
				line-height: 20px;
				margin-left: 15px;
			}
			.wopb-notice-buttons {
				display: flex;
				align-items: center;
				gap: 15px;
			}
			.wopb-notice-dont-save-money {
				font-size: 12px;
			}
			.wopb-notice-close {
				position: absolute;
				right: 2px;
				top: 5px;
				text-decoration: unset;
				color: #b6b6b6;
				font-family: dashicons;
				font-size: 16px;
				font-style: normal;
				font-weight: 400;
				line-height: 20px;
			}
			.wopb-notice-close-icon {
				font-size: 14px;
			}
			.wopb-free-notice.wc-install {
				display: flex;
				align-items: center;
				background: #fff;
				margin-top: 20px;
				width: 100%;
				box-sizing: border-box;
				border: 1px solid #ccd0d4;
				padding: 4px;
				border-radius: 4px;
				border-left: 3px solid #037fff;
				line-height: 0;
			}   
			.wopb-free-notice.wc-install img {
				margin-right: 0; 
				max-width: 100%;
			}
			.wopb-free-notice .wc-install-body {
				-ms-flex: 1;
				flex: 1;
				position: relative;
				padding: 10px;
			}
			.wopb-free-notice .wc-install-body.wopb-image-banner{
				padding: 0px;
			}
			.wopb-free-notice .wc-install-body h3 {
				margin-top: 0;
				font-size: 24px;
				margin-bottom: 15px;
			}
			.wopb-install-btn {
				margin-top: 15px;
				display: inline-block;
			}
			.wopb-free-notice .wc-install .dashicons-image-rotate.dashicons {
				display: none;
				animation: dashicons-spin 1s infinite;
				animation-timing-function: linear;
			}
			.wopb-free-notice.wc-install.loading .dashicons-image-rotate.dashicons {
				display: inline-block;
				margin-top: 12px;
				margin-right: 5px;
			}
			.wopb-free-notice .wc-install-body h3 {
				font-size: 20px;
				margin-bottom: 5px;
			}
			.wopb-free-notice .wc-install-body > div {
				max-width: 100%;
				margin-bottom: 10px;
			}
			.wopb-free-notice .button-hero {
				padding: 8px 14px !important;
				min-height: inherit !important;
				line-height: 1 !important;
				box-shadow: none;
				border: none;
				transition: 400ms;
			}
			.wopb-free-notice .wopb-btn-notice-pro {
				background: #2271b1;
				color: #fff;
			}
			.wopb-free-notice .wopb-btn-notice-pro:hover,
			.wopb-free-notice .wopb-btn-notice-pro:focus {
				background: #185a8f;
			}
			.wopb-free-notice .button-hero:hover,
			.wopb-free-notice .button-hero:focus {
				border: none;
				box-shadow: none;
			}
			@keyframes dashicons-spin {
				0% {
					transform: rotate( 0deg );
				}
				100% {
					transform: rotate( 360deg );
				}
			}
			.wopb-free-notice .wc-dismiss-notice {
				color: #fff;
				background-color: #000000;
				padding-top: 0px;
				position: absolute;
				right: 0;
				top: 0px;
				padding: 10px 10px 14px;
				border-radius: 0 0 0 4px;
				display: inline-block;
				transition: 400ms;
			}
			.wopb-free-notice .wc-dismiss-notice:hover {
				color:red;
			}
			.wopb-free-notice .wc-dismiss-notice .dashicons{
				display: inline-block;
				text-decoration: none;
				animation: none;
				font-size: 16px;
			}
			/* ===== Eid Banner Css ===== */
			.wopb-free-notice .wc-install-body {
				background: linear-gradient(90deg,rgb(0,110,188) 0%,rgb(2,17,196) 100%);
			}
			.wopb-free-notice p{
				color: #fff;
				margin: 5px 0px;
				font-size: 16px;
				font-weight: 300;
				letter-spacing: 1px;
			}
			.wopb-free-notice p.wopb-enjoy-offer {
				display: inline;
				font-weight: bold;
				
			}
			.wopb-free-notice .wopb-get-now {
				font-size: 14px;
				color: #fff;
				background: #14a8ff;
				padding: 8px 12px;
				border-radius: 4px;
				text-decoration: none;
				margin-left: 10px;
				position: relative;
				top: -4px;
				transition: 400ms;
			}
			.wopb-free-notice .wopb-get-now:hover{
				background: #068fe0;
			}
			.wopb-free-notice .wopb-dismiss {
				color: #fff;
				background-color: #000964;
				padding-top: 0px;
				position: absolute;
				right: 0;
				top: 0px;
				padding: 10px 8px 12px;
				border-radius: 0 0 0 4px;
				display: inline-block;
				transition: 400ms;
			}
			.wopb-free-notice .wopb-dismiss:hover {
				color: #d2d2d2;
			}
			/*----- WOPB_URL Into Notice ------*/
			.notice.notice-success.wopb-notice {
				border-left-color: #4D4DFF;
				padding: 0;
			}
			.wopb-notice-container {
				display: flex;
			}
			.wopb-notice-container a{
				text-decoration: none;
			}
			.wopb-notice-container a:visited{
				color: white;
			}
			.wopb-notice-container img {
				height: 100px; 
				width: 100px;
			}
			.wopb-notice-image {
				padding-top: 15px;
				padding-left: 12px;
				padding-right: 12px;
				background-color: #f4f4ff;
			}
			.wopb-notice-image img{
				max-width: 100%;
			}
			.wopb-notice-content {
				width: 100%;
				padding: 16px;
				display: flex;
				flex-direction: column;
				gap: 8px;
			}
			.wopb-notice-wopb-button {
				max-width: fit-content;
				padding: 8px 15px;
				font-size: 16px;
				color: white;
				background-color: #4D4DFF;
				border: none;
				border-radius: 2px;
				cursor: pointer;
				margin-top: 6px;
				text-decoration: none;
			}
			.wopb-notice-heading {
				font-size: 18px;
				font-weight: 500;
				color: #1b2023;
			}
			.wopb-notice-content-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
			}
			.wopb-notice-close .dashicons-no-alt {
				font-size: 25px;
				height: 26px;
				width: 25px;
				cursor: pointer;
				color: #585858;
			}
			.wopb-notice-close .dashicons-no-alt:hover {
				color: red;
			}
			.wopb-notice-content-body {
				font-size: 14px;
				color: #343b40;
			}
			.wopb-notice-wholesalex-button:hover {
				background-color: #6C6CFF;
				color: white;
			}
			span.wopb-bold {
				font-weight: bold;
			}
			a.wopb-pro-dismiss:focus {
				outline: none;
				box-shadow: unset;
			}
			.wopb-free-notice .loading, .wopb-notice .loading {
				width: 16px;
				height: 16px;
				border: 3px solid #FFF;
				border-bottom-color: transparent;
				border-radius: 50%;
				display: inline-block;
				box-sizing: border-box;
				animation: rotation 1s linear infinite;
				margin-left: 10px;
			}
			a.wopb-notice-wopb-button:hover {
				color: #fff !important;
			}
			@keyframes rotation {
				0% {
					transform: rotate(0deg);
				}
				100% {
					transform: rotate(360deg);
				}
			}
		</style>
		<?php
	}

	/**
	 * The Durbin Html
	 *
	 * @return STRING | HTML
	 */
	public function wopb_dashboard_durbin_notice_callback() {
		$durbin_key = 'wopb_durbin_dc1';
		if (
			isset( $_GET['wopb_durbin_key'] ) ||
			'off' === Xpo::get_transient_without_cache( 'wopb_durbin_notice_' . $durbin_key ) ||
			defined( 'WOPB_PRO_VER' )
		) {
			return;
		}

		if ( ! $this->notice_js_css_applied ) {
			$this->wopb_banner_notice_css();
			$this->notice_js_css_applied = true;
		}
		?>
		<style>
				.wopb-consent-box {
					width: 656px;
					padding: 16px;
					border: 1px solid #070707;
					border-left-width: 4px;
					border-radius: 4px;
					background-color: #fff;
					position: relative;
				}
				.wopb-consent-content {
					display: flex;
					justify-content: space-between;
					align-items: flex-end;
					gap: 26px;
				}
 
				.wopb-consent-text-first {
					font-size: 14px;
					font-weight: 600;
					color: #070707;
				}
				.wopb-consent-text-last {
					margin: 4px 0 0;
					font-size: 14px;
					color: #070707;
				}
 
				.wopb-consent-accept {
					background-color: #070707;
					color: #fff;
					border: none;
					padding: 6px 10px;
					border-radius: 4px;
					cursor: pointer;
					font-size: 12px;
					font-weight: 600;
					text-decoration: none;
				}
				.wopb-consent-accept:hover {
					background-color:rgb(38, 38, 38);
					color: #fff;
				}
			</style>
			<div class="wopb-consent-box wopb-notice-wrapper notice data_collection_notice">
			<div class="wopb-consent-content">
			<div class="wopb-consent-text">
			<div class="wopb-consent-text-first"><?php esc_html_e( 'Want to help make WowStore even more awesome?', 'product-blocks' ); ?></div>
			<div class="wopb-consent-text-last">
					<?php esc_html_e( 'Allow us to collect diagnostic data and usage information. see ', 'product-blocks' ); ?>
			<a href="https://www.wpxpo.com/data-collection-policy/" target="_blank" ><?php esc_html_e( 'what we collect.', 'product-blocks' ); ?></a>
			</div>
			</div>
			<a
									class="wopb-consent-accept"
									href=
					<?php
									echo esc_url(
										add_query_arg(
											array(
												'wopb_durbin_key' => $durbin_key,
												'wopb_get_durbin'  => 'get',
											)
										)
									);
					?>
									class="wopb-notice-close"
			><?php esc_html_e( 'Accept & Close', 'product-blocks' ); ?></a>
			</div>
			<a href=
					<?php
								echo esc_url(
									add_query_arg(
										array(
											'wopb_durbin_key' => $durbin_key,
										)
									)
								);
					?>
								class="wopb-notice-close"
			>
				<span class="wopb-notice-close-icon dashicons dashicons-dismiss"> </span></a>
			</div>
		<?php
	}



	/**
	 * Woocommerce Notice HTML
	 *
	 * @since v.1.0.0
	 * @return STRING | HTML
	 */
	public function other_plugin_install_notice_callback( $type = '' ) {
		$install_key_tran = 'woocommerce';
		$plugin_slug      = 'woocommerce';
		if ( 'required' !== $type ) {
			if ( ( isset( $_GET['wopb_install_key'] ) && $_GET['wopb_install_key'] === $install_key_tran ) ||
				'off' === Xpo::get_transient_without_cache( 'wopb_install_notice_' . $install_key_tran, )
			) {
				return;
			}
		}

		if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
			return;
		}

		$this->install_notice_css();
		$this->install_notice_js();
		?>
			<div class="wopb-pro-notice wopb-wc-install wc-install">
				<img width="100" src="<?php echo esc_url( WOPB_URL . 'assets/img/woocommerce.png' ); ?>" alt="logo" />
				<div class="wopb-install-body">
					<h3><?php esc_html_e( 'Welcome to WowStore.', 'product-blocks' ); ?></h3>
					<p><?php esc_html_e( 'WowStore is a WooCommerce-based plugin. So you need to installed & activate WooCommerce to start using WowStore.', 'product-blocks' ); ?></p>
					<div class="wopb-install-btn-wrap">
						<a class="wc-install-btn wopb-install-btn button button-primary" data-plugin-slug="<?php echo esc_attr( $plugin_slug ); ?>" href="#"><span class="dashicons dashicons-image-rotate"></span><?php file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ? esc_html_e( ' Activate WooCommerce', 'product-blocks' ) : esc_html_e( ' Install WooCommerce', 'product-blocks' ); ?></a>
						<?php if ( 'required' !== $type ) : ?>
							<a href="<?php echo esc_url( add_query_arg( array( 'wopb_install_key' => $install_key_tran ) ) ); ?>" class="wopb-install-cancel wc-dismiss-notice">
								<?php esc_html_e( 'Discard', 'product-blocks' ); ?>
							</a>
						<?php endif; ?>
					</div>
					<div id="installation-msg"></div>
				</div>
			</div>
		<?php
	}

	/**
	 * Plugin Install and Active Action
	 *
	 * @since v.1.6.8
	 * @return STRING | Redirect URL
	 */
	public function install_activate_plugin() {
		if ( ! isset( $_POST['install_plugin'] ) ) {
			return wp_send_json_error( esc_html__( 'Invalid request.', 'product-blocks' ) );
		}
		$plugin_slug = sanitize_text_field( wp_unslash( $_POST['install_plugin'] ) );

		Xpo::install_and_active_plugin( $plugin_slug );

		if ( wp_doing_ajax() || is_network_admin() || isset( $_GET['activate-multi'] ) || isset( $_POST['action'] ) && 'activate-selected' == sanitize_text_field( $_POST['action'] ) ) { //phpcs:ignore
			return;
		}

		return wp_send_json_success( admin_url( 'admin.php?page=wopb-dashboard#dashboard' ) );
	}

	/**
	 * Installation Notice CSS
	 *
	 * @since v.1.0.0
	 */
	public function install_notice_css() {
		?>
		<style type="text/css">
			.wopb-wc-install {
				display: flex;
				align-items: center;
				background: #fff;
				margin-top: 30px !important;
				/*width: calc(100% - 65px);*/
				border: 1px solid #ccd0d4;
				padding: 4px !important;
				border-radius: 4px;
				border-left: 3px solid #46b450;
				line-height: 0;
				gap: 15px;
				padding: 15px 10px !important;
			}
			.wopb-wc-install img {
				width: 100px;
			}
			.wopb-install-body {
				-ms-flex: 1;
				flex: 1;
			}
			.wopb-install-body.wopb-image-banner {
				padding: 0px !important;
			}
			.wopb-install-body.wopb-image-banner img {
				width: 100%;
			}
			.wopb-install-body>div {
				max-width: 450px;
				margin-bottom: 20px !important;
			}
			.wopb-install-body h3 {
				margin: 0 !important;
				font-size: 20px;
				margin-bottom: 10px !important;
				line-height: 1;
			}
			.wopb-pro-notice .wc-install-btn,
			.wp-core-ui .wopb-wc-active-btn {
				display: inline-flex;
				align-items: center;
				padding: 3px 20px !important;
			}
			.wopb-pro-notice.loading .wc-install-btn {
				opacity: 0.7;
				pointer-events: none;
			}
			.wopb-wc-install.wc-install .dashicons-image-rotate.dashicons {
				display: none;
				animation: dashicons-spin 1s infinite;
				animation-timing-function: linear;
			}
			.wopb-wc-install.wc-install.loading .dashicons {
				display: inline-block;
				margin-right: 5px !important;
			}
			@keyframes dashicons-spin {
				0% {
					transform: rotate(0deg);
				}
				100% {
					transform: rotate(360deg);
				}
			}
			.wopb-wc-install .wc-dismiss-notice {
				position: relative;
				text-decoration: none;
				float: right;
				right: 5px;
				display: flex;
				align-items: center;
			}
			.wopb-wc-install .wc-dismiss-notice .dashicons {
				display: flex;
				text-decoration: none;
				animation: none;
				align-items: center;
			}
			.wopb-pro-notice {
				position: relative;
				border-left: 3px solid #037fff;
				width: calc(100% - 20px);
    			box-sizing: border-box;
			}
			.wopb-pro-notice .wopb-install-body h3 {
				font-size: 20px;
				margin-bottom: 5px !important;
			}
			.wopb-pro-notice .wopb-install-body>div {
				max-width: 800px;
				margin-bottom: 0 !important;
			}
			.wopb-pro-notice .button-hero {
				padding: 8px 14px !important;
				min-height: inherit !important;
				line-height: 1 !important;
				box-shadow: none;
				border: none;
				transition: 400ms;
				background: #46b450;
			}
			.wopb-pro-notice .button-hero:hover,
			.wp-core-ui .wopb-pro-notice .button-hero:active {
				background: #389e41;
			}
			.wopb-pro-notice .wopb-btn-notice-pro {
				background: #e5561e;
				color: #fff;
			}
			.wopb-pro-notice .wopb-btn-notice-pro:hover,
			.wopb-pro-notice .wopb-btn-notice-pro:focus {
				background: #ce4b18;
			}
			.wopb-pro-notice .button-hero:hover,
			.wopb-pro-notice .button-hero:focus {
				border: none;
				box-shadow: none;
			}
			.wopb-pro-notice .wopb-promotional-dismiss-notice {
				background-color: #000000;
				padding-top: 0px !important;
				position: absolute;
				right: 0;
				top: 0px;
				padding: 10px 10px 14px !important;
				border-radius: 0 0 0 4px;
				border: 1px solid;
				display: inline-block;
				color: #fff;
			}
			.wopb-eid-notice p {
				margin: 0 !important;
				color: #f7f7f7;
				font-size: 16px;
			}
			.wopb-eid-notice p.wopb-eid-offer {
				color: #fff;
				font-weight: 700;
				font-size: 18px;
			}
			.wopb-eid-notice p.wopb-eid-offer a {
				background-color: #ffc160;
				padding: 8px 12px !important;
				border-radius: 4px;
				color: #000;
				font-size: 14px;
				margin-left: 3px !important;
				text-decoration: none;
				font-weight: 500;
				position: relative;
				top: -4px;
			}
			.wopb-eid-notice p.wopb-eid-offer a:hover {
				background-color: #edaa42;
			}
			.wopb-install-body .wopb-promotional-dismiss-notice {
				right: 4px;
				top: 3px;
				border-radius: unset !important;
				padding: 10px 8px 12px !important;
				text-decoration: none;
			}
			.wopb-notice {
				background: #fff;
				border: 1px solid #c3c4c7;
				border-left-color: #037fff !important;
				border-left-width: 4px;
				border-radius: 4px 0px 0px 4px;
				box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
				padding: 0px !important;
				margin: 40px 20px 0 2px !important;
				clear: both;
			}
			.wopb-notice .wopb-notice-container {
				display: flex;
				width: 100%;
			}
			.wopb-notice .wopb-notice-container a {
				text-decoration: none;
			}
			.wopb-notice .wopb-notice-container a:visited {
				color: white;
			}
			.wopb-notice .wopb-notice-container img {
				width: 100%;
				max-width: 30px !important;
				padding: 12px !important;
			}
			.wopb-notice .wopb-notice-image {
				display: flex;
				align-items: center;
				flex-direction: column;
				justify-content: center;
				background-color: #f4f4ff;
			}
			.wopb-notice .wopb-notice-image img {
				max-width: 100%;
			}
			.wopb-notice .wopb-notice-content {
				width: 100%;
				margin: 5px !important;
				padding: 8px !important;
				display: flex;
				flex-direction: column;
				gap: 0px;
			}
			.wopb-notice .wopb-notice-wopb-button {
				max-width: fit-content;
				text-decoration: none;
				padding: 7px 12px !important;
				font-size: 12px;
				color: white;
				border: none;
				border-radius: 2px;
				cursor: pointer;
				margin-top: 6px !important;
				background-color: #e5561e;
			}
			.wopb-notice-heading {
				font-size: 18px;
				font-weight: 500;
				color: #1b2023;
			}
			.wopb-notice-content-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
			}
			.wopb-notice-close .dashicons-no-alt {
				font-size: 25px;
				height: 26px;
				width: 25px;
				cursor: pointer;
				color: #585858;
			}
			.wopb-notice-close .dashicons-no-alt:hover {
				color: red;
			}
			.wopb-notice-content-body {
				font-size: 12px;
				color: #343b40;
			}
			.wopb-bold {
				font-weight: bold;
			}
			a.wopb-pro-dismiss:focus {
				outline: none;
				box-shadow: unset;
			}
			.wopb-free-notice .loading,
			.wopb-notice .loading {
				width: 16px;
				height: 16px;
				border: 3px solid #FFF;
				border-bottom-color: transparent;
				border-radius: 50%;
				display: inline-block;
				box-sizing: border-box;
				animation: rotation 1s linear infinite;
				margin-left: 10px !important;
			}
			a.wopb-notice-wopb-button:hover {
				color: #fff !important;
			}
			.wopb-notice .wopb-link-wrap {
				margin-top: 10px !important;
			}
			.wopb-notice .wopb-link-wrap a {
				margin-right: 4px !important;
			}
			.wopb-notice .wopb-link-wrap a:hover {
				background-color: #ce4b18;
			}
			body .wopb-notice .wopb-link-wrap>a.wopb-notice-skip {
				background: none !important;
				border: 1px solid #e5561e;
				color: #e5561e;
				padding: 6px 15px !important;
			}
			body .wopb-notice .wopb-link-wrap>a.wopb-notice-skip:hover {
				background: #ce4b18 !important;
			}
			@keyframes rotation {
				0% {
					transform: rotate(0deg);
				}
				100% {
					transform: rotate(360deg);
				}
			}

			.wopb-install-btn-wrap {
				display: flex;
				align-items: stretch;
				gap: 10px;
			}
			.wopb-install-btn-wrap .wopb-install-cancel {
				position: static !important;
				padding: 3px 20px;
				border: 1px solid #a0a0a0;
				border-radius: 2px;
			}

			.wopb-wowrev-notice {
				margin: 20px 20px 0 2px;
				padding: 0px !important;
				border: 0px;
				display: block;
			}

			.wopb-wowrev-notice__wrapper {
				background-color: #fff;
				padding: 30px 40px;
				box-sizing: border-box;
				box-shadow: 0px 0px 16px 32px #585C5F1A;
				background-image: url("<?php echo esc_url( WOPB_URL . 'assets/img/wow_rev_activation_updated.jpg' ); ?>");
				background-position: 50% 50%;
				background-repeat: no-repeat;
				border-radius: 8px;
				position: relative;
				border: 0px;
				background-position: 100% 100%;
				background-size: cover;
			}

			.wopb-wowrev-notice__title {
				font-size: 24px;
				font-weight: 600;
				line-height: 32px;
				color: #0A0D14;
				margin-bottom: 8px;
			}

			.wopb-wowrev-notice__desc {
				color: #525866;
				max-width: 664px;
				margin-bottom: 16px;
			}

			.wopb-wowrev-notice__tag,
			.wopb-wowrev-notice__tag div {
				display: flex;
				align-items: center;
				gap: 8px;
			}

			.wopb-wowrev-notice__tag div {
				color: #6E3FF3;
				font-weight: 400;
				text-decoration: none;
			}

			.wopb-wowrev-notice__tag span {
				width: 6px;
				height: 6px;
				display: block;
				border-radius: 10px;
				background-color: #6E3FF3;
				box-sizing: border-box;
			}

			.wopb-wowrev-notice__desc,
			.wopb-wowrev-notice__button,
			.wopb-wowrev-notice__tag div {
				font-size: 14px;
				line-height: 20px;
				text-decoration: none;
			}

			.wopb-wowrev-notice .wopb-wowrev-notice__button {
				color: #fff;
				border-radius: 8px;
				padding: 10px 20px !important;
				box-sizing: border-box;
				display: block;
				width: fit-content;
				margin-top: 24px;
				background-color: #00A464;
			}
			.wopb-wowrev-notice__button:focus,
			.wopb-wowrev-notice__button:active, 
			.wopb-wowrev-notice__button:hover {
				color: #fff;
			}

			.wopb-wowrev-notice__campaign-img {
				position: absolute;
				top: 0px;
				right: 0px;
				border-top-right-radius: 8px;
				border-bottom-right-radius: 8px;
			}

			.wopb-notice-close .dashicons-no-alt {
				font-size: 25px;
				height: 26px;
				width: 25px;
				cursor: pointer;
				color: #fff;
			}

			.wopb-wowrev-notice__notice-close {
				position: absolute;
				top: 12px;
				right: 12px;
			}

			.wopb-wowrev-notice__notice-close a {
				display: block;
				width: fit-content;
				text-decoration: none;
			}
		</style>
		<?php
	}

	/**
	 * Installation Notice JS
	 *
	 * @since v.1.0.0
	 */
	public function install_notice_js() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				'use strict';
				$(document).on('click', '.wc-install-btn.wopb-install-btn', function(e) {
					e.preventDefault();
					const $that = $(this);
					console.log($that.attr('data-plugin-slug'), "data-plugin-slug");
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {
							install_plugin: $that.attr('data-plugin-slug'),
							action: 'wopb_install'
						},
						beforeSend: function() {
							$that.parents('.wc-install').addClass('loading');
						},
						success: function(response) {
							window.location.reload()
						},
						complete: function() {
							// $that.parents('.wc-install').removeClass('loading');
						}
					});
				});
			});
		</script>
		<?php
	}

	/**
	 * Wow Revenue Campaign
	 *
	 * @return void
	 * @since 2.6.1
	 */
	public function get_revenue_campaign_count() {

		global $wpdb;
		$res = $wpdb->get_row(
			"SELECT COUNT(*) AS total_campaigns FROM {$wpdb->prefix}revenue_campaigns;"
		);

		return $res;
	}
}
