<?php
/**
 * Payment Gateways by User Roles for WooCommerce - Settings
 *
 * @version 1.1.1
 * @since   1.0.0
 * @author  Imaginate Solutions
 * @package pgur
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Settings_Payment_Gateways_By_User_Roles' ) ) :

	/**
	 * Settings Class.
	 */
	class Alg_WC_Settings_Payment_Gateways_By_User_Roles extends WC_Settings_Page {

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			$this->id    = 'alg_wc_payment_gateways_by_user_roles';
			$this->label = __( 'Payment Gateways by User Roles', 'payment-gateways-by-user-roles-for-woocommerce' );
			parent::__construct();
		}

		/**
		 * Get Settings Array.
		 *
		 * @version 1.1.1
		 * @since   1.0.0
		 */
		public function get_settings() {
			global $current_section;
			return array_merge(
				apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() ),
				array(
					array(
						'title' => __( 'Reset Settings', 'payment-gateways-by-user-roles-for-woocommerce' ),
						'type'  => 'title',
						'id'    => $this->id . '_' . $current_section . '_reset_options',
					),
					array(
						'title'   => __( 'Reset section settings', 'payment-gateways-by-user-roles-for-woocommerce' ),
						'desc'    => '<strong>' . __( 'Reset', 'payment-gateways-by-user-roles-for-woocommerce' ) . '</strong>',
						'id'      => $this->id . '_' . $current_section . '_reset',
						'default' => 'no',
						'type'    => 'checkbox',
					),
					array(
						'type' => 'sectionend',
						'id'   => $this->id . '_' . $current_section . '_reset_options',
					),
				)
			);
		}

		/**
		 * Reset Settings.
		 *
		 * @version 1.1.1
		 * @since   1.0.0
		 */
		public function maybe_reset_settings() {
			global $current_section;
			if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
				foreach ( $this->get_settings() as $value ) {
					if ( isset( $value['id'] ) ) {
						$id = explode( '[', $value['id'] );
						delete_option( $id[0] );
					}
				}
				add_action( 'admin_notices', array( $this, 'admin_notice_settings_reset' ) );
			}
		}

		/**
		 * Admin notice for Reset.
		 *
		 * @version 1.1.1
		 * @since   1.1.1
		 */
		public function admin_notice_settings_reset() {
			echo '<div class="notice notice-warning is-dismissible"><p><strong>' .
			esc_html__( 'Your settings have been reset.', 'payment-gateways-by-user-roles-for-woocommerce' ) . '</strong></p></div>';
		}

		/**
		 * Save settings.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function save() {
			parent::save();
			$this->maybe_reset_settings();
		}

	}

endif;

return new Alg_WC_Settings_Payment_Gateways_By_User_Roles();
