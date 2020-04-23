<?php
/*
Plugin Name: Payment Gateways by User Roles for WooCommerce
Plugin URI: https://wpfactory.com/item/payment-gateways-by-user-roles-for-woocommerce/
Description: Set user roles to include/exclude for WooCommerce payment gateways to show up.
Version: 1.2.2
Author: Tyche Softwares
Author URI: https://tychesoftwares.com
Text Domain: payment-gateways-by-user-roles-for-woocommerce
Domain Path: /langs
Copyright: � 2020 Tyche Softwares
WC tested up to: 4.0
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'payment-gateways-by-user-roles-for-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return
	$plugin = 'payment-gateways-by-user-roles-for-woocommerce-pro/payment-gateways-by-user-roles-for-woocommerce-pro.php';
	if (
		in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
		( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

if ( ! class_exists( 'Alg_WC_Payment_Gateways_by_User_Roles' ) ) :

/**
 * Main Alg_WC_Payment_Gateways_by_User_Roles Class
 *
 * @class   Alg_WC_Payment_Gateways_by_User_Roles
 * @version 1.2.1
 * @since   1.0.0
 */
final class Alg_WC_Payment_Gateways_by_User_Roles {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '1.2.2';

	/**
	 * @var   Alg_WC_Payment_Gateways_by_User_Roles The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_Payment_Gateways_by_User_Roles Instance
	 *
	 * Ensures only one instance of Alg_WC_Payment_Gateways_by_User_Roles is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @static
	 * @return  Alg_WC_Payment_Gateways_by_User_Roles - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_Payment_Gateways_by_User_Roles Constructor.
	 *
	 * @version 1.2.1
	 * @since   1.0.0
	 * @access  public
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'payment-gateways-by-user-roles-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Pro
		if ( 'payment-gateways-by-user-roles-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
			require_once( 'includes/pro/class-alg-wc-payment-gateways-by-user-roles-pro.php' );
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.2.1
	 * @since   1.0.0
	 */
	function includes() {
		// Core
		$this->core = require_once( 'includes/class-alg-wc-payment-gateways-by-user-roles-core.php' );
	}

	/**
	 * admin.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function admin() {
		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		require_once( 'includes/settings/class-alg-wc-payment-gateways-by-user-roles-settings-section.php' );
		$this->settings = array();
		$this->settings['general'] = require_once( 'includes/settings/class-alg-wc-payment-gateways-by-user-roles-settings-general.php' );
		// Version update
		if ( get_option( 'alg_wc_payment_gateways_by_user_roles_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_payment_gateways_by_user_roles' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if ( 'payment-gateways-by-user-roles-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a target="_blank" href="https://wpfactory.com/item/payment-gateways-by-user-roles-for-woocommerce/">' .
				__( 'Unlock All', 'payment-gateways-by-user-roles-for-woocommerce' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Add Payment Gateways by User Roles settings tab to WooCommerce settings.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once( 'includes/settings/class-alg-wc-settings-payment-gateways-by-user-roles.php' );
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function version_updated() {
		update_option( 'alg_wc_payment_gateways_by_user_roles_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'alg_wc_payment_gateways_by_user_roles' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Payment_Gateways_by_User_Roles to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_Payment_Gateways_by_User_Roles
	 */
	function alg_wc_payment_gateways_by_user_roles() {
		return Alg_WC_Payment_Gateways_by_User_Roles::instance();
	}
}

alg_wc_payment_gateways_by_user_roles();
