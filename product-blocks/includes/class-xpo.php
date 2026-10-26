<?php

namespace WOPB\Includes;

defined( 'ABSPATH' ) || exit;

/**
 * Core class for managing plugin actions and integrations.
 */
class Xpo {

	/**
	 * Retrieve an option through WordPress, preserving the plugin's value filters.
	 *
	 * The legacy method name remains for existing callers; WordPress manages caching.
	 *
	 * @since v.1.0.7
	 * @param string $option        Option name.
	 * @param mixed  $default_value Default when the option does not exist.
	 * @return mixed
	 */
	public static function get_option_without_cache( $option, $default_value = false ) {
		if ( ! is_scalar( $option ) ) {
			return false;
		}
		$option = trim( (string) $option );
		if ( empty( $option ) ) {
			return false;
		}

		$exists = true;
		$value  = self::read_option( $option, $default_value, $exists );
		if ( ! $exists ) {
			return apply_filters( "wopb_default_option_{$option}", $value, $option );
		}
		return apply_filters( "wopb_option_{$option}", $value, $option );
	}

	/**
	 * Observe WordPress's missing-option path without bypassing its cache or filters.
	 *
	 * @param string $option        Option name.
	 * @param mixed  $default_value Default option value.
	 * @param bool   $exists        Receives whether WordPress found an existing value.
	 * @return mixed
	 */
	private static function read_option( $option, $default_value, &$exists ) {
		$exists       = true;
		$mark_missing = static function ( $value ) use ( &$exists ) {
			$exists = false;
			return $value;
		};
		$hook = "default_option_{$option}";
		add_filter( $hook, $mark_missing, PHP_INT_MAX );
		try {
			return get_option( $option, $default_value );
		} finally {
			remove_filter( $hook, $mark_missing, PHP_INT_MAX );
		}
	}

	/**
	 * Add an option through WordPress without overwriting an existing value.
	 *
	 * @since v.1.0.7
	 * @param string      $option   Option name.
	 * @param mixed       $value    Option value.
	 * @param string|bool $autoload Whether WordPress should autoload the option.
	 * @return bool
	 */
	public static function add_option_without_cache( $option, $value = '', $autoload = 'yes' ) {
		if ( ! is_scalar( $option ) ) {
			return false;
		}
		$option = trim( (string) $option );
		if ( empty( $option ) ) {
			return false;
		}
		$exists = true;
		self::read_option( $option, false, $exists );
		if ( $exists ) {
			return false;
		}
		return add_option( $option, $value, '', 'no' !== $autoload && false !== $autoload );
	}

	/**
	 * Retrieve a transient through WordPress, including its object-cache backend.
	 *
	 * @since v.1.0.7
	 * @param string $transient Transient name.
	 * @return mixed
	 */
	public static function get_transient_without_cache( $transient ) {
		if ( ! is_scalar( $transient ) || '' === (string) $transient ) {
			return false;
		}
		$transient = (string) $transient;
		return apply_filters( "wopb_transient_{$transient}", get_transient( $transient ), $transient );
	}

	/**
	 * Store a transient through WordPress, with native cache and expiry handling.
	 *
	 * @since v.1.0.7
	 * @param string $transient  Transient name.
	 * @param mixed  $value      Transient value.
	 * @param int    $expiration Expiration in seconds; zero means no expiration.
	 * @return bool
	 */
	public static function set_transient_without_cache( $transient, $value, $expiration = 0 ) {
		if ( ! is_scalar( $transient ) || '' === (string) $transient ) {
			return false;
		}
		return set_transient( (string) $transient, $value, (int) $expiration );
	}

