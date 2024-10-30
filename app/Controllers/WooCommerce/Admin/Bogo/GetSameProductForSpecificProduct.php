<?php

namespace hexcoupon\app\Controllers\WooCommerce\Admin\Bogo;

use HexCoupon\App\Controllers\BaseController;
use HexCoupon\App\Core\Lib\SingleTon;

class GetSameProductForSpecificProduct extends BaseController
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method specific_products_against_same_product
	 * @return void
	 * Add same product against a specific product
	 */
	public function specific_products_against_same_product( $customer_purchases, $customer_gets_as_free, $main_product_min_purchased_quantity, $cart_item_quantity, $free_item_id, $coupon_id, $wc_cart, $main_product_id )
	{
		$hexcoupon_bogo_instance = HexcouponBogoController::getInstance();

		if ( 'same_product_as_free' === $customer_gets_as_free && 'a_specific_product' === $customer_purchases ) {
			if ( $cart_item_quantity < $main_product_min_purchased_quantity ) {
				// Show error message to the user if main product quantity is not sufficient to get the offer
				add_action( 'woocommerce_before_cart', [ $hexcoupon_bogo_instance, 'cart_custom_error_message' ] );
			}
			else {
				foreach ( $free_item_id as $free_single_item ) {
					$free_converted_title = HexcouponBogoController::getInstance()->convert_and_replace_unnecessary_string( $free_single_item );
					$free_quantity = get_post_meta( $coupon_id, $free_converted_title . '-free_product_quantity', true );
					$purchased_min_quantity = get_post_meta( $coupon_id, $free_converted_title . '-purchased_min_quantity', true );
					$free_single_key = $wc_cart->generate_cart_id( $free_single_item );
					if ( ! $wc_cart->find_product_in_cart( $free_single_key ) ) {
						if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) )
							return;

						if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
							return;

						$wc_cart->add_to_cart( $free_single_item, $free_quantity );
					}
					if ( $wc_cart->find_product_in_cart( $free_single_key ) ) {
						if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) )
							return;

						if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
							return;

						$final_quantity = $free_quantity + $purchased_min_quantity;

						$wc_cart->set_quantity( $free_single_key, $final_quantity );
					}
				}
			}
		}
	}
}
