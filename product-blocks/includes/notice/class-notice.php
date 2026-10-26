<?php //phpcs:ignore
namespace WOPB\Includes\Notice;

use WOPB\Includes\Xpo;

defined( 'ABSPATH' ) || exit;
/**
 * Promo system facade. Data lives in promos/, one file per surface; all
 * surfaces share config() and the promo_is_live() date gate.
 *
 *   promos/notice.php       -> rendered here on admin_notices, via templates/<type>.php
 *   promos/hellobar.php     -> get_hellobar_config(), localized for React
 *   promos/plugin-meta.php  -> get_active_promo(), called by the plugin's
 *                              plugin_action_links / plugin_row_meta handler
 */
class Notice {


	/**
	 * Per-plugin identity. See config.php.
	 *
	 * @var array
	 */
	private $config = array();

	/**
	 * The complete set of promo surfaces: every place in wp-admin a promo can
	 * appear. Deliberately a closed, hand-written list, unlike templates/ which
	 * is open-ended by design — see get_promos() and template_for().
	 *
	 * @var array<string, string> surface slug => file under promos/.
	 */
	private const SURFACES = array(
		'notice'      => 'notice.php',
		'hellobar'    => 'hellobar.php',
		'plugin-meta' => 'plugin-meta.php',
	);


	/**
	 * Notice Constructor
	 */
	public function __construct() {
		$this->config = self::config();

		add_action( 'admin_notices', array( $this, 'admin_notices_callback' ) );
		add_action( 'admin_init', array( $this, 'set_dismiss_notice_callback' ) );

		// REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );

		add_filter( 'xpo_active_notice_lists', array( $this, 'handle_xpo_active_notice_lists' ), 99, 1 );
	}

	/**
	 * Per-plugin identity, shared by every promo surface.
	 *
	 * Always read config.php through here: it RETURNS an array, so a caller
	 * using require_once would get `true` on the second call.
	 *
	 * @return array
	 */
	public static function config() {
		static $config = null;

		if ( null === $config ) {
			$config = require __DIR__ . '/config.php';
		}

		return $config;
	}

