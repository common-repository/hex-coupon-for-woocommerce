<?php
namespace HexCoupon\App\Core\WooCommerce\StoreCredit;

use HexCoupon\App\Core\Helpers\StoreCreditPaymentHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

class AddStoreCreditCheckbox {

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		if ( is_plugin_active( 'hex-coupon-for-woocommerce-pro/hex-coupon-for-woocommerce-pro.php' ) ) {
			$store_credit_payment_data = get_option( 'store_credit_payment_system_data' );

			if ( $store_credit_payment_data['storeCreditPayment'] === 'partial_payment' ) {
				// Add checkbox in the checkout page for applying store credit in the legacy checkout
				add_action( 'woocommerce_review_order_before_submit', [ $this, 'add_store_credit_checkbox_in_checkout' ] );
			}
		} else {
			// Add checkbox in the checkout page for applying store credit in the legacy checkout
			add_action( 'woocommerce_review_order_before_submit', [ $this, 'add_store_credit_checkbox_in_checkout' ] );
		}
	}

	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_store_credit_checkbox_in_checkout
	 * @return void
	 * Adding store credit checkbox in legacy checkout page based on store_credit_enable and partial or full payment of store credit
	 */
	public function add_store_credit_checkbox_in_checkout()
	{
		$store_credit_enable_data = get_option( 'store_credit_enable_data' );

		if ( is_plugin_active( 'hex-coupon-for-woocommerce-pro/hex-coupon-for-woocommerce-pro.php' ) ) {
			$store_credit_payment_system_data = get_option( 'store_credit_payment_system_data' );

			if ( $store_credit_enable_data['enable'] && $store_credit_payment_system_data['storeCreditPayment'] === 'partial_payment' ) {
				$show_total_remaining_amount = StoreCreditPaymentHelpers::getInstance()->show_total_remaining_amount();
				$show_total_remaining_amount = $show_total_remaining_amount ?? 0.0;

				echo '<div class="store-credit-checkbox"><h3>' . esc_html__( 'Available Store Credit: ', 'hex-coupon-for-woocommerce' ) . esc_html( number_format( $show_total_remaining_amount, 2 ) ) . '</h3>';
				woocommerce_form_field( 'store_credit_checkbox', [
					'type' => 'checkbox',
					'class' => array( 'input-checkbox' ),
					'label' => esc_html__( 'Deduct credit amount from total', 'hex-coupon-for-woocommerce' ),
					'required' => false,
				], WC()->checkout->get_value( 'store_credit_checkbox' ) );
				echo '</div>';
			}
		} else {
			if ( $store_credit_enable_data['enable'] ) {
				$show_total_remaining_amount = StoreCreditPaymentHelpers::getInstance()->show_total_remaining_amount();
				$show_total_remaining_amount = $show_total_remaining_amount ?? 0.0;

				echo '<div class="store-credit-checkbox"><h3>' . esc_html__( 'Available Store Credit: ', 'hex-coupon-for-woocommerce' ) . esc_html( number_format( $show_total_remaining_amount, 2 ) ) . '</h3>';
				woocommerce_form_field( 'store_credit_checkbox', [
					'type' => 'checkbox',
					'class' => array( 'input-checkbox' ),
					'label' => esc_html__( 'Deduct credit amount from total', 'hex-coupon-for-woocommerce' ),
					'required' => false,
				], WC()->checkout->get_value( 'store_credit_checkbox' ) );
				echo '</div>';
			}
		}
	}

}
