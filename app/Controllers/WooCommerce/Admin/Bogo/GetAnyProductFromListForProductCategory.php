<?php
namespace hexcoupon\app\Controllers\WooCommerce\Admin\Bogo;

use HexCoupon\App\Controllers\BaseController;
use HexCoupon\App\Core\Lib\SingleTon;

class GetAnyProductFromListForProductCategory extends BaseController
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method product_categories_against_any_product_listed_below
	 * @return void
	 * Customer gets any product listed below against any product from the product categories
	 */
	public function product_categories_against_any_product_listed_below( $customer_purchases, $customer_gets_as_free, $coupon_id, $free_item_id, $main_product_id, $wc_cart, $selected_products_as_free )
	{
		$hexcoupon_bogo_instance = HexcouponBogoController::getInstance();

		if ( 'any_products_listed_below' === $customer_gets_as_free && 'product_categories' === $customer_purchases ) {
			add_action( 'woocommerce_after_cart_table', [ $hexcoupon_bogo_instance, 'custom_content_below_coupon_button_for_categories' ] );

			HexcouponBogoController::getInstance()->update_quantity_after_updating_cart( $coupon_id, $free_item_id, $main_product_id, $customer_purchases );

			HexcouponBogoController::getInstance()->remove_product_in_case_of_any_product_listed_below( $wc_cart, $selected_products_as_free );
		}
	}
}
