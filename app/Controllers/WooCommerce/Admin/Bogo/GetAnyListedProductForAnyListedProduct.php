<?php
namespace hexcoupon\app\Controllers\WooCommerce\Admin\Bogo;

use HexCoupon\App\Controllers\BaseController;
use HexCoupon\App\Core\Lib\SingleTon;

class GetAnyListedProductForAnyListedProduct extends BaseController
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method any_product_listed_below_against_any_product_listed_below
	 * @return void
	 * Customer gets any product listed below against any product listed below
	 */
	public function any_product_listed_below_against_any_product_listed_below( $customer_purchases, $customer_gets_as_free, $wc_cart, $main_product_id, $coupon_id, $free_item_id, $selected_products_as_free )
	{
		$hexcoupon_bogo_instance = HexcouponBogoController::getInstance();

		if ( 'any_products_listed_below' === $customer_gets_as_free && 'any_products_listed_below' === $customer_purchases ) {
			$main_product_in_cart = false;
			$quantities = $wc_cart->get_cart_item_quantities();

			foreach ( $main_product_id as $main_single_id ) {
				$main_single_key = $wc_cart->generate_cart_id( $main_single_id );

				$main_single_converted_title = HexcouponBogoController::getInstance()->convert_and_replace_unnecessary_string( $main_single_id );
				$main_single_min_quantity = get_post_meta( $coupon_id, $main_single_converted_title . '-purchased_min_quantity', true );

				if ( $wc_cart->find_product_in_cart( $main_single_key ) && $quantities[$main_single_id] >= $main_single_min_quantity ) {
					$main_product_in_cart = true;
					break;
				}
			}

			if ( $main_product_in_cart ) {
				add_action( 'woocommerce_after_cart_table', [ $hexcoupon_bogo_instance, 'custom_content_below_coupon_button' ] );

				HexcouponBogoController::getInstance()->update_quantity_after_updating_cart( $coupon_id, $free_item_id, $main_product_id, $customer_purchases );

				HexcouponBogoController::getInstance()->remove_product_in_case_of_any_product_listed_below( $wc_cart, $selected_products_as_free );
			}
			else {
				HexcouponBogoController::getInstance()->remove_cart_product( $free_item_id );
			}
		}
	}
}
