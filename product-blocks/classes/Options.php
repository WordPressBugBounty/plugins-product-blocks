<?php
/**
 * Options Action.
 *
 * @package WOPB\Notice
 * @since v.1.0.0
 */
namespace WOPB;

use WOPB\Includes\Xpo;
use WOPB\Includes\Notice\Notice;

defined( 'ABSPATH' ) || exit;

/**
 * Options class.
 */
class Options {

	/**
	 * Where the "go pro" menu link points, for its redirect page.
	 *
	 * @var string
	 */
	private static $go_pro_url = '';

	/**
	 * Setup class.
	 *
	 * @since v.1.1.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu_page_callback' ) );
		add_action( 'in_admin_header', array( $this, 'relocate_notices' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_settings_meta' ), 10, 2 );
		add_filter( 'plugin_action_links_' . WOPB_BASE, array( $this, 'plugin_action_links_callback' ) );
		add_action( 'add_meta_boxes', array( $this, 'single_product_meta_box' ) );
	}

	/**
	 * Notice callbacks other plugins hooked above the dashboard, held until the dashboard has printed.
	 *
	 * @var array
	 */
	private static $held_notice_hooks = array();

	/**
	 * Hold other plugins' admin notices so they print below the WowStore dashboard instead of above it.
	 *
	 * This screen is a full-width React app (Builder, Template Kits, Settings). A notice of unknown
	 * height and markup printed above it pushes the app's header and builder canvas out of place.
	 * Nothing is removed and no output is buffered: the notice callbacks are taken off the hooks before
	 * WordPress runs them, and `wowstore_dashboard()` runs them again under the app, so each notice
	 * prints itself with its own buttons and scripts. Every other admin screen is untouched.
	 *
	 * @since v.1.0.0
	 * @return void
	 */
	public static function relocate_notices() {
		if ( wopb_function()->get_screen() !== 'wopb-settings' ) {
			return;
		}
		global $wp_filter;
		foreach ( array( 'admin_notices', 'all_admin_notices' ) as $hook ) {
			if ( ! empty( $wp_filter[ $hook ]->callbacks ) ) {
				self::$held_notice_hooks[ $hook ] = $wp_filter[ $hook ]->callbacks;
				remove_all_actions( $hook );
			}
		}
	}

	/**
	 * Print the notices held by `relocate_notices()`.
	 *
	 * @since 4.6.0
	 * @return void
	 */
	private static function print_held_notices() {
		if ( empty( self::$held_notice_hooks ) ) {
			return;
		}
		echo '<div class="wopb-admin-notices" style="margin:20px 20px 0 2px;">';
		// Run the held callbacks directly, in hook and priority order, with the arguments `do_action()` would pass.
		foreach ( self::$held_notice_hooks as $priorities ) {
			foreach ( $priorities as $callbacks ) {
				foreach ( $callbacks as $callback ) {
					call_user_func_array( $callback['function'], array_slice( array( '' ), 0, (int) $callback['accepted_args'] ) );
				}
			}
		}
		echo '</div>';
		self::$held_notice_hooks = array();
	}


	/**
	 * Plugins Settings Meta Menu Add
	 *
	 * @since v.1.0.0
	 * @return NULL
	 */
	public function plugin_settings_meta( $links, $file ) {
		if ( strpos( $file, 'product-blocks.php' ) !== false ) {
			$new_links = array(
				'wopb_docs'     => '<a href="https://wpxpo.com/docs/wowstore/?utm_source=db-wstore-plugin&utm_medium=docs&utm_campaign=wstore-dashboard" target="_blank">' . esc_html__( 'Docs', 'product-blocks' ) . '</a>',
				'wopb_tutorial' => '<a href="https://www.youtube.com/@wpxpo/videos" target="_blank">' . esc_html__( 'Tutorials', 'product-blocks' ) . '</a>',
				'wopb_support'  => '<a href="https://wordpress.org/support/plugin/product-blocks/" target="_blank">' . esc_html__( 'Support', 'product-blocks' ) . '</a>',
			);
			$links     = array_merge( $links, $new_links );
		}
		return $links;
	}


