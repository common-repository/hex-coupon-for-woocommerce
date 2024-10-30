<?php
namespace HexCoupon\App\Core\WooCommerce\CheckoutBlock;

use HexCoupon\App\Core\Helpers\StoreCreditPaymentHelpers;
use HexCoupon\App\Core\Lib\SingleTon;
use HexCoupon\App\Core\WooCommerce\StoreCredit\AddStoreCreditDeductionRow;
use HexCoupon\App\Traits\NonceVerify;

class StoreBlock {

	use NonceVerify, SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method __construct
	 * @return void
	 */
	public function register()
	{
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', [ &$this, 'update_order_meta_for_store_credit' ], 10, 2 );
		add_action( 'admin_post_store_credit_deduction_and_enable_save', [ $this, 'store_credit_deduction_and_enable_save' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method store_credit_deduction_and_enable_save
	 * @return void
	 * Getting store credit amount that is deducted from the main order total in checkout block
	 */
	public function store_credit_deduction_and_enable_save()
	{
		if ( $this->verify_nonce('POST') ) {
			$formData = json_encode( $_POST );

			$dataArray = json_decode( $formData, true );

			$deducted_store_credit = [
				'deducted_store_credit' => $dataArray['deductedStoreCredit'],
			];

			if ( session_status() === PHP_SESSION_NONE ) {
				session_start();
			}
			$_SESSION['deducted_store_credit_amount'] = $deducted_store_credit['deducted_store_credit'];

			wp_send_json( $_POST );
		}else {
			wp_send_json( [
				'error' => 'Nonce verification failed',
			], 403); // 403 Forbidden status code
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method update_order_meta_for_store_credit
	 * @return void
	 * Updating order meta and storing 'deducted_store_credit' amount in the order details of checkout block page
	 */
	public static function update_order_meta_for_store_credit( $order, $request )
	{
		$data = isset( $request['extensions']['hex-coupon-for-woocommerce'] ) ? $request['extensions']['hex-coupon-for-woocommerce'] : [];

		if ( ! empty( $data['use_store_credit'] ) ) {
			$order->update_meta_data( 'use_store_credit', $data['use_store_credit'] );
		}

		if ( ! empty( $data['use_store_credit'] ) && $data['use_store_credit'] == 1 ) {
			if ( session_status() === PHP_SESSION_NONE ) {
				session_start();
			}
			$deducted_store_credit_amount = isset( $_SESSION['deducted_store_credit_amount'] ) ? $_SESSION['deducted_store_credit_amount'] : '';

			$order->update_meta_data( 'deducted_store_credit', $deducted_store_credit_amount );

			/**
			 * Change recurring row position in the order thank_you page order details table
			 */
			$store_credit_deduction_data_in_order_thank_you = AddStoreCreditDeductionRow::getInstance();

			add_filter( 'woocommerce_get_order_item_totals', [ $store_credit_deduction_data_in_order_thank_you, 'change_recurring_row_position' ], 10, 2 );

			// Deducting the store credit balance from the 'hex_store_credit' table
			StoreCreditPaymentHelpers::getInstance()->deduct_store_credit( $deducted_store_credit_amount );

			// Sending logs in the 'hex_store_credit_logs' table after successful order completion with store order
			$order_id = $order->get_id();
			StoreCreditPaymentHelpers::getInstance()->send_log_for_store_credit_order_purchase( $order_id, $deducted_store_credit_amount );
		}
	}
}
