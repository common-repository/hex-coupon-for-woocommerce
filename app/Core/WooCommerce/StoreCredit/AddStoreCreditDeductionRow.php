<?php

namespace HexCoupon\App\Core\WooCommerce\StoreCredit;

use HexCoupon\App\Controllers\BaseController;
use HexCoupon\App\Core\Lib\SingleTon;

class AddStoreCreditDeductionRow extends BaseController
{
	use SingleTon;

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		// Change recurring row position in the order thank_you page order details table in case of legacy checkout
		add_filter( 'woocommerce_get_order_item_totals', [ $this, 'change_recurring_row_position' ], 10, 2 );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method change_recurring_row_position
	 * @return void
	 * Add store credit deduction data in order thank you page within the order details table
	 */
	public function change_recurring_row_position( $total_rows, $myorder_obj )
	{
		$order_id = $myorder_obj->get_id();
		$order = wc_get_order( $order_id );

		$store_credit_checkbox_checked = get_post_meta( $order_id, 'use_store_credit', true );
		$store_credit_checkbox = get_post_meta( $order_id, 'store_credit_checkbox', true );
		$deducted_store_credit_amount = get_post_meta( $order_id, 'deducted_store_credit_amount', true );
		$deducted_store_credit = $order->get_meta( 'deducted_store_credit' );



		$deducted_store_credit_amount = ! empty( $deducted_store_credit_amount ) ? number_format( $deducted_store_credit_amount, 2 ) : 0;

		if ( $store_credit_checkbox_checked || $store_credit_checkbox === 'yes' ) {
			// Adding a new row in the checkout thank_you page
			$new_row = [
				'recurr_not' => [
					'label' => esc_html__( 'Deducted Store Credit: ', 'hex-coupon-for-woocommerce' ),
					'value' => '-' . esc_html( $deducted_store_credit_amount )
				]
			];

			// Add the new row to the desired position in the array
			$position = 2; // Change this to the desired position
			$total_rows = array_slice( $total_rows, 0, $position, true ) +
				$new_row +
				array_slice( $total_rows, $position, NULL, true );
		}
		// This code of block will be executed if partial payment is enabled in the checkout block
		if ( $deducted_store_credit ) {
			// Define the new row data
			$new_row = [
				'recurr_not' => [
					'label' => esc_html__( 'Deducted Store Credit:', 'hex-coupon-for-woocommerce' ),
					'value' => esc_html( $deducted_store_credit )
				]
			];

			// Add the new row to the desired position in the array
			$position = 2; // Change this to the desired position
			$total_rows = array_slice( $total_rows, 0, $position, true ) +
				$new_row +
				array_slice( $total_rows, $position, NULL, true );
		}

		return $total_rows;
	}

}
