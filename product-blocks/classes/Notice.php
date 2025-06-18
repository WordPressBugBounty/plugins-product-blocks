<?php

/**
 * Notice Action.
 *
 * @package WOPB\Notice
 * @since v.1.0.0
 */

namespace WOPB;

defined( 'ABSPATH' ) || exit;

/**
 * Notice class.
 */
class Notice {


	/**
	 * Setup class.
	 *
	 * @since v.1.0.0
	 */
	private $available_notice = array();
	private $price_id         = '';
	private $type;
	private $content;
	private $force;
	private $pro_user_notice;
	private $days_remaining;


	public function __construct() {
		 $this->type           = '';
		$this->content         = '';
		$this->force           = false;
		$this->pro_user_notice = false;
		$this->days_remaining  = '';
		add_action( 'admin_init', array( $this, 'admin_init_callback' ) );
		add_action( 'admin_init', array( $this, 'set_promotional_notice_callback' ) );
		add_action( 'wp_ajax_wc_install', array( $this, 'wc_install_callback' ) );
		add_action( 'admin_action_wc_activate', array( $this, 'wc_activate_callback' ) );
		add_action( 'wp_ajax_wopb_dismiss_notice', array( $this, 'set_dismiss_notice_callback' ) );
		/**
		 * WholesaleX Intro Banner and Remove Banner Function implementation
		 *
		 * @since 2.6.1
		 */
		// add_action( 'admin_init', array( $this, 'remove_wholesalex_intro_banner' ) );
		// add_action( 'admin_notices', array( $this, 'wholesalex_intro_notice' ) );
		// add_action( 'wp_ajax_install_wholesalex', array( $this, 'wholesalex_installation_callback' ) );
		add_action( 'admin_init', array( $this, 'remove_revenue_activation_banner' ) );

		add_action( 'admin_notices', array( $this, 'display_notices' ), 0 );
	}

	private function set_new_notice( $id = '', $type = '', $design_type = '', $start = '', $end = '', $repeat = false, $priority = 10, $show_if = false ) {
		return array(
			'id'                        => $id,
			'type'                      => $type,
			'design_type'               => $design_type,
			'start'                     => $start, // Start Date
			'end'                       => $end, // End Date
			'repeat_notice_after'       => $repeat, // Repeat after how many days
			'priority'                  => $priority, // Notice Priority
			'display_with_other_notice' => false, // Display With Other Notice
			'show_if'                   => $show_if, // Notice Showing Conditions
			'capability'                => 'manage_options', // Capability of users, who can see the notice
		);
	}

