<?php
namespace HexCoupon\App\Controllers;

use HexCoupon\App\Core\Lib\SingleTon;
use Kathamo\Framework\Lib\Controller;


class ConvertCartPageToClassic extends Controller
{
	use SingleTon;

	private $base_url = 'hexcoupon/v1/';

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		add_filter( 'the_content', [ $this, 'alter_cart_page_with_cart_shortcode' ] );
	}

	/**
	 * Override the cart page with the old woocommerce legacy pattern
	 *
	 * @return void
	 */
	public function alter_cart_page_with_cart_shortcode( $content ) {
		if ( class_exists( 'WooCommerce' ) ) {
			// Check if it's the WooCommerce cart page
			if ( is_cart() ) {
				// Insert the [woocommerce_cart] shortcode in the cart page of the site.
				$content = '[woocommerce_cart]';
			}
		}

		return $content;
	}
}
