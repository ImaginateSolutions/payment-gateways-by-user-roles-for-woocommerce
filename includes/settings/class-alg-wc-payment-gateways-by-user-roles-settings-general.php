<?php
/**
 * Payment Gateways by User Roles for WooCommerce - General Section Settings
 *
 * @version 1.2.1
 * @since   1.0.0
 * @author  Imaginate Solutions
 * @package pgur
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Payment_Gateways_By_User_Roles_Settings_General' ) ) :

	/**
	 * Settings Class.
	 */
	class Alg_WC_Payment_Gateways_By_User_Roles_Settings_General extends Alg_WC_Payment_Gateways_By_User_Roles_Settings_Section {

		/**
		 * Description for payment gateway
		 * @var string $desc
		 */
		public $desc;

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			$this->id   = '';
			$this->desc = __( 'General', 'payment-gateways-by-user-roles-for-woocommerce' );
			parent::__construct();
		}

		/**
		 * Get user role options.
		 *
		 * @version 1.2.1
		 * @since   1.0.0
		 */
		public function get_user_roles_options() {
			global $wp_roles;
			$all_roles = array_merge(
				array(
					'guest' => array(
						'name'         => __( 'Guest', 'payment-gateways-by-user-roles-for-woocommerce' ),
						'capabilities' => array(),
					),
				),
				apply_filters( 'editable_roles', ( isset( $wp_roles ) && is_object( $wp_roles ) ? $wp_roles->roles : array() ) )
			);
			return wp_list_pluck( $all_roles, 'name' );
		}

		/**
		 * Get Settings.
		 *
		 * @version 1.2.1
		 * @since   1.0.0
		 * @todo    [dev] (maybe) remove `alg_wc_payment_gateways_by_user_roles_plugin_enabled`
		 * @todo    [dev] (maybe) link to some custom roles plugin
		 * @todo    [dev] (maybe) link to some custom payment gateways plugin
		 * @todo    [dev] (maybe) `( isset( $gateway->enabled ) && 'yes' === $gateway->enabled ? '&#9745; ' : '' )`
		 */
		public function get_settings() {
			$main_settings     = array(
				array(
					'title' => __( 'Payment Gateways by User Roles Options', 'payment-gateways-by-user-roles-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_wc_payment_gateways_by_user_roles_plugin_options',
				),
				array(
					'title'    => __( 'Payment Gateways by User Roles', 'payment-gateways-by-user-roles-for-woocommerce' ),
					'desc'     => '<strong>' . __( 'Enable plugin', 'payment-gateways-by-user-roles-for-woocommerce' ) . '</strong>',
					'desc_tip' => __( 'Set user roles to include/exclude for WooCommerce payment gateways to show up.', 'payment-gateways-by-user-roles-for-woocommerce' ),
					'id'       => 'alg_wc_payment_gateways_by_user_roles_plugin_enabled',
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'title'   => __( 'Check user roles', 'payment-gateways-by-user-roles-for-woocommerce' ),
					'id'      => 'alg_wc_payment_gateways_by_user_roles_check_roles',
					'default' => 'all',
					'type'    => 'select',
					'class'   => 'chosen_select',
					'options' => array(
						'all'   => __( 'All roles', 'payment-gateways-by-user-roles-for-woocommerce' ),
						'first' => __( 'First role only', 'payment-gateways-by-user-roles-for-woocommerce' ),
					),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_payment_gateways_by_user_roles_plugin_options',
				),
			);
			$gateways_settings = array();
			$user_roles        = $this->get_user_roles_options();
			$gateways          = WC()->payment_gateways->payment_gateways();
			foreach ( $gateways as $key => $gateway ) {
				$gateways_settings = array_merge(
					$gateways_settings,
					array(
						array(
							'title' => $gateway->title,
							'type'  => 'title',
							'id'    => 'alg_wc_gateway_roles_' . $key,
							'desc'  => ( ! in_array( $key, array( 'bacs', 'cheque', 'cod', 'paypal' ) ) ? apply_filters( //phpcs:ignore
								'alg_wc_payment_gateways_by_user_roles_settings',
								'<span style="background-color: #fafafa; padding: 10px;">' . sprintf(
									'You will need %s plugin to set roles for "%s" gateway.',
									'<a target="_blank" href="https://imaginate-solutions.com/downloads/payment-gateways-by-user-roles-for-woocommerce/">' .
										'Payment Gateways by User Roles for WooCommerce Pro' .
									'</a>',
									$gateway->title
								) . '</span>'
							) : '' ),
						),
						array(
							'title'             => __( 'Include user roles', 'payment-gateways-by-user-roles-for-woocommerce' ),
							'desc_tip'          => __( 'Payment gateway will be available ONLY to selected roles.', 'payment-gateways-by-user-roles-for-woocommerce' ) . ' ' .
								__( 'If set empty - option is ignored.', 'payment-gateways-by-user-roles-for-woocommerce' ),
							'id'                => 'alg_wc_gateway_roles_in_' . $key,
							'default'           => '',
							'type'              => 'multiselect',
							'class'             => 'chosen_select',
							'css'               => 'width: 100%;',
							'options'           => $user_roles,
							'custom_attributes' => array_merge(
								array( 'data-placeholder' => __( 'Select user roles...', 'payment-gateways-by-user-roles-for-woocommerce' ) ),
								( ! in_array( $key, array( 'bacs', 'cheque', 'cod', 'paypal' ) ) ? //phpcs:ignore
								apply_filters( 'alg_wc_payment_gateways_by_user_roles_settings', array( 'disabled' => 'disabled' ), 'array' ) : array() )
							),
						),
						array(
							'title'             => __( 'Exclude user roles', 'payment-gateways-by-user-roles-for-woocommerce' ),
							'desc_tip'          => __( 'Payment gateway will be NOT available to selected roles.', 'payment-gateways-by-user-roles-for-woocommerce' ) . ' ' .
								__( 'If set empty - option is ignored.', 'payment-gateways-by-user-roles-for-woocommerce' ),
							'id'                => 'alg_wc_gateway_roles_ex_' . $key,
							'default'           => '',
							'type'              => 'multiselect',
							'class'             => 'chosen_select',
							'css'               => 'width: 100%;',
							'options'           => $user_roles,
							'custom_attributes' => array_merge(
								array( 'data-placeholder' => __( 'Select user roles...', 'payment-gateways-by-user-roles-for-woocommerce' ) ),
								( ! in_array( $key, array( 'bacs', 'cheque', 'cod', 'paypal' ) ) ? //phpcs:ignore
								apply_filters( 'alg_wc_payment_gateways_by_user_roles_settings', array( 'disabled' => 'disabled' ), 'array' ) : array() )
							),
						),
						array(
							'type' => 'sectionend',
							'id'   => 'alg_wc_gateway_roles_' . $key,
						),
					)
				);
			}
			return array_merge( $main_settings, $gateways_settings );
		}

	}

endif;

return new Alg_WC_Payment_Gateways_By_User_Roles_Settings_General();
