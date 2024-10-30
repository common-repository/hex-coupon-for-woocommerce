<?php

namespace hexcoupon\app\Controllers\WooCommerce\Admin\Bogo;

use HexCoupon\App\Controllers\BaseController;
use HexCoupon\App\Core\Lib\SingleTon;

class GetSpecificProductForSpecificProduct extends BaseController
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method specific_products_against_specific_products
	 * @return void
	 * Customer gets a specific products against a specific product
	 */
	public function specific_products_against_specific_products( $customer_purchases, $customer_gets_as_free, $main_product_min_purchased_quantity, $cart_item_quantity, $free_item_id, $string_to_be_replaced, $coupon_id, $main_product_single_id, $cart_product_ids )
	{
		$hexcoupon_bogo_instance = HexcouponBogoController::getInstance();

		if ( 'a_specific_product' === $customer_gets_as_free && 'a_specific_product' === $customer_purchases ) {
			if ( $cart_item_quantity < $main_product_min_purchased_quantity ) {
				// Remove free item from cart, if '$main_product_min_purchased_quantity' is less than the '$cart_item_quantity'
				HexcouponBogoController::getInstance()->remove_cart_product( $free_item_id );

				// Show error message to the user if main product quantity is not sufficient to get the offer
				add_action( 'woocommerce_before_cart', [ $hexcoupon_bogo_instance, 'cart_custom_error_message' ] );
			}
			// check if free items is not empty and cart item quantity is bigger than the main product min purchases quantity
			if ( ! empty( $free_item_id ) && $cart_item_quantity >= $main_product_min_purchased_quantity ) {
				// loop through all the free products
				foreach ( $free_item_id as $free_gift_single_id ) {
					// Get the title of product
					$free_product_title = get_the_title( $free_gift_single_id );
					// Replace the unnecessary strings from the title
					$free_product_title_lowercase = str_replace( $string_to_be_replaced, '-', strtolower( $free_product_title ) );
					// Get the quantity of free products
					$free_product_quantity = get_post_meta( $coupon_id, $free_product_title_lowercase . '-free_product_quantity', true );
					$free_product_quantity = ! empty( $free_product_quantity ) ? $free_product_quantity : 1;

					// If the main purchased product and the free product is not the same product
					if ( $free_gift_single_id != $main_product_single_id ) {
						// If free item is not in the cart then add free items to the cart with 'add_to_cart()'
						if ( ! in_array( $free_gift_single_id, $cart_product_ids ) ) {
							WC()->cart->add_to_cart( $free_gift_single_id, $free_product_quantity );
							add_action( 'woocommerce_before_cart', [ $hexcoupon_bogo_instance, 'cart_custom_success_message' ] );
							break;
						}
						// If the free item is already in the cart then update the quantity
						if ( in_array( $free_gift_single_id, $cart_product_ids ) ) {
							if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) )
								return;

							if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
								return;

							// generate free item cart key
							$generate_free_item_id = WC()->cart->generate_cart_id( $free_gift_single_id );
							WC()->cart->set_quantity( $generate_free_item_id, $free_product_quantity );
							add_action( 'woocommerce_before_cart', [ $hexcoupon_bogo_instance, 'cart_custom_success_message' ] );
							break;
						}
					}
				}
			}
		}
	}
}
