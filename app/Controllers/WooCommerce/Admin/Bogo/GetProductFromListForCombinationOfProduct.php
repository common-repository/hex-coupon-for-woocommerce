<?php
namespace hexcoupon\app\Controllers\WooCommerce\Admin\Bogo;

use HexCoupon\App\Controllers\BaseController;
use HexCoupon\App\Core\Lib\SingleTon;

class GetProductFromListForCombinationOfProduct extends BaseController
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method combination_of_product_against_any_product_listed_below
	 * @return void
	 * Customer gets any product listed below against a combination of product
	 */
	public function combination_of_product_against_any_product_listed_below( $customer_purchases, $customer_gets_as_free, $main_product_id, $coupon_id, $free_item_id, $wc_cart, $selected_products_as_free )
	{
		$hexcoupon_bogo_instance = HexcouponBogoController::getInstance();

		if ( 'any_products_listed_below' === $customer_gets_as_free && 'a_combination_of_products' === $customer_purchases ) {
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				// Checking if the cart has all products that the store owner has selected to purchase
				if ( in_array( $cart_item['product_id'], $main_product_id ) ) {
					$product_title = HexcouponBogoController::getInstance()->convert_and_replace_unnecessary_string( $cart_item['product_id'] );

					$main_product_min_quantity = get_post_meta( $coupon_id, $product_title . '-purchased_min_quantity', true );

					if ( $cart_item['quantity'] >= $main_product_min_quantity ) {
						$is_main_product_greater_or_equal_to_min = true;
					}
					else {
						$is_main_product_greater_or_equal_to_min = false;
						// Show error message to the user if main product quantity is less than the store owner has selected
						add_action( 'woocommerce_before_cart', [ $hexcoupon_bogo_instance, 'cart_custom_error_message' ] );
						break;
					}
				}
			}

			if ( $is_main_product_greater_or_equal_to_min ) {
				add_action( 'woocommerce_after_cart_table', [ $hexcoupon_bogo_instance, 'custom_content_below_coupon_button' ] );

				HexcouponBogoController::getInstance()->update_quantity_after_updating_cart( $coupon_id, $free_item_id, $main_product_id, $customer_purchases );
			}
			else {
				// Removing free product from the list if it does not satisfy the rules
				HexcouponBogoController::getInstance()->remove_cart_product( $selected_products_as_free );
			}

			// Removing product if customer tries to add more than one product from the list.
			HexcouponBogoController::getInstance()->remove_product_in_case_of_any_product_listed_below( $wc_cart, $selected_products_as_free );
		}
	}
}