	public function get_notice_content( $key, $design_type ) {
		$close_url = add_query_arg( 'wopb-notice-disable', $key );

		switch ( $design_type ) {
			case 'pro_4':
				//
				// Will Get Free User
				$url = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=db-wstore-global&utm_medium=blackfriday-sale&utm_campaign=wstore-dashboard';
				$this->wc_notice_css();
				ob_start();
				?>

				<div class="wopb-pro-notice wopb-wc-install wopb-notice-wrapper notice">
					<div class="wopb-install-body wopb-image-banner">
						<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-promotional-dismiss-notice">
							<?php esc_html_e( 'Dismiss', 'product-blocks' ); ?>
						</a>
						<a href="<?php echo esc_url( $url ); ?>" target="_blank">
							<img src="<?php echo WOPB_URL . 'assets/img/banner_offer.jpg'; ?>" alt="Banner">
						</a>
					</div>
				</div>

				<?php

				return ob_get_clean();

				break;
			case 'pro_3':
				// Will Get single
				$url = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=wstore_topbar&utm_medium=flash_sale_pro&utm_campaign=wstore-dashboard';
				ob_start();
				?>

				<div class="wopb-notice-wrapper wopb-notice-type-5 notice">
					<div class="wopb-notice-content-wrapper">
						<div class="wopb-notice-content"><strong>WowStore</strong> Special Offer: Grab the advanced Product Filter with a special discount</div>
						<div class="wopb-notice-buttons">
							<a class="wopb-notice-btn button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank"> Give me Product Filter Access </a>
							<!-- <a class="wopb-notice-btn button" href=""> Give Me LIFETIME Access</a> -->
							<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-dont-save-money"> I Don’t Want Access </a>
						</div>
					</div>
					<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-close"><span class="wopb-notice-close-icon dashicons dashicons-dismiss"> </span></a>
				</div>

				<?php
				return ob_get_clean();
				break;

			case 'pro_2':
				// User Get Free User
				$icon     = WOPB_URL . 'assets/img/logo-sm.svg';
				$url      = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=wstore_topbar&utm_medium=flash_sale_pro&utm_campaign=wstore-dashboard';
				$discount = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=wstore_topbar&utm_medium=flash_sale_pro&utm_campaign=wstore-dashboard';

				ob_start();
				?>

				<div class="wopb-notice-wrapper wopb-notice-type-2 notice">
					<div class="wopb-notice-content-wrapper">
						<div class="wopb-notice-content"> Grab the <strong>special discount</strong> and reduce 80% development time with WowStore Pro. </div>
						<div class="wopb-notice-buttons">
							<a class="wopb-notice-btn button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank"> Upgrade to Pro </a>
							<a class="wopb-notice-btn button" href="<?php echo esc_url( $discount ); ?>" target="_blank"> Give Me Discount</a>
							<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-dont-save-money"> I don’t Want </a>
						</div>
					</div>
					<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-close"><span class="wopb-notice-close-icon dashicons dashicons-dismiss"> </span></a>
				</div>

				<?php

				return ob_get_clean();
				break;

			case 'pro_1':
				// Will Get single
				// Lifetime and Ultimited
				$icon       = WOPB_URL . 'assets/img/logo-sm.svg';
				$url        = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=db-wstore-global&utm_medium=blackfriday-sale&utm_campaign=wstore-dashboard';
				$access_url = 'https://www.wpxpo.com/wowstore/?utm_source=db-wstore-global&utm_medium=discount_explore_now&utm_campaign=wstore-dashboard';
				ob_start();
				?>
				<div class="wopb-notice-section notice">
					<div class="wopb-notice-wrapper wopb-notice-type-1">
						<div class="wopb-notice-icon"> <img src="<?php echo esc_url( $icon ); ?>" /> </div>
						<div class="wopb-notice-content-wrapper">
							<div class="wopb-notice-content"> <strong>Black Friday Deal Alert:</strong> WowStore on Sale - Enjoy <strong style="color: #ff176b;">up to 65% OFF</strong> on this Complete WooCommerce Solution</div>
							<div class="wopb-notice-buttons">
								<a class="wopb-notice-btn button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank"> SAVE 65% NOW! </a>
								<a class="wopb-notice-btn button" href="<?php echo esc_url( $access_url ); ?>" target="_blank">Explore WowStore</a>
								<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-dont-save-money"> I Don’t Want to Save Money </a>
							</div>
						</div>
						<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-close"><span class="wopb-notice-close-icon dashicons dashicons-dismiss"> </span></a>
					</div>
				</div>

				<?php

				return ob_get_clean();
			case 'lifetime_n_unlimited_1':
				// Will Get single
				// Lifetime and Ultimited
				$icon = WOPB_URL . 'assets/img/logo-sm.svg';
				$url  = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=wstore_topbar&utm_medium=pro_upsell&utm_campaign=wstore-dashboard';

				$lifetime_url = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=wstore_topbar&utm_medium=pro_upsell&utm_campaign=wstore-dashboard';
				ob_start();
				?>

				<div class="wopb-notice-wrapper wopb-notice-type-1 notice">
					<div class="wopb-notice-icon"> <img src="<?php echo esc_url( $icon ); ?>" /> </div>
					<div class="wopb-notice-content-wrapper">
						<div class="wopb-notice-content"> Upgrade to lifetime access and enjoy <strong>WowStore</strong> forever without any renewal hassle!</div>
						<div class="wopb-notice-buttons">
							<a class="wopb-notice-btn button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank">Upgrade Now </a>
							<a class="wopb-notice-btn button" href="<?php echo esc_url( $lifetime_url ); ?>" target="_blank"> Give me Lifetime Access </a>
							<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-dont-save-money"> I don’t want to Save Money </a>
						</div>
					</div>
					<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-close"><span class="wopb-notice-close-icon dashicons dashicons-dismiss"> </span></a>
				</div>

				<?php

				return ob_get_clean();
			case 'lifetime_1':
				// Lifetime 1
				$icon    = WOPB_URL . 'assets/img/logo-sm.svg';
				$url     = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=wstore_topbar&utm_medium=pro_upsell&utm_campaign=wstore-dashboard';
				$pro_url = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=wstore_topbar&utm_medium=pro_upsell&utm_campaign=wstore-dashboard';

				ob_start();
				?>

				<div class="wopb-notice-wrapper wopb-notice-type-3 notice">
					<div class="wopb-notice-content-wrapper">
						<div class="wopb-notice-content"> Skip renewal hassle by upgrading to lifetime access - no renewal charges required!</div>
						<div class="wopb-notice-buttons">
							<a class="wopb-notice-btn button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank">Upgrade Now</a>
							<a class="wopb-notice-btn button" href="<?php echo esc_url( $pro_url ); ?>" target="_blank"> Give me Lifetime Access</a>
							<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-dont-save-money"> I don’t want to Save Money </a>
						</div>
					</div>
					<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-close"><span class="wopb-notice-close-icon dashicons dashicons-dismiss"> </span></a>
				</div>

				<?php

				return ob_get_clean();
			case 'lifetime_2':
				// Lifetime 2

				$icon                  = WOPB_URL . 'assets/img/logo-sm.svg';
				$url                   = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=wstore_topbar&utm_medium=pro_upsell&utm_campaign=wstore-dashboard';
				$unlimited_site_access = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=wstore_topbar&utm_medium=pro_upsell&utm_campaign=wstore-dashboard';

				ob_start();
				?>

				<div class="wopb-notice-wrapper wopb-notice-type-4 notice">
					<div class="wopb-notice-icon"> <img class="wopb-notice-icon-img" src="<?php echo esc_url( $icon ); ?>" /> </div>
					<div class="wopb-notice-content-wrapper">
						<div class="wopb-notice-content"> Upgrade to unlimited site access and manage all of your sites with WowStore!</div>
						<div class="wopb-notice-buttons">
							<a class="wopb-notice-btn button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank">Upgrade Now</a>
							<a class="wopb-notice-btn button" href="<?php echo esc_url( $unlimited_site_access ); ?>" target="_blank">Give Me Unlimited Sites Access</a>
							<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-dont-save-money"> I don’t want to Save Money </a>
						</div>
					</div>
					<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-close"><span class="wopb-notice-close-icon dashicons dashicons-dismiss"> </span></a>
				</div>

				<?php

				return ob_get_clean();
			case 'welcome':
				$url = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=wstore_topbar&utm_medium=welcome_offer&utm_campaign=wstore-dashboard';
				ob_start();
				?>

				<div class="wopb-notice-wrapper wopb-notice-type-5 notice">
					<div class="wopb-notice-content-wrapper">
						<div class="wopb-notice-content"> Welcome to <strong>WowStore</strong> family. We have a welcomer offer for <strong>upgrading to Pro</strong> </div>
						<div class="wopb-notice-buttons">
							<a class="wopb-notice-btn button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank"> Claim Welcome Offer</a>
							<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-dont-save-money"> I Don’t Want to Save Money </a>
						</div>
					</div>
					<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-close"><span class="wopb-notice-close-icon dashicons dashicons-dismiss"> </span></a>
				</div>

				<?php

				return ob_get_clean();
				// code...
				break;
			case 'data_collection':
				$icon             = WOPB_URL . 'assets/img/logo-sm.svg';
				$data_collect_url = add_query_arg( 'wopb-data-collect', $key );

				ob_start();
				?>
				<div class="wopb-notice-wrapper data_collection_notice notice">
					<?php
					if ( isset( $icon ) ) {
						?>
						<div class="wopb-notice-icon"> <img src="<?php echo esc_url( $icon ); ?>" /> </div>
						<?php
					}
					?>

					<div class="wopb-notice-content-wrapper">
						<div class="wopb-notice-content">Let us send you effective tips for WowStore and special offers by sharing your non-sensitive date. <a class="wopb-notice-dc-learn-more" href="https://www.wpxpo.com/privacy-policy/" target="_blank">Learn more.</a></div>
						<div class="wopb-notice-buttons">
							<a class="wopb-notice-btn button button-primary" href="<?php echo esc_url( $data_collect_url ); ?>">Sure I’d love to help</a>
							<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-dont-save-money"> No Thanks</a>
						</div>
					</div>
					<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-close"><span class="wopb-notice-close-icon dashicons dashicons-dismiss"> </span></a>
				</div>

				<?php

				return ob_get_clean();
			case 'data_collection_2':
				$url = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=wstore_topbar&utm_medium=welcome_offer&utm_campaign=wstore-dashboard';
				ob_start();
				?>

				<div class="wopb-notice-wrapper wopb-notice-type-5 notice">
					<div class="wopb-notice-content-wrapper">
						<div class="wopb-notice-content">Share your non-sensitive data and let us send you effective tips and special discount offers <a class="wopb-notice-dc-learn-more" href="https://www.wpxpo.com/privacy-policy/" target="_blank">Learn more.</a></div>
						<div class="wopb-notice-buttons">
							<a class="wopb-notice-btn button button-primary" href="<?php echo esc_url( $url ); ?>"> Sure I’d love to help</a>
							<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-dont-save-money"> I Don’t Want to Save Money </a>
						</div>
					</div>
					<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-notice-close"><span class="wopb-notice-close-icon dashicons dashicons-dismiss"> </span></a>
				</div>

				<?php

				return ob_get_clean();
				// code...
				break;

			case 'wow_revenue_active_notice':
				$this->wc_notice_css();
				$this->wc_notice_js('wow_revenue');
				$this->wow_rev_notice_css();
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
				<div class="wopb-wowrev-notice notice">
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
						<a href="<?php echo $campaign_url; ?>" class="wopb-wowrev-notice__button">Create Discount WowRevenue<span></span></a>
						<?php
					} elseif ( $revenue_installed && ! is_plugin_active( 'revenue/revenue.php' ) ) {
						?>
						<a href="" class="wopb-wowrev-btn wopb-wowrev-notice__button wopb-revx-active wopb-revx-activate" data-link="<?php echo $campaign_url; ?>" data-api-url="<?php echo esc_url( rest_url( '/wopb/v2/install-extra-plugin' ) ); ?>">Active WowRevenue <span></span></a>
						<?php
					} elseif ( ! $revenue_installed ) {
						?>
						<a href="" class="wopb-wowrev-btn wopb-wowrev-notice__button wopb-revx-install" data-link="<?php echo $campaign_url; ?>" data-api-url="<?php echo esc_url( rest_url( '/wopb/v2/install-extra-plugin' ) ); ?>">Free Install WowRevenue<span></span></a>
						<?php
					}
					?>


					<div class="wopb-notice-close wopb-wowrev-notice__notice-close">
						<a href="<?php echo esc_url( $close_url ); ?>"><span class="dashicons dashicons-no-alt"></span></a>
					</div>
				</div>
				<?php
				return ob_get_clean();

				break;
			case 'wow_store_summer_sale_countdown_notice':
				//
				// Will Get Free User
				$url = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=db-wstore-global&utm_medium=summer-sale-countdown&utm_campaign=wstore-dashboard';
				$this->wc_notice_css();
				ob_start();
				?>

				<div class="wopb-pro-notice wopb-wc-install wopb-notice-wrapper notice">
					<div class="wopb-install-body wopb-image-banner">
						<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-promotional-dismiss-notice">
							<?php esc_html_e( 'Dismiss', 'product-blocks' ); ?>
						</a>
						<a href="<?php echo esc_url( $url ); ?>" target="_blank">
							<img src="<?php echo WOPB_URL . 'assets/img/summer_sale_countdown.jpg'; ?>" alt="summer sale countdown">
						</a>
					</div>
				</div>

				<?php

				return ob_get_clean();

				break;
			case 'wow_store_summer_sale_notice':
				//
				// Will Get Free User
				$url = 'https://www.wpxpo.com/wowstore/pricing/?utm_source=db-wstore-global&utm_medium=summer-sale&utm_campaign=wstore-dashboard';
				$this->wc_notice_css();
				ob_start();
				?>

				<div class="wopb-pro-notice wopb-wc-install wopb-notice-wrapper notice">
					<div class="wopb-install-body wopb-image-banner">
						<a href="<?php echo esc_url( $close_url ); ?>" class="wopb-promotional-dismiss-notice">
							<?php esc_html_e( 'Dismiss', 'product-blocks' ); ?>
						</a>
						<a href="<?php echo esc_url( $url ); ?>" target="_blank">
							<img src="<?php echo WOPB_URL . 'assets/img/summer_sale_regular.jpg'; ?>" alt="summer sale ">
						</a>
					</div>
				</div>

				<?php

				return ob_get_clean();

				break;

			default:
				// code...
				break;
		}
		return '';
	}

	private function get_price_id() {
		if ( wopb_function()->get_setting( 'is_lc_active' ) ) {
			$license_data = get_option( 'edd_wopb_license_data', false );
			if ( is_array( $license_data ) && isset( $license_data['price_id'] ) ) {
				return $license_data['price_id'];
			} else {
				return false;
			}
		}
		return false;
	}

	public function display_notices() {
		usort( $this->available_notice, array( $this, 'sort_notices' ) );

		$displayed_notice_count = 0;

		foreach ( $this->available_notice as $notice ) {
			if ( $this->is_valid_notice( $notice ) ) {

				if ( isset( $notice['show_if'] ) && true === $notice['show_if'] ) {
					if ( 0 !== $displayed_notice_count && false === $notice['display_with_other_notice'] ) {
						continue;
					}

					if ( isset( $notice['id'], $notice['design_type'] ) ) {
						echo $this->get_notice_content( $notice['id'], $notice['design_type'] ); //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

						++$displayed_notice_count;
					}
				}
			}
		}
	}

	public function is_valid_notice( $notice ) {
		$is_data_collect = isset( $notice['type'] ) && 'data_collect' == $notice['type'];
		$notice_status   = $is_data_collect ? $this->get_notice( $notice['id'] ) : $this->get_user_notice( $notice['id'] );

		if ( ! current_user_can( $notice['capability'] ) || 'off' === $notice_status ) {
			return false;
		}

		$current_time = gmdate( 'U' ); // Todays Data
		// $current_time = 1710493466;
		if ( $current_time > strtotime( $notice['start'] ) && $current_time < strtotime( $notice['end'] ) && isset( $notice['show_if'] ) && true === $notice['show_if'] ) { // Has Duration
			// Now Check Max Duration
			return true;
		}
	}


	public function set_user_notice_meta( $key = '', $value = '', $expiration = '' ) {
		if ( $key ) {
			$user_id     = get_current_user_id();
			$meta_key    = 'wopb_notice';
			$notice_data = get_user_meta( $user_id, $meta_key, true );
			if ( ! isset( $notice_data ) || ! is_array( $notice_data ) ) {
				$notice_data = array();
			}

			$notice_data[ $key ] = $value;

			if ( $expiration ) {
				$expire_notice_key                 = 'timeout_' . $key;
				$notice_data[ $expire_notice_key ] = $expiration;
			}

			update_user_meta( $user_id, $meta_key, $notice_data );
		}
	}

	public function get_user_notice( $key = '' ) {
		if ( $key ) {
			$user_id     = get_current_user_id();
			$meta_key    = 'wopb_notice';
			$notice_data = get_user_meta( $user_id, $meta_key, true );
			if ( ! isset( $notice_data ) || ! is_array( $notice_data ) ) {
				return false;
			}

			if ( isset( $notice_data[ $key ] ) ) {
				$expire_notice_key = 'timeout_' . $key;
				$current_time      = time();
				// $current_time = 1710493466;
				if ( isset( $notice_data[ $expire_notice_key ] ) && $notice_data[ $expire_notice_key ] < $current_time ) {
					unset( $notice_data[ $key ] );
					unset( $notice_data[ $expire_notice_key ] );
					update_user_meta( $user_id, $meta_key, $notice_data );
					return false;
				}
				return $notice_data[ $key ];
			}
		}
		return false;
	}

	/**
	 * Sort the notices based on the given priority of the notice.
	 *
	 * @param array $notice_1 First notice.
	 * @param array $notice_2 Second Notice.
	 * @return array
	 */
	public function sort_notices( $notice_1, $notice_2 ) {
		if ( ! isset( $notice_1['priority'] ) ) {
			$notice_1['priority'] = 10;
		}
		if ( ! isset( $notice_2['priority'] ) ) {
			$notice_2['priority'] = 10;
		}

		return $notice_1['priority'] - $notice_2['priority'];
	}

	private function get_notice_by_id( $id ) {
		if ( isset( $this->available_notice[ $id ] ) ) {
			return $this->available_notice[ $id ];
		}
	}
	/**
	 * Promotional Dismiss Notice Option Data
	 *
	 * @param NULL
	 * @return NULL
	 */
	public function set_promotional_notice_callback() {
		 $notice_collect = wopb_function()->get_screen( 'wopb-data-collect' );
		if ( $notice_collect ) {
			$notice = $this->get_notice_by_id( $notice_collect );
			if ( 'data_collect' == $notice['type'] ) {
				if ( isset( $notice['if_allow_repeat_days'] ) && $notice['if_allow_repeat_days'] ) {
					$repeat_timestamp = ( DAY_IN_SECONDS * intval( $notice['if_allow_repeat_days'] ) );
					$this->set_notice( $notice_collect, 'off', $repeat_timestamp );
					Deactive::send_plugin_data( 'allow' );
				}
			}
		}
		$notice_disable = wopb_function()->get_screen( 'wopb-notice-disable' );
		if ( $notice_disable ) {
			$notice = $this->get_notice_by_id( $notice_disable );
			if ( isset($notice['type']) && 'data_collect' == $notice['type'] ) {
				if ( isset( $notice['repeat_notice_after'] ) && $notice['repeat_notice_after'] ) {
					$repeat_timestamp = ( DAY_IN_SECONDS * intval( $notice['repeat_notice_after'] ) );
					$this->set_notice( $notice_disable, 'off', $repeat_timestamp );
				}
			} else {
				if ( isset( $notice['repeat_notice_after'] ) && $notice['repeat_notice_after'] ) {
					$repeat_timestamp = time() + ( DAY_IN_SECONDS * intval( $notice['repeat_notice_after'] ) );
					$this->set_user_notice_meta( $notice_disable, 'off', $repeat_timestamp );
				} else {
					$this->set_user_notice_meta( $notice_disable, 'off', false );
				}
			}
		}
	}


	/**
	 * Dismiss Notice Option Data
	 *
	 * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function set_dismiss_notice_callback() {
		if ( ! ( isset( $_REQUEST['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['wpnonce'] ) ), 'wopb-nonce' ) ) ) {
			return;
		}
		update_option( 'dismiss_notice', 'yes' );
	}


	/**
	 * Admin Notice Action Add
	 *
	 * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function admin_init_callback() {
		global $pagenow;

		if ( ! file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
			add_action( 'admin_notices', array( $this, 'wc_installation_notice_callback' ) );
		} elseif ( wopb_function()->get_setting( 'is_wc_ready' ) == false ) {
			add_action( 'admin_notices', array( $this, 'wc_activation_notice_callback' ) );
		}
		// else {
		// $this->price_id         = $this->get_price_id();
		$activate_date      = get_option( 'wopb_activation', false );
		$is_already_collect = get_transient( 'wpxpo_data_collect_productx' ) == 'yes';

		$this->available_notice = array(
			'wopb_holiday_1'         => $this->set_new_notice( 'wopb_halloween_1', 'promotion', 'pro_4', '23-12-2024', '01-01-2025', false, 10, ! wopb_function()->get_setting( 'is_lc_active' ) ),
			'wopb_holiday_2'         => $this->set_new_notice( 'wopb_black_friday_1', 'promotion', 'pro_4', '02-01-2025', '10-01-2025', false, 10, ! wopb_function()->get_setting( 'is_lc_active' ) ),
			// Welcome
			// 'welcome_notice'         => array(
			// 'id'                        => 'welcome_notice',
			// 'type'                      => 'promotion',
			// 'start'                     => $activate_date, // Start Date
			// 'end'                       => strtotime( '+7 day', $activate_date ), // End Date date('d-m-Y',strtotime($activate_date,strtotime('+7 day',$activate_date)))
			// 'design_type'               => 'welcome',
			// 'repeat_notice_after'       => false, // Repeat after how many days
			// 'priority'                  => 31, // Notice Priority
			// 'display_with_other_notice' => false, // Display With Other Notice
			// 'show_if'                   => ! wopb_function()->get_setting( 'is_lc_active' ) && $activate_date, // Notice Showing Conditions
			// 'capability'                => 'manage_options', // Capability of users, who can see the notice
			// ),

			// Data Collection
			// 'data_collection_notice'   => array(
			// 'id'                        => 'data_collection_notice',
			// 'type'                      => 'data_collect',
			// 'design_type'               => 'data_collection',
			// 'start'                     => '1-1-2024', // Start Date
			// 'end'                       => '1-1-2030', // End Date
			// 'repeat_notice_after'       => 60, // Repeat after how many days
			// 'if_allow_repeat_days'      => 2000,
			// 'priority'                  => 32, // Notice Priority
			// 'display_with_other_notice' => false, // Display With Other Notice
			// 'show_if'                   => ! $is_already_collect, // Notice Showing Conditions
			// 'capability'                => 'manage_options', // Capability of users, who can see the notice
			// ),

			'wow_revenue_active_notice' => array(
				'id'                        => 'wow_revenue_active_notice',
				'type'                      => 'promotion',
				'start'                     => '27-2-2025', // Start Date.
				'end'                       => '27-3-2030', // End Date date('d-m-Y',strtotime($activate_date,strtotime('+7 day',$activate_date))).
				'design_type'               => 'wow_revenue_active_notice',
				'repeat_notice_after'       => false, // Repeat after how many days.
				'priority'                  => 30, // Notice Priority.
				'display_with_other_notice' => true, // Display With Other Notice.
				'show_if'                   => ( 'plugins.php' === $pagenow || 'index.php' === $pagenow ), // Notice Showing Conditions.
				'capability'                => 'manage_options', // Capability of users, who can see the notice.
			),
			'wow_store_summer_sale_notice' => array(
				'id'                        => 'wow_store_summer_sale_notice',
				'type'                      => 'promotion',
				'start'                     => '23-06-2025', // Start Date.
				'end'                       => '05-07-2025', // End Date date('d-m-Y',strtotime($activate_date,strtotime('+7 day',$activate_date))).
				'design_type'               => 'wow_store_summer_sale_notice',
				'repeat_notice_after'       => false, // Repeat after how many days.
				'priority'                  => 31, // Notice Priority.
				'display_with_other_notice' => true, // Display With Other Notice.
				'show_if'                   => !wopb_function()->get_setting( 'is_lc_active' ), // Notice Showing Conditions.
				'capability'                => 'manage_options', // Capability of users, who can see the notice.
			),
			'wow_store_summer_sale_countdown_notice' => array(
				'id'                        => 'wow_store_summer_sale_countdown_notice',
				'type'                      => 'promotion',
				'start'                     => '06-07-2025', // Start Date.
				'end'                       => '09-07-2025', // End Date date('d-m-Y',strtotime($activate_date,strtotime('+7 day',$activate_date))).
				'design_type'               => 'wow_store_summer_sale_countdown_notice',
				'repeat_notice_after'       => false, // Repeat after how many days.
				'priority'                  => 31, // Notice Priority.
				'display_with_other_notice' => true, // Display With Other Notice.
				'show_if'                   => !wopb_function()->get_setting( 'is_lc_active' ), // Notice Showing Conditions.
				'capability'                => 'manage_options', // Capability of users, who can see the notice.
			),
		);
		// }
	}


	/**
	 * WooCommerce Installation Notice
	 *
	 * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_installation_notice_callback() {
		if ( ! get_option( 'dismiss_notice' ) ) {
			$this->wc_notice_css();
			$this->wc_notice_js('woocommerce');
			?>
			<div class="wopb-pro-notice wopb-wc-install wc-install">
				<img width="100" src="<?php echo esc_url( WOPB_URL . 'assets/img/woocommerce.png' ); ?>" alt="logo" />
				<div class="wopb-install-body">
					<a class="wc-dismiss-notice" data-security="<?php echo esc_attr( wp_create_nonce( 'wopb-nonce' ) ); ?>" data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" href="#"><span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Dismiss', 'product-blocks' ); ?></a>
					<h3><?php esc_html_e( 'Welcome to WowStore.', 'product-blocks' ); ?></h3>
					<p><?php esc_html_e( 'WowStore is a WooCommerce-based plugin. So you need to installed & activate WooCommerce to start using WowStore.', 'product-blocks' ); ?></p>
					<a  data-security="<?php echo esc_attr( wp_create_nonce( 'wopb-nonce' ) ); ?>" class="wc-install-btn button button-primary" href=""><span></span><?php esc_html_e( 'Install & Activate WooCommerce', 'product-blocks' ); ?></a>
					<div id="installation-msg"></div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * WooCommerce Activation Notice
	 *
	 * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_activation_notice_callback() {
		if ( ! get_option( 'dismiss_notice' ) ) {
			$this->wc_notice_css();
			$this->wc_notice_js('woo_activation');
			?>
			<div class="wopb-wc-install wc-install">
				<img width="100" src="<?php echo esc_url( WOPB_URL . 'assets/img/woocommerce.png' ); ?>" alt="logo" />
				<div class="wopb-install-body">
					<a class="wc-dismiss-notice" data-security="<?php echo wp_create_nonce( 'wopb-nonce' ); ?>" data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" href="#"><span class="dashicons dashicons-no-alt"></span><?php esc_html_e( 'Dismiss', 'product-blocks' ); ?></a>
					<h3><?php esc_html_e( 'Welcome to WowStore.', 'product-blocks' ); ?></h3>
					<p><?php esc_html_e( 'WowStore is a WooCommerce-based plugin. So you need to installed and activated WooCommerce to start using WowStore.', 'product-blocks' ); ?></p>
					<a  data-security="<?php echo esc_attr( wp_create_nonce( 'wopb-nonce' ) ); ?>" class="button button-primary wopb-wc-active-btn" href="<?php echo esc_url( add_query_arg( array( 'action' => 'wc_activate', 'wpnonce' =>  wp_create_nonce( 'wopb-nonce' ) ), admin_url() ) ); ?>"> <?php esc_html_e( 'Activate WooCommerce', 'product-blocks' ); ?></a>
				</div>
			</div>
			<?php
		}
	}


	/**
	 * WooCommerce Notice Styles
	 *
	 * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_notice_css() {
		?>
		<style type="text/css">
			.wopb-wc-install {
				display: flex;
				align-items: center;
				background: #fff;
				margin-top: 40px;
				width: calc(100% - 30px);
				border: 1px solid #ccd0d4;
				padding: 4px;
				border-radius: 4px;
				border-left: 3px solid #46b450;
				line-height: 0;
				gap: 16px;
			}

			.wopb-wc-install img {
				/* width: 120px !important; */
			}

			.wopb-install-body {
				-ms-flex: 1;
				flex: 1;
			}

			.wopb-install-body.wopb-image-banner {
				padding: 0px;
			}

			.wopb-install-body.wopb-image-banner img {
				width: 100%;
			}

			.wopb-install-body>div {
				max-width: 450px;
				margin-bottom: 20px;
			}

			.wopb-install-body h3 {
				margin: 0;
				font-size: 20px;
				margin-bottom: 10px;
				line-height: 1;
			}

			.wopb-pro-notice .wc-install-btn,
			.wp-core-ui .wopb-wc-active-btn {
				display: inline-flex;
				align-items: center;
				padding: 3px 20px;
				gap: 6px;
			}

			.wopb-pro-notice.loading .wc-install-btn {
				opacity: 0.7;
				pointer-events: none;
			}

			.wopb-wc-install.wc-install .dashicons {
				display: none;
				animation: dashicons-spin 1s infinite;
				animation-timing-function: linear;
			}

			.wopb-wc-install.wc-install.loading .dashicons {
				display: inline-block;
				margin-right: 5px;
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
				border-left: 3px solid #FF176B;
			}

			.wopb-pro-notice .wopb-install-body h3 {
				font-size: 20px;
				margin-bottom: 5px;
			}

			.wopb-pro-notice .wopb-install-body>div {
				max-width: 800px;
				margin-bottom: 0;
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
				padding-top: 0px;
				position: absolute;
				right: 0;
				top: 0px;
				padding: 10px 10px 14px;
				border-radius: 0 0 0 4px;
				border: 1px solid;
				display: inline-block;
				color: #fff;
			}

			.wopb-eid-notice p {
				margin: 0;
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
				padding: 8px 12px;
				border-radius: 4px;
				color: #000;
				font-size: 14px;
				margin-left: 3px;
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
				padding: 10px 8px 12px;
				text-decoration: none;
			}

			.wopb-notice {
				background: #fff;
				border: 1px solid #c3c4c7;
				border-left-color: #037FFF !important;
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
				padding: 12px;
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
				padding: 7px 12px;
				font-size: 12px;
				color: white;
				border: none;
				border-radius: 2px;
				cursor: pointer;
				margin-top: 6px;
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
				margin-left: 10px;
			}

			a.wopb-notice-wopb-button:hover {
				color: #fff !important;
			}

			.wopb-notice .wopb-link-wrap {
				margin-top: 10px;
			}

			.wopb-notice .wopb-link-wrap a {
				margin-right: 4px;
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
		</style>
		<?php
	}

	/**
	 * Wow Revenue Notice Style
	 *
	 * @since v.4.2.1
	 * @param NULL
	 * @return NULL
	 */
	public function wow_rev_notice_css() {
		?>
		<style type="text/css">
			.wopb-wc-active-btn {
				display: inline-flex;
				align-items: center;
				padding: 3px 20px;
			}

			.wopb-wowrev-notice {
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

			.wopb-wowrev-notice__button {
				color: #fff;
				border-radius: 8px;
				padding: 10px 20px;
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

			.loading span {
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

			@media only screen and (max-width: 1300px) {
				.wopb-wowrev-notice {
					background-position: 79% 100%;
					/* wow_rev_activation_responsive.jpg */
					background-image: url("<?php echo esc_url( WOPB_URL . 'assets/img/wow_rev_activation_responsive.jpg' ); ?>");;
				}
				.wopb-wowrev-notice__title{
					max-width: 350px;
				}
				.wopb-wowrev-notice__desc {
					max-width: 400px;
				}
				
			}
			@media only screen and (max-width: 1024px) {
				.wopb-wowrev-notice {
					background-image: none !important;
				}
				.wopb-wowrev-notice__title{
					max-width: 100%;
				}
				.wopb-wowrev-notice__desc {
					max-width: 100%;
				}
				
			}
			@media only screen and (max-width: 600px) {
				.wopb-wowrev-notice {
					padding: 15px;
				}
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
	 * WooCommerce Notice JavaScript
	 *
	 * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_notice_js($condition) {
		?>
		<script type="text/javascript">
		<?php
			switch ($condition) {
				case "woocommerce":
					?> 
					jQuery(document).ready(function($) {
						'use strict';
						$(document).on('click', '.wc-install-btn', function(e) {
							e.preventDefault();
							const $that = $(this);
							$.ajax({
								type: 'POST',
								url: ajaxurl,
								data: {
									install_plugin: 'woocommerce',
									action: 'wc_install',
									wpnonce: $that.data('security')
								},
								beforeSend: function() {
									$that.parents('.wc-install').addClass('loading');
								},
								success: function(response) {
									if(response && response?.success) {
										window.location.href = response.data;
									}
								},
								error: function(jqXHR, textStatus, errorThrown) {
									console.error('AJAX Error Log:');
									console.error('Status:', textStatus);
									console.error('Error:', errorThrown);
									console.error('Response Text:', jqXHR.responseText);
								},
								complete: function() {
									$that.parents('.wc-install').removeClass('loading');
								}
							});
						});
					});
					<?php
					break;
				case "wow_revenue":
					?> 
					jQuery(document).ready(function($) {
						'use strict';
						$(document).on('click', '.wopb-wowrev-btn', function(e) {
							e.preventDefault();
							const that = $(this);
							that.addClass('loading');
							if (that.hasClass('wopb-revx-install')) {
								$.ajax({
									url: wopb_option.ajax,
									method: 'POST',
									data: {
										action: 'wopb_revenue_install',
										wpnonce: wopb_option.security,
										name: 'revenue'
									},
									success: function(res) {
										if (res) {
											window.location.href = wopb_option.revenue_campaigns;
										}
									}
								});
							} else if (that.hasClass('wopb-revx-activate')) {
								$.ajax({
									url: wopb_option.ajax,
									method: 'POST',
									data: {
										wpnonce: wopb_option.security,
										action: 'wopb_revenue_install',

										name: 'revenue'
									},
									success: function(res) {
										if (res) {
											window.location.href = wopb_option.revenue_campaigns;
										}
									}
								});
							}

						});
					});
					<?php
					break;
				default:
					?> <?php
					break;
			}
		?>
				jQuery(document).ready(function($) {
				'use strict';
				// Dismiss notice
				$(document).on('click', '.wc-dismiss-notice', function(e) {
					e.preventDefault();
					const that = $(this);

					$.ajax({
						url: that.data('ajax'),
						type: 'POST',
						data: {
							action: 'wopb_dismiss_notice',
							wpnonce: that.data('security')
						},
						success: function(data) {
							that.parents('.wc-install').hide("slow", function() {
								that.parents('.wc-install').remove();
							});
						},
						error: function(xhr) {
							console.log('Error occured. Please try again' + xhr.statusText + xhr.responseText);
						},
					});
				});
			});
		</script>
		<?php
	}


	/**
	 * WooCommerce Force Install Action
	 *
	 * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_install_callback() {
		if ( ! ( isset( $_REQUEST['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['wpnonce'] ) ), 'wopb-nonce' ) ) ) {
			return;
		}
		if ( current_user_can( 'manage_options' ) ) {
			$this->plugin_install( 'woocommerce', '', true );
			die();
		}
	}


	/**
	 * WooCommerce Redirect After Active Action
	 *
	 * @since v.1.0.0
	 * @param NULL
	 * @return NULL
	 */
	public function wc_activate_callback() {
		if ( ! ( isset( $_REQUEST['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['wpnonce'] ) ), 'wopb-nonce' ) ) ) {
			return;
		}
		if ( current_user_can( 'manage_options' ) ) {
			activate_plugin( 'woocommerce/woocommerce.php' );
			wp_redirect( admin_url( 'admin.php?page=wopb-settings' ) );
			exit();
		}
	}

	/**
	 * WholesaleX Intro Notice
	 *
	 * @return void
	 * @since 2.6.1
	 */
	public function wholesalex_intro_notice() {
		 // check wholesalex is installed or not.
		$wholesalex_installed = file_exists( WP_PLUGIN_DIR . '/wholesalex/wholesalex.php' );

		$notice_status = $this->get_notice( '__wpxpo_wholesalex_intro_notice_status' );
		if ( ! $notice_status && ! $wholesalex_installed ) {
			ob_start();
			?>
			<style type="text/css">
				/*----- WholesaleX Into Notice ------*/
				.notice.notice-success.wopb-wholesalex-notice {
					border-left-color: #4D4DFF;
					padding: 0;
				}

				.wopb-notice-container {
					display: flex;
				}

				.wopb-notice-container a {
					text-decoration: none;
				}

				.wopb-notice-container a:visited {
					color: white;
				}

				.wopb-notice-image {
					padding-top: 15px;
					padding-left: 12px;
					padding-right: 12px;
					background-color: #f4f4ff;
					max-width: 40px;
				}

				.wopb-notice-image img {
					max-width: 100%;
				}

				.wopb-notice-content {
					width: 100%;
					padding: 16px;
					display: flex;
					flex-direction: column;
					gap: 8px;
				}

				.wopb-notice-wholesalex-button {
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

				a.wopb-wholesalex-pro-dismiss:focus {
					outline: none;
					box-shadow: unset;
				}

				.loading {
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

				@keyframes rotation {
					0% {
						transform: rotate(0deg);
					}

					100% {
						transform: rotate(360deg);
					}
				}

				/*----- End WholesaleX Into Notice ------*/
			</style>
			<div class="notice notice-success wopb-wholesalex-notice">
				<div class="wopb-notice-container">
					<div class="wopb-notice-image"><img src="<?php echo esc_url( WOPB_URL ) . 'assets/img/wholesalex-icon.svg'; ?>" /></div>
					<div class="wopb-notice-content">
						<div class="wopb-notice-content-header">
							<div class="wopb-notice-heading">
								<?php
								echo __( 'Introducing <span class="wopb-bold">WholesaleX</span> - The Most Complete <span class="wopb-bold">B2B Solution', 'product-blocks' ); //phpcs:ignore WordPress.Security.EscapeOutput 
								?>
							</div>
							<div class="wopb-notice-close">
								<a href="<?php echo esc_url( add_query_arg( 'close_wholesalex_promo', 'yes' ) ); ?>" class="wopb-wholesalex-pro-dismiss"><span class="dashicons dashicons-no-alt"></span></a>
							</div>
						</div>
						<div class="wopb-notice-content-body">
							<?php echo __('Start wholesaling in your WooCommerce store and enjoy up to <span class="wopb-bold">300% revenue</span>', 'product-blocks');  //phpcs:ignore  
							?>
						</div>
						<a id="wopb_install_wholesalex" class="wopb-notice-wholesalex-button "><?php echo esc_html__( 'Get WholesaleX', 'product-blocks' ); ?></a>
					</div>
				</div>
			</div>

			<script type="text/javascript">
				const installWholesaleX = (element) => {
					element.innerHTML = "<?php echo esc_html_e( 'Installing WholesaleX', 'product-blocks' ); ?> <span class='loading'></span>";
					const wopb_ajax = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
					const formData = new FormData();
					formData.append('action', 'install_wholesalex');
					formData.append('wpnonce', "<?php echo esc_attr( wp_create_nonce( 'install_wholesalex' ) ); ?>");
					fetch(wopb_ajax, {
							method: 'POST',
							body: formData,
						})
						.then(res => res.json())
						.then(res => {
							if (res) {
								if (res.success) {
									element.innerHTML = "<?php echo esc_html_e( 'Installed', 'product-blocks' ); ?>";
								} else {
									console.log("installation failed..");
								}
							}
							location.reload();
						})
				}
				const wopbInstallWholesaleX = document.getElementById('wopb_install_wholesalex');
				wopbInstallWholesaleX.addEventListener('click', (e) => {
					e.preventDefault();
					installWholesaleX(wopbInstallWholesaleX);
				})
			</script>
			<?php
			echo ob_get_clean(); //phpcs:ignore
		}
	}


	/**
	 * Remove WholesaleX Intro Banner
	 *
	 * @return void
	 * @since 2.6.1
	 */
	public function remove_wholesalex_intro_banner() { 		if (isset($_GET['close_wholesalex_promo']) && 'yes' === $_GET['close_wholesalex_promo']) { //phpcs:ignore
			$this->set_notice( '__wpxpo_wholesalex_intro_notice_status', true );
	}
	}


	/**
	 * WholesaleX Installation Callback From Banner.
	 *
	 * @return void
	 */
	public function wholesalex_installation_callback() {
		if ( ! isset( $_POST['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wpnonce'] ) ), 'install_wholesalex' ) ) {
			wp_send_json_error( 'Nonce Verification Failed' );
			die();
		}

		$wholesalex_installed = file_exists( WP_PLUGIN_DIR . '/wholesalex/wholesalex.php' );

		if ( ! $wholesalex_installed ) {
			$status = $this->plugin_install( 'wholesalex' );
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

		$this->set_notice( '__wpxpo_wholesalex_intro_notice_status', true );

		wp_send_json_success( __( 'Successfully Installed and Activated', 'product-blocks' ) );
	}

	/**
	 * Plugin Install
	 *
	 * @param string $plugin Plugin Slug.
	 * @param string $source From Where Send Install.
	 * @return boolean
	 * @since 2.6.1
	 */
	public function plugin_install( $plugin, $source = '', $install = false ) {
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions to install plugins.', 'wholesalex' ) );
		}
		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}
		if ( ! class_exists( 'WP_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
		}
		if ( ! class_exists( 'Plugin_Installer_Skin' ) ) {
			include ABSPATH . 'wp-admin/includes/class-plugin-installer-skin.php';
		}

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

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result = $upgrader->install( $api->download_link );
		if($result) {
			if( $install) {
				$plugin_path = $plugin . '/' . $plugin . '.php';
				$activate_status = activate_plugin( $plugin_path, '', false, false );
				if(!(is_wp_error( $activate_status ))){
					if($plugin == 'woocommerce') {
						return wp_send_json_success( admin_url( 'admin.php?page=wopb-settings' ) );
					} else {
						return wp_send_json_success(  __( 'Successfully Installed and Activated', 'product-blocks' ) );
					}
				} else {
					return wp_send_json_error( 'Plugin activation failed' );
				}
			} else {
				return wp_send_json_success('Successfully Installed and Activated');
			}
		} else {
			$errors = $upgrader->skin->get_errors();
			if ( is_wp_error( $errors ) ) {
				return wp_send_json_error( array( 'message' => __( 'Woocommerce Plugin Installation Failed!', 'product-blocks' ), 'error' => $result) );
			} else {
				return wp_send_json_error( 'Plugin installation failed. Possibly permission issues.' );
			}
		}
	}

	public function set_notice( $key = '', $value = '', $expiration = '' ) {
		if ( $key ) {
			$notice_data = wopb_function()->get_option_without_cache( 'wopb_notice', array() );

			if ( ! isset( $notice_data ) || ! is_array( $notice_data ) ) {
				$notice_data = array();
			}

			$notice_data[ $key ] = $value;

			if ( $expiration ) {
				$expire_notice_key                 = 'timeout_' . $key;
				$notice_data[ $expire_notice_key ] = time() + $expiration;
			}
			update_option( 'wopb_notice', $notice_data );
		}
	}

	public function get_notice( $key = '' ) {
		if ( $key ) {
			$notice_data = wopb_function()->get_option_without_cache( 'wopb_notice', array() );

			if ( ! isset( $notice_data ) || ! is_array( $notice_data ) ) {
				return false;
			}

			if ( isset( $notice_data[ $key ] ) ) {
				$expire_notice_key = 'timeout_' . $key;
				$current_time      = time();
				if ( isset( $notice_data[ $expire_notice_key ] ) && $notice_data[ $expire_notice_key ] < $current_time ) {
					unset( $notice_data[ $key ] );
					unset( $notice_data[ $expire_notice_key ] );
					update_option( 'wopb_notice', $notice_data );
					return false;
				}
				return $notice_data[ $key ];
			}
		}
		return false;
	}

	/**
	 * Remove WholesaleX Intro Banner
	 *
	 * @return void
	 * @since 2.6.1
	 */
	public function remove_revenue_activation_banner() { 		if (isset($_GET['close_revenue_activation']) && 'yes' === $_GET['close_revenue_activation']) { //phpcs:ignore
			$this->set_notice( '__wpxpo_revenue_activation_status', true );
	}
	}

	/**
	 * Remove WholesaleX Intro Banner
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
