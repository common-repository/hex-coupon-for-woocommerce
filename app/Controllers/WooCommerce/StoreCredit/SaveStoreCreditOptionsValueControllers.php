<?php

namespace HexCoupon\app\Controllers\WooCommerce\StoreCredit;

use HexCoupon\App\Controllers\BaseController;
use HexCoupon\App\Core\Helpers\StoreCreditPaymentHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

class SaveStoreCreditOptionsValueControllers extends BaseController
{
	use SingleTon;

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		// Save store credit checkbox field value to order meta for legacy checkout page
		add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'save_store_credit_checkbox_field' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_store_credit_checkbox_field
	 * @return void
	 * Saving the store credit checkbox field value in the checkout page.
	 */
	public function save_store_credit_checkbox_field( $order_id )
	{
		if ( isset( $_POST['store_credit_checkbox'] ) && $_POST['store_credit_checkbox'] != 0 ) {
			update_post_meta( $order_id, 'store_credit_checkbox', 'yes' );
			$order = wc_get_order( $order_id );

			$total = $order->get_total();
			$total_available_store_credit = StoreCreditPaymentHelpers::getInstance()->show_total_remaining_amount();

			if ( $total_available_store_credit > $total ) {
				$deducted_credit = $total;

				update_post_meta( $order_id, 'deducted_store_credit_amount', $deducted_credit );
				// Deducting the store credit balance from the 'hex_store_credit' table
				StoreCreditPaymentHelpers::getInstance()->deduct_store_credit( $deducted_credit );

				// Sending logs after checkout completion in the 'hex_store_credit_logs' table
				StoreCreditPaymentHelpers::getInstance()->send_log_for_store_credit_order_purchase( $order_id, $deducted_credit );
			}
			if ( $total_available_store_credit < $total ) {
				$deducted_credit = $total_available_store_credit;

				update_post_meta( $order_id, 'deducted_store_credit_amount', $deducted_credit );

				// Deducting the store credit balance from the 'hex_store_credit' table
				StoreCreditPaymentHelpers::getInstance()->deduct_store_credit( $deducted_credit );

				// Inserting logs in the 'hex_store_credit_logs' table after successful order in the checkout page of legacy form.
				StoreCreditPaymentHelpers::getInstance()->send_log_for_store_credit_order_purchase( $order_id, $deducted_credit );
			}
		}
	}

}
