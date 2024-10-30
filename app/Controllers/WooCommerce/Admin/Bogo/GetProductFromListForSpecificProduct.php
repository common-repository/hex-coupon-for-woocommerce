<?php
namespace hexcoupon\app\Controllers\WooCommerce\Admin\Bogo;

use HexCoupon\App\Controllers\BaseController;
use HexCoupon\App\Core\Lib\SingleTon;

class GetProductFromListForSpecificProduct extends BaseController
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method specific_products_against_any_product_listed_below
	 * @return void
	 * Customer gets any product listed below against a specific product
	 */
	public function specific_products_against_any_product_listed_below( $customer_purchases, $customer_gets_as_free, $main_product_min_purchased_quantity, $cart_item_quantity, $free_item_id, $wc_cart, $selected_products_as_free, $coupon_id, $main_product_id )
	{
		$hexcoupon_bogo_instance = HexcouponBogoController::getInstance();

		if ( 'any_products_listed_below' === $customer_gets_as_free && 'a_specific_product' === $customer_purchases ) {
			if ( $cart_item_quantity < $main_product_min_purchased_quantity ) {
				// Remove free item from cart, if '$main_product_min_purchased_quantity' is less than the '$cart_item_quantity'
				HexcouponBogoController::getInstance()->remove_cart_product( $free_item_id );

				// Show error message to the user if main product quantity is less than the store owner has selected
				add_action( 'woocommerce_before_cart', [ $hexcoupon_bogo_instance, 'cart_custom_error_message' ] );
			}

			add_action( 'woocommerce_after_cart_table', [ $hexcoupon_bogo_instance, 'custom_content_below_coupon_button' ] );

			HexcouponBogoController::getInstance()->remove_product_in_case_of_any_product_listed_below( $wc_cart, $selected_products_as_free );

			HexcouponBogoController::getInstance()->update_quantity_after_updating_cart( $coupon_id, $free_item_id, $main_product_id, $customer_purchases );
		}
	}
}
