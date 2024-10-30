<?php
namespace hexcoupon\app\Controllers\WooCommerce\Admin\Bogo;

use HexCoupon\App\Controllers\BaseController;
use HexCoupon\App\Core\Lib\SingleTon;

class GetCombinationOfProductForSpecificProduct extends BaseController
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method specific_products_against_a_combination_of_products
	 * @return void
	 * Customer gets a combination of products against a specific product
	 */
	public function specific_products_against_a_combination_of_products( $customer_purchases, $customer_gets_as_free, $main_product_min_purchased_quantity, $cart_item_quantity, $free_item_id, $coupon_id, $wc_cart, $main_product_id )
	{
		$hexcoupon_bogo_instance = HexcouponBogoController::getInstance();

		// Add product in the case of customer purchases 'a specific product' and getting 'a combination of product' as free
		if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) )
			return;

		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
			return;

		if ( 'a_combination_of_products' === $customer_gets_as_free && 'a_specific_product' === $customer_purchases ) {
			if ( $cart_item_quantity < $main_product_min_purchased_quantity ) {
				// Remove free item from cart, if '$main_product_min_purchased_quantity' is less than the '$cart_item_quantity'
				HexcouponBogoController::getInstance()->remove_cart_product( $free_item_id );

				// Show error message to the user if main product quantity is less than the store owner has selected
				add_action( 'woocommerce_before_cart', [ $hexcoupon_bogo_instance, 'cart_custom_error_message' ] );
			}

			foreach ( $free_item_id as $single_id ) {
				$free_single_title = HexcouponBogoController::getInstance()->convert_and_replace_unnecessary_string( $single_id );

				$single_free_quantity = get_post_meta( $coupon_id, $free_single_title . '-free_product_quantity', true );
				$single_free_quantity = ! empty( $single_free_quantity ) ? $single_free_quantity : 1;

				$single_free_key = $wc_cart->generate_cart_id( $single_id );

				// If the cart item quantity is equal to main purchased product minimum quantity
				if ( ! empty( $free_item_id ) && $cart_item_quantity >= $main_product_min_purchased_quantity ) {
					// If free products does not already exist in the cart page
					if ( ! $wc_cart->find_product_in_cart( $single_free_key ) ) {
						$customer_gets = $single_free_quantity;

						// Finally add the free products in the cart
						$wc_cart->add_to_cart( $single_id, $customer_gets );

						add_action( 'woocommerce_before_cart', [ $hexcoupon_bogo_instance, 'cart_custom_success_message' ] );
					}
					// If free products does already exist in the cart page
					if ( $wc_cart->find_product_in_cart( $single_free_key )  && ! in_array( $single_id , $main_product_id ) ) {
						$customer_gets = $single_free_quantity;

						// Finally update the quantity of the free products
						$wc_cart->set_quantity( $single_free_key, $customer_gets );

						add_action( 'woocommerce_before_cart', [ $hexcoupon_bogo_instance, 'cart_custom_success_message' ] );
					}
				}
			}
		}
	}

}
