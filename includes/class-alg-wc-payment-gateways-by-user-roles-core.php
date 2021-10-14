<?php
/**
 * Payment Gateways by User Roles for WooCommerce - Core Class
 *
 * @version 1.2.0
 * @since   1.0.0
 * @author  Imaginate Solutions
 * @package pgur
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Payment_Gateways_By_User_Roles_Core' ) ) :

	/**
	 * Core Class.
	 */
	class Alg_WC_Payment_Gateways_By_User_Roles_Core {

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			if ( 'yes' === get_option( 'alg_wc_payment_gateways_by_user_roles_plugin_enabled', 'yes' ) ) {
				add_filter( 'woocommerce_available_payment_gateways', array( $this, 'available_payment_gateways' ), PHP_INT_MAX, 1 );
			}
		}

		/**
		 * Get Current User Roles.
		 *
		 * @param bool $is_single_role Check if its a single role.
		 * @version 1.2.0
		 * @since   1.0.0
		 * @todo    [dev] (maybe) remove `$is_single_role === true` option
		 */
		public function get_current_user_roles( $is_single_role ) {
			$current_user = wp_get_current_user();
			if ( isset( $current_user->roles ) && is_array( $current_user->roles ) && ! empty( $current_user->roles ) ) {
				return ( $is_single_role ? $this->handle_guest_role( reset( $current_user->roles ) ) : array_map( array( $this, 'handle_guest_role' ), $current_user->roles ) );
			} else {
				return ( $is_single_role ? 'guest' : array( 'guest' ) );
			}
		}

		/**
		 * Handke Guest User.
		 *
		 * @param string $role Role.
		 * @return bool
		 * @version 1.2.0
		 * @since   1.2.0
		 * @todo    [dev] `super_admin`
		 */
		public function handle_guest_role( $role ) {
			return ( '' != $role ? $role : 'guest' ); //phpcs:ignore
		}

		/**
		 * Check User Roles.
		 *
		 * @param array $customer_roles Customer Roles.
		 * @param array $roles_to_check Roles to Check.
		 * @param bool  $is_single_role If single Role option is enabled.
		 * @version 1.2.0
		 * @since   1.2.0
		 */
		public function check_user_roles( $customer_roles, $roles_to_check, $is_single_role ) {
			if ( $is_single_role ) {
				return in_array( $customer_roles, $roles_to_check ); //phpcs:ignore
			} else {
				$intersect = array_intersect( $customer_roles, $roles_to_check );
				return ( ! empty( $intersect ) );
			}
		}

		/**
		 * Available payment gateways.
		 *
		 * @param array $_available_gateways Available Gateways.
		 * @return array
		 * @version 1.2.0
		 * @since   1.0.0
		 */
		public function available_payment_gateways( $_available_gateways ) {
			if ( is_array( $_available_gateways ) ) {
				$is_single_role = ( 'first' === get_option( 'alg_wc_payment_gateways_by_user_roles_check_roles', 'all' ) );
				$customer_roles = $this->get_current_user_roles( $is_single_role );
				foreach ( $_available_gateways as $key => $gateway ) {
					$include_roles = get_option( 'alg_wc_gateway_roles_in_' . $key, '' );
					if ( ! empty( $include_roles ) && ! $this->check_user_roles( $customer_roles, $include_roles, $is_single_role ) ) {
						unset( $_available_gateways[ $key ] );
						continue;
					}
					$exclude_roles = get_option( 'alg_wc_gateway_roles_ex_' . $key, '' );
					if ( ! empty( $exclude_roles ) && $this->check_user_roles( $customer_roles, $exclude_roles, $is_single_role ) ) {
						unset( $_available_gateways[ $key ] );
						continue;
					}
				}
			}
			return $_available_gateways;
		}

	}

endif;

return new Alg_WC_Payment_Gateways_By_User_Roles_Core();