	/**
	 * A developer typo: throw in dev so it's caught, ignore in production so a
	 * shipped mistake can't white-screen every admin page.
	 *
	 * @param string $message What was wrong.
	 * @return void
	 */
	private static function fail_in_dev( $message ) {
		if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || defined( 'XPO_DEV_MODE' ) ) {
			$config = self::config();
			throw new \RuntimeException( esc_html( $config['brand_name'] ) . ' promos: ' . esc_html( $message ) );
		}
	}

	/**
	 * Load one surface's promo list from promos/.
	 *
	 * The filename comes from the fixed SURFACES map, not from $surface: the
	 * surface set is closed, unlike templates/. See "Adding a fourth surface"
	 * in README.md.
	 *
	 * The locals below are inherited by the included file — that is how promos/
	 * files reach $prefix, $asset_url, etc. without requiring config.php.
	 *
	 * @param string $surface One of the SURFACES keys.
	 * @return array
	 */
	public static function get_promos( $surface = 'notice' ) {
		$config      = self::config();
		$prefix      = $config['prefix'];
		$asset_url   = $config['asset_url'];
		$brand_name  = $config['brand_name'];
		$brand_color = $config['brand_color'];

		if ( ! isset( self::SURFACES[ $surface ] ) ) {
			self::fail_in_dev( sprintf( 'unknown surface "%s" (known: %s)', $surface, implode( ', ', array_keys( self::SURFACES ) ) ) );
			return array();
		}

		// A known surface with no file is legitimate, not a typo: a plugin
		// without a hello bar just deletes promos/hellobar.php.
		$file = __DIR__ . '/promos/' . self::SURFACES[ $surface ];
		if ( ! is_readable( $file ) ) {
			return array();
		}

		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- filename comes from SURFACES.
		$promos = include $file;

		return is_array( $promos ) ? $promos : array();
	}

	/**
	 * First live promo on a surface, for the surfaces that show only one thing.
	 * The notice surface shows one per type, so it loops in render_notices().
	 *
	 * @param string $surface Surface slug.
	 * @return array|null Promo entry, or null when nothing is live.
	 */
	public static function get_active_promo( $surface ) {
		foreach ( self::get_promos( $surface ) as $promo ) {
			if ( self::promo_is_live( $promo ) ) {
				return $promo;
			}
		}

		return null;
	}

	/**
	 * Resolve a promo's `type` to templates/<type>.php. No map to maintain:
	 * adding a design means dropping in a template and naming the type after it.
	 *
	 * @param string $type Promo type, from a promos/ entry (never user input).
	 * @return string Absolute template path, or '' if the type is unknown.
	 */
	private function template_for( $type ) {
		if ( is_string( $type ) && preg_match( '/^[a-z0-9_-]+$/', $type ) ) {
			$template = __DIR__ . '/templates/' . $type . '.php';
			if ( is_readable( $template ) ) {
				return $template;
			}
		}

		self::fail_in_dev( sprintf( 'no template for type "%s" (expected templates/%s.php)', $type, $type ) );
		return '';
	}

	/**
	 * The one date gate, shared by all three surfaces: dismiss-in-flight
	 * ($_GET), date window, visibility, dismissed transient.
	 *
	 * XPO_DEV_MODE forces every start date to 2026-01-01, so upcoming promos
	 * are visible without editing promos/.
	 *
	 * @param array $notice Promo entry.
	 * @return bool
	 */
	private static function promo_is_live( $notice ) {
		$config = self::config();

		if ( empty( $notice['key'] ) ) {
			return false;
		}
		$notice_key  = $notice['key'];
		$dismiss_arg = 'disable_' . $config['prefix'] . '_notice';

		if ( isset( $_GET[ $dismiss_arg ] ) && $notice_key === sanitize_text_field( wp_unslash( $_GET[ $dismiss_arg ] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		$current_time = gmdate( 'U' );
		$start        = defined( 'XPO_DEV_MODE' ) ? '2026-01-01 00:00 Asia/Dhaka' : $notice['start'];
		$notice_start = gmdate( 'U', strtotime( $start ) );
		$notice_end   = gmdate( 'U', strtotime( $notice['end'] ) );

		if ( $current_time < $notice_start || $current_time > $notice_end || empty( $notice['visibility'] ) ) {
			return false;
		}

		if ( 'off' === Xpo::get_transient_without_cache( $config['prefix'] . '_get_pro_notice_' . $notice_key ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Does this plugin have at least one promo eligible to show right now?
	 *
	 * @return bool
	 */
	private function has_active_promo() {
		foreach ( self::get_promos( 'notice' ) as $notice ) {
			$type = isset( $notice['type'] ) ? $notice['type'] : '';
			if ( $this->template_for( $type ) && self::promo_is_live( $notice ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Handle Plugin Notice for all plugins
	 *
	 * @param array $active_lists Lists of all active plugin notice.
	 * @return array
	 */
	public function handle_xpo_active_notice_lists( $active_lists ) {

		if ( $this->has_active_promo() ) {
			$active_lists[ $this->config['priority_key'] ] = $this->config['priority'];
		}

		return $active_lists;
	}

	/**
	 * Handle Plugin Notice for all plugins
	 *
	 * @return bool
	 */
	public function is_available_for_notice() {
		$active_notices = apply_filters( 'xpo_active_notice_lists', array() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- shared "Xpo" notice framework hook used across WPXPO plugins.

		if ( empty( $active_notices ) ) {
			return true;
		}

		asort( $active_notices );

		return array_key_first( $active_notices ) === $this->config['priority_key'];
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

		// Namespace tracks config.php's prefix, so this file carries no plugin
		// literal. The React side must use the same string — see README.
		// example api: revx/v1/hello_bar
		$rest_namespace = $this->config['prefix'] . '/v1';

		foreach ( $routes as $route ) {
			register_rest_route(
				$rest_namespace,
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
	 * The live hello-bar promo, ready to render. The plugin's admin-menu class
	 * localizes this into its JS object under `helloBar`. PHP resolves which
	 * promo is live and builds its URL, so the React bundle holds no promo list
	 * or date logic.
	 *
	 * @return array|null Null when no promo is live (React renders nothing).
	 */
	public static function get_hellobar_config() {
		$promo = null;

		foreach ( self::get_promos( 'hellobar' ) as $candidate ) {
			// Hello-bar dismissal writes 'hide' under the promo key itself, not
			// the notices' transient. Don't unify: it would un-dismiss the bar
			// for everyone who already closed it.
			if ( 'hide' === Xpo::get_transient_without_cache( $candidate['key'] ) ) {
				continue;
			}

			if ( self::promo_is_live( $candidate ) ) {
				$promo = $candidate;
				break;
			}
		}

		if ( ! $promo ) {
			return null;
		}

		$config = self::config();

		return array(
			'id'                => $promo['key'],
			'brandColor'        => $config['brand_color'],
			'text'              => isset( $promo['text'] ) ? $promo['text'] : '',
			'highlight'         => isset( $promo['highlight'] ) ? $promo['highlight'] : '',
			'buttonText'        => isset( $promo['button_text'] ) ? $promo['button_text'] : '',
			'url'               => isset( $promo['url'] ) ? $promo['url'] : '',
			'countdownDuration' => isset( $promo['countdown_duration'] ) ? (int) $promo['countdown_duration'] : 0,
		);
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
		$id             = isset( $request_params['id'] ) ? $request_params['id'] : '';

		$id = is_string( $id ) ? sanitize_text_field( $id ) : '';

		// Only the keys of this plugin's own hello-bar promos name a transient, so a request
		// can't write or overwrite an option of its own choosing.
		if ( 'hello_bar' === $type && '' !== $id ) {
			$hellobar_keys = array();
			foreach ( self::get_promos( 'hellobar' ) as $promo ) {
				if ( ! empty( $promo['key'] ) ) {
					$hellobar_keys[] = (string) $promo['key'];
				}
			}
			if ( ! in_array( $id, $hellobar_keys, true ) ) {
				return new \WP_Error( 'wopb_invalid_hello_bar', __( 'Unknown hello bar.', 'product-blocks' ), array( 'status' => 400 ) );
			}
			// we are setting the transient for 15 days to hide the hello bar.
			Xpo::set_transient_without_cache( $id, 'hide', 15 * DAY_IN_SECONDS );
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
		// Runs on every `admin_init`, including logged-out requests to admin-ajax.php, so it
		// does nothing unless an administrator carries this action's own nonce.
		if ( empty( $_GET['wpnonce'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$prefix = $this->config['prefix'];

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wpnonce'] ) ), $prefix . '-dismiss-nonce' ) ) {
			return;
		}

		// Promo dismiss. Only the keys of the promos this plugin shows are accepted.
		$notice_key = sanitize_text_field( wp_unslash( $_GET[ 'disable_' . $prefix . '_notice' ] ?? '' ) );
		if ( ! empty( $notice_key ) && in_array( $notice_key, $this->known_promo_keys(), true ) ) {
			$interval = absint( sanitize_text_field( wp_unslash( $_GET[ $prefix . '_interval' ] ?? '' ) ) );
			if ( ! empty( $interval ) ) {
				Xpo::set_transient_without_cache( $prefix . '_get_pro_notice_' . $notice_key, 'off', $interval );
			} else {
				Xpo::set_transient_without_cache( $prefix . '_get_pro_notice_' . $notice_key, 'off' );
			}
		}
	}

	/**
	 * Keys of every promo this plugin can show, so a dismiss request can't create a key of its own.
	 *
	 * @return string[]
	 */
	private function known_promo_keys() {
		$keys = array();
		foreach ( array_keys( self::SURFACES ) as $surface ) {
			foreach ( self::get_promos( $surface ) as $promo ) {
				if ( ! empty( $promo['key'] ) ) {
					$keys[] = (string) $promo['key'];
				}
			}
		}
		return $keys;
	}

	/**
	 * Admin Notices Callback
	 *
	 * @return void
	 */
	public function admin_notices_callback() {
		// Promos are for the people who run the store.
		// The WowStore dashboard shows none of them: it has its own hello bar and banner.
		if ( ! current_user_can( 'manage_options' ) || 'wopb-settings' === wopb_function()->get_screen() ) {
			return;
		}

		// Promos show only where they are relevant: the Plugins screen and
		// this plugin's own screens.
		if ( $this->is_available_for_notice() && $this->is_own_or_overview_screen() ) {
			$this->render_notices( self::get_promos( 'notice' ) );
		}
	}

	/**
	 * Render at most one live promo per type (first match wins), each through
	 * its own template. XPO_DEV_MODE drops the one-per-type limit so every
	 * design can be checked at once.
	 *
	 * @param array $promos List of promo entries.
	 * @return void
	 */
	private function render_notices( $promos ) {
		$shown                = array();
		$enforce_one_per_type = ! defined( 'XPO_DEV_MODE' );
		$prefix               = $this->config['prefix'];
		$brand_color          = $this->config['brand_color'];

		foreach ( $promos as $notice ) {
			$type = isset( $notice['type'] ) ? $notice['type'] : '';

			if ( $enforce_one_per_type && isset( $shown[ $type ] ) ) {
				continue;
			}

			$template = $this->template_for( $type );
			if ( ! $template || ! self::promo_is_live( $notice ) ) {
				continue;
			}

			$query_args = array(
				'disable_' . $prefix . '_notice' => $notice['key'],
				'wpnonce'                        => wp_create_nonce( $prefix . '-dismiss-nonce' ),
			);
			if ( ! empty( $notice['repeat_interval'] ) ) {
				$query_args[ $prefix . '_interval' ] = $notice['repeat_interval'];
			}

			// $notice, $query_args, $prefix and $brand_color are in scope for the template.
			include $template;

			$shown[ $type ] = true;
		}
	}

	/**
	 * Whether the current admin screen is Plugins or one of this plugin's own
	 * screens (its pages or post types).
	 *
	 * @return bool
	 */
	private function is_own_or_overview_screen() {
		$pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
		if ( 'plugins.php' === $pagenow ) {
			return true;
		}

		$prefix = $this->config['prefix'];
		$page   = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check.
		if ( 0 === strpos( $page, $prefix ) ) {
			return true;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && ! empty( $screen->post_type ) && 0 === strpos( $screen->post_type, $prefix );
	}
}
