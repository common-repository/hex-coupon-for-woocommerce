<?php
namespace hexcoupon\app\Controllers\WooCommerce\Admin\Bogo;

use HexCoupon\App\Controllers\BaseController;
use HexCoupon\App\Core\Lib\SingleTon;

class GetSpecificProductAndCombinationOfProductForProductCategory extends BaseController
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method product_categories_against_specific_product_and_combination_of_product
	 * @return void
	 * Customer gets specific product and a combination of product against any product from the categories
	 */
	public function product_categories_against_specific_product_and_combination_of_product( $customer_purchases, $customer_gets_as_free, $free_item_id, $wc_cart, $coupon_id )
	{
		if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) )
			return;

		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
			return;

		if ( ( 'a_specific_product' === $customer_gets_as_free || 'a_combination_of_products' === $customer_gets_as_free ) && 'product_categories' === $customer_purchases ) {
			foreach ( $free_item_id as $free_single ) {
				$free_single_key = $wc_cart->generate_cart_id( $free_single );
				$free_single_converted_title = HexcouponBogoController::getInstance()->convert_and_replace_unnecessary_string( $free_single );
				$free_single_quantity = get_post_meta( $coupon_id, $free_single_converted_title . '-free_product_quantity', true );
				$free_single_quantity = ! empty( $free_single_quantity ) ? $free_single_quantity : 1;

				// Add the product to the cart if it's not already been added in the cart
				if ( ! $wc_cart->find_product_in_cart( $free_single_key ) ) {
					$wc_cart->add_to_cart( $free_single, $free_single_quantity );
				}
				// Update the product quantity if it's already been added in the cart
				if ( $wc_cart->find_product_in_cart( $free_single_key ) ) {
					$wc_cart->set_quantity( $free_single_key, $free_single_quantity );
				}
			}
		}
	}
}
