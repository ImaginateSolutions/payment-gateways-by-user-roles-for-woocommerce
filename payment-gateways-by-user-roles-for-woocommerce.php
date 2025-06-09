<?php
/**
 * Plugin Name: Payment Gateways by User Roles for WooCommerce
 * Plugin URI: https://imaginate-solutions.com/downloads/payment-gateways-by-user-roles-for-woocommerce/
 * Description: Set user roles to include/exclude for WooCommerce payment gateways to show up.
 * Version: 1.3.0
 * Author: Imaginate Solutions
 * Author URI: https://imaginate-solutions.com
 * Text Domain: payment-gateways-by-user-roles-for-woocommerce
 * Domain Path: /langs
 * Copyright: © 2023 Imaginate Solutions
 * Requires PHP: 7.0
 * WC requires at least: 3.0.0
 * WC tested up to: 8.2
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package pgur
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check if WooCommerce is active.
$plugin_woo = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin_woo, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) &&
	! ( is_multisite() && array_key_exists( $plugin_woo, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'payment-gateways-by-user-roles-for-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return.
	$plugin_pro = 'payment-gateways-by-user-roles-for-woocommerce-pro/payment-gateways-by-user-roles-for-woocommerce-pro.php';
	if (
		in_array( $plugin_pro, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) ||
		( is_multisite() && array_key_exists( $plugin_pro, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

register_activation_hook( __FILE__, function() {
	if ( ! get_option( 'pgur_install_time' ) ) {
		update_option( 'pgur_install_time', time() );
	}
});

if ( ! class_exists( 'Alg_WC_Payment_Gateways_By_User_Roles' ) ) :

	/**
	 * Main Alg_WC_Payment_Gateways_By_User_Roles Class
	 *
	 * @class   Alg_WC_Payment_Gateways_By_User_Roles
	 * @version 1.2.1
	 * @since   1.0.0
	 */
	final class Alg_WC_Payment_Gateways_By_User_Roles {

		/**
		 * Plugin version.
		 *
		 * @var   string
		 * @since 1.0.0
		 */
		public $version = '1.3.0';

		/**
		 * Plugin Instance.
		 *
		 * @var   Alg_WC_Payment_Gateways_By_User_Roles The single instance of the class
		 * @since 1.0.0
		 */
		protected static $plugin_instance = null;

		/**
 		* Core instance for frontend and admin.
 		* @var object $core
 		*/
		protected $core = '';

		/**
		 * Settings tab for wooCommerce setting.
		 * @var string $settings
		 */
		public $settings = '';

		/**
		 * Main Alg_WC_Payment_Gateways_By_User_Roles Instance
		 *
		 * Ensures only one instance of Alg_WC_Payment_Gateways_By_User_Roles is loaded or can be loaded.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @static
		 * @return  Alg_WC_Payment_Gateways_By_User_Roles - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$plugin_instance ) ) {
				self::$plugin_instance = new self();
			}
			return self::$plugin_instance;
		}

		/**
		 * Alg_WC_Payment_Gateways_By_User_Roles Constructor.
		 *
		 * @version 1.2.1
		 * @since   1.0.0
		 * @access  public
		 */
		public function __construct() {

			// Set up localisation.
			load_plugin_textdomain( 'payment-gateways-by-user-roles-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

			// Pro.
			if ( 'payment-gateways-by-user-roles-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
				require_once 'includes/pro/class-alg-wc-payment-gateways-by-user-roles-pro.php';
			}

			// Include required files.
			$this->includes();

			// Admin.
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
		public function includes() {
			// Core.
			$this->core = require_once 'includes/class-alg-wc-payment-gateways-by-user-roles-core.php';
		}

		/**
		 * Admin Actions.
		 *
		 * @version 1.1.0
		 * @since   1.1.0
		 */
		public function admin() {
			//HPOS compatibilty
			add_action( 'before_woocommerce_init', array( $this, 'pgur_declare_hpos_compatibility' ) );
			// Action links.
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
			// Settings.
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			require_once 'includes/settings/class-alg-wc-payment-gateways-by-user-roles-settings-section.php';
			$this->settings            = array();
			$this->settings['general'] = require_once 'includes/settings/class-alg-wc-payment-gateways-by-user-roles-settings-general.php';
			// Version update.
			if ( get_option( 'alg_wc_payment_gateways_by_user_roles_version', '' ) !== $this->version ) {
				add_action( 'admin_init', array( $this, 'version_updated' ) );
			}
			// Show the review notice.
			add_action( 'admin_notices', array( $this, 'show_review_notice' ) );
			// Handle AJAX dismiss.
			add_action( 'wp_ajax_pgur_dismiss_review_notice', array( $this, 'dismiss_review_notice' ) );
		}

		/**
		 * Declare HPOS Compatible
		 *
		 * @return void
		 */
		public function pgur_declare_hpos_compatibility() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 * @param   mixed $links Links.
		 * @return  array
		 */
		public function action_links( $links ) {
			$custom_links   = array();
			$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_payment_gateways_by_user_roles' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
			if ( 'payment-gateways-by-user-roles-for-woocommerce.php' === basename( __FILE__ ) ) {
				$custom_links[] = '<a target="_blank" href="https://imaginate-solutions.com/downloads/payment-gateways-by-user-roles-for-woocommerce/">' .
				__( 'Unlock All', 'payment-gateways-by-user-roles-for-woocommerce' ) . '</a>';
			}
			return array_merge( $custom_links, $links );
		}

		/**
		 * Add Payment Gateways by User Roles settings tab to WooCommerce settings.
		 *
		 * @param array $settings Settings.
		 * @return array
		 * @version 1.1.0
		 * @since   1.0.0
		 */
		public function add_woocommerce_settings_tab( $settings ) {
			$settings[] = require_once 'includes/settings/class-alg-wc-settings-payment-gateways-by-user-roles.php';
			return $settings;
		}

		/**
		 * Version_updated.
		 *
		 * @version 1.1.0
		 * @since   1.1.0
		 */
		public function version_updated() {
			update_option( 'alg_wc_payment_gateways_by_user_roles_version', $this->version );
		}

		/**
		 * Get the plugin url.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return  string
		 */
		public function plugin_url() {
			return untrailingslashit( plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return  string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
		/**
		 * Show a dismissible admin review notice after a set time from activation.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 * @return void
		 */
		public function show_review_notice() {
			// Only admins.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			// Only if not dismissed.
			if ( get_option( 'pgur_review_notice_dismissed' ) ) {
				return;
			}
			// Only after 1 week (604800 seconds) from installation.
			$install_time = get_option( 'pgur_install_time', 0 );
			if ( ! $install_time || ( time() - intval( $install_time ) < 604800 ) ) { // 604800 = 1 week
				return;
			}
			$nonce = wp_create_nonce( 'pgur_dismiss_notice' );
			?>
			<div class="notice notice-info is-dismissible pgur-review-notice">
				<p>
					🎉 <strong><?php esc_html_e( 'Thanks for using Payment Gateways by User Roles for WooCommerce!', 'payment-gateways-by-user-roles-for-woocommerce' ); ?></strong><br>
					<?php esc_html_e( 'If our plugin helps your workflow, please consider', 'payment-gateways-by-user-roles-for-woocommerce' ); ?>
					<a href="https://wordpress.org/support/plugin/payment-gateways-by-user-roles-for-woocommerce/reviews/?rate=5#new-post" target="_blank"><?php esc_html_e( 'leaving a 5-star review', 'payment-gateways-by-user-roles-for-woocommerce' ); ?></a> 💖
				</p>
			</div>
			<script>
			(function($){
				$(document).on('click', '.pgur-review-notice .notice-dismiss', function(){
					$.post(ajaxurl, {
						action: 'pgur_dismiss_review_notice',
						_nonce: '<?php echo esc_js( $nonce ); ?>'
					});
				});
			})(jQuery);
			</script>
			<?php
		}

		/**
		 * Handle AJAX request to dismiss the admin review notice.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 * @return void
		 */
		public function dismiss_review_notice() {
			check_ajax_referer( 'pgur_dismiss_notice', '_nonce' );
			update_option( 'pgur_review_notice_dismissed', 1 );
			wp_die();
		}

	}

endif;

if ( ! function_exists( 'alg_wc_payment_gateways_by_user_roles' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Payment_Gateways_By_User_Roles to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_Payment_Gateways_By_User_Roles
	 */
	function alg_wc_payment_gateways_by_user_roles() {
		return Alg_WC_Payment_Gateways_By_User_Roles::instance();
	}
}

alg_wc_payment_gateways_by_user_roles();
