<?php
/**
 * Payment Gateways by User Roles for WooCommerce - Section Settings
 *
 * @version 1.1.0
 * @since   1.0.0
 * @author  Imaginate Solutions
 * @package pgur
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Payment_Gateways_By_User_Roles_Settings_Section' ) ) :

	/**
	 * Settings Section.
	 */
	class Alg_WC_Payment_Gateways_By_User_Roles_Settings_Section {

		/**
		 * section id
		 * @var int $id
		 */
		public $id;

		/**
		 * Constructor.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 */
		public function __construct() {
			add_filter( 'woocommerce_get_sections_alg_wc_payment_gateways_by_user_roles', array( $this, 'settings_section' ) );
			add_filter( 'woocommerce_get_settings_alg_wc_payment_gateways_by_user_roles_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
		}

		/**
		 * Settings section.
		 *
		 * @param array $sections Section Array.
		 * @return array
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function settings_section( $sections ) {
			$sections[ $this->id ] = $this->desc;
			return $sections;
		}

	}

endif;