	/**
	 * Plugins Settings Meta Pro Link Add
	 *
	 * @since v.1.1.0
	 * @return NULL
	 */
	public function plugin_action_links_callback( $links ) {
		$setting_link = array(
			'wopb_settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=wopb-settings#settings' ) ) . '">' . esc_html__( 'Settings', 'product-blocks' ) . '</a>',
		);

		$upgrade_link = array();

		// The upgrade link shows while `wopb_show_upsell` is true. With Pro, Pro adds its own
		// renew link to this row through the core `plugin_action_links_{plugin}` filter.
		if ( wopb_function()->show_upsell() ) {
			// Evergreen default. Not a promo, so it stays here rather than in
			// includes/notice/promos/ — a live entry there simply overrides it.
			$text = __( 'Upgrade to Pro', 'product-blocks' );
			$url  = Xpo::generate_utm_link();

			$promo = Notice::get_active_promo( 'plugin-meta' );
			if ( $promo ) {
				$text = $promo['text'];
				$url  = $promo['url'];
			}

			$upgrade_link['wopb_pro'] = '<a style="color: #e83838; font-weight: bold;" target="_blank" href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
		}

		return array_merge( $setting_link, $links, $upgrade_link );
	}


	/**
	 * Plugins Menu Page Added
	 *
	 * @since v.1.0.0
	 * @return NULL
	 */
	public static function menu_page_callback() {
		$menupage_cap = apply_filters( 'wopb_menu_page_capability', 'manage_options' );
		add_menu_page(
			esc_html__( 'WowStore', 'product-blocks' ),
			esc_html__( 'WowStore', 'product-blocks' ),
			$menupage_cap,
			'wopb-settings',
			array( self::class, 'wowstore_dashboard' ),
			WOPB_URL . 'assets/img/logo-sm.svg',
			58.5
		);
		add_submenu_page(
			'wopb-settings',
			esc_html__( 'WowStore Dashboard', 'product-blocks' ),
			esc_html__( 'Addons', 'product-blocks' ),
			$menupage_cap,
			'wopb-settings'
		);
		$menu_lists = array(
			'builder'         => __( 'Woo Builder', 'product-blocks' ),
			'templatekit'     => __( 'Template Kits', 'product-blocks' ),
			'blocks'          => __( 'Blocks', 'product-blocks' ),
			'saved-templates' => __( 'Saved Template', 'product-blocks' ),
			'custom-font'     => __( 'Custom Font', 'product-blocks' ),
		);

		$menu_lists = array_merge( $menu_lists, array( 'size-chart' => __( 'Size Chart', 'product-blocks' ) ) );
		$menu_lists = array_merge(
			$menu_lists,
			array(
				'settings' => __( 'Settings', 'product-blocks' ),
				// 'support'           => __( 'Quick Support', 'product-blocks' )
			)
		);

		foreach ( $menu_lists as $key => $val ) {
			add_submenu_page(
				'wopb-settings',
				$val,
				$val,
				$menupage_cap,
				'wopb-settings#' . $key,
				array( __CLASS__, 'render_main' )
			);
		}

		do_action( 'wowstore_menu' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- established hook name, renaming would break existing integrations.

		// The upgrade link shows while `wopb_show_upsell` is true. With Pro, Pro adds its
		// renew link here through the action below.
		if ( wopb_function()->show_upsell() ) {
			$current_date         = current_time( 'Y-m-d' );
			$menu_pro_text_period = (
				$current_date >= '2026-01-01' &&
				$current_date <= '2026-02-15'
			);
			self::add_go_pro_submenu(
				Xpo::generate_utm_link(
					array(
						'utmKey' => $menu_pro_text_period ? 'new_year_sale' : 'sub_menu',
					)
				),
				$menu_pro_text_period ? __( 'New Year Offer!', 'product-blocks' ) : __( 'Upgrade to Pro', 'product-blocks' )
			);
		}

		do_action( 'wopb_go_pro_menu' );
	}

	/**
	 * Add the highlighted link at the end of the WowStore menu.
	 *
	 * Free uses it for "Upgrade to Pro"; Pro calls it from the `wopb_go_pro_menu` action.
	 *
	 * @since v.4.6.0
	 * @param string $url  Where the link goes.
	 * @param string $text Link text.
	 * @return void
	 */
	public static function add_go_pro_submenu( $url, $text ) {
		if ( empty( $url ) ) {
			return;
		}
		self::$go_pro_url = $url;

		$wopb_buffer_level_1 = ob_get_level();
		ob_start();
		try {
			?>
			<a href="<?php echo esc_url( $url ); ?>" target="_blank" class="wopb-go-pro">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M2.86 6.553a.5.5 0 01.823-.482l3.02 2.745c.196.178.506.13.64-.098L9.64 4.779a.417.417 0 01.72 0l2.297 3.939a.417.417 0 00.64.098l3.02-2.745a.5.5 0 01.823.482l-1.99 8.63a.833.833 0 01-.813.646H5.663a.833.833 0 01-.812-.646L2.86 6.553z" stroke="currentColor" stroke-width="1.5"></path>
				</svg>
				<span><?php echo esc_html( $text ); ?></span>
			</a>
			<?php
			$submenu_content = ob_get_clean();
		} finally {
			while ( ob_get_level() > $wopb_buffer_level_1 ) {
				if ( ! ob_end_clean() ) {
					break;
				}
			}
		}

		add_submenu_page(
			'wopb-settings',
			'',
			$submenu_content,
			'manage_options',
			'wopb-pro',
			array( self::class, 'handle_external_redirects' )
		);
	}


	public static function handle_external_redirects() {
		// WordPress sets $plugin_page from the requested admin page before it runs this page callback.
		global $plugin_page;
		if ( 'wopb-pro' === $plugin_page ) {
			$redirect_url = self::$go_pro_url ? self::$go_pro_url : wopb_function()->get_premium_link( '', 'main_menu_go_pro' );
			wp_redirect( $redirect_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- the highlighted WowStore menu item (free's upgrade link, or the URL Pro passes to add_go_pro_submenu()) goes to the plugin's own wpxpo.com page, an external host wp_safe_redirect() would refuse.
			die();
		}
	}

	/**
	 * Content of Tab Page
	 *
	 * @return STRING
	 */
	public static function wowstore_dashboard() {
		echo '<div id="wopb-dashboard"></div>';

		self::print_held_notices();
	}

	/**
	 * Single Product Meta Box
	 *
	 * @return void
	 * @since v.4.0.0
	 */
	public function single_product_meta_box() {
		if ( wopb_function()->get_setting( 'wopb_builder' ) === 'true' && current_user_can( 'manage_options' ) ) {
			add_meta_box(
				'wopb-single-product-meta-box',
				'<div class="wopb-single-product-meta-box"><img src="' . esc_url( WOPB_URL . 'assets/img/logo-sm.svg' ) . '" /><span>' . esc_html__( 'WowStore Settings', 'product-blocks' ) . '</span></div>',
				array( $this, 'builder_product_metabox_html' ),
				'product',
				'side',
				'core'
			);
		}
	}

	public function builder_product_metabox_html() {
		?>
		<div class="wopb-meta-builder">
			<a class="wopb-dash-builder-btn" target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wopb-settings#builder' ) ); ?>"><?php echo esc_html__( 'Enable Product Single Builder', 'product-blocks' ); ?></a>
		</div>
		<?php
	}
}
