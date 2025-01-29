<?php
/**
 * Options Action.
 *
 * @package WOPB\Notice
 * @since v.1.0.0
 */
namespace WOPB;

defined('ABSPATH') || exit;

/**
 * Options class.
 */
class Options{

    /**
     * Setup class.
     *
     * @since v.1.1.0
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'menu_page_callback' ) );
        add_action( 'in_admin_header', array( $this, 'remove_all_notices' ) );
        add_filter( 'plugin_row_meta', array( $this, 'plugin_settings_meta' ), 10, 2 );
        add_filter( 'plugin_action_links_' . WOPB_BASE, array( $this, 'plugin_action_links_callback' ) );
        add_action( 'add_meta_boxes', array( $this, 'single_product_meta_box' ) );
    }

    /**
     * Remove All Notification From Menu Page
     *
     * @since v.1.0.0
     * @return NULL
     */
    public static function remove_all_notices() {
        if (
            wopb_function()->get_screen() == 'wopb-settings' ||
            wopb_function()->get_screen() == 'wopb-initial-setup-wizard'
        ) {
            remove_all_actions( 'admin_notices' );
            remove_all_actions( 'all_admin_notices' );
        }
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
                'wopb_docs'     =>  '<a href="https://wpxpo.com/docs/wowstore/?utm_source=db-wstore-plugin&utm_medium=docs&utm_campaign=wstore-dashboard" target="_blank">' . esc_html__( 'Docs', 'product-blocks' ) . '</a>',
                'wopb_tutorial' =>  '<a href="https://www.youtube.com/@wpxpo/videos" target="_blank">' . esc_html__( 'Tutorials', 'product-blocks' ) . '</a>',
                'wopb_support'  =>  '<a href="https://www.wpxpo.com/contact/?utm_source=db-wstore-plugin&utm_medium=quick-support&utm_campaign=wstore-dashboard" target="_blank">' . esc_html__( 'Support', 'product-blocks' ) . '</a>'
            );
            $links = array_merge( $links, $new_links );
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
        $upgrade_link = array();
        $setting_link = array();
        if ( ! wopb_function()->isPro() ) {
            $upgrade_link = array(
                'wopb_pro' => '<a href="'.esc_url( wopb_function()->get_premium_link( '', 'plugin_list_productx_go_pro' ) ) . '" target="_blank"><span style="color: #c51173; font-weight: bold;">' . esc_html__( 'Upgrade to Pro', 'product-blocks' ) . '</span></a>'
            );

            $notice = [];
            /*
            // If you want to apply discount for lifetime users and other users
            $discount = 50;
            $license_expire = get_option( 'edd_wopb_license_expire' );
            if (
                wopb_function()->get_setting( 'is_lc_active' ) 
                && (
                    ( $license_expire == 'lifetime' && get_option( 'edd_wopb_license_activations_left' ) != 'unlimitedss' )
                    || $license_expire != 'lifetime' 
                )
            ) {
                $discount = 55;
            }
            $notice =  array(
                'start' => '12-2-2023', // Date format "d-m-Y" [08-02-2019]
                'end' => '22-07-2024',
                'content' => 'Upgrade ' . $discount . '% off Sale!'
            );
            */
            if ( count( $notice ) > 0 ) {
                $current_time = gmdate( 'U' );
                if ( $current_time > strtotime( $notice['start'] ) && $current_time < strtotime( $notice['end'] ) ) {
                    $upgrade_link['wopb_pro'] = '<a href="'.esc_url( wopb_function()->get_premium_link( '', 'plugin_dir_pro' ) ).'" target="_blank"><span style="color: #e83838; font-weight: bold;">'.$notice['content'].'</span></a>';
                }
            }
        }

        $setting_link['wopb_settings'] = '<a href="' . esc_url( admin_url( 'admin.php?page=wopb-settings#settings' ) ) .'">'. esc_html__( 'Settings', 'product-blocks' ) .'</a>';
        return array_merge( $setting_link, $links, $upgrade_link );
    }


    /**
     * Plugins Menu Page Added
     *
     * @since v.1.0.0
     * @return NULL
     */
    public static function menu_page_callback() {
        $menupage_cap = apply_filters('wopb_menu_page_capability','manage_options');
        add_menu_page(
            esc_html__( 'WowStore', 'product-blocks' ),
            esc_html__( 'WowStore', 'product-blocks' ),
            $menupage_cap,
            'wopb-settings',
            array( self::class, 'wowstore_dashboard' ),
            plugins_url( 'product-blocks/assets/img/logo-sm.svg' ),
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
            'builder'           => __( 'Woo Builder', 'product-blocks' ),
            'templatekit'       => __( 'Template Kits', 'product-blocks' ),
            'blocks'            => __( 'Blocks', 'product-blocks' ),
            'saved-templates'   => __( 'Saved Template', 'product-blocks' ),
            'custom-font'       => __( 'Custom Font', 'product-blocks' ),
        );

        if( wopb_function()->is_lc_active() ) {
            $menu_lists = array_merge($menu_lists, array('size-chart' => __( 'Size Chart', 'product-blocks' )));
        }
        $menu_lists = array_merge(
            $menu_lists,
            array(
                'revenue'           => __( 'Revenue', 'product-blocks' ) . '<span class="wopb-revenue-tag">New</span>',
                'settings'          => __( 'Settings', 'product-blocks' ),
                'license'           => __( 'License', 'product-blocks' ),
                'support'           => __( 'Quick Support', 'product-blocks' )
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

        do_action( 'wowstore_menu' );
        
        if ( ! wopb_function()->isPro() ) {
            add_submenu_page(
                'wopb-settings',
                '',
                '<span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . esc_html__( 'Upgrade to Pro', 'product-blocks' ),
                'manage_options',
                'go_productx_pro',
                array( self::class, 'handle_external_redirects' )
            );
        }
    }


    public static function handle_external_redirects() {
        if ( empty( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }
        if ( wopb_function()->get_screen() === 'go_productx_pro' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            wp_redirect( wopb_function()->get_premium_link( '', 'main_menu_go_pro' ) ); //phpcs:ignore
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
    }

    /**
     * Single Product Meta Box
     *
     * @return void
     * @since v.4.0.0
     */
    public function single_product_meta_box() {
        if ( wopb_function()->get_setting('wopb_builder') == 'true' && current_user_can( 'manage_options' ) ) {
            add_meta_box(
                'wopb-single-product-meta-box',
                '<div class="wopb-single-product-meta-box"><img src="' . WOPB_URL . 'assets/img/logo-sm.svg" /><span>WowStore Settings</span></div>',
                array( $this, 'builder_product_metabox_html' ),
                'product',
                'side',
                'core',
            );
        }
    }

    public function builder_product_metabox_html() { ?>
        <div class="wopb-meta-builder">
            <a class="wopb-dash-builder-btn" target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wopb-settings#builder' ) ); ?>"><?php echo esc_html__('Enable Product Single Builder', 'product-blocks'); ?></a>
        </div>
    <?php }
}