	public static function generate_utm_link( $params = array() ) {
		$default_config = array(
			'example'                     => array(
				'source'   => 'db-wowaddons-featurename',
				'medium'   => 'block-feature',
				'campaign' => 'wowaddons-dashboard',
			),
			'menu_saved_template_go_pro'  => array(
				'source'   => 'wowstore-ghost',
				'medium'   => 'ST-upgrade_to_pro',
				'campaign' => 'wowstore-dashboard',
			),
			'menu_WB_go_pro'              => array(
				'source'   => 'wowstore-ghost',
				'medium'   => 'WB-upgrade_to_pro',
				'campaign' => 'wowstore-dashboard',
			),

			// productx main menu
			'main_menu_go_pro'            => array(
				'source'   => 'db-wstore-plugin',
				'medium'   => 'left-menu-upgrade',
				'campaign' => 'wstore-dashboard',
			),

			// Wordpress plugin list
			'plugin_list_productx_go_pro' => array(
				'source'   => 'db-wstore-plugin',
				'medium'   => 'upgrade',
				'campaign' => 'wstore-dashboard',
			),
			'plugin_dir_pro'              => array(
				'source'   => 'db-wstore-plugin',
				'medium'   => 'upgrade-pro',
				'campaign' => 'wstore-dashboard',
			),
			'sub_menu'                    => array(
				'source'   => 'db-wstore-plugin',
				'medium'   => 'sub-menu',
				'campaign' => 'wstore-dashboard',
			),
			'exclusive_deals'             => array(
				'source'   => 'db-wowstore-notice-logo',
				'medium'   => 'exclusive-deals',
				'campaign' => 'wowstore-dashboard',
			),
			'black_friday'                => array(
				'source'   => 'db-wowstore-notice-logo',
				'medium'   => 'black-friday',
				'campaign' => 'wowstore-dashboard',
			),
			'new_year_sale'               => array(
				'source'   => 'db-wowstore-notice-logo',
				'medium'   => 'new-year-sale',
				'campaign' => 'wowstore-dashboard',
			),
			'preorder'                    => array(
				'source'   => 'wowstore',
				'medium'   => 'preorder',
				'campaign' => 'product-addons',
			),
			// -----------------------------------------
			// 2026 Summer sale utms
			'summer_db'                   => array(
				'source'   => 'db-wowstore-notice',
				'medium'   => 'summer-sale',
				'campaign' => 'wowstore-dashboard',
			),
			// -----------------------------------------

		);

		// Step 1: Get parameters
		$base_url      = $params['url'] ?? 'https://www.wpxpo.com/product/wowstore/';
		$utm_key       = $params['utmKey'] ?? null;
		$affiliate     = $params['affiliate'] ?? apply_filters( 'wopb_affiliate_id', '' );
		$hash          = $params['hash'] ?? 'pricing';
		$custom_config = $params['config'] ?? null;

		$parsed_url = wp_parse_url( $base_url );
		$scheme     = $parsed_url['scheme'] ?? 'https';
		$host       = $parsed_url['host'] ?? '';
		$path       = $parsed_url['path'] ?? '';
		$query      = array();

		// Step 3: Extract existing query params if present
		if ( isset( $parsed_url['query'] ) ) {
			parse_str( $parsed_url['query'], $query );
		}

		// Step 4: Determine config
		$utm_config = $custom_config ?? ( $utm_key && isset( $default_config[ $utm_key ] ) ? $default_config[ $utm_key ] : array() );

		// Step 5: Add UTM parameters
		if ( ! empty( $utm_config ) ) {
			$query = array_merge(
				$query,
				array(
					'utm_source'   => $utm_config['source'],
					'utm_medium'   => $utm_config['medium'],
					'utm_campaign' => $utm_config['campaign'],
				)
			);
		}

		// Step 6: Add affiliate if present
		if ( $affiliate ) {
			$query['ref'] = $affiliate;
		}

		// Step 7: Reconstruct URL
		$final_url = $scheme . '://' . $host . $path;

		if ( ! empty( $query ) ) {
			$final_url .= '?' . http_build_query( $query );
		}

		if ( $hash ) {
			$final_url .= '#' . $hash;
		}

		return $final_url;
	}
}